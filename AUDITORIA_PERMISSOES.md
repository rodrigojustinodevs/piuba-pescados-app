# Auditoria Técnica: Sistema de Permissões e Autorização

**Laravel 12 · PHP 8.3 · Clean Architecture · RBAC**

---

## 1. Análise do Middleware CheckPermission

### 1.1 Estado Atual

```12:34:app/Presentation/Middleware/CheckPermission.php
class CheckPermission
{
    public function __construct(
        protected AuthRepositoryInterface $authRepository
    ) {
    }

    public function handle(Request $request, Closure $next, string $permissions): mixed
    {
        // Suporta tanto vírgula quanto pipe como separadores
        $permissionsArray = preg_split('/[,|]/', $permissions);

        foreach ($permissionsArray as $permission) {
            $permission = trim($permission);

            if ($permission && $this->authRepository->userHasPermission($permission)) {
                return $next($request);
            }
        }

        throw new AccessDeniedHttpException('Forbidden: missing required permission. ' . $permissions);
    }
}
```

### 1.2 Avaliação de Acoplamento

**✅ Pontos Positivos:**
- Middleware delegando responsabilidade para `AuthRepositoryInterface` (respeita Dependency Inversion)
- Não acessa diretamente Models Eloquent
- Não viola diretamente a separação de camadas

**❌ Problemas Identificados:**

1. **Lógica de Parsing no Middleware**
   - Parsing de string (`preg_split`) deveria estar em um Service ou Value Object
   - Middleware deveria apenas orquestrar, não processar dados

2. **Múltiplas Chamadas ao Repository**
   - Em rotas com múltiplas permissões (ex: `create-tank|view-tank|update-tank|delete-tank`), o método `userHasPermission` é chamado sequencialmente
   - Cada chamada executa queries independentes sem otimização

3. **Falta de Contexto Multi-Tenant**
   - Não há verificação de empresa ativa no fluxo de permissões
   - Sistema é multi-empresa mas permissões não consideram contexto de empresa

### 1.3 Violações de Princípios

**SRP (Single Responsibility Principle):**
- Middleware faz parsing E orquestração E validação
- Deveria apenas orquestrar a verificação

**Middleware "Thin":**
- Lógica de processamento de string deveria estar em camada Application

---

## 2. Análise do Fluxo Completo de Autorização

### 2.1 Fluxo Atual Mapeado

```
Request → ApiAuthenticate → CheckPermission → AuthRepository::userHasPermission()
                                                      ↓
                                    Auth::user() [Query 1: Busca usuário]
                                                      ↓
                                    $user->roles()->where('name', 'master_admin')->exists() [Query 2]
                                                      ↓
                                    $user->permissions()->where('name', $permission)->exists() [Query 3]
                                                      ↓
                                    $user->roles()->whereHas('permissions', ...)->exists() [Query 4]
```

### 2.2 Problemas de Performance Identificados

#### 2.2.1 Queries Redundantes por Request

**Cenário Real:**
- Rota com middleware `permission:create-tank|view-tank|update-tank|delete-tank`
- Cada permissão executa 3-4 queries sequenciais
- **Total: 12-16 queries para uma única requisição**

**Código Problemático:**

```39:63:app/Infrastructure/Persistence/AuthRepository.php
public function userHasPermission(string $permission): bool
{
    $user = Auth::user();

    // Master admin tem acesso a todas as permissões
    if ($user->roles()->where('name', 'master_admin')->exists()) {
        return true;
    }

    // Verifica se o usuário tem a permissão diretamente
    if (
        $user
            ->permissions()
            ->where('name', $permission)
            ->exists()
    ) {
        return true;
    }

    // Verifica se algum role do usuário tem a permissão
    return (bool) $user
        ->roles()
        ->whereHas('permissions', fn ($q) => $q->where('name', $permission))
        ->exists();
}
```

**Problemas Específicos:**

1. **N+1 Queries:**
   - `Auth::user()` pode não estar em memória, causando query adicional
   - Cada verificação de role/permission executa query separada
   - `whereHas` é especialmente custoso (subquery)

2. **Ausência de Eager Loading:**
   - Roles e permissions não são carregados antecipadamente
   - Cada verificação recarrega relacionamentos

3. **Verificações Sequenciais:**
   - Três queries sequenciais quando poderia ser uma única query otimizada

#### 2.2.2 Ausência de Cache

