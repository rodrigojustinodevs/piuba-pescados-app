<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\LoginAttemptLimiter;
use App\Application\UseCases\Auth\LoginUseCase;
use App\Application\UseCases\Auth\LogoutUseCase;
use App\Application\UseCases\Auth\MeUseCase;
use App\Application\UseCases\Auth\RefreshTokenUseCase;
use App\Infrastructure\Mappers\AuthMapper;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Presentation\Requests\Auth\LoginRequest;
use App\Presentation\Requests\Auth\RefreshTokenRequest;
use App\Presentation\Resources\Auth\AuthResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;

final class AuthController
{
    public function __construct(
        private readonly AuthMapper $mapper,
    ) {}

    /**
     * POST /auth/login
     *
     * Exceções tratadas pelo Handler:
     *  - InvalidCredentialsException   → 401
     *  - UnauthorizedException          → 401 (rate limit)
     *  - ValidationException            → 422
     */
    public function login(
        LoginRequest $request,
        LoginUseCase $useCase,
        LoginAttemptLimiter $limiter,
    ): JsonResponse {
        $dto = $this->mapper->toLoginInput($request->validated());

        // Block after MAX_ATTEMPTS failures — throw UnauthorizedException
        $limiter->ensureNotLocked($dto->email);

        try {
            $result = $useCase->execute($dto);
        } catch (InvalidCredentialsException $e) {
            // Increment the counter only on invalid credentials
            $limiter->increment($dto->email);
            throw $e;
        }

        // Login successful — reset the counter
        $limiter->clear($dto->email);

        return ApiResponse::success(
            data:    (new AuthResource($result))->toArray($request),
            message: 'Login successful.',
        );
    }

    /**
     * POST /auth/logout
     * Middleware: jwt.auth
     *
     * Exceções tratadas pelo Handler:
     *  - UnauthorizedException → 401
     */
    public function logout(
        LogoutUseCase $useCase,
    ): JsonResponse {
        $useCase->execute();

        return ApiResponse::success(message: 'Logged out successfully.');
    }

    /**
     * POST /auth/refresh
     * Middleware: jwt.refresh
     *
     * Exceções tratadas pelo Handler:
     *  - UnauthorizedException → 401
     */
    public function refresh(
        RefreshTokenRequest $request,
        RefreshTokenUseCase $useCase,
    ): JsonResponse {
        $result = $useCase->execute();

        return ApiResponse::success(
            data:    (new AuthResource($result))->toArray($request),
            message: 'Token refreshed successfully.',
        );
    }

    /**
     * GET /auth/me
     * Middleware: jwt.auth
     *
     * Exceções tratadas pelo Handler:
     *  - UnauthorizedException → 401
     */
    public function me(
        MeUseCase $useCase,
    ): JsonResponse {
        $user = $useCase->execute();

        return ApiResponse::success(
            data:    $user->toArray(),
            message: 'User retrieved successfully.',
        );
    }
}