<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Harvest;

use App\Domain\Enums\HarvestDestination;
use App\Domain\Enums\HarvestStatus;
use App\Domain\Enums\HarvestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HarvestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'batchId'            => 'batch_id',
            'tankId'             => 'tank_id',
            'harvestDate'        => 'harvest_date',
            'initialPopulation'  => 'initial_population',
            'harvestedQuantity'  => 'harvested_quantity',
            'averageWeight'      => 'average_weight',
            'sizeClassifications' => 'size_classifications',
            'clientDestination'  => 'client_destination',
            'operationalCost'    => 'operational_cost',
        ];

        $normalized = [];
        foreach ($map as $camel => $snake) {
            if ($this->has($camel) && ! $this->has($snake)) {
                $normalized[$snake] = $this->input($camel);
            }
        }

        // Normaliza chaves dentro de cada item de sizeClassifications
        $classifications = $normalized['size_classifications']
            ?? $this->input('size_classifications')
            ?? [];

        if (is_array($classifications)) {
            $normalized['size_classifications'] = array_map(function (array $item): array {
                if (isset($item['averageWeight']) && ! isset($item['average_weight'])) {
                    $item['average_weight'] = $item['averageWeight'];
                    unset($item['averageWeight']);
                }
                if (isset($item['pricePerKg']) && ! isset($item['price_per_kg'])) {
                    $item['price_per_kg'] = $item['pricePerKg'];
                    unset($item['pricePerKg']);
                }

                return $item;
            }, $classifications);
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'batch_id'            => ['required', 'uuid', 'exists:batches,id'],
            'tank_id'             => ['required', 'uuid', 'exists:tanks,id'],
            'harvest_date'        => ['required', 'date'],
            'type'                => ['required', Rule::enum(HarvestType::class)],
            'status'              => ['required', Rule::enum(HarvestStatus::class)],
            'destination'         => ['required', Rule::enum(HarvestDestination::class)],
            'initial_population'  => ['required', 'integer', 'min:0'],
            'harvested_quantity'  => ['required', 'integer', 'min:0'],
            'average_weight'      => ['required', 'numeric', 'min:0'],

            'size_classifications'                    => ['required', 'array', 'min:1'],
            'size_classifications.*.class'            => ['required', 'string', 'max:10'],
            'size_classifications.*.quantity'         => ['required', 'integer', 'min:0'],
            'size_classifications.*.average_weight'   => ['required', 'numeric', 'min:0'],
            'size_classifications.*.price_per_kg'     => ['required', 'numeric', 'min:0'],

            'client_destination'  => ['nullable', 'string', 'max:255'],
            'responsible'         => ['nullable', 'string', 'max:255'],
            'operational_cost'    => ['nullable', 'numeric', 'min:0'],
            'notes'               => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batch_id.required'  => 'The batch ID is required.',
            'batch_id.uuid'      => 'The batch ID must be a valid UUID.',
            'batch_id.exists'    => 'The selected batch does not exist.',

            'tank_id.required'   => 'The tank ID is required.',
            'tank_id.uuid'       => 'The tank ID must be a valid UUID.',
            'tank_id.exists'     => 'The selected tank does not exist.',

            'harvest_date.required' => 'The harvest date is required.',
            'harvest_date.date'     => 'The harvest date must be a valid date.',

            'type.required' => 'The harvest type is required.',
            'type.enum'     => 'Invalid harvest type. Allowed: total, partial, selective, emergency.',

            'status.required' => 'The harvest status is required.',
            'status.enum'     => 'Invalid status. Allowed: completed, scheduled, in_progress, cancelled.',

            'destination.required' => 'The destination is required.',
            'destination.enum'     => 'Invalid destination. Allowed: wholesale, retail, processing, restaurant, live_market, internal.',

            'initial_population.required' => 'The initial population is required.',
            'initial_population.integer'  => 'The initial population must be an integer.',

            'harvested_quantity.required' => 'The harvested quantity is required.',
            'harvested_quantity.integer'  => 'The harvested quantity must be an integer.',

            'average_weight.required' => 'The average weight is required.',
            'average_weight.numeric'  => 'The average weight must be a number.',

            'size_classifications.required'                   => 'At least one size classification is required.',
            'size_classifications.*.class.required'           => 'Each classification must have a class.',
            'size_classifications.*.quantity.required'        => 'Each classification must have a quantity.',
            'size_classifications.*.average_weight.required'  => 'Each classification must have an average weight.',
            'size_classifications.*.price_per_kg.required'    => 'Each classification must have a price per kg.',
        ];
    }
}
