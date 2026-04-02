<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supply;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\SupplyInputDTO;
use App\Domain\Models\Supply;
use App\Infrastructure\Persistence\SupplyRepository;
use Illuminate\Support\Facades\DB;

final readonly class CreateSupplyUseCase
{
    public function __construct(
        private SupplyRepository $supplyRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Supply
    {
        return DB::transaction(function () use ($data): Supply {
            $data['company_id'] = $this->companyResolver->resolve();

            $dto = SupplyInputDTO::fromArray($data);

            return $this->supplyRepository->create($dto);
        });
    }
}
