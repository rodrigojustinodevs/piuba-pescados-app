<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\RolesEnum;
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

    protected $casts = [
        'settings'      => 'array',
        'is_active'     => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Company $company): void {
            $company->id ??= (string) Str::uuid();
            $company->status ??= 'active';
        });
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

    // ─── Relacionamentos ──────────────────────────────────────────────────────

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot(['role', 'is_active', 'joined_at'])
            ->withTimestamps()
            ->using(CompanyUserPivot::class);
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_active', true);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function hasUser(int $userId): bool
    {
        return $this->users()->where('users.id', $userId)->exists();
    }

    public function getUserRole(int $userId): ?RolesEnum
    {
        $pivotModel = $this->users()
            ->where('users.id', $userId)
            ->first()?->pivot;

        $pivot = $pivotModel instanceof CompanyUserPivot ? $pivotModel : null;

        return $pivot ? RolesEnum::from($pivot->role) : null;
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
