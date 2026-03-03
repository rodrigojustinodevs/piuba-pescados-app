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
            'cultivation'     => ['required', Rule::in(['daycare', 'nursery'])->__toString()],
        ];
    }
}
