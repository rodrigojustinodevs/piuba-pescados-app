<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string         $id
 * @property string         $sale_id
 * @property float          $amount
 * @property PaymentMethod  $payment_method
 * @property Carbon         $payment_date
 * @property string|null    $reference
 * @property string|null    $notes
 * @property Carbon|null    $created_at
 * @property Carbon|null    $updated_at
 * @property-read Sale|null $sale
 */
class SalePayment extends BaseModel
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'sale_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference',
        'notes',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'amount'         => 'decimal:2',
        'payment_method' => PaymentMethod::class,
        'payment_date'   => 'date:Y-m-d',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (SalePayment $payment): void {
            $payment->id ??= (string) Str::uuid();
        });
    }

    /** @phpstan-return BelongsTo<Sale, static> */
    public function sale(): BelongsTo
    {
        /** @var BelongsTo<Sale, static> $relation */
        $relation = $this->belongsTo(Sale::class, 'sale_id');

        return $relation;
    }
}
