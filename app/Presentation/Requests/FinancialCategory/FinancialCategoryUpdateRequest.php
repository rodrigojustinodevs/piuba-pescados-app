<?php

declare(strict_types=1);

namespace App\Presentation\Requests\FinancialCategory;

use App\Domain\Enums\FinancialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class FinancialCategoryUpdateRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:100'],
            'type' => ['sometimes', 'string', new Enum(FinancialType::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'name.string' => 'The financial category name must be a string.',
            'name.max'    => 'The financial category name cannot exceed 100 characters.',

            'type.Illuminate\Validation\Rules\Enum' => 'The financial category type must be:  '
                . 'revenue (Revenue), expense (Expense) or investment (Investment).',
        ];
    }
}
