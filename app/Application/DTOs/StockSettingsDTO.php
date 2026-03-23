<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class StockSettingsDTO
{
    public function __construct(
        public ?float $minimumStock = null,
        public ?string $supplierId = null,
        public ?float $withdrawalQuantity = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
     */
    public static function fromArray(array $data): self
    {
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
        ], static fn (string | float | null $v): bool => $v !== null);
    }
}
