<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $sensor_id
 * @property float $value
 * @property Carbon|null $reading_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Sensor|null $sensor
 */
class SensorReading extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'sensor_id',
        'reading_date',
        'value',
    ];

    /** @var array<string> */
    protected $dates = [
        'reading_date',
        'created_at',
        'updated_at',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (SensorReading $reading): void {
            $reading->id = (string) Str::uuid();
        });
    }

    /**
     * @phpstan-return BelongsTo<Sensor, static>
     */
    public function sensor(): BelongsTo
    {
        /** @var BelongsTo<Sensor, static> $relation */
        $relation = $this->belongsTo(Sensor::class, 'sensor_id');

        return $relation;
    }
}
