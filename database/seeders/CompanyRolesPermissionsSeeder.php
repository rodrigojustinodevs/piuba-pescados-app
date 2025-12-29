<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\Company;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyRolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Garantir que os roles existam
        $adminRole        = Role::firstOrCreate(['name' => 'admin']);
        $companyAdminRole = Role::firstOrCreate(['name' => 'company-admin']);
        $guestRole        = Role::firstOrCreate(['name' => 'guest']);

        // Buscar permissões relacionadas a company
        $companyPermissions = [
            'create-company',
            'update-company',
            'delete-company',
            'view-company',
        ];

        $permissions = [];

        foreach ($companyPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();

            if ($permission) {
                $permissions[$permissionName] = $permission;
            }
        }

        // Atribuir permissões aos roles
        // Admin tem todas as permissões de company
        $adminPermissionIds = [];

        foreach ($companyPermissions as $permissionName) {
            if (isset($permissions[$permissionName])) {
                $permissionId = $permissions[$permissionName]->id;

                // Verificar se já não está associado
                if (! $adminRole->permissions()->where('permissions.id', $permissionId)->exists()) {
                    $adminPermissionIds[] = $permissionId;
                }
            }
        }

        if (! empty($adminPermissionIds)) {
            $adminRole->permissions()->attach($adminPermissionIds);
        }

        // Company-admin tem permissões de visualização e atualização (mas não delete)
        $companyAdminPermissionIds = [];

        if (isset($permissions['view-company'])) {
            $permissionId = $permissions['view-company']->id;

            if (! $companyAdminRole->permissions()->where('permissions.id', $permissionId)->exists()) {
                $companyAdminPermissionIds[] = $permissionId;
            }
        }

        if (isset($permissions['update-company'])) {
            $permissionId = $permissions['update-company']->id;

            if (! $companyAdminRole->permissions()->where('permissions.id', $permissionId)->exists()) {
                $companyAdminPermissionIds[] = $permissionId;
            }
        }

        if (! empty($companyAdminPermissionIds)) {
            $companyAdminRole->permissions()->attach($companyAdminPermissionIds);
        }

        // Guest tem apenas visualização
        if (isset($permissions['view-company'])) {
            $permissionId = $permissions['view-company']->id;

            if (! $guestRole->permissions()->where('permissions.id', $permissionId)->exists()) {
                $guestRole->permissions()->attach($permissionId);
            }
        }

        // Criar companies de teste
        $companies = [
            [
                'name'                 => 'Piscicultura AquaVida',
                'cnpj'                 => '12.345.678/0001-90',
                'email'                => 'contato@aquavida.com.br',
                'phone'                => '(85) 99999-1111',
                'address_street'       => 'Rua das Águas',
                'address_number'       => '100',
                'address_complement'   => 'Fazenda AquaVida',
                'address_neighborhood' => 'Zona Rural',
                'address_city'         => 'Fortaleza',
                'address_state'        => 'CE',
                'address_zip_code'     => '60000-000',
                'status'               => 'active',
            ],
            [
                'name'                 => 'Peixes do Nordeste LTDA',
                'cnpj'                 => '98.765.432/0001-10',
                'email'                => 'contato@peixesnordeste.com.br',
                'phone'                => '(85) 99999-2222',
                'address_street'       => 'Avenida dos Peixes',
                'address_number'       => '250',
                'address_complement'   => 'Sede Administrativa',
                'address_neighborhood' => 'Centro',
                'address_city'         => 'Fortaleza',
                'address_state'        => 'CE',
                'address_zip_code'     => '60010-000',
                'status'               => 'active',
            ],
            [
                'name'                 => 'Aquacultura Piuba',
                'cnpj'                 => '11.222.333/0001-44',
                'email'                => 'contato@aquaculturapiuba.com.br',
                'phone'                => '(85) 99999-3333',
                'address_street'       => 'Estrada do Tanque',
                'address_number'       => 'KM 15',
                'address_complement'   => 'Propriedade Rural',
                'address_neighborhood' => 'Distrito Industrial',
                'address_city'         => 'Maracanaú',
                'address_state'        => 'CE',
                'address_zip_code'     => '61900-000',
                'status'               => 'active',
            ],
            [
                'name'                 => 'Pesque e Pague Ceará',
                'cnpj'                 => '55.666.777/0001-88',
                'email'                => 'contato@pesquepaguece.com.br',
                'phone'                => '(85) 99999-4444',
                'address_street'       => 'Rodovia BR-116',
                'address_number'       => 'S/N',
                'address_complement'   => 'Lote 5',
                'address_neighborhood' => 'Zona Rural',
                'address_city'         => 'Eusébio',
                'address_state'        => 'CE',
                'address_zip_code'     => '61760-000',
                'status'               => 'active',
            ],
            [
                'name'                 => 'Tilápia Premium',
                'cnpj'                 => '22.333.444/0001-55',
                'email'                => 'contato@tilapiapremium.com.br',
                'phone'                => '(85) 99999-5555',
                'address_street'       => 'Rua dos Tanques',
                'address_number'       => '500',
                'address_complement'   => 'Complexo Aquícola',
                'address_neighborhood' => 'Industrial',
                'address_city'         => 'Caucaia',
                'address_state'        => 'CE',
                'address_zip_code'     => '61600-000',
                'status'               => 'active',
            ],
        ];

        foreach ($companies as $companyData) {
            $company = Company::firstOrCreate(
                ['cnpj' => $companyData['cnpj']],
                $companyData
            );

            // Associar roles padrões à company (se ainda não estiverem associados)
            $rolesToAttach = [];

            if (! $company->roles()->where('roles.id', $adminRole->id)->exists()) {
                $rolesToAttach[] = [
                    'id'      => (string) Str::uuid(),
                    'role_id' => $adminRole->id,
                ];
            }

            if (! $company->roles()->where('roles.id', $companyAdminRole->id)->exists()) {
                $rolesToAttach[] = [
                    'id'      => (string) Str::uuid(),
                    'role_id' => $companyAdminRole->id,
                ];
            }

            if (! $company->roles()->where('roles.id', $guestRole->id)->exists()) {
                $rolesToAttach[] = [
                    'id'      => (string) Str::uuid(),
                    'role_id' => $guestRole->id,
                ];
            }

            if (! empty($rolesToAttach)) {
                foreach ($rolesToAttach as $roleData) {
                    DB::table('company_role')->insert([
                        'id'         => $roleData['id'],
                        'company_id' => $company->id,
                        'role_id'    => $roleData['role_id'],
                    ]);
                }
            }
        }

        $this->command->info('Roles e permissões de company configuradas com sucesso!');
        $this->command->info('Companies de teste criadas: ' . count($companies));
    }
}
