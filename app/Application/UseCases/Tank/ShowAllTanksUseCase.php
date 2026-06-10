<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ShowAllTanksUseCase
{
    public function __construct(
        private TankRepositoryInterface $tankRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, scalar|null> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {

        if (!CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        $normalized = [
            'companyId'  => isset($filters['companyId']) ? (string) $filters['companyId'] : null,
            'status'     => isset($filters['status']) ? (string) $filters['status'] : null,
            'tankTypeId' => isset($filters['tankTypeId']) ? (string) $filters['tankTypeId'] : null,
            'perPage'    => isset($filters['perPage']) ? (int) $filters['perPage'] : null,
            'search'     => isset($filters['search']) ? (string) $filters['search'] : null,
        ];

        return $this->tankRepository->paginate($normalized);
    }
}
