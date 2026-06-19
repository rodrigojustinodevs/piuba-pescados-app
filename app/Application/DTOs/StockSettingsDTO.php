<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\StockStatusEnum;
use App\Domain\Enums\StockTypeEnum;

final readonly class StockSettingsDTO
{
    public function __construct(
        public ?float $minimumStock = null,
        public ?string $supplierId = null,
        public ?float $withdrawalQuantity = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?StockTypeEnum $type = null,
        public ?string $location = null,
        public ?string $responsible = null,
        public ?float $capacity = null,
        public ?StockStatusEnum $status = null,
        public ?string $notes = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
     */
    public static function fromArray(array $data): self
    {
        $type   = $data['type'] ?? null;
        $status = $data['status'] ?? null;

        return new self(
            minimumStock:       isset($data['minimum_stock'])
                ? (float) $data['minimum_stock']
                : (isset($data['minimumStock']) ? (float) $data['minimumStock'] : null),
            supplierId:         isset($data['supplier_id'])
                ? (string) $data['supplier_id']
                : (isset($data['supplierId']) ? (string) $data['supplierId'] : null),
            withdrawalQuantity: isset($data['withdrawal_quantity'])
                ? (float) $data['withdrawal_quantity']
                : (isset($data['withdrawalQuantity']) ? (float) $data['withdrawalQuantity'] : null),
            code:               isset($data['code']) ? (string) $data['code'] : null,
            name:               isset($data['name']) ? (string) $data['name'] : null,
            type:               $type !== null
                ? ($type instanceof StockTypeEnum ? $type : StockTypeEnum::from((string) $type))
                : null,
            location:           isset($data['location']) ? (string) $data['location'] : null,
            responsible:        isset($data['responsible']) ? (string) $data['responsible'] : null,
            capacity:           isset($data['capacity']) ? (float) $data['capacity'] : null,
            status:             $status !== null
                ? ($status instanceof StockStatusEnum ? $status : StockStatusEnum::from((string) $status))
                : null,
            notes:              isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    /**
     * Retorna apenas os campos presentes (não-nulos) para o updateAttributes().
     * Evita sobrescrever valores existentes com null acidentalmente.
     *
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return array_filter([
            'minimum_stock'       => $this->minimumStock,
            'supplier_id'         => $this->supplierId,
            'withdrawal_quantity' => $this->withdrawalQuantity,
            'code'                => $this->code,
            'name'                => $this->name,
            'type'                => $this->type?->value,
            'location'            => $this->location,
            'responsible'         => $this->responsible,
            'capacity'            => $this->capacity,
            'status'              => $this->status?->value,
            'notes'               => $this->notes,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
