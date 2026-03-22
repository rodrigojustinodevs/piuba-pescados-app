<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string      $id
 * @property string      $company_id
 * @property string      $supply_id
 * @property string|null $supplier_id
 * @property float       $current_quantity
 * @property string      $unit
 * @property float       $unit_price
 * @property float       $minimum_stock
 * @property float       $withdrawal_quantity
 *
 * @property-read Supply    $supply
 * @property-read Supplier|null $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StockTransaction> $transactions
 */
class Stock extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'supply_id',
        'supplier_id',
        'current_quantity',
        'unit',
        'unit_price',
        'minimum_stock',
        'withdrawal_quantity',
    ];

    protected $casts = [
        'current_quantity'    => 'float',
        'unit_price'          => 'decimal:2',
        'minimum_stock'       => 'float',
        'withdrawal_quantity' => 'float',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Stock $stock): void {
            $stock->id ??= (string) Str::uuid();
        });
    }

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class, 'stock_id');
    }

    // -------------------------------------------------------------------------
    // Helpers de leitura
    // -------------------------------------------------------------------------

    public function isBelowMinimum(): bool
    {
        return $this->current_quantity < $this->minimum_stock;
    }
}
