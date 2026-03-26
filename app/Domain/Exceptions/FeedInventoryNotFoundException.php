<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class FeedInventoryNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("FeedInventory [{$id}] not found.");
    }
}
