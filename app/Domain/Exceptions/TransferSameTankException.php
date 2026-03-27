<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class TransferSameTankException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The origin tank cannot be the same as the destination tank.');
    }
}
