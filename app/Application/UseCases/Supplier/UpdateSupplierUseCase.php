<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supplier;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\SupplierInputDTO;
use App\Domain\Models\Supplier;
use App\Domain\Repositories\SupplierRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateSupplierUseCase
{
    public function __construct(
        private SupplierRepositoryInterface $supplierRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Supplier
    {
        $supplier = $this->supplierRepository->findOrFail($id);

        $data['company_id'] = $this->companyResolver->resolve(
            $data['company_id']
                ?? $data['companyId']
                ?? (string) $supplier->company_id,
        );

        return DB::transaction(function () use ($id, $data): Supplier {
            $dto = SupplierInputDTO::fromArray($data);

            $map = [
                'company_id'         => 'companyId',
                'name'               => 'name',
                'contact'            => 'contact',
                'phone'              => 'phone',
                'email'              => 'email',
                'trade_name'         => 'tradeName',
                'document'           => 'document',
                'state_registration' => 'stateRegistration',
                'category'           => 'category',
                'payment_terms'      => 'paymentTerms',
                'rating'             => 'rating',
                'address'            => 'address',
                'status'             => 'status',
            ];

            $attributes = [];

            foreach ($map as $snakeKey => $camelKey) {
                if (array_key_exists($snakeKey, $data) || array_key_exists($camelKey, $data)) {
                    $attributes[$snakeKey] = $dto->{$camelKey};
                }
            }

            return $this->supplierRepository->update($id, $attributes);
        });
    }
}
