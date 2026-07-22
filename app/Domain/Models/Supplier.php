<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\SupplierCategoryEnum;
use App\Domain\Enums\SupplierStatusEnum;
use App\Infrastructure\Persistence\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string                 $id
 * @property string                 $company_id
 * @property string                 $name
 * @property string|null            $trade_name
 * @property string|null            $contact
 * @property string|null            $phone
 * @property string|null            $email
 * @property string|null            $document
 * @property string|null            $state_registration
 * @property SupplierCategoryEnum   $category
 * @property string|null            $payment_terms
 * @property float                  $rating
 * @property array<string,mixed>|null $address
 * @property SupplierStatusEnum     $status
 * @property \Carbon\Carbon         $created_at
 * @property \Carbon\Carbon         $updated_at
 * @property \Carbon\Carbon|null    $deleted_at
 *
 * @property-read Company|null      $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Stock>    $stocks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Supply>   $supplies
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Purchase> $purchases
 */
class Supplier extends BaseModel
{
    use HasCompanyScope;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'name',
        'trade_name',
        'contact',
        'phone',
        'email',
        'document',
        'state_registration',
        'category',
        'payment_terms',
        'rating',
        'address',
        'status',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'category' => SupplierCategoryEnum::class,
        'status'   => SupplierStatusEnum::class,
        'address'  => 'array',
        'rating'   => 'decimal:1',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Supplier $supplier): void {
            $supplier->id ??= (string) Str::uuid();
        });
    }

    public function setDocumentAttribute(?string $value): void
    {
        $this->attributes['document'] = $value === null
            ? null
            : preg_replace('/\D/', '', $value);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'supplier_id');
    }

    public function supplies(): HasMany
    {
        return $this->hasMany(Supply::class, 'supplier_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }
}
