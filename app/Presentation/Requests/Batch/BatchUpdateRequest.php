<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Batch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<\Illuminate\Validation\Rules\In|string>|array<int,
     *         \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'tankId'          => ['sometimes', 'uuid', 'exists:tanks,id'],
            'name'            => ['sometimes', 'string', 'max:255'],
            'description'     => ['sometimes', 'nullable', 'string'],
            'entryDate'       => ['sometimes', 'date'],
            'initialQuantity' => ['sometimes', 'integer', 'min:1'],
            'species'         => ['sometimes', 'string', 'max:255'],
            'status'          => ['sometimes', Rule::in(['active', 'finished'])],
            'cultivation'     => ['sometimes', Rule::in(['growout', 'nursery'])],
        ];
    }

    /** @return array<string, string> */
    #[\Override]
    public function messages(): array
    {
        return [
            'tankId.uuid'             => 'The tank ID must be a valid UUID.',
            'tankId.exists'           => 'The tank ID must exist in the tanks table.',
            'name.string'             => 'The name must be a string.',
            'name.max'                => 'The name must be less than 255 characters.',
            'description.string'      => 'The description must be a string.',
            'description.max'         => 'The description must be less than 255 characters.',
            'entryDate.date'          => 'The entry date must be a valid date.',
            'initialQuantity.integer' => 'The initial quantity must be an integer.',
            'initialQuantity.min'     => 'The initial quantity must be at least 1.',
            'species.string'          => 'The species must be a string.',
            'species.max'             => 'The species must be less than 255 characters.',
            'status.in'               => 'The status must be either active or finished.',
            'cultivation.in'          => 'The cultivation must be either growout or nursery.',
        ];
    }
}
