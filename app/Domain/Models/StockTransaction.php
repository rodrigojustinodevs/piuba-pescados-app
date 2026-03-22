<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string      $id
 * @property string      $stock_id
 * @property string      $company_id
 * @property string|null $supplier_id
 * @property string|null $reference_id
 * @property string|null $reference_type
 * @property float       $quantity
 * @property float       $unit_price
 * @property float       $total_cost
 * @property string      $unit
 * @property string      $direction
 *
 * @property-read Stock         $stock
 * @property-read Supplier|null $supplier
 */
class StockTransaction extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    // Transações são imutáveis — sem SoftDeletes, sem update
    public $timestamps = true;

    protected $fillable = [
        'id',
        'stock_id',
        'company_id',
        'supplier_id',
        'reference_id',
        'reference_type',
        'quantity',
        'unit_price',
        'total_cost',
        'unit',
        'direction',
    ];

    protected $casts = [
        'quantity'   => 'float',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (StockTransaction $t): void {
            $t->id ??= (string) Str::uuid();
        });
    }

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function direction(): StockTransactionDirection
    {
        return StockTransactionDirection::from($this->direction);
    }

    public function referenceType(): ?StockTransactionReferenceType
    {
        return $this->reference_type
            ? StockTransactionReferenceType::from($this->reference_type)
            : null;
    }

    public function isEntry(): bool
    {
        return $this->direction()->isIn();
    }
}
