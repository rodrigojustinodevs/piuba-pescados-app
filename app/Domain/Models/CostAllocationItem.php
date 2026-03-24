<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $cost_allocation_id
 * @property string $stocking_id
 * @property float  $percentage
 * @property float  $amount
 *
 * @property-read CostAllocation|null $costAllocation
 * @property-read Stocking|null       $stocking
 */
class CostAllocationItem extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    protected $table = 'cost_allocation_items';

    protected $fillable = [
        'id',
        'cost_allocation_id',
        'stocking_id',
        'percentage',
        'amount',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'percentage' => 'decimal:4',
        'amount'     => 'decimal:2',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (CostAllocationItem $item): void {
            $item->id ??= (string) Str::uuid();
        });
    }

    /** @phpstan-return BelongsTo<CostAllocation, static> */
    public function costAllocation(): BelongsTo
    {
        /** @var BelongsTo<CostAllocation, static> $relation */
        $relation = $this->belongsTo(CostAllocation::class, 'cost_allocation_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<Stocking, static> */
    public function stocking(): BelongsTo
    {
        /** @var BelongsTo<Stocking, static> $relation */
        $relation = $this->belongsTo(Stocking::class, 'stocking_id');

        return $relation;
    }
}
