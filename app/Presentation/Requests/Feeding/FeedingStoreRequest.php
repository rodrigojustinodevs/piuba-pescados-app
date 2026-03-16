<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Feeding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedingStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $merge = [];

        if (! $this->has('batchId') && $this->has('batch_id')) {
            $merge['batchId'] = $this->input('batch_id');
        }

        if (! $this->has('batchId') && $this->has('batche_id')) {
            $merge['batchId'] = $this->input('batche_id');
        }

        if (! $this->has('feedingDate') && $this->has('feeding_date')) {
            $merge['feedingDate'] = $this->input('feeding_date');
        }

        if (! $this->has('quantityProvided') && $this->has('quantity_provided')) {
            $merge['quantityProvided'] = $this->input('quantity_provided');
        }

        if (! $this->has('feedType') && $this->has('feed_type')) {
            $merge['feedType'] = $this->input('feed_type');
        }

        if (! $this->has('stockReductionQuantity') && $this->has('stock_reduction_quantity')) {
            $merge['stockReductionQuantity'] = $this->input('stock_reduction_quantity');
        }

        if (! $this->has('stockId') && $this->has('stock_id')) {
            $merge['stockId'] = $this->input('stock_id');
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     * Usa camelCase para não expor estrutura do banco.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'batchId' => [
                'required',
                'uuid',
                Rule::exists('batches', 'id')->where('status', 'active'),
            ],
            'feedingDate'            => ['required', 'date'],
            'quantityProvided'       => ['required', 'numeric', 'gt:0'],
            'feedType'               => ['required', 'string', 'max:100'],
            'stockId'                => ['nullable', 'uuid', 'exists:stocks,id'],
            'stockReductionQuantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batchId.exists' => 'The batch informed does not exist or is not active. '
                . 'Only active batches allow feeding.',
        ];
    }
}
