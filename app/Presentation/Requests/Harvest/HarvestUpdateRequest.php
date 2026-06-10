<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Harvest;

use App\Domain\Enums\HarvestDestination;
use App\Domain\Enums\HarvestStatus;
use App\Domain\Enums\HarvestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HarvestUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'batchId'             => 'batch_id',
            'tankId'              => 'tank_id',
            'harvestDate'         => 'harvest_date',
            'initialPopulation'   => 'initial_population',
            'harvestedQuantity'   => 'harvested_quantity',
            'averageWeight'       => 'average_weight',
            'sizeClassifications' => 'size_classifications',
            'clientDestination'   => 'client_destination',
            'operationalCost'     => 'operational_cost',
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
            ?? null;

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
            'batch_id'            => ['sometimes', 'uuid', 'exists:batches,id'],
            'tank_id'             => ['sometimes', 'uuid', 'exists:tanks,id'],
            'harvest_date'        => ['sometimes', 'date'],
            'type'                => ['sometimes', Rule::enum(HarvestType::class)],
            'status'              => ['sometimes', Rule::enum(HarvestStatus::class)],
            'destination'         => ['sometimes', Rule::enum(HarvestDestination::class)],
            'initial_population'  => ['sometimes', 'integer', 'min:0'],
            'harvested_quantity'  => ['sometimes', 'integer', 'min:0'],
            'average_weight'      => ['sometimes', 'numeric', 'min:0'],

            'size_classifications'                           => ['sometimes', 'array', 'min:1'],
            'size_classifications.*.class'                   => ['required_with:size_classifications', 'string', 'max:10'],
            'size_classifications.*.quantity'                => ['required_with:size_classifications', 'integer', 'min:0'],
            'size_classifications.*.average_weight'          => ['required_with:size_classifications', 'numeric', 'min:0'],
            'size_classifications.*.price_per_kg'            => ['required_with:size_classifications', 'numeric', 'min:0'],

            'client_destination'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'responsible'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'operational_cost'    => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'notes'               => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batch_id.uuid'   => 'The batch ID must be a valid UUID.',
            'batch_id.exists' => 'The selected batch does not exist.',

            'tank_id.uuid'   => 'The tank ID must be a valid UUID.',
            'tank_id.exists' => 'The selected tank does not exist.',

            'harvest_date.date' => 'The harvest date must be a valid date.',

            'type.enum'        => 'Invalid harvest type. Allowed: total, partial, selective, emergency.',
            'status.enum'      => 'Invalid status. Allowed: completed, scheduled, in_progress, cancelled.',
            'destination.enum' => 'Invalid destination. Allowed: wholesale, retail, processing, restaurant, live_market, internal.',

            'size_classifications.*.class.required_with'          => 'Each classification must have a class.',
            'size_classifications.*.quantity.required_with'        => 'Each classification must have a quantity.',
            'size_classifications.*.average_weight.required_with'  => 'Each classification must have an average weight.',
            'size_classifications.*.price_per_kg.required_with'    => 'Each classification must have a price per kg.',
        ];
    }
}
