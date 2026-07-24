<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Application\Contracts\Auth\TokenServiceInterface;
use App\Application\DTOs\LoginOutputDTO;
use App\Application\DTOs\UserContextDTO;
use App\Domain\Enums\RolesEnum;
use App\Domain\Enums\UserStatusEnum;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Models\Company;
use App\Domain\Models\User;
use App\Domain\Repositories\CompanyRepositoryInterface;
use Throwable;

final readonly class RefreshTokenUseCase
{
    public function __construct(
        private TokenServiceInterface $tokenService,
        private CompanyRepositoryInterface $companyRepository,
    ) {
    }

    public function execute(): LoginOutputDTO
    {
        try {
            $newToken = $this->tokenService->refresh();
        } catch (Throwable) {
            throw UnauthorizedException::tokenExpired();
        }

        [
            'user'      => $user,
            'companyId' => $companyId,
            'role'      => $role
        ] = $this->tokenService->resolveFromToken($newToken);

        if (! $user instanceof User) {
            throw UnauthorizedException::tokenInvalid();
        }

        if ($user->status !== UserStatusEnum::ACTIVE) {
            throw UnauthorizedException::userInactive();
        }

        if ($companyId !== null && $role !== RolesEnum::MASTER_ADMIN->value) {
            $company = $this->companyRepository->showCompany('id', $companyId);

            if (! $company instanceof Company || ! $company->is_active) {
                throw UnauthorizedException::companyInactive();
            }
        }

        return new LoginOutputDTO(
            token:     $newToken,
            tokenType: 'Bearer',
            expiresIn: $this->tokenService->ttlInSeconds(),
            user:      UserContextDTO::fromModel($user),
        );
    }
}
