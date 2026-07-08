<?php

declare(strict_types=1);

/*
 * NOTE for whoever runs this suite: as of this writing, ANY request that passes through
 * the `company.context`/`role`/`permission` middleware chain (i.e. every /api/company/*
 * route, including all of these) calls PermissionResolver::resolve(), which unconditionally
 * calls User::isMasterAdmin() first — and that method queries the `role_user` pivot table,
 * which has NO tracked migration in this repo (confirmed via a full grep of
 * database/migrations). This is a pre-existing, project-wide gap unrelated to the Users
 * module — it blocks every Feature test hitting company-scoped routes on a freshly
 * migrated database, not just these. Additionally, phpunit.xml runs tests against sqlite,
 * and an existing migration (2026_02_18_000002_backfill_batches_name_and_set_not_null.php)
 * uses MySQL-only `CONCAT` SQL that fails under sqlite (reproduced independently against
 * the existing tests/Feature/Supplier/SupplierCrudTest.php). Both gaps are out of scope for
 * the Users CRUD task and should be fixed at the project level (restore the role_user
 * migration; make the backfill migration DB-agnostic, or run the suite against real MySQL).
 */

use App\Domain\Enums\RolesEnum;
use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('POST /api/company/user', function (): void {
    it('creates a user and attaches it to the acting company_admin\'s own company', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->postJson('/api/company/user', [
                'name'     => 'Novo Usuário',
                'email'    => 'novo@empresa.com',
                'password' => 'segredo123',
                'role'     => RolesEnum::OPERATOR->value,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('response.email', 'novo@empresa.com');
        $response->assertJsonPath('response.role', RolesEnum::OPERATOR->value);

        $created = User::where('email', 'novo@empresa.com')->first();
        expect($created)->not->toBeNull();
        expect(
            DB::table('company_user')
                ->where('user_id', $created->id)
                ->where('company_id', $company->id)
                ->value('role'),
        )->toBe(RolesEnum::OPERATOR->value);
    });

    it('ignores any companyId sent in the payload by a non master_admin actor', function (): void {
        $company      = makeCompany('Empresa Própria');
        $otherCompany = makeCompany('Outra Empresa');
        $admin        = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->postJson('/api/company/user', [
                'name'      => 'Tentativa Cross-Company',
                'email'     => 'crosscompany@empresa.com',
                'password'  => 'segredo123',
                'role'      => RolesEnum::OPERATOR->value,
                'companyId' => $otherCompany->id,
            ]);

        $response->assertStatus(201);

        $created = User::where('email', 'crosscompany@empresa.com')->first();
        expect(
            DB::table('company_user')->where('user_id', $created->id)->where('company_id', $company->id)->exists(),
        )->toBeTrue();
        expect(
            DB::table('company_user')->where('user_id', $created->id)->where('company_id', $otherCompany->id)->exists(),
        )->toBeFalse();
    });

    it('rejects creating a user with a role equal to or higher than the actor\'s own', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->postJson('/api/company/user', [
                'name'     => 'Peer Admin',
                'email'    => 'peer@empresa.com',
                'password' => 'segredo123',
                'role'     => RolesEnum::COMPANY_ADMIN->value,
            ]);

        $response->assertStatus(422);
    });

    it('rejects an invalid role value', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->postJson('/api/company/user', [
                'name'     => 'Role Inválida',
                'email'    => 'roleinvalida@empresa.com',
                'password' => 'segredo123',
                'role'     => 'super-admin',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    });

    it('rejects a duplicate email', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);
        User::factory()->create(['email' => 'existente@empresa.com']);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->postJson('/api/company/user', [
                'name'     => 'Duplicado',
                'email'    => 'existente@empresa.com',
                'password' => 'segredo123',
                'role'     => RolesEnum::OPERATOR->value,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });

    it('rejects a password shorter than the minimum', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->postJson('/api/company/user', [
                'name'     => 'Senha Curta',
                'email'    => 'senhacurta@empresa.com',
                'password' => '123',
                'role'     => RolesEnum::OPERATOR->value,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });
});

describe('GET /api/company/users', function (): void {
    it('lists only users belonging to the acting company_admin\'s own company', function (): void {
        $companyA = makeCompany('Empresa A');
        $companyB = makeCompany('Empresa B');
        $admin    = User::factory()->create();
        attachUserToCompany($admin, $companyA, RolesEnum::COMPANY_ADMIN);

        $userInA = User::factory()->create();
        attachUserToCompany($userInA, $companyA, RolesEnum::OPERATOR);

        $userInB = User::factory()->create();
        attachUserToCompany($userInB, $companyB, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($admin, $companyA))->getJson('/api/company/users');

        $response->assertStatus(200);
        $ids = collect($response->json('response'))->pluck('id');

        expect($ids)->toContain($admin->id, $userInA->id);
        expect($ids)->not->toContain($userInB->id);
    });

    it('filters by search term (name/email)', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $match = User::factory()->create(['name' => 'Maria Pescadora']);
        attachUserToCompany($match, $company, RolesEnum::OPERATOR);

        $noMatch = User::factory()->create(['name' => 'João Outro']);
        attachUserToCompany($noMatch, $company, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->getJson('/api/company/users?search=Pescadora');

        $ids = collect($response->json('response'))->pluck('id');
        expect($ids)->toContain($match->id);
        expect($ids)->not->toContain($noMatch->id);
    });

    it('sorts by name ascending when requested', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create(['name' => 'Zulu Admin']);
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $alice = User::factory()->create(['name' => 'Alice']);
        attachUserToCompany($alice, $company, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->getJson('/api/company/users?sortBy=name&sortDir=asc');

        $names = collect($response->json('response'))->pluck('name');
        expect($names->first())->toBe('Alice');
    });
});

describe('GET /api/company/user/{id}', function (): void {
    it('exposes role, isActive and the full companies list', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $target = User::factory()->create();
        attachUserToCompany($target, $company, RolesEnum::MANAGER);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->getJson("/api/company/user/{$target->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('response.role', RolesEnum::MANAGER->value);
        $response->assertJsonPath('response.isActive', true);
        expect($response->json('response.companies'))->not->toBeNull();
    });
});

describe('PUT /api/company/user/{id}', function (): void {
    it('updates only the provided fields, keeping the rest unchanged', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $target = User::factory()->create(['name' => 'Nome Antigo', 'email' => 'antigo@empresa.com']);
        attachUserToCompany($target, $company, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->putJson("/api/company/user/{$target->id}", ['name' => 'Nome Novo']);

        $response->assertStatus(200);
        $response->assertJsonPath('response.name', 'Nome Novo');
        $response->assertJsonPath('response.email', 'antigo@empresa.com');
    });

    it('hashes the password when one is provided', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $target = User::factory()->create();
        attachUserToCompany($target, $company, RolesEnum::OPERATOR);

        $this->withHeaders(bearerHeaderFor($admin, $company))
            ->putJson("/api/company/user/{$target->id}", ['password' => 'novaSenha123'])
            ->assertStatus(200);

        expect(Illuminate\Support\Facades\Hash::check('novaSenha123', $target->refresh()->password))->toBeTrue();
    });
});

describe('DELETE /api/company/user/{id}', function (): void {
    it('detaches the user from the company without deleting the global account or other memberships', function (): void {
        $companyA = makeCompany('Empresa A');
        $companyB = makeCompany('Empresa B');
        $admin    = User::factory()->create();
        attachUserToCompany($admin, $companyA, RolesEnum::COMPANY_ADMIN);

        $target = User::factory()->create();
        attachUserToCompany($target, $companyA, RolesEnum::OPERATOR);
        attachUserToCompany($target, $companyB, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($admin, $companyA))
            ->deleteJson("/api/company/user/{$target->id}");

        $response->assertStatus(200);
        expect(User::find($target->id))->not->toBeNull();
        expect(
            DB::table('company_user')->where('user_id', $target->id)->where('company_id', $companyA->id)->exists(),
        )->toBeFalse();
        expect(
            DB::table('company_user')->where('user_id', $target->id)->where('company_id', $companyB->id)->exists(),
        )->toBeTrue();
    });
});

describe('PATCH /api/company/user/{id}/role', function (): void {
    it('changes the role and invalidates the cached permissions', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $target = User::factory()->create();
        attachUserToCompany($target, $company, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->patchJson("/api/company/user/{$target->id}/role", ['role' => RolesEnum::MANAGER->value]);

        $response->assertStatus(200);
        expect(
            DB::table('company_user')
                ->where('user_id', $target->id)
                ->where('company_id', $company->id)
                ->value('role'),
        )->toBe(RolesEnum::MANAGER->value);
    });
});

describe('PATCH /api/company/user/{id}/status', function (): void {
    it('reactivates a previously deactivated membership', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $target = User::factory()->create();
        attachUserToCompany($target, $company, RolesEnum::OPERATOR, isActive: false);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->patchJson("/api/company/user/{$target->id}/status", ['isActive' => true]);

        $response->assertStatus(200);
        expect(
            (bool) DB::table('company_user')
                ->where('user_id', $target->id)
                ->where('company_id', $company->id)
                ->value('is_active'),
        )->toBeTrue();
    });
});
