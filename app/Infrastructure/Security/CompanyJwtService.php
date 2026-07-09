<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Contracts\Auth\TokenServiceInterface;
use App\Domain\Enums\RolesEnum;
use App\Domain\Models\Company;
use App\Domain\Models\CompanyUserPivot;
use App\Domain\Models\User;
use App\Domain\ValueObjects\Role;
use App\Domain\ValueObjects\TenantContext;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;

/**
 * Responsável por gerar tokens JWT com claims de company e role.
 *
 * O token contém:
 *   - sub      → user_id  (default JWT)
 *   - cid      → active company_id
 *   - role     → role in the active company
 *   - perms    → list of permissions (optional, increases the token size)
 */
final readonly class CompanyJwtService implements TokenServiceInterface
{
    public function __construct(
        private JWTAuth $jwt,
    ) {
    }

    public function issue(User $user): string
    {
        return $this->generateForCompanyUser($user, $user->companies->first());
    }

    public function invalidate(): void
    {
        $this->jwt->parseToken()->invalidate();
    }

    public function generateForMasterAdmin(User $user): string
    {
        return $this->generateToken($user, new TenantContext(
            userId:      (string) $user->id,
            companyId:   '',
            role:        new Role(RolesEnum::MASTER_ADMIN->value),
            permissions: $user->permissions->toArray(),
        ));
    }

    public function generateForCompanyUser(User $user, Company $company): string
    {
        // Leitura do pivot e do role encapsulada aqui — não no UseCase
        $pivotValue = $company->getRelationValue('pivot');
        $pivot      = $pivotValue instanceof CompanyUserPivot ? $pivotValue : null;

        // Se o pivot estiver ausente, o JWT não pode ser gerado com role correto
        if (! $pivot instanceof CompanyUserPivot) {
            throw new \RuntimeException(
                "CompanyUserPivot not loaded for user [{$user->id}] and company [{$company->id}]."
            );
        }

        return $this->generateToken($user, new TenantContext(
            userId:      (string) $user->id,
            companyId:   (string) $company->id,
            role:        new Role(RolesEnum::from((string) $pivot->role)),
            permissions: $user->permissions->toArray(),
        ));
    }

    /**
     * @param bool $includePermissions Include permissions in the payload (increases the token size)
     */
    public function generateToken(
        User $user,
        TenantContext $context,
        bool $includePermissions = false,
    ): string {
        $customClaims = [
            'unm'  => $user->name,
            'cid'  => $context->companyId,
            'role' => $context->role->value(),
        ];

        if ($includePermissions) {
            $customClaims['perms'] = $context->permissions;
        }

        return $this->jwt->claims($customClaims)->fromUser($user);
    }

    /**
     * Extracts the company_id from the current JWT token (without re-authenticating).
     */
    public function extractCompanyId(): ?string
    {
        try {
            $payload = $this->jwt->parseToken()->getPayload();
            $cid     = $payload->get('cid');

            return $cid ? (string) $cid : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Extracts the role from the current JWT token.
     */
    public function extractRole(): ?string
    {
        try {
            $payload = $this->jwt->parseToken()->getPayload();

            return $payload->get('role');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Invalidates the current token (logout).
     */
    public function invalidateCurrentToken(): void
    {
        try {
            $this->jwt->parseToken()->invalidate();
        } catch (\Throwable) {
            // Token already invalid or expired — no action required
        }
    }

    /**
     * Refreshes an existing token keeping the company claims.
     */
    public function refresh(): string
    {
        return $this->jwt->parseToken()->refresh();
    }

    public function authenticateFromToken(): ?User
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (\Throwable) {
            return null;
        }

        return $user instanceof User ? $user : null;
    }

    public function ttlInSeconds(): int
    {
        return (int) config('jwt.ttl', 60) * 60;
    }
}
