<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Contracts\UserResolverInterface;
use App\Application\Exceptions\UserNotFoundException;
use App\Domain\Models\User;
use Illuminate\Contracts\Auth\Guard;

final readonly class UserResolver implements UserResolverInterface
{
    public function __construct(
        private Guard $auth,
    ) {
    }

    public function resolve(?string $hint = null): string
    {
        $userId = $this->tryResolve($hint);

        if ($userId === null) {
            throw $hint !== null
                ? UserNotFoundException::forHint($hint)
                : new UserNotFoundException();
        }

        return $userId;
    }

    public function tryResolve(?string $hint = null): ?string
    {
        if ($this->isValidId($hint)) {
            return $hint;
        }

        $user = $this->authenticatedUser();

        if (! $user instanceof User) {
            return null;
        }

        $directId = $this->resolveDirectUserId($user);

        if ($this->isValidId($directId)) {
            return $directId;
        }

        return $this->resolveFromRelation($user);
    }

    private function authenticatedUser(): ?User
    {
        $user = $this->auth->user();

        return $user instanceof User ? $user : null;
    }

    private function resolveDirectUserId(User $user): ?string
    {
        $attributes = $user->getAttributes();
        $id         = $attributes['user_id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function resolveFromRelation(User $user): ?string
    {
        if (! method_exists($user, 'users')) {
            return null;
        }

        $id = $user->users()->value('users.id');

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function isValidId(mixed $value): bool
    {
        return is_string($value) && $value !== '';
    }
}
