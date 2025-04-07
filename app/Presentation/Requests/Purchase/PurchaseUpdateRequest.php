<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseUpdateRequest extends FormRequest
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
            'company_id'         => ['sometimes', 'uuid', 'exists:companies,id'],
            'supplier_id'        => ['sometimes', 'uuid', 'exists:suppliers,id'],
            'input_name'         => ['sometimes', 'string', 'max:255'],
            'purchased_quantity' => ['sometimes', 'numeric', 'min:0'],
            'total_price'        => ['sometimes', 'numeric', 'min:0'],
            'purchase_date'      => ['sometimes', 'date'],
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
            'company_id.uuid'            => 'The company ID must be a valid UUID.',
            'company_id.exists'          => 'The company must exist.',
            'supplier_id.uuid'           => 'The supplier ID must be a valid UUID.',
            'supplier_id.exists'         => 'The supplier must exist.',
            'input_name.string'          => 'The input name must be a string.',
            'purchased_quantity.numeric' => 'The purchased quantity must be numeric.',
            'total_price.numeric'        => 'The total price must be numeric.',
            'purchase_date.date'         => 'The purchase date must be a valid date.',
        ];
    }
}
