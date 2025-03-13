<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\UserService;
use App\Presentation\Requests\Auth\AuthRequest;
use App\Presentation\Requests\Auth\RegisterRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class AuthController
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $token = $this->userService->login($request->validated());

            return ApiResponse::success([
                'access_token' => $token,
                'token_type'   => 'bearer',
            ]);
        } catch (Throwable $exception) {
            return ApiResponse::error(
                $exception,
                $exception->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Store a newly created batche.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->create($request->validated());

            return ApiResponse::created($user->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->userService->logout($request->user());

        return ApiResponse::success(null, Response::HTTP_OK, 'Logged out successfully');
    }
}
