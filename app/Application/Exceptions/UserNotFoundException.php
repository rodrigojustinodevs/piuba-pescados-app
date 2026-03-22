<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

use RuntimeException;

final class UserNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Could not resolve a user for the current context. '
            . 'Provide a valid user_id or ensure the authenticated user belongs to a user.'
        );
    }

    public static function forHint(string $hint): self
    {
        $instance          = new self();
        $instance->message = "User [{$hint}] not found or not accessible by the current user.";

        return $instance;
    }
}
