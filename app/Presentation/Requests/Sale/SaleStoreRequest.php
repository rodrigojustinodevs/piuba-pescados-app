<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;

class SaleStoreRequest extends FormRequest
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
            'client_id'     => ['required', 'uuid', 'exists:clients,id'],
            'batche_id'     => ['required', 'uuid', 'exists:batches,id'],
            'total_weight'  => ['required', 'numeric', 'min:0'],
            'price_per_kg'  => ['required', 'numeric', 'min:0'],
            'total_revenue' => ['required', 'numeric', 'min:0'],
            'sale_date'     => ['required', 'date'],
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
            'company_id.required' => 'The company ID is required.',
            'company_id.uuid'     => 'The company ID must be a valid UUID.',
            'company_id.exists'   => 'The company must exist in the companies table.',

            'client_id.required' => 'The client ID is required.',
            'client_id.uuid'     => 'The client ID must be a valid UUID.',
            'client_id.exists'   => 'The client must exist in the clients table.',

            'batche_id.required' => 'The batche ID is required.',
            'batche_id.uuid'     => 'The batche ID must be a valid UUID.',
            'batche_id.exists'   => 'The batche must exist in the batches table.',

            'total_weight.required' => 'The total weight is required.',
            'total_weight.numeric'  => 'The total weight must be a number.',
            'total_weight.min'      => 'The total weight must be at least 0.',

            'price_per_kg.required' => 'The price per kilogram is required.',
            'price_per_kg.numeric'  => 'The price per kilogram must be a number.',
            'price_per_kg.min'      => 'The price per kilogram must be at least 0.',

            'total_revenue.required' => 'The total revenue is required.',
            'total_revenue.numeric'  => 'The total revenue must be a number.',
            'total_revenue.min'      => 'The total revenue must be at least 0.',

            'sale_date.required' => 'The sale date is required.',
            'sale_date.date'     => 'The sale date must be a valid date.',
        ];
    }
}
