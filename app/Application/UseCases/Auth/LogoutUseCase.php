<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Application\Contracts\Auth\TokenServiceInterface;
use App\Domain\Exceptions\UnauthorizedException;
use Throwable;

final class LogoutUseCase
{
    public function __construct(
        private readonly TokenServiceInterface $tokenService,
    ) {}

    public function execute(): void
    {
        try {
            $this->tokenService->invalidate();
        } catch (Throwable) {
            // Token already expired or invalid — logout is idempotent
            throw UnauthorizedException::tokenInvalid();
        }
    }
}