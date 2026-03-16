<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\FeedingDTO;
use App\Domain\Models\Feeding;
use Carbon\Carbon;

final class FeedingMapper
{
    public static function toDTO(Feeding $model): FeedingDTO
    {
        return new FeedingDTO(
            id: $model->id,
            batchId: (string) $model->batch_id,
            feedingDate: self::formatFeedingDate($model->feeding_date),
            quantityProvided: (float) $model->quantity_provided,
            feedType: (string) $model->feed_type,
            stockId: $model->stock_id !== null ? (string) $model->stock_id : null,
            stockReductionQuantity: (float) $model->stock_reduction_quantity,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString()
        );
    }

    /**
     * Converte DTO para array (formato para persistência).
     *
     * @return array<string, mixed>
     */
    public static function toArray(FeedingDTO $dto): array
    {
        return [
            'id'                       => $dto->id,
            'batch_id'                 => $dto->batchId,
            'feeding_date'             => $dto->feedingDate,
            'quantity_provided'        => $dto->quantityProvided,
            'feed_type'                => $dto->feedType,
            'stock_id'                 => $dto->stockId,
            'stock_reduction_quantity' => $dto->stockReductionQuantity,
        ];
    }

    /**
     * Converte array de request para array de persistência.
     * Aceita camelCase (batchId, feedingDate, quantityProvided, etc.) e snake_case.
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
        } elseif (isset($data['batcheId'])) {
            $mapped['batch_id'] = $data['batcheId'];
        } elseif (isset($data['batche_id'])) {
            $mapped['batch_id'] = $data['batche_id'];
        }

        if (isset($data['feedingDate'])) {
            $mapped['feeding_date'] = self::formatFeedingDate($data['feedingDate']);
        } elseif (isset($data['feeding_date'])) {
            $mapped['feeding_date'] = self::formatFeedingDate($data['feeding_date']);
        }

        if (isset($data['quantityProvided'])) {
            $mapped['quantity_provided'] = (float) $data['quantityProvided'];
        } elseif (isset($data['quantity_provided'])) {
            $mapped['quantity_provided'] = (float) $data['quantity_provided'];
        }

        if (isset($data['feedType'])) {
            $mapped['feed_type'] = (string) $data['feedType'];
        } elseif (isset($data['feed_type'])) {
            $mapped['feed_type'] = (string) $data['feed_type'];
        }

        if (isset($data['stockId'])) {
            $mapped['stock_id'] = $data['stockId'];
        } elseif (isset($data['stock_id'])) {
            $mapped['stock_id'] = $data['stock_id'];
        }

        if (isset($data['stockReductionQuantity'])) {
            $mapped['stock_reduction_quantity'] = (float) $data['stockReductionQuantity'];
        } elseif (isset($data['stock_reduction_quantity'])) {
            $mapped['stock_reduction_quantity'] = (float) $data['stock_reduction_quantity'];
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    public static function modelToArray(Feeding $model): array
    {
        return [
            'id'                     => $model->id,
            'batchId'                => $model->batch_id,
            'feedingDate'            => self::formatFeedingDate($model->feeding_date),
            'quantityProvided'       => (float) $model->quantity_provided,
            'feedType'               => $model->feed_type,
            'stockId'                => $model->stock_id !== null ? (string) $model->stock_id : null,
            'stockReductionQuantity' => (float) $model->stock_reduction_quantity,
            'createdAt'              => $model->created_at?->toDateTimeString(),
            'updatedAt'              => $model->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * Formata feeding_date para string (Y-m-d).
     *
     * @param mixed $feedingDate
     */
    private static function formatFeedingDate($feedingDate): string
    {
        if ($feedingDate === null || $feedingDate === '') {
            return '';
        }

        if ($feedingDate instanceof \DateTimeInterface) {
            return $feedingDate->format('Y-m-d');
        }

        if (is_object($feedingDate) && method_exists($feedingDate, 'toDateString')) {
            $result = $feedingDate->toDateString();

            return is_string($result) ? $result : '';
        }

        if (is_string($feedingDate)) {
            try {
                return Carbon::parse($feedingDate)->format('Y-m-d');
            } catch (\Exception) {
                return preg_match('/^\d{4}-\d{2}-\d{2}/', $feedingDate) ? substr($feedingDate, 0, 10) : '';
            }
        }

        return '';
    }
}
