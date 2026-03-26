<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\Actions\Batch\ValidateActiveBatchInTankAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\BatchInputDTO;
use App\Domain\Enums\BatchStatus;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateBatchUseCase
{
    public function __construct(
        private BatchRepositoryInterface $repository,
        private ValidateActiveBatchInTankAction $validateTank,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data Validated data from the FormRequest
     */
    public function execute(array $data): Batch
    {
        $dto       = BatchInputDTO::fromArray($data);
        $companyId = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        if ($dto->status === BatchStatus::ACTIVE->value) {
            $this->validateTank->execute($dto->tankId, $companyId);
        }

        return DB::transaction(fn (): Batch => $this->repository->create($dto));
    }
}
