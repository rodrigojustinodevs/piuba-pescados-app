<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Enums\SaleStatus;
use App\Domain\Models\Sale;

/**
 * Value Object imutável que carrega apenas os campos presentes no payload do update.
 *
 * Responsabilidades:
 *  - Normalizar tipos (float, bool, enum → valor canônico)
 *  - Distinguir "campo ausente" de "campo enviado com valor null"
 *  - Nunca sobrescrever o estado atual da venda com valores não enviados
 *
 * Não contém regras de negócio — é apenas um portador de dados validados.
 */
final class SaleAttributes
{
    private function __construct(
        /** @var array<string, mixed> */
        private readonly array $fields,
    ) {}

    /**
     * Constrói o Value Object a partir do array validado da Request.
     * Somente as chaves presentes em $data entram no objeto.
     *
     * @param array<string, mixed> $data Array já normalizado (snake_case) e validado.
     */
    public static function fromValidatedData(array $data): self
    {
        $fields = [];

        if (array_key_exists('total_weight', $data)) {
            $fields['total_weight'] = (float) $data['total_weight'];
        }

        if (array_key_exists('price_per_kg', $data)) {
            $fields['price_per_kg'] = (float) $data['price_per_kg'];
        }

        if (array_key_exists('sale_date', $data)) {
            $fields['sale_date'] = $data['sale_date'];
        }

        if (array_key_exists('status', $data)) {
            $fields['status'] = SaleStatus::from((string) $data['status'])->value;
        }

        if (array_key_exists('notes', $data)) {
            $fields['notes'] = $data['notes'];
        }

        if (array_key_exists('is_total_harvest', $data)) {
            $fields['is_total_harvest'] = (bool) $data['is_total_harvest'];
        }

        return new self($fields);
    }

    public function isEmpty(): bool
    {
        return $this->fields === [];
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->fields);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->fields;
    }

    /**
     * Retorna o peso efetivo: o novo (se enviado) ou o atual da venda.
     */
    public function resolveWeight(Sale $sale): float
    {
        return isset($this->fields['total_weight'])
            ? (float) $this->fields['total_weight']
            : (float) $sale->total_weight;
    }

    /**
     * Retorna o preço efetivo: o novo (se enviado) ou o atual da venda.
     */
    public function resolvePrice(Sale $sale): float
    {
        return isset($this->fields['price_per_kg'])
            ? (float) $this->fields['price_per_kg']
            : (float) $sale->price_per_kg;
    }

    /**
     * Retorna se a venda continua/passa a ser despesca total.
     */
    public function resolveIsTotalHarvest(Sale $sale): bool
    {
        return isset($this->fields['is_total_harvest'])
            ? (bool) $this->fields['is_total_harvest']
            : (bool) $sale->is_total_harvest;
    }

    /**
     * Retorna uma cópia imutável com a receita total calculada inserida.
     */
    public function withRevenue(float $revenue): self
    {
        $clone         = clone $this;
        $fields        = $this->fields;
        $fields['total_revenue'] = $revenue;

        return new self($fields);
    }
}