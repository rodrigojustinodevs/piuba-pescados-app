<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseStoreRequest extends FormRequest
{
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
            'company_id'    => ['required', 'uuid', 'exists:companies,id'],
            'supplier_id'   => ['required', 'uuid', 'exists:suppliers,id'],
            'input_name'    => ['required', 'string', 'max:255'],
            'quantity'      => ['required', 'numeric', 'min:0'],
            'total_price'   => ['required', 'numeric', 'min:0'],
            'purchase_date' => ['required', 'date'],
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
            'company_id.required'    => 'The company ID is required.',
            'company_id.uuid'        => 'The company ID must be a valid UUID.',
            'company_id.exists'      => 'The selected company does not exist.',
            'supplier_id.required'   => 'The supplier ID is required.',
            'supplier_id.uuid'       => 'The supplier ID must be a valid UUID.',
            'supplier_id.exists'     => 'The selected supplier does not exist.',
            'input_name.required'    => 'The input name is required.',
            'input_name.string'      => 'The input name must be a string.',
            'input_name.max'         => 'The input name may not be greater than 255 characters.',
            'quantity.required'      => 'The quantity is required.',
            'quantity.numeric'       => 'The quantity must be a number.',
            'quantity.min'           => 'The quantity must be at least 0.',
            'total_price.required'   => 'The total price is required.',
            'total_price.numeric'    => 'The total price must be a number.',
            'total_price.min'        => 'The total price must be at least 0.',
            'purchase_date.required' => 'The purchase date is required.',
            'purchase_date.date'     => 'The purchase date must be a valid date.',
        ];
    }
}
