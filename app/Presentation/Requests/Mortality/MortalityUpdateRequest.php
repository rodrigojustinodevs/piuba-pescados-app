<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Mortality;

use Illuminate\Foundation\Http\FormRequest;

class MortalityUpdateRequest extends FormRequest
{
    #[\Override]
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('batch_id') && ! $this->has('batchId')) {
            $data['batchId'] = $this->input('batch_id');
        }

        if ($this->has('mortality_date') && ! $this->has('mortalityDate')) {
            $data['mortalityDate'] = $this->input('mortality_date');
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }

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
            'batchId'       => 'sometimes|uuid|exists:batches,id',
            'mortalityDate' => 'sometimes|date|date_format:Y-m-d',
            'quantity'      => 'sometimes|integer|min:1',
            'cause'         => 'sometimes|string|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batchId.uuid'              => 'The batch ID must be a valid UUID.',
            'batchId.exists'            => 'The batch ID must exist in the batches table.',
            'mortalityDate.date'        => 'The mortality date must be a valid date.',
            'mortalityDate.date_format' => 'The mortality date must be in Y-m-d format.',
            'quantity.integer'          => 'The quantity must be an integer.',
            'quantity.min'              => 'The quantity must be at least 1.',
            'cause.string'              => 'The cause must be a valid text.',
            'cause.max'                 => 'The cause must not exceed 255 characters.',
        ];
    }
}
