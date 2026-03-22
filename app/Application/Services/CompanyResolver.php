<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\Exceptions\CompanyNotFoundException;
use App\Domain\Models\User;
use Illuminate\Contracts\Auth\Guard;

final class CompanyResolver implements CompanyResolverInterface
{
    public function __construct(
        private readonly Guard $auth,
    ) {}

    public function resolve(?string $hint = null): string
    {
        $companyId = $this->tryResolve($hint);

        if ($companyId === null) {
            throw $hint !== null
                ? CompanyNotFoundException::forHint($hint)
                : new CompanyNotFoundException();
        }

        return $companyId;
    }

    public function tryResolve(?string $hint = null): ?string
    {
        // 1. Hint explícito — admin operando em outra empresa, multi-tenant, etc.
        if ($this->isValidId($hint)) {
            return $hint;
        }

        $user = $this->authenticatedUser();

        if ($user === null) {
            return null;
        }

        // 2. company_id diretamente no usuário (relação simples 1:N)
        $directId = $this->resolveDirectCompanyId($user);
        if ($this->isValidId($directId)) {
            return $directId;
        }

        // 3. Primeira empresa vinculada (relação N:N via pivot)
        return $this->resolveFromRelation($user);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function authenticatedUser(): ?User
    {
        $user = $this->auth->user();

        return $user instanceof User ? $user : null;
    }

    private function resolveDirectCompanyId(User $user): ?string
    {
        $attributes = $user->getAttributes();
        $id = $attributes['company_id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function resolveFromRelation(User $user): ?string
    {
        if (! method_exists($user, 'companies')) {
            return null;
        }

        $id = $user->companies()->value('companies.id');

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function isValidId(mixed $value): bool
    {
        return is_string($value) && $value !== '';
    }
}