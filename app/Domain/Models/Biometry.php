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
 * @property float $sample_weight
 * @property int $sample_quantity
 * @property float|null $biomass_estimated
 * @property float $fcr
 * @property float|null $density_at_time
 * @property float|null $recommended_ration
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Batch|null $batch
 */
class Biometry extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'batch_id',
        'biometry_date',
        'average_weight',
        'sample_weight',
        'sample_quantity',
        'biomass_estimated',
        'fcr',
        'density_at_time',
        'recommended_ration',
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
     * @phpstan-return BelongsTo<Batch, static>
     */
    public function batch(): BelongsTo
    {
        /** @var BelongsTo<Batch, static> $relation */
        $relation = $this->belongsTo(Batch::class, 'batch_id');

        return $relation;
    }
}
