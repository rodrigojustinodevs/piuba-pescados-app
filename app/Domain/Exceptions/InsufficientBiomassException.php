<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class InsufficientBiomassException extends RuntimeException
{
    public function __construct(
        public readonly float $available,
        public readonly float $requested,
        public readonly string $stockingId,
    ) {
        parent::__construct(
            sprintf(
                'Insufficient biomass in the batch/stocking (id: %s). '
                . 'Available: %.2f kg | Requested: %.2f kg.',
                $stockingId,
                $available,
                $requested,
            )
        );
    }
}
