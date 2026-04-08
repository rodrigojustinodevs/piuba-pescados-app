<?php

declare(strict_types=1);

namespace App\Application\Actions\Client;

use App\Application\Services\Client\ClientCreditService;
use App\Domain\Exceptions\ClientCreditLimitExceededException;
use App\Domain\Repositories\ClientRepositoryInterface;

/**
 * Verifica se o cliente possui limite de crédito definido e se a nova venda
 * ultrapassaria esse limite somando a exposição atual (contas a receber pendentes/em atraso).
 *
 * Regra: se credit_limit for null, nenhuma restrição é aplicada.
 */
final readonly class GuardClientCreditAction
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private ClientCreditService $creditService,
    ) {
    }

    /**
     * @throws ClientCreditLimitExceededException
     */
    public function execute(string $clientId, float $newSaleAmount): void
    {
        $client = $this->clientRepository->findOrFail($clientId);

        $this->creditService->guardCreditLimit($client, $newSaleAmount);
    }
}
