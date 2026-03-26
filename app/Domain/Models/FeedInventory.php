<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string              $id
 * @property string              $company_id
 * @property string              $feed_type
 * @property float               $current_stock
 * @property float               $minimum_stock
 * @property float               $daily_consumption
 * @property float               $total_consumption
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Company|null $company
 */
class FeedInventory extends BaseModel
{
    use SoftDeletes;

    protected $table = 'feed_inventory';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'feed_type',
        'current_stock',
        'minimum_stock',
        'daily_consumption',
        'total_consumption',
        'company_id',
        'updated_at',
    ];

    protected $casts = [
        'current_stock'     => 'decimal:2',
        'minimum_stock'     => 'decimal:2',
        'daily_consumption' => 'decimal:2',
        'total_consumption' => 'decimal:2',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (FeedInventory $feedInventory): void {
            $feedInventory->id ??= (string) Str::uuid();
        });
    }

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

    /**
     * @phpstan-return BelongsTo<Company, static>
     */
    public function company(): BelongsTo
    {
        /** @var BelongsTo<Company, static> $relation */
        $relation = $this->belongsTo(Company::class, 'company_id');

        return $relation;
    }
}
