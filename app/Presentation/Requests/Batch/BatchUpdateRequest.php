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
}
