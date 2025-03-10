<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Permission extends BaseModel
{
    public string $id;

    #[\Override]
    protected static function booted()
    {
        static::creating(function (Permission $permission): void {
            $permission->id = (string) Str::uuid();
        });
    }

    /**
     *
     * @return BelongsToMany<Role, static>
     */
    public function roles(): BelongsToMany
    {
        /** @var BelongsToMany<Role, static> $relation */
        $relation = $this->belongsToMany(Role::class);

        return $relation;
    }

    /**
     *
     * @return BelongsToMany<User, static>
     */
    public function users(): BelongsToMany
    {
        /** @var BelongsToMany<User, static> $relation */
        $relation = $this->belongsToMany(User::class);

        return $relation;
    }
}
