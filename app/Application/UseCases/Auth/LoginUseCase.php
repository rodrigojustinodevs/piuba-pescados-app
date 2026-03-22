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

final class LoginUseCase
{
    public function __construct(
        private readonly AuthRepositoryInterface $authRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly TokenServiceInterface   $tokenService,
    ) {}

    public function execute(LoginInputDTO $input): LoginOutputDTO
    {
        $user = $this->authRepository->findByEmail($input->email);

        if ($user === null || ! $this->passwordHasher->check($input->password, (string) $user->password)) {
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