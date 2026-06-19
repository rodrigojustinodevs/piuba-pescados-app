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
     *     tankId?: string|null,
     *     species?: string|null,
     *     perPage?: int,
     *
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        if (! CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        return $this->repository->paginate($filters);
    }
}
