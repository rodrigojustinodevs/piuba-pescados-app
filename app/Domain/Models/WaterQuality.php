<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $tank_id
 * @property string $company_id
 * @property Carbon|null $measured_at
 * @property float $ph
 * @property float $dissolved_oxygen
 * @property float $temperature
 * @property float $ammonia
 * @property float $salinity
 * @property float $turbidity
 * @property string $notes
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
        'measured_at',
        'ph',
        'dissolved_oxygen',
        'temperature',
        'ammonia',
        'salinity',
        'turbidity',
        'notes',
    ];

    /** @var array<string> */
    protected $dates = [
        'measured_at',
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
