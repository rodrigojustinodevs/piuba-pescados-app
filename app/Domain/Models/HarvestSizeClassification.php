<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $harvest_id
 * @property string $class
 * @property int    $quantity
 * @property float  $average_weight
 * @property float  $price_per_kg
 *
 * @property-read Harvest $harvest
 */
class HarvestSizeClassification extends BaseModel
{
    public $timestamps = true;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'harvest_id',
        'class',
        'quantity',
        'average_weight',
        'price_per_kg',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (HarvestSizeClassification $model): void {
            $model->id = (string) Str::uuid();
        });
    }

    /**
     * @phpstan-return BelongsTo<Harvest, static>
     */
    public function harvest(): BelongsTo
    {
        /** @var BelongsTo<Harvest, static> $relation */
        $relation = $this->belongsTo(Harvest::class, 'harvest_id');

        return $relation;
    }

    public function totalWeight(): float
    {
        return round(($this->quantity * $this->average_weight) / 1000, 3);
    }

    public function revenue(): float
    {
        return round($this->totalWeight() * $this->price_per_kg, 2);
    }
}
