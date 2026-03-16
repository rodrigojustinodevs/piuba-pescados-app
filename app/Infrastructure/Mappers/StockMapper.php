<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\StockDTO;
use App\Domain\Models\Stock;

final class StockMapper
{
    public static function toDTO(Stock $model): StockDTO
    {
        return new StockDTO(
            id: $model->id,
            currentQuantity: (float) $model->current_quantity,
            unit: (string) $model->unit,
            unitPrice: (float) $model->unit_price,
            minimumStock: (float) $model->minimum_stock,
            withdrawalQuantity: (float) $model->withdrawal_quantity,
            company: $model->relationLoaded('company') ? [
                'name' => $model->company->name ?? null,
            ] : null,
            supplier: $model->relationLoaded('supplier') ? [
                'id'   => $model->supplier->id ?? null,
                'name' => $model->supplier->name ?? null,
            ] : null,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(StockDTO $dto): array
    {
        return [
            'id'                  => $dto->id,
            'current_quantity'    => $dto->currentQuantity,
            'unit'                => $dto->unit,
            'unit_price'          => $dto->unitPrice,
            'minimum_stock'       => $dto->minimumStock,
            'withdrawal_quantity' => $dto->withdrawalQuantity,
        ];
    }

    /**
     * Converte dados de request para array de persistência.
     * Aceita camelCase e snake_case.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function fromRequest(array $data): array
    {
        $mapped = [];

        if (isset($data['companyId'])) {
            $mapped['company_id'] = $data['companyId'];
        } elseif (isset($data['company_id'])) {
            $mapped['company_id'] = $data['company_id'];
        }

        if (isset($data['supplierId'])) {
            $mapped['supplier_id'] = $data['supplierId'];
        } elseif (isset($data['supplier_id'])) {
            $mapped['supplier_id'] = $data['supplier_id'];
        }

        if (isset($data['currentQuantity'])) {
            $mapped['current_quantity'] = (float) $data['currentQuantity'];
        } elseif (isset($data['current_quantity'])) {
            $mapped['current_quantity'] = (float) $data['current_quantity'];
        }

        if (isset($data['unit'])) {
            $mapped['unit'] = (string) $data['unit'];
        }

        if (isset($data['minimumStock'])) {
            $mapped['minimum_stock'] = (float) $data['minimumStock'];
        } elseif (isset($data['minimum_stock'])) {
            $mapped['minimum_stock'] = (float) $data['minimum_stock'];
        }

        if (isset($data['withdrawalQuantity'])) {
            $mapped['withdrawal_quantity'] = (float) $data['withdrawalQuantity'];
        } elseif (isset($data['withdrawal_quantity'])) {
            $mapped['withdrawal_quantity'] = (float) $data['withdrawal_quantity'];
        }

        if (isset($data['unitPrice'])) {
            $mapped['unit_price'] = (float) $data['unitPrice'];
        } elseif (isset($data['unit_price'])) {
            $mapped['unit_price'] = (float) $data['unit_price'];
        }

        if (isset($data['totalCost'])) {
            $mapped['total_cost'] = (float) $data['totalCost'];
        } elseif (isset($data['total_cost'])) {
            $mapped['total_cost'] = (float) $data['total_cost'];
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    public static function modelToArray(Stock $model): array
    {
        return [
            'id'                 => $model->id,
            'currentQuantity'    => (float) $model->current_quantity,
            'unit'               => (string) $model->unit,
            'unitPrice'          => (float) $model->unit_price,
            'minimumStock'       => (float) $model->minimum_stock,
            'withdrawalQuantity' => (float) $model->withdrawal_quantity,
            'company'            => $model->relationLoaded('company') ? [
                'name' => $model->company->name ?? null,
            ] : null,
            'supplier' => $model->relationLoaded('supplier') ? [
                'id'   => $model->supplier->id ?? null,
                'name' => $model->supplier->name ?? null,
            ] : null,
            'createdAt' => $model->created_at?->toDateTimeString(),
            'updatedAt' => $model->updated_at?->toDateTimeString(),
        ];
    }
}
