<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $name
 * @property int $capacity_liters
 * @property string $location
 * @property string $status
 * @property string $cultivation
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read TankType|null $tankType
 * @property-read Company|null $company
 */
class Tank extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'capacity_liters',
        'location',
        'volume',
        'status',
        'cultivation',
        'tank_type_id',
        'company_id',
    ];

    /** @var array<string> */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    #[\Override]
    protected static function booted()
    {
        static::creating(function (Tank $tank): void {
            $tank->id          = (string) Str::uuid();
            $tank->status      = 'active';
            $tank->cultivation = 'nursery';
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
        $relation = $this->belongsTo(TankType::class, 'tank_type_id'); // Correção do nome da chave

        return $relation;
    }
}
