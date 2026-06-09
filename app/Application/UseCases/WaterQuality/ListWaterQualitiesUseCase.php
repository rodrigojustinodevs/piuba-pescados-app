<?php

declare(strict_types=1);

namespace App\Application\UseCases\WaterQuality;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\WaterQualityListResult;
use App\Domain\Repositories\WaterQualityRepositoryInterface;
use App\Domain\ValueObjects\WaterQualityScore;
use App\Infrastructure\Security\CompanyContext;

class ListWaterQualitiesUseCase
{
    public function __construct(
        protected WaterQualityRepositoryInterface $waterQualityRepository,
        protected CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array{
     *     company_id?: string|null,
     *     search?: string|null,
     *     tank_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int|string|null,
     *     page?: int|string|null,
     * } $filters
     */
    public function execute(array $filters = []): WaterQualityListResult
    {
        if (! CompanyContext::isMasterAdmin()) {
            $filters['company_id'] = CompanyContext::requireCompanyId();
        }

        $paginator = $this->waterQualityRepository->paginate($filters);
        $score     = WaterQualityScore::fromCounts(
            $this->waterQualityRepository->countByQuality($filters),
        );

        return new WaterQualityListResult($paginator, $score);
    }
}
