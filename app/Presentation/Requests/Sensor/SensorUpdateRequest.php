<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sensor;

use Illuminate\Foundation\Http\FormRequest;

class SensorUpdateRequest extends FormRequest
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
            'tank_id'           => ['sometimes', 'uuid', 'exists:tanks,id'],
            'sensor_type'       => ['sometimes', 'string', 'in:ph,temperature,oxygen,ammonia'],
            'installation_date' => ['sometimes', 'date'],
            'status'            => ['sometimes', 'string', 'in:active,inactive'],
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
            'tank_id.uuid'           => 'The tank ID must be a valid UUID.',
            'tank_id.exists'         => 'The tank ID must exist in the tanks table.',
            'sensor_type.string'     => 'The sensor type must be a string.',
            'sensor_type.in'         => 'The sensor type must be one of: ph, temperature, oxygen, or ammonia.',
            'installation_date.date' => 'The installation date must be a valid date.',
            'status.string'          => 'The status must be a string.',
            'status.in'              => 'The status must be either active or inactive.',
        ];
    }
}
