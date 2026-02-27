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

    #[\Override]
    protected function prepareForValidation(): void
    {
        $merge = [];
        if (! $this->has('batcheId') && $this->has('batche_id')) {
            $merge['batcheId'] = $this->input('batche_id');
        }
        if (! $this->has('biometryDate') && $this->has('biometry_date')) {
            $merge['biometryDate'] = $this->input('biometry_date');
        }
        if (! $this->has('averageWeight') && $this->has('average_weight')) {
            $merge['averageWeight'] = $this->input('average_weight');
        }
        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'batcheId'      => 'sometimes|uuid|exists:batches,id',
            'biometryDate'  => 'sometimes|date',
            'averageWeight' => 'sometimes|numeric|min:0',
            'fcr'           => 'sometimes|numeric|min:0',
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batcheId.uuid'         => 'The batche ID must be a valid UUID.',
            'batcheId.exists'       => 'The batche ID must exist in the batches table.',
            'biometryDate.date'     => 'The biometry date must be a valid date.',
            'averageWeight.numeric' => 'The average weight must be a number.',
            'averageWeight.min'     => 'The average weight must be at least 0.',
            'fcr.numeric'           => 'The FCR must be a number.',
            'fcr.min'               => 'The FCR must be at least 0.',
        ];
    }
}
