<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Harvest;

use Illuminate\Foundation\Http\FormRequest;

class HarvestStoreRequest extends FormRequest
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
            'batche_id'    => ['required', 'uuid', 'exists:batches,id'],
            'total_weight' => ['required', 'numeric', 'min:0'],
            'price_per_kg' => ['required', 'numeric', 'min:0'],
            'total_income' => ['required', 'numeric', 'min:0'],
            'harvest_date' => ['required', 'date'],
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
            'batche_id.required' => 'The batche ID is required.',
            'batche_id.uuid'     => 'The batche ID must be a valid UUID.',
            'batche_id.exists'   => 'The selected batche does not exist.',

            'total_weight.required' => 'The total weight is required.',
            'total_weight.numeric'  => 'The total weight must be a number.',
            'total_weight.min'      => 'The total weight must be at least 0.',

            'price_per_kg.required' => 'The price per kg is required.',
            'price_per_kg.numeric'  => 'The price per kg must be a number.',
            'price_per_kg.min'      => 'The price per kg must be at least 0.',

            'total_income.required' => 'The total income is required.',
            'total_income.numeric'  => 'The total income must be a number.',
            'total_income.min'      => 'The total income must be at least 0.',

            'harvest_date.required' => 'The harvest date is required.',
            'harvest_date.date'     => 'The harvest date must be a valid date.',
        ];
    }
}
