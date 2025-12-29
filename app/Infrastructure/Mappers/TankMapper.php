<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\TankDTO;
use App\Domain\Enums\Status;
use App\Domain\Models\Tank;
use App\Domain\ValueObjects\CapacityLiters;
use App\Domain\ValueObjects\Location;
use App\Domain\ValueObjects\Name;

final class TankMapper
{
    public static function toDTO(Tank $model): TankDTO
    {
        return new TankDTO(
            id: $model->id,
            name: $model->name,
            capacityLiters: $model->capacity_liters,
            location: $model->location,
            status: Status::from($model->status ?? 'active'),
            tankType: [
                'id'   => $model->tankType->id ?? null,
                'name' => $model->tankType->name ?? null,
            ],
            company: [
                'name' => $model->company->name ?? null,
            ],
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(TankDTO $dto): array
    {
        return [
            'id'              => $dto->id,
            'name'            => $dto->name,
            'capacity_liters' => $dto->capacityLiters,
            'location'        => $dto->location,
            'status'          => $dto->status->value,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function fromRequest(array $data): array
    {
        $mapped = [];

        if (isset($data['name'])) {
            $name           = new Name($data['name']);
            $mapped['name'] = $name->value();
        }

        if (isset($data['capacityLiters'])) {
            $capacity                  = CapacityLiters::fromInt((int) $data['capacityLiters']);
            $mapped['capacity_liters'] = $capacity->value();
        } elseif (isset($data['capacity_liters'])) {
            $capacity                  = CapacityLiters::fromInt((int) $data['capacity_liters']);
            $mapped['capacity_liters'] = $capacity->value();
        }

        if (isset($data['location'])) {
            $location           = new Location($data['location']);
            $mapped['location'] = $location->value();
        }

        if (isset($data['tankTypeId'])) {
            $mapped['tank_types_id'] = $data['tankTypeId'];
        } elseif (isset($data['tank_types_id'])) {
            $mapped['tank_types_id'] = $data['tank_types_id'];
        }

        if (isset($data['companyId'])) {
            $mapped['company_id'] = $data['companyId'];
        } elseif (isset($data['company_id'])) {
            $mapped['company_id'] = $data['company_id'];
        }

        // Processar status
        if (isset($data['status'])) {
            $mapped['status'] = $data['status'];
        } else {
            $mapped['status'] = 'active';
        }

        return $mapped;
    }

    /**
     * Converte Model para array usando Value Objects
     *
     * @return array<string, mixed>
     */
    public static function modelToArray(Tank $model): array
    {
        return [
            'id'             => $model->id,
            'name'           => $model->name,
            'capacityLiters' => $model->capacity_liters,
            'location'       => $model->location,
            'status'         => $model->status,
            'tankType'       => [
                'id'   => $model->tankType->id ?? null,
                'name' => $model->tankType->name ?? null,
            ],
            'company' => [
                'name' => $model->company->name ?? null,
            ],
            'created_at' => $model->created_at?->toDateTimeString(),
            'updated_at' => $model->updated_at?->toDateTimeString(),
        ];
    }
}
