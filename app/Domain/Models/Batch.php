<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string|null $name
 * @property string|null $description
 * @property Carbon|null $entry_date
 * @property int $initial_quantity
 * @property string $species
 * @property string $status
 * @property string $cultivation
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
        'name',
        'description',
        'tank_id',
        'entry_date',
        'initial_quantity',
        'species',
        'status',
        'cultivation',
    ];

    /** @var array<string> */
    protected $dates = [
        'entry_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted()
    {
        static::creating(function (Batch $batch): void {
            $batch->id     = (string) Str::uuid();
            $batch->status = 'active';
        });
    }

    /**
     * @phpstan-return BelongsTo<Tank, static>
     */
    public function tank(): BelongsTo
    {
        /** @var BelongsTo<Tank, static> $relation */
        $relation = $this->belongsTo(Tank::class, 'tank_id');

        return $relation;
    }
}
