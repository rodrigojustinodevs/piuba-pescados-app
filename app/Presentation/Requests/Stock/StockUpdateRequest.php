<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Stock;

use App\Domain\Models\Stock;
use Illuminate\Foundation\Http\FormRequest;

class StockUpdateRequest extends FormRequest
{
    #[\Override]
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('current_quantity') && ! $this->has('currentQuantity')) {
            $data['currentQuantity'] = $this->input('current_quantity');
        }

        if ($this->has('minimum_stock') && ! $this->has('minimumStock')) {
            $data['minimumStock'] = $this->input('minimum_stock');
        }

        if ($this->has('withdrawal_quantity') && ! $this->has('withdrawalQuantity')) {
            $data['withdrawalQuantity'] = $this->input('withdrawal_quantity');
        }

        if ($this->has('unit_price') && ! $this->has('unitPrice')) {
            $data['unitPrice'] = $this->input('unit_price');
        }

        if ($this->has('supplier_id') && ! $this->has('supplierId')) {
            $data['supplierId'] = $this->input('supplier_id');
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'currentQuantity'    => ['sometimes', 'numeric', 'min:0'],
            'unit'               => ['sometimes', 'string', 'min:1', 'max:50'],
            'unitPrice'          => ['sometimes', 'numeric', 'min:0'],
            'minimumStock'       => ['sometimes', 'numeric', 'min:0'],
            'supplierId'         => [
                'nullable',
                'uuid',
                'exists:suppliers,id',
            ],
        ];
    }
}
