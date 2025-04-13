<?php

declare(strict_types=1);

namespace App\Application\UseCases\Client;

use App\Application\DTOs\ClientDTO;
use App\Domain\Models\Client;
use App\Domain\Repositories\ClientRepositoryInterface;
use RuntimeException;

class UpdateClientUseCase
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): ClientDTO
    {
        $client = $this->clientRepository->update($id, $data);

        if (! $client instanceof Client) {
            throw new RuntimeException('Client not found');
        }

        return new ClientDTO(
            id: $client->id,
            name: $client->name,
            personType: $client->person_type,
            documentNumber: $client->document_number,
            email: $client->email,
            address: $client->address,
            contact: $client->contact,
            phone: $client->phone,
            company: [
                'name' => $client->company->name ?? null,
            ],
            createdAt: $client->created_at?->toDateTimeString(),
            updatedAt: $client->updated_at?->toDateTimeString()
        );
    }
}
