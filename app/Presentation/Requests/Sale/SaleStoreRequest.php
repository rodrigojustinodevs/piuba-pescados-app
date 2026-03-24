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

            'is_total_harvest.boolean'  => 'The total harvest field must be true or false.',
            'tolerance_percent.numeric' => 'The tolerance percent must be numeric.',
            'tolerance_percent.min'     => 'The tolerance percent must be greater than zero.',
            'tolerance_percent.max'     => 'The tolerance percent must be less than or equal to 50.',
        ];
    }
}
