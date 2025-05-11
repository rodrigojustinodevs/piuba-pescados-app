<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;

class SaleUpdateRequest extends FormRequest
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
            'company_id'    => ['sometimes', 'uuid', 'exists:companies,id'],
            'client_id'     => ['sometimes', 'uuid', 'exists:clients,id'],
            'batche_id'     => ['sometimes', 'uuid', 'exists:batches,id'],
            'total_weight'  => ['sometimes', 'numeric', 'min:0'],
            'price_per_kg'  => ['sometimes', 'numeric', 'min:0'],
            'total_revenue' => ['sometimes', 'numeric', 'min:0'],
            'sale_date'     => ['sometimes', 'date'],
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
            'company_id.uuid'   => 'The company ID must be a valid UUID.',
            'company_id.exists' => 'The company must exist in the companies table.',

            'client_id.uuid'   => 'The client ID must be a valid UUID.',
            'client_id.exists' => 'The client must exist in the clients table.',

            'batche_id.uuid'   => 'The batche ID must be a valid UUID.',
            'batche_id.exists' => 'The batche must exist in the batches table.',

            'total_weight.numeric' => 'The total weight must be a number.',
            'total_weight.min'     => 'The total weight must be at least 0.',

            'price_per_kg.numeric' => 'The price per kilogram must be a number.',
            'price_per_kg.min'     => 'The price per kilogram must be at least 0.',

            'total_revenue.numeric' => 'The total revenue must be a number.',
            'total_revenue.min'     => 'The total revenue must be at least 0.',

            'sale_date.date' => 'The sale date must be a valid date.',
        ];
    }
}
