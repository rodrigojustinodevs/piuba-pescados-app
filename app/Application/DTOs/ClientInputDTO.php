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
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $contact = null,
        public ?string $address = null,
        public ?float $creditLimit = null,
        public ?string $priceGroup = null,
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
            email:          isset($data['email']) ? (string) $data['email'] : null,
            phone:          isset($data['phone']) ? (string) $data['phone'] : null,
            contact:        isset($data['contact']) ? (string) $data['contact'] : null,
            address:        isset($data['address']) ? (string) $data['address'] : null,
            creditLimit:    isset($data['credit_limit']) ? (float) $data['credit_limit']
                          : (isset($data['creditLimit']) ? (float) $data['creditLimit'] : null),
            priceGroup:     isset($data['price_group']) ? (string) $data['price_group']
                          : (isset($data['priceGroup']) ? (string) $data['priceGroup'] : null),
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
            'person_type'     => $this->personType,
            'document_number' => $this->documentNumber,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'contact'         => $this->contact,
            'address'         => $this->address,
            'credit_limit'    => $this->creditLimit,
            'price_group'     => $this->priceGroup,
        ];
    }
}
