<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Models\User;
use App\Domain\ValueObjects\TenantContext;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Responsável por gerar tokens JWT com claims de company e role.
 *
 * O token contém:
 *   - sub      → user_id  (default JWT)
 *   - cid      → active company_id
 *   - role     → role in the active company
 *   - perms    → list of permissions (optional, increases the token size)
 */
final class CompanyJwtService
{
    /**
     * @param bool $includePermissions Include permissions in the payload (increases the token size)
     */
    public function generateToken(
        User $user,
        TenantContext $context,
        bool $includePermissions = false,
    ): string {
        $customClaims = [
            'cid'  => $context->companyId,
            'role' => $context->role->value(),
        ];

        if ($includePermissions) {
            $customClaims['perms'] = $context->permissions;
        }

        return JWTAuth::claims($customClaims)->fromUser($user);
    }

    /**
     * Extracts the company_id from the current JWT token (without re-authenticating).
     */
    public function extractCompanyId(): ?string
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
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
            $payload = JWTAuth::parseToken()->getPayload();

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
            JWTAuth::parseToken()->invalidate();
        } catch (\Throwable) {
            // Token already invalid or expired — no action required
        }
    }

    /**
     * Refreshes an existing token keeping the company claims.
     */
    public function refresh(): string
    {
        return JWTAuth::parseToken()->refresh();
    }
}
