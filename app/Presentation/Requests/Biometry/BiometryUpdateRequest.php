<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Biometry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'batchId' => [
                'sometimes',
                'uuid',
                Rule::exists('batches', 'id')->where('status', 'active'),
            ],
            'biometryDate'   => 'sometimes|date',
            'averageWeight'  => 'sometimes|numeric|min:0',
            'sampleWeight'   => 'sometimes|numeric|min:0',
            'sampleQuantity' => 'sometimes|integer|min:1',
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
            'batchId.uuid'   => 'The batch ID must be a valid UUID.',
            'batchId.exists' => 'The batch informed does not exist or is not active. '
                . 'Only active batches allow biometry.',
            'biometryDate.date'      => 'The biometry date must be a valid date.',
            'averageWeight.numeric'  => 'The average weight must be a number.',
            'averageWeight.min'      => 'The average weight must be at least 0.',
            'sampleWeight.numeric'   => 'The sample weight must be a number.',
            'sampleWeight.min'       => 'The sample weight must be at least 0.',
            'sampleQuantity.integer' => 'The sample quantity must be an integer.',
            'sampleQuantity.min'     => 'The sample quantity must be at least 1.',
            'fcr.numeric'            => 'The FCR must be a number.',
            'fcr.min'                => 'The FCR must be at least 0.',
        ];
    }
}
