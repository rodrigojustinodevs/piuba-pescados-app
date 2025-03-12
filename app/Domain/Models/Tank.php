<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tank extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'tank_types_id',
        'name',
        'capacity_liters',
        'location',
        'status',
        'cultivation'
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
            $tank->id = (string) Str::uuid();
            $tank->status = (string) 'active';
            $tank->cultivation = (string) 'nursery';
        });
    }

    /**
     * @phpstan-return BelongsToMany<Company, static>
     */
    public function company(): BelongsTo
    {
        /** @var BelongsTo<Company, static> $relation */
        $relation = $this->belongsTo(Company::class, 'company_id');

        return $relation;
    }

    /**
     * @phpstan-return BelongsToMany<TankType, static>
     */
    public function tankType(): BelongsTo
    {
        /** @var BelongsTo<TankType, static> $relation */
        $relation = $this->belongsTo(TankType::class, 'tank_types_id');

        return $relation;
    }
}
