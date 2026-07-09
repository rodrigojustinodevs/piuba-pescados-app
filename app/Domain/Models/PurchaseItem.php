<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $purchase_id
 * @property string $supply_id
 * @property float  $quantity
 * @property float  $received_quantity
 * @property string $unit
 * @property float  $unit_price
 * @property float  $total_price
 *
 * @property-read Purchase $purchase
 */
class PurchaseItem extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'purchase_id',
        'supply_id',
        'quantity',
        'received_quantity',
        'unit',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity'          => 'float',
        'received_quantity' => 'float',
        'unit_price'        => 'decimal:2',
        'total_price'       => 'decimal:2',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (PurchaseItem $item): void {
            $item->id ??= (string) Str::uuid();
        });

        // Garante que total_price é sempre calculado no backend
        static::saving(static function (PurchaseItem $item): void {
            $item->total_price = round(
                (float) $item->quantity * (float) $item->unit_price,
                2,
            );
        });
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }

    public function getPendingQuantity(): float
    {
        return max(0.0, (float) $this->quantity - (float) $this->received_quantity);
    }

    public function canReceive(float $amount): bool
    {
        return $amount > 0 && ($this->received_quantity + $amount) <= $this->quantity;
    }

    public function receive(float $amount): void
    {
        $this->received_quantity = (float) $this->received_quantity + $amount;
        $this->save();
    }
}
