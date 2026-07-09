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

        if (! $user instanceof \App\Domain\Models\User) {
            throw new InvalidCredentialsException();
        }

        if (! $this->passwordHasher->check($input->password, (string) $user->password)) {
            throw new InvalidCredentialsException();
        }

        $user->update(['last_access_at' => now()]);

        $token = $user->isMasterAdmin()
            ? $this->tokenService->generateForMasterAdmin($user)
            : $this->generateForCompanyUser($user);

        return new LoginOutputDTO(
            token:     $token,
            tokenType: 'Bearer',
            expiresIn: $this->tokenService->ttlInSeconds(),
            user:      UserContextDTO::fromModel($user),
        );
    }

    /**
     * @throws InvalidCredentialsException
     */
    private function generateForCompanyUser(\App\Domain\Models\User $user): string
    {
        $company = $user->companies->first();

        if ($company === null) {
            throw new InvalidCredentialsException();
        }

        return $this->tokenService->generateForCompanyUser($user, $company);
    }
}
