<?php

declare(strict_types=1);

namespace App\Presentation\Requests\FinancialCategory;

use App\Domain\Enums\FinancialCategoryStatus;
use App\Domain\Enums\FinancialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class FinancialCategoryStoreRequest extends FormRequest
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
            'company_id' => ['nullable', 'uuid', 'exists:companies,id'],
            'name'       => ['required', 'string', 'max:100'],
            'type'       => ['required', 'string', new Enum(FinancialType::class)],
            'status'     => ['sometimes', 'string', new Enum(FinancialCategoryStatus::class)],
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

            'name.required' => 'The financial category name is required.',
            'name.string'   => 'The financial category name must be a string.',
            'name.max'      => 'The financial category name cannot exceed 100 characters.',

            'type.required'                         => 'The financial category type is required.',
            'type.Illuminate\Validation\Rules\Enum' => 'The financial category type must be:'
                . 'revenue (Revenue), expense (Expense) or investment (Investment).',

            'status.Illuminate\Validation\Rules\Enum' => 'The status must be:'
                . 'active or inactive.',
        ];
    }
}
