<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'sourceStockId'      => 'source_stock_id',
            'destinationStockId' => 'destination_stock_id',
            'supplyId'           => 'supply_id',
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
            'source_stock_id'      => ['required', 'uuid', 'exists:stocks,id'],
            'destination_stock_id' => ['required', 'uuid', 'exists:stocks,id'],
            'supply_id'            => ['required', 'uuid', 'exists:supplies,id'],
            'quantity'             => ['required', 'numeric', 'gt:0'],
            'reason'               => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($this->input('source_stock_id') === $this->input('destination_stock_id')) {
                $v->errors()->add('destination_stock_id', 'O estoque de destino deve ser diferente do de origem.');
            }
        });
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'source_stock_id.required'      => 'O estoque de origem é obrigatório.',
            'source_stock_id.exists'        => 'Estoque de origem não encontrado.',
            'destination_stock_id.required' => 'O estoque de destino é obrigatório.',
            'destination_stock_id.exists'   => 'Estoque de destino não encontrado.',
            'supply_id.required'            => 'O insumo é obrigatório.',
            'supply_id.exists'              => 'Insumo não encontrado.',
            'quantity.required'             => 'A quantidade é obrigatória.',
            'quantity.gt'                   => 'A quantidade deve ser maior que zero.',
        ];
    }
}
