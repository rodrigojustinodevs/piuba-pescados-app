<?php

declare(strict_types=1);

namespace App\Presentation\Requests\CostAllocation;

use App\Domain\Enums\AllocationMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CostAllocationStoreRequest extends FormRequest
{
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
            'company_id'               => ['nullable', 'uuid', 'exists:companies,id'],
            'financial_transaction_id' => ['required', 'uuid', 'exists:financial_transactions,id'],
            'allocation_method'        => ['required', new Enum(AllocationMethod::class)],
            'notes'                    => ['nullable', 'string'],

            'allocations'               => ['required', 'array', 'min:1'],
            'allocations.*.stocking_id' => ['required', 'uuid', 'exists:stockings,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'financial_transaction_id.required' => 'A transação financeira de origem é obrigatória.',
            'financial_transaction_id.exists'   => 'A transação financeira selecionada não existe.',

            'allocation_method.required'                         => 'O método de distribuição é obrigatório.',
            'allocation_method.Illuminate\Validation\Rules\Enum' => 'Método inválido. Use: flat, biomass ou volume.',

            'allocations.required'               => 'Informe ao menos uma estocagem para o rateio.',
            'allocations.min'                    => 'Informe ao menos uma estocagem para o rateio.',
            'allocations.*.stocking_id.required' => 'O ID da estocagem é obrigatório.',
            'allocations.*.stocking_id.exists'   => 'Estocagem não encontrada.',
        ];
    }
}
