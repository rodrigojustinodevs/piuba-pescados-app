<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TankRepositoryInterface;

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
        $companyHint = null;

        if (isset($filters['company_id'])) {
            $companyHint = (string) $filters['company_id'];
        } elseif (isset($filters['companyId'])) {
            $companyHint = (string) $filters['companyId'];
        }

        $companyId = $this->companyResolver->resolve($companyHint);

        $normalized = [
            'companyId'  => $companyId,
            'status'     => isset($filters['status']) ? (string) $filters['status'] : null,
            'tankTypeId' => isset($filters['tankTypeId']) ? (string) $filters['tankTypeId'] : null,
            'perPage'    => isset($filters['perPage']) ? (int) $filters['perPage'] : null,
            'search'     => isset($filters['search']) ? (string) $filters['search'] : null,
        ];

        return $this->tankRepository->paginate($normalized);
    }
}
