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

        if (! $this->has('batchId') && $this->has('batch_id')) {
            $merge['batchId'] = $this->input('batch_id');
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
            'batchId'       => 'sometimes|uuid|exists:batches,id',
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
            'batchId.uuid'          => 'The batch ID must be a valid UUID.',
            'batchId.exists'        => 'The batch ID must exist in the batches table.',
            'biometryDate.date'     => 'The biometry date must be a valid date.',
            'averageWeight.numeric' => 'The average weight must be a number.',
            'averageWeight.min'     => 'The average weight must be at least 0.',
            'fcr.numeric'           => 'The FCR must be a number.',
            'fcr.min'               => 'The FCR must be at least 0.',
        ];
    }
}
