<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Domain\Repositories\MortalityRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteMortalityUseCase
{
    public function __construct(
        private MortalityRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): void
    {
        $this->repository->findOrFail($id);

        DB::transaction(fn (): bool => $this->repository->delete($id));
    }
}
