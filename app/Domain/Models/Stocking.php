<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Stocking (povoamento/estocagem) – aquaculture term for introducing organisms into a batch.
 *
 * @property string $id
 * @property Carbon|null $stocking_date
 * @property int $quantity
 * @property float $average_weight
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Batch|null $batch
 */
class Stocking extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'stockings';

    protected $fillable = [
        'id',
        'batch_id',
        'stocking_date',
        'quantity',
        'average_weight',
    ];

    /** @var array<string> */
    protected $dates = [
        'stocking_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted()
    {
        static::creating(function (Stocking $stocking): void {
            $stocking->id = (string) Str::uuid();
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
