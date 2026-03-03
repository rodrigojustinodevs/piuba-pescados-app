<?php

declare(strict_types=1);

namespace App\Presentation\Requests\GrowthCurve;

use Illuminate\Foundation\Http\FormRequest;

class GrowthCurveStoreRequest extends FormRequest
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
            'batch_id'       => ['required', 'uuid', 'exists:batches,id'],
            'average_weight' => ['required', 'numeric', 'min:0'],
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
            'batch_id.required' => 'The batch ID is required.',
            'batch_id.uuid'     => 'The batch ID must be a valid UUID.',
            'batch_id.exists'   => 'The selected batch does not exist.',

            'average_weight.required' => 'The average weight is required.',
            'average_weight.numeric'  => 'The average weight must be a number.',
            'average_weight.min'      => 'The average weight must be at least 0.',
        ];
    }
}
