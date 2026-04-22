<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\RolesEnum;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Custom pivot for company_user.
 * Allows specific behavior for the pivot table.
 */
final class CompanyUserPivot extends Pivot
{
    protected $table = 'company_user';

    protected $casts = [
        'is_active' => 'boolean',
        'joined_at' => 'datetime',
    ];

    public function getRoleEnumAttribute(): RolesEnum
    {
        return RolesEnum::from($this->role);
    }
}
