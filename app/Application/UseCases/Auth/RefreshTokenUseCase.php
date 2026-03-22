<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Application\Contracts\Auth\TokenServiceInterface;
use App\Application\DTOs\LoginOutputDTO;
use App\Application\DTOs\UserContextDTO;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Throwable;

final class RefreshTokenUseCase
{
    public function __construct(
        private readonly TokenServiceInterface $tokenService,
        private readonly Guard                 $auth,
    ) {}

    public function execute(): LoginOutputDTO
    {
        try {
            $token = $this->tokenService->refresh();
        } catch (Throwable) {
            throw UnauthorizedException::tokenExpired();
        }

        /** @var User $user */
        $user = $this->auth->user();

        return new LoginOutputDTO(
            token:     $token,
            tokenType: 'Bearer',
            expiresIn: $this->tokenService->ttlInSeconds(),
            user:      UserContextDTO::fromModel($user),
        );
    }
}