<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\LoginCredentialsDTO;
use App\Application\Services\AuthService;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthController
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    /**
     * Authenticate user and return JWT token.
     */
    public function authenticateUser(Request $request): JsonResponse
    {
        try {
            $credentials = new LoginCredentialsDTO(
                email: $request->get('email'),
                password: $request->get('password')
            );

            $result = $this->authService->authenticate($credentials);

            if ($result === null || $result === '' || $result === '0') {
                return ApiResponse::error(null, 'Invalid credentials', Response::HTTP_UNAUTHORIZED);
            }

            return ApiResponse::success(
                [
                    'token' => $result,
                ],
                Response::HTTP_OK,
                'Authenticated successfully'
            );
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Authentication failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
