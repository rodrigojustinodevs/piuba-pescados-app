<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supply;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\SupplyInputDTO;
use App\Domain\Models\Supply;
use App\Domain\Repositories\SupplyRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateSupplyUseCase
{
    public function __construct(
        private SupplyRepositoryInterface $supplyRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Supply
    {
        return DB::transaction(function () use ($data): Supply {
            $hint = $data['company_id'] ?? $data['companyId'] ?? null;

            $data['company_id'] = $this->companyResolver->resolve(
                is_string($hint) && $hint !== '' ? $hint : null
            );

            $dto = SupplyInputDTO::fromArray($data);

            return $this->supplyRepository->create($dto);
        });
    }
}
