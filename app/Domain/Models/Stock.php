<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $company_id
 * @property string|null $supplier_id
 * @property float $current_quantity
 * @property float $unit_price
 * @property string $unit
 * @property float $minimum_stock
 * @property Carbon|null $updated_at
 * @property float $withdrawal_quantity
 * @property-read Company|null $company
 * @property-read Supplier|null $supplier
 */
class Stock extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'supplier_id',
        'current_quantity',
        'unit_price',
        'unit',
        'minimum_stock',
        'updated_at',
        'withdrawal_quantity',
    ];

    /** @var array<string> */
    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted()
    {
        static::creating(function (Stock $stock): void {
            $stock->id = (string) Str::uuid();
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
     * @phpstan-return BelongsTo<Supplier, static>
     */
    public function supplier(): BelongsTo
    {
        /** @var BelongsTo<Supplier, static> $relation */
        $relation = $this->belongsTo(Supplier::class, 'supplier_id');

        return $relation;
    }
}
