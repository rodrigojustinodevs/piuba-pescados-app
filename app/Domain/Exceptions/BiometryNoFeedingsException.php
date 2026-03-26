<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class BiometryNoFeedingsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('validation.biometry.no_feedings'));
    }
}
