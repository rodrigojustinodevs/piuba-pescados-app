<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SaleStoreRequest extends FormRequest
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
            'company_id'            => ['nullable', 'uuid', 'exists:companies,id'],
            'client_id'             => ['required', 'uuid', 'exists:clients,id'],
            'batch_id'              => ['required', 'uuid', 'exists:batches,id'],
            'stocking_id'           => ['nullable', 'uuid', 'exists:stockings,id'],
            'financial_category_id' => ['nullable', 'uuid', 'exists:financial_categories,id'],
            'total_weight'          => ['required', 'numeric', 'min:0.001'],
            'price_per_kg'          => ['required', 'numeric', 'min:0'],
            'sale_date'             => ['required', 'date'],
            'status'                => ['nullable', new Enum(SaleStatus::class)],
            'notes'                 => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'client_id.required' => 'O cliente é obrigatório.',
            'client_id.exists'   => 'O cliente selecionado não existe.',

            'batch_id.required' => 'O lote é obrigatório.',
            'batch_id.exists'   => 'O lote selecionado não existe.',

            'stocking_id.exists' => 'A estocagem selecionada não existe.',

            'financial_category_id.exists' => 'A categoria financeira selecionada não existe.',

            'total_weight.required' => 'O peso total é obrigatório.',
            'total_weight.numeric'  => 'O peso total deve ser numérico.',
            'total_weight.min'      => 'O peso total deve ser maior que zero.',

            'price_per_kg.required' => 'O preço por kg é obrigatório.',
            'price_per_kg.numeric'  => 'O preço por kg deve ser numérico.',
            'price_per_kg.min'      => 'O preço por kg não pode ser negativo.',

            'sale_date.required' => 'A data da venda é obrigatória.',
            'sale_date.date'     => 'A data da venda deve ser uma data válida.',

            'status.Illuminate\Validation\Rules\Enum' => 'O status deve ser: pending, confirmed ou cancelled.',
        ];
    }
}
