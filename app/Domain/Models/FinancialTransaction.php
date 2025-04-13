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
 * @property string $financial_category_id
 * @property string $type
 * @property string $description
 * @property float $amount
 * @property Carbon|null $transaction_date
 * @property-read Company|null $company
 * @property-read FinancialCategory|null $category
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class FinancialTransaction extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'financial_category_id',
        'category_id',
        'type',
        'description',
        'amount',
        'transaction_date',
    ];

    /** @var array<string> */
    protected $dates = [
        'transaction_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted()
    {
        static::creating(function (FinancialTransaction $transaction): void {
            $transaction->id = (string) Str::uuid();
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
     * @phpstan-return BelongsTo<FinancialCategory, static>
     */
    public function category(): BelongsTo
    {
        /** @var BelongsTo<FinancialCategory, static> $relation */
        $relation = $this->belongsTo(FinancialCategory::class, 'financial_category_id');

        return $relation;
    }
}
