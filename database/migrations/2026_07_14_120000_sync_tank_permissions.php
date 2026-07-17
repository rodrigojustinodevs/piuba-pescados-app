<?php

declare(strict_types=1);

use App\Application\UseCases\Auth\SyncPermissionsUseCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    private const array PERMISSION_NAMES = [
        'view-tank',
        'create-tank',
        'update-tank',
        'delete-tank',
    ];

    /**
     * Sincroniza `permissions`/`role_permissions` com o PermissionsEnum atual,
     * garantindo que as permissions de tank (adicionadas ao enum) fiquem
     * persistidas e mapeadas por role.
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
