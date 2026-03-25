<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class ClientHasPendingObligationsException extends RuntimeException
{
    public function __construct(string $clientId)
    {
        parent::__construct(
            "The client (id: {$clientId}) has sales or receivables pending/overdue. "
            . 'It is not possible to delete a client with outstanding financial obligations.'
        );
    }
}
