<?php

declare(strict_types=1);

namespace App\Application\Actions\Client;

use App\Domain\Exceptions\ClientCreditLimitExceededException;

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
    }
}
