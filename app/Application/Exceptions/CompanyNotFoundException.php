<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

use RuntimeException;

final class CompanyNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Could not resolve a company for the current context. '
            . 'Provide a valid company_id or ensure the authenticated user belongs to a company.'
        );
    }

    public static function forHint(string $hint): self
    {
        $instance          = new self();
        $instance->message = "Company [{$hint}] not found or not accessible by the current user.";

        return $instance;
    }
}
