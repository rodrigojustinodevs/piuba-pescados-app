<?php

declare(strict_types=1);

namespace App\Application\Services\Client;

use App\Domain\Exceptions\ClientCreditLimitExceededException;
use App\Domain\Models\Client;
use App\Domain\Repositories\ClientRepositoryInterface;

final readonly class ClientCreditService
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
    ) {
    }

    public function guardCreditLimit(Client $client, float $saleAmount): void
    {
        if ($client->credit_limit === null) {
            return;
        }

        $creditLimit = (float) $client->credit_limit;

        if ($creditLimit < $saleAmount) {
            throw new ClientCreditLimitExceededException(
                clientName:        $client->name,
                creditLimit:     $creditLimit,
                currentExposure: 0,
                newSaleAmount:   $saleAmount,
            );
        }

        $currentExposure = $this->clientRepository->getPendingObligations($client->id);

        if (($currentExposure + $saleAmount) > $creditLimit) {
            throw new ClientCreditLimitExceededException(
                clientName:        $client->name,
                creditLimit:     $creditLimit,
                currentExposure: $currentExposure,
                newSaleAmount:   $saleAmount,
            );
        }
    }
}
