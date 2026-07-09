# Onboarding — Papel `company_admin`

Guia prático de primeiros passos para quem assume o papel **`company_admin`** no Piuba API. Não repete as regras de negócio completas (ver [`DOCUMENTACAO_REGRAS_NEGOCIO.md`](DOCUMENTACAO_REGRAS_NEGOCIO.md)) nem o mapa de arquivos por camada (ver [`MAPA_ARQUITETURA_PROJETO.md`](MAPA_ARQUITETURA_PROJETO.md)) — aqui o foco é "o que fazer, em que ordem, e o que hoje não funciona".

> ⚠️ **Leia a seção 2 antes de tudo.** Hoje, por um gap de configuração no sistema de permissões, um `company_admin` recém-criado **não consegue operar a maior parte do sistema** (tanques, lotes, povoamento, vendas parciais, compras, financeiro, clientes etc.). Este documento descreve o fluxo pretendido e deixa explícito onde ele quebra na prática.

---

## 1. O que é o `company_admin`

`company_admin` é o papel mais alto **dentro de uma empresa** (abaixo apenas do `master_admin`, que é global e enxerga todas as empresas).

| Role | Nível (`RolesEnum::level()`) | Escopo |
|------|------|--------|
| guest | 0 | Empresa |
| operator | 1 | Empresa |
| manager | 2 | Empresa |
| admin | 3 | Empresa |
| **company_admin** | **4** | **Empresa** |
| master_admin | 99 | Global (`isGlobal()` = bypass de tenant) |

Fonte: `app/Domain/Enums/RolesEnum.php:9-46`.

O papel é atribuído **por empresa**, na coluna `company_user.role` (migration `database/migrations/2026_04_20_120000_create_company_user_table.php:17`, default `'operator'`). Não existe hierarquia global de "usuário company_admin" — o mesmo usuário pode ter roles diferentes em empresas diferentes.

---

## 2. Pré-requisito crítico: gap no sistema de permissões (hoje)

O sistema real de autorização (o que roda em produção) funciona assim:

```
Rota → middleware permission:<string> → PermissionResolver::hasPermission()
                                              │
                                              ├─ company_user.role (RolesEnum)
                                              ├─ PermissionsEnum::forRole(role)   ← defaults por role
                                              └─ user_company_permissions        ← overrides grant/deny por usuário
```

Arquivos-chave: `app/Domain/Enums/PermissionsEnum.php:171-181` (defaults do `company_admin`), `app/Infrastructure/Security/PermissionResolver.php`, `app/Presentation/Middleware/CheckPermission.php`, `app/Domain/ValueObjects/TenantContext.php`.

**O problema:** `PermissionsEnum::forRole()` só define permissões para um domínio genérico (dashboard, user, company, sale, product, report, setting, customer, inventory, finance, audit, sensor). Ele **não tem casos** para as entidades específicas de aquicultura — tank, alert, harvest, cost-allocation, growth-curve, feeding, feed-inventory, mortality, stocking, stocking-history, tank-history, sensor-reading, stock, purchase, supplier, client, sales-order, financial-category, financial-transaction. Como consequência, essas strings **nunca aparecem** nos defaults de nenhum role — nem `admin`, nem `company_admin`.

Todas as rotas de `routes/app/company/*.php` (exceto 5, ver tabela) exigem `permission:<string>` — sem bypass por role. Resultado, por módulo:

