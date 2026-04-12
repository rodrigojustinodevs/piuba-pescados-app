<?php

declare(strict_types=1);

namespace App\Application\Actions\Batch;

final readonly class GenerateBatchNameAction
{
    public function execute(string $species, int $quantity): string
    {
        return sprintf('Lot - %s (%d un)', $species, $quantity);
    }
}
