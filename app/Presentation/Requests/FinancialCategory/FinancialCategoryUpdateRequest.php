<?php

declare(strict_types=1);

namespace App\Presentation\Requests\FinancialCategory;

use Illuminate\Foundation\Http\FormRequest;

class FinancialCategoryUpdateRequest extends FormRequest
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
            'company_id' => ['sometimes', 'uuid', 'exists:companies,id'],
            'name'       => ['sometimes', 'string', 'max:100'],
            'type'       => ['sometimes', 'string', 'in:income,expense'],
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
            'company_id.exists' => 'The selected company does not exist.',

            'name.string' => 'The financial category name must be a string.',
            'name.max'    => 'The financial category name may not be greater than 100 characters.',

            'type.string' => 'The category type must be a string.',
            'type.in'     => 'The category type must be either "income" or "expense".',
        ];
    }
}
