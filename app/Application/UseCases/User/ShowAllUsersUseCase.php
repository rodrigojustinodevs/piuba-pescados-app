<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ShowAllUsersUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        if (! CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        return $this->userRepository->paginate($filters);
    }
}
