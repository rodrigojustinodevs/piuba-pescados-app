<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ListTransfersUseCase
{
    public function __construct(
        private TransferRepositoryInterface $transferRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array{
     *     companyId?: string|null,
     *     batchId?: string|null,
     *     originTankId?: string|null,
     *     destinationTankId?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        if (!CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        return $this->transferRepository->paginate($filters);
    }
}
