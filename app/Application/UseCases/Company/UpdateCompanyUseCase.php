<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Application\DTOs\CompanyDTO;
use App\Domain\Enums\Status;
use App\Domain\Models\Company;
use App\Domain\Repositories\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateCompanyUseCase
{
    public function __construct(
        protected CompanyRepositoryInterface $companyRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): CompanyDTO
    {
        return DB::transaction(function () use ($id, $data): CompanyDTO {
            if (isset($data['address']) && is_array($data['address'])) {
                $address = $data['address'];
                $data['address_street']      = $address['street'] ?? null;
                $data['address_number']      = $address['number'] ?? null;
                $data['address_complement']  = $address['complement'] ?? null;
                $data['address_neighborhood'] = $address['neighborhood'] ?? null;
                $data['address_city']        = $address['city'] ?? null;
                $data['address_state']       = $address['state'] ?? null;
                $data['address_zip_code']    = $address['zipCode'] ?? null;
                unset($data['address']);
            }

            if (isset($data['active'])) {
                $data['status'] = $data['active'] ? 'active' : 'inactive';
                unset($data['active']);
            }

            $company = $this->companyRepository->update($id, $data);

            if (! $company instanceof Company) {
                throw new RuntimeException('Company not found');
            }

            return CompanyDTO::fromArray([
                'id'                 => $company->id,
                'name'               => $company->name,
                'cnpj'               => $company->cnpj,
                'email'              => $company->email,
                'phone'              => $company->phone,
                'address_street'      => $company->address_street,
                'address_number'      => $company->address_number,
                'address_complement'  => $company->address_complement,
                'address_neighborhood' => $company->address_neighborhood,
                'address_city'        => $company->address_city,
                'address_state'       => $company->address_state,
                'address_zip_code'    => $company->address_zip_code,
                'status'              => $company->status,
                'created_at'          => $company->created_at?->toDateTimeString(),
                'updated_at'          => $company->updated_at?->toDateTimeString(),
            ]);
        });
    }
}
