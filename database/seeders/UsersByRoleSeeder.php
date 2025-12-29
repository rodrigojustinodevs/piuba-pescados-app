<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\Company;
use App\Domain\Models\Role;
use App\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersByRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar uma company (usar a primeira disponível)
        $company = Company::first();

        if (! $company) {
            $this->command->warn('Nenhuma company encontrada. Execute o CompanyRolesPermissionsSeeder primeiro.');

            return;
        }

        // Definir usuários para cada role
        $usersByRole = [
            'admin' => [
                'name'  => 'Admin User',
                'email' => 'admin@piuba.com',
            ],
            'master_admin' => [
                'name'  => 'Master Admin User',
                'email' => 'master.admin@piuba.com',
            ],
            'company_admin' => [
                'name'  => 'Company Admin User',
                'email' => 'company.admin@piuba.com',
            ],
            'company-admin' => [
                'name'  => 'Company Admin (with dash)',
                'email' => 'company-admin@piuba.com',
            ],
            'manager' => [
                'name'  => 'Manager User',
                'email' => 'manager@piuba.com',
            ],
            'operator' => [
                'name'  => 'Operator User',
                'email' => 'operator@piuba.com',
            ],
            'guest' => [
                'name'  => 'Guest User',
                'email' => 'guest@piuba.com',
            ],
        ];

        $this->command->info('Criando usuários para cada role...');

        foreach ($usersByRole as $roleName => $userData) {
            // Buscar ou criar o role
            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                $this->command->warn("Role '{$roleName}' não encontrado. Pulando...");

                continue;
            }

            // Criar ou buscar o usuário
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name'              => $userData['name'],
                    'password'          => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'is_admin'          => in_array($roleName, ['admin', 'master_admin']),
                ]
            );

            // Associar role globalmente ao usuário (tabela role_user)
            if (! $user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }

            // Associar usuário à company (tabela company_user)
            $companyUserExists = DB::table('company_user')
                ->where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->exists();

            if (! $companyUserExists) {
                DB::table('company_user')->insert([
                    'id'         => (string) Str::uuid(),
                    'company_id' => $company->id,
                    'user_id'    => $user->id,
                ]);
            }

            // Associar role do usuário na company (tabela company_user_role)
            $companyUserRoleExists = DB::table('company_user_role')
                ->where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->exists();

            if (! $companyUserRoleExists) {
                DB::table('company_user_role')->insert([
                    'id'         => (string) Str::uuid(),
                    'company_id' => $company->id,
                    'user_id'    => $user->id,
                    'role_id'    => $role->id,
                ]);
            }

            $this->command->info("✓ Usuário '{$userData['name']}' criado/atualizado com role '{$roleName}'");
            $this->command->line("  Email: {$userData['email']} | Senha: password123");
        }

        $this->command->newLine();
        $this->command->info('Todos os usuários foram criados com sucesso!');
        $this->command->info("Company vinculada: {$company->name} (ID: {$company->id})");
        $this->command->newLine();
        $this->command->comment('Você pode usar qualquer um desses usuários para testar os diferentes tipos de acesso.');
    }
}
