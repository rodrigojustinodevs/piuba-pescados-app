<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batche;

use App\Domain\Repositories\BatcheRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteBatcheUseCase
{
    public function __construct(
        protected BatcheRepositoryInterface $batcheRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->batcheRepository->delete($id));
    }
}
