<?php

declare(strict_types=1);

namespace App\Presentation\Resources\GrowthCurve;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="GrowthCurveResource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="batchId", type="string", format="uuid"),
 *     @OA\Property(property="averageWeight", type="number", format="float"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 *
 * @property-read string                           $id
 * @property-read string                           $batch_id
 * @property-read float                            $average_weight
 * @property-read \Illuminate\Support\Carbon|null  $created_at
 * @property-read \Illuminate\Support\Carbon|null  $updated_at
 * @property-read \App\Domain\Models\Batch|null    $batch
 */
final class GrowthCurveResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'batchId'       => $this->batch_id,
            'averageWeight' => $this->average_weight,
            'createdAt'     => $this->created_at?->toDateTimeString(),
            'updatedAt'     => $this->updated_at?->toDateTimeString(),
        ];
    }
}
