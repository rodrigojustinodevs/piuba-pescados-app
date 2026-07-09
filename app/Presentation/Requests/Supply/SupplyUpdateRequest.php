<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Supply;

use App\Domain\Enums\SupplyCategoryEnum;
use App\Domain\Enums\SupplyStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SupplyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'unitCost'     => 'unit_cost',
            'salePrice'    => 'sale_price',
            'currentStock' => 'current_stock',
            'minStock'     => 'min_stock',
            'isProduct'    => 'is_product',
        ];

        $normalized = [];

        foreach ($map as $camel => $snake) {
            if ($this->has($camel) && ! $this->has($snake)) {
                $normalized[$snake] = $this->input($camel);
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $supplyId = $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'sku'  => [
                'sometimes', 'nullable', 'string', 'max:100',
                Rule::unique('supplies', 'sku')->ignore($supplyId)->whereNull('deleted_at'),
            ],
            'category'      => ['sometimes', Rule::enum(SupplyCategoryEnum::class)],
            'unit'          => ['sometimes', 'string', 'max:50'],
            'unit_cost'     => ['sometimes', 'numeric', 'min:0'],
            'sale_price'    => ['sometimes', 'numeric', 'min:0'],
            'current_stock' => ['sometimes', 'numeric', 'min:0'],
            'min_stock'     => ['sometimes', 'numeric', 'min:0'],
            'supplier_id'   => ['sometimes', 'nullable', 'uuid', 'exists:suppliers,id'],
            'is_product'    => ['sometimes', 'boolean'],
            'status'        => ['sometimes', Rule::enum(SupplyStatusEnum::class)],
            'description'   => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'name.max'              => 'O nome não pode ter mais de 255 caracteres.',
            'sku.unique'            => 'Este SKU já está em uso.',
            'category.enum'         => 'Categoria inválida.',
            'unit_cost.numeric'     => 'O custo unitário deve ser numérico.',
            'unit_cost.min'         => 'O custo unitário não pode ser negativo.',
            'sale_price.numeric'    => 'O preço de venda deve ser numérico.',
            'current_stock.numeric' => 'O estoque atual deve ser numérico.',
            'min_stock.numeric'     => 'O estoque mínimo deve ser numérico.',
            'is_product.boolean'    => 'O campo produto vendável deve ser verdadeiro ou falso.',
            'status.enum'           => 'Status inválido.',
        ];
    }
}
