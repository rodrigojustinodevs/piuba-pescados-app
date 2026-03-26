<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Application\DTOs\TankInputDTO;
use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
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
    public function execute(string $id, array $data): Tank
    {
        return DB::transaction(function () use ($id, $data): Tank {
            $dto = TankInputDTO::fromArray($data);

            $tank = $this->tankRepository->update($id, $dto->toPersistence());

            if (! $tank instanceof Tank) {
                throw new RuntimeException('Tank not found');
            }

            return $tank;
        });
    }
}
