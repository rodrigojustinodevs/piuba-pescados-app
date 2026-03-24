<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string          $id
 * @property string          $company_id
 * @property string          $client_id
 * @property string          $batch_id
 * @property string|null     $stocking_id
 * @property string|null     $financial_category_id
 * @property float           $total_weight
 * @property float           $price_per_kg
 * @property float           $total_revenue
 * @property Carbon          $sale_date
 * @property SaleStatus      $status
 * @property string|null     $notes
 * @property-read Company|null          $company
 * @property-read Client|null           $client
 * @property-read Batch|null            $batch
 * @property-read Stocking|null         $stocking
 * @property-read FinancialCategory|null $financialCategory
 * @property Carbon|null     $created_at
 * @property Carbon|null     $updated_at
 * @property Carbon|null     $deleted_at
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
        'batch_id',
        'stocking_id',
        'financial_category_id',
        'total_weight',
        'price_per_kg',
        'total_revenue',
        'sale_date',
        'status',
        'notes',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'status'        => SaleStatus::class,
        'total_weight'  => 'float',
        'price_per_kg'  => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'sale_date'     => 'date:Y-m-d',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Sale $sale): void {
            $sale->id ??= (string) Str::uuid();
            $sale->status ??= SaleStatus::PENDING;
        });
    }

    /** @phpstan-return BelongsTo<Company, static> */
    public function company(): BelongsTo
    {
        /** @var BelongsTo<Company, static> $relation */
        $relation = $this->belongsTo(Company::class, 'company_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<Client, static> */
    public function client(): BelongsTo
    {
        /** @var BelongsTo<Client, static> $relation */
        $relation = $this->belongsTo(Client::class, 'client_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<Batch, static> */
    public function batch(): BelongsTo
    {
        /** @var BelongsTo<Batch, static> $relation */
        $relation = $this->belongsTo(Batch::class, 'batch_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<Stocking, static> */
    public function stocking(): BelongsTo
    {
        /** @var BelongsTo<Stocking, static> $relation */
        $relation = $this->belongsTo(Stocking::class, 'stocking_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<FinancialCategory, static> */
    public function financialCategory(): BelongsTo
    {
        /** @var BelongsTo<FinancialCategory, static> $relation */
        $relation = $this->belongsTo(FinancialCategory::class, 'financial_category_id');

        return $relation;
    }

    /** @phpstan-return HasMany<FinancialTransaction, static> */
    public function financialTransactions(): HasMany
    {
        /** @var HasMany<FinancialTransaction, static> $relation */
        $relation = $this->hasMany(FinancialTransaction::class, 'reference_id')
            ->where('reference_type', 'sale');

        return $relation;
    }

    public function totalRevenue(): float
    {
        return round((float) $this->total_weight * (float) $this->price_per_kg, 2);
    }
}
