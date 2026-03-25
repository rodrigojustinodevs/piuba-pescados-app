<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SaleStoreRequest extends FormRequest
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
            'company_id'            => ['nullable', 'uuid', 'exists:companies,id'],
            'client_id'             => ['required', 'uuid', 'exists:clients,id'],
            'batch_id'              => ['required', 'uuid', 'exists:batches,id'],
            'stocking_id'           => ['nullable', 'uuid', 'exists:stockings,id'],
            'financial_category_id' => ['nullable', 'uuid', 'exists:financial_categories,id'],
            'total_weight'          => ['required', 'numeric', 'min:0.001'],
            'price_per_kg'          => ['required', 'numeric', 'min:0'],
            'sale_date'             => ['required', 'date'],
            'status'                => ['nullable', new Enum(SaleStatus::class)],
            'notes'                 => ['nullable', 'string'],
            'is_total_harvest'      => ['nullable', 'boolean'],
            'tolerance_percent'     => ['nullable', 'numeric', 'min:0', 'max:50'],
            'needs_invoice'         => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'client_id.required' => 'The customer is required.',
            'client_id.exists'   => 'The selected customer does not exist.',

            'batch_id.required' => 'The batch is required.',
            'batch_id.exists'   => 'The selected batch does not exist.',

            'stocking_id.exists' => 'The selected stocking does not exist.',

            'financial_category_id.exists' => 'The selected financial category does not exist.',

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
            'tolerance_percent.numeric' => 'The tolerance percent must be numeric.',
            'tolerance_percent.min'     => 'The tolerance percent must be greater than zero.',
            'tolerance_percent.max'     => 'The tolerance percent must be less than or equal to 50.',
        ];
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id'            => $this->input('company_id', $this->input('companyId')),
            'client_id'             => $this->input('client_id', $this->input('clientId')),
            'batch_id'              => $this->input('batch_id', $this->input('batchId')),
            'stocking_id'           => $this->input('stocking_id', $this->input('stockingId')),
            'financial_category_id' => $this->input('financial_category_id', $this->input('financialCategoryId')),

            'total_weight'      => $this->input('total_weight', $this->input('totalWeight')),
            'price_per_kg'      => $this->input('price_per_kg', $this->input('pricePerKg')),
            'sale_date'         => $this->input('sale_date', $this->input('saleDate')),
            'status'            => $this->input('status'),
            'notes'             => $this->input('notes'),
            'is_total_harvest'  => $this->input('is_total_harvest', $this->input('isTotalHarvest')),
            'tolerance_percent' => $this->input('tolerance_percent', $this->input('tolerancePercent')),
            'needs_invoice'     => $this->input('needs_invoice', $this->input('needsInvoice')),
        ]);
    }
}
