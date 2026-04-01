<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\TankType;
use App\Domain\Repositories\TankTypeRepositoryInterface;

final class TankTypeRepository implements TankTypeRepositoryInterface
{
    public function listAllOrdered(): array
    {
        return TankType::query()
            ->orderBy('name')
            ->get(['id', 'name', 'description'])
            ->map(static fn (TankType $t): array => [
                'id'          => $t->id,
                'name'        => $t->name,
                'description' => $t->description,
            ])
            ->values()
            ->all();
    }
}
