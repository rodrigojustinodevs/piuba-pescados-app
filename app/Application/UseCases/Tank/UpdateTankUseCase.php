<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Application\DTOs\TankDTO;
use App\Domain\Enums\Status;
use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class UpdateTankUseCase
{
    public function __construct(
        protected TankRepositoryInterface $tankRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): TankDTO
    {
        return DB::transaction(function () use ($id, $data): TankDTO {
            $tank = $this->tankRepository->update($id, $data);

            if (! $tank instanceof Tank) {
                throw new Exception('Tank not found');
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
        });
    }
}
