<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Domain\Repositories\MortalityRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteMortalityUseCase
{
    public function __construct(
        protected MortalityRepositoryInterface $mortalityRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->mortalityRepository->delete($id));
    }
}
