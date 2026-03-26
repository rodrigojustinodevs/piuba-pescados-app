<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string              $id
 * @property string              $batch_id
 * @property \Carbon\Carbon|null $biometry_date
 * @property float               $average_weight
 * @property float               $sample_weight
 * @property int                 $sample_quantity
 * @property float|null          $biomass_estimated
 * @property float               $fcr
 * @property float|null          $density_at_time
 * @property float|null          $recommended_ration
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
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

    protected $casts = [
        'biometry_date'      => 'date:Y-m-d',
        'average_weight'     => 'decimal:2',
        'sample_weight'      => 'decimal:2',
        'sample_quantity'    => 'integer',
        'biomass_estimated'  => 'decimal:2',
        'fcr'                => 'decimal:2',
        'density_at_time'    => 'decimal:2',
        'recommended_ration' => 'decimal:2',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Biometry $biometry): void {
            $biometry->id ??= (string) Str::uuid();
        });
    }

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

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
