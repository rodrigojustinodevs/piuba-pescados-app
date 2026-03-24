<?php

declare(strict_types=1);

namespace App\Application\UseCases\WaterQuality;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\WaterQualityRepositoryInterface;

class ListWaterQualitiesUseCase
{
    public function __construct(
        protected WaterQualityRepositoryInterface $waterQualityRepository,
        protected CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array{
     *     tank_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int|string|null,
     *     page?: int|string|null,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->waterQualityRepository->paginate($filters);
    }
}
