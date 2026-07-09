<?php

declare(strict_types=1);

use App\Domain\Enums\RolesEnum;
use App\Domain\Models\Company;
use App\Domain\Models\User;
use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Infrastructure\Security\PermissionResolver;
use App\Presentation\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

it('retorna 401 no CheckRole quando request nao tem usuario autenticado', function (): void {
    $resolver   = app(PermissionResolver::class);
    $middleware = new CheckRole($resolver);
    $request    = Request::create('/api/admin/companies', 'GET');

    $response = $middleware->handle(
        $request,
        fn (): SymfonyResponse => response()->json(['ok' => true], 200),
        RolesEnum::MASTER_ADMIN->value
    );

    expect($response->getStatusCode())->toBe(401);
    expect($response->getContent())->toContain('Unauthenticated.');
});

it('permite acessar admin companies para master_admin sem exigir company context', function (): void {
    $this->app->bind(CompanyRepositoryInterface::class, fn (): CompanyRepositoryInterface => new class () implements CompanyRepositoryInterface
    {
        public function create(App\Application\DTOs\CompanyInputDTO $dto): Company
        {
            throw new RuntimeException('Not implemented for this test.');
        }

        public function update(string $id, array $attributes): Company
        {
            throw new RuntimeException('Not implemented for this test.');
        }

        public function delete(string $id): void
        {
            throw new RuntimeException('Not implemented for this test.');
        }

        public function paginate(array $filters = []): PaginationInterface
        {
            return new class () implements PaginationInterface
            {
                public function total(): int
                {
                    return 0;
                }

                public function items(): array
                {
                    return [];
                }

                public function currentPage(): int
                {
                    return 1;
                }

                public function perPage(): int
                {
                    return 25;
                }

                public function firstPage(): int
                {
                    return 1;
                }

                public function lastPage(): int
                {
                    return 1;
                }
            };
        }

        public function findOrFail(string $id): Company
        {
            throw new RuntimeException('Not implemented for this test.');
        }

        public function showCompany(string $field, string | int $value): ?Company
        {
            throw new RuntimeException('Not implemented for this test.');
        }
    });

    $baseUser = User::factory()->make([
        'id' => (string) Str::uuid(),
    ]);

    $user = Mockery::mock($baseUser)->makePartial();
    $user->shouldReceive('isMasterAdmin')->andReturnTrue();

    $response = $this
        ->actingAs($user, 'api')
        ->getJson('/api/admin/companies');

    $response
        ->assertStatus(200)
        ->assertJsonPath('message', 'Success');
});
