<?php

declare(strict_types=1);

namespace App\Application\UseCases\Client;

use App\Application\DTOs\ClientDTO;
use App\Domain\Repositories\ClientRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateClientUseCase
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): ClientDTO
    {
        return DB::transaction(function () use ($data): ClientDTO {
            $client = $this->clientRepository->create($data);

            return new ClientDTO(
                id: $client->id,
                name: $client->name,
                documentNumber: $client->document_number,
                personType: $client->person_type,
                email: $client->email,
                phone: $client->phone,
                contact: $client->contact,
                address: $client->address,
                company: [
                    'name' => $client->company->name ?? null,
                ],
                createdAt: $client->created_at?->toDateTimeString(),
                updatedAt: $client->updated_at?->toDateTimeString()
            );
        });
    }
}
