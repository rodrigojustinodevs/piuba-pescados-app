<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Catálogo de insumos por empresa (ração, químicos, etc.).
 *
 * @property string $id
 * @property string $company_id
 * @property string $name
 * @property string|null $category
 * @property string $default_unit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Stock> $stocks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchaseItem> $purchaseItems
 */
class Supply extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'name',
        'category',
        'default_unit',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (Supply $supply): void {
            $supply->id = (string) Str::uuid();
        });
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
     * @phpstan-return HasMany<Stock, static>
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'supply_id');
    }

    /**
     * @phpstan-return HasMany<PurchaseItem, static>
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'supply_id');
    }
}
