<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\Can;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'operator',
            'master_admin',
            'company_admin',
            'manager',
            'admin',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        foreach (Can::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value]);
        }

        $userAdmin          = Role::where('name', 'admin')->first();
        $viewUserPermission = Permission::where('name', 'view-user')->first();

        if ($userAdmin && $viewUserPermission && ! $userAdmin->permissions()->where('permissions.id', $viewUserPermission->id)->exists()) {
            $userAdmin->permissions()->attach($viewUserPermission->id);
        }
    }
}
