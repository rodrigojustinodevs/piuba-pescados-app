<?php

declare(strict_types=1);

namespace App\Presentation\Requests\FinancialTransaction;

use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class FinancialTransactionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_id'            => ['nullable', 'uuid', 'exists:companies,id'],
            'financial_category_id' => ['required', 'uuid', 'exists:financial_categories,id'],
            'type'                  => ['required', 'string', new Enum(FinancialType::class)],
            'status'                => ['nullable', 'string', new Enum(FinancialTransactionStatus::class)],
            'amount'                => ['required', 'numeric', 'min:0.01'],
            'due_date'              => ['required', 'date'],
            'payment_date'          => ['nullable', 'date', 'before_or_equal:today'],
            'description'           => ['nullable', 'string', 'max:500'],
            'notes'                 => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'company_id.uuid'   => 'The company ID must be a valid UUID.',
            'company_id.exists' => 'The selected company does not exist.',

            'financial_category_id.required' => 'The financial category is required.',
            'financial_category_id.uuid'     => 'The financial category ID must be a valid UUID.',
            'financial_category_id.exists'   => 'The selected financial category does not exist.',

            'type.required'                         => 'The transaction type is required.',
            'type.Illuminate\Validation\Rules\Enum' => 'The transaction type must be:'
                . 'revenue (Revenue), expense (Expense) or investment (Investment).',

            'status.Illuminate\Validation\Rules\Enum' => 'The status must be:'
                . 'pending, paid, overdue or cancelled.',

            'amount.required' => 'The amount is required.',
            'amount.numeric'  => 'The amount must be numeric.',
            'amount.min'      => 'The amount must be greater than zero.',

            'due_date.required' => 'The due date (maturity) is required.',
            'due_date.date'     => 'The due date must be a valid date.',

            'payment_date.date'            => 'The payment date must be a valid date.',
            'payment_date.before_or_equal' => 'The payment date cannot be a future date.',

            'description.max' => 'The description cannot exceed 500 characters.',

            'notes.string' => 'The notes must be a string.',
        ];
    }
}
