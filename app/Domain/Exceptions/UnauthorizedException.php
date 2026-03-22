<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class UnauthorizedException extends RuntimeException
{
    public function __construct(string $message = 'Unauthorized.')
    {
        parent::__construct($message);
    }

    public static function tokenExpired(): self
    {
        return new self('Token expired.');
    }

    public static function tokenInvalid(): self
    {
        return new self('Token invalid.');
    }

    public static function tokenMissing(): self
    {
        return new self('Token not provided.');
    }
}
