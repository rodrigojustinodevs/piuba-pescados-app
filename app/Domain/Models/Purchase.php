<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $company_id
 * @property string $supplier_id
 * @property string $input_name
 * @property float $quantity
 * @property float $total_price
 * @property Carbon|null $purchase_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Company|null $company
 * @property-read Supplier|null $supplier
 */
class Purchase extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'supplier_id',
        'input_name',
        'quantity',
        'total_price',
        'purchase_date',
    ];

    /** @var array<string> */
    protected $dates = [
        'purchase_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (Purchase $purchase): void {
            $purchase->id = (string) Str::uuid();
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
