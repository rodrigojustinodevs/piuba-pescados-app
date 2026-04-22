<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Domain\Enums\PermissionsEnum;
use App\Domain\Enums\RolesEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class SyncPermissionsUseCase
{
    /**
     * Popula/atualiza a tabela permissions com todos os valores do Enum.
     * Seguro para re-executar (upsert idempotente).
     *
     * @return list<string>
     */
    public function execute(): array
    {
        $synced = [];

        foreach (PermissionsEnum::cases() as $permission) {
            DB::table('permissions')->upsert(
                [
                    'name'        => $permission->value,
                    'label'       => $permission->label(),
                    'category'    => $permission->category(),
                    'description' => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                uniqueBy: ['name'],
                update: ['label', 'category', 'updated_at'],
            );

            $synced[] = $permission->value;
        }

        // Sincroniza role_permissions com o mapeamento do Enum
        DB::table('role_permissions')->truncate();

        foreach (RolesEnum::cases() as $role) {
            $rolePermissions = PermissionsEnum::forRole($role);

            foreach ($rolePermissions as $permission) {
                $permId = DB::table('permissions')
                    ->where('name', $permission->value)
                    ->value('id');

                if ($permId) {
                    DB::table('role_permissions')->insert([
                        'id'            => (string) Str::uuid(),
                        'role'          => $role->value,
                        'permission_id' => $permId,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        }

        return $synced;
    }
}
