<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\UserContextDTO;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Models\User;
use Illuminate\Contracts\Auth\Guard;

final readonly class MeUseCase
{
    public function __construct(
        private Guard $auth,
    ) {
    }

    public function execute(): UserContextDTO
    {
        $user = $this->auth->user();

        if (! $user instanceof User) {
            throw UnauthorizedException::tokenMissing();
        }

        return UserContextDTO::fromModel($user);
    }
}
