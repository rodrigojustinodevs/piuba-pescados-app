<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\StockMovementTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string               $id
 * @property string               $stock_id
 * @property string               $supply_id
 * @property string               $user_id
 * @property StockMovementTypeEnum $type
 * @property float                $quantity
 * @property string|null          $reason
 *
 * @property-read Stock  $stock
 * @property-read Supply $supply
 */
class StockMovement extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'stock_id',
        'supply_id',
        'user_id',
        'type',
        'quantity',
        'reason',
    ];

    protected $casts = [
        'type'     => StockMovementTypeEnum::class,
        'quantity' => 'decimal:3',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (StockMovement $movement): void {
            $movement->id ??= (string) Str::uuid();
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
