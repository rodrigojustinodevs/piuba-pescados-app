<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Auth\AssignUserToCompanyUseCase;
use App\Application\UseCases\User\CreateUserUseCase;
use App\Application\UseCases\User\DeleteUserUseCase;
use App\Application\UseCases\User\ShowAllUsersUseCase;
use App\Application\UseCases\User\ShowUserUseCase;
use App\Application\UseCases\User\UpdateUserStatusUseCase;
use App\Application\UseCases\User\UpdateUserUseCase;
use App\Domain\Enums\RolesEnum;
use App\Domain\Models\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;
use App\Presentation\Requests\User\UserAssignRoleRequest;
use App\Presentation\Requests\User\UserStoreRequest;
use App\Presentation\Requests\User\UserUpdateRequest;
use App\Presentation\Requests\User\UserUpdateStatusRequest;
use App\Presentation\Resources\User\UserResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(name="Users", description="Usuários")
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string", example="João Silva"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="(85) 99999-9999"),
 *     @OA\Property(property="emailVerifiedAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="role", type="string", nullable=true, example="operator"),
 *     @OA\Property(property="status", type="string", enum={"active","inactive"}, nullable=true),
 *     @OA\Property(property="isActive", type="boolean", nullable=true),
 *     @OA\Property(property="joinedAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
final class UserController
{
    /**
     * Display a listing of users, scoped by company for non master_admin users.
     *
     * Query params: page, perPage, search, role, isActive, sortBy, sortDir,
     * companyId (master_admin only).
     *
     * @OA\Get(
     *     path="/company/users",
     *     summary="List users",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="perPage", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sortBy", in="query", @OA\Schema(type="string", enum={"name","email","created_at"})),
     *     @OA\Parameter(name="sortDir", in="query", @OA\Schema(type="string", enum={"asc","desc"})),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of users",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request, ShowAllUsersUseCase $useCase): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $result     = $useCase->execute($request->all());
        $collection = UserResource::collection($result->items());

        return ApiResponse::success($collection, Response::HTTP_OK, 'Success', [
            'total'        => $result->total(),
            'current_page' => $result->currentPage(),
            'last_page'    => $result->lastPage(),
            'first_page'   => $result->firstPage(),
            'per_page'     => $result->perPage(),
        ]);
    }

    /**
     * Display the specified user.
     *
     * @OA\Get(
     *     path="/company/user/{id}",
     *     summary="Get user by ID",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="User found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(string $id, ShowUserUseCase $useCase): JsonResponse
    {
        $user = $useCase->execute($id);

        Gate::authorize('view', $user);

        $resource                   = new UserResource($user);
        $resource->includeCompanies = true;

        return ApiResponse::success($resource, Response::HTTP_OK, 'Success');
    }

    /**
     * Store a newly created user and attach it to a company with a role.
     *
     * @OA\Post(
     *     path="/company/user",
     *     summary="Create user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","role"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password", minLength=6),
     *             @OA\Property(property="phone", type="string", nullable=true, maxLength=20),
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="companyId", type="string", format="uuid", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="response", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(UserStoreRequest $request, CreateUserUseCase $useCase): JsonResponse
    {
        Gate::authorize('create', User::class);

        $user = $useCase->execute($request->validated(), $request->user());

        return ApiResponse::created(new UserResource($user));
    }

    /**
     * Update the specified user's own data (name/email/password).
     *
     * @OA\Put(
     *     path="/company/user/{id}",
     *     summary="Update user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password", minLength=6),
     *             @OA\Property(property="phone", type="string", nullable=true, maxLength=20)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(
        UserUpdateRequest $request,
        string $id,
        UpdateUserUseCase $useCase,
        UserRepositoryInterface $userRepository,
    ): JsonResponse {
        Gate::authorize('update', $userRepository->findOrFail($id));

        $user = $useCase->execute($id, $request->validated());

        return ApiResponse::success(new UserResource($user), Response::HTTP_OK, 'Success');
    }

    /**
     * Remove the user's membership from the target company (does not delete the global account).
     *
     * @OA\Delete(
     *     path="/company/user/{id}",
     *     summary="Remove user from company",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(
     *         name="companyId",
     *         in="query",
     *         description="Required for master_admin without an active company context",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User removed from company",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User successfully removed from company"),
     *             @OA\Property(property="response", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=422, description="companyId required or user not in company"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(
        Request $request,
        string $id,
        DeleteUserUseCase $useCase,
        UserRepositoryInterface $userRepository,
    ): JsonResponse {
        Gate::authorize('delete', $userRepository->findOrFail($id));

        $useCase->execute($id, $request->query('companyId'));

        return ApiResponse::success(null, Response::HTTP_OK, 'User successfully removed from company');
    }

    /**
     * Change the user's role within a company.
     *
     * @OA\Patch(
     *     path="/company/user/{id}/role",
     *     summary="Assign role to user in company",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="companyId", type="string", format="uuid", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error or role higher than actor's own"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function updateRole(
        UserAssignRoleRequest $request,
        string $id,
        AssignUserToCompanyUseCase $useCase,
        UserRepositoryInterface $userRepository,
    ): JsonResponse {
        Gate::authorize('assignRole', User::class);

        $target    = $userRepository->findOrFail($id);
        $companyId = CompanyContext::resolveTargetCompanyId($request->validated('companyId'));

        $useCase->execute(
            $target,
            $companyId,
            RolesEnum::from((string) $request->validated('role')),
            $request->user(),
        );

        $target->refresh()->load(['companyMemberships' => fn ($q) => $q->where('company_id', $companyId)->with('company')]);

        return ApiResponse::success(new UserResource($target), Response::HTTP_OK, 'Success');
    }

    /**
     * Toggle the user's active status within a company.
     *
     * @OA\Patch(
     *     path="/company/user/{id}/status",
     *     summary="Update user status in company",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"isActive"},
     *             @OA\Property(property="isActive", type="boolean"),
     *             @OA\Property(property="companyId", type="string", format="uuid", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function updateStatus(
        UserUpdateStatusRequest $request,
        string $id,
        UpdateUserStatusUseCase $useCase,
        UserRepositoryInterface $userRepository,
    ): JsonResponse {
        $target = $userRepository->findOrFail($id);

        Gate::authorize('update', $target);

        $companyId = CompanyContext::resolveTargetCompanyId($request->validated('companyId'));

        $useCase->execute($id, (bool) $request->validated('isActive'), $companyId);

        $target->refresh()->load(['companyMemberships' => fn ($q) => $q->where('company_id', $companyId)->with('company')]);

        return ApiResponse::success(new UserResource($target), Response::HTTP_OK, 'Success');
    }
}
