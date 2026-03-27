<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Tank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TankUpdateRequest extends FormRequest
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
     * @return array<string, list<\Illuminate\Validation\Rules\In|string>
     * |array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
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
            'companyId.uuid'          => 'The company ID must be a valid UUID.',
            'companyId.exists'        => 'The selected company does not exist.',
            'tankTypeId.uuid'         => 'The tank type ID must be a valid UUID.',
            'tankTypeId.exists'       => 'The selected tank type does not exist.',
            'name.string'             => 'The name must be a string.',
            'name.max'                => 'The name must not exceed 255 characters.',
            'status.in'               => 'The status must be either active or inactive.',
            'capacityLiters.required' => 'The capacity liters is required.',
            'capacityLiters.integer'  => 'The capacity liters must be an integer.',
            'capacityLiters.min'      => 'The capacity liters must be at least 1.',
            'location.required'       => 'The location is required.',
            'location.string'         => 'The location must be a string.',
            'status.required'         => 'The status is required.',
        ];
    }
}
