<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string               $id
 * @property string               $purchase_id
 * @property \Carbon\Carbon       $payment_date
 * @property float                $amount
 * @property string               $payment_method
 * @property string|null          $reference
 * @property string|null          $notes
 * @property \Carbon\Carbon       $created_at
 * @property \Carbon\Carbon       $updated_at
 *
 * @property-read Purchase $purchase
 */
class PurchasePayment extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'purchase_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (PurchasePayment $payment): void {
            $payment->id ??= (string) Str::uuid();
        });
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
}
