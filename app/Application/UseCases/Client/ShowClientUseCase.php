<?php

declare(strict_types=1);

namespace App\Application\UseCases\Client;

use App\Application\DTOs\ClientDTO;
use App\Domain\Models\Client;
use App\Domain\Repositories\ClientRepositoryInterface;
use RuntimeException;

class ShowClientUseCase
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
    }

    public function execute(string $id): ?ClientDTO
    {
        $client = $this->clientRepository->showClient('id', $id);

        if (! $client instanceof Client) {
            throw new RuntimeException('Client not found');
        }

        return new ClientDTO(
            id: $client->id,
            name: $client->name,
            phone: $client->phone,
            email: $client->email,
            contact: $client->contact,
            personType: $client->person_type,
            documentNumber: $client->document_number,
            address: $client->address,
            company: [
                'name' => $client->company->name ?? null,
            ],
            createdAt: $client->created_at?->toDateTimeString(),
            updatedAt: $client->updated_at?->toDateTimeString()
        );
    }
}
