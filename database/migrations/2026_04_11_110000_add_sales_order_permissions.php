<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class () extends Migration
{
    private const array PERMISSION_NAMES = [
        'create-sales-order',
        'view-sales-order',
        'update-sales-order',
        'delete-sales-order',
        'cancel-sale-order',
    ];

    public function up(): void
    {
        $now = now();

        $permissionIds = [];

        foreach (self::PERMISSION_NAMES as $name) {
            $existing = DB::table('permissions')->where('name', $name)->first();

            if ($existing !== null) {
                $permissionIds[] = $existing->id;

                continue;
            }

            $id = (string) Str::uuid();
            DB::table('permissions')->insert([
                'id'         => $id,
                'name'       => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $permissionIds[] = $id;
        }

        $roleId = DB::table('roles')->where('name', 'company-admin')->value('id');

        if ($roleId === null) {
            return;
        }

        foreach ($permissionIds as $permissionId) {
            $exists = DB::table('permission_role')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->exists();

            if (! $exists) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permissionId,
                    'role_id'       => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('name', 'company-admin')->value('id');

        $permissionIds = DB::table('permissions')
            ->whereIn('name', self::PERMISSION_NAMES)
            ->pluck('id');

        if ($roleId !== null && $permissionIds->isNotEmpty()) {
            DB::table('permission_role')
                ->where('role_id', $roleId)
                ->whereIn('permission_id', $permissionIds)
                ->delete();
        }

        DB::table('permissions')->whereIn('name', self::PERMISSION_NAMES)->delete();
    }
};