- Nenhum mecanismo de cache implementado
- Permissões são verificadas repetidamente em cada request
- Dados de autorização não são persistidos no request lifecycle

#### 2.2.3 Contexto Multi-Tenant Não Aplicado

**Estrutura de Dados:**
- Existem tabelas `company_user_permission` e `company_user_role`
- Mas o método `userHasPermission` não considera `company_id`
- Permissões globais vs permissões por empresa não são diferenciadas

---

## 3. Análise de Performance e Otimizações

### 3.1 Banco de Dados

#### 3.1.1 Índices Ausentes

**Tabelas sem índices em colunas críticas:**

```13:23:database/migrations/2025_02_27_222857_create_roles_table.php
Schema::create('roles', function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->string('name');
    $table->timestamps();
});

Schema::create('permissions', function (Blueprint $table): void {
    $table->uuid('id')->primary();
    $table->string('name');
    $table->timestamps();
});
```

**Problema:** Coluna `name` em `roles` e `permissions` não possui índice, causando full table scan em buscas por nome.

**Tabelas Pivot:**
- `role_user`, `permission_user`, `permission_role` possuem primary keys compostas (adequado)
- Mas faltam índices adicionais para queries reversas (buscar roles de um user, permissions de um role)

#### 3.1.2 Queries Não Otimizadas

**Query Atual (3 queries sequenciais):**
```php
// Query 1
$user->roles()->where('name', 'master_admin')->exists();

// Query 2  
$user->permissions()->where('name', $permission)->exists();

// Query 3
$user->roles()->whereHas('permissions', fn ($q) => $q->where('name', $permission))->exists();
```

**Query Otimizada (1 query única):**
```sql
SELECT EXISTS(
    SELECT 1 FROM role_user ru
    INNER JOIN roles r ON r.id = ru.role_id
    WHERE ru.user_id = ? AND r.name = 'master_admin'
    
    UNION
    
    SELECT 1 FROM permission_user pu
    INNER JOIN permissions p ON p.id = pu.permission_id
    WHERE pu.user_id = ? AND p.name = ?
    
    UNION
    
    SELECT 1 FROM role_user ru
    INNER JOIN permission_role pr ON pr.role_id = ru.role_id
    INNER JOIN permissions p ON p.id = pr.permission_id
    WHERE ru.user_id = ? AND p.name = ?
) as has_permission
```

### 3.2 Código

#### 3.2.1 Uso Inadequado de Enums

**Estado Atual:**
- Enum `Can` existe mas contém apenas 4 permissões
- Sistema usa strings hardcoded nas rotas (`create-tank`, `view-tank`, etc.)
- Não há type-safety nas permissões

**Problema:**
```php
// Rotas usam strings
Route::middleware(['permission:create-tank|view-tank'])

// Mas enum Can não cobre essas permissões
enum Can: string {
    case ViewUser = 'view-user';
    case CreateUser = 'create-user';
    // ... apenas 4 casos
}
```

#### 3.2.2 Ausência de Value Objects

- Permissões são tratadas como strings primitivas
- Não há validação de formato
- Não há agregação de contexto (permissão + empresa + usuário)

#### 3.2.3 Logging em Produção

**Problema Crítico:**

```194:200:app/Infrastructure/Providers/AppServiceProvider.php
Log::info(
    'Checking permission: ' . $permission->value,
    [
        'user'  => $user->id,
        'check' => $check ? 'true' : 'false',
    ]
);
```

- **Log::info** em cada verificação de Gate (não usado pelo middleware, mas configurado)
- Em alta carga, gera milhares de logs por minuto
- Impacto significativo em I/O e storage

### 3.3 Cache

#### 3.3.1 Ausência Total de Cache

- Nenhum cache de permissões por usuário
- Nenhum cache de contexto de autorização
- Dados recalculados em cada request

#### 3.3.2 Oportunidades de Cache

**Cache por Request (Request Lifecycle):**
- Carregar todas as permissões do usuário uma vez no início do request
- Armazenar em propriedade do middleware ou service
- Reutilizar em verificações subsequentes

**Cache Persistente:**
- Cache de permissões por `user_id` + `company_id`
- TTL: 5-15 minutos (dependendo da frequência de mudanças)
- Invalidação: ao atualizar roles/permissions do usuário

**Estrutura Sugerida:**
```php
Cache::tags(['permissions', "user:{$userId}", "company:{$companyId}"])
    ->remember("user:{$userId}:company:{$companyId}:permissions", 600, function() {
        // Carregar todas as permissões
    });
```

