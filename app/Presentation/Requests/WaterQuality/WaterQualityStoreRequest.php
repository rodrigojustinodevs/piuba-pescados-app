<?php

declare(strict_types=1);

namespace App\Presentation\Requests\WaterQuality;

use Illuminate\Foundation\Http\FormRequest;

final class WaterQualityStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'tankId'          => 'tank_id',
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
            'tank_id'     => ['required', 'uuid', 'exists:tanks,id'],
            'measured_at' => ['required', 'date'],

            'ph'               => ['nullable', 'numeric', 'between:0,14'],
            'dissolved_oxygen' => ['nullable', 'numeric', 'min:0'],
            'temperature'      => ['nullable', 'numeric', 'between:-10,50'],
            'ammonia'          => ['nullable', 'numeric', 'min:0'],
            'salinity'         => ['nullable', 'numeric', 'min:0'],
            'turbidity'        => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'tank_id.required'     => 'The tank is required.',
            'tank_id.exists'       => 'The tank was not found.',
            'measured_at.required' => 'The measurement date is required.',
            'measured_at.date'     => 'Please enter a valid date and time.',
            'ph.between'           => 'The pH must be between 0 and 14.',
            'temperature.between'  => 'The temperature must be between -10°C and 50°C.',
            'dissolved_oxygen.min' => 'The dissolved oxygen cannot be negative.',
            'ammonia.min'          => 'The ammonia cannot be negative.',
            'salinity.min'         => 'The salinity cannot be negative.',
            'turbidity.min'        => 'The turbidity cannot be negative.',
            'notes.max'            => 'The notes must be less than 500 characters.',
        ];
    }
}
