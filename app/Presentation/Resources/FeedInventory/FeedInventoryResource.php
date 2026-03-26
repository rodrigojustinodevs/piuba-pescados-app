<?php

declare(strict_types=1);

namespace App\Presentation\Resources\FeedInventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="FeedInventoryResource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="feedType", type="string"),
 *     @OA\Property(property="currentStock", type="number", format="float"),
 *     @OA\Property(property="minimumStock", type="number", format="float"),
 *     @OA\Property(property="dailyConsumption", type="number", format="float"),
 *     @OA\Property(property="totalConsumption", type="number", format="float"),
 *     @OA\Property(property="company", type="object",
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 *
 * @property-read string                           $id
 * @property-read string                           $feed_type
 * @property-read float                            $current_stock
 * @property-read float                            $minimum_stock
 * @property-read float                            $daily_consumption
 * @property-read float                            $total_consumption
 * @property-read \Illuminate\Support\Carbon|null  $created_at
 * @property-read \Illuminate\Support\Carbon|null  $updated_at
 * @property-read \App\Domain\Models\Company|null  $company
 */
final class FeedInventoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'feedType'         => $this->feed_type,
            'currentStock'     => $this->current_stock,
            'minimumStock'     => $this->minimum_stock,
            'dailyConsumption' => $this->daily_consumption,
            'totalConsumption' => $this->total_consumption,
            'company'          => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
