<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final readonly class ListBiometriesUseCase
{
    public function __construct(
        private BiometryRepositoryInterface $biometryRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->biometryRepository->paginate($filters);
    }
}