---

## 4. Arquitetura e Boas Práticas Laravel 12

### 4.1 Uso de Gates vs Middleware

**Estado Atual:**
- Gates configurados mas **não utilizados** pelo middleware
- Middleware usa diretamente o repository
- Duplicação de lógica de verificação

**Problema:**
```182:206:app/Infrastructure/Providers/AppServiceProvider.php
private function configGates(): void
{
    foreach (Can::cases() as $permission) {
        Gate::define(
            $permission->value,
            function (User $user) use ($permission) {
                /** @var User $user */
                $check = $user
                    ->permissions()
                    ->whereName($permission->value)
                    ->exists();

                Log::info(
                    'Checking permission: ' . $permission->value,
                    [
                        'user'  => $user->id,
                        'check' => $check ? 'true' : 'false',
                    ]
                );

                return $check;
            }
        );
    }
}
```

- Gates configurados mas middleware não os utiliza
- Lógica duplicada entre Gates e `AuthRepository::userHasPermission`
- Gates não consideram contexto de empresa

### 4.2 Policies Não Utilizadas

- Laravel oferece Policies para autorização mais complexa
- Sistema não utiliza Policies, apenas middleware
- Policies permitiriam lógica mais complexa (ex: "usuário pode editar apenas recursos da sua empresa")

### 4.3 Separação de Responsabilidades

**✅ Respeitado:**
- Repository pattern implementado
- Interface `AuthRepositoryInterface` no Domain
- Implementação `AuthRepository` em Infrastructure

**❌ Violado:**
- Lógica de negócio (verificação de master_admin) no Repository
- Repository fazendo queries complexas que deveriam estar em Service
- Ausência de Service layer para orquestração de autorização

---

## 5. Propostas de Refatoração

### 5.1 Extração de PermissionResolver Service

**Problema Atual:**
- Lógica de resolução de permissões espalhada entre Repository e Middleware
- Queries não otimizadas
- Sem cache

**Solução Proposta:**

**1. Criar Service na camada Application:**

```php
// app/Application/Services/PermissionResolverService.php
namespace App\Application\Services;

use App\Domain\Models\User;
use Illuminate\Support\Collection;

interface PermissionResolverServiceInterface
{
    /**
     * Resolve todas as permissões do usuário (com cache)
     */
    public function resolveUserPermissions(User $user, ?string $companyId = null): Collection;
    
    /**
     * Verifica se usuário tem permissão específica
     */
    public function userHasPermission(User $user, string $permission, ?string $companyId = null): bool;
    
    /**
     * Verifica se usuário tem qualquer uma das permissões
     */
    public function userHasAnyPermission(User $user, array $permissions, ?string $companyId = null): bool;
}
```

**2. Implementação com Cache e Query Otimizada:**

```php
// app/Infrastructure/Services/PermissionResolverService.php
namespace App\Infrastructure\Services;

use App\Application\Services\PermissionResolverServiceInterface;
use App\Domain\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionResolverService implements PermissionResolverServiceInterface
{
    private const CACHE_TTL = 600; // 10 minutos
    
    public function resolveUserPermissions(User $user, ?string $companyId = null): Collection
    {
        $cacheKey = $this->getCacheKey($user->id, $companyId);
        
        return Cache::tags(['permissions', "user:{$user->id}"])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($user, $companyId) {
                return $this->loadPermissionsFromDatabase($user, $companyId);
            });
    }
    
    public function userHasPermission(User $user, string $permission, ?string $companyId = null): bool
    {
        $permissions = $this->resolveUserPermissions($user, $companyId);
        
        return $permissions->contains('name', $permission);
    }
    
    public function userHasAnyPermission(User $user, array $permissions, ?string $companyId = null): bool
    {
        $userPermissions = $this->resolveUserPermissions($user, $companyId);
        
        return $userPermissions->pluck('name')->intersect($permissions)->isNotEmpty();
    }
    
    private function loadPermissionsFromDatabase(User $user, ?string $companyId): Collection
    {
        // Verificar se é master_admin (uma query)
        $isMasterAdmin = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->where('roles.name', 'master_admin')
            ->exists();
        
        if ($isMasterAdmin) {
            // Retornar todas as permissões do sistema
            return DB::table('permissions')->select('name')->get();
        }
        
        // Query única otimizada para todas as permissões do usuário
        $query = DB::table('permission_user')
            ->select('permissions.name')
            ->join('permissions', 'permissions.id', '=', 'permission_user.permission_id')
            ->where('permission_user.user_id', $user->id);
        
        if ($companyId) {
            $query->join('company_user_permission', function ($join) use ($user, $companyId) {
                $join->on('company_user_permission.permission_id', '=', 'permission_user.permission_id')
                     ->where('company_user_permission.user_id', '=', $user->id)
                     ->where('company_user_permission.company_id', '=', $companyId);
            });
        }
        
        $directPermissions = $query->pluck('name');
        
        // Permissões via roles
        $rolePermissions = DB::table('role_user')
            ->select('permissions.name')
            ->join('permission_role', 'permission_role.role_id', '=', 'role_user.role_id')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('role_user.user_id', $user->id);
        
        if ($companyId) {
            $rolePermissions->join('company_user_role', function ($join) use ($user, $companyId) {
                $join->on('company_user_role.role_id', '=', 'role_user.role_id')
                     ->where('company_user_role.user_id', '=', $user->id)
                     ->where('company_user_role.company_id', '=', $companyId);
            });
        }
        
        $rolePermissions = $rolePermissions->pluck('name');
        
        return $directPermissions->merge($rolePermissions)->unique();
    }
    
    private function getCacheKey(string $userId, ?string $companyId): string
    {
        return "user:{$userId}:permissions" . ($companyId ? ":company:{$companyId}" : '');
    }
}
```

