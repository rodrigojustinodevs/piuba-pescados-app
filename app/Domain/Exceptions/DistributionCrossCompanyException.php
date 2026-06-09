<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class DistributionCrossCompanyException extends RuntimeException
{
    /** @param string[] $companyIds */
    public function __construct(array $companyIds)
    {
        $list = implode(', ', $companyIds);

        parent::__construct(
            "Todos os tanques da distribuição devem pertencer à mesma empresa. "
            . "Empresas encontradas: [{$list}]."
        );
    }
}