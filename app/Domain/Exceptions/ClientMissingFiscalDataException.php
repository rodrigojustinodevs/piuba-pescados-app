<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class ClientMissingFiscalDataException extends RuntimeException
{
    public function __construct(string $clientId)
    {
        parent::__construct(
            "The client (id: {$clientId}) does not have CPF/CNPJ and/or address registered. "
            . 'For invoice issuance, the document and address are required.'
        );
    }
}
