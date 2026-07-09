<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Supply;

use App\Domain\Enums\SupplyCategoryEnum;
use App\Domain\Enums\SupplyStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SupplyStoreRequest extends FormRequest
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
        return [
            'companyId' => ['sometimes', 'uuid', 'exists:companies,id'],
            'name'      => ['required', 'string', 'max:255'],
            'sku'       => ['nullable', 'string', 'max:100',
                Rule::unique('supplies', 'sku')->whereNull('deleted_at')],
            'category'      => ['required', Rule::enum(SupplyCategoryEnum::class)],
            'unit'          => ['required', 'string', 'max:50'],
            'unit_cost'     => ['required', 'numeric', 'min:0'],
            'sale_price'    => ['required', 'numeric', 'min:0'],
            'current_stock' => ['required', 'numeric', 'min:0'],
            'min_stock'     => ['required', 'numeric', 'min:0'],
            'supplier_id'   => ['nullable', 'uuid', 'exists:suppliers,id'],
            'is_product'    => ['required', 'boolean'],
            'status'        => ['sometimes', Rule::enum(SupplyStatusEnum::class)],
            'description'   => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'name.required'          => 'O nome do insumo é obrigatório.',
            'name.max'               => 'O nome não pode ter mais de 255 caracteres.',
            'sku.unique'             => 'Este SKU já está em uso.',
            'category.required'      => 'A categoria é obrigatória.',
            'category.enum'          => 'Categoria inválida.',
            'unit.required'          => 'A unidade de medida é obrigatória.',
            'unit_cost.required'     => 'O custo unitário é obrigatório.',
            'unit_cost.numeric'      => 'O custo unitário deve ser numérico.',
            'unit_cost.min'          => 'O custo unitário não pode ser negativo.',
            'sale_price.required'    => 'O preço de venda é obrigatório.',
            'sale_price.numeric'     => 'O preço de venda deve ser numérico.',
            'current_stock.required' => 'O estoque atual é obrigatório.',
            'current_stock.numeric'  => 'O estoque atual deve ser numérico.',
            'min_stock.required'     => 'O estoque mínimo é obrigatório.',
            'min_stock.numeric'      => 'O estoque mínimo deve ser numérico.',
            'supplier_id.uuid'       => 'O fornecedor deve ser um UUID válido.',
            'supplier_id.exists'     => 'O fornecedor selecionado não existe.',
            'is_product.required'    => 'O campo produto vendável é obrigatório.',
            'is_product.boolean'     => 'O campo produto vendável deve ser verdadeiro ou falso.',
            'status.enum'            => 'Status inválido.',
        ];
    }
}
