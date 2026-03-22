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

        if ($normalized) {
            $this->merge($normalized);
        }
    }

    public function rules(): array
    {
        return [
            'company_id'          => ['sometimes', 'nullable', 'uuid', 'exists:companies,id'],
            'supply_id'           => ['required', 'uuid', 'exists:supplies,id'],
            'supplier_id'         => ['nullable', 'uuid', 'exists:suppliers,id'],
            'quantity'            => ['required', 'numeric', 'gt:0'],
            'unit'                => ['required', 'string', 'max:20'],
            'unit_price'          => ['required', 'numeric', 'min:0'],
            'total_cost'          => ['nullable', 'numeric', 'min:0'],
            'minimum_stock'       => ['nullable', 'numeric', 'min:0'],
            'withdrawal_quantity' => ['nullable', 'numeric', 'min:0'],
            'reference_id'        => ['nullable', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'supply_id.required'  => 'The supply is required.',
            'supply_id.exists'    => 'Supply not found.',
            'quantity.required'   => 'The quantity is required.',
            'quantity.gt'         => 'The quantity must be greater than zero.',
            'unit.required'       => 'The unit is required.',
            'unit_price.required' => 'The unit price is required.',
            'unit_price.min'      => 'The unit price cannot be negative.',
        ];
    }
}