<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;

final readonly class ShowTankUseCase
{
    public function __construct(
        private TankRepositoryInterface $tankRepository,
    ) {
    }

    public function execute(string $id): Tank
    {
        return $this->tankRepository->findOrFail($id);
    }
}
