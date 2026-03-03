<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\BatchDTO;
use App\Domain\Enums\Cultivation;
use App\Domain\Enums\Status;
use App\Domain\Models\Batch;
use App\Domain\ValueObjects\EntryDate;
use App\Domain\ValueObjects\InitialQuantity;
use App\Domain\ValueObjects\Species;
use Carbon\Carbon;

final class BatchMapper
{
    public static function toDTO(Batch $model): BatchDTO
    {
        $entryDate = self::formatEntryDate($model->entry_date);

        return new BatchDTO(
            id: $model->id,
            name: $model->name ?? null,
            description: $model->description ?? null,
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
    public static function toArray(BatchDTO $dto): array
    {
        return [
            'id'               => $dto->id,
            'name'             => $dto->name,
            'description'      => $dto->description,
            'entry_date'       => $dto->entryDate,
            'initial_quantity' => $dto->initialQuantity,
            'species'          => $dto->species,
            'status'           => $dto->status->value,
            'cultivation'      => $dto->cultivation->value,
        ];
    }

    /**
     * Map request array to persistence array.
     * Accepts camelCase (tankId, entryDate, initialQuantity).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function fromRequest(array $data): array
    {
        $mapped = [];

        if (isset($data['name'])) {
            $mapped['name'] = (string) $data['name'];
        }

        if (isset($data['description'])) {
            $mapped['description'] = (string) $data['description'];
        }

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
    public static function modelToArray(Batch $model): array
    {
        $entryDate = self::formatEntryDate($model->entry_date);

        return [
            'id'              => $model->id,
            'name'            => $model->name,
            'description'     => $model->description,
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

    /**
     * @param mixed $entryDate
     */
    private static function formatEntryDate($entryDate): ?string
    {
        if ($entryDate === null || $entryDate === '') {
            return null;
        }

        if (is_string($entryDate)) {
            try {
                $parsed = Carbon::parse($entryDate);
                return $parsed->format('Y-m-d');
            } catch (\Exception $e) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}/', $entryDate)) {
                    return $entryDate;
                }
                return null;
            }
        }

        if ($entryDate instanceof \DateTimeInterface) {
            return $entryDate->format('Y-m-d');
        }

        if (is_object($entryDate) && method_exists($entryDate, 'toDateString')) {
            return $entryDate->toDateString();
        }

        return null;
    }
}
