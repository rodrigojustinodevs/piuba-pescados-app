<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sale;

use App\Domain\Enums\PaymentMethod;
use App\Domain\Enums\SaleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Valida e normaliza o payload de criação de uma venda/despesca.
 *
 * Suporta dois formatos:
 *   - Novo: { items: [{ batch_id, stocking_id, total_weight, price_per_kg, ... }], ... }
 *   - Legado: { batch_id, stocking_id, total_weight, price_per_kg, ... } → normalizado para items[0]
 *
 * Atualmente limitado a max:1 item — a restrição é de negócio, não estrutural.
 * Remover max:1 quando o produto evoluir para suportar múltiplos itens por venda.
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
        // Normaliza camelCase → snake_case para campos do header
        $headerMap = [
            'companyId'           => 'company_id',
            'clientId'            => 'client_id',
            'financialCategoryId' => 'financial_category_id',
            'responsibleUserId'   => 'responsible_user_id',
            'saleDate'            => 'sale_date',
            'dueDate'             => 'due_date',
            'needsInvoice'        => 'needs_invoice',
            'tolerancePercent'    => 'tolerance_percent',
            'paymentMethod'       => 'payment_method',
            'invoiceNumber'       => 'invoice_number',
        ];

        $normalized = [];

        foreach ($headerMap as $camel => $snake) {
            if ($this->has($camel) && ! $this->has($snake)) {
                $normalized[$snake] = $this->input($camel);
            }
        }

        // Formato legado: campos diretos → items[0]
        if (! $this->has('items') && ($this->has('stocking_id') || $this->has('stockingId'))) {
            $normalized['items'] = [[
                'batch_id'         => $this->input('batch_id', $this->input('batchId', '')),
                'stocking_id'      => $this->input('stocking_id', $this->input('stockingId', '')),
                'total_weight'     => $this->input('total_weight', $this->input('totalWeight')),
                'price_per_kg'     => $this->input('price_per_kg', $this->input('pricePerKg')),
                'is_total_harvest' => $this->input('is_total_harvest', $this->input('isTotalHarvest', false)),
            ]];
        }

        // Normaliza camelCase → snake_case dentro de cada item
        if ($this->has('items') && is_array($this->input('items'))) {
            $itemMap = [
                'batchId'        => 'batch_id',
                'stockingId'     => 'stocking_id',
                'totalWeight'    => 'total_weight',
                'pricePerKg'     => 'price_per_kg',
                'isTotalHarvest' => 'is_total_harvest',
            ];

            $normalized['items'] = array_map(static function (array $item) use ($itemMap): array {
                foreach ($itemMap as $camel => $snake) {
                    if (array_key_exists($camel, $item) && ! array_key_exists($snake, $item)) {
                        $item[$snake] = $item[$camel];
                        unset($item[$camel]);
                    }
                }

                return $item;
            }, $this->input('items'));
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /** @return array<string, mixed[]> */
    public function rules(): array
    {
        return [
            // Header da venda
            'company_id'            => ['nullable', 'uuid', 'exists:companies,id'],
            'client_id'             => ['required', 'uuid', 'exists:clients,id'],
            'financial_category_id' => ['nullable', 'uuid', 'exists:financial_categories,id'],
            'responsible_user_id'   => ['nullable', 'uuid', 'exists:users,id'],
            'sale_date'             => ['required', 'date'],
            'due_date'              => ['nullable', 'date', 'after_or_equal:sale_date'],
            'status'                => ['nullable', new Enum(SaleStatus::class)],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'needs_invoice'         => ['nullable', 'boolean'],
            'tolerance_percent'     => ['nullable', 'numeric', 'min:0', 'max:50'],
            'discount'              => ['nullable', 'numeric', 'min:0'],
            'shipping'              => ['nullable', 'numeric', 'min:0'],
            'taxes'                 => ['nullable', 'numeric', 'min:0'],
            'payment_method'        => ['nullable', new Enum(PaymentMethod::class)],
            'invoice_number'        => ['nullable', 'string', 'max:50'],

            // Itens da venda — max:1 enquanto regra de negócio limita a 1 produto
            'items'                   => ['required', 'array', 'min:1', 'max:1'],
            'items.*.batch_id'        => ['required', 'uuid', 'exists:batches,id'],
            'items.*.stocking_id'     => ['required', 'uuid', 'exists:stockings,id'],
            'items.*.total_weight'    => ['required', 'numeric', 'min:0.001'],
            'items.*.price_per_kg'    => ['required', 'numeric', 'min:0'],
            'items.*.is_total_harvest'=> ['nullable', 'boolean'],
            'items.*.category'        => ['nullable', 'string', 'max:50'],
            'items.*.notes'           => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    #[\Override]
    public function messages(): array
    {
        return [
            'client_id.required' => 'The client is required.',
            'client_id.exists'   => 'The client informed was not found.',

            'sale_date.required' => 'The sale date is required.',
            'sale_date.date'     => 'The sale date must be a valid date.',

            'items.required'     => 'At least one sale item is required.',
            'items.max'          => 'Currently only one item per sale is supported.',

            'items.*.batch_id.required'     => 'Each item must have a batch.',
            'items.*.batch_id.exists'       => 'The batch informed was not found.',
            'items.*.stocking_id.required'  => 'Each item must have a stocking.',
            'items.*.stocking_id.exists'    => 'The stocking informed was not found.',
            'items.*.total_weight.required' => 'The total weight is required.',
            'items.*.total_weight.min'      => 'The total weight must be greater than zero.',
            'items.*.price_per_kg.required' => 'The price per kg is required.',

            'status.Illuminate\Validation\Rules\Enum' => 'The status must be: pending, confirmed or cancelled.',
            'tolerance_percent.max'                   => 'The tolerance must be less than or equal to 50%.',
        ];
    }
}
