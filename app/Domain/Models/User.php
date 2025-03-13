<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasRolesAndPermissions;
    use HasApiTokens;

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

    /**
     * Define a factory associada ao modelo.
     *
     * @return Factory<User>
     */
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
        $relation = $this->belongsToMany(Permission::class);

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

    public function hasPermission(string $permission): bool
    {
        if ($this->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        return $this->roles()->whereHas('permissions', fn ($q) => $q->where('name', $permission))->exists();
    }
}
