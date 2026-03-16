<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseUpdateRequest extends FormRequest
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

        if ($this->has('purchased_quantity') && ! $this->has('quantity')) {
            $data['quantity'] = $this->input('purchased_quantity');
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
            'companyId'    => ['sometimes', 'uuid', 'exists:companies,id'],
            'supplierId'   => ['sometimes', 'uuid', 'exists:suppliers,id'],
            'stockingId'   => ['sometimes', 'nullable', 'uuid', 'exists:stockings,id'],
            'inputName'    => ['sometimes', 'string', 'max:255'],
            'quantity'     => ['sometimes', 'numeric', 'min:0'],
            'totalPrice'   => ['sometimes', 'numeric', 'min:0'],
            'purchaseDate' => ['sometimes', 'date'],
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
            'companyId.uuid'    => 'The company ID must be a valid UUID.',
            'companyId.exists'  => 'The company must exist.',
            'supplierId.uuid'   => 'The supplier ID must be a valid UUID.',
            'supplierId.exists' => 'The supplier must exist.',
            'stockingId.uuid'   => 'The stocking ID must be a valid UUID.',
            'stockingId.exists' => 'The selected stocking does not exist.',
            'inputName.string'  => 'The input name must be a string.',
            'quantity.numeric'  => 'The quantity must be numeric.',
            'totalPrice.numeric'=> 'The total price must be numeric.',
            'purchaseDate.date' => 'The purchase date must be a valid date.',
        ];
    }
}
