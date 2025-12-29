<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Application\DTOs\TankDTO;
use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Infrastructure\Mappers\TankMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

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
            $mappedData = TankMapper::fromRequest($data);

            $tank = $this->tankRepository->update($id, $mappedData);

            if (! $tank instanceof Tank) {
                throw new RuntimeException('Tank not found');
            }

            return TankMapper::toDTO($tank);
        });
    }
}
