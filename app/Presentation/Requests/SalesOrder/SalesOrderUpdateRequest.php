<?php

declare(strict_types=1);

namespace App\Presentation\Requests\SalesOrder;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Enums\SalesOrderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalesOrderUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->merge(['id' => $this->route('id')]);

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
        $companyId = $this->companyId();

        return [
            'id' => [
                'required',
                'uuid',
                Rule::exists('sales_orders', 'id')->where(static function ($q) use ($companyId): void {
                    $q->where('company_id', $companyId)
                        ->where('type', SalesOrderType::ORDER->value)
                        ->whereNull('deleted_at');
                }),
            ],
            'client_id'              => $this->clientRules(),
            'issue_date'             => ['required', 'date_format:Y-m-d'],
            'expected_payment_date'  => ['required', 'date_format:Y-m-d', 'after_or_equal:issue_date'],
            'expected_delivery_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:issue_date'],
            'financial_category_id'  => ['required', 'uuid', 'exists:financial_categories,id'],
            'needs_invoice'          => ['required', 'boolean'],
            'notes'                  => ['nullable', 'string', 'max:65535'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.stocking_id'    => $this->stockingRules(),
            'items.*.quantity'       => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeFields(): array
    {
        return $this->mapCamelToSnake([
            'clientId'             => 'client_id',
            'issueDate'            => 'issue_date',
            'expectedPaymentDate'  => 'expected_payment_date',
            'expectedDeliveryDate' => 'expected_delivery_date',
            'needsInvoice'         => 'needs_invoice',
            'financialCategoryId'  => 'financial_category_id',
            'companyId'            => 'company_id',
        ]);
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
                fn (?array $item): array => $this->mapCamelToSnake([
                    'stockingId' => 'stocking_id',
                    'unitPrice'  => 'unit_price',
                ], $item),
                $this->input('items')
            ),
        ];
    }

    /**
     * @param array<string, string> $map
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

    /**
     * @return array<int, mixed>
     */
    private function clientRules(): array
    {
        return [
            'required',
            'uuid',
            Rule::exists('clients', 'id')
                ->where('company_id', $this->companyId())
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
                ->where(fn ($q) => $q->whereNull('deleted_at')),
        ];
    }

    private function companyId(): ?string
    {
        $hint = $this->input('company_id');

        if (! is_string($hint) || $hint === '') {
            $camel = $this->input('companyId');
            $hint  = (is_string($camel) && $camel !== '') ? $camel : null;
        }

        return app(CompanyResolverInterface::class)->tryResolve($hint);
    }
}
