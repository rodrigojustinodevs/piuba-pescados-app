<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class BiometryNoFeedingsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            __('This batch has no feeding records. Register at least one feeding before creating biometry.')
        );
    }
}
