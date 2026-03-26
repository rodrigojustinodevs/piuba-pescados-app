<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string              $id
 * @property string              $batch_id
 * @property \Carbon\Carbon|null $feeding_date
 * @property float               $quantity_provided
 * @property string              $feed_type
 * @property string|null         $stock_id
 * @property float               $stock_reduction_quantity
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Batch|null $batch
 * @property-read Stock|null $stock
 */
class Feeding extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'batch_id',
        'feeding_date',
        'quantity_provided',
        'feed_type',
        'stock_id',
        'stock_reduction_quantity',
    ];

    protected $casts = [
        'feeding_date'             => 'date:Y-m-d',
        'quantity_provided'        => 'decimal:2',
        'stock_reduction_quantity' => 'decimal:2',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Feeding $feeding): void {
            $feeding->id ??= (string) Str::uuid();
        });
    }

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

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
     * @phpstan-return BelongsTo<Stock, static>
     */
    public function stock(): BelongsTo
    {
        /** @var BelongsTo<Stock, static> $relation */
        $relation = $this->belongsTo(Stock::class, 'stock_id');

        return $relation;
    }
}
