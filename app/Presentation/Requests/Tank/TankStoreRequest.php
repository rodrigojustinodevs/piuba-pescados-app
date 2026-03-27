<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Tank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TankStoreRequest extends FormRequest
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
     * Usa camelCase para não expor estrutura do banco de dados
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Contracts\Validation\Rule|\Illuminate\Validation\Rules\In|string>|string>
     */
    public function rules(): array
    {
        return [
            'companyId'      => ['required', 'uuid', 'exists:companies,id'],
            'tankTypeId'     => ['required', 'uuid', 'exists:tank_types,id'],
            'name'           => ['required', 'string', 'max:255'],
            'capacityLiters' => ['required', 'integer', 'min:1'],
            'location'       => ['required', 'string'],
            'status'         => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'companyId.required'      => 'The company ID is required.',
            'companyId.uuid'          => 'The company ID must be a valid UUID.',
            'companyId.exists'        => 'The selected company does not exist.',
            'tankTypeId.required'     => 'The tank type ID is required.',
            'tankTypeId.uuid'         => 'The tank type ID must be a valid UUID.',
            'tankTypeId.exists'       => 'The selected tank type does not exist.',
            'name.required'           => 'The name is required.',
            'name.string'             => 'The name must be a string.',
            'name.max'                => 'The name must not exceed 255 characters.',
            'capacityLiters.required' => 'The capacity liters is required.',
            'capacityLiters.integer'  => 'The capacity liters must be an integer.',
            'capacityLiters.min'      => 'The capacity liters must be at least 1.',
            'location.required'       => 'The location is required.',
            'location.string'         => 'The location must be a string.',
            'status.required'         => 'The status is required.',
            'status.in'               => 'The status must be either active or inactive.',
        ];
    }
}
