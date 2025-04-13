<?php

declare(strict_types=1);

namespace App\Presentation\Requests\FinancialTransaction;

use Illuminate\Foundation\Http\FormRequest;

class FinancialTransactionStoreRequest extends FormRequest
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
            'company_id'            => ['required', 'uuid', 'exists:companies,id'],
            'financial_category_id' => ['required', 'uuid', 'exists:financial_categories,id'],
            'type'                  => ['required', 'string', 'in:income,expense'],
            'description'           => ['nullable', 'string'],
            'amount'                => ['required', 'numeric', 'min:0'],
            'transaction_date'      => ['required', 'date'],
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
            'company_id.exists'   => 'The selected company does not exist.',

            'financial_category_id.required' => 'The financial category ID is required.',
            'financial_category_id.uuid'     => 'The financial category ID must be a valid UUID.',
            'financial_category_id.exists'   => 'The selected financial category does not exist.',

            'type.required' => 'The transaction type is required.',
            'type.string'   => 'The transaction type must be a string.',
            'type.in'       => 'The transaction type must be either "income" or "expense".',

            'description.string' => 'The description must be a string.',

            'amount.required' => 'The amount is required.',
            'amount.numeric'  => 'The amount must be a number.',
            'amount.min'      => 'The amount must be at least 0.',

            'transaction_date.required' => 'The transaction date is required.',
            'transaction_date.date'     => 'The transaction date must be a valid date.',
        ];
    }
}
