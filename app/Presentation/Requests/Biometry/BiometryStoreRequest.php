<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Biometry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BiometryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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

        if (! $this->has('sampleWeight') && $this->has('sample_weight')) {
            $merge['sampleWeight'] = $this->input('sample_weight');
        }

        if (! $this->has('sampleQuantity') && $this->has('sample_quantity')) {
            $merge['sampleQuantity'] = $this->input('sample_quantity');
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     * Usa camelCase para não expor estrutura do banco.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'batchId' => [
                'required',
                'uuid',
                Rule::exists('batches', 'id')->where('status', 'active'),
            ],
            'biometryDate'   => ['required', 'date'],
            'averageWeight'  => ['required_without_all:sampleWeight,sampleQuantity', 'numeric', 'min:0'],
            'sampleWeight'   => ['required_without:averageWeight', 'numeric', 'min:0'],
            'sampleQuantity' => ['required_without:averageWeight', 'integer', 'min:1'],
            'fcr'            => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batchId.exists' => 'The batch informed does not exist or is not active. '
                . 'Only active batches allow biometry.',
            'averageWeight.required_without_all' => 'The average weight is required when sample weight '
                . 'and sample quantity are not provided.',
            'sampleWeight.required_without' => 'The sample weight is required when average weight '
                . 'is not provided.',
            'sampleQuantity.required_without' => 'The sample quantity is required when average weight '
                . 'is not provided.',
            'sampleWeight.numeric'   => 'The sample weight must be a number.',
            'sampleWeight.min'       => 'The sample weight must be at least 0.',
            'sampleQuantity.integer' => 'The sample quantity must be an integer.',
            'sampleQuantity.min'     => 'The sample quantity must be at least 1.',
        ];
    }
}
