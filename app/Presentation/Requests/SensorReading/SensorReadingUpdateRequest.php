<?php

declare(strict_types=1);

namespace App\Presentation\Requests\SensorReading;

use Illuminate\Foundation\Http\FormRequest;

class SensorReadingUpdateRequest extends FormRequest
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
            'sensor_id'    => ['sometimes', 'uuid', 'exists:sensors,id'],
            'reading_date' => ['sometimes', 'date'],
            'value'        => ['sometimes', 'numeric'],
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
            'sensor_id.uuid'    => 'The sensor ID must be a valid UUID.',
            'sensor_id.exists'  => 'The sensor must exist in the sensors table.',
            'reading_date.date' => 'The reading date must be a valid date.',
            'value.numeric'     => 'The value must be a valid number.',
        ];
    }
}
