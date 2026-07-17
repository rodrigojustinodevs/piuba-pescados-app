<?php

declare(strict_types=1);

use App\Application\UseCases\Auth\SyncPermissionsUseCase;
use App\Domain\Enums\PermissionsEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    /**
     * OPERATOR e MANAGER passam a ter view-sensor (e MANAGER também
     * create/update-sensor). Resincroniza role_permissions com o enum atual.
     */
    public function up(): void
    {
        app(SyncPermissionsUseCase::class)->execute();
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('name', PermissionsEnum::VIEW_SENSOR->value)->value('id');

        if ($permissionId) {
            DB::table('role_permissions')
                ->where('permission_id', $permissionId)
                ->where('role', 'operator')
                ->delete();
        }

        $createUpdateIds = DB::table('permissions')
            ->whereIn('name', [PermissionsEnum::CREATE_SENSOR->value, PermissionsEnum::UPDATE_SENSOR->value, PermissionsEnum::VIEW_SENSOR->value])
            ->pluck('id');

        DB::table('role_permissions')
            ->whereIn('permission_id', $createUpdateIds)
            ->where('role', 'manager')
            ->delete();
    }
};
