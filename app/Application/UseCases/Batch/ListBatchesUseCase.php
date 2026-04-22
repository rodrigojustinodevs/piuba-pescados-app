<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ListBatchesUseCase
{
    public function __construct(
        private BatchRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array{
     *     status?: string|null,
     *     tank_id?: string|null,
     *     species?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['companyId'] = CompanyContext::requireCompanyId();

        return $this->repository->paginate($filters);
    }
}
