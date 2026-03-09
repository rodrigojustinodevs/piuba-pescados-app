<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Stocking;

use Illuminate\Foundation\Http\FormRequest;

class StockingUpdateRequest extends FormRequest
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

        if (! $this->has('stockingDate') && $this->has('stocking_date')) {
            $merge['stockingDate'] = $this->input('stocking_date');
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
            'stockingDate'  => 'sometimes|date',
            'quantity'      => 'sometimes|integer|min:1',
            'averageWeight' => 'sometimes|numeric|min:0',
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
            'stockingDate.date'     => 'The stocking date must be a valid date.',
            'quantity.integer'      => 'The quantity must be an integer.',
            'quantity.min'          => 'The quantity must be at least 1.',
            'averageWeight.numeric' => 'The average weight must be a number.',
            'averageWeight.min'     => 'The average weight must be at least 0.',
        ];
    }
}
