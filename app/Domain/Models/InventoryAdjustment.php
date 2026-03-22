<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\InventoryAdjustmentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string  $id
 * @property string  $stock_id
 * @property string  $company_id
 * @property string  $user_id
 * @property float   $previous_quantity
 * @property float   $new_quantity
 * @property float   $adjusted_quantity    Delta (positivo ou negativo)
 * @property string  $unit
 * @property float   $unit_price           PMP no momento do ajuste (snapshot)
 * @property string  $status
 * @property string|null $reason
 * @property string|null $reference_transaction_id
 *
 * @property-read Stock            $stock
 * @property-read User             $user
 * @property-read StockTransaction $transaction
 */
class InventoryAdjustment extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'stock_id',
        'company_id',
        'user_id',
        'previous_quantity',
        'new_quantity',
        'adjusted_quantity',
        'unit',
        'unit_price',
        'status',
        'reason',
        'reference_transaction_id',
    ];

    protected $casts = [
        'previous_quantity' => 'float',
        'new_quantity'      => 'float',
        'adjusted_quantity' => 'float',
        'unit_price'        => 'decimal:2',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (InventoryAdjustment $model): void {
            $model->id ??= (string) Str::uuid();
            $model->status ??= InventoryAdjustmentStatus::PENDING->value;
        });
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(StockTransaction::class, 'reference_transaction_id');
    }

    public function isLoss(): bool
    {
        return $this->adjusted_quantity < 0;
    }

    public function isGain(): bool
    {
        return $this->adjusted_quantity > 0;
    }

    public function currentStatus(): InventoryAdjustmentStatus
    {
        return InventoryAdjustmentStatus::from($this->status);
    }
}
