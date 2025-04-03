<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $batch_id
 * @property Carbon|null $biometry_date
 * @property float $average_weight
 * @property float $fcr
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Batche|null $batche
 */
class Biometry extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'batche_id',
        'biometry_date',
        'average_weight',
        'fcr',
    ];

    /** @var array<string> */
    protected $dates = [
        'biometry_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted()
    {
        static::creating(function (Biometry $biometry): void {
            $biometry->id = (string) Str::uuid();
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
