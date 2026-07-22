<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\PurchasePaymentMethod;
use App\Domain\Enums\PurchasePaymentStatus;
use App\Domain\Enums\PurchaseStatus;
use App\Infrastructure\Persistence\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property string                    $id
 * @property string                    $code
 * @property string                    $company_id
 * @property string                    $supplier_id
 * @property string|null               $invoice_number
 * @property \Carbon\Carbon            $order_date
 * @property \Carbon\Carbon|null       $expected_date
 * @property \Carbon\Carbon|null       $received_date
 * @property PurchaseStatus            $status
 * @property PurchasePaymentStatus     $payment_status
 * @property PurchasePaymentMethod|null $payment_method
 * @property float                     $total_price
 * @property float                     $freight
 * @property float                     $other_costs
 * @property string|null               $notes
 * @property string|null               $responsible
 * @property \Carbon\Carbon            $created_at
 * @property \Carbon\Carbon            $updated_at
 * @property \Carbon\Carbon|null       $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchaseItem>   $items
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchasePayment> $payments
 * @property-read Supplier $supplier
 * @property-read Company  $company
 */
class Purchase extends BaseModel
{
    use HasCompanyScope;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'company_id',
        'supplier_id',
        'invoice_number',
        'order_date',
        'expected_date',
        'received_date',
        'status',
        'payment_status',
        'payment_method',
        'total_price',
        'freight',
        'other_costs',
        'notes',
        'responsible',
    ];

    protected $casts = [
        'status'         => PurchaseStatus::class,
        'payment_status' => PurchasePaymentStatus::class,
        'payment_method' => PurchasePaymentMethod::class,
        'total_price'    => 'decimal:2',
        'freight'        => 'decimal:2',
        'other_costs'    => 'decimal:2',
        'order_date'     => 'datetime',
        'expected_date'  => 'date',
        'received_date'  => 'datetime',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Purchase $purchase): void {
            $purchase->id ??= (string) Str::uuid();
            $purchase->status ??= PurchaseStatus::DRAFT;
            $purchase->payment_status ??= PurchasePaymentStatus::PENDING;
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_id')->orderBy('payment_date');
    }

    public function isReceived(): bool
    {
        return $this->status->isReceived();
    }

    public function currentStatus(): PurchaseStatus
    {
        return $this->status;
    }

    public function getTotalQuantity(): float
    {
        return (float) $this->items->sum('quantity');
    }

    public function getTotalReceived(): float
    {
        return (float) $this->items->sum('received_quantity');
    }

    public function getTotalPending(): float
    {
        return max(0.0, $this->getTotalQuantity() - $this->getTotalReceived());
    }

    public function getReceiveProgress(): float
    {
        $total = $this->getTotalQuantity();

        if ($total == 0) {
            return 0.0;
        }

        return round(($this->getTotalReceived() / $total) * 100, 2);
    }

    public function getTotalAmount(): float
    {
        return (float) $this->total_price;
    }

    public function getTotalPaid(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->sum('amount');
        }

        return (float) DB::table('purchase_payments')
            ->where('purchase_id', $this->id)
            ->sum('amount');
    }

    public function getOutstandingBalance(): float
    {
        return max(0.0, $this->getTotalAmount() - $this->getTotalPaid());
    }

    public function getPaymentProgress(): float
    {
        $total = $this->getTotalAmount();

        if ($total <= 0) {
            return 0.0;
        }

        return round(min(($this->getTotalPaid() / $total) * 100, 100), 2);
    }

    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->getTotalPaid();
        $total     = $this->getTotalAmount();

        $newStatus = match (true) {
            $totalPaid <= 0     => PurchasePaymentStatus::PENDING,
            $totalPaid < $total => PurchasePaymentStatus::PARTIAL,
            default             => PurchasePaymentStatus::PAID,
        };

        $this->payment_status = $newStatus;
        $this->save();
    }

    public function canRegisterPayment(): bool
    {
        return $this->status !== PurchaseStatus::CANCELLED
            && $this->payment_status !== PurchasePaymentStatus::PAID;
    }

    public function updateReceivingStatus(): void
    {
        $received = $this->getTotalReceived();
        $total    = $this->getTotalQuantity();

        $newStatus = match (true) {
            $received <= 0     => PurchaseStatus::APPROVED,
            $received < $total => PurchaseStatus::PARTIALLY_RECEIVED,
            default            => PurchaseStatus::RECEIVED,
        };

        $this->status = $newStatus;

        if ($newStatus === PurchaseStatus::RECEIVED) {
            $this->received_date = \Carbon\Carbon::now();
        }

        $this->save();
    }
}
