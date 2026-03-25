<?php

declare(strict_types=1);

namespace App\Application\UseCases\Client;

use App\Application\DTOs\ClientDTO;
use App\Domain\Models\Client;
use App\Domain\Repositories\ClientRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShowClientUseCase
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
    }

    public function execute(string $id): ClientDTO
    {
        $client = $this->clientRepository->showClient('id', $id);

        if (! $client instanceof Client) {
            throw (new ModelNotFoundException())->setModel(Client::class, $id);
        }

        return ClientDTO::fromModel($client);
    }
}
