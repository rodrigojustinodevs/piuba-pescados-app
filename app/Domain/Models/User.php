<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\RolesEnum;
use App\Infrastructure\Persistence\Traits\HasPermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements Auditable, JWTSubject
{
    use \OwenIt\Auditing\Auditable;
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasPermissions;
    use Notifiable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    #[\Override]
    protected static function booted()
    {
        static::creating(function (User $user): void {
            $user->id = (string) Str::uuid();
        });
    }

    /** @return Factory<User> */
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    /**
     * @return BelongsToMany<Permission, static>
     */
    public function permissions(): BelongsToMany
    {
        /** @var BelongsToMany<Permission, static> $relation */
        $relation = $this->belongsToMany(Permission::class, 'user_company_permissions');

        return $relation;
    }

    /**
     * @return BelongsToMany<Role, static>
     */
    public function roles(): BelongsToMany
    {
        /** @var BelongsToMany<Role, static> $relation */
        $relation = $this->belongsToMany(Role::class);

        return $relation;
    }

    /**
     * @return BelongsToMany<Company, static>
     */
    public function companies(): BelongsToMany
    {
        /** @var BelongsToMany<Company, static> $relation */
        $relation = $this->belongsToMany(Company::class, 'company_user')
            ->withPivot(['role', 'is_active', 'joined_at'])
            ->withTimestamps()
            ->using(CompanyUserPivot::class);

        return $relation;
    }

    /**
     * @return BelongsToMany<Company, static>
     */
    public function activeCompanies(): BelongsToMany
    {
        return $this->companies()->wherePivot('is_active', true);
    }

    public function belongsToCompany(string $companyId): bool
    {
        return $this->activeCompanies()
            ->where('companies.id', $companyId)
            ->exists();
    }

    public function roleInCompany(string $companyId): ?RolesEnum
    {
        $pivotModel = $this->companies()
            ->where('companies.id', $companyId)
            ->first()?->pivot;

        $pivot = $pivotModel instanceof CompanyUserPivot ? $pivotModel : null;

        return $pivot ? RolesEnum::from($pivot->role) : null;
    }

    /**
     * @return list<array{id: string, name: string, slug: string|null, role: string}>
     */
    public function companiesWithRoles(): array
    {
        return $this->activeCompanies()
            ->get()
            ->map(function (Company $company): array {
                $pivotValue = $company->getRelationValue('pivot');
                $pivot      = $pivotValue instanceof CompanyUserPivot ? $pivotValue : null;

                return [
                    'id'   => (string) $company->id,
                    'name' => (string) $company->name,
                    'slug' => isset($company->slug) ? (string) $company->slug : null,
                    'role' => $pivot instanceof CompanyUserPivot ? $pivot->role : RolesEnum::GUEST->value,
                ];
            })
            ->toArray();
    }

    /** Verifica se o usuário é master_admin em qualquer contexto. */
    public function isMasterAdmin(): bool
    {
        return $this->companies()
            ->wherePivot('role', RolesEnum::MASTER_ADMIN->value)
            ->exists();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
