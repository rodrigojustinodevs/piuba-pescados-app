<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\StockingHistoryEvent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string                $id
 * @property string                $company_id
 * @property string                $stocking_id
 * @property StockingHistoryEvent  $event
 * @property Carbon                $event_date
 * @property int|null              $quantity
 * @property float|null            $average_weight
 * @property string|null           $notes
 * @property Carbon|null           $created_at
 * @property Carbon|null           $updated_at
 * @property-read Stocking|null    $stocking
 * @property-read Company|null     $company
 */
class StockingHistory extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'stocking_histories';

    protected $fillable = [
        'id',
        'company_id',
        'stocking_id',
        'event',
        'event_date',
        'quantity',
        'average_weight',
        'notes',
    ];

    /** @var array<string> */
    protected $dates = [
        'event_date',
        'created_at',
        'updated_at',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'event'          => StockingHistoryEvent::class,
        'event_date'     => 'date',
        'quantity'       => 'integer',
        'average_weight' => 'float',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (StockingHistory $history): void {
            $history->id ??= (string) Str::uuid();
        });
    }

    /**
     * @phpstan-return BelongsTo<Stocking, static>
     */
    public function stocking(): BelongsTo
    {
        /** @var BelongsTo<Stocking, static> $relation */
        $relation = $this->belongsTo(Stocking::class, 'stocking_id');

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
