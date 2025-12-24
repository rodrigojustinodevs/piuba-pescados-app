<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\Company;
use App\Domain\Models\Role;
use App\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasterAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar ou criar o role master_admin
        $masterAdminRole = Role::firstOrCreate(['name' => 'master_admin']);

        // Buscar uma company (pegar a primeira disponível ou criar uma se não existir)
        $company = Company::first();

        if (! $company) {
            $this->command->warn('Nenhuma company encontrada. Execute o CompanyRolesPermissionsSeeder primeiro.');
            
            return;
        }

        // Criar usuário master_admin
        $masterAdminUser = User::firstOrCreate(
            ['email' => 'master.admin@piuba.com'],
            [
                'name'              => 'Master Admin',
                'email'             => 'master.admin@piuba.com',
                'password'          => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_admin'          => true,
            ]
        );

        // Vincular usuário ao role master_admin globalmente
        if (! $masterAdminUser->roles()->where('roles.id', $masterAdminRole->id)->exists()) {
            $masterAdminUser->roles()->attach($masterAdminRole->id);
        }

        // Vincular usuário à company na tabela company_user
        if (! $company->users()->where('users.id', $masterAdminUser->id)->exists()) {
            $company->users()->attach($masterAdminUser->id);
        }

        // Vincular role master_admin ao usuário na company (tabela company_user_role)
        $companyUserRoleExists = DB::table('company_user_role')
            ->where('company_id', $company->id)
            ->where('user_id', $masterAdminUser->id)
            ->where('role_id', $masterAdminRole->id)
            ->exists();

        if (! $companyUserRoleExists) {
            DB::table('company_user_role')->insert([
                'id'         => (string) \Illuminate\Support\Str::uuid(),
                'company_id' => $company->id,
                'user_id'    => $masterAdminUser->id,
                'role_id'    => $masterAdminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Usuário master_admin criado com sucesso!");
        $this->command->info("Email: {$masterAdminUser->email}");
        $this->command->info("Senha: password123");
        $this->command->info("Company vinculada: {$company->name} (ID: {$company->id})");
    }
}

