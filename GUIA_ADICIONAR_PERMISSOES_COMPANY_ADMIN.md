# Guia Passo a Passo: Adicionar Permissões ao Role `company-admin`

Este guia explica como adicionar permissões ao role `company-admin` no sistema.

## 📋 Pré-requisitos

- Entender que o sistema possui permissões globais (vinculadas ao role na tabela `permission_role`)
- Saber quais permissões você deseja adicionar (ex: `create-tank`, `view-tank`, `update-tank`, `delete-tank`)

## 🎯 Método 1: Via Seeder (Recomendado)

Este é o método recomendado para produção, pois mantém o código versionado e pode ser executado em qualquer ambiente.

### Passo 1: Identificar as Permissões

Primeiro, identifique quais permissões você deseja adicionar. As permissões seguem o padrão:
- `{acao}-{entidade}`

Exemplos:
- `create-tank`
- `view-tank`
- `update-tank`
- `delete-tank`
- `create-client`
- `view-client`
- etc.z

### Passo 2: Criar ou Editar um Seeder

Você pode:
- **Opção A**: Editar o seeder existente `CompanyRolesPermissionsSeeder.php`
- **Opção B**: Criar um novo seeder específico (recomendado para manter organizado)

#### Opção A: Editar o Seeder Existente

Edite o arquivo `database/seeders/CompanyRolesPermissionsSeeder.php`:

```php
// Adicione as permissões que deseja atribuir ao company-admin
$companyAdminPermissions = [
    'view-company',
    'update-company',
    // Adicione aqui as novas permissões
    'create-tank',
    'view-tank',
    'update-tank',
    'delete-tank',
    'create-client',
    'view-client',
    'update-client',
    'delete-client',
    // ... adicione quantas precisar
];

$permissions = [];
foreach ($companyAdminPermissions as $permissionName) {
    $permission = Permission::where('name', $permissionName)->first();
    if ($permission) {
        $permissions[$permissionName] = $permission;
    }
}

// Atribuir permissões ao company-admin
$companyAdminPermissionIds = [];
foreach ($companyAdminPermissions as $permissionName) {
    if (isset($permissions[$permissionName])) {
        $permissionId = $permissions[$permissionName]->id;
        if (! $companyAdminRole->permissions()->where('permissions.id', $permissionId)->exists()) {
            $companyAdminPermissionIds[] = $permissionId;
        }
    }
}

if ($companyAdminPermissionIds !== []) {
    $companyAdminRole->permissions()->attach($companyAdminPermissionIds);
}
```

#### Opção B: Criar um Novo Seeder (Recomendado)

Crie um novo arquivo `database/seeders/CompanyAdminPermissionsSeeder.php`:

```php
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
     */
    public function run(): void
    {
        // Buscar o role company-admin
        $companyAdminRole = Role::where('name', 'company-admin')->first();

        if (! $companyAdminRole) {
            $this->command->warn('Role company-admin não encontrado!');
            return;
        }

        // Lista de permissões que o company-admin deve ter
        $permissionsToAdd = [
            // Permissões de Company
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
            
            // Adicione aqui outras permissões conforme necessário
            // 'create-alert',
            // 'view-alert',
            // etc.
        ];

        $permissionsToAttach = [];

        foreach ($permissionsToAdd as $permissionName) {
            // Buscar a permissão
            $permission = Permission::where('name', $permissionName)->first();

            if (! $permission) {
                $this->command->warn("Permissão '{$permissionName}' não encontrada. Pulando...");
                continue;
            }

            // Verificar se já não está associada
            if (! $companyAdminRole->permissions()->where('permissions.id', $permission->id)->exists()) {
                $permissionsToAttach[] = $permission->id;
            }
        }

        // Atribuir as permissões ao role
        if ($permissionsToAttach !== []) {
            $companyAdminRole->permissions()->attach($permissionsToAttach);
            $this->command->info('Permissões adicionadas ao company-admin: ' . count($permissionsToAttach));
        } else {
            $this->command->info('Nenhuma nova permissão para adicionar.');
        }
    }
}
```

### Passo 3: Registrar o Seeder (se criou um novo)

Se você criou um novo seeder, adicione-o ao `DatabaseSeeder.php`:

```php
public function run(): void
{
    $this->call([
        // ... outros seeders
        CompanyAdminPermissionsSeeder::class,
    ]);
}
```

### Passo 4: Executar o Seeder

Execute o seeder usando um dos comandos:

```bash
# Executar apenas o seeder específico
php artisan db:seed --class=CompanyAdminPermissionsSeeder

# Ou executar todos os seeders
php artisan db:seed
```

## 🎯 Método 2: Via Tinker (Para Testes Rápidos)

Use este método apenas para testes rápidos. Não é recomendado para produção.

### Passo 1: Abrir o Tinker

```bash
php artisan tinker
```

### Passo 2: Executar os Comandos

