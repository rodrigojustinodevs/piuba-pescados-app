<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\BatchStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string              $id
 * @property string|null         $parent_group_id
 * @property string|null         $name
 * @property string|null         $description
 * @property string              $tank_id
 * @property \Carbon\Carbon|null $entry_date
 * @property int                 $initial_quantity
 * @property float               $unit_cost
 * @property float               $total_cost
 * @property string              $species
 * @property string              $status
 * @property string|null         $cultivation
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Tank|null $tank
 */
class Batch extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'parent_group_id',
        'name',
        'description',
        'tank_id',
        'entry_date',
        'initial_quantity',
        'unit_cost',
        'total_cost',
        'species',
        'status',
        'cultivation',
    ];

    protected $casts = [
        'entry_date'       => 'date:Y-m-d',
        'initial_quantity' => 'integer',
        'unit_cost'        => 'decimal:2',
        'total_cost'       => 'decimal:2',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Batch $batch): void {
            $batch->id ??= (string) Str::uuid();
            $batch->status ??= BatchStatus::ACTIVE->value;
        });
    }

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

    /**
     * @phpstan-return BelongsTo<Tank, static>
     */
    public function tank(): BelongsTo
    {
        /** @var BelongsTo<Tank, static> $relation */
        $relation = $this->belongsTo(Tank::class, 'tank_id');

        return $relation;
    }

    /**
     * @phpstan-return HasMany<SalesOrderItem, static>
     */
    public function salesOrderItems(): HasMany
    {
        /** @var HasMany<SalesOrderItem, static> $relation */
        $relation = $this->hasMany(SalesOrderItem::class, 'batch_id');

        return $relation;
    }

    // -------------------------------------------------------------------------
    // Helpers de domínio (leitura apenas)
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === BatchStatus::ACTIVE->value;
    }

    public function isFinished(): bool
    {
        return $this->status === BatchStatus::FINISHED->value;
    }

    public function currentStatus(): BatchStatus
    {
        return BatchStatus::from($this->status);
    }
}
