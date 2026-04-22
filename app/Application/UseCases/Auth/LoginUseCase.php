<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Application\Contracts\Auth\PasswordHasherInterface;
use App\Application\Contracts\Auth\TokenServiceInterface;
use App\Application\DTOs\LoginInputDTO;
use App\Application\DTOs\LoginOutputDTO;
use App\Application\DTOs\UserContextDTO;
use App\Domain\Enums\RolesEnum;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Models\CompanyUserPivot;
use App\Domain\Repositories\AuthRepositoryInterface;
use App\Domain\ValueObjects\Role;
use App\Domain\ValueObjects\TenantContext;
use App\Infrastructure\Security\CompanyJwtService;

final readonly class LoginUseCase
{
    public function __construct(
        private AuthRepositoryInterface $authRepository,
        private PasswordHasherInterface $passwordHasher,
        private TokenServiceInterface $tokenService,
        private CompanyJwtService $companyJwtService,
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

        $company    = $user->companies->first();
        $pivotValue = $company?->getRelationValue('pivot');
        $pivot      = $pivotValue instanceof CompanyUserPivot ? $pivotValue : null;

        if (! $company || ! $pivot) {
            throw new InvalidCredentialsException();
        }

        $token = $this->companyJwtService->generateToken($user, new TenantContext(
            userId: (string) $user->id,
            companyId: (string) $company->id,
            role: new Role(RolesEnum::from($pivot->role)),
            permissions: $user->permissions->toArray(),
        ));

        return new LoginOutputDTO(
            token:     $token,
            tokenType: 'Bearer',
            expiresIn: $this->tokenService->ttlInSeconds(),
            user:      UserContextDTO::fromModel($user),
        );
    }
}
