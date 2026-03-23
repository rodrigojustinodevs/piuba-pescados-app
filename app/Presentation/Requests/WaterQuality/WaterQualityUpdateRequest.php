<?php

declare(strict_types=1);

namespace App\Presentation\Requests\WaterQuality;

use Illuminate\Foundation\Http\FormRequest;

final class WaterQualityUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'measuredAt'      => 'measured_at',
            'dissolvedOxygen' => 'dissolved_oxygen',
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
            // tank_id não é atualizável — uma medição pertence a um tanque imutável
            'measured_at'      => ['sometimes', 'date'],
            'ph'               => ['sometimes', 'nullable', 'numeric', 'between:0,14'],
            'dissolved_oxygen' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'temperature'      => ['sometimes', 'nullable', 'numeric', 'between:-10,50'],
            'ammonia'          => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'salinity'         => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'turbidity'        => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'notes'            => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'measured_at.date'     => 'Informe uma data/hora válida.',
            'ph.between'           => 'O pH deve estar entre 0 e 14.',
            'temperature.between'  => 'A temperatura deve estar entre -10°C e 50°C.',
            'dissolved_oxygen.min' => 'O oxigênio dissolvido não pode ser negativo.',
            'ammonia.min'          => 'A amônia não pode ser negativa.',
            'salinity.min'         => 'A salinidade não pode ser negativa.',
            'turbidity.min'        => 'A turbidez não pode ser negativa.',
        ];
    }
}
