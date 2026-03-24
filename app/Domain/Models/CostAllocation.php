<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\AllocationMethod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string           $id
 * @property string           $company_id
 * @property string           $financial_transaction_id
 * @property AllocationMethod $allocation_method
 * @property float            $total_amount
 * @property string|null      $notes
 * @property Carbon|null      $created_at
 * @property Carbon|null      $updated_at
 * @property Carbon|null      $deleted_at
 *
 * @property-read Company|null                                                       $company
 * @property-read FinancialTransaction|null                                          $financialTransaction
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CostAllocationItem> $items
 */
class CostAllocation extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'financial_transaction_id',
        'allocation_method',
        'total_amount',
        'notes',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'allocation_method' => AllocationMethod::class,
        'total_amount'      => 'decimal:2',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (CostAllocation $allocation): void {
            $allocation->id ??= (string) Str::uuid();
        });
    }

    /** @phpstan-return BelongsTo<Company, static> */
    public function company(): BelongsTo
    {
        /** @var BelongsTo<Company, static> $relation */
        $relation = $this->belongsTo(Company::class, 'company_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<FinancialTransaction, static> */
    public function financialTransaction(): BelongsTo
    {
        /** @var BelongsTo<FinancialTransaction, static> $relation */
        $relation = $this->belongsTo(FinancialTransaction::class, 'financial_transaction_id');

        return $relation;
    }

    /** @phpstan-return HasMany<CostAllocationItem, static> */
    public function items(): HasMany
    {
        /** @var HasMany<CostAllocationItem, static> $relation */
        $relation = $this->hasMany(CostAllocationItem::class, 'cost_allocation_id');

        return $relation;
    }
}
