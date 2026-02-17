<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\SettlementDTO;
use App\Domain\Models\Settlement;
use Carbon\Carbon;

final class SettlementMapper
{
    public static function toDTO(Settlement $model): SettlementDTO
    {
        return new SettlementDTO(
            id: $model->id,
            batcheId: (string) $model->batche_id,
            settlementDate: self::formatSettlementDate($model->settlement_date),
            quantity: (int) $model->quantity,
            averageWeight: (float) $model->average_weight,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString()
        );
    }

    /**
     * Converte DTO para array (formato para persistência).
     *
     * @return array<string, mixed>
     */
    public static function toArray(SettlementDTO $dto): array
    {
        return [
            'id'              => $dto->id,
            'batche_id'       => $dto->batcheId,
            'settlement_date' => $dto->settlementDate,
            'quantity'        => $dto->quantity,
            'average_weight'  => $dto->averageWeight,
        ];
    }

    /**
     * Converte array de request para array de persistência.
     * Aceita campos em camelCase (batcheId, settlementDate, averageWeight)
     * e mantém compatibilidade com snake_case (batche_id, settlement_date, average_weight).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function fromRequest(array $data): array
    {
        $mapped = [];

        // Compatibilidade: houve caso de "batch_id" no update request
        if (isset($data['batcheId'])) {
            $mapped['batche_id'] = $data['batcheId'];
        } elseif (isset($data['batche_id'])) {
            $mapped['batche_id'] = $data['batche_id'];
        } elseif (isset($data['batch_id'])) {
            $mapped['batche_id'] = $data['batch_id'];
        }

        if (isset($data['settlementDate'])) {
            $mapped['settlement_date'] = self::formatSettlementDate($data['settlementDate']);
        } elseif (isset($data['settlement_date'])) {
            $mapped['settlement_date'] = self::formatSettlementDate($data['settlement_date']);
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
    public static function modelToArray(Settlement $model): array
    {
        return [
            'id'             => $model->id,
            'batcheId'       => $model->batche?->id,
            'settlementDate' => self::formatSettlementDate($model->settlement_date),
            'quantity'       => (int) $model->quantity,
            'averageWeight'  => (float) $model->average_weight,
            'createdAt'      => $model->created_at?->toDateTimeString(),
            'updatedAt'      => $model->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * Formata settlement_date para string (Y-m-d), tratando Carbon/string/null.
     *
     * @param mixed $settlementDate
     */
    private static function formatSettlementDate($settlementDate): string
    {
        if ($settlementDate === null || $settlementDate === '') {
            return '';
        }

        if ($settlementDate instanceof \DateTimeInterface) {
            return $settlementDate->format('Y-m-d');
        }

        if (is_object($settlementDate) && method_exists($settlementDate, 'toDateString')) {
            /** @var mixed $result */
            $result = $settlementDate->toDateString();
            return is_string($result) ? $result : '';
        }

        if (is_string($settlementDate)) {
            try {
                return Carbon::parse($settlementDate)->format('Y-m-d');
            } catch (\Exception) {
                // Se já vier no formato esperado, devolve como está
                return preg_match('/^\d{4}-\d{2}-\d{2}$/', $settlementDate) ? $settlementDate : '';
            }
        }

        return '';
    }
}

