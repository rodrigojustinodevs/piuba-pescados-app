<?php

declare(strict_types=1);

use App\Application\UseCases\Auth\SyncPermissionsUseCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    private const array PERMISSION_NAMES = [
        'view-sensor-reading',
        'create-sensor-reading',
        'update-sensor-reading',
        'delete-sensor-reading',
    ];

    /**
     * As permissions de sensor-reading não existiam no enum (a rota usava um
     * middleware quebrado com '|', que o CheckPermission não separa). Sincroniza
     * permissions/role_permissions com o PermissionsEnum atual agora que os
     * 4 cases foram adicionados.
     */
    public function up(): void
    {
        app(SyncPermissionsUseCase::class)->execute();
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', self::PERMISSION_NAMES)->pluck('id');

        DB::table('role_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('name', self::PERMISSION_NAMES)->delete();
    }
};
