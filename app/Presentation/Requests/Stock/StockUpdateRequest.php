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

        if ($normalized) {
            $this->merge($normalized);
        }
    }

    public function rules(): array
    {
        return [
            'unit'                => ['sometimes', 'string', 'max:20'],
            'supplier_id'         => ['sometimes', 'uuid',    'exists:suppliers,id'],
            'unit_price'          => ['sometimes', 'numeric', 'min:0'],
            'minimum_stock'       => ['sometimes', 'numeric', 'min:0'],
            'withdrawal_quantity' => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_price.min'      => 'The unit price cannot be negative.',
            'minimum_stock.min'   => 'The minimum stock cannot be negative.',
            'withdrawal_quantity.min' => 'The withdrawal quantity cannot be negative.',
        ];
    }
}