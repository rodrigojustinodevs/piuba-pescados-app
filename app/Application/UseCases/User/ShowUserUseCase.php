<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Domain\Models\User;
use App\Domain\Repositories\UserRepositoryInterface;

final readonly class ShowUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function execute(string $id): User
    {
        $user = $this->userRepository->findOrFail($id);
        $user->loadMissing('activeCompanies');

        return $user;
    }
}
