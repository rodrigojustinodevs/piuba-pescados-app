# Refatoração: Service → UseCase + Repository

## Mudança Aplicada

Refatorado `PermissionResolverService` para seguir o padrão do projeto: **UseCases + Repositories**.

---

## Estrutura Anterior (Incorreta)

```
Middleware → PermissionResolverService (com queries diretas DB::table)
```

**Problemas:**
- ❌ Service fazendo queries diretas (viola Clean Architecture)
- ❌ Queries não isoladas em Repository
- ❌ Não segue padrão do projeto (UseCases + Repositories)

---

## Estrutura Nova (Correta)

```
Middleware → CheckUserPermissionUseCase → ResolveUserPermissionsUseCase → AuthRepository
```

**Benefícios:**
- ✅ Queries isoladas no Repository
- ✅ UseCases contêm lógica de negócio
- ✅ Segue padrão do projeto
- ✅ Testável e manutenível

---

## Arquivos Criados

### 1. UseCase: ResolveUserPermissionsUseCase
**Arquivo:** `app/Application/UseCases/Auth/ResolveUserPermissionsUseCase.php`

**Responsabilidades:**
- Gerencia cache de permissões
- Orquestra chamadas ao Repository
- Combina permissões globais e por empresa

### 2. UseCase: CheckUserPermissionUseCase
**Arquivo:** `app/Application/UseCases/Auth/CheckUserPermissionUseCase.php`

**Responsabilidades:**
- Verifica se usuário tem permissão específica
- Verifica se usuário tem qualquer uma das permissões
- Usa ResolveUserPermissionsUseCase internamente

---

## Arquivos Modificados

### 1. AuthRepositoryInterface (Domain)
**Arquivo:** `app/Domain/Repositories/AuthRepositoryInterface.php`

**Novos Métodos:**
```php
public function isMasterAdmin(User $user): bool;
public function getAllPermissions(): Collection;
public function getUserDirectPermissions(User $user): Collection;
public function getUserDirectPermissionsByCompany(User $user, string $companyId): Collection;
public function getUserRolePermissions(User $user): Collection;
public function getUserRolePermissionsByCompany(User $user, string $companyId): Collection;
```

### 2. AuthRepository (Infrastructure)
**Arquivo:** `app/Infrastructure/Persistence/AuthRepository.php`

**Implementações:**
- Todos os métodos novos implementados
- Queries isoladas e otimizadas
- Type hints adicionados para resolver erros de lint

### 3. CheckPermission Middleware
**Arquivo:** `app/Presentation/Middleware/CheckPermission.php`

**Mudança:**
- Antes: `PermissionResolverService`
- Depois: `CheckUserPermissionUseCase`

---

## Arquivos Removidos

- ❌ `app/Application/Services/PermissionResolverService.php` (deletado)

---

## Fluxo Completo

```
Request
  ↓
CheckPermission Middleware
  ↓
CheckUserPermissionUseCase::userHasAnyPermission()
  ↓
ResolveUserPermissionsUseCase::execute() [com cache]
  ↓
AuthRepository::getUserDirectPermissions()
AuthRepository::getUserRolePermissions()
AuthRepository::getUserDirectPermissionsByCompany()
AuthRepository::getUserRolePermissionsByCompany()
  ↓
Database
```

---

## Vantagens da Refatoração

### 1. **Alinhamento com Padrão do Projeto**
- ✅ Controllers → Services → UseCases → Repositories
- ✅ Agora segue o mesmo padrão dos outros módulos

### 2. **Separação de Responsabilidades**
- ✅ **Repository**: Apenas queries ao banco
- ✅ **UseCase**: Lógica de negócio e orquestração
- ✅ **Middleware**: Apenas orquestra

### 3. **Testabilidade**
- ✅ Repository pode ser mockado facilmente
- ✅ UseCases testáveis isoladamente
- ✅ Cache pode ser testado separadamente

### 4. **Manutenibilidade**
- ✅ Queries centralizadas no Repository
- ✅ Fácil mudar implementação (ex: Eloquent → Query Builder)
- ✅ Lógica de negócio isolada em UseCases

### 5. **Reutilização**
- ✅ UseCases podem ser usados em outros contextos
- ✅ Repository pode ser usado por outros UseCases
- ✅ Cache compartilhado entre diferentes verificações

---

## Comparação: Antes vs Depois

### Antes
```php
// Service com queries diretas
class PermissionResolverService {
    private function loadPermissionsFromDatabase() {
        $isMasterAdmin = DB::table('role_user')
            ->join('roles', ...)
            ->exists();
        // ... mais queries diretas
    }
}
```

### Depois
```php
// Repository com queries isoladas
class AuthRepository {
    public function isMasterAdmin(User $user): bool {
        return DB::table('role_user')
            ->join('roles', ...)
            ->exists();
    }
}

// UseCase com lógica de negócio
class ResolveUserPermissionsUseCase {
    public function execute(User $user, ?string $companyId): Collection {
        if ($this->authRepository->isMasterAdmin($user)) {
            return $this->authRepository->getAllPermissions();
        }
        // ... lógica de negócio
    }
}
```

---

## Conclusão

A refatoração está completa e alinhada com:
- ✅ Clean Architecture
- ✅ Padrão do projeto (UseCases + Repositories)
- ✅ Princípios SOLID
- ✅ Boas práticas de manutenibilidade

O código agora está mais organizado, testável e fácil de manter.

