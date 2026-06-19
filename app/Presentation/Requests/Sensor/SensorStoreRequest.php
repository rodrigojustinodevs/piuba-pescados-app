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

        if (! $this->has('serialNumber') && $this->has('serial_number')) {
            $merge['serialNumber'] = $this->input('serial_number');
        }

        if (! $this->has('lastReading') && $this->has('last_reading')) {
            $merge['lastReading'] = $this->input('last_reading');
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
            'companyId'        => ['sometimes', 'uuid', 'exists:companies,id'],
            'sensorType'       => ['required', 'string', 'in:ph,temperature,oxygen,ammonia'],
            'name'             => ['required', 'string', 'max:150'],
            'serialNumber'     => ['required', 'string', 'max:100'],
            'battery'          => ['required', 'integer', 'min:0', 'max:100'],
            'unit'             => ['required', 'string', 'max:20'],
            'lastReading'      => ['required', 'numeric'],
            'installationDate' => ['required', 'date'],
            'status'           => ['required', 'string', 'in:active,inactive,online,offline,ativo,inativo'],
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
            'companyId.uuid'            => 'The company ID must be a valid UUID.',
            'companyId.exists'          => 'The company ID must exist in the companies table.',
            'sensorType.required'       => 'The sensor type is required.',
            'sensorType.string'         => 'The sensor type must be a string.',
            'sensorType.in'             => 'The sensor type must be one of: ph, temperature, oxygen, or ammonia.',
            'name.required'             => 'The sensor name is required.',
            'name.string'               => 'The sensor name must be a string.',
            'name.max'                  => 'The sensor name must not exceed 150 characters.',
            'serialNumber.required'     => 'The serial number is required.',
            'serialNumber.string'       => 'The serial number must be a string.',
            'serialNumber.max'          => 'The serial number must not exceed 100 characters.',
            'battery.required'          => 'The battery level is required.',
            'battery.integer'           => 'The battery level must be an integer.',
            'battery.min'               => 'The battery level must be at least 0.',
            'battery.max'               => 'The battery level must be at most 100.',
            'unit.required'             => 'The unit is required.',
            'unit.string'               => 'The unit must be a string.',
            'unit.max'                  => 'The unit must not exceed 20 characters.',
            'lastReading.required'      => 'The last reading is required.',
            'lastReading.numeric'       => 'The last reading must be numeric.',
            'installationDate.required' => 'The installation date is required.',
            'installationDate.date'     => 'The installation date must be a valid date.',
            'status.required'           => 'The status is required.',
            'status.string'             => 'The status must be a string.',
            'status.in'                 => 'The status must be one of: online,'
                . ' offline, active, inactive, ativo or inativo.',
            'notes.string'              => 'The notes must be a string.',
            'notes.max'                 => 'The notes must not exceed 2000 characters.',
        ];
    }
}
