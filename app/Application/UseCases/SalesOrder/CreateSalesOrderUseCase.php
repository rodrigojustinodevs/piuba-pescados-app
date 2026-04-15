<?php

declare(strict_types=1);

namespace App\Application\UseCases\SalesOrder;

use App\Application\Actions\Client\GuardClientCreditAction;
use App\Application\Actions\FinancialTransaction\GenerateReceivableAction;
use App\Application\Actions\Sale\GuardBiomassAction;
use App\Application\Actions\Sale\GuardClientFiscalDataAction;
use App\Application\Actions\Sale\RegisterBiomassOutflowAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\SaleInputDTO;
use App\Application\DTOs\SalesOrderDTO;
use App\Domain\Enums\SalesOrderType;
use App\Domain\Enums\SaleStatus;
use App\Domain\Events\SaleProcessed;
use App\Domain\Exceptions\ClosedStockingException;
use App\Domain\Models\Sale;
use App\Domain\Models\SalesOrder;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\SalesOrderRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateSalesOrderUseCase
{
    private const float BIOMASS_TOLERANCE_PERCENT = 50.0;

    public function __construct(
        private CompanyResolverInterface $companyResolver,
        private SalesOrderRepositoryInterface $salesOrderRepository,
        private StockingRepositoryInterface $stockingRepository,
        private SaleRepositoryInterface $saleRepository,
        private GuardBiomassAction $guardBiomass,
        private GuardClientFiscalDataAction $guardClientFiscalData,
        private GuardClientCreditAction $guardClientCredit,
        private RegisterBiomassOutflowAction $registerOutflow,
        private GenerateReceivableAction $generateReceivable,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): SalesOrder
    {
        $data['company_id'] = $this->companyResolver->resolve(hint: $data['company_id'] ?? null);
        $data['type']       = SalesOrderType::ORDER->value;

        $dto = SalesOrderDTO::fromArray($data);

        // ── Passo 1: Validações — zero escritas no banco ───────────────────────
        $this->guardClientFiscalData->execute($dto->clientId, $dto->needsInvoice);
        $this->guardClientCredit->execute($dto->clientId, $dto->totalAmount());

        $validatedItems = $this->resolveAndValidateStockings($dto);

        // ── Passo 2: Persistência atômica ─────────────────────────────────────
        return DB::transaction(fn (): SalesOrder => $this->persist($dto, $validatedItems));
    }

    /**
     * @return array<int, array{saleInputDTO: SaleInputDTO, stocking: Stocking}>
     */
    private function resolveAndValidateStockings(SalesOrderDTO $dto): array
    {
        $validated = [];

        foreach ($dto->items as $item) {
            $stocking = $this->stockingRepository->findOrFail($item->stockingId);

            if ($stocking->isClosed()) {
                throw new ClosedStockingException($stocking->id);
            }

            $this->guardBiomass->executeWithTolerance(
                stocking:         $stocking,
                requestedWeight:  $item->quantity,
                tolerancePercent: self::BIOMASS_TOLERANCE_PERCENT,
            );

            $saleInputDTO = new SaleInputDTO(
                companyId:           $dto->companyId,
                clientId:            $dto->clientId,
                batchId:             (string) $stocking->batch_id,
                totalWeight:         $item->quantity,
                pricePerKg:          $item->unitPrice,
                saleDate:            now()->format('Y-m-d'),
                stockingId:          (string) $stocking->id,
                financialCategoryId: $dto->financialCategoryId,
                status:              SaleStatus::PENDING,
                notes:               $dto->notes,
            );

            $validated[] = ['saleInputDTO' => $saleInputDTO, 'stocking' => $stocking];
        }

        return $validated;
    }

    /**
     * @param array<int, array{saleInputDTO: SaleInputDTO, stocking: Stocking}> $validatedItems
     */
    private function persist(SalesOrderDTO $dto, array $validatedItems): SalesOrder
    {
        $order = $this->salesOrderRepository->createWithItems($dto);

        /**
 * @var Sale[] $createdSales
*/
        $createdSales = [];

        foreach ($validatedItems as ['saleInputDTO' => $saleInputDTO, 'stocking' => $stocking]) {
            $sale = $this->saleRepository->create($saleInputDTO);

            $alreadySoldWeight = $this->saleRepository->soldWeightByStocking(
                stockingId:    (string) $stocking->id,
                excludeSaleId: (string) $sale->id,
            );

            $this->registerOutflow->execute($stocking, $sale, $alreadySoldWeight);

            // Assinatura correta: GenerateReceivableAction::execute(SaleInputDTO, Sale)
            // Não (string) $sale->id — o Model é necessário para compor a descrição do recebível
            $this->generateReceivable->execute($saleInputDTO, (string) $sale->id);

            $createdSales[] = $sale;
        }

        foreach ($createdSales as $sale) {
            SaleProcessed::dispatch($sale);
        }

        return $order;
    }
}
