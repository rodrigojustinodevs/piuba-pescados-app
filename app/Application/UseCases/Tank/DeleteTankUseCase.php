<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Domain\Repositories\TankRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteTankUseCase
{
    public function __construct(
        private TankRepositoryInterface $tankRepository,
    ) {
    }

    public function execute(string $id): void
    {
        $this->tankRepository->findOrFail($id);

        DB::transaction(function () use ($id): void {
            $this->tankRepository->delete($id);
        });
    }
}
