<?php

declare(strict_types=1);

namespace App\Presentation\Resources\CostAllocation;

use App\Domain\Enums\AllocationMethod;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                          $id
 * @property-read string                          $company_id
 * @property-read string                          $financial_transaction_id
 * @property-read AllocationMethod                $allocation_method
 * @property-read float                           $total_amount
 * @property-read string|null                     $notes
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Domain\Models\Company|null $company
 * @property-read \App\Domain\Models\FinancialTransaction|null $financialTransaction
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domain\Models\CostAllocationItem> $items
 */
class CostAllocationResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray($request): array
    {
        /** @var AllocationMethod $method */
        $method = $this->allocation_method;

        return [
            'id'                     => $this->id,
            'financialTransactionId' => $this->financial_transaction_id,
            'allocationMethod'       => $method->value,
            'allocationMethodLabel'  => $method->label(),
            'totalAmount'            => (float) $this->total_amount,
            'notes'                  => $this->notes,
            'company'                => $this->whenLoaded('company', fn (): array => [
                'id'   => $this->company->id,
                'name' => $this->company->name,
            ]),
            'financialTransaction' => $this->whenLoaded(
                'financialTransaction',
                fn (): ?array => $this->financialTransaction ? [
                    'id'          => $this->financialTransaction->id,
                    'description' => $this->financialTransaction->description,
                    'amount'      => (float) $this->financialTransaction->amount,
                    'dueDate'     => $this->financialTransaction->due_date->toDateString(),
                    'isAllocated' => $this->financialTransaction->isAllocated(),
                ] : null,
            ),
            'items' => $this->whenLoaded('items', fn (): array => $this->items->map(static fn ($item): array => [
                'id'         => $item->id,
                'stockingId' => $item->stocking_id,
                'percentage' => (float) $item->percentage,
                'amount'     => (float) $item->amount,
                'stocking'   => $item->relationLoaded('stocking') && $item->stocking
                    ? [
                        'id'                   => $item->stocking->id,
                        'quantity'             => $item->stocking->quantity,
                        'averageWeight'        => $item->stocking->average_weight,
                        'biomassKg'            => $item->stocking->initialBiomass(),
                        'accumulatedFixedCost' => (float) $item->stocking->accumulated_fixed_cost,
                    ]
                    : null,
            ])->all()),
            'createdAt' => $this->created_at?->toDateTimeString(),
        ];
    }
}
