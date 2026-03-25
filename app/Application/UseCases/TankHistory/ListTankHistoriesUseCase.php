<?php

declare(strict_types=1);

namespace App\Application\UseCases\TankHistory;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TankHistoryRepositoryInterface;

final readonly class ListTankHistoriesUseCase
{
    public function __construct(
        private TankHistoryRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array{
     *     tank_id?: string|null,
     *     event?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->repository->paginate($filters);
    }
}
