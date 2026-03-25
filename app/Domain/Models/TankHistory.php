<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\TankHistoryEvent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string            $id
 * @property string            $company_id
 * @property string            $tank_id
 * @property TankHistoryEvent  $event
 * @property Carbon            $event_date
 * @property string|null       $description
 * @property string|null       $performed_by
 * @property Carbon|null       $created_at
 * @property Carbon|null       $updated_at
 * @property-read Tank|null    $tank
 * @property-read Company|null $company
 */
class TankHistory extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'tank_histories';

    protected $fillable = [
        'id',
        'company_id',
        'tank_id',
        'event',
        'event_date',
        'description',
        'performed_by',
    ];

    /** @var array<string> */
    protected $dates = [
        'event_date',
        'created_at',
        'updated_at',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'event'      => TankHistoryEvent::class,
        'event_date' => 'date',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (TankHistory $history): void {
            $history->id ??= (string) Str::uuid();
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
