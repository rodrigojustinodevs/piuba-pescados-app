<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Harvest;

use Illuminate\Foundation\Http\FormRequest;

class HarvestUpdateRequest extends FormRequest
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
            'batche_id'    => ['sometimes', 'uuid', 'exists:batches,id'],
            'total_weight' => ['sometimes', 'numeric', 'min:0'],
            'price_per_kg' => ['sometimes', 'numeric', 'min:0'],
            'total_income' => ['sometimes', 'numeric', 'min:0'],
            'harvest_date' => ['sometimes', 'date'],
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
            'batche_id.uuid'   => 'The batche ID must be a valid UUID.',
            'batche_id.exists' => 'The selected batche does not exist.',

            'total_weight.numeric' => 'The total weight must be a number.',
            'total_weight.min'     => 'The total weight must be at least 0.',

            'price_per_kg.numeric' => 'The price per kg must be a number.',
            'price_per_kg.min'     => 'The price per kg must be at least 0.',

            'total_income.numeric' => 'The total income must be a number.',
            'total_income.min'     => 'The total income must be at least 0.',

            'harvest_date.date' => 'The harvest date must be a valid date.',
        ];
    }
}
