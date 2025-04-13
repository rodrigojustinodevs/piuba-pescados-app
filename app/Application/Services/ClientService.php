<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\ClientDTO;
use App\Application\UseCases\Client\CreateClientUseCase;
use App\Application\UseCases\Client\DeleteClientUseCase;
use App\Application\UseCases\Client\ListClientsUseCase;
use App\Application\UseCases\Client\ShowClientUseCase;
use App\Application\UseCases\Client\UpdateClientUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientService
{
    public function __construct(
        protected CreateClientUseCase $createClientUseCase,
        protected ListClientsUseCase $listClientsUseCase,
        protected ShowClientUseCase $showClientUseCase,
        protected UpdateClientUseCase $updateClientUseCase,
        protected DeleteClientUseCase $deleteClientUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): ClientDTO
    {
        return $this->createClientUseCase->execute($data);
    }

    public function showAllClients(): AnonymousResourceCollection
    {
        return $this->listClientsUseCase->execute();
    }

    public function showClient(string $id): ?ClientDTO
    {
        return $this->showClientUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateClient(string $id, array $data): ClientDTO
    {
        return $this->updateClientUseCase->execute($id, $data);
    }

    public function deleteClient(string $id): bool
    {
        return $this->deleteClientUseCase->execute($id);
    }
}
