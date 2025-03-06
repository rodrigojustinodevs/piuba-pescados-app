<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends BaseModel
{
    protected $fillable = ['name', 'cnpj', 'address', 'phone'];

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
