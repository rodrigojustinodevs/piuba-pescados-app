<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\Actions\Batch\ValidateActiveBatchInTankAction;
use App\Application\DTOs\BatchInputDTO;
use App\Domain\Enums\BatchStatus;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateBatchUseCase
{
    public function __construct(
        private BatchRepositoryInterface $repository,
        private ValidateActiveBatchInTankAction $validateTank,
        private TankRepositoryInterface $tankRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data Validated data from the FormRequest
     */
    public function execute(array $data): Batch
    {
        $dto = BatchInputDTO::fromArray($data);

        $tank = $this->tankRepository->findOrFail($dto->tankId);

        $companyId = $tank->company_id ?? null;

        if (($dto->status ?? BatchStatus::ACTIVE->value) === BatchStatus::ACTIVE->value) {
            $this->validateTank->execute($dto->tankId, $companyId);
        }

        return DB::transaction(fn (): Batch => $this->repository->create($dto));
    }
}
