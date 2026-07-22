<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\SupplyCategoryEnum;
use App\Domain\Enums\SupplyStatusEnum;
use App\Infrastructure\Persistence\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Catálogo de insumos por empresa (ração, químicos, etc.).
 *
 * @property string                $id
 * @property string                $company_id
 * @property string|null           $sku
 * @property string                $name
 * @property SupplyCategoryEnum    $category
 * @property string                $unit
 * @property float                 $unit_cost
 * @property float                 $sale_price
 * @property float                 $current_stock
 * @property float                 $min_stock
 * @property string|null           $supplier_id
 * @property bool                  $is_product
 * @property SupplyStatusEnum      $status
 * @property string|null           $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Company|null     $company
 * @property-read Supplier|null    $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Stock> $stocks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchaseItem> $purchaseItems
 */
class Supply extends BaseModel
{
    use HasCompanyScope;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'sku',
        'name',
        'category',
        'unit',
        'unit_cost',
        'sale_price',
        'current_stock',
        'min_stock',
        'supplier_id',
        'is_product',
        'status',
        'description',
    ];

    protected $casts = [
        'category'      => SupplyCategoryEnum::class,
        'status'        => SupplyStatusEnum::class,
        'is_product'    => 'boolean',
        'unit_cost'     => 'decimal:2',
        'sale_price'    => 'decimal:2',
        'current_stock' => 'decimal:3',
        'min_stock'     => 'decimal:3',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (Supply $supply): void {
            $supply->id = (string) Str::uuid();
        });

        static::saving(function (Supply $supply): void {
            $supply->resolveStatus();
        });
    }

    /**
     * Applies low-stock rule: sets status to low_stock when current_stock <= min_stock,
     * unless the supply is explicitly inactive.
     */
    public function resolveStatus(): void
    {
        if ($this->status === SupplyStatusEnum::INACTIVE) {
            return;
        }

        if ((float) $this->current_stock <= (float) $this->min_stock) {
            $this->status = SupplyStatusEnum::LOW_STOCK;
        } else {
            $this->status = SupplyStatusEnum::ACTIVE;
        }
    }

    /**
     * @phpstan-return BelongsTo<Company, static>
     */
    public function company(): BelongsTo
    {
        /** @var BelongsTo<Company, static> $relation */
        $relation = $this->belongsTo(Company::class, 'company_id');

        return $relation;
    }

    /**
     * @phpstan-return BelongsTo<Supplier, static>
     */
    public function supplier(): BelongsTo
    {
        /** @var BelongsTo<Supplier, static> $relation */
        $relation = $this->belongsTo(Supplier::class, 'supplier_id');

        return $relation;
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'supply_id');
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'supply_id');
    }
}
