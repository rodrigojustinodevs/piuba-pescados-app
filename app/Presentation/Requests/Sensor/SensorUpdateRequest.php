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

    #[\Override]
    protected function prepareForValidation(): void
    {
        $merge = [];

        if (! $this->has('tankId') && $this->has('tank_id')) {
            $merge['tankId'] = $this->input('tank_id');
        }

        if (! $this->has('sensorType') && $this->has('sensor_type')) {
            $merge['sensorType'] = $this->input('sensor_type');
        }

        if (! $this->has('installationDate') && $this->has('installation_date')) {
            $merge['installationDate'] = $this->input('installation_date');
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'tankId'           => ['sometimes', 'uuid', 'exists:tanks,id'],
            'sensorType'       => ['sometimes', 'string', 'in:ph,temperature,oxygen,ammonia'],
            'installationDate' => ['sometimes', 'date'],
            'notes'            => ['sometimes', 'string', 'max:2000'],
            'status'           => ['sometimes', 'string', 'in:active,inactive,maintenance'],
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
            'tankId.uuid'           => 'The tank ID must be a valid UUID.',
            'tankId.exists'         => 'The tank ID must exist in the tanks table.',
            'sensorType.string'     => 'The sensor type must be a string.',
            'sensorType.in'         => 'The sensor type must be one of: ph, temperature, oxygen, or ammonia.',
            'installationDate.date' => 'The installation date must be a valid date.',
            'notes.string'          => 'The notes must be a string.',
            'notes.max'             => 'The notes must not exceed 2000 characters.',
            'status.string'         => 'The status must be a string.',
            'status.in'             => 'The status must be either active, inactive or maintenance.',
        ];
    }
}
