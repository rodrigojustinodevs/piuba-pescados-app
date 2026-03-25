<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * Representa os dados de um cliente na camada de saída (use case → controller → API).
 * Construído a partir do model Eloquent Client após persistência.
 */
final readonly class ClientDTO
{
    /**
     * @param array{name?: string|null}|null $company
     */
    public function __construct(
        public string $id,
        public string $name,
        public ?string $personType = null,
        public ?string $documentNumber = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $contact = null,
        public ?string $address = null,
        public ?float $creditLimit = null,
        public bool $isDefaulter = false,
        public ?string $priceGroup = null,
        public ?array $company = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    /**
     * Constrói o DTO a partir de um model Eloquent Client.
     *
     * @param \App\Domain\Models\Client $client
     */
    public static function fromModel(\App\Domain\Models\Client $client): self
    {
        return new self(
            id:             $client->id,
            name:           $client->name,
            personType:     $client->person_type,
            documentNumber: $client->document_number,
            email:          $client->email,
            phone:          $client->phone,
            contact:        $client->contact,
            address:        $client->address,
            creditLimit:    $client->credit_limit !== null ? (float) $client->credit_limit : null,
            isDefaulter:    (bool) $client->is_defaulter,
            priceGroup:     $client->price_group?->value,
            company:        $client->relationLoaded('company') ? [
                'name' => $client->company?->name,
            ] : null,
            createdAt:      $client->created_at?->toDateTimeString(),
            updatedAt:      $client->updated_at?->toDateTimeString(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'personType'     => $this->personType,
            'documentNumber' => $this->documentNumber,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'contact'        => $this->contact,
            'address'        => $this->address,
            'creditLimit'    => $this->creditLimit,
            'isDefaulter'    => $this->isDefaulter,
            'priceGroup'     => $this->priceGroup,
            'company'        => $this->company,
            'createdAt'      => $this->createdAt,
            'updatedAt'      => $this->updatedAt,
        ];
    }
}
