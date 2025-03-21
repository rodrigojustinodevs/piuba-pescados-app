<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\RolePermissionService;
use App\Presentation\Requests\RolePermission\PermissionToUserRequest;
use App\Presentation\Requests\RolePermission\PermisssionToUserInCompanyRequest;
use App\Presentation\Requests\RolePermission\RoleToUserInCompanyRequest;
use App\Presentation\Requests\RolePermission\RoleToUserRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class RolePermissionController
{
    public function __construct(
        protected RolePermissionService $rolePermissionService,
    ) {
    }

    public function assignRoleToUser(RoleToUserRequest $request): JsonResponse
    {
        try {
            $this->rolePermissionService->assignRoleToUser($request->validated());

            return ApiResponse::success(null, Response::HTTP_OK, 'Role assigned successfully');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function assignPermissionToUser(PermissionToUserRequest $request): JsonResponse
    {
        try {
            $this->rolePermissionService->assignPermissionToUser($request->validated());

            return ApiResponse::success(null, Response::HTTP_OK, 'Permission assigned successfully');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function assignRoleToUserInCompany(RoleToUserInCompanyRequest $request): JsonResponse
    {
        try {
            $this->rolePermissionService->assignRoleToUserInCompany($request->validated());

            return ApiResponse::success(null, Response::HTTP_OK, 'Role assigned to user in company successfully');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function assignPermissionToUserInCompany(PermisssionToUserInCompanyRequest $request): JsonResponse
    {
        try {
            $this->rolePermissionService->assignPermissionToUserInCompany($request->validated());

            return ApiResponse::success(null, Response::HTTP_OK, 'Permission assigned to user in company successfully');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
