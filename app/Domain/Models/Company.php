<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'cnpj',
        'email',
        'phone',
        'address_street',
        'address_number',
        'address_complement',
        'address_neighborhood',
        'address_city',
        'address_state',
        'address_zip_code',
        'status',
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
        static::creating(function (Company $company): void {
            $company->id = (string) Str::uuid();

            if (! isset($company->status)) {
                $company->status = 'active';
            }
        });
    }

    /**
     * @phpstan-return BelongsToMany<User, static>
     */
    public function users(): BelongsToMany
    {
        /** @var BelongsToMany<User, static> $relation */
        $relation = $this->belongsToMany(User::class, 'company_user');

        return $relation;
    }

    /**
     * @phpstan-return BelongsToMany<Role, static>
     */
    public function roles(): BelongsToMany
    {
        /** @var BelongsToMany<Role, static> $relation */
        $relation = $this->belongsToMany(Role::class, 'company_role');

        return $relation;
    }

    /**
     * @phpstan-return BelongsToMany<Permission, static>
     */
    public function permissions(): BelongsToMany
    {
        /** @var BelongsToMany<Permission, static> $relation */
        $relation = $this->belongsToMany(Permission::class, 'company_user_permission');

        return $relation;
    }
}
