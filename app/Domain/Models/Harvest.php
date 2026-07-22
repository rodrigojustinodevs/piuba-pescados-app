<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\HarvestDestination;
use App\Domain\Enums\HarvestStatus;
use App\Domain\Enums\HarvestType;
use App\Infrastructure\Persistence\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string                  $id
 * @property string                  $batch_id
 * @property string|null             $tank_id
 * @property Carbon|null             $harvest_date
 * @property HarvestType             $type
 * @property HarvestStatus           $status
 * @property HarvestDestination|null $destination
 * @property int                     $initial_population
 * @property int                     $harvested_quantity
 * @property float                   $average_weight
 * @property float                   $total_weight
 * @property float                   $price_per_kg
 * @property float                   $total_revenue
 * @property float                   $operational_cost
 * @property string|null             $client_destination
 * @property string|null             $responsible
 * @property string|null             $notes
 * @property Carbon|null             $created_at
 * @property Carbon|null             $updated_at
 * @property Carbon|null             $deleted_at
 *
 * @property-read Batch|null $batch
 * @property-read Tank|null  $tank
 * @property-read \Illuminate\Database\Eloquent\Collection<int, HarvestSizeClassification> $sizeClassifications
 */
class Harvest extends BaseModel
{
    use HasCompanyScope;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'batch_id',
        'tank_id',
        'harvest_date',
        'type',
        'status',
        'destination',
        'initial_population',
        'harvested_quantity',
        'average_weight',
        'total_weight',
        'price_per_kg',
        'total_revenue',
        'operational_cost',
        'client_destination',
        'responsible',
        'notes',
    ];

    /** @var array<string> */
    protected $dates = [
        'harvest_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'type'        => HarvestType::class,
        'status'      => HarvestStatus::class,
        'destination' => HarvestDestination::class,
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (Harvest $harvest): void {
            $harvest->id = (string) Str::uuid();
        });
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
     * @phpstan-return BelongsTo<Tank, static>
     */
    public function tank(): BelongsTo
    {
        /** @var BelongsTo<Tank, static> $relation */
        $relation = $this->belongsTo(Tank::class, 'tank_id');

        return $relation;
    }

    /**
     * @phpstan-return HasMany<HarvestSizeClassification, static>
     */
    public function sizeClassifications(): HasMany
    {
        /** @var HasMany<HarvestSizeClassification, static> $relation */
        $relation = $this->hasMany(HarvestSizeClassification::class, 'harvest_id');

        return $relation;
    }

    public function survivalRate(): float
    {
        if ($this->initial_population === 0) {
            return 0.0;
        }

        return round(($this->harvested_quantity / $this->initial_population) * 100, 1);
    }

    public function netProfit(): float
    {
        return round($this->total_revenue - $this->operational_cost, 2);
    }
}
