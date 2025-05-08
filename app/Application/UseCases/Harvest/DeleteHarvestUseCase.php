<?php

declare(strict_types=1);

namespace App\Application\UseCases\Harvest;

use App\Domain\Repositories\HarvestRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteHarvestUseCase
{
    public function __construct(
        protected HarvestRepositoryInterface $harvestRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->harvestRepository->delete($id));
    }
}
