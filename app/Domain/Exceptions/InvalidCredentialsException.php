<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class InvalidCredentialsException extends RuntimeException
{
    public function __construct()
    {
        // Mensagem genérica intencional — não revela se o email existe ou não
        parent::__construct('Invalid credentials.');
    }
}