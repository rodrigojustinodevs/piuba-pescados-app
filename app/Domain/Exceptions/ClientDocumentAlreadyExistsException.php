<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class ClientDocumentAlreadyExistsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'This CPF/CNPJ is already registered for this company.'
        );
    }
}
