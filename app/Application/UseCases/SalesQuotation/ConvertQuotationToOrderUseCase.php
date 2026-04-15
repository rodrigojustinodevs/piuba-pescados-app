<?php

declare(strict_types=1);

namespace App\Application\UseCases\SalesQuotation;

use App\Application\Actions\Client\GuardClientCreditAction;
use App\Application\Actions\FinancialTransaction\GenerateReceivableAction;
use App\Application\Actions\Sale\GuardBiomassAction;
use App\Application\Actions\Sale\GuardClientFiscalDataAction;
use App\Application\Actions\Sale\RegisterBiomassOutflowAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\ConvertQuotationDTO;
use App\Application\DTOs\SaleInputDTO;
use App\Domain\Enums\SalesOrderStatus;
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
use InvalidArgumentException;

final readonly class ConvertQuotationToOrderUseCase
{
    private const float BIOMASS_TOLERANCE_PERCENT = 50.0;

    public function __construct(
        private SalesOrderRepositoryInterface $salesOrderRepository,
        private StockingRepositoryInterface $stockingRepository,
        private SaleRepositoryInterface $saleRepository,
        private GuardBiomassAction $guardBiomass,
        private GuardClientFiscalDataAction $guardClientFiscalData,
        private GuardClientCreditAction $guardClientCredit,
        private RegisterBiomassOutflowAction $registerOutflow,
        private GenerateReceivableAction $generateReceivable,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param string               $quotationId ID do orçamento a ser
     *                                          convertido
     * @param array<string, mixed> $overrides Payload validado com dados
     *                                        adicionais (ex: needsInvoice, financialCategoryId)
     */
    public function execute(string $quotationId, array $overrides): SalesOrder
    {
        $companyId = $this->companyResolver->resolve(
            isset($overrides['company_id']) && is_string($overrides['company_id'])
                ? $overrides['company_id']
                : (isset($overrides['companyId']) && is_string($overrides['companyId'])
                    ? $overrides['companyId']
                    : null),
        );

        $quotation = $this->salesOrderRepository->findForCompanyOrFail($quotationId, $companyId);

        // 2. Guardrail de Domínio (Fail Fast)
        $this->ensureCanBeConverted($quotation);

        // O DTO absorve os dados que faltavam no orçamento original mas são exigidos no Pedido
        $dto = ConvertQuotationDTO::fromArray($overrides);

        // ── Passo 1: Validações — zero escritas no banco ───────────────────────
        $this->guardClientFiscalData->execute($quotation->client_id, $dto->needsInvoice);
        $this->guardClientCredit->execute($quotation->client_id, (float) $quotation->total_amount);

        // Valida se os peixes ainda estão nos tanques desde que o orçamento foi feito
        $validatedItems = $this->resolveAndValidateStockings($quotation, $dto);

        // ── Passo 2: Persistência atômica ─────────────────────────────────────
        return DB::transaction(fn (): SalesOrder => $this->persistConversion($quotation, $validatedItems, $dto));
    }

    private function ensureCanBeConverted(SalesOrder $quotation): void
    {
        if ($quotation->type->value !== SalesOrderType::QUOTATION->value) {
            throw new InvalidArgumentException('Document is not a quotation or has already been converted.');
        }

        // Se houver lógica de validade, entra aqui:
        if ($quotation->expiration_date && $quotation->expiration_date->isPast()) {
            throw new InvalidArgumentException("Quotation {$quotation->id} has expired.");
        }
    }

    /**
     * @return array<int, array{saleInputDTO: SaleInputDTO, stocking: Stocking}>
     */
    private function resolveAndValidateStockings(SalesOrder $quotation, ConvertQuotationDTO $dto): array
    {
        $validated = [];

        // Itera sobre os itens já existentes no orçamento
        foreach ($quotation->items as $item) {
            $stocking = $this->stockingRepository->findOrFail($item->stocking_id);

            if ($stocking->isClosed()) {
                throw new ClosedStockingException($stocking->id);
            }

            $this->guardBiomass->executeWithTolerance(
                stocking:         $stocking,
                requestedWeight:  (float) $item->quantity,
                tolerancePercent: self::BIOMASS_TOLERANCE_PERCENT,
            );

            // Monta o DTO combinando dados do Orçamento antigo com os novos inputs (overrides)
            $saleInputDTO = new SaleInputDTO(
                companyId:           $quotation->company_id,
                clientId:            $quotation->client_id,
                batchId:             (string) $stocking->batch_id,
                totalWeight:         (float) $item->quantity,
                pricePerKg:          (float) $item->unit_price,
                saleDate:            $dto->expectedPaymentDate,
                stockingId:          (string) $stocking->id,
                financialCategoryId: $dto->financialCategoryId,
                status:              SaleStatus::PENDING,
                notes:               $dto->notes ?? $quotation->notes,
            );

            $validated[] = [
                'saleInputDTO' => $saleInputDTO,
                'stocking'     => $stocking,
            ];
        }

        return $validated;
    }

    /**
     * @param array<int, array{saleInputDTO: SaleInputDTO, stocking: Stocking}> $validatedItems
     */
    private function persistConversion(
        SalesOrder $quotation,
        array $validatedItems,
        ConvertQuotationDTO $dto,
    ): SalesOrder {
        $order = $this->salesOrderRepository->update(
            (string) $quotation->id,
            [
            'type'                   => SalesOrderType::ORDER->value,
            'expected_delivery_date' => $dto->expectedDeliveryDate,
            'status'                 => SalesOrderStatus::OPEN->value,
            ]
        );

        /** @var Sale[] $createdSales */
        $createdSales = [];

        // 2. Os Side-Effects são idênticos aos do CreateSalesOrderUseCase
        foreach ($validatedItems as ['saleInputDTO' => $saleInputDTO, 'stocking' => $stocking]) {
            $sale = $this->saleRepository->create($saleInputDTO);

            $alreadySoldWeight = $this->saleRepository->soldWeightByStocking(
                stockingId:    (string) $stocking->id,
                excludeSaleId: (string) $sale->id,
            );

            $this->registerOutflow->execute($stocking, $sale, $alreadySoldWeight);

            $this->generateReceivable->execute($saleInputDTO, (string) $sale->id);

            $createdSales[] = $sale;
        }

        // 3. Disparo de eventos coletados
        foreach ($createdSales as $sale) {
            SaleProcessed::dispatch($sale);
        }

        return $order;
    }
}
