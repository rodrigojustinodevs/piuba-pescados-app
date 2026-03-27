<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TransferRepositoryInterface;

final readonly class ListTransfersUseCase
{
    public function __construct(
        private TransferRepositoryInterface $transferRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array{
     *     batch_id?: string|null,
     *     origin_tank_id?: string|null,
     *     destination_tank_id?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        // Multi-tenancy: garante isolamento por empresa
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->transferRepository->paginate($filters);
    }
}
