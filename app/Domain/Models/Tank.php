<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\TankHistoryEvent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string              $id
 * @property string              $name
 * @property int                 $capacity_liters
 * @property string              $location
 * @property string              $status
 * @property string              $tank_type_id
 * @property string              $company_id
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read TankType|null $tankType
 * @property-read Company|null  $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Batch> $batches
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TankHistory> $histories
 */
class Tank extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'capacity_liters',
        'location',
        'status',
        'tank_type_id',
        'company_id',
    ];

    protected $casts = [
        'capacity_liters' => 'integer',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Tank $tank): void {
            $tank->id ??= (string) Str::uuid();
            $tank->status ??= 'active';
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
     * @phpstan-return BelongsTo<TankType, static>
     */
    public function tankType(): BelongsTo
    {
        /** @var BelongsTo<TankType, static> $relation */
        $relation = $this->belongsTo(TankType::class, 'tank_type_id');

        return $relation;
    }

    /**
     * @phpstan-return HasMany<Batch, static>
     */
    public function batches(): HasMany
    {
        /** @var HasMany<Batch, static> $relation */
        $relation = $this->hasMany(Batch::class, 'tank_id');

        return $relation;
    }

    /**
     * @phpstan-return HasMany<TankHistory, static>
     */
    public function histories(): HasMany
    {
        /** @var HasMany<TankHistory, static> $relation */
        $relation = $this->hasMany(TankHistory::class, 'tank_id');

        return $relation;
    }

    /**
     * Status a aplicar quando ocorre um evento de histórico (persistência fica no UseCase).
     */
    public function statusForEvent(TankHistoryEvent $event): string
    {
        return $event->value;
    }
}
