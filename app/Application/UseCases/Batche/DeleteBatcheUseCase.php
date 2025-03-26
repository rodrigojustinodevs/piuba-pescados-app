<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batche;

use App\Domain\Repositories\BatcheRepositoryInterface;

class DeleteBatcheUseCase
{
    public function __construct(
        protected BatcheRepositoryInterface $batcheRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return $this->batcheRepository->delete($id);
    }
}
