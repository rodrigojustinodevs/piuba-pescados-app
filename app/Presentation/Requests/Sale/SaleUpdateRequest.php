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
            'client_id.exists'                        => 'The selected customer does not exist.',
            'total_weight.min'                        => 'The total weight must be greater than zero.',
            'price_per_kg.min'                        => 'The price per kg must be greater than zero.',
            'sale_date.date'                          => 'The sale date must be a valid date.',
            'status.Illuminate\Validation\Rules\Enum' => 'The status must be: pending, confirmed or cancelled.',
        ];
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'client_id'    => $this->input('client_id', $this->input('clientId')),
            'total_weight' => $this->input('total_weight', $this->input('totalWeight')),
            'price_per_kg' => $this->input('price_per_kg', $this->input('pricePerKg')),
            'sale_date'    => $this->input('sale_date', $this->input('saleDate')),
            'status'       => $this->input('status'),
            'notes'        => $this->input('notes'),
        ]);
    }
}
