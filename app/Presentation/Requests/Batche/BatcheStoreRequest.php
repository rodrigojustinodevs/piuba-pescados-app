<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Batche;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatcheStoreRequest extends FormRequest
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
            'tankId'          => ['required', 'uuid', 'exists:tanks,id'],
            'entryDate'       => ['required', 'date'],
            'initialQuantity' => ['required', 'integer', 'min:1'],
            'species'         => ['required', 'string', 'max:255'],
            'cultivation'     => ['required', Rule::in(['daycare', 'nursery'])->__toString()],
        ];
    }
}
