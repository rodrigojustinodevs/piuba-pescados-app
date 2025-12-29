<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Application\DTOs\TankDTO;
use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Infrastructure\Mappers\TankMapper;
use RuntimeException;

class ShowTankUseCase
{
    public function __construct(
        protected TankRepositoryInterface $tankRepository
    ) {
    }

    public function execute(string $id): ?TankDTO
    {
        $tank = $this->tankRepository->showTank('id', $id);

        if (! $tank instanceof Tank) {
            throw new RuntimeException('Tank not found');
        }

        return TankMapper::toDTO($tank);
    }
}
