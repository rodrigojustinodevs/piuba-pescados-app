<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string           $id
 * @property string           $sales_order_id
 * @property string           $stocking_id
 * @property string           $quantity
 * @property string           $unit_price
 * @property string           $subtotal
 * @property string           $measure_unit
 * @property-read SalesOrder|null $salesOrder
 * @property-read Stocking|null      $stocking
 * @property Carbon|null      $created_at
 * @property Carbon|null      $updated_at
 */
class SalesOrderItem extends BaseModel
{
    protected $table = 'sales_order_items';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'sales_order_id',
        'stocking_id',
        'quantity',
        'unit_price',
        'subtotal',
        'measure_unit',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'quantity'   => 'decimal:3',
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (SalesOrderItem $item): void {
            $item->id ??= (string) Str::uuid();
        });
    }

    /** @phpstan-return BelongsTo<SalesOrder, static> */
    public function salesOrder(): BelongsTo
    {
        /** @var BelongsTo<SalesOrder, static> $relation */
        $relation = $this->belongsTo(SalesOrder::class, 'sales_order_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<Stocking, static> */
    public function stocking(): BelongsTo
    {
        /** @var BelongsTo<Stocking, static> $relation */
        $relation = $this->belongsTo(Stocking::class, 'stocking_id');

        return $relation;
    }
}
