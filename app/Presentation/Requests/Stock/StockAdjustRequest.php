<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

final class StockAdjustRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        if ($this->has('physicalQuantity') && ! $this->has('new_physical_quantity')) {
            $this->merge(['new_physical_quantity' => $this->input('physicalQuantity')]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'new_physical_quantity' => ['required', 'numeric', 'min:0'],
            'reason'                => ['nullable', 'string', 'max:500'],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'new_physical_quantity.required' => 'Informe a quantidade física contada.',
            'new_physical_quantity.numeric'  => 'A quantidade deve ser um número.',
            'new_physical_quantity.min'      => 'A quantidade física não pode ser negativa.',
        ];
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function validated($key = null, $default = null): array
    {
        return array_merge(parent::validated($key, $default), [
            'user_id'  => (string) $this->user()?->id,
            'stock_id' => (string) $this->route('id'),
        ]);
    }
}
