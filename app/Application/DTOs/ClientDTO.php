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
        public string $companyId,
        public string $name,
        public ?string $personType = null,
        public ?string $tradeName = null,
        public ?string $documentNumber = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $contact = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public string $status = 'active',
        public ?float $creditLimit = null,
        public bool $isDefaulter = false,
        public ?string $priceGroup = null,
        public ?string $notes = null,
        public ?array $company = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function fromModel(\App\Domain\Models\Client $client): self
    {
        return new self(
            id:             $client->id,
            companyId:      $client->company_id,
            name:           $client->name,
            personType:     $client->person_type,
            tradeName:      $client->trade_name,
            documentNumber: $client->document_number,
            email:          $client->email,
            phone:          $client->phone,
            contact:        $client->contact,
            address:        $client->address,
            city:           $client->city,
            state:          $client->state,
            status:         $client->status->value,
            creditLimit:    $client->credit_limit !== null ? (float) $client->credit_limit : null,
            isDefaulter:    (bool) $client->is_defaulter,
            priceGroup:     $client->price_group?->value,
            notes:          $client->notes,
            company:        $client->relationLoaded('company') ? [
                'name' => $client->company?->name,
            ] : null,
            createdAt:      $client->created_at?->toIso8601String(),
            updatedAt:      $client->updated_at?->toIso8601String(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'companyId'      => $this->companyId,
            'name'           => $this->name,
            'personType'     => $this->personType,
            'tradeName'      => $this->tradeName,
            'documentNumber' => $this->documentNumber,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'contact'        => $this->contact,
            'address'        => $this->address,
            'city'           => $this->city,
            'state'          => $this->state,
            'status'         => $this->status,
            'creditLimit'    => $this->creditLimit,
            'isDefaulter'    => $this->isDefaulter,
            'priceGroup'     => $this->priceGroup,
            'notes'          => $this->notes,
            'company'        => $this->company,
            'createdAt'      => $this->createdAt,
            'updatedAt'      => $this->updatedAt,
        ];
    }
}
