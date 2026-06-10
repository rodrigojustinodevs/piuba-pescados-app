<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supply;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SupplyRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

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
        if (!CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        return $this->supplyRepository->paginate($filters);
    }
}
