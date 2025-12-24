<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            TankTypeSeeder::class,
            PermissionSeeder::class,
            CompanyRolesPermissionsSeeder::class,
            MasterAdminUserSeeder::class,
            UsersByRoleSeeder::class,
        ]);

        // Factory users disabled in production (requires fakerphp/faker which is dev dependency)
        // Uncomment if running in development environment:
        // User::factory()->create([
        //     'is_admin' => true,
        //     'name'     => 'Test User',
        //     'email'    => 'test@example.com',
        // ]);
        //
        // User::factory()->create([
        //     'name'  => 'Test User 2',
        //     'email' => 'test2@example.com',
        // ]);
    }
}
