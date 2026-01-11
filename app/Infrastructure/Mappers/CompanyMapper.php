<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\CompanyDTO;
use App\Domain\Enums\Status;
use App\Domain\Models\Company;
use App\Domain\ValueObjects\Address;
use App\Domain\ValueObjects\CNPJ;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Phone;

final class CompanyMapper
{
    /**
     * Converte Model para DTO usando Value Objects
     */
    public static function toDTO(Company $model): CompanyDTO
    {
        $address = new Address(
            street: $model->address_street,
            number: $model->address_number,
            complement: $model->address_complement,
            neighborhood: $model->address_neighborhood,
            city: $model->address_city,
            state: $model->address_state,
            zipCode: $model->address_zip_code
        );

        return new CompanyDTO(
            id: $model->id,
            name: $model->name,
            cnpj: $model->cnpj,
            email: $model->email,
            phone: $model->phone,
            address: $address->toArray(),
            status: Status::from($model->status ?? 'active'),
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString()
        );
    }

    /**
     * Converte DTO para array (formato para persistência)
     *
     * @return array<string, mixed>
     */
    public static function toArray(CompanyDTO $dto): array
    {
        $address = $dto->address;

        return [
            'id'                   => $dto->id,
            'name'                 => $dto->name,
            'cnpj'                 => $dto->cnpj,
            'email'                => $dto->email,
            'phone'                => $dto->phone,
            'address_street'       => $address['street'] ?? null,
            'address_number'       => $address['number'] ?? null,
            'address_complement'   => $address['complement'] ?? null,
            'address_neighborhood' => $address['neighborhood'] ?? null,
            'address_city'         => $address['city'] ?? null,
            'address_state'        => $address['state'] ?? null,
            'address_zip_code'     => $address['zipCode'] ?? null,
            'status'               => $dto->status->value,
        ];
    }

    /**
     * Converte array de request para array de persistência
     * Encapsula criação de Value Objects e validações
     * Aceita campos em camelCase (addressStreet) para não expor estrutura do banco
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function fromRequest(array $data): array
    {
        $mapped = [];

        if (isset($data['name'])) {
            $name           = new Name($data['name']);
            $mapped['name'] = $name->value();
        }

        if (isset($data['cnpj'])) {
            $cnpj           = new CNPJ($data['cnpj']);
            $mapped['cnpj'] = $cnpj->value();
        }

        if (isset($data['email']) && ! empty($data['email'])) {
            $email           = new Email($data['email']);
            $mapped['email'] = $email->value();
        }

        if (isset($data['phone'])) {
            $phone           = new Phone($data['phone']);
            $mapped['phone'] = $phone->value();
        }

        // Processar address - prioriza campos camelCase diretos para não expor estrutura do banco
        // Formato 1: Campos diretos em camelCase (addressStreet, addressNumber, etc.) - PREFERENCIAL
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

        // Formato 2: Objeto address (address.street, address.number, etc.) - COMPATIBILIDADE
        // Usado apenas se não houver campos camelCase diretos
        if ($addressData === [] && isset($data['address']) && is_array($data['address'])) {
            $addressData = $data['address'];
        }

        if ($addressData !== []) {
            $address                        = Address::fromArray($addressData);
            $addressArray                   = $address->toArray();
            $mapped['address_street']       = $addressArray['street'] ?? null;
            $mapped['address_number']       = $addressArray['number'] ?? null;
            $mapped['address_complement']   = $addressArray['complement'] ?? null;
            $mapped['address_neighborhood'] = $addressArray['neighborhood'] ?? null;
            $mapped['address_city']         = $addressArray['city'] ?? null;
            $mapped['address_state']        = $addressArray['state'] ?? null;
            $mapped['address_zip_code']     = $addressArray['zipCode'] ?? null;
        }

        if (isset($data['active'])) {
            $mapped['status'] = $data['active'] ? 'active' : 'inactive';
        } elseif (isset($data['status'])) {
            $mapped['status'] = $data['status'];
        } else {
            $mapped['status'] = 'active';
        }

        return $mapped;
    }

    /**
     * Converte Model para array usando Value Objects
     *
     * @return array<string, mixed>
     */
    public static function modelToArray(Company $model): array
    {
        $address = new Address(
            street: $model->address_street,
            number: $model->address_number,
            complement: $model->address_complement,
            neighborhood: $model->address_neighborhood,
            city: $model->address_city,
            state: $model->address_state,
            zipCode: $model->address_zip_code
        );

        return [
            'id'         => $model->id,
            'name'       => $model->name,
            'cnpj'       => $model->cnpj,
            'email'      => $model->email,
            'phone'      => $model->phone,
            'address'    => $address->toArray(),
            'status'     => $model->status,
            'created_at' => $model->created_at?->toDateTimeString(),
            'updated_at' => $model->updated_at?->toDateTimeString(),
        ];
    }
}
