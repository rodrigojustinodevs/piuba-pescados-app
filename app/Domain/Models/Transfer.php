<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string              $id
 * @property string              $batch_id
 * @property string              $origin_tank_id
 * @property string              $destination_tank_id
 * @property int                 $quantity
 * @property string              $description
 * @property Carbon|null         $created_at
 * @property Carbon|null         $updated_at
 * @property Carbon|null         $deleted_at
 * @property-read Batch|null     $batch
 * @property-read Tank|null      $originTank
 * @property-read Tank|null      $destinationTank
 */
class Transfer extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'batch_id',
        'origin_tank_id',
        'destination_tank_id',
        'quantity',
        'description',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Transfer $transfer): void {
            $transfer->id ??= (string) Str::uuid();
        });
    }

    /**
     * @phpstan-return BelongsTo<Batch, static>
     */
    public function batch(): BelongsTo
    {
        /** @var BelongsTo<Batch, static> $relation */
        $relation = $this->belongsTo(Batch::class, 'batch_id');

        return $relation;
    }

    /**
     * @phpstan-return BelongsTo<Tank, static>
     */
    public function originTank(): BelongsTo
    {
        /** @var BelongsTo<Tank, static> $relation */
        $relation = $this->belongsTo(Tank::class, 'origin_tank_id');

        return $relation;
    }

    /**
     * @phpstan-return BelongsTo<Tank, static>
     */
    public function destinationTank(): BelongsTo
    {
        /** @var BelongsTo<Tank, static> $relation */
        $relation = $this->belongsTo(Tank::class, 'destination_tank_id');

        return $relation;
    }
}
