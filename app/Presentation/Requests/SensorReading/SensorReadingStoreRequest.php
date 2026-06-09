<?php

declare(strict_types=1);

namespace App\Presentation\Requests\SensorReading;

use Illuminate\Foundation\Http\FormRequest;

final class SensorReadingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'sensorId'   => 'sensor_id',
            'companyId'  => 'company_id',
            'measuredAt' => 'measured_at',
        ];

        $normalized = [];

        foreach ($map as $camel => $snake) {
            if ($this->has($camel) && ! $this->has($snake)) {
                $normalized[$snake] = $this->input($camel);
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'sensor_id'   => ['required', 'uuid', 'exists:sensors,id'],
            'company_id'  => ['sometimes', 'uuid', 'exists:companies,id'],
            'value'       => ['required', 'numeric'],
            'unit'        => ['required', 'string', 'max:50'],
            'measured_at' => ['required', 'date'],
            'type'        => ['sometimes', 'string', 'in:automatic,manual'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'sensor_id.required'   => 'O sensor é obrigatório.',
            'sensor_id.uuid'       => 'O ID do sensor deve ser um UUID válido.',
            'sensor_id.exists'     => 'Sensor não encontrado.',
            'company_id.uuid'      => 'O ID da empresa deve ser um UUID válido.',
            'company_id.exists'    => 'Empresa não encontrada.',
            'value.required'       => 'O valor da leitura é obrigatório.',
            'value.numeric'        => 'O valor deve ser numérico.',
            'unit.required'        => 'A unidade de medida é obrigatória.',
            'measured_at.required' => 'A data/hora da leitura é obrigatória.',
            'measured_at.date'     => 'Informe uma data/hora válida.',
            'type.in'              => 'O tipo deve ser automatic ou manual.',
        ];
    }
}