| Situação | Módulos | O que acontece com `company_admin` hoje |
|---|---|---|
| **A — sem gate de permissão** (só a role mínima `role:operator,admin,company_admin,master_admin` do grupo, `routes/api.php:47`) | Stocking, WaterQuality, Transfer, Supplier, Supply | Funciona — mas também funciona para `operator`, o nível mais baixo. Sem granularidade nenhuma. |
| **B — gate existe, mas a permissão não está nos defaults de nenhum role** | Tank, Batch, Alert, CostAllocation, Feeding, FeedInventory, FinancialCategory, FinancialTransaction, GrowthCurve, Harvest, Mortality, Purchase, SalesOrder, TankHistory, StockingHistory, SensorReading, Stock, Client | **403 Forbidden** para `company_admin` (e para qualquer role) enquanto o gap não for corrigido |
| **C — funciona por padrão hoje** | Dashboard (`view-dashboard`), User (`view/create/edit/delete-user`, `assign-user-role`), Sensor (`view/create/update/delete-sensor`), Sale (parcial: `create-sale`/`view-sale`/`delete-sale` batem; `update-sale`/`cancel-sale` **não** — o enum define `edit-sale`, a rota exige `update-sale`, nomes diferentes) | Funciona conforme esperado (com a ressalva do Sale) |

**Não há como contornar via API hoje.** Existe a tabela `user_company_permissions` (grant/deny por usuário+empresa+permissão) e `RevokeUserCompanyPermissionUseCase`, mas **nenhum endpoint/UseCase de "grant"** foi implementado — a única forma de destravar um módulo do Bucket B para um usuário específico é inserir a linha manualmente (tinker/SQL), não pela API.

**Company também não é auto-atendível pelo `company_admin`:** não existe `routes/app/company/company.php`. As únicas rotas de CRUD de `Company` estão em `routes/app/admin/company.php`, dentro do grupo `role:master_admin` (`routes/api.php:110-116`). Ou seja, mesmo `view-company`/`edit-company` estando nos defaults do `company_admin` (`PermissionsEnum.php:175-176`), **não existe rota exposta para ele usar essas permissões** — só `master_admin` consegue ver/editar dados da empresa hoje.

**Rotas mortas encontradas de passagem:** `routes/api.php:38` e `:41` (`companies/{companyId}/members`, `addMember`) chamam `CompanyController::members`/`addMember`, métodos que **não existem** em `app/Presentation/Controllers/CompanyController.php` — qualquer chamada quebra em runtime.

**Docs antigos do repo descrevem um sistema morto:** `GUIA_ADICIONAR_PERMISSOES_COMPANY_ADMIN.md` e `AUDITORIA_PERMISSOES.md` tratam do sistema legado Eloquent `Role`/`Permission` (tabelas `permission_role`, `role_user`) — esse código **não é consultado** pelo `PermissionResolver` em runtime (só sobra em `User::isMasterAdmin()`). Além disso, os seeders desse sistema legado têm um bug de nomenclatura: `CompanyRolesPermissionsSeeder`/`CompanyAdminPermissionsSeeder` usam o role `'company-admin'` (hífen), enquanto `RolesEnum`/`company_user.role` usam `'company_admin'` (underscore) — são registros diferentes no banco. Não siga aquele guia para o sistema atual.

**O que precisa ser feito antes deste guia valer 100% na prática** (fora do escopo deste documento — é só o registro do bloqueio):
1. Estender `PermissionsEnum::forRole(RolesEnum::COMPANY_ADMIN)` para cobrir as entidades do Bucket B, com os nomes de permissão que as rotas realmente exigem (ex.: `update-sale`, não `edit-sale`).
2. Criar uma rota de company para `company_admin` visualizar/editar a própria empresa (ou reaproveitar `CompanyController::show/update` sob `routes/app/company/company.php`, restrito por `assertOwns()`/`TenantContext`).
3. Se for necessário conceder exceções pontuais por usuário, implementar o endpoint de grant para `user_company_permissions` (hoje só existe o de revoke).

---

## 3. Como o `company_admin` é criado hoje

Não existe cadastro self-service. Fluxo real (dois passos, dois atores):

