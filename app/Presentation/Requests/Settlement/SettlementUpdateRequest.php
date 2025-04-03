<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Settlement;

use Illuminate\Foundation\Http\FormRequest;

class SettlementUpdateRequest extends FormRequest
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
            'batch_id'        => 'sometimes|uuid|exists:batches,id',
            'settlement_date' => 'sometimes|date',
            'quantity'        => 'sometimes|integer|min:1',
            'average_weight'  => 'sometimes|numeric|min:0',
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batch_id.uuid'          => 'The batch ID must be a valid UUID.',
            'batch_id.exists'        => 'The batch ID must exist in the batches table.',
            'settlement_date.date'   => 'The settlement date must be a valid date.',
            'quantity.integer'       => 'The quantity must be an integer.',
            'quantity.min'           => 'The quantity must be at least 1.',
            'average_weight.numeric' => 'The average weight must be a number.',
            'average_weight.min'     => 'The average weight must be at least 0.',
        ];
    }
}
