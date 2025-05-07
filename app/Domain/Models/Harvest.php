<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $batche_id
 * @property float $total_weight
 * @property float $price_per_kg
 * @property float $total_revenue
 * @property Carbon|null $harvest_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Batche|null $batch
 */
class Harvest extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'batche_id',
        'total_weight',
        'price_per_kg',
        'total_revenue',
        'harvest_date',
    ];

    /** @var array<string> */
    protected $dates = [
        'harvest_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (Harvest $harvest): void {
            $harvest->id = (string) Str::uuid();
        });
    }

    /**
     * @phpstan-return BelongsTo<Batche, static>
     */
    public function batche(): BelongsTo
    {
        /** @var BelongsTo<Batche, static> $relation */
        $relation = $this->belongsTo(Batche::class, 'batche_id');

        return $relation;
    }
}
