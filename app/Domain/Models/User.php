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
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    /** @use HasFactory<UserFactory> */
    use HasFactory;
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

    public string $id;

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
}
