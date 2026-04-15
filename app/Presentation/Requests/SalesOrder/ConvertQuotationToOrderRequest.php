<?php

declare(strict_types=1);

namespace App\Presentation\Requests\SalesOrder;

use Illuminate\Foundation\Http\FormRequest;

final class ConvertQuotationToOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizeFields());
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'expected_delivery_date' => ['required', 'date_format:Y-m-d'],
            'expected_payment_date'  => ['required', 'date_format:Y-m-d'],
            'financial_category_id'  => ['required', 'uuid', 'exists:financial_categories,id'],
            'needs_invoice'          => ['required', 'boolean'],
            'notes'                  => ['nullable', 'string', 'max:65535'],
        ];
    }

    /** @return array<string, mixed> */
    private function normalizeFields(): array
    {
        return $this->mapCamelToSnake([
            'expectedDeliveryDate' => 'expected_delivery_date',
            'expectedPaymentDate'  => 'expected_payment_date',
            'financialCategoryId'  => 'financial_category_id',
            'needsInvoice'         => 'needs_invoice',
        ]);
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
}
