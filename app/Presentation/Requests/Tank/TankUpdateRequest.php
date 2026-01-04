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
}
