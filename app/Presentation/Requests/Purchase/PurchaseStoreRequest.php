<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseStoreRequest extends FormRequest
{
    #[\Override]
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('company_id') && ! $this->has('companyId')) {
            $data['companyId'] = $this->input('company_id');
        }

        if ($this->has('supplier_id') && ! $this->has('supplierId')) {
            $data['supplierId'] = $this->input('supplier_id');
        }

        if ($this->has('stocking_id') && ! $this->has('stockingId')) {
            $data['stockingId'] = $this->input('stocking_id');
        }

        if ($this->has('input_name') && ! $this->has('inputName')) {
            $data['inputName'] = $this->input('input_name');
        }

        if ($this->has('total_price') && ! $this->has('totalPrice')) {
            $data['totalPrice'] = $this->input('total_price');
        }

        if ($this->has('purchase_date') && ! $this->has('purchaseDate')) {
            $data['purchaseDate'] = $this->input('purchase_date');
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
            'companyId'    => ['required', 'uuid', 'exists:companies,id'],
            'supplierId'   => ['required', 'uuid', 'exists:suppliers,id'],
            'stockingId'   => ['nullable', 'uuid', 'exists:stockings,id'],
            'inputName'    => ['required', 'string', 'max:255'],
            'quantity'     => ['required', 'numeric', 'min:0'],
            'totalPrice'   => ['required', 'numeric', 'min:0'],
            'purchaseDate' => ['required', 'date'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'companyId.required'    => 'The company ID is required.',
            'companyId.uuid'        => 'The company ID must be a valid UUID.',
            'companyId.exists'      => 'The selected company does not exist.',
            'supplierId.required'   => 'The supplier ID is required.',
            'supplierId.uuid'       => 'The supplier ID must be a valid UUID.',
            'supplierId.exists'     => 'The selected supplier does not exist.',
            'stockingId.uuid'       => 'The stocking ID must be a valid UUID.',
            'stockingId.exists'     => 'The selected stocking does not exist.',
            'inputName.required'    => 'The input name is required.',
            'inputName.string'      => 'The input name must be a string.',
            'inputName.max'         => 'The input name may not be greater than 255 characters.',
            'quantity.required'      => 'The quantity is required.',
            'quantity.numeric'       => 'The quantity must be a number.',
            'quantity.min'           => 'The quantity must be at least 0.',
            'totalPrice.required'   => 'The total price is required.',
            'totalPrice.numeric'    => 'The total price must be a number.',
            'totalPrice.min'        => 'The total price must be at least 0.',
            'purchaseDate.required' => 'The purchase date is required.',
            'purchaseDate.date'     => 'The purchase date must be a valid date.',
        ];
    }
}
