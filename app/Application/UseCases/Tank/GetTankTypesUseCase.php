<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Domain\Models\TankType;

class GetTankTypesUseCase
{
    /**
     * @return array<int, array<string, string|null>>
     */
    public function execute(): array
    {
        $tankTypes = TankType::orderBy('name')->get();

        return $tankTypes->map(function (TankType $tankType): array {
            return [
                'id'          => $tankType->id,
                'name'        => $tankType->name,
                'description' => $tankType->description ?? null,
            ];
        })->toArray();
    }
}