1. **`master_admin`** cria a empresa: `POST /admin/company` (`routes/app/admin/company.php:9`, guardado por `role:master_admin`). Campos obrigatórios (`CompanyStoreRequest`): `name`, `cnpj` (único), `phone`, `addressStreet`, `addressNumber`, `addressNeighborhood`, `addressCity`, `addressState` (2 letras), `addressZipCode`. Opcionais: `email`, `addressComplement`, `active`, `status`.
2. **Alguém com `create-user`** (hoje, na prática, `master_admin` ou outro `company_admin` já existente na empresa) cria o usuário: `POST /company/user` (`routes/app/company/user.php:8`), informando `role: "company_admin"` e `companyId`. Isso roda `CreateUserUseCase` → `AssignUserToCompanyUseCase`, que grava `company_user.role = 'company_admin'`.

**Gap de entrega de credencial:** `CreateUserUseCase.php:36` gera uma **senha aleatória** (`Str::password(20)`) — não existe `app/Mail` nem `app/Notifications` no projeto, ou seja, **não há e-mail de convite nem fluxo de "esqueci minha senha" automatizado**. Hoje, a senha do novo `company_admin` só chega a ele se alguém copiar/repassar manualmente (ou resetar via tinker). Vale registrar como limitação conhecida ao planejar o onboarding real de um cliente.

---

## 4. Primeiro acesso

1. Login: `POST /auth/login` com o e-mail cadastrado e a senha recebida por fora do sistema (ver gap acima).
2. `GET /auth/me` para conferir a empresa ativa e o role retornado.
3. Se o usuário pertencer a mais de uma empresa, `POST /auth/switch-company` para trocar o contexto ativo.
4. **Editar dados da empresa (endereço completo, CNPJ etc.) não é possível pelo `company_admin` hoje** — ver gap na seção 2. Encaminhar para o `master_admin` via `PUT /admin/company/{id}`.

---

## 5. Passo a passo operacional (ordem recomendada)

Ordem definida pelas dependências reais de FK/validação (`DOCUMENTACAO_REGRAS_NEGOCIO.md`), assumindo que o gap da seção 2 tenha sido corrigido (ou que as permissões tenham sido concedidas manualmente para os módulos do Bucket B):

| Ordem | Módulo | Depende de | Regra-chave (ver doc de regras) |
|---|---|---|---|
| 1 | **Tanque** | Empresa + tipo de tanque (catálogo fixo, `TankTypeSeeder`) | Nasce sempre "Ativo" (T1) |
| 2 | **Lote (Batch)** | Tanque | Só 1 lote ativo por tanque (L1) |
| 3 | **Povoamento (Stocking)** | Lote | Calcula biomassa/quantidade automaticamente (P7) |
| 4 | **Fornecedor → Insumo (Supply)** | Empresa | Independente, pode ser feito em paralelo |
| 5 | **Categoria financeira** | Empresa | Deve existir antes de transações/recebíveis (FN1) |
| 6 | **Operação diária**: Arraçoamento, Biometria, Mortalidade, Sensores/Qualidade da água, Transferência | Lote/tanque ativo | Arraçoamento e biometria exigem lote ativo (A1, B1, B3) |
| 7 | **Compras** | Fornecedor + Insumo | Só afeta estoque quando "Recebida" (C5) |
| 8 | **Clientes** | Empresa | Documento único por empresa (CL2) |
| 9 | **Vendas / Despesca** | Lote + Povoamento + Cliente | Controle de biomassa e crédito (V4-V6, CL4) |

Cada linha corresponde a um módulo já documentado inteiramente em `DOCUMENTACAO_REGRAS_NEGOCIO.md` (seções 1 a 19) — não duplicado aqui.

---

## 6. Onde consultar mais

- **Regras de negócio completas, por módulo**: [`DOCUMENTACAO_REGRAS_NEGOCIO.md`](DOCUMENTACAO_REGRAS_NEGOCIO.md)
- **Arquitetura (camadas, controllers, use cases, por módulo)**: [`MAPA_ARQUITETURA_PROJETO.md`](MAPA_ARQUITETURA_PROJETO.md)
- **Sistema de permissões real (código-fonte de referência)**: `app/Domain/Enums/PermissionsEnum.php`, `app/Infrastructure/Security/PermissionResolver.php`, `app/Domain/ValueObjects/TenantContext.php`
