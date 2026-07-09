<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ShowTanksWithoutBatchesUseCase
{
    public function __construct(
        private TankRepositoryInterface $tankRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        if (! CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        return $this->tankRepository->paginateWithoutBatches($filters);
    }
}
