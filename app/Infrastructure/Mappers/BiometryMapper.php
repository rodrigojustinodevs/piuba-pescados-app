<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\BiometryDTO;
use App\Domain\Models\Biometry;
use Carbon\Carbon;

final class BiometryMapper
{
    public static function toDTO(Biometry $model): BiometryDTO
    {
        return new BiometryDTO(
            id: $model->id,
            batchId: (string) $model->batch_id,
            averageWeight: (float) $model->average_weight,
            fcr: (float) $model->fcr,
            biometryDate: self::formatBiometryDate($model->biometry_date),
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
            sampleWeight: isset($model->sample_weight) ? (float) $model->sample_weight : null,
            sampleQuantity: isset($model->sample_quantity) ? (int) $model->sample_quantity : null,
            biomassEstimated: isset($model->biomass_estimated) ? (float) $model->biomass_estimated : null,
            densityAtTime: isset($model->density_at_time) ? (float) $model->density_at_time : null,
            recommendedRation: isset($model->recommended_ration) ? (float) $model->recommended_ration : null,
        );
    }

    /**
     * Converte DTO para array (formato para persistência).
     *
     * @return array<string, mixed>
     */
    public static function toArray(BiometryDTO $dto): array
    {
        return [
            'id'             => $dto->id,
            'batch_id'       => $dto->batchId,
            'biometry_date'  => $dto->biometryDate,
            'average_weight' => $dto->averageWeight,
            'fcr'            => $dto->fcr,
        ];
    }

    /**
     * Converte array de request para array de persistência.
     * Aceita camelCase (batchId, biometryDate, averageWeight) e snake_case.
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

        if (isset($data['biometryDate'])) {
            $mapped['biometry_date'] = self::formatBiometryDate($data['biometryDate']);
        } elseif (isset($data['biometry_date'])) {
            $mapped['biometry_date'] = self::formatBiometryDate($data['biometry_date']);
        }

        if (isset($data['averageWeight'])) {
            $mapped['average_weight'] = (float) $data['averageWeight'];
        } elseif (isset($data['average_weight'])) {
            $mapped['average_weight'] = (float) $data['average_weight'];
        }

        if (isset($data['fcr'])) {
            $mapped['fcr'] = (float) $data['fcr'];
        }

        if (isset($data['sampleWeight'])) {
            $mapped['sample_weight'] = (float) $data['sampleWeight'];
        } elseif (isset($data['sample_weight'])) {
            $mapped['sample_weight'] = (float) $data['sample_weight'];
        }

        if (isset($data['sampleQuantity'])) {
            $mapped['sample_quantity'] = (int) $data['sampleQuantity'];
        } elseif (isset($data['sample_quantity'])) {
            $mapped['sample_quantity'] = (int) $data['sample_quantity'];
        }

        if (isset($data['biomassEstimated'])) {
            $mapped['biomass_estimated'] = (float) $data['biomassEstimated'];
        } elseif (isset($data['biomass_estimated'])) {
            $mapped['biomass_estimated'] = (float) $data['biomass_estimated'];
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    public static function modelToArray(Biometry $model): array
    {
        $arr = [
            'id'            => $model->id,
            'batchId'       => $model->batch_id,
            'biometryDate'  => self::formatBiometryDate($model->biometry_date),
            'averageWeight' => (float) $model->average_weight,
            'fcr'           => (float) $model->fcr,
            'createdAt'     => $model->created_at?->toDateTimeString(),
            'updatedAt'     => $model->updated_at?->toDateTimeString(),
        ];
        if (isset($model->sample_weight)) {
            $arr['sampleWeight'] = (float) $model->sample_weight;
        }
        if (isset($model->sample_quantity)) {
            $arr['sampleQuantity'] = (int) $model->sample_quantity;
        }
        if (isset($model->biomass_estimated)) {
            $arr['biomassEstimated'] = (float) $model->biomass_estimated;
        }
        return $arr;
    }

    /**
     * Formata biometry_date para string (Y-m-d).
     *
     * @param mixed $biometryDate
     */
    private static function formatBiometryDate($biometryDate): string
    {
        if ($biometryDate === null || $biometryDate === '') {
            return '';
        }

        if ($biometryDate instanceof \DateTimeInterface) {
            return $biometryDate->format('Y-m-d');
        }

        if (is_object($biometryDate) && method_exists($biometryDate, 'toDateString')) {
            $result = $biometryDate->toDateString();

            return is_string($result) ? $result : '';
        }

        if (is_string($biometryDate)) {
            try {
                return Carbon::parse($biometryDate)->format('Y-m-d');
            } catch (\Exception) {
                return preg_match('/^\d{4}-\d{2}-\d{2}$/', $biometryDate) ? $biometryDate : '';
            }
        }

        return '';
    }
}