**Ganhos Técnicos:**
- ✅ Redução de 12-16 queries para 1-2 queries por request
- ✅ Cache reduz queries a zero em requests subsequentes
- ✅ Suporte a contexto multi-tenant
- ✅ Service layer adequado (Application)
- ✅ Testável e mockável

### 5.2 Value Object para Permissões

**Problema:**
- Permissões como strings primitivas
- Sem validação de formato
- Sem type-safety

**Solução:**

```php
// app/Domain/ValueObjects/Permission.php
namespace App\Domain\ValueObjects;

readonly class Permission
{
    public function __construct(
        public string $name
    ) {
        $this->validate();
    }
    
    private function validate(): void
    {
        if (!preg_match('/^[a-z]+(-[a-z]+)+$/', $this->name)) {
            throw new \InvalidArgumentException("Invalid permission format: {$this->name}");
        }
    }
    
    public function equals(Permission $other): bool
    {
        return $this->name === $other->name;
    }
    
    public function toString(): string
    {
        return $this->name;
    }
    
    public static function fromString(string $name): self
    {
        return new self($name);
    }
}
```

### 5.3 Refatoração do Middleware CheckPermission

**Middleware Refatorado (Thin):**

```php
// app/Presentation/Middleware/CheckPermission.php
namespace App\Presentation\Middleware;

use App\Application\Services\PermissionResolverServiceInterface;
use App\Domain\ValueObjects\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckPermission
{
    public function __construct(
        protected PermissionResolverServiceInterface $permissionResolver
    ) {
    }
    
    public function handle(Request $request, Closure $next, string $permissions): mixed
    {
        $user = $request->user();
        
        if (!$user) {
            throw new AccessDeniedHttpException('Unauthorized');
        }
        
        $permissionList = $this->parsePermissions($permissions);
        $companyId = $this->resolveCompanyId($request);
        
        if ($this->permissionResolver->userHasAnyPermission($user, $permissionList, $companyId)) {
            return $next($request);
        }
        
        throw new AccessDeniedHttpException('Forbidden: missing required permission. ' . $permissions);
    }
    
    /**
     * @return array<string>
     */
    private function parsePermissions(string $permissions): array
    {
        return array_map(
            'trim',
            preg_split('/[,|]/', $permissions)
        );
    }
    
    private function resolveCompanyId(Request $request): ?string
    {
        // Implementar lógica de resolução de empresa ativa
        // Ex: header X-Company-Id, route parameter, etc.
        return $request->header('X-Company-Id') 
            ?? $request->route('companyId') 
            ?? null;
    }
}
```

**Ganhos:**
- ✅ Middleware apenas orquestra
- ✅ Parsing isolado (pode ser extraído para Service se necessário)
- ✅ Suporte a contexto multi-tenant
- ✅ Uso de Service layer

### 5.4 Migration para Índices

**Criar migration:**

