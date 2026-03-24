<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string                              $id
 * @property string                              $company_id
 * @property string                              $financial_category_id
 * @property FinancialType                       $type
 * @property FinancialTransactionStatus          $status
 * @property string|null                         $description
 * @property float                               $amount
 * @property Carbon                              $due_date
 * @property Carbon|null                         $payment_date
 * @property FinancialTransactionReferenceType|null $reference_type
 * @property string|null                         $reference_id
 * @property string|null                         $notes
 * @property bool                                $is_allocated
 * @property-read Company|null                   $company
 * @property-read FinancialCategory|null         $category
 * @property Carbon|null                         $created_at
 * @property Carbon|null                         $updated_at
 * @property Carbon|null                         $deleted_at
 */
class FinancialTransaction extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'financial_category_id',
        'type',
        'status',
        'description',
        'amount',
        'due_date',
        'payment_date',
        'reference_type',
        'reference_id',
        'notes',
        'is_allocated',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'type'           => FinancialType::class,
        'status'         => FinancialTransactionStatus::class,
        'reference_type' => FinancialTransactionReferenceType::class,
        'amount'         => 'decimal:2',
        'due_date'       => 'date:Y-m-d',
        'payment_date'   => 'date:Y-m-d',
        'is_allocated'   => 'boolean',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (FinancialTransaction $transaction): void {
            $transaction->id ??= (string) Str::uuid();
            $transaction->status ??= FinancialTransactionStatus::PENDING;
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

    public function isOriginatedExternally(): bool
    {
        return $this->reference_type !== null;
    }

    public function isPaid(): bool
    {
        return $this->status === FinancialTransactionStatus::PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === FinancialTransactionStatus::CANCELLED;
    }

    public function isAllocated(): bool
    {
        return (bool) $this->is_allocated;
    }
}
