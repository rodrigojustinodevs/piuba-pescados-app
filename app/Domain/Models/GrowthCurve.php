<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Ponto da curva de crescimento (peso médio por lote).
 *
 * @property string              $id
 * @property string              $batch_id
 * @property float               $average_weight
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Batch|null $batch
 */
class GrowthCurve extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'batch_id',
        'average_weight',
    ];

    protected $casts = [
        'average_weight' => 'decimal:4',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (GrowthCurve $growthCurve): void {
            $growthCurve->id ??= (string) Str::uuid();
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
