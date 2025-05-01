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
            'admin',
            'company-admin',
            'guest',
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        foreach (Can::cases() as $permission) {
            Permission::create(['name' => $permission]);
        }

        $userAdmin = Role::where('name', 'admin')->first();
        $userAdmin->permissions()->attach(Permission::where('name', 'view-users')->first());
    }
}
