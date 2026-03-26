<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Biometry;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="BiometryResource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="batchId", type="string", format="uuid"),
 *     @OA\Property(property="batch", type="object",
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="biometryDate", type="string", format="date"),
 *     @OA\Property(property="averageWeight", type="number", format="float"),
 *     @OA\Property(property="fcr", type="number", format="float"),
 *     @OA\Property(property="sampleWeight", type="number", format="float", nullable=true),
 *     @OA\Property(property="sampleQuantity", type="integer", nullable=true),
 *     @OA\Property(property="biomassEstimated", type="number", format="float", nullable=true),
 *     @OA\Property(property="densityAtTime", type="number", format="float", nullable=true),
 *     @OA\Property(property="recommendedRation", type="number", format="float", nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 *
 * @property-read string                           $id
 * @property-read string                           $batch_id
 * @property-read float                            $average_weight
 * @property-read float                            $fcr
 * @property-read float|null                       $sample_weight
 * @property-read int|null                         $sample_quantity
 * @property-read float|null                       $biomass_estimated
 * @property-read float|null                       $density_at_time
 * @property-read float|null                       $recommended_ration
 * @property-read \Illuminate\Support\Carbon|null  $biometry_date
 * @property-read \Illuminate\Support\Carbon|null  $created_at
 * @property-read \Illuminate\Support\Carbon|null  $updated_at
 * @property-read \App\Domain\Models\Batch|null    $batch
 */
final class BiometryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        $biometryDate = $this->biometry_date;

        if ($biometryDate instanceof \DateTimeInterface) {
            $biometryDate = $biometryDate->format('Y-m-d');
        }

        return [
            'id'      => $this->id,
            'batchId' => $this->batch_id,
            'batch'   => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
            'biometryDate'      => $biometryDate,
            'averageWeight'     => $this->average_weight,
            'fcr'               => $this->fcr,
            'sampleWeight'      => $this->sample_weight,
            'sampleQuantity'    => $this->sample_quantity,
            'biomassEstimated'  => $this->biomass_estimated,
            'densityAtTime'     => $this->density_at_time,
            'recommendedRation' => $this->recommended_ration,
            'createdAt'         => $this->created_at?->toDateTimeString(),
            'updatedAt'         => $this->updated_at?->toDateTimeString(),
        ];
    }
}
