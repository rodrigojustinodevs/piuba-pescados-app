<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\TransferDTO;
use App\Domain\Models\Transfer;

final class TransferMapper
{
    public static function toDTO(Transfer $model): TransferDTO
    {
        return new TransferDTO(
            id: $model->id,
            batchId: (string) $model->batch_id,
            originTankId: (string) $model->origin_tank_id,
            destinationTankId: (string) $model->destination_tank_id,
            quantity: (int) $model->quantity,
            description: (string) $model->description,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString()
        );
    }

    /**
     * Converte DTO para array (formato para persistência).
     *
     * @return array<string, mixed>
     */
    public static function toArray(TransferDTO $dto): array
    {
        return [
            'id'                  => $dto->id,
            'batch_id'            => $dto->batchId,
            'origin_tank_id'      => $dto->originTankId,
            'destination_tank_id' => $dto->destinationTankId,
            'quantity'            => $dto->quantity,
            'description'         => $dto->description,
        ];
    }

    /**
     * Converte array de request para array de persistência.
     * Aceita campos em camelCase (batchId, originTankId, destinationTankId)
     * e mantém compatibilidade com snake_case (batch_id, origin_tank_id, destination_tank_id).
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

        if (isset($data['originTankId'])) {
            $mapped['origin_tank_id'] = $data['originTankId'];
        } elseif (isset($data['origin_tank_id'])) {
            $mapped['origin_tank_id'] = $data['origin_tank_id'];
        }

        if (isset($data['destinationTankId'])) {
            $mapped['destination_tank_id'] = $data['destinationTankId'];
        } elseif (isset($data['destination_tank_id'])) {
            $mapped['destination_tank_id'] = $data['destination_tank_id'];
        }

        if (isset($data['quantity'])) {
            $mapped['quantity'] = (int) $data['quantity'];
        }

        if (isset($data['description'])) {
            $mapped['description'] = (string) $data['description'];
        }

        return $mapped;
    }

    /**
     * Converte Model para array (formato de saída).
     *
     * @return array<string, mixed>
     */
    public static function modelToArray(Transfer $model): array
    {
        return [
            'id'                => $model->id,
            'batchId'           => $model->batch_id,
            'originTankId'      => $model->origin_tank_id,
            'destinationTankId' => $model->destination_tank_id,
            'quantity'          => (int) $model->quantity,
            'description'       => $model->description,
            'createdAt'         => $model->created_at?->toDateTimeString(),
            'updatedAt'         => $model->updated_at?->toDateTimeString(),
        ];
    }
}
