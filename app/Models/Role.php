<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends BaseModel
{
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
