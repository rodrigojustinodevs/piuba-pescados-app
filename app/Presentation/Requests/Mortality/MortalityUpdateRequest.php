<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Mortality;

use App\Domain\Enums\MortalityCause;
use App\Domain\Enums\MortalitySeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'batchId'       => 'sometimes|uuid|exists:batches,id',
            'mortalityDate' => 'sometimes|date|date_format:Y-m-d',
            'quantity'      => 'sometimes|integer|min:1',
            'cause'         => ['sometimes', Rule::enum(MortalityCause::class)],
            'description'   => 'sometimes|nullable|string|max:255',
            'severity'      => ['sometimes', Rule::enum(MortalitySeverity::class)],
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
            'cause.enum'                => 'The cause must be one of: disease, water_quality,'
                . ' predation, handling, climate, unknown, other.',
            'severity.enum'             => 'The severity must be one of: low, medium, high, critical.',
        ];
    }
}
