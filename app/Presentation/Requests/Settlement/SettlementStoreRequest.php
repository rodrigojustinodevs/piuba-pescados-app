<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Settlement;

use Illuminate\Foundation\Http\FormRequest;

class SettlementStoreRequest extends FormRequest
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
            'batche_id'       => 'required|uuid|exists:batches,id',
            'settlement_date' => 'required|date',
            'quantity'        => 'required|integer|min:1',
            'average_weight'  => 'required|numeric|min:0',
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batche_id.required'       => 'The batch ID is required.',
            'batche_id.uuid'           => 'The batch ID must be a valid UUID.',
            'batche_id.exists'         => 'The batch ID must exist in the batches table.',
            'settlement_date.required' => 'The settlement date is required.',
            'settlement_date.date'     => 'The settlement date must be a valid date.',
            'quantity.required'        => 'The quantity is required.',
            'quantity.integer'         => 'The quantity must be an integer.',
            'quantity.min'             => 'The quantity must be at least 1.',
            'average_weight.required'  => 'The average weight is required.',
            'average_weight.numeric'   => 'The average weight must be a number.',
            'average_weight.min'       => 'The average weight must be at least 0.',
        ];
    }
}
