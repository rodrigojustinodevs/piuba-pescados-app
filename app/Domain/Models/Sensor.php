<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $tank_id
 * @property string $sensor_type
 * @property string $status
 * @property Carbon|null $installation_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Tank|null $tank
 */
class Sensor extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tank_id',
        'sensor_type',
        'installation_date',
        'status',
    ];

    /** @var array<string> */
    protected $dates = [
        'installation_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (Sensor $sensor): void {
            $sensor->id     = (string) Str::uuid();
            $sensor->status = 'active';
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
