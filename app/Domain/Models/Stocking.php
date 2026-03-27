<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\StockingStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Stocking (povoamento/estocagem) – aquaculture term for introducing organisms into a batch.
 *
 * @property string              $id
 * @property string              $batch_id
 * @property \Carbon\Carbon|null $stocking_date
 * @property int                 $quantity
 * @property int|null            $current_quantity
 * @property float               $average_weight
 * @property float|null          $estimated_biomass
 * @property float               $accumulated_fixed_cost
 * @property StockingStatus      $status
 * @property \Carbon\Carbon|null $closed_at
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Batch|null $batch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StockingHistory> $histories
 */
class Stocking extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'stockings';

    protected $fillable = [
        'id',
        'batch_id',
        'stocking_date',
        'quantity',
        'current_quantity',
        'average_weight',
        'estimated_biomass',
        'accumulated_fixed_cost',
        'status',
        'closed_at',
    ];

    protected $casts = [
        'stocking_date'          => 'date:Y-m-d',
        'closed_at'              => 'datetime',
        'status'                 => StockingStatus::class,
        'quantity'               => 'integer',
        'current_quantity'       => 'integer',
        'average_weight'         => 'decimal:4',
        'estimated_biomass'      => 'decimal:4',
        'accumulated_fixed_cost' => 'decimal:4',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Stocking $stocking): void {
            $stocking->id ??= (string) Str::uuid();
            $stocking->status ??= StockingStatus::ACTIVE;
            $stocking->current_quantity ??= $stocking->quantity;
            $stocking->estimated_biomass ??= $stocking->initialBiomass();
        });
    }

    public function initialBiomass(): float
    {
        return (float) $this->quantity * (float) $this->average_weight;
    }

    public function isClosed(): bool
    {
        return $this->status === StockingStatus::CLOSED;
    }

    /**
     * Marks the stocking as closed (total harvest completed).
     * Updates status and sets closed_at timestamp, then persists immediately.
     */
    public function markAsClosed(): void
    {
        $this->update([
            'status'    => StockingStatus::CLOSED,
            'closed_at' => now(),
        ]);
    }

    /**
     * Calculates the current unit cost (R$/kg) based on accumulated allocated costs
     * and the remaining biomass after sold weight is deducted.
     *
     * Unit cost = accumulated_fixed_cost / remaining_biomass
     *
     * @param float $soldWeight Total weight (kg) already sold/committed from this stocking.
     */
    public function calculateCurrentUnitCost(float $soldWeight = 0.0): float
    {
        $remaining = max(0.001, $this->initialBiomass() - $soldWeight);

        return round((float) $this->accumulated_fixed_cost / $remaining, 4);
    }

    /**
     * Calculates the attributes to update after a biometry reading.
     * Persistence is the caller's responsibility (UseCase → Repository).
     *
     * @return array{average_weight: float, estimated_biomass: float}
     */
    public function biometryAttributes(float $averageWeight): array
    {
        $currentQty = (int) ($this->current_quantity ?? $this->quantity);

        return [
            'average_weight'    => $averageWeight,
            'estimated_biomass' => round($currentQty * $averageWeight, 4),
        ];
    }

    /**
     * Calculates the attributes to update after registering mortality losses.
     * Persistence is the caller's responsibility (UseCase → Repository).
     *
     * @return array{current_quantity: int, estimated_biomass: float}
     */
    public function mortalityAttributes(int $quantity): array
    {
        $currentQty = max(0, (int) ($this->current_quantity ?? $this->quantity) - $quantity);
        $avgWeight  = (float) $this->average_weight;

        return [
            'current_quantity'  => $currentQty,
            'estimated_biomass' => round($currentQty * $avgWeight, 4),
        ];
    }

    /**
     * @phpstan-return BelongsTo<Batch, static>
     */
    public function batch(): BelongsTo
    {
        /** @var BelongsTo<Batch, static> $relation */
        $relation = $this->belongsTo(Batch::class, 'batch_id');

        return $relation;
    }

    /**
     * @phpstan-return HasMany<StockingHistory, static>
     */
    public function histories(): HasMany
    {
        /** @var HasMany<StockingHistory, static> $relation */
        $relation = $this->hasMany(StockingHistory::class, 'stocking_id');

        return $relation;
    }
}
