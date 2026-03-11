<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\MortalityDTO;
use App\Domain\Models\Mortality;
use Carbon\Carbon;

final class MortalityMapper
{
    public static function toDTO(Mortality $model): MortalityDTO
    {
        return new MortalityDTO(
            id: $model->id,
            batchId: (string) $model->batch_id,
            mortalityDate: self::formatMortalityDate($model->mortality_date),
            quantity: (int) $model->quantity,
            cause: (string) $model->cause,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString()
        );
    }

    /**
     * Converte DTO para array (formato para persistência).
     *
     * @return array<string, mixed>
     */
    public static function toArray(MortalityDTO $dto): array
    {
        return [
            'id'             => $dto->id,
            'batch_id'       => $dto->batchId,
            'mortality_date' => $dto->mortalityDate,
            'quantity'       => $dto->quantity,
            'cause'          => $dto->cause,
        ];
    }

    /**
     * Converte array de request para array de persistência.
     * Aceita camelCase (batchId, mortalityDate) e snake_case (batch_id, mortality_date).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function fromRequest(array $data): array
    {
        $mapped = [];

        if (isset($data['batchId'])) {
            $mapped['batch_id'] = $data['batchId'];
        } elseif (isset($data['batch_id'])) {
            $mapped['batch_id'] = $data['batch_id'];
        }

        if (isset($data['mortalityDate'])) {
            $mapped['mortality_date'] = self::formatMortalityDate($data['mortalityDate']);
        } elseif (isset($data['mortality_date'])) {
            $mapped['mortality_date'] = self::formatMortalityDate($data['mortality_date']);
        }

        if (isset($data['quantity'])) {
            $mapped['quantity'] = (int) $data['quantity'];
        }

        if (isset($data['cause'])) {
            $mapped['cause'] = (string) $data['cause'];
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    public static function modelToArray(Mortality $model): array
    {
        return [
            'id'            => $model->id,
            'batchId'       => $model->batch_id,
            'mortalityDate' => self::formatMortalityDate($model->mortality_date),
            'quantity'      => (int) $model->quantity,
            'cause'         => (string) $model->cause,
            'createdAt'     => $model->created_at?->toDateTimeString(),
            'updatedAt'     => $model->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * Formata mortality_date para string (Y-m-d).
     *
     * @param mixed $mortalityDate
     */
    private static function formatMortalityDate($mortalityDate): string
    {
        if ($mortalityDate === null || $mortalityDate === '') {
            return '';
        }

        if ($mortalityDate instanceof \DateTimeInterface) {
            return $mortalityDate->format('Y-m-d');
        }

        if (is_object($mortalityDate) && method_exists($mortalityDate, 'toDateString')) {
            $result = $mortalityDate->toDateString();

            return is_string($result) ? $result : '';
        }

        if (is_string($mortalityDate)) {
            try {
                return Carbon::parse($mortalityDate)->format('Y-m-d');
            } catch (\Exception) {
                return preg_match('/^\d{4}-\d{2}-\d{2}/', $mortalityDate) ? substr($mortalityDate, 0, 10) : '';
            }
        }

        return '';
    }
}
