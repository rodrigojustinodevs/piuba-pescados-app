<?php

declare(strict_types=1);

namespace App\Presentation\Requests\FinancialTransaction;

use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class FinancialTransactionUpdateRequest extends FormRequest
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
            'financial_category_id' => ['sometimes', 'uuid', 'exists:financial_categories,id'],
            'type'                  => ['sometimes', 'string', new Enum(FinancialType::class)],
            'status'                => ['sometimes', 'string', new Enum(FinancialTransactionStatus::class)],
            'amount'                => ['sometimes', 'numeric', 'min:0.01'],
            'due_date'              => ['sometimes', 'date'],
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
            'financial_category_id.uuid'   => 'The financial category ID must be a valid UUID.',
            'financial_category_id.exists' => 'The selected financial category does not exist.',

            'type.Illuminate\Validation\Rules\Enum' => 'The transaction type must be:'
                . 'revenue (Revenue), expense (Expense) or investment (Investment).',

            'status.Illuminate\Validation\Rules\Enum' => 'The status must be:'
                . 'pending, paid, overdue or cancelled.',

            'amount.numeric' => 'The amount must be numeric.',
            'amount.min'     => 'The amount must be greater than zero.',

            'due_date.date' => 'The due date must be a valid date.',

            'payment_date.date'            => 'The payment date must be a valid date.',
            'payment_date.before_or_equal' => 'The payment date cannot be a future date.',

            'description.max' => 'The description cannot exceed 500 characters.',

            'notes.string' => 'The notes must be a string.',
        ];
    }
}
