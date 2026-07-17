<?php

declare(strict_types=1);

use App\Domain\Enums\RolesEnum;
use App\Domain\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('ignora company_id e role enviados no payload de PUT /company/user/{id}', function (): void {
    $company      = makeCompany();
    $otherCompany = makeCompany('Outra Empresa');

    /** @var User $actingUser */
    $actingUser = User::factory()->create(['id' => (string) Str::uuid()]);
    attachUserToCompany($actingUser, $company, RolesEnum::ADMIN);

    /** @var User $targetUser */
    $targetUser = User::factory()->create(['id' => (string) Str::uuid()]);
    attachUserToCompany($targetUser, $company, RolesEnum::OPERATOR);

    $headers = bearerHeaderFor($actingUser, $company);

    $response = $this
        ->withHeaders($headers)
        ->putJson("/api/company/user/{$targetUser->id}", [
            'name'      => 'Nome Atualizado',
            'company_id' => $otherCompany->id,
            'companyId'  => $otherCompany->id,
            'role'       => RolesEnum::MASTER_ADMIN->value,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('response.name', 'Nome Atualizado');

    $pivot = DB::table('company_user')
        ->where('user_id', $targetUser->id)
        ->where('company_id', $company->id)
        ->first();

    expect($pivot)->not->toBeNull();
    expect($pivot->role)->toBe(RolesEnum::OPERATOR->value);

    $hasOtherCompanyMembership = DB::table('company_user')
        ->where('user_id', $targetUser->id)
        ->where('company_id', $otherCompany->id)
        ->exists();

    expect($hasOtherCompanyMembership)->toBeFalse();
});
