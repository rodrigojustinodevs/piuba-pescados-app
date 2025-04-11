<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $company_id
 * @property string $description
 * @property float $amount
 * @property string $registration_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Company|null $company
 */
class CostAllocation extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'description',
        'amount',
        'registration_date',
    ];

    /** @var array<string> */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'registration_date',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (CostAllocation $costAllocation): void {
            $costAllocation->id = (string) Str::uuid();
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
}
