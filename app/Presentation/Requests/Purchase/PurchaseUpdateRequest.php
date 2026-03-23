<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'supplierId'   => ['required', 'uuid'],
            'purchaseDate' => ['required', 'date'],

            'status' => [
                'nullable',
                'string',
                Rule::in(['draft', 'approved']), // ❗ não permitir 'received'
            ],

            'items' => ['required', 'array', 'min:1'],

            'items.*.id' => ['nullable', 'uuid'],

            'items.*.supplyId' => ['required', 'uuid'],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.unit' => [
                'required',
                'string',
                'max:20',
            ],

            'items.*.unitPrice' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'supplierId.required' => 'Fornecedor é obrigatório.',
            'supplierId.uuid'     => 'Fornecedor inválido.',

            'purchaseDate.required' => 'Data da compra é obrigatória.',
            'purchaseDate.date'     => 'Data inválida.',

            'status.in' => 'Status inválido.',

            'items.required' => 'Itens são obrigatórios.',
            'items.array'    => 'Itens deve ser um array.',
            'items.min'      => 'A compra deve possuir ao menos 1 item.',

            'items.*.id.uuid' => 'ID do item inválido.',

            'items.*.supplyId.required' => 'Insumo é obrigatório.',
            'items.*.supplyId.uuid'     => 'Insumo inválido.',

            'items.*.quantity.required' => 'Quantidade é obrigatória.',
            'items.*.quantity.numeric'  => 'Quantidade deve ser numérica.',
            'items.*.quantity.gt'       => 'Quantidade deve ser maior que zero.',

            'items.*.unit.required' => 'Unidade é obrigatória.',
            'items.*.unit.max'      => 'Unidade muito longa.',

            'items.*.unitPrice.required' => 'Preço unitário é obrigatório.',
            'items.*.unitPrice.numeric'  => 'Preço deve ser numérico.',
            'items.*.unitPrice.gt'       => 'Preço deve ser maior que zero.',
        ];
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        if ($this->has('items')) {
            $this->merge([
                'items' => array_map(fn (array $item): array => [
                    'id'        => $item['id'] ?? null,
                    'supplyId'  => $item['supplyId'] ?? null,
                    'quantity'  => isset($item['quantity']) ? (float) $item['quantity'] : null,
                    'unit'      => $item['unit'] ?? null,
                    'unitPrice' => isset($item['unitPrice']) ? (float) $item['unitPrice'] : null,
                ], $this->input('items')),
            ]);
        }
    }
}
