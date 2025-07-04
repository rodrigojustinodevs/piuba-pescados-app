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
 * @property float $average_weight
 * @property Carbon|null $deleted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Batche|null $batche
 */
class GrowthCurve extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'batche_id',
        'average_weight',
    ];

    /** @var array<string> */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (GrowthCurve $growthCurve): void {
            $growthCurve->id = (string) Str::uuid();
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
