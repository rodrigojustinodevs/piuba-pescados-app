<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Application\Contracts\Auth\PasswordHasherInterface;
use App\Application\Contracts\Auth\TokenServiceInterface;
use App\Application\DTOs\LoginInputDTO;
use App\Application\DTOs\LoginOutputDTO;
use App\Application\DTOs\UserContextDTO;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Repositories\AuthRepositoryInterface;

final readonly class LoginUseCase
{
    public function __construct(
        private AuthRepositoryInterface $authRepository,
        private PasswordHasherInterface $passwordHasher,
        private TokenServiceInterface $tokenService,
    ) {
    }

    public function execute(LoginInputDTO $input): LoginOutputDTO
    {
        $user = $this->authRepository->findByEmail($input->email);

        $validUser     = $user instanceof \App\Domain\Models\User;
        $validPassword = $validUser
            && $this->passwordHasher->check($input->password, (string) $user->password);

        if (! $validUser || ! $validPassword) {
            throw new InvalidCredentialsException();
        }

        $token = $this->tokenService->issue($user);

        return new LoginOutputDTO(
            token:     $token,
            tokenType: 'Bearer',
            expiresIn: $this->tokenService->ttlInSeconds(),
            user:      UserContextDTO::fromModel($user),
        );
    }
}
