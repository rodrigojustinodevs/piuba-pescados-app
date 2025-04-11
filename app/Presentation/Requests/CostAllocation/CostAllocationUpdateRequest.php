<?php

declare(strict_types=1);

namespace App\Presentation\Requests\CostAllocation;

use Illuminate\Foundation\Http\FormRequest;

class CostAllocationUpdateRequest extends FormRequest
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
            'company_id'        => ['sometimes', 'uuid', 'exists:companies,id'],
            'description'       => ['sometimes', 'string', 'max:255'],
            'amount'            => ['sometimes', 'numeric', 'min:0'],
            'registration_date' => ['sometimes', 'date', 'date_format:Y-m-d'],
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

            'description.string' => 'The description must be a string.',
            'description.max'    => 'The description may not be greater than 255 characters.',

            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min'     => 'The amount must be at least 0.',

            'registration_date.date' => 'The registration date must be a valid date.',
        ];
    }
}
