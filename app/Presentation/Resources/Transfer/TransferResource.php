<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Transfer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TransferResource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="batchId", type="string", format="uuid"),
 *     @OA\Property(property="originTankId", type="string", format="uuid"),
 *     @OA\Property(property="destinationTankId", type="string", format="uuid"),
 *     @OA\Property(property="quantity", type="integer", example=1000),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(
 *         property="batch",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="originTank",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="destinationTank",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 *
 * @property-read string $id
 * @property-read string $batch_id
 * @property-read string $origin_tank_id
 * @property-read string $destination_tank_id
 * @property-read int $quantity
 * @property-read string $description
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Batch|null $batch
 * @property-read \App\Domain\Models\Tank|null $originTank
 * @property-read \App\Domain\Models\Tank|null $destinationTank
 */
final class TransferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'batchId'           => $this->batch_id,
            'originTankId'      => $this->origin_tank_id,
            'destinationTankId' => $this->destination_tank_id,
            'batch'             => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
            'originTank' => $this->whenLoaded('originTank', fn (): array => [
                'id'   => $this->originTank->id,
                'name' => $this->originTank->name,
            ]),
            'destinationTank' => $this->whenLoaded('destinationTank', fn (): array => [
                'id'   => $this->destinationTank->id,
                'name' => $this->destinationTank->name,
            ]),
            'quantity'    => $this->quantity,
            'description' => $this->description,
            'createdAt'   => $this->created_at?->toDateTimeString(),
            'updatedAt'   => $this->updated_at?->toDateTimeString(),
        ];
    }
}
