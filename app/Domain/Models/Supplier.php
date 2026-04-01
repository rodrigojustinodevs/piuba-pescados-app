<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string              $id
 * @property string              $company_id
 * @property string              $name
 * @property string|null         $contact
 * @property string|null         $phone
 * @property string|null         $email
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Stock> $stocks
 */
class Supplier extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'name',
        'contact',
        'phone',
        'email',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Supplier $supplier): void {
            $supplier->id ??= (string) Str::uuid();
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
     * @phpstan-return HasMany<Stock, static>
     */
    public function stocks(): HasMany
    {
        /** @var HasMany<Stock, static> $relation */
        $relation = $this->hasMany(Stock::class, 'supplier_id');

        return $relation;
    }
}
