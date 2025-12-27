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
     * @OA\Post(
     *     path="/login",
     *     summary="Authenticate user and get JWT token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentication successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Authenticated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
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
