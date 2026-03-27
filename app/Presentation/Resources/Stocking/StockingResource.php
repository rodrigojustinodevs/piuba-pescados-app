<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Stocking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="StockingResource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="batchId", type="string", format="uuid"),
 *     @OA\Property(property="batch", type="object",
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string", nullable=true)
 *     ),
 *     @OA\Property(property="stockingDate", type="string", format="date"),
 *     @OA\Property(property="quantity", type="integer"),
 *     @OA\Property(property="currentQuantity", type="integer", nullable=true),
 *     @OA\Property(property="averageWeight", type="number", format="float"),
 *     @OA\Property(property="estimatedBiomass", type="number", format="float", nullable=true),
 *     @OA\Property(property="accumulatedFixedCost", type="number", format="float"),
 *     @OA\Property(property="status", type="string", enum={"active","closed"}),
 *     @OA\Property(property="closedAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 *
 * @property-read string                           $id
 * @property-read string                           $batch_id
 * @property-read int                              $quantity
 * @property-read int|null                         $current_quantity
 * @property-read float                            $average_weight
 * @property-read float|null                       $estimated_biomass
 * @property-read float                            $accumulated_fixed_cost
 * @property-read \App\Domain\Enums\StockingStatus $status
 * @property-read \Illuminate\Support\Carbon|null  $stocking_date
 * @property-read \Illuminate\Support\Carbon|null  $closed_at
 * @property-read \Illuminate\Support\Carbon|null  $created_at
 * @property-read \Illuminate\Support\Carbon|null  $updated_at
 * @property-read \App\Domain\Models\Batch|null      $batch
 */
final class StockingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        $stockingDate = $this->stocking_date;

        if ($stockingDate instanceof \DateTimeInterface) {
            $stockingDate = $stockingDate->format('Y-m-d');
        }

        return [
            'id'    => $this->id,
            'batch' => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
            'stockingDate'         => $stockingDate,
            'quantity'             => $this->quantity,
            'currentQuantity'      => $this->current_quantity,
            'averageWeight'        => $this->average_weight,
            'estimatedBiomass'     => $this->estimated_biomass,
            'accumulatedFixedCost' => $this->accumulated_fixed_cost,
            'status'               => $this->status->value,
            'closedAt'             => $this->closed_at?->toDateTimeString(),
            'createdAt'            => $this->created_at?->toDateTimeString(),
            'updatedAt'            => $this->updated_at?->toDateTimeString(),
        ];
    }
}
