<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

final class StockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'stockId'  => 'stock_id',
            'supplyId' => 'supply_id',
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

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'stock_id'  => ['required', 'uuid', 'exists:stocks,id'],
            'supply_id' => ['required', 'uuid', 'exists:supplies,id'],
            'type'      => ['required', 'string', 'in:entry,exit,adjustment,transfer'],
            'quantity'  => ['required', 'numeric', 'gt:0'],
            'reason'    => ['nullable', 'string'],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'stock_id.required'  => 'O estoque é obrigatório.',
            'stock_id.exists'    => 'Estoque não encontrado.',
            'supply_id.required' => 'O insumo é obrigatório.',
            'supply_id.exists'   => 'Insumo não encontrado.',
            'type.required'      => 'O tipo de movimentação é obrigatório.',
            'type.in'            => 'Tipo inválido. Use: entry, exit, adjustment, transfer.',
            'quantity.required'  => 'A quantidade é obrigatória.',
            'quantity.gt'        => 'A quantidade deve ser maior que zero.',
        ];
    }
}
