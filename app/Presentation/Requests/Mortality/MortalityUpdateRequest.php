<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Mortality;

use Illuminate\Foundation\Http\FormRequest;

class MortalityUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'batche_id' => 'sometimes|uuid|exists:batches,id',
            'quantity'  => 'sometimes|integer|min:1',
            'cause'     => 'sometimes|string|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batche_id.uuid'   => 'The batch ID must be a valid UUID.',
            'batche_id.exists' => 'The batch ID must exist in the batches table.',
            'quantity.integer' => 'The quantity must be an integer.',
            'quantity.min'     => 'The quantity must be at least 1.',
            'cause.string'     => 'The cause must be a valid text.',
            'cause.max'        => 'The cause must not exceed 255 characters.',
        ];
    }
}
