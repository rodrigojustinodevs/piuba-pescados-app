<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string              $id
 * @property string              $company_id
 * @property string              $supplier_id
 * @property string|null         $invoice_number
 * @property string              $purchase_date
 * @property string              $status
 * @property float               $total_price
 * @property string|null         $received_at
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchaseItem> $items
 * @property-read Supplier $supplier
 * @property-read Company  $company
 */
class Purchase extends BaseModel
{
    use SoftDeletes;

    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'supplier_id',
        'invoice_number',
        'purchase_date',
        'total_price',
        'status',
        'received_at',
    ];

    protected $casts = [
        'total_price'   => 'decimal:2',
        'purchase_date' => 'date:Y-m-d',
        'received_at'   => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(static function (Purchase $purchase): void {
            $purchase->id     ??= (string) Str::uuid();
            $purchase->status ??= PurchaseStatus::DRAFT->value;
        });
    }

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Helpers de domínio (leitura apenas — sem mutação de estado)
    // -------------------------------------------------------------------------

    public function isReceived(): bool
    {
        return $this->status === PurchaseStatus::RECEIVED->value;
    }

    public function currentStatus(): PurchaseStatus
    {
        return PurchaseStatus::from($this->status);
    }
}