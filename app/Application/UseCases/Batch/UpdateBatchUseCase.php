<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\Actions\Batch\ValidateActiveBatchInTankAction;
use App\Application\DTOs\BatchInputDTO;
use App\Domain\Enums\BatchStatus;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateBatchUseCase
{
    public function __construct(
        private BatchRepositoryInterface $repository,
        private ValidateActiveBatchInTankAction $validateTank,
    ) {
    }

    /**
     * @param array<string, mixed> $data Validated data from the FormRequest
     */
    public function execute(string $id, array $data): Batch
    {
        $currentBatch = $this->repository->findOrFail($id);
        $dto          = BatchInputDTO::fromArray($data);

        $targetStatus = $dto->status ?? $currentBatch->status;

        if ($targetStatus === BatchStatus::ACTIVE->value) {
            $this->validateTank->execute($dto->tankId, $id);
        }

        return DB::transaction(fn (): Batch => $this->repository->update($id, $dto->toPersistence()));
    }
}
