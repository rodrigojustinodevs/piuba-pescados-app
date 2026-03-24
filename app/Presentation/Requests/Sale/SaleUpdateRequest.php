<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SaleUpdateRequest extends FormRequest
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
            'client_id'    => ['sometimes', 'uuid', 'exists:clients,id'],
            'total_weight' => ['sometimes', 'numeric', 'min:0.001'],
            'price_per_kg' => ['sometimes', 'numeric', 'min:0'],
            'sale_date'    => ['sometimes', 'date'],
            'status'       => ['sometimes', new Enum(SaleStatus::class)],
            'notes'        => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'client_id.exists'                        => 'O cliente selecionado não existe.',
            'total_weight.min'                        => 'O peso total deve ser maior que zero.',
            'price_per_kg.min'                        => 'O preço por kg não pode ser negativo.',
            'sale_date.date'                          => 'A data da venda deve ser uma data válida.',
            'status.Illuminate\Validation\Rules\Enum' => 'O status deve ser: pending, confirmed ou cancelled.',
        ];
    }
}
