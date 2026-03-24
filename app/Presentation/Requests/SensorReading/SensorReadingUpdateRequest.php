<?php

declare(strict_types=1);

namespace App\Presentation\Requests\SensorReading;

use Illuminate\Foundation\Http\FormRequest;

final class SensorReadingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        if ($this->has('measuredAt') && ! $this->has('measured_at')) {
            $this->merge(['measured_at' => $this->input('measuredAt')]);
        }
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'value'       => ['sometimes', 'numeric'],
            'unit'        => ['sometimes', 'string', 'max:50'],
            'measured_at' => ['sometimes', 'date'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ];
    }
}
