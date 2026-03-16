<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StockStoreRequest extends FormRequest
{
    #[\Override]
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('company_id') && ! $this->has('companyId')) {
            $data['companyId'] = $this->input('company_id');
        }

        if ($this->has('current_quantity') && ! $this->has('currentQuantity')) {
            $data['currentQuantity'] = $this->input('current_quantity');
        }

        if ($this->has('minimum_stock') && ! $this->has('minimumStock')) {
            $data['minimumStock'] = $this->input('minimum_stock');
        }

        if ($this->has('unit_price') && ! $this->has('unitPrice')) {
            $data['unitPrice'] = $this->input('unit_price');
        }

        if ($this->has('total_cost') && ! $this->has('totalCost')) {
            $data['totalCost'] = $this->input('total_cost');
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
            'companyId'       => ['required', 'uuid', 'exists:companies,id'],
            'currentQuantity' => ['required', 'numeric', 'min:0'],
            'unit'            => ['required', 'string', 'max:50'],
            'unitPrice'       => ['required', 'numeric', 'min:0'],
            'totalCost'       => ['nullable', 'numeric', 'min:0'],
            'minimumStock'    => ['required', 'numeric', 'min:0'],
            'supplierId'      => ['nullable', 'uuid', 'exists:suppliers,id'],
        ];
    }
}
