<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\StockingDTO;
use App\Domain\Models\Stocking;
use Carbon\Carbon;

final class StockingMapper
{
    public static function toDTO(Stocking $model): StockingDTO
    {
        return new StockingDTO(
            id: $model->id,
            batchId: (string) $model->batch_id,
            stockingDate: self::formatStockingDate($model->stocking_date),
            quantity: (int) $model->quantity,
            averageWeight: (float) $model->average_weight,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString()
        );
    }

    /**
     * Converts DTO to array (persistence format).
     *
     * @return array<string, mixed>
     */
    public static function toArray(StockingDTO $dto): array
    {
        return [
            'id'             => $dto->id,
            'batch_id' => $dto->batchId,
            'stocking_date'  => $dto->stockingDate,
            'quantity'       => $dto->quantity,
            'average_weight' => $dto->averageWeight,
        ];
    }

    /**
     * Converts request array to persistence array.
     * Accepts camelCase (batchId, stockingDate, averageWeight) and snake_case (batch_id, stocking_date, average_weight).
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
        } elseif (isset($data['batch_id'])) {
            $mapped['batch_id'] = $data['batch_id'];
        }

        if (isset($data['stockingDate'])) {
            $mapped['stocking_date'] = self::formatStockingDate($data['stockingDate']);
        } elseif (isset($data['stocking_date'])) {
            $mapped['stocking_date'] = self::formatStockingDate($data['stocking_date']);
        }

        if (isset($data['quantity'])) {
            $mapped['quantity'] = (int) $data['quantity'];
        }

        if (isset($data['averageWeight'])) {
            $mapped['average_weight'] = (float) $data['averageWeight'];
        } elseif (isset($data['average_weight'])) {
            $mapped['average_weight'] = (float) $data['average_weight'];
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    public static function modelToArray(Stocking $model): array
    {
        return [
            'id'            => $model->id,
            'batchId' => $model->batch?->id,
            'stockingDate'  => self::formatStockingDate($model->stocking_date),
            'quantity'      => (int) $model->quantity,
            'averageWeight' => (float) $model->average_weight,
            'createdAt'     => $model->created_at?->toDateTimeString(),
            'updatedAt'     => $model->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * Format stocking_date to string (Y-m-d).
     *
     * @param mixed $stockingDate
     */
    private static function formatStockingDate($stockingDate): string
    {
        if ($stockingDate === null || $stockingDate === '') {
            return '';
        }

        if ($stockingDate instanceof \DateTimeInterface) {
            return $stockingDate->format('Y-m-d');
        }

        if (is_object($stockingDate) && method_exists($stockingDate, 'toDateString')) {
            /** @var mixed $result */
            $result = $stockingDate->toDateString();
            return is_string($result) ? $result : '';
        }

        if (is_string($stockingDate)) {
            try {
                return Carbon::parse($stockingDate)->format('Y-m-d');
            } catch (\Exception) {
                return preg_match('/^\d{4}-\d{2}-\d{2}$/', $stockingDate) ? $stockingDate : '';
            }
        }

        return '';
    }
}
