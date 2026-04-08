<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida e normaliza o payload de atualização de uma venda.
 *
 * Todos os campos são opcionais (PATCH semântico em endpoint PUT):
 * campos ausentes não sobrescrevem o estado atual da venda.
 *
 * A normalização camelCase → snake_case é feita em prepareForValidation()
 * somente quando a chave camelCase está presente e a snake_case ausente,
 * evitando injeção silenciosa de null para campos não enviados.
 */
final class SaleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'totalWeight'    => 'total_weight',
            'pricePerKg'     => 'price_per_kg',
            'saleDate'       => 'sale_date',
            'isTotalHarvest' => 'is_total_harvest',
        ];

        $normalized = [];

        foreach ($map as $camel => $snake) {
            if ($this->has($camel) && ! $this->has($snake)) {
                $normalized[$snake] = $this->input($camel);
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'total_weight'     => ['sometimes', 'numeric', 'min:0.001'],
            'price_per_kg'     => ['sometimes', 'numeric', 'min:0'],
            'sale_date'        => ['sometimes', 'date'],
            'status'           => ['sometimes', Rule::enum(SaleStatus::class)],
            'notes'            => ['sometimes', 'nullable', 'string', 'max:1000'],
            'is_total_harvest' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    #[\Override]
    public function messages(): array
    {
        return [
            'total_weight.min' => 'The total weight must be greater than zero.',
            'price_per_kg.min' => 'The price per kg must be greater than zero.',
            'sale_date.date'   => 'The sale date must be a valid date.',
            'status.enum'      => 'The status must be: pending, confirmed or cancelled.',
            'notes.max'        => 'The notes must not exceed 1000 characters.',
        ];
    }
}
