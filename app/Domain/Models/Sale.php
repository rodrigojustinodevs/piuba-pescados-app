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
 * @property string $client_id
 * @property string $batche_id
 * @property float $total_weight
 * @property float $price_per_kg
 * @property float $total_revenue
 * @property Carbon|null $sale_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Company|null $company
 * @property-read Client|null $client
 * @property-read Batche|null $batche
 */
class Sale extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'client_id',
        'batche_id',
        'total_weight',
        'price_per_kg',
        'total_revenue',
        'sale_date',
    ];

    /** @var array<string> */
    protected $dates = [
        'sale_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (Sale $sale): void {
            $sale->id = (string) Str::uuid();
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

    /**
     * @phpstan-return BelongsTo<Client, static>
     */
    public function client(): BelongsTo
    {
        /** @var BelongsTo<Client, static> $relation */
        $relation = $this->belongsTo(Client::class, 'client_id');

        return $relation;
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
