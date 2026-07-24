<?php

declare(strict_types=1);

namespace App\Application\Contracts\Auth;

use App\Domain\Models\Company;
use App\Domain\Models\User;

interface TokenServiceInterface
{
    public function issue(User $user): string;

    public function invalidate(): void;

    public function refresh(): string;

    /**
     * Resolves the user and tenant claims from a specific token string,
     * without touching the token currently attached to the request.
     *
     * @return array{user: ?User, companyId: ?string, role: ?string}
     */
    public function resolveFromToken(string $token): array;

    public function ttlInSeconds(): int;

    public function generateForMasterAdmin(User $user): string;

    public function generateForCompanyUser(User $user, Company $company): string;
}
