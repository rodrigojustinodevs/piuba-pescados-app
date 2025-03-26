<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Domain\Repositories\TankRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteTankUseCase
{
    public function __construct(
        protected TankRepositoryInterface $tankRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->tankRepository->delete($id));
    }
}
