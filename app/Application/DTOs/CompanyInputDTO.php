<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\CNPJ;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Phone;

final readonly class CompanyInputDTO
{
    public function __construct(
        public ?string $name,
        public ?string $cnpj,
        public ?string $email,
        public ?string $phone,
        public ?string $addressStreet,
        public ?string $addressNumber,
        public ?string $addressComplement,
        public ?string $addressNeighborhood,
        public ?string $addressCity,
        public ?string $addressState,
        public ?string $addressZipCode,
        public string $status = 'active',
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $name  = isset($data['name']) ? (new Name($data['name']))->value() : null;
        $cnpj  = isset($data['cnpj']) ? (new CNPJ($data['cnpj']))->value() : null;
        $email = isset($data['email']) && ! empty($data['email']) ? (new Email($data['email']))->value() : null;
        $phone = isset($data['phone']) ? (new Phone($data['phone']))->value() : null;

        $addressData = [];

        if (isset($data['addressStreet'])) {
            $addressData['street'] = $data['addressStreet'];
        }

        if (isset($data['addressNumber'])) {
            $addressData['number'] = $data['addressNumber'];
        }

        if (isset($data['addressComplement'])) {
            $addressData['complement'] = $data['addressComplement'];
        }

        if (isset($data['addressNeighborhood'])) {
            $addressData['neighborhood'] = $data['addressNeighborhood'];
        }

        if (isset($data['addressCity'])) {
            $addressData['city'] = $data['addressCity'];
        }

        if (isset($data['addressState'])) {
            $addressData['state'] = $data['addressState'];
        }

        if (isset($data['addressZipCode'])) {
            $addressData['zipCode'] = $data['addressZipCode'];
        }

        if ($addressData === [] && isset($data['address']) && is_array($data['address'])) {
            $addressData = $data['address'];
        }

        $address      = $addressData !== [] ? Address::fromArray($addressData) : null;
        $addressArray = $address?->toArray() ?? [];

        $status = 'active';

        if (isset($data['active'])) {
            $status = $data['active'] ? 'active' : 'inactive';
        } elseif (isset($data['status'])) {
            $status = $data['status'];
        }

        return new self(
            name:                $name,
            cnpj:                $cnpj,
            email:               $email,
            phone:               $phone,
            addressStreet:       $addressArray['street'] ?? null,
            addressNumber:       $addressArray['number'] ?? null,
            addressComplement:   $addressArray['complement'] ?? null,
            addressNeighborhood: $addressArray['neighborhood'] ?? null,
            addressCity:         $addressArray['city'] ?? null,
            addressState:        $addressArray['state'] ?? null,
            addressZipCode:      $addressArray['zipCode'] ?? null,
            status:              $status,
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(): array
    {
        return array_filter([
            'name'                 => $this->name,
            'cnpj'                 => $this->cnpj,
            'email'                => $this->email,
            'phone'                => $this->phone,
            'address_street'       => $this->addressStreet,
            'address_number'       => $this->addressNumber,
            'address_complement'   => $this->addressComplement,
            'address_neighborhood' => $this->addressNeighborhood,
            'address_city'         => $this->addressCity,
            'address_state'        => $this->addressState,
            'address_zip_code'     => $this->addressZipCode,
            'status'               => $this->status,
        ], static fn (?string $v): bool => $v !== null);
    }
}
