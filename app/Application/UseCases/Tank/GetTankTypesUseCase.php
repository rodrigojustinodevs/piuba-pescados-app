<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Domain\Repositories\TankTypeRepositoryInterface;

final readonly class GetTankTypesUseCase
{
    public function __construct(
        private TankTypeRepositoryInterface $tankTypeRepository,
    ) {
    }

    /**
     * @return list<array{id: string, name: string|null, description: string|null}>
     */
    public function execute(): array
    {
        return $this->tankTypeRepository->listAllOrdered();
    }
}
