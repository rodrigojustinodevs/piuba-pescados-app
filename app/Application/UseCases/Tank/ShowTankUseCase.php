<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Application\DTOs\TankDTO;
use App\Domain\Enums\Status;
use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;

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
            return null;
        }

        return new TankDTO(
            id: $tank->id,
            name: $tank->name,
            capacityLiters: $tank->capacity_liters,
            location: $tank->location,
            status: Status::from($tank->status),
            tankType: [
                'id'   => $tank->tankType->id ?? '',
                'name' => $tank->tankType->name ?? '',
            ],
            company: [
                'name' => $tank->company->name ?? '',
            ],
            createdAt: $tank->created_at?->toDateTimeString(),
            updatedAt: $tank->updated_at?->toDateTimeString()
        );
    }
}
