<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $tank_id
 * @property Carbon|null $analysis_date
 * @property float $ph
 * @property float $oxygen
 * @property float $temperature
 * @property float $ammonia
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Tank|null $tank
 */
class WaterQuality extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tank_id',
        'analysis_date',
        'ph',
        'oxygen',
        'temperature',
        'ammonia',
    ];

    /** @var array<string> */
    protected $dates = [
        'analysis_date',
        'created_at',
        'updated_at',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (WaterQuality $quality): void {
            $quality->id = (string) Str::uuid();
        });
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
}
