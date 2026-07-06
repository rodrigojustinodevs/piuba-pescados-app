<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string      $id
 * @property string      $sale_id
 * @property string      $batch_id
 * @property string      $stocking_id
 * @property string|null $product_name
 * @property string|null $species
 * @property string|null $category
 * @property float       $total_weight
 * @property float       $price_per_kg
 * @property float       $subtotal
 * @property float       $unit_cost
 * @property float       $total_cost
 * @property bool        $is_total_harvest
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SaleItem extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'sale_id',
        'batch_id',
        'stocking_id',
        'product_name',
        'species',
        'category',
        'total_weight',
        'price_per_kg',
        'subtotal',
        'unit_cost',
        'total_cost',
        'is_total_harvest',
        'notes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'total_weight'    => 'float',
        'price_per_kg'    => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'unit_cost'       => 'decimal:4',
        'total_cost'      => 'decimal:2',
        'is_total_harvest'=> 'boolean',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (SaleItem $item): void {
            $item->id ??= (string) Str::uuid();
        });
    }

    /** @phpstan-return BelongsTo<Sale, static> */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    /** @phpstan-return BelongsTo<Batch, static> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    /** @phpstan-return BelongsTo<Stocking, static> */
    public function stocking(): BelongsTo
    {
        return $this->belongsTo(Stocking::class, 'stocking_id');
    }
}
