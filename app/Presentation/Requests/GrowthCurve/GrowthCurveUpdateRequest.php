<?php

declare(strict_types=1);

namespace App\Presentation\Requests\GrowthCurve;

use Illuminate\Foundation\Http\FormRequest;

class GrowthCurveUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $merge = [];

        if (! $this->has('batchId') && $this->has('batch_id')) {
            $merge['batchId'] = $this->input('batch_id');
        }

        if (! $this->has('averageWeight') && $this->has('average_weight')) {
            $merge['averageWeight'] = $this->input('average_weight');
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'batchId'       => ['sometimes', 'uuid', 'exists:batches,id'],
            'averageWeight' => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batchId.uuid'          => 'The batch ID must be a valid UUID.',
            'batchId.exists'        => 'The selected batch does not exist.',
            'averageWeight.numeric' => 'The average weight must be a number.',
            'averageWeight.min'     => 'The average weight must be at least 0.',
        ];
    }
}
