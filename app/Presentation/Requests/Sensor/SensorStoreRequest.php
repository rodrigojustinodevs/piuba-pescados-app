<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sensor;

use Illuminate\Foundation\Http\FormRequest;

class SensorStoreRequest extends FormRequest
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
            'tankId'           => ['required', 'uuid', 'exists:tanks,id'],
            'sensorType'       => ['required', 'string', 'in:ph,temperature,oxygen,ammonia'],
            'installationDate' => ['required', 'date'],
            'status'           => ['required', 'string', 'in:active,inactive'],
            'notes'            => ['nullable', 'string', 'max:2000'],
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
            'tankId.required'           => 'The tank ID is required.',
            'tankId.uuid'               => 'The tank ID must be a valid UUID.',
            'tankId.exists'             => 'The tank ID must exist in the tanks table.',
            'sensorType.required'       => 'The sensor type is required.',
            'sensorType.string'         => 'The sensor type must be a string.',
            'sensorType.in'             => 'The sensor type must be one of: ph, temperature, oxygen, or ammonia.',
            'installationDate.required' => 'The installation date is required.',
            'installationDate.date'     => 'The installation date must be a valid date.',
            'status.required'           => 'The status is required.',
            'status.string'             => 'The status must be a string.',
            'status.in'                 => 'The status must be either active or inactive.',
            'notes.string'              => 'The notes must be a string.',
            'notes.max'                 => 'The notes must not exceed 2000 characters.',
        ];
    }
}
