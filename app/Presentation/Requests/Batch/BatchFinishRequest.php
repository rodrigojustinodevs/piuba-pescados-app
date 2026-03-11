<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Batch;

use Illuminate\Foundation\Http\FormRequest;

class BatchFinishRequest extends FormRequest
{
    #[\Override]
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('totalWeight') && ! $this->has('total_weight')) {
            $data['total_weight'] = $this->input('totalWeight');
        }

        if ($this->has('pricePerKg') && ! $this->has('price_per_kg')) {
            $data['price_per_kg'] = $this->input('pricePerKg');
        }

        if ($this->has('harvestDate') && ! $this->has('harvest_date')) {
            $data['harvest_date'] = $this->input('harvestDate');
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'total_weight' => ['required', 'numeric', 'min:0'],
            'price_per_kg' => ['required', 'numeric', 'min:0'],
            'harvest_date' => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'total_weight.required' => 'The total weight of the catch is mandatory.',
            'total_weight.numeric' => 'The total weight must be a number.',
            'total_weight.min'     => 'The total weight must be greater than or equal to zero.',

            'price_per_kg.required' => 'The price per kg is mandatory.',
            'price_per_kg.numeric'  => 'The price per kg must be a number.',
            'price_per_kg.min'      => 'The price per kg must be greater than or equal to zero.',

            'harvest_date.date'        => 'The harvest date must be a valid date.',
            'harvest_date.date_format' => 'The harvest date must be in Y-m-d format.',
        ];
    }
}
