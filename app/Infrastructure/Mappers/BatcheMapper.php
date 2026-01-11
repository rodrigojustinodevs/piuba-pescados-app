<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\BatcheDTO;
use App\Domain\Enums\Cultivation;
use App\Domain\Enums\Status;
use App\Domain\Models\Batche;
use App\Domain\ValueObjects\EntryDate;
use App\Domain\ValueObjects\InitialQuantity;
use App\Domain\ValueObjects\Species;

final class BatcheMapper
{
    public static function toDTO(Batche $model): BatcheDTO
    {
        $entryDate = $model->entry_date?->toDateString();

        return new BatcheDTO(
            id: $model->id,
            initialQuantity: $model->initial_quantity,
            species: $model->species,
            status: Status::from($model->status ?? 'active'),
            cultivation: Cultivation::from($model->cultivation),
            tank: [
                'id'   => $model->tank->id ?? null,
                'name' => $model->tank->name ?? null,
            ],
            entryDate: $entryDate,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(BatcheDTO $dto): array
    {
        return [
            'id'               => $dto->id,
            'entry_date'       => $dto->entryDate,
            'initial_quantity' => $dto->initialQuantity,
            'species'          => $dto->species,
            'status'           => $dto->status->value,
            'cultivation'      => $dto->cultivation->value,
        ];
    }

    /**
     * Converte array de request para array de persistência
     * Encapsula criação de Value Objects e validações
     * Aceita campos em camelCase (tankId, entryDate, initialQuantity) para não expor estrutura do banco
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function fromRequest(array $data): array
    {
        $mapped = [];

        if (isset($data['species'])) {
            $species           = new Species($data['species']);
            $mapped['species'] = $species->value();
        }

        if (isset($data['initialQuantity'])) {
            $quantity                   = InitialQuantity::fromInt((int) $data['initialQuantity']);
            $mapped['initial_quantity'] = $quantity->value();
        } elseif (isset($data['initial_quantity'])) {
            $quantity                   = InitialQuantity::fromInt((int) $data['initial_quantity']);
            $mapped['initial_quantity'] = $quantity->value();
        }

        if (isset($data['entryDate'])) {
            $entryDate            = EntryDate::fromString($data['entryDate']);
            $mapped['entry_date'] = $entryDate->toDateString();
        } elseif (isset($data['entry_date'])) {
            $entryDate            = EntryDate::fromString($data['entry_date']);
            $mapped['entry_date'] = $entryDate->toDateString();
        }

        if (isset($data['tankId'])) {
            $mapped['tank_id'] = $data['tankId'];
        } elseif (isset($data['tank_id'])) {
            $mapped['tank_id'] = $data['tank_id'];
        }

        $mapped['status'] = $data['status'] ?? 'active';

        if (isset($data['cultivation'])) {
            $mapped['cultivation'] = $data['cultivation'];
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    public static function modelToArray(Batche $model): array
    {
        $entryDate = $model->entry_date?->toDateString();

        return [
            'id'              => $model->id,
            'entryDate'       => $entryDate,
            'initialQuantity' => $model->initial_quantity,
            'species'         => $model->species,
            'status'          => $model->status,
            'cultivation'     => $model->cultivation,
            'tank'            => [
                'id'   => $model->tank->id ?? null,
                'name' => $model->tank->name ?? null,
            ],
            'created_at' => $model->created_at?->toDateTimeString(),
            'updated_at' => $model->updated_at?->toDateTimeString(),
        ];
    }
}
