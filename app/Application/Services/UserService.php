<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\UserDTO;
use App\Domain\Models\User;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): UserDTO
    {
        return DB::transaction(function () use ($data): UserDTO {
            $user = $this->userRepository->create($data);

            return $this->mapToDTO($user);
        });
    }

    /**
     * Returns the details of a user.
     */
    public function showUser(string $id): ?UserDTO
    {
        $user = $this->userRepository->showUser('id', $id);

        if (! $user instanceof User) {
            return null;
        }

        return $this->mapToDTO($user);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateUser(string $id, array $data): UserDTO
    {
        return DB::transaction(function () use ($id, $data): UserDTO {
            $user = $this->userRepository->update($id, $data);

            if (! $user instanceof User) {
                throw new \Exception('User not found');
            }

            return $this->mapToDTO($user);
        });
    }

    public function deleteUser(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->userRepository->delete($id));
    }

    /**
     * @param array{email: string, password: string} $data
     */
    public function login(array $data): string
    {
        $user = $this->userRepository->showUser('email', $data['email']);

        if (! $user instanceof User || ! Hash::check($data['password'], $user['password'])) {
            throw new \Exception('The provided credentials are incorrect.');
        }

        return $user->createToken('auth_token')->plainTextToken;
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    private function mapToDTO(User $user): UserDTO
    {
        return new UserDTO(
            id: $user->id,
            isAdmin: $user->is_admin,
            name: $user->name,
            email: $user->email,
            emailVerifiedAt: $user->email_verified_at,
            password: $user->password,
            rememberToken: $user->remember_token,
            createdAt: $user->created_at?->toDateTimeString(),
            updatedAt: $user->updated_at?->toDateTimeString()
        );
    }
}
