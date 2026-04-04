<?php

declare(strict_types=1);

namespace App\Application\Actions\Client;

use App\Application\Services\Client\ClientCreditService;
use App\Domain\Exceptions\ClientCreditLimitExceededException;
use App\Domain\Repositories\ClientRepositoryInterface;

final class GuardClientCreditAction
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
        private readonly ClientCreditService       $creditService,
    ) {
    }

    /**
     * Valida o limite de crédito do cliente antes de confirmar uma nova venda.
     * Não executa se o cliente não tiver limite de crédito configurado.
     *
     * @throws ClientCreditLimitExceededException
     */
    public function execute(string $clientId, float $saleAmount): void
    {
        $client = $this->clientRepository->findOrFail($clientId);

        $this->creditService->guardCreditLimit($client, $saleAmount);
    }
}
