<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Client\GuardClientCreditAction;
use App\Application\Actions\Sale\GenerateReceivableAction;
use App\Application\Actions\Sale\GuardBiomassAction;
use App\Application\Actions\Sale\RegisterBiomassOutflowAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\HarvestSaleDTO;
use App\Domain\Events\SaleProcessed;
use App\Domain\Exceptions\ClientMissingFiscalDataException;
use App\Domain\Exceptions\ClosedStockingException;
use App\Domain\Models\Client;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Motor principal de despesca e venda.
 *
 * Dentro de um único DB::transaction:
 *   1. Valida que a estocagem não está encerrada.
 *   2. Valida biomassa disponível com margem de tolerância.
 *   3. Persiste a venda.
 *   4. Registra baixa no livro-razão (direction=out) com custo unitário acumulado.
 *   5. Encerra a estocagem quando is_total_harvest = true.
 *   6. Gera "Contas a Receber" (FinancialTransaction PENDING, reference_type=sale).
 */
final readonly class ProcessHarvestSaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
        private GuardBiomassAction $guardBiomass,
        private RegisterBiomassOutflowAction $registerOutflow,
        private GenerateReceivableAction $generateReceivable,
        private GuardClientCreditAction $guardClientCredit,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
     */
    public function execute(array $data): Sale
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto = HarvestSaleDTO::fromArray($data);

        return DB::transaction(function () use ($dto): Sale {
            if ($dto->stockingId !== null) {
                return $this->processWithStocking($dto);
            }

            return $this->processWithoutStocking($dto);
        });
    }

    /**
     * Fluxo completo: validação de biomassa → venda → baixa → ciclo de vida → recebível.
     */
    private function processWithStocking(HarvestSaleDTO $dto): Sale
    {
        /** @var Stocking $stocking */
        $stocking = Stocking::findOrFail($dto->stockingId);

        if ($stocking->isClosed()) {
            throw new ClosedStockingException($stocking->id);
        }

        // Passo 0: Valida dados fiscais se emissão de nota fiscal solicitada
        $this->guardClientFiscalData($dto);

        // Passo 1a: Valida limite de crédito do cliente
        $this->guardClientCredit->execute($dto->clientId, $dto->totalRevenue());

        // Passo 1b: Valida biomassa com tolerância configurável
        $this->guardBiomass->executeWithTolerance(
            stocking:         $stocking,
            requestedWeight:  $dto->totalWeight,
            tolerancePercent: $dto->tolerancePercent,
        );

        // Passo 2: Persiste a venda
        $sale = $this->repository->create($dto->toSaleInputDTO());

        // Passo 3: Baixa de biomassa no livro-razão
        // Calcula peso já vendido ANTES desta venda (exclui a atual do cálculo)
        $alreadySoldWeight = $this->repository->soldWeightByStocking(
            stockingId:    $stocking->id,
            excludeSaleId: $sale->id,
        );

        $this->registerOutflow->execute($stocking, $sale, $alreadySoldWeight);

        // Passo 4: Encerra a estocagem se for despesca total
        if ($dto->isHarvestTotal) {
            $stocking->markAsClosed();
        }

        // Passo 5: Gera Contas a Receber
        $this->generateReceivable->execute($dto->toSaleInputDTO(), $sale);

        // Passo 6: Dispara evento para gerar histórico automático no lote
        SaleProcessed::dispatch($sale);

        return $sale;
    }

    /**
     * Venda simples sem estocagem vinculada: persiste + recebível apenas.
     */
    private function processWithoutStocking(HarvestSaleDTO $dto): Sale
    {
        $this->guardClientFiscalData($dto);
        $this->guardClientCredit->execute($dto->clientId, $dto->totalRevenue());

        $sale = $this->repository->create($dto->toSaleInputDTO());

        $this->generateReceivable->execute($dto->toSaleInputDTO(), $sale);

        // Dispara mesmo sem stocking; o listener ignora quando stocking_id é null
        SaleProcessed::dispatch($sale);

        return $sale;
    }

    /**
     * Se needs_invoice for true, exige que o cliente tenha document_number e address.
     *
     * @throws ClientMissingFiscalDataException
     */
    private function guardClientFiscalData(HarvestSaleDTO $dto): void
    {
        if (! $dto->needsInvoice) {
            return;
        }

        /** @var Client|null $client */
        $client = Client::find($dto->clientId);

        if (
            $client === null
            || empty($client->document_number)
            || empty($client->address)
        ) {
            throw new ClientMissingFiscalDataException($dto->clientId);
        }
    }
}
