<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\Actions\Batch\CreateDistributedBatchesAction;
use App\Application\Actions\Batch\ValidateTanksForDistributionAction;
use App\Application\DTOs\BatchDistributionInputDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class DistributeBatchUseCase
{
    public function __construct(
        private ValidateTanksForDistributionAction $validateTanks,
        private CreateDistributedBatchesAction $createBatches,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Collection<int, \App\Domain\Models\Batch>
     *
     * @throws \App\Domain\Exceptions\TankAlreadyHasActiveBatchException
     * @throws \Throwable
     */
    public function execute(array $data): Collection
    {
        $input = BatchDistributionInputDTO::fromArray($data);

        $this->validateTanks->execute($input);

        return DB::transaction(fn (): Collection => $this->createBatches->execute($input));
    }
}
