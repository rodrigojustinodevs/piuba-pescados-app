<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Mortality;

use Illuminate\Foundation\Http\FormRequest;

class MortalityStoreRequest extends FormRequest
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
            'batche_id' => ['required', 'uuid', 'exists:batches,id'],
            'quantity'  => ['required', 'integer', 'min:1'],
            'cause'     => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batche_id.required' => 'The batche ID is required.',
            'batche_id.uuid'     => 'The batche ID must be a valid UUID.',
            'batch_id.exists'    => 'The batch ID must exist in the batches table.',
            'quantity.required'  => 'The quantity is required.',
            'quantity.integer'   => 'The quantity must be an integer.',
            'quantity.min'       => 'The quantity must be at least 1.',
            'cause.required'     => 'The cause is required.',
            'cause.string'       => 'The cause must be a valid text.',
            'cause.max'          => 'The cause must not exceed 255 characters.',
        ];
    }
}
