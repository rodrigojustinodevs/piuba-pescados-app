<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\MortalityCause;
use App\Domain\Enums\MortalitySeverity;
use App\Infrastructure\Persistence\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string              $id
 * @property string              $batch_id
 * @property \Carbon\Carbon|null $mortality_date
 * @property int                 $quantity
 * @property MortalityCause      $cause
 * @property MortalitySeverity|null $severity
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Batch|null $batch
 */
class Mortality extends BaseModel
{
    use HasCompanyScope;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'batch_id',
        'mortality_date',
        'quantity',
        'cause',
        'description',
        'severity',
    ];

    protected $casts = [
        'mortality_date' => 'date:Y-m-d',
        'quantity'       => 'integer',
        'cause'          => MortalityCause::class,
        'severity'       => MortalitySeverity::class,
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Mortality $mortality): void {
            $mortality->id ??= (string) Str::uuid();
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