```php
// Buscar o role company-admin
$role = \App\Domain\Models\Role::where('name', 'company-admin')->first();

// Buscar as permissões que deseja adicionar
$permissions = \App\Domain\Models\Permission::whereIn('name', [
    'create-tank',
    'view-tank',
    'update-tank',
    'delete-tank',
])->get();

// Verificar quais já estão associadas
$existingPermissions = $role->permissions()->pluck('permissions.id')->toArray();

// Filtrar apenas as que ainda não estão associadas
$newPermissions = $permissions->filter(function ($permission) use ($existingPermissions) {
    return !in_array($permission->id, $existingPermissions);
});

// Adicionar as novas permissões
if ($newPermissions->isNotEmpty()) {
    $role->permissions()->attach($newPermissions->pluck('id')->toArray());
    echo "Permissões adicionadas: " . $newPermissions->pluck('name')->implode(', ');
} else {
    echo "Todas as permissões já estão associadas ao role.";
}
```

## 🎯 Método 3: Via Código Direto (Para Desenvolvimento)

Se você precisa adicionar permissões programaticamente em algum lugar do código:

```php
use App\Domain\Models\Permission;
use App\Domain\Models\Role;

// Buscar o role
$companyAdminRole = Role::where('name', 'company-admin')->first();

if (!$companyAdminRole) {
    throw new \Exception('Role company-admin não encontrado');
}

// Lista de permissões
$permissionNames = [
    'create-tank',
    'view-tank',
    'update-tank',
    'delete-tank',
];

// Buscar as permissões
$permissions = Permission::whereIn('name', $permissionNames)->get();

// Verificar e adicionar apenas as que não existem
$permissionsToAttach = [];

foreach ($permissions as $permission) {
    if (!$companyAdminRole->permissions()->where('permissions.id', $permission->id)->exists()) {
        $permissionsToAttach[] = $permission->id;
    }
}

// Adicionar as permissões
if (!empty($permissionsToAttach)) {
    $companyAdminRole->permissions()->attach($permissionsToAttach);
}
```

## ✅ Verificar se Funcionou

Após adicionar as permissões, você pode verificar de algumas formas:

### Via Tinker:

```php
$role = \App\Domain\Models\Role::where('name', 'company-admin')->first();
$permissions = $role->permissions()->pluck('name')->toArray();
print_r($permissions);
```

### Via Query Direta:

```sql
SELECT p.name 
FROM permissions p
INNER JOIN permission_role pr ON p.id = pr.permission_id
INNER JOIN roles r ON pr.role_id = r.id
WHERE r.name = 'company-admin';
```

## 🔄 Limpar Cache de Permissões

Após adicionar permissões, é importante limpar o cache:

```bash
php artisan cache:clear
```

Ou programaticamente:

```php
use App\Application\UseCases\Auth\ResolveUserPermissionsUseCase;

// Limpar cache de um usuário específico
$resolvePermissionsUseCase = app(ResolveUserPermissionsUseCase::class);
$resolvePermissionsUseCase->invalidateAllUserCache($userId);
```

## 📝 Notas Importantes

1. **Permissões Globais vs Por Company**: 
   - As permissões adicionadas via `permission_role` são **globais** para o role
   - Se você precisar de permissões específicas por company, use a tabela `company_user_permission` ou `company_user_role`

2. **Ordem de Execução dos Seeders**:
   - Certifique-se de que o `PermissionSeeder` foi executado antes
   - Certifique-se de que o `RolesPermissionsSeeder` foi executado antes

3. **Verificação de Duplicatas**:
   - O código sempre verifica se a permissão já está associada antes de adicionar
   - Isso evita erros de duplicação

4. **Master Admin**:
   - O `master_admin` tem acesso a todas as permissões automaticamente
   - Não é necessário adicionar permissões manualmente para este role

## 🎯 Exemplo Completo: Adicionar Todas as Permissões de Tank

Aqui está um exemplo completo de como adicionar todas as permissões relacionadas a Tank:

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use Illuminate\Database\Seeder;

class AddTankPermissionsToCompanyAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'company-admin')->first();
        
        if (!$role) {
            $this->command->error('Role company-admin não encontrado!');
            return;
        }

        $tankPermissions = [
            'create-tank',
            'view-tank',
            'update-tank',
            'delete-tank',
        ];

        $permissions = Permission::whereIn('name', $tankPermissions)->get();
        
        $toAttach = $permissions->filter(function ($permission) use ($role) {
            return !$role->permissions()->where('permissions.id', $permission->id)->exists();
        });

        if ($toAttach->isNotEmpty()) {
            $role->permissions()->attach($toAttach->pluck('id')->toArray());
            $this->command->info('Permissões de Tank adicionadas ao company-admin!');
        } else {
            $this->command->info('Todas as permissões de Tank já estão associadas.');
        }
    }
}
```

Execute com:
```bash
php artisan db:seed --class=AddTankPermissionsToCompanyAdminSeeder
```


