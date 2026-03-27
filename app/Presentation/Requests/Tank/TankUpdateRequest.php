<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Tank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TankUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Contracts\Validation\Rule|\Illuminate\Validation\Rules\In|string>|string>
     */
    public function rules(): array
    {
        return [
            'companyId'      => ['sometimes', 'uuid', 'exists:companies,id'],
            'tankTypeId'     => ['sometimes', 'uuid', 'exists:tank_types,id'],
            'name'           => ['sometimes', 'string', 'max:255'],
            'capacityLiters' => ['sometimes', 'integer', 'min:1'],
            'location'       => ['sometimes', 'string'],
            'status'         => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'companyId.uuid'         => 'The company ID must be a valid UUID.',
            'companyId.exists'       => 'The selected company does not exist.',
            'tankTypeId.uuid'        => 'The tank type ID must be a valid UUID.',
            'tankTypeId.exists'      => 'The selected tank type does not exist.',
            'name.string'            => 'The name must be a string.',
            'name.max'               => 'The name must not exceed 255 characters.',
            'capacityLiters.integer' => 'The capacity liters must be an integer.',
            'capacityLiters.min'     => 'The capacity liters must be at least 1.',
            'location.string'        => 'The location must be a string.',
            'status.in'              => 'The status must be either active or inactive.',
        ];
    }
}
