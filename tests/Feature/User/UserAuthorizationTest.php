<?php

declare(strict_types=1);

/*
 * See the note at the top of tests/Feature/User/UserCrudTest.php about the two
 * pre-existing, project-wide environment gaps (missing `role_user` migration and the
 * sqlite/CONCAT incompatibility) that currently block Feature tests hitting
 * company-scoped routes. The master_admin scenario below follows the same Mockery
 * pattern already used in tests/Feature/AdminCompaniesAuthorizationTest.php.
 */

use App\Domain\Enums\RolesEnum;
use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('cross-company isolation', function (): void {
    it('returns 403 when a company_admin tries to view a user of another company', function (): void {
        $companyA = makeCompany('Empresa A');
        $companyB = makeCompany('Empresa B');
        $adminA   = User::factory()->create();
        attachUserToCompany($adminA, $companyA, RolesEnum::COMPANY_ADMIN);

        $userInB = User::factory()->create();
        attachUserToCompany($userInB, $companyB, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($adminA, $companyA))
            ->getJson("/api/company/user/{$userInB->id}");

        $response->assertStatus(403);
    });

    it('returns 403 when a company_admin tries to update a user of another company', function (): void {
        $companyA = makeCompany('Empresa A');
        $companyB = makeCompany('Empresa B');
        $adminA   = User::factory()->create();
        attachUserToCompany($adminA, $companyA, RolesEnum::COMPANY_ADMIN);

        $userInB = User::factory()->create();
        attachUserToCompany($userInB, $companyB, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($adminA, $companyA))
            ->putJson("/api/company/user/{$userInB->id}", ['name' => 'Hackeado']);

        $response->assertStatus(403);
    });

    it('returns 403 when a company_admin tries to delete a user of another company', function (): void {
        $companyA = makeCompany('Empresa A');
        $companyB = makeCompany('Empresa B');
        $adminA   = User::factory()->create();
        attachUserToCompany($adminA, $companyA, RolesEnum::COMPANY_ADMIN);

        $userInB = User::factory()->create();
        attachUserToCompany($userInB, $companyB, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($adminA, $companyA))
            ->deleteJson("/api/company/user/{$userInB->id}");

        $response->assertStatus(403);
    });
});

describe('self-action guard', function (): void {
    it('blocks a user from deleting themselves', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::COMPANY_ADMIN);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->deleteJson("/api/company/user/{$admin->id}");

        $response->assertStatus(403);
    });
});

describe('role hierarchy', function (): void {
    it('blocks an admin from updating a company_admin (equal-or-higher role) in the same company', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::ADMIN);

        $companyAdmin = User::factory()->create();
        attachUserToCompany($companyAdmin, $company, RolesEnum::COMPANY_ADMIN);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->putJson("/api/company/user/{$companyAdmin->id}", ['name' => 'Tentativa']);

        $response->assertStatus(403);
    });

    it('blocks an admin from assigning a role equal to or higher than their own', function (): void {
        $company = makeCompany();
        $admin   = User::factory()->create();
        attachUserToCompany($admin, $company, RolesEnum::ADMIN);

        $target = User::factory()->create();
        attachUserToCompany($target, $company, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($admin, $company))
            ->patchJson("/api/company/user/{$target->id}/role", ['role' => RolesEnum::COMPANY_ADMIN->value]);

        $response->assertStatus(422);
    });
});

describe('insufficient permissions', function (): void {
    it('blocks an operator from creating a user', function (): void {
        $company  = makeCompany();
        $operator = User::factory()->create();
        attachUserToCompany($operator, $company, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($operator, $company))
            ->postJson('/api/company/user', [
                'name'     => 'Novo',
                'email'    => 'novo@empresa.com',
                'password' => 'segredo123',
                'role'     => RolesEnum::OPERATOR->value,
            ]);

        $response->assertStatus(403);
    });

    it('blocks a manager from deleting a user', function (): void {
        $company = makeCompany();
        $manager = User::factory()->create();
        attachUserToCompany($manager, $company, RolesEnum::MANAGER);

        $target = User::factory()->create();
        attachUserToCompany($target, $company, RolesEnum::OPERATOR);

        $response = $this->withHeaders(bearerHeaderFor($manager, $company))
            ->deleteJson("/api/company/user/{$target->id}");

        $response->assertStatus(403);
    });
});

describe('master_admin bypass', function (): void {
    it('lets master_admin view a user regardless of company', function (): void {
        $company = makeCompany();
        $target  = User::factory()->create();
        attachUserToCompany($target, $company, RolesEnum::OPERATOR);

        $baseMaster = User::factory()->make(['id' => (string) Illuminate\Support\Str::uuid()]);
        $master     = Mockery::mock($baseMaster)->makePartial();
        $master->shouldReceive('isMasterAdmin')->andReturnTrue();

        $response = $this->actingAs($master, 'api')->getJson("/api/company/user/{$target->id}");

        $response->assertStatus(200);
    });
});
