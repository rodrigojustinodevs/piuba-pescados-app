<?php

declare(strict_types=1);

namespace App\Application\Services\Client;

use App\Domain\Exceptions\ClientCreditLimitExceededException;
use App\Domain\Models\Client;
use App\Domain\Repositories\ClientRepositoryInterface;

final class ClientCreditService
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
    ) {
    }

    public function guardCreditLimit(Client $client, float $saleAmount): void
    {
        if ($client->credit_limit === null) {
            return;
        }

        if ($client->credit_limit < $saleAmount) {
            throw new ClientCreditLimitExceededException(
                clientId:        $client->id,
                creditLimit:     $client->credit_limit,
                currentExposure: 0,
                newSaleAmount:   $saleAmount,
            );
        }

        $creditLimit = (float) $client->credit_limit;

        $currentExposure = $this->clientRepository->getPendingObligations($client->id);

        if (($currentExposure + $saleAmount) > $creditLimit) {
            throw new ClientCreditLimitExceededException(
                clientId:        $client->id,
                creditLimit:     $creditLimit,
                currentExposure: $currentExposure,
                newSaleAmount:   $saleAmount,
            );
        }
    }
}
