<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something(): void
{
    // ..
}

/**
 * Creates a minimal Company fixture. `Company::factory()` doesn't exist in this
 * codebase (no HasFactory/CompanyFactory), so tests build companies directly.
 */
function makeCompany(string $name = 'Empresa Teste'): App\Domain\Models\Company
{
    /** @var App\Domain\Models\Company $company */
    $company = App\Domain\Models\Company::create([
        'name'  => $name,
        'cnpj'  => (string) random_int(10_000_000_000_000, 99_999_999_999_999),
        'phone' => '11999999999',
    ]);

    return $company;
}

/**
 * Attaches a user to a company via the company_user pivot, bypassing
 * AssignUserToCompanyUseCase's business rules — for fixture setup only.
 */
function attachUserToCompany(
    App\Domain\Models\User $user,
    App\Domain\Models\Company $company,
    App\Domain\Enums\RolesEnum $role,
    bool $isActive = true,
): void {
    Illuminate\Support\Facades\DB::table('company_user')->insert([
        'id'         => (string) Illuminate\Support\Str::uuid(),
        'user_id'    => $user->id,
        'company_id' => $company->id,
        'role'       => $role->value,
        'is_active'  => $isActive,
        'joined_at'  => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/**
 * Builds a real JWT bearer header for a company-scoped user (via CompanyJwtService),
 * matching the claims CheckCompanyContext expects (`cid`/`role`). Master admin
 * scenarios can't be generated this way today — see the note in UserAuthorizationTest.
 *
 * @return array<string, string>
 */
function bearerHeaderFor(App\Domain\Models\User $user, App\Domain\Models\Company $company): array
{
    $companyWithPivot = $user->companies()->where('companies.id', $company->id)->first();

    $token = app(App\Infrastructure\Security\CompanyJwtService::class)
        ->generateForCompanyUser($user, $companyWithPivot);

    return ['Authorization' => 'Bearer ' . $token];
}
