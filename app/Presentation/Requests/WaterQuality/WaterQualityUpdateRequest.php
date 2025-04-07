<?php

declare(strict_types=1);

namespace App\Presentation\Requests\WaterQuality;

use Illuminate\Foundation\Http\FormRequest;

class WaterQualityUpdateRequest extends FormRequest
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
            'tank_id'       => ['sometimes', 'uuid', 'exists:tanks,id'],
            'analysis_date' => ['sometimes', 'date'],
            'ph'            => ['sometimes', 'numeric', 'between:0,14'],
            'oxygen'        => ['sometimes', 'numeric', 'min:0'],
            'temperature'   => ['sometimes', 'numeric'],
            'ammonia'       => ['sometimes', 'numeric', 'min:0'],
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
            'tank_id.uuid'        => 'The tank ID must be a valid UUID.',
            'tank_id.exists'      => 'The tank ID must exist in the tanks table.',
            'analysis_date.date'  => 'The analysis date must be a valid date.',
            'ph.numeric'          => 'The pH must be a numeric value.',
            'ph.between'          => 'The pH must be between 0 and 14.',
            'oxygen.numeric'      => 'The oxygen level must be a numeric value.',
            'oxygen.min'          => 'The oxygen level must be at least 0.',
            'temperature.numeric' => 'The temperature must be a numeric value.',
            'ammonia.numeric'     => 'The ammonia level must be a numeric value.',
            'ammonia.min'         => 'The ammonia level must be at least 0.',
        ];
    }
}
