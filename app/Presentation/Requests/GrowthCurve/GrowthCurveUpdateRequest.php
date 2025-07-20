<?php

declare(strict_types=1);

namespace App\Presentation\Requests\GrowthCurve;

use Illuminate\Foundation\Http\FormRequest;

class GrowthCurveUpdateRequest extends FormRequest
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
            'batche_id'      => ['sometimes', 'uuid', 'exists:batches,id'],
            'average_weight' => ['sometimes', 'numeric', 'min:0'],
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

            'average_weight.numeric' => 'The average weight must be a number.',
            'average_weight.min'     => 'The average weight must be at least 0.',
        ];
    }
}
