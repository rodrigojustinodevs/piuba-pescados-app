<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\ValueObjects\WaterQualityThresholds;
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
 * @property string $quality
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Tank|null $tank
 * @property-read Company|null $company
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
        'quality',
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
        static::creating(function (WaterQuality $record): void {
            $record->id = (string) Str::uuid();
        });

        static::saving(function (WaterQuality $record): void {
            $record->quality = WaterQualityThresholds::quality(
                ph:              $record->ph               !== null ? (float) $record->ph               : null,
                dissolvedOxygen: $record->dissolved_oxygen !== null ? (float) $record->dissolved_oxygen : null,
                ammonia:         $record->ammonia          !== null ? (float) $record->ammonia          : null,
                temperature:     $record->temperature      !== null ? (float) $record->temperature      : null,
            )->value;
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

    /**
     * @phpstan-return BelongsTo<Company, static>
     */
    public function company(): BelongsTo
    {
        /** @var BelongsTo<Company, static> $relation */
        $relation = $this->belongsTo(Company::class, 'company_id');

        return $relation;
    }
}
