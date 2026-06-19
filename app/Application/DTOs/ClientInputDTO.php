<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * Carrega os dados de entrada para criação ou atualização de um cliente.
 * Construído a partir do array validado pelo FormRequest e consumido pelo Repository.
 */
final readonly class ClientInputDTO
{
    public function __construct(
        public string $companyId,
        public string $name,
        public string $personType,
        public ?string $documentNumber = null,
        public ?string $tradeName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $contact = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public string $status = 'active',
        public ?float $creditLimit = null,
        public ?string $priceGroup = null,
        public ?string $notes = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:      (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            name:           (string) ($data['name'] ?? ''),
            personType:     (string) ($data['person_type'] ?? $data['personType'] ?? ''),
            documentNumber: isset($data['document_number']) ? (string) $data['document_number']
                          : (isset($data['documentNumber']) ? (string) $data['documentNumber'] : null),
            tradeName:      isset($data['trade_name']) ? (string) $data['trade_name']
                          : (isset($data['tradeName']) ? (string) $data['tradeName'] : null),
            email:          isset($data['email']) ? (string) $data['email'] : null,
            phone:          isset($data['phone']) ? (string) $data['phone'] : null,
            contact:        isset($data['contact']) ? (string) $data['contact'] : null,
            address:        isset($data['address']) ? (string) $data['address'] : null,
            city:           isset($data['city']) ? (string) $data['city'] : null,
            state:          isset($data['state']) ? (string) $data['state'] : null,
            status:         (string) ($data['status'] ?? 'active'),
            creditLimit:    isset($data['credit_limit']) ? (float) $data['credit_limit']
                          : (isset($data['creditLimit']) ? (float) $data['creditLimit'] : null),
            priceGroup:     isset($data['price_group']) ? (string) $data['price_group']
                          : (isset($data['priceGroup']) ? (string) $data['priceGroup'] : null),
            notes:          isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    /**
     * Retorna os atributos prontos para persistência no banco.
     *
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return [
            'company_id'      => $this->companyId,
            'name'            => $this->name,
            'trade_name'      => $this->tradeName,
            'person_type'     => $this->personType,
            'document_number' => $this->documentNumber,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'contact'         => $this->contact,
            'address'         => $this->address,
            'city'            => $this->city,
            'state'           => $this->state,
            'status'          => $this->status,
            'credit_limit'    => $this->creditLimit,
            'price_group'     => $this->priceGroup,
            'notes'           => $this->notes,
        ];
    }
}
