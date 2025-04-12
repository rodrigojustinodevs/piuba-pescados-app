<?php

declare(strict_types=1);

namespace App\Presentation\Requests\CostAllocation;

use Illuminate\Foundation\Http\FormRequest;

class CostAllocationStoreRequest extends FormRequest
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
            'company_id'        => ['bail', 'required', 'uuid', 'exists:companies,id'],
            'description'       => ['bail', 'required', 'string', 'max:255'],
            'amount'            => ['bail', 'required', 'numeric', 'min:0'],
            'registration_date' => ['bail', 'required', 'date', 'date_format:Y-m-d'],
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
            'company_id.required' => 'The :attribute is required.',
            'company_id.uuid'     => 'The :attribute must be a valid UUID.',
            'company_id.exists'   => 'The selected company does not exist.',

            'description.required' => 'The :attribute is required.',
            'description.string'   => 'The :attribute must be a string.',
            'description.max'      => 'The :attribute may not be greater than :max characters.',

            'amount.required' => 'The :attribute is required.',
            'amount.numeric'  => 'The :attribute must be a number.',
            'amount.min'      => 'The :attribute must be at least 0.',

            'registration_date.required'    => 'The :attribute is required.',
            'registration_date.date'        => 'The :attribute must be a valid date.',
            'registration_date.date_format' => 'The :attribute must be in the format YYYY-MM-DD.',
        ];
    }

    /**
     * Get custom attribute names for validation rules.
     *
     * @return array<string, string>
     */
    #[\Override]
    public function attributes(): array
    {
        return [
            'company_id'        => 'company ID',
            'description'       => 'description',
            'amount'            => 'amount',
            'registration_date' => 'registration date',
        ];
    }
}
