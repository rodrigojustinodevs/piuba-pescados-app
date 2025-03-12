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
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'company_id'      => ['required', 'uuid', 'exists:companies,id'],
            'tank_types_id'   => ['required', 'uuid', 'exists:tank_types,id'],
            'name'            => ['required', 'string', 'max:255'],
            'capacity_liters' => ['required', 'integer', 'min:1'],
            'location'        => ['required', 'string'],
            'status'          => ['required', Rule::in(['active', 'inactive'])->__toString()],
            'cultivation'     => ['required', Rule::in(['daycare', 'nursery'])->__toString()],
        ];
    }
}
