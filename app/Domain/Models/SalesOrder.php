<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\SalesOrderStatus;
use App\Domain\Enums\SalesOrderType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string               $id
 * @property string               $company_id
 * @property string               $client_id
 * @property SalesOrderType       $type
 * @property SalesOrderStatus     $status
 * @property string               $total_amount
 * @property Carbon               $issue_date
 * @property Carbon|null          $expiration_date
 * @property string|null          $quotation_id
 * @property string|null          $notes
 * @property-read Company|null    $company
 * @property-read Client|null     $client
 * @property-read SalesOrder|null $quotation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SalesOrder> $ordersFromQuotation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SalesOrderItem> $items
 * @property Carbon|null          $created_at
 * @property Carbon|null          $updated_at
 * @property Carbon|null          $deleted_at
 */
class SalesOrder extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'client_id',
        'type',
        'status',
        'total_amount',
        'issue_date',
        'expiration_date',
        'expected_delivery_date',
        'delivered_at',
        'expected_payment_date',
        'quotation_id',
        'notes',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'type'                   => SalesOrderType::class,
        'status'                 => SalesOrderStatus::class,
        'total_amount'           => 'decimal:2',
        'issue_date'             => 'date:Y-m-d',
        'expiration_date'        => 'date:Y-m-d',
        'expected_delivery_date' => 'date:Y-m-d',
        'delivered_at'           => 'datetime',
        'expected_payment_date'  => 'date:Y-m-d',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (SalesOrder $order): void {
            $order->id ??= (string) Str::uuid();
            $order->type ??= SalesOrderType::QUOTATION;
            $order->status ??= SalesOrderStatus::DRAFT;
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

    /** @phpstan-return BelongsTo<SalesOrder, static> */
    public function quotation(): BelongsTo
    {
        /** @var BelongsTo<SalesOrder, static> $relation */
        $relation = $this->belongsTo(self::class, 'quotation_id');

        return $relation;
    }

    /** @phpstan-return HasMany<SalesOrder, static> */
    public function ordersFromQuotation(): HasMany
    {
        /** @var HasMany<SalesOrder, static> $relation */
        $relation = $this->hasMany(self::class, 'quotation_id');

        return $relation;
    }

    /** @phpstan-return HasMany<SalesOrderItem, static> */
    public function items(): HasMany
    {
        /** @var HasMany<SalesOrderItem, static> $relation */
        $relation = $this->hasMany(SalesOrderItem::class, 'sales_order_id');

        return $relation;
    }

    public function sales(): HasMany
    {
        /** @var HasMany<Sale, static> $relation */
        $relation = $this->hasMany(Sale::class, 'sales_order_id');

        return $relation;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            SalesOrderStatus::DRAFT,
            SalesOrderStatus::OPEN,
            SalesOrderStatus::CONFIRMED,
        ], strict: true);
    }
}
