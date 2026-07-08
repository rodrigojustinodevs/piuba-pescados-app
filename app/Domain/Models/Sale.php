<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\PaymentMethod;
use App\Domain\Enums\SaleStatus;
use App\Infrastructure\Persistence\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string           $id
 * @property string           $company_id
 * @property string           $client_id
 * @property string           $batch_id
 * @property string|null      $stocking_id
 * @property string|null      $financial_category_id
 * @property string|null      $responsible_user_id
 * @property string|null      $code
 * @property string|null      $invoice_number
 * @property bool             $needs_invoice
 * @property float            $total_weight
 * @property float            $price_per_kg
 * @property float            $total_revenue
 * @property float            $discount
 * @property float            $shipping
 * @property float            $taxes
 * @property Carbon           $sale_date
 * @property Carbon|null      $due_date
 * @property Carbon|null      $paid_date
 * @property Carbon|null      $delivered_at
 * @property PaymentMethod      $payment_method
 * @property SaleStatus        $status
 * @property string|null       $notes
 * @property-read Company|null          $company
 * @property-read Client|null           $client
 * @property-read Batch|null            $batch
 * @property-read Stocking|null         $stocking
 * @property-read FinancialCategory|null $financialCategory
 * @property-read User|null              $responsibleUser
 * @property Carbon|null      $created_at
 * @property Carbon|null      $updated_at
 * @property Carbon|null      $deleted_at
 */
class Sale extends BaseModel
{
    use HasCompanyScope;
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
        'responsible_user_id',
        'code',
        'invoice_number',
        'needs_invoice',
        'total_weight',
        'price_per_kg',
        'total_revenue',
        'discount',
        'shipping',
        'taxes',
        'sale_date',
        'due_date',
        'paid_date',
        'delivered_at',
        'payment_method',
        'status',
        'notes',
        'is_total_harvest',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'status'           => SaleStatus::class,
        'payment_method'   => PaymentMethod::class,
        'needs_invoice'    => 'boolean',
        'total_weight'     => 'float',
        'price_per_kg'     => 'decimal:2',
        'total_revenue'    => 'decimal:2',
        'discount'         => 'decimal:2',
        'shipping'         => 'decimal:2',
        'taxes'            => 'decimal:2',
        'sale_date'        => 'date:Y-m-d',
        'due_date'         => 'date:Y-m-d',
        'paid_date'        => 'date:Y-m-d',
        'delivered_at'     => 'datetime',
        'is_total_harvest' => 'boolean',
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

    /** @phpstan-return HasMany<SalePayment, static> */
    public function payments(): HasMany
    {
        /** @var HasMany<SalePayment, static> $relation */
        $relation = $this->hasMany(SalePayment::class, 'sale_id');

        return $relation;
    }

    /** @phpstan-return HasMany<SaleItem, static> */
    public function items(): HasMany
    {
        /** @var HasMany<SaleItem, static> $relation */
        $relation = $this->hasMany(SaleItem::class, 'sale_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<User, static> */
    public function responsibleUser(): BelongsTo
    {
        /** @var BelongsTo<User, static> $relation */
        $relation = $this->belongsTo(User::class, 'responsible_user_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<SalesOrder, static> */
    public function salesOrder(): BelongsTo
    {
        /** @var BelongsTo<SalesOrder, static> $relation */
        $relation = $this->belongsTo(SalesOrder::class, 'sales_order_id');

        return $relation;
    }

    public function totalRevenue(): float
    {
        return round((float) $this->total_weight * (float) $this->price_per_kg, 2);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function remainingBalance(): float
    {
        return round((float) $this->total_revenue - $this->totalPaid(), 2);
    }

    public function isFullyPaid(): bool
    {
        return $this->remainingBalance() <= 0;
    }
}
