<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\ValueObjects\WaterQualityScore;

final readonly class WaterQualityListResult
{
    public function __construct(
        public PaginationInterface $paginator,
        public WaterQualityScore $score,
    ) {
    }
}
