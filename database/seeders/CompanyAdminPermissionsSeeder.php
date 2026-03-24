<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

class CompanyAdminPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Este seeder adiciona permissões ao role company-admin.
     * Para adicionar novas permissões, edite o array $permissionsToAdd abaixo.
     */
    public function run(): void
    {
        // Buscar o role company-admin
        $companyAdminRole = Role::where('name', 'company-admin')->first();

        if (! $companyAdminRole) {
            $this->command->warn('Role company-admin não encontrado! Certifique-se de que o RolesPermissionsSeeder foi executado.');

            return;
        }

        // ============================================
        // LISTA DE PERMISSÕES PARA O COMPANY-ADMIN
        // ============================================
        // Adicione aqui as permissões que o company-admin deve ter
        // Formato: 'acao-entidade' (ex: 'create-tank', 'view-client')
        $permissionsToAdd = [
            // Permissões de Company (já existentes no CompanyRolesPermissionsSeeder)
            'view-company',
            'update-company',

            // Permissões de Tank
            'create-tank',
            'view-tank',
            'update-tank',
            'delete-tank',

            // Permissões de Client
            'create-client',
            'view-client',
            'update-client',
            'delete-client',

            // Permissões de Alert
            'create-alert',
            'view-alert',
            'update-alert',
            'delete-alert',

            // Permissões de Batch
            'create-batch',
            'view-batch',
            'update-batch',
            'delete-batch',

            // Permissões de Biometry
            'create-batch', // Nota: biometry usa as mesmas permissões de batch
            'view-batch',
            'update-batch',
            'delete-batch',

            // Permissões de Cost Allocation
            'create-cost-allocation',
            'view-cost-allocation',
            'update-cost-allocation',
            'delete-cost-allocation',

            // Permissões de Dashboard
            'view-dashboard',

            // Permissões de Feeding
            'create-feeding',
            'view-feeding',
            'update-feeding',
            'delete-feeding',

            // Permissões de Feed Inventory (estoque de ração)
            'create-feed-inventory',
            'view-feed-inventory',
            'update-feed-inventory',
            'delete-feed-inventory',

            // Permissões de Financial Category
            'create-financial-category',
            'view-financial-category',
            'update-financial-category',
            'delete-financial-category',

            // Permissões de Financial Transaction
            'create-financial-transaction',
            'view-financial-transaction',
            'update-financial-transaction',
            'delete-financial-transaction',

            // Permissões de Growth Curve
            'create-growth-curve',
            'view-growth-curve',
            'update-growth-curve',
            'delete-growth-curve',

            // Permissões de Harvest
            'create-harvest',
            'view-harvest',
            'update-harvest',
            'delete-harvest',

            // Permissões de Mortality
            'create-mortality',
            'view-mortality',
            'update-mortality',
            'delete-mortality',

            // Permissões de Purchase
            'create-purchase',
            'view-purchase',
            'update-purchase',
            'delete-purchase',

            // Permissões de Sale
            'create-sale',
            'view-sale',
            'update-sale',
            'delete-sale',

            // Permissões de Sensor
            'create-sensor',
            'view-sensor',
            'update-sensor',
            'delete-sensor',

            // Permissões de leituras de sensor
            'create-sensor-reading',
            'view-sensor-reading',
            'update-sensor-reading',
            'delete-sensor-reading',

            // Permissões de Stocking (aquaculture: povoamento/estocagem)
            'create-stocking',
            'view-stocking',
            'update-stocking',
            'delete-stocking',

            // Permissões de Stock
            'create-stock',
            'view-stock',
            'update-stock',
            'delete-stock',

            // Permissões de Supplier
            'create-supplier',
            'view-supplier',
            'update-supplier',
            'delete-supplier',

            // Permissões de Transfer
            'create-transfer',
            'view-transfer',
            'update-transfer',
            'delete-transfer',

            // Permissões de Water Quality
            'create-water-quality',
            'view-water-quality',
            'update-water-quality',
            'delete-water-quality',

            // Adicione aqui outras permissões conforme necessário
        ];

        // ============================================
        // PROCESSAMENTO
        // ============================================
        $permissionsToAttach        = [];
        $permissionsNotFound        = [];
        $permissionsAlreadyAttached = [];

        foreach ($permissionsToAdd as $permissionName) {
            // Buscar a permissão
            $permission = Permission::where('name', $permissionName)->first();

            if (! $permission) {
                $permissionsNotFound[] = $permissionName;
                $this->command->warn("Permissão '{$permissionName}' não encontrada. Pulando...");

                continue;
            }

            // Verificar se já não está associada
            if ($companyAdminRole->permissions()->where('permissions.id', $permission->id)->exists()) {
                $permissionsAlreadyAttached[] = $permissionName;

                continue;
            }

            $permissionsToAttach[] = $permission->id;
        }

        // ============================================
        // ATRIBUIR PERMISSÕES
        // ============================================
        if ($permissionsToAttach !== []) {
            $companyAdminRole->permissions()->attach($permissionsToAttach);

            $this->command->info('✅ Permissões adicionadas ao company-admin: ' . count($permissionsToAttach));
            $this->command->line('   Permissões: ' . implode(', ', array_map(fn ($id) => Permission::find($id)->name, $permissionsToAttach)));
        } else {
            $this->command->info('ℹ️  Nenhuma nova permissão para adicionar.');
        }

        // ============================================
        // RELATÓRIO
        // ============================================
        if ($permissionsAlreadyAttached !== []) {
            $this->command->line('ℹ️  Permissões já associadas (' . count($permissionsAlreadyAttached) . '): ' . implode(', ', $permissionsAlreadyAttached));
        }

        if ($permissionsNotFound !== []) {
            $this->command->warn('⚠️  Permissões não encontradas (' . count($permissionsNotFound) . '): ' . implode(', ', $permissionsNotFound));
            $this->command->warn('   Certifique-se de que o PermissionSeeder foi executado.');
        }

        // Mostrar total de permissões do role
        $totalPermissions = $companyAdminRole->permissions()->count();
        $this->command->info("📊 Total de permissões do company-admin: {$totalPermissions}");
    }
}
