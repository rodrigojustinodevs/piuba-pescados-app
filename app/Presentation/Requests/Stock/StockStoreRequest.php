<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

final class StockStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'companyId'          => 'company_id',
            'supplyId'           => 'supply_id',
            'supplierId'         => 'supplier_id',
            'unitPrice'          => 'unit_price',
            'totalCost'          => 'total_cost',
            'minimumStock'       => 'minimum_stock',
            'withdrawalQuantity' => 'withdrawal_quantity',
            'referenceId'        => 'reference_id',
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
            'company_id'          => ['sometimes', 'nullable', 'uuid', 'exists:companies,id'],
            'supply_id'           => ['nullable', 'uuid', 'exists:supplies,id'],
            'supplier_id'         => ['nullable', 'uuid', 'exists:suppliers,id'],
            'quantity'            => ['sometimes', 'numeric', 'min:0'],
            'unit'                => ['sometimes', 'string', 'max:20'],
            'unit_price'          => ['sometimes', 'numeric', 'min:0'],
            'total_cost'          => ['nullable', 'numeric', 'min:0'],
            'minimum_stock'       => ['nullable', 'numeric', 'min:0'],
            'withdrawal_quantity' => ['nullable', 'numeric', 'min:0'],
            'reference_id'        => ['nullable', 'uuid'],
            // Location fields
            'code'        => ['required', 'string', 'max:100'],
            'name'        => ['required', 'string', 'max:255'],
            'type'        => ['required', 'string', 'in:warehouse,cold_room,silo,storage,field'],
            'location'    => ['required', 'string', 'max:255'],
            'responsible' => ['required', 'string', 'max:255'],
            'capacity'    => ['required', 'numeric', 'min:0'],
            'status'      => ['nullable', 'string', 'in:active,inactive'],
            'notes'       => ['nullable', 'string'],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'code.required'        => 'O código do local é obrigatório.',
            'name.required'        => 'O nome do local é obrigatório.',
            'type.required'        => 'O tipo do local é obrigatório.',
            'type.in'              => 'Tipo inválido. Use: warehouse, cold_room, silo, storage, field.',
            'location.required'    => 'A localização é obrigatória.',
            'responsible.required' => 'O responsável é obrigatório.',
            'capacity.required'    => 'A capacidade é obrigatória.',
            'unit_price.min'       => 'O preço unitário não pode ser negativo.',
        ];
    }
}
