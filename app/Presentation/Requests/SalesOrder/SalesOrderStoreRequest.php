<?php

declare(strict_types=1);

namespace App\Presentation\Requests\SalesOrder;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalesOrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->merge(
            array_merge(
                $this->normalizeFields(),
                $this->normalizeItems()
            )
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id'              => $this->clientRules(),
            'issue_date'             => ['required', 'date_format:Y-m-d'],
            'expected_payment_date'  => ['required', 'date_format:Y-m-d', 'after_or_equal:issue_date'],
            'expected_delivery_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:issue_date'],

            'financial_category_id' => ['required', 'uuid', 'exists:financial_categories,id'],
            'needs_invoice'         => ['required', 'boolean'],
            'notes'                 => ['nullable', 'string', 'max:65535'],

            'items'               => ['required', 'array', 'min:1'],
            'items.*.stocking_id' => $this->stockingRules(),
            'items.*.quantity'    => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'client_id.required' => 'The client is required.',
            'client_id.exists'   => 'The client does not belong to the company.',

            'issue_date.required'    => 'The issue date is required.',
            'issue_date.date_format' => 'The issue date must be a valid date.',

            'expected_delivery_date.required'       => 'The expected delivery date is required.',
            'expected_delivery_date.date_format'    => 'The expected delivery date must be a valid date.',
            'expected_delivery_date.after_or_equal' => 'The expected delivery date must be after or equal to '
                . 'issue date.',

            'expected_payment_date.required'       => 'The expected payment date is required.',
            'expected_payment_date.date_format'    => 'The expected payment date must be a valid date.',
            'expected_payment_date.after_or_equal' => 'The expected payment date must be after or equal to issue date.',

            'financial_category_id.required' => 'The financial category is required.',
            'financial_category_id.exists'   => 'The financial category does not belong to the company.',

            'needs_invoice.required' => 'The needs invoice field is required.',
            'needs_invoice.boolean'  => 'The needs invoice field must be true or false.',

            'items.required'              => 'At least one item is required.',
            'items.*.stocking_id.exists'  => 'Invalid stocking for this company.',
            'items.*.quantity.required'   => 'The quantity is required.',
            'items.*.quantity.numeric'    => 'The quantity must be a number.',
            'items.*.quantity.gt'         => 'The quantity must be greater than zero.',
            'items.*.unit_price.required' => 'The unit price is required.',
            'items.*.unit_price.numeric'  => 'The unit price must be a number.',
            'items.*.unit_price.min'      => 'The unit price must be greater than zero.',
        ];
    }

    // =========================
    // 🔹 Normalization Layer
    // =========================

    /**
     * @return array<string, mixed>
     */
    private function normalizeFields(): array
    {
        return $this->mapCamelToSnake(
            [
                'clientId'             => 'client_id',
                'issueDate'            => 'issue_date',
                'expectedPaymentDate'  => 'expected_payment_date',
                'expectedDeliveryDate' => 'expected_delivery_date',
                'needsInvoice'         => 'needs_invoice',
                'financialCategoryId'  => 'financial_category_id',
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeItems(): array
    {
        if (! $this->has('items') || ! is_array($this->input('items'))) {
            return [];
        }

        return [
            'items' => array_map(
                fn (?array $item): array => $this->mapCamelToSnake(
                    [
                        'stockingId' => 'stocking_id',
                        'unitPrice'  => 'unit_price',
                    ],
                    $item
                ),
                $this->input('items')
            ),
        ];
    }

    /**
     * @param array<string, string>     $map
     * @param array<string, mixed>|null $source
     *
     * @return array<string, mixed>
     */
    private function mapCamelToSnake(array $map, ?array $source = null): array
    {
        $source ??= $this->all();
        $normalized = [];

        foreach ($map as $camel => $snake) {
            if (array_key_exists($camel, $source) && ! array_key_exists($snake, $source)) {
                $normalized[$snake] = $source[$camel];
            }
        }

        return array_merge($source, $normalized);
    }

    // =========================
    // 🔹 Rules Layer
    // =========================

    /**
     * @return array<int, mixed>
     */
    private function clientRules(): array
    {
        return [
            'required',
            'uuid',
            Rule::exists('clients', 'id')
                ->whereNull('deleted_at'),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function stockingRules(): array
    {
        return [
            'required',
            'uuid',
            Rule::exists('stockings', 'id')
                ->where(
                    fn ($q) => $q
                        ->whereNull('deleted_at')
                ),
        ];
    }
}
