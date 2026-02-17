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

    #[\Override]
    protected function prepareForValidation(): void
    {
        // Compatibilidade com payloads antigos (snake_case / typo batch_id)
        // Só faz merge quando o campo alternativo existe, para não injetar null.
        $merge = [];

        if (! $this->has('batcheId')) {
            if ($this->has('batche_id')) {
                $merge['batcheId'] = $this->input('batche_id');
            } elseif ($this->has('batch_id')) {
                $merge['batcheId'] = $this->input('batch_id');
            }
        }

        if (! $this->has('settlementDate') && $this->has('settlement_date')) {
            $merge['settlementDate'] = $this->input('settlement_date');
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
            // Usa camelCase para não expor estrutura do banco de dados
            'batcheId'       => 'required|uuid|exists:batches,id',
            'settlementDate' => 'required|date',
            'quantity'       => 'required|integer|min:1',
            'averageWeight'  => 'required|numeric|min:0',
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batcheId.required'       => 'The batch ID is required.',
            'batcheId.uuid'           => 'The batch ID must be a valid UUID.',
            'batcheId.exists'         => 'The batch ID must exist in the batches table.',
            'settlementDate.required' => 'The settlement date is required.',
            'settlementDate.date'     => 'The settlement date must be a valid date.',
            'quantity.required'       => 'The quantity is required.',
            'quantity.integer'        => 'The quantity must be an integer.',
            'quantity.min'            => 'The quantity must be at least 1.',
            'averageWeight.required'  => 'The average weight is required.',
            'averageWeight.numeric'   => 'The average weight must be a number.',
            'averageWeight.min'       => 'The average weight must be at least 0.',
        ];
    }
}
