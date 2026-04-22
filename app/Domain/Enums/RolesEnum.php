<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum RolesEnum: string
{
    case GUEST         = 'guest';
    case OPERATOR      = 'operator';
    case MANAGER       = 'manager';
    case ADMIN         = 'admin';
    case COMPANY_ADMIN = 'company_admin';
    case MASTER_ADMIN  = 'master_admin';

    /** Roles com acesso irrestrito de company (bypass de tenant scope) */
    public function isGlobal(): bool
    {
        return $this === self::MASTER_ADMIN;
    }

    /** Retorna label legível */
    public function label(): string
    {
        return match ($this) {
            self::GUEST         => 'Visitante',
            self::OPERATOR      => 'Operador',
            self::MANAGER       => 'Gerente',
            self::ADMIN         => 'Administrador',
            self::COMPANY_ADMIN => 'Admin da Empresa',
            self::MASTER_ADMIN  => 'Master Admin',
        };
    }

    /** Hierarquia numérica (maior = mais poderoso) */
    public function level(): int
    {
        return match ($this) {
            self::GUEST         => 0,
            self::OPERATOR      => 1,
            self::MANAGER       => 2,
            self::ADMIN         => 3,
            self::COMPANY_ADMIN => 4,
            self::MASTER_ADMIN  => 99,
        };
    }

    public function isAtLeast(self $required): bool
    {
        return $this->level() >= $required->level();
    }
}
