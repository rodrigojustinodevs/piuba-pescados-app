<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

final class StockUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'unitPrice'          => 'unit_price',
            'minimumStock'       => 'minimum_stock',
            'supplierId'         => 'supplier_id',
            'withdrawalQuantity' => 'withdrawal_quantity',
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
            'unit'                => ['sometimes', 'string', 'max:20'],
            'supplier_id'         => ['sometimes', 'uuid', 'exists:suppliers,id'],
            'unit_price'          => ['sometimes', 'numeric', 'min:0'],
            'minimum_stock'       => ['sometimes', 'numeric', 'min:0'],
            'withdrawal_quantity' => ['sometimes', 'numeric', 'min:0'],
            // Location fields
            'code'        => ['sometimes', 'string', 'max:100'],
            'name'        => ['sometimes', 'string', 'max:255'],
            'type'        => ['sometimes', 'string', 'in:warehouse,cold_room,silo,storage,field'],
            'location'    => ['sometimes', 'string', 'max:255'],
            'responsible' => ['sometimes', 'string', 'max:255'],
            'capacity'    => ['sometimes', 'numeric', 'min:0'],
            'status'      => ['sometimes', 'string', 'in:active,inactive'],
            'notes'       => ['nullable', 'string'],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'unit_price.min'          => 'O preço unitário não pode ser negativo.',
            'minimum_stock.min'       => 'O estoque mínimo não pode ser negativo.',
            'withdrawal_quantity.min' => 'A quantidade de retirada não pode ser negativa.',
            'type.in'                 => 'Tipo inválido. Use: warehouse, cold_room, silo, storage, field.',
            'status.in'               => 'Status inválido. Use: active, inactive.',
        ];
    }
}
