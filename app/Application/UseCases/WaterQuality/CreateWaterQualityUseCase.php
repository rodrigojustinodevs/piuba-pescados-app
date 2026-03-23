<?php

declare(strict_types=1);

namespace App\Application\UseCases\WaterQuality;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\WaterQualityDTO;
use App\Domain\Models\WaterQuality;
use App\Domain\Repositories\WaterQualityRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateWaterQualityUseCase
{
    public function __construct(
        protected WaterQualityRepositoryInterface $waterQualityRepository,
        protected CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): WaterQuality
    {
        return DB::transaction(function () use ($data): WaterQuality {
            $data['company_id'] = $this->companyResolver->resolve();
            $dto                = WaterQualityDTO::fromArray($data);
            $waterQuality       = $this->waterQualityRepository->create($dto);

            return $waterQuality->load('tank');
        });
    }
}
