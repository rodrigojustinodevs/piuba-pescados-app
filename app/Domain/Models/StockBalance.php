<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $stock_id
 * @property string $supply_id
 * @property float  $quantity
 *
 * @property-read Stock  $stock
 * @property-read Supply $supply
 */
class StockBalance extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'stock_id',
        'supply_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (StockBalance $balance): void {
            $balance->id ??= (string) Str::uuid();
        });
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }
}
