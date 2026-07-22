<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\StockStatusEnum;
use App\Domain\Enums\StockTypeEnum;
use App\Infrastructure\Persistence\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string           $id
 * @property string           $company_id
 * @property string|null      $supply_id
 * @property string|null      $supplier_id
 * @property string|null      $code
 * @property string|null      $name
 * @property StockTypeEnum|null   $type
 * @property string|null      $location
 * @property string|null      $responsible
 * @property float|null       $capacity
 * @property StockStatusEnum  $status
 * @property string|null      $notes
 * @property float            $current_quantity
 * @property string           $unit
 * @property float            $unit_price
 * @property float            $minimum_stock
 * @property float            $withdrawal_quantity
 *
 * @property-read Supply|null                                                        $supply
 * @property-read Supplier|null                                                      $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StockTransaction>    $transactions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StockBalance>        $balances
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StockMovement>       $movements
 */
class Stock extends BaseModel
{
    use HasCompanyScope;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'supply_id',
        'supplier_id',
        'code',
        'name',
        'type',
        'location',
        'responsible',
        'capacity',
        'status',
        'notes',
        'current_quantity',
        'unit',
        'unit_price',
        'minimum_stock',
        'withdrawal_quantity',
    ];

    protected $casts = [
        'type'                => StockTypeEnum::class,
        'status'              => StockStatusEnum::class,
        'capacity'            => 'decimal:3',
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

    public function balances(): HasMany
    {
        return $this->hasMany(StockBalance::class, 'stock_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'stock_id');
    }

    // -------------------------------------------------------------------------
    // Helpers de leitura
    // -------------------------------------------------------------------------

    public function isBelowMinimum(): bool
    {
        return $this->current_quantity < $this->minimum_stock;
    }
}
