<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string                        $id
 * @property string                        $sensor_id
 * @property string                        $company_id
 * @property float                         $value
 * @property string                        $unit
 * @property \Illuminate\Support\Carbon|null $measured_at
 * @property string|null                   $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Sensor|null  $sensor
 * @property-read Company|null $company
 */
class SensorReading extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'sensor_id',
        'company_id',
        'value',
        'unit',
        'measured_at',
        'notes',
    ];

    protected $casts = [
        'value'       => 'float',
        'measured_at' => 'datetime',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (SensorReading $reading): void {
            $reading->id ??= (string) Str::uuid();
        });
    }

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class, 'sensor_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
