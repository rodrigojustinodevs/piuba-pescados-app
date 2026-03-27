<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Application\DTOs\TankInputDTO;
use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateTankUseCase
{
    public function __construct(
        private TankRepositoryInterface $tankRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Tank
    {
        return DB::transaction(function () use ($data): Tank {
            $dto = TankInputDTO::fromArray($data);

            return $this->tankRepository->create($dto);
        });
    }
}
