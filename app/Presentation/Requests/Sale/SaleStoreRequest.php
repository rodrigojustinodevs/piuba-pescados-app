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

    #[\Override]
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
    #[\Override]
    public function messages(): array
    {
        return [
            'client_id.required' => 'The client is required.',
            'client_id.exists'   => 'The client informed was not found.',

            'batch_id.required' => 'The batch is required.',
            'batch_id.exists'   => 'The batch informed was not found.',

            'stocking_id.required' => 'The stocking is required.',
            'stocking_id.exists'   => 'The stocking informed was not found.',

            'financial_category_id.exists' => 'The financial category informed was not found.',

            'total_weight.required' => 'The total weight is required.',
            'total_weight.numeric'  => 'The total weight must be numeric.',
            'total_weight.min'      => 'The total weight must be greater than zero.',

            'price_per_kg.required' => 'The price per kg is required.',
            'price_per_kg.numeric'  => 'The price per kg must be numeric.',
            'price_per_kg.min'      => 'The price per kg must be greater than zero.',

            'sale_date.required' => 'The sale date is required.',
            'sale_date.date'     => 'The sale date must be a valid date.',

            'status.Illuminate\Validation\Rules\Enum' => 'The status must be: pending, confirmed or cancelled.',

            'needs_invoice.boolean'     => 'The needs invoice field must be true or false.',
            'is_total_harvest.boolean'  => 'The total harvest field must be true or false.',
            'tolerance_percent.numeric' => 'The tolerance must be numeric.',
            'tolerance_percent.min'     => 'The tolerance must be greater than zero.',
            'tolerance_percent.max'     => 'The tolerance must be less than or equal to 50%.',
        ];
    }
}
