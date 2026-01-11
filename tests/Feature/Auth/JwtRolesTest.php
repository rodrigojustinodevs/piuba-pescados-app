<?php

declare(strict_types=1);

use App\Domain\Models\Role;
use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

test('JWT token includes user roles in custom claims', function (): void {
    // Arrange: Criar roles
    $adminRole = Role::create(['name' => 'admin']);
    $operatorRole = Role::create(['name' => 'operator']);

    // Arrange: Criar usuário com roles
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $user->roles()->attach([$adminRole->id, $operatorRole->id]);

    // Act: Fazer login
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    // Assert: Verificar resposta de sucesso
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'response' => [
            'token',
        ],
        'message',
    ]);

    // Assert: Decodificar token e verificar claims
    $token = $response->json('response.token');
    expect($token)->not->toBeEmpty();

    $payload = JWTAuth::setToken($token)->getPayload()->toArray();

    // Verificar que as roles estão no token
    expect($payload)->toHaveKey('roles');
    expect($payload['roles'])->toBeArray();
    expect($payload['roles'])->toContain('admin');
    expect($payload['roles'])->toContain('operator');
    expect($payload['roles'])->toHaveCount(2);

    // Verificar que is_master_admin está presente
    expect($payload)->toHaveKey('is_master_admin');
    expect($payload['is_master_admin'])->toBeFalse();
});

test('JWT token includes master_admin flag when user has master_admin role', function (): void {
    // Arrange: Criar role master_admin
    $masterAdminRole = Role::create(['name' => 'master_admin']);

    // Arrange: Criar usuário com role master_admin
    $user = User::factory()->create([
        'email' => 'master@example.com',
        'password' => Hash::make('password123'),
    ]);

    $user->roles()->attach($masterAdminRole->id);

    // Act: Fazer login
    $response = $this->postJson('/api/login', [
        'email' => 'master@example.com',
        'password' => 'password123',
    ]);

    // Assert: Verificar resposta de sucesso
    $response->assertStatus(200);

    // Assert: Decodificar token e verificar claims
    $token = $response->json('response.token');
    $payload = JWTAuth::setToken($token)->getPayload()->toArray();

    // Verificar que master_admin está nas roles
    expect($payload['roles'])->toContain('master_admin');

    // Verificar que is_master_admin é true
    expect($payload['is_master_admin'])->toBeTrue();
});

test('JWT token includes empty roles array when user has no roles', function (): void {
    // Arrange: Criar usuário sem roles
    $user = User::factory()->create([
        'email' => 'noroles@example.com',
        'password' => Hash::make('password123'),
    ]);

    // Act: Fazer login
    $response = $this->postJson('/api/login', [
        'email' => 'noroles@example.com',
        'password' => 'password123',
    ]);

    // Assert: Verificar resposta de sucesso
    $response->assertStatus(200);

    // Assert: Decodificar token e verificar claims
    $token = $response->json('response.token');
    $payload = JWTAuth::setToken($token)->getPayload()->toArray();

    // Verificar que roles está presente mas vazio
    expect($payload)->toHaveKey('roles');
    expect($payload['roles'])->toBeArray();
    expect($payload['roles'])->toBeEmpty();

    // Verificar que is_master_admin é false
    expect($payload['is_master_admin'])->toBeFalse();
});

test('JWT token can be used to authenticate and access protected routes', function (): void {
    // Arrange: Criar role
    $adminRole = Role::create(['name' => 'admin']);

    // Arrange: Criar usuário com role
    $user = User::factory()->create([
        'email' => 'protected@example.com',
        'password' => Hash::make('password123'),
    ]);

    $user->roles()->attach($adminRole->id);

    // Act: Fazer login
    $loginResponse = $this->postJson('/api/login', [
        'email' => 'protected@example.com',
        'password' => 'password123',
    ]);

    $token = $loginResponse->json('response.token');
    expect($token)->not->toBeEmpty();

    // Act: Acessar rota protegida
    $protectedResponse = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/ping');

    // Assert: Verificar que a rota protegida foi acessada com sucesso
    $protectedResponse->assertStatus(200);
    expect($protectedResponse->content())->toBe('pong');
});

