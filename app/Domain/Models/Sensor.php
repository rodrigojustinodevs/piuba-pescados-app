<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string            $id
 * @property string            $tank_id
 * @property string            $company_id
 * @property string            $sensor_type
 * @property string            $status
 * @property Carbon|null       $installation_date
 * @property string|null       $notes
 *
 * @property-read Tank|null    $tank
 * @property-read Company|null $company
 */
class Sensor extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tank_id',
        'company_id',
        'sensor_type',
        'status',
        'installation_date',
        'notes',
    ];

    protected $casts = [
        'installation_date' => 'date',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Sensor $sensor): void {
            $sensor->id ??= (string) Str::uuid();
        });
    }

    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class, 'tank_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
