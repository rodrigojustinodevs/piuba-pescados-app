<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Services;

use App\Application\Exceptions\CompanyNotFoundException;
use App\Application\Services\CompanyResolver;
use App\Domain\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

final class CompanyResolverTest extends TestCase
{
    private Guard $guard;

    private CompanyResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guard    = Mockery::mock(Guard::class);
        $this->resolver = new CompanyResolver($this->guard);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // resolve() — caminho feliz
    // -------------------------------------------------------------------------

    public function test_resolve_returns_hint_when_provided(): void
    {
        $hint = 'company-uuid-123';

        // Guard nunca deve ser chamado quando hint é informado
        $this->guard->shouldNotReceive('user');

        $result = $this->resolver->resolve($hint);

        self::assertSame($hint, $result);
    }

    public function test_resolve_returns_direct_company_id_from_user(): void
    {
        $user = $this->makeUser(companyId: 'company-from-user');

        $this->guard->shouldReceive('user')->once()->andReturn($user);

        $result = $this->resolver->resolve();

        self::assertSame('company-from-user', $result);
    }

    public function test_resolve_falls_back_to_companies_relation(): void
    {
        $user = $this->makeUserWithRelation(firstCompanyId: 'company-from-relation');

        $this->guard->shouldReceive('user')->once()->andReturn($user);

        $result = $this->resolver->resolve();

        self::assertSame('company-from-relation', $result);
    }

    // -------------------------------------------------------------------------
    // resolve() — erros
    // -------------------------------------------------------------------------

    public function test_resolve_throws_when_no_user_authenticated(): void
    {
        $this->guard->shouldReceive('user')->once()->andReturn(null);

        $this->expectException(CompanyNotFoundException::class);

        $this->resolver->resolve();
    }

    public function test_resolve_throws_when_user_has_no_company(): void
    {
        $user = $this->makeUser(companyId: null);

        $this->guard->shouldReceive('user')->once()->andReturn($user);

        $this->expectException(CompanyNotFoundException::class);

        $this->resolver->resolve();
    }

    public function test_resolve_throws_with_hint_message_when_hint_provided_but_invalid(): void
    {
        // Hint vazio — deve tratar como ausente e tentar fallback
        $user = $this->makeUser(companyId: null);

        $this->guard->shouldReceive('user')->once()->andReturn($user);

        $this->expectException(CompanyNotFoundException::class);

        $this->resolver->resolve(''); // string vazia é inválida
    }

    // -------------------------------------------------------------------------
    // tryResolve() — retorna null em vez de lançar
    // -------------------------------------------------------------------------

    public function test_try_resolve_returns_null_when_no_company_found(): void
    {
        $this->guard->shouldReceive('user')->once()->andReturn(null);

        $result = $this->resolver->tryResolve();

        self::assertNull($result);
    }

    public function test_try_resolve_returns_hint_directly(): void
    {
        $result = $this->resolver->tryResolve('explicit-company');

        self::assertSame('explicit-company', $result);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeUser(?string $companyId): User
    {
        /** @var User&MockInterface $user */
        $user = Mockery::mock(User::class)->makePartial();

        $user->shouldReceive('getAttributes')
            ->andReturn(array_filter(['company_id' => $companyId]));

        return $user;
    }

    private function makeUserWithRelation(string $firstCompanyId): User
    {
        /** @var User&MockInterface $user */
        $user = Mockery::mock(User::class)->makePartial();

        $user->shouldReceive('getAttributes')->andReturn([]);

        $query = Mockery::mock();
        $query->shouldReceive('value')->with('companies.id')->andReturn($firstCompanyId);

        $user->shouldReceive('companies')->andReturn($query);

        return $user;
    }
}
