<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $batcheID
 * @property Carbon|null $feeding_date
 * @property float $quantity_provided
 * @property string $feed_type
 * @property float $stock_reduction_quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Batche|null $batche
 */
class Feeding extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'batche_id',
        'feeding_date',
        'quantity_provided',
        'feed_type',
        'stock_reduction_quantity',
    ];

    /** @var array<string> */
    protected $dates = [
        'feeding_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted()
    {
        static::creating(function (Feeding $feeding): void {
            $feeding->id = (string) Str::uuid();
        });
    }

    /**
     * @phpstan-return BelongsTo<Batche, static>
     */
    public function batche(): BelongsTo
    {
        /** @var BelongsTo<Batche, static> $relation */
        $relation = $this->belongsTo(Batche::class, 'batche_id');

        return $relation;
    }
}
