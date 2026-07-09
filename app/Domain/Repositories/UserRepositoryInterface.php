<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\UserInputDTO;
use App\Domain\Models\User;

interface UserRepositoryInterface
{
    public function create(UserInputDTO $dto): User;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): User;

    public function findOrFail(string $id): User;

    /**
     * @param array{
     *     companyId?: string|null,
     *     search?: string|null,
     *     role?: string|null,
     *     isActive?: bool|null,
     *     sortBy?: string|null,
     *     sortDir?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    /**
     * Removes the membership of a user in a company (does not delete the global account).
     */
    public function detachFromCompany(string $userId, string $companyId): void;

    public function updateCompanyStatus(string $userId, string $companyId, bool $isActive): void;
}
