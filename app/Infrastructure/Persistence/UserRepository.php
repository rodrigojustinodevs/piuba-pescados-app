<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\User;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Create a new user.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update an existing user.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?User
    {
        $user = User::find($id);

        if ($user) {
            $user->update($data);

            return $user;
        }

        return null;
    }

    /**
     * Get paginated companies.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<User> $paginator */
        $paginator = User::paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show user by field and value.
     */
    public function showUser(string $field, string | int $value): ?User
    {
        return User::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $user = User::find($id);

        if (! $user) {
            return false;
        }

        return (bool) $user->delete();
    }
}
