<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Batch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'tankId'          => ['required', 'uuid', 'exists:tanks,id'],
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'entryDate'       => ['required', 'date'],
            'initialQuantity' => ['required', 'integer', 'min:1'],
            'species'         => ['required', 'string', 'max:255'],
            'cultivation'     => ['required', Rule::in(['growout', 'nursery'])->__toString()],
        ];
    }

    /** @return array<string, string> */
    #[\Override]
    public function messages(): array
    {
        return [
            'tankId.required'          => 'The tank ID is required.',
            'tankId.uuid'              => 'The tank ID must be a valid UUID.',
            'tankId.exists'            => 'The tank ID must exist in the tanks table.',
            'name.required'            => 'The name is required.',
            'name.string'              => 'The name must be a string.',
            'name.max'                 => 'The name must be less than 255 characters.',
            'description.string'       => 'The description must be a string.',
            'description.max'          => 'The description must be less than 255 characters.',
            'entryDate.required'       => 'The entry date is required.',
            'entryDate.date'           => 'The entry date must be a valid date.',
            'initialQuantity.required' => 'The initial quantity is required.',
            'initialQuantity.integer'  => 'The initial quantity must be an integer.',
            'initialQuantity.min'      => 'The initial quantity must be at least 1.',
            'species.required'         => 'The species is required.',
            'species.string'           => 'The species must be a string.',
            'species.max'              => 'The species must be less than 255 characters.',
            'cultivation.required'     => 'The cultivation is required.',
            'cultivation.in'           => 'The cultivation must be either growout or nursery.',
        ];
    }
}
