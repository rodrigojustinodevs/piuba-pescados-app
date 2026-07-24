<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\LoginInputDTO;
use App\Application\Services\LoginAttemptLimiter;
use App\Application\UseCases\Auth\LoginUseCase;
use App\Application\UseCases\Auth\LogoutUseCase;
use App\Application\UseCases\Auth\MeUseCase;
use App\Application\UseCases\Auth\RefreshTokenUseCase;
use App\Application\UseCases\Auth\SwitchCompanyUseCase;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Models\User;
use App\Presentation\Requests\Auth\LoginRequest;
use App\Presentation\Requests\Auth\RefreshTokenRequest;
use App\Presentation\Requests\Auth\SwitchCompanyRequest;
use App\Presentation\Resources\Auth\AuthResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Auth", description="Autenticação")
 */
final class AuthController
{
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
        $dto = LoginInputDTO::fromArray($request->validated());

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
            data:    (new AuthResource($result))->toArray(),
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
     * Sem middleware de rota — o token (mesmo expirado, dentro da janela de
     * refresh_ttl) é validado inteiramente pelo RefreshTokenUseCase.
     *
     * Exceções tratadas pelo Handler:
     *  - UnauthorizedException → 401 (token expirado/inválido, usuário ou company inativos)
     *
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Refresh the access token",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully."),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="tokenType", type="string", example="Bearer"),
     *                 @OA\Property(property="expiresIn", type="integer", example=3600),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="email", type="string", format="email"),
     *                     @OA\Property(property="companyId", type="string", format="uuid", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token expired, invalid, or user/company inactive",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token expired.")
     *         )
     *     )
     * )
     */
    public function refresh(
        RefreshTokenRequest $request,
        RefreshTokenUseCase $useCase,
    ): JsonResponse {
        $result = $useCase->execute();

        return ApiResponse::success(
            data:    (new AuthResource($result))->toArray(),
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

    /**
     * POST /auth/switch-company
     * Middleware: auth:api
     *
     * Exceções tratadas pelo Handler:
     *  - \DomainException → 422 (usuário não pertence à empresa / vínculo inativo)
     *
     * @OA\Post(
     *     path="/auth/switch-company",
     *     summary="Switch the active company and receive a new token",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"companyId"},
     *             @OA\Property(property="companyId", type="string", format="uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Company switched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Company switched successfully."),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="company_id", type="string", format="uuid"),
     *                 @OA\Property(property="role", type="string"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="User does not belong to the company or membership is inactive")
     * )
     */
    public function switchCompany(
        SwitchCompanyRequest $request,
        SwitchCompanyUseCase $useCase,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $result = $useCase->execute($user, (string) $request->validated('companyId'));

        return ApiResponse::success(
            data:    $result,
            message: 'Company switched successfully.',
        );
    }
}
