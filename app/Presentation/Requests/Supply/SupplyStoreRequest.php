<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Supply;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SupplyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'defaultUnit' => 'default_unit',
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
            'name'         => ['required', 'string', 'max:255'],
            'companyId'    => ['required', 'uuid', 'exists:companies,id'],
            'category'     => ['nullable', 'string', 'max:255'],
            'default_unit' => ['required', 'string', Rule::in(['kg', 'g', 'liter', 'ml', 'unit', 'box', 'piece'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'name.required' => 'The supply name is required.',
            'name.string'   => 'The supply name must be a string.',
            'name.max'      => 'The supply name may not be greater than 255 characters.',

            'companyId.required' => 'The company ID is required.',
            'companyId.uuid'     => 'The company ID must be a valid UUID.',
            'companyId.exists'   => 'The selected company does not exist.',

            'category.string' => 'The category must be a string.',
            'category.max'    => 'The category may not be greater than 255 characters.',

            'default_unit.required' => 'The default unit is required.',
            'default_unit.string'   => 'The default unit must be a string.',
            'default_unit.in'       => 'The default unit must be: kg, g, liter, ml, unit, box, piece.',
        ];
    }
}

