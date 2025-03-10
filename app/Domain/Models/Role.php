<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Role extends BaseModel
{


    protected static function booted()
    {
        static::creating(function (Role $role): void {
            $role->id = (string) Str::uuid();
        });
    }

    /**
     * @return BelongsToMany<Permission, Role>
     */
    public function permissions(): BelongsToMany
    {
        /** @var BelongsToMany<Permission, Role> $relation */
        $relation = $this->belongsToMany(Permission::class);

        return $relation;
    }

    /**
     * @return BelongsToMany<User, Role>
     */
    public function users(): BelongsToMany
    {
        /** @var BelongsToMany<User, Role> $relation */
        $relation = $this->belongsToMany(User::class);

        return $relation;
    }
}
