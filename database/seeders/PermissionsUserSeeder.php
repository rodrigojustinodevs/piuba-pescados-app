<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = '477e360a-7b88-45c1-8f2a-0909d1ee9ded';

        $permissions = DB::table('permissions')->pluck('id');

        $permissionsUser = [];

        foreach ($permissions as $permissionId) {
            $permissionsUser[] = [
                'user_id'       => $userId,
                'permission_id' => $permissionId,
            ];
        }

        DB::table('permission_user')->insert($permissionsUser);
    }
}
