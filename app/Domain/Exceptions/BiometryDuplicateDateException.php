<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class BiometryDuplicateDateException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('A biometry record already exists for this batch on the selected date'));
    }
}
