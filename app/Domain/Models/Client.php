<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\PriceGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $company_id
 * @property string $name
 * @property string $person_type
 * @property string|null $contact
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $document_number
 * @property string|null $address
 * @property float|null $credit_limit
 * @property bool $is_defaulter
 * @property PriceGroup|null $price_group
 * @property-read Company|null $company
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Client extends BaseModel
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
        'person_type',
        'document_number',
        'address',
        'credit_limit',
        'is_defaulter',
        'price_group',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'credit_limit' => 'decimal:2',
        'is_defaulter' => 'boolean',
        'price_group'  => PriceGroup::class,
    ];

    /** @var array<string> */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    #[\Override]
    protected static function booted()
    {
        static::creating(function (Client $client): void {
            $client->id = (string) Str::uuid();
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
     * @phpstan-return HasMany<Sale, static>
     */
    public function sales(): HasMany
    {
        /** @var HasMany<Sale, static> $relation */
        $relation = $this->hasMany(Sale::class, 'client_id');

        return $relation;
    }

    /**
     * @phpstan-return HasMany<SalesOrder, static>
     */
    public function salesOrders(): HasMany
    {
        /** @var HasMany<SalesOrder, static> $relation */
        $relation = $this->hasMany(SalesOrder::class, 'client_id');

        return $relation;
    }
}