```php
// database/migrations/YYYY_MM_DD_HHMMSS_add_indexes_to_permissions_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->index('name', 'idx_roles_name');
        });
        
        Schema::table('permissions', function (Blueprint $table): void {
            $table->index('name', 'idx_permissions_name');
        });
        
        Schema::table('role_user', function (Blueprint $table): void {
            $table->index('user_id', 'idx_role_user_user_id');
        });
        
        Schema::table('permission_user', function (Blueprint $table): void {
            $table->index('user_id', 'idx_permission_user_user_id');
        });
        
        Schema::table('permission_role', function (Blueprint $table): void {
            $table->index('role_id', 'idx_permission_role_role_id');
        });
    }
    
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropIndex('idx_roles_name');
        });
        
        Schema::table('permissions', function (Blueprint $table): void {
            $table->dropIndex('idx_permissions_name');
        });
        
        Schema::table('role_user', function (Blueprint $table): void {
            $table->dropIndex('idx_role_user_user_id');
        });
        
        Schema::table('permission_user', function (Blueprint $table): void {
            $table->dropIndex('idx_permission_user_user_id');
        });
        
        Schema::table('permission_role', function (Blueprint $table): void {
            $table->dropIndex('idx_permission_role_role_id');
        });
    }
};
```

### 5.5 Remoção de Logging em Produção

**Correção em AppServiceProvider:**

```php
private function configGates(): void
{
    foreach (Can::cases() as $permission) {
        Gate::define(
            $permission->value,
            function (User $user) use ($permission) {
                return $user
                    ->permissions()
                    ->whereName($permission->value)
                    ->exists();
            }
        );
    }
}
```

**Ou, se logging for necessário, usar nível debug:**

```php
if (config('app.debug')) {
    Log::debug('Checking permission: ' . $permission->value, [
        'user' => $user->id,
    ]);
}
```

---

## 6. Resumo Executivo

### 6.1 Problemas Críticos

1. **Performance:**
   - 12-16 queries por request em rotas com múltiplas permissões
   - Ausência total de cache
   - Queries não otimizadas (N+1, whereHas custoso)

2. **Arquitetura:**
   - Lógica de negócio no Repository
   - Middleware fazendo parsing (deveria estar em Service)
   - Ausência de Service layer para autorização

3. **Multi-Tenant:**
   - Permissões não consideram contexto de empresa
   - Tabelas `company_user_permission` existem mas não são utilizadas

4. **Banco de Dados:**
   - Índices ausentes em colunas críticas (`name` em roles/permissions)
   - Queries fazendo full table scan

5. **Logging:**
   - `Log::info` em cada verificação de Gate (alto volume em produção)

### 6.2 Impacto Estimado das Melhorias

**Performance:**
- Redução de 12-16 queries para 1-2 queries (primeira chamada)
- Zero queries em requests subsequentes (cache)
- **Ganho estimado: 80-95% de redução em queries de autorização**

**Escalabilidade:**
- Cache permite suportar maior volume de requests
- Índices melhoram performance em bases grandes
- Service layer facilita testes e manutenção

**Manutenibilidade:**
- Código mais testável (Services mockáveis)
- Separação clara de responsabilidades
- Suporte nativo a multi-tenant

### 6.3 Priorização de Implementação

**Alta Prioridade (Impacto Imediato):**
1. Adicionar índices nas tabelas (migration simples, ganho imediato)
2. Remover `Log::info` de produção
3. Implementar `PermissionResolverService` com cache

**Média Prioridade (Arquitetura):**
4. Refatorar middleware para usar Service
5. Implementar suporte a contexto multi-tenant
6. Criar Value Objects para permissões

**Baixa Prioridade (Melhorias Incrementais):**
7. Expandir enum `Can` para todas as permissões
8. Considerar uso de Policies para casos complexos
9. Implementar invalidação automática de cache

---

## 7. Conclusão

O sistema atual apresenta uma arquitetura base sólida (Clean Architecture, Repository Pattern), mas sofre de problemas significativos de performance e algumas violações de separação de responsabilidades. As principais oportunidades de melhoria estão em:

1. **Otimização de queries** (consolidação e índices)
2. **Implementação de cache** (request lifecycle + persistente)
3. **Extração de Service layer** para lógica de autorização
4. **Suporte adequado a multi-tenant** no fluxo de permissões

As refatorações propostas mantêm o respeito à arquitetura em camadas e não alteram regras de negócio existentes, apenas otimizam a execução e melhoram a organização do código.

