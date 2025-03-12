<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Batche;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatcheUpdateRequest extends FormRequest
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
     * @return array<string, list<\Illuminate\Validation\Rules\In|string>
     * |array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'tank_id'          => ['sometimes', 'uuid', 'exists:tanks,id'],
            'entry_date'       => ['sometimes', 'date'],
            'initial_quantity' => ['sometimes', 'integer', 'min:1'],
            'species'          => ['sometimes', 'string', 'max:255'],
            'status'           => ['sometimes', Rule::in(['active', 'finished'])],
            'cultivation'      => ['sometimes', Rule::in(['daycare', 'nursery'])],
        ];
    }
}
