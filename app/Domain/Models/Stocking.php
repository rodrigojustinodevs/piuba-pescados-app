<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\StockingStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Stocking (povoamento/estocagem) – aquaculture term for introducing organisms into a batch.
 *
 * @property string          $id
 * @property string          $batch_id
 * @property Carbon|null     $stocking_date
 * @property int             $quantity
 * @property float           $average_weight
 * @property float           $accumulated_fixed_cost
 * @property StockingStatus  $status
 * @property Carbon|null     $closed_at
 * @property Carbon|null     $created_at
 * @property Carbon|null     $updated_at
 * @property-read Batch|null $batch
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
        'average_weight',
        'accumulated_fixed_cost',
        'status',
        'closed_at',
    ];

    /** @var array<string> */
    protected $dates = [
        'stocking_date',
        'closed_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'status'    => StockingStatus::class,
        'closed_at' => 'datetime',
    ];

    #[\Override]
    protected static function booted()
    {
        static::creating(function (Stocking $stocking): void {
            $stocking->id ??= (string) Str::uuid();
            $stocking->status ??= StockingStatus::ACTIVE;
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
     * @phpstan-return BelongsTo<Batch, static>
     */
    public function batch(): BelongsTo
    {
        /** @var BelongsTo<Batch, static> $relation */
        $relation = $this->belongsTo(Batch::class, 'batch_id');

        return $relation;
    }
}
