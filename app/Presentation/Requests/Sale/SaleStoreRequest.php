<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Valida e normaliza o payload de criação de uma venda/despesca.
 *
 * Correção em relação à versão anterior:
 *   - prepareForValidation() usava input('field', input('camelCase')), que injeta
 *     null silenciosamente quando nenhuma das duas chaves existe, contaminando
 *     a validação de campos obrigatórios.
 *   - Agora só normaliza quando a chave camelCase está presente e a snake_case ausente
 *     (mesmo padrão da SaleUpdateRequest).
 *
 * tolerancePercent é aceito no payload mas NÃO é usado pelo ProcessHarvestSaleUseCase
 * (que usa constante interna de 50%). O campo é mantido no contrato da API por
 * compatibilidade — remover quando o UseCase passar a consumi-lo.
 */
final class SaleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $map = [
            'companyId'           => 'company_id',
            'clientId'            => 'client_id',
            'batchId'             => 'batch_id',
            'stockingId'          => 'stocking_id',
            'financialCategoryId' => 'financial_category_id',
            'totalWeight'         => 'total_weight',
            'pricePerKg'          => 'price_per_kg',
            'saleDate'            => 'sale_date',
            'isTotalHarvest'      => 'is_total_harvest',
            'requiresInvoice'     => 'requires_invoice',
            'tolerancePercent'    => 'tolerance_percent',
            'needsInvoice'        => 'needs_invoice',
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

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            'company_id'            => ['nullable', 'uuid', 'exists:companies,id'],
            'client_id'             => ['required', 'uuid', 'exists:clients,id'],
            'batch_id'              => ['required', 'uuid', 'exists:batches,id'],
            'stocking_id'           => ['required', 'uuid', 'exists:stockings,id'],
            'financial_category_id' => ['nullable', 'uuid', 'exists:financial_categories,id'],
            'total_weight'          => ['required', 'numeric', 'min:0.001'],
            'price_per_kg'          => ['required', 'numeric', 'min:0'],
            'sale_date'             => ['required', 'date'],
            'status'                => ['nullable', new Enum(SaleStatus::class)],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'is_total_harvest'      => ['nullable', 'boolean'],
            'needs_invoice'         => ['nullable', 'boolean'],
            'tolerance_percent'     => ['nullable', 'numeric', 'min:0', 'max:50'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'client_id.required'  => 'O cliente é obrigatório.',
            'client_id.exists'    => 'O cliente informado não foi encontrado.',

            'batch_id.required'   => 'O lote é obrigatório.',
            'batch_id.exists'     => 'O lote informado não foi encontrado.',

            'stocking_id.required' => 'O povoamento é obrigatório.',
            'stocking_id.exists'   => 'O povoamento informado não foi encontrado.',

            'financial_category_id.exists' => 'A categoria financeira informada não foi encontrada.',

            'total_weight.required' => 'O peso total é obrigatório.',
            'total_weight.numeric'  => 'O peso total deve ser numérico.',
            'total_weight.min'      => 'O peso total deve ser maior que zero.',

            'price_per_kg.required' => 'O preço por kg é obrigatório.',
            'price_per_kg.numeric'  => 'O preço por kg deve ser numérico.',
            'price_per_kg.min'      => 'O preço por kg não pode ser negativo.',

            'sale_date.required'    => 'A data de venda é obrigatória.',
            'sale_date.date'        => 'A data de venda deve ser uma data válida.',

            'status.Illuminate\Validation\Rules\Enum' => 'O status deve ser: pending, confirmed ou cancelled.',

            'needs_invoice.boolean'     => 'O campo nota fiscal deve ser verdadeiro ou falso.',
            'is_total_harvest.boolean'  => 'O campo despesca total deve ser verdadeiro ou falso.',
            'tolerance_percent.numeric' => 'A tolerância deve ser numérica.',
            'tolerance_percent.min'     => 'A tolerância deve ser maior que zero.',
            'tolerance_percent.max'     => 'A tolerância deve ser no máximo 50%.',
        ];
    }
}