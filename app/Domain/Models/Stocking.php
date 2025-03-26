<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property Carbon|null $stocking_date
 * @property int $quantity
 * @property float $average_weight
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Batche|null $batche
 */
class Stocking extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'batche_id',
        'stocking_date',
        'quantity',
        'average_weight',
    ];

    /** @var array<string> */
    protected $dates = [
        'stocking_date',
        'created_at',
        'updated_at',
    ];

    #[\Override]
    protected static function booted()
    {
        static::creating(function (Stocking $stocking): void {
            $stocking->id = (string) Str::uuid();
        });
    }

    /**
     * @phpstan-return BelongsTo<Batche, static>
     */
    public function batche(): BelongsTo
    {
        /** @var BelongsTo<Batche, static> $relation */
        $relation = $this->belongsTo(Batche::class, 'batche_id');

        return $relation;
    }
}
