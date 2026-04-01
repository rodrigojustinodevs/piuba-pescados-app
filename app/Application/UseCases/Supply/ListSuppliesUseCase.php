<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supply;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SupplyRepositoryInterface;

final readonly class ListSuppliesUseCase
{
    public function __construct(
        private SupplyRepositoryInterface $supplyRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->supplyRepository->paginate($filters);
    }
}
