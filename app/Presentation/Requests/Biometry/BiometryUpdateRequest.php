<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Biometry;

use Illuminate\Foundation\Http\FormRequest;

class BiometryUpdateRequest extends FormRequest
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
            'batche_id'      => 'sometimes|uuid|exists:batches,id',
            'biometry_date'  => 'sometimes|date',
            'average_weight' => 'sometimes|numeric|min:0',
            'fcr'            => 'sometimes|numeric|min:0',
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batche_id.uuid'         => 'The batche ID must be a valid UUID.',
            'batche_id.exists'       => 'The batche ID must exist in the batches table.',
            'biometry_date.date'     => 'The biometry date must be a valid date.',
            'average_weight.numeric' => 'The average weight must be a number.',
            'average_weight.min'     => 'The average weight must be at least 0.',
            'fcr.numeric'            => 'The FCR must be a number.',
            'fcr.min'                => 'The FCR must be at least 0.',
        ];
    }
}
