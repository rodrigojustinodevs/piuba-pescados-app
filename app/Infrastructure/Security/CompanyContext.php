<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Enums\RolesEnum;

/**
 * Holds the current tenant context (company + role) for the request lifecycle.
 * This is the single source of truth for company_id — never trust request input.
 */
final class CompanyContext
{
    private static ?string $companyId = null;

    private static ?string $role = null;

    private static string $userId = '';

    public static function set(string $userId, ?string $companyId, string $role): void
    {
        self::$userId    = $userId;
        self::$companyId = $companyId;
        self::$role      = $role;
    }

    public static function clear(): void
    {
        self::$companyId = null;
        self::$role      = null;
        self::$userId    = '';
    }

    public static function getCompanyId(): ?string
    {
        return self::$companyId;
    }

    public static function getUserId(): string
    {
        return self::$userId;
    }

    public static function getRole(): ?string
    {
        return self::$role;
    }

    public static function isMasterAdmin(): bool
    {
        return self::$role === RolesEnum::MASTER_ADMIN->value;
    }

    public static function isSet(): bool
    {
        return self::$companyId !== null;
    }

    public static function requireCompanyId(): string
    {
        if (self::$companyId === null) {
            throw new \RuntimeException(
                'Company context not initialized. Did you forget the CheckCompanyContext middleware?'
            );
        }

        return self::$companyId;
    }

    /**
     * Resolves which company_id an action targets when the acting user isn't
     * necessarily scoped to a single company (master_admin).
     *
     * Non-master users are always forced to their own active company, ignoring
     * whatever was requested — never trust company_id coming from the request.
     * master_admin may pass an explicit companyId, or fall back to an active
     * one if they've switched into a company; otherwise it's an error.
     *
     * @throws \DomainException if master_admin has no company to target
     */
    public static function resolveTargetCompanyId(?string $requested): string
    {
        if (! self::isMasterAdmin()) {
            return self::requireCompanyId();
        }

        $companyId = $requested ?? self::$companyId;

        if ($companyId === null || $companyId === '') {
            throw new \DomainException(
                'O campo companyId é obrigatório para master_admin sem empresa ativa selecionada.'
            );
        }

        return $companyId;
    }
}
