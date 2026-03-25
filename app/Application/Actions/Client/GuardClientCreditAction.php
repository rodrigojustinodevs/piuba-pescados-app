<?php

declare(strict_types=1);

namespace App\Application\Actions\Client;

use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Exceptions\ClientCreditLimitExceededException;
use App\Domain\Models\Client;
use Illuminate\Support\Facades\DB;

/**
 * Verifica se o cliente possui limite de crédito definido e se a nova venda
 * ultrapassaria esse limite somando a exposição atual (contas a receber pendentes/em atraso).
 *
 * Regra: se credit_limit for null, nenhuma restrição é aplicada.
 */
final readonly class GuardClientCreditAction
{
    /**
     * @throws ClientCreditLimitExceededException
     */
    public function execute(string $clientId, float $newSaleAmount): void
    {
        /** @var Client|null $client */
        $client = Client::find($clientId);

        if ($client === null || $client->credit_limit === null) {
            return;
        }

        $creditLimit = (float) $client->credit_limit;

        $currentExposure = (float) DB::table('financial_transactions')
            ->join('sales', 'sales.id', '=', 'financial_transactions.reference_id')
            ->where('sales.client_id', $clientId)
            ->where('financial_transactions.reference_type', 'sale')
            ->whereIn('financial_transactions.status', [
                FinancialTransactionStatus::PENDING->value,
                FinancialTransactionStatus::OVERDUE->value,
            ])
            ->whereNull('financial_transactions.deleted_at')
            ->whereNull('sales.deleted_at')
            ->sum('financial_transactions.amount');

        if (($currentExposure + $newSaleAmount) > $creditLimit) {
            throw new ClientCreditLimitExceededException(
                clientId:        $clientId,
                creditLimit:     $creditLimit,
                currentExposure: $currentExposure,
                newSaleAmount:   $newSaleAmount,
            );
        }
    }
}
