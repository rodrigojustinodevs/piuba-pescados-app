<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Application\DTOs\TankDTO;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Infrastructure\Mappers\TankMapper;
use Illuminate\Support\Facades\DB;

class CreateTankUseCase
{
    public function __construct(
        protected TankRepositoryInterface $tankRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): TankDTO
    {
        return DB::transaction(function () use ($data): TankDTO {
            $mappedData = TankMapper::fromRequest($data);

            $tank = $this->tankRepository->create($mappedData);

            return TankMapper::toDTO($tank);
        });
    }
}
