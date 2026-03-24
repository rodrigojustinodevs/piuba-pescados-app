<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\SaleInputDTO;
use App\Application\Services\SaleService;
use App\Domain\Models\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateSaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
        private SaleService $saleService,
    ) {
    }

    /**
     * @param array<string, mixed> $data Data already validated by the FormRequest
     */
    public function execute(array $data): Sale
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto = SaleInputDTO::fromArray($data);

        return DB::transaction(function () use ($dto): Sale {
            if ($dto->stockingId !== null) {
                $this->saleService->guardBiomass(
                    stockingId:      $dto->stockingId,
                    requestedWeight: $dto->totalWeight,
                );
            }

            $sale = $this->repository->create($dto);

            $this->saleService->generateReceivable($dto, $sale);

            return $sale;
        });
    }
}
