<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supply;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\SupplyInputDTO;
use App\Domain\Models\Supply;
use App\Infrastructure\Persistence\SupplyRepository;
use Illuminate\Support\Facades\DB;

final readonly class UpdateSupplyUseCase
{
    public function __construct(
        private SupplyRepository $supplyRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Supply
    {
        $supply = $this->supplyRepository->findOrFail($id);

        $data['company_id'] = $this->companyResolver->resolve((string) $supply->company_id);

        return DB::transaction(function () use ($id, $data): Supply {
            $dto = SupplyInputDTO::fromArray($data);

            return $this->supplyRepository->update($id, [
                'company_id'   => $dto->companyId,
                'name'         => $dto->name,
                'category'     => $dto->category,
                'default_unit' => $dto->defaultUnit,
            ]);
        });
    }
}

