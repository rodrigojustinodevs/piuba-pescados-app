<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\PurchaseDTO;
use App\Domain\Models\Purchase;
use Carbon\Carbon;

final class PurchaseMapper
{
    public static function toDTO(Purchase $model): PurchaseDTO
    {
        $purchaseDate = self::formatPurchaseDate($model->purchase_date);

        $stocking = $model->relationLoaded('stocking') ? $model->stocking : null;

        return new PurchaseDTO(
            id: $model->id,
            inputName: (string) $model->input_name,
            quantity: (float) $model->quantity,
            totalPrice: (float) $model->total_price,
            purchaseDate: $purchaseDate,
            supplier: $model->relationLoaded('supplier') ? [
                'id'   => $model->supplier->id ?? null,
                'name' => $model->supplier->name ?? null,
            ] : null,
            company: $model->relationLoaded('company') ? [
                'name' => $model->company->name ?? null,
            ] : null,
            stockingId: $model->stocking_id,
            stocking: $stocking ? [
                'id'           => $stocking->id ?? null,
                'stockingDate' => $stocking->stocking_date?->toDateString(),
            ] : null,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(PurchaseDTO $dto): array
    {
        return [
            'id'            => $dto->id,
            'input_name'    => $dto->inputName,
            'quantity'      => $dto->quantity,
            'total_price'   => $dto->totalPrice,
            'purchase_date' => $dto->purchaseDate,
            'stocking_id'   => $dto->stockingId,
        ];
    }

    /**
     * Converte array de request para array de persistência.
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

        if (array_key_exists('stockingId', $data)) {
            $mapped['stocking_id'] = $data['stockingId'];
        } elseif (array_key_exists('stocking_id', $data)) {
            $mapped['stocking_id'] = $data['stocking_id'];
        }

        if (isset($data['inputName'])) {
            $mapped['input_name'] = $data['inputName'];
        } elseif (isset($data['input_name'])) {
            $mapped['input_name'] = $data['input_name'];
        }

        if (isset($data['quantity'])) {
            $mapped['quantity'] = (float) $data['quantity'];
        } elseif (isset($data['purchased_quantity'])) {
            $mapped['quantity'] = (float) $data['purchased_quantity'];
        }

        if (isset($data['totalPrice'])) {
            $mapped['total_price'] = (float) $data['totalPrice'];
        } elseif (isset($data['total_price'])) {
            $mapped['total_price'] = (float) $data['total_price'];
        }

        if (isset($data['purchaseDate'])) {
            $mapped['purchase_date'] = self::formatPurchaseDate($data['purchaseDate']);
        } elseif (isset($data['purchase_date'])) {
            $mapped['purchase_date'] = self::formatPurchaseDate($data['purchase_date']);
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    public static function modelToArray(Purchase $model): array
    {
        $purchaseDate = self::formatPurchaseDate($model->purchase_date);

        $stocking = $model->relationLoaded('stocking') ? $model->stocking : null;

        return [
            'id'           => $model->id,
            'inputName'    => $model->input_name,
            'quantity'     => (float) $model->quantity,
            'totalPrice'   => (float) $model->total_price,
            'purchaseDate' => $purchaseDate,
            'supplier'     => $model->relationLoaded('supplier') ? [
                'id'   => $model->supplier->id ?? null,
                'name' => $model->supplier->name ?? null,
            ] : null,
            'company'      => $model->relationLoaded('company') ? [
                'name' => $model->company->name ?? null,
            ] : null,
            'stockingId' => $model->stocking_id,
            'stocking'   => $stocking ? [
                'id'           => $stocking->id ?? null,
                'stockingDate' => $stocking->stocking_date?->toDateString(),
            ] : null,
            'createdAt' => $model->created_at?->toDateTimeString(),
            'updatedAt' => $model->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * @param mixed $purchaseDate
     */
    private static function formatPurchaseDate($purchaseDate): string
    {
        if ($purchaseDate === null || $purchaseDate === '') {
            return '';
        }

        if ($purchaseDate instanceof \DateTimeInterface) {
            return $purchaseDate->format('Y-m-d');
        }

        if (is_object($purchaseDate) && method_exists($purchaseDate, 'toDateString')) {
            $result = $purchaseDate->toDateString();

            return is_string($result) ? $result : '';
        }

        if (is_string($purchaseDate)) {
            try {
                return Carbon::parse($purchaseDate)->format('Y-m-d');
            } catch (\Exception) {
                return preg_match('/^\d{4}-\d{2}-\d{2}/', $purchaseDate) ? substr($purchaseDate, 0, 10) : '';
            }
        }

        return '';
    }
}

