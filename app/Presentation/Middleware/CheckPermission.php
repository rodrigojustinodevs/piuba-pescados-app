<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Application\UseCases\Auth\CheckUserPermissionUseCase;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckPermission
{
    public function __construct(
        protected CheckUserPermissionUseCase $checkPermissionUseCase
    ) {
    }

    public function handle(Request $request, Closure $next, string $permissions): mixed
    {
        $user = $request->user();

        if (! $user) {
            throw new AccessDeniedHttpException('Unauthorized');
        }

        $permissionList = $this->parsePermissions($permissions);
        $companyId      = $this->resolveCompanyId($request);

        if ($this->checkPermissionUseCase->userHasAnyPermission($user, $permissionList, $companyId)) {
            return $next($request);
        }

        throw new AccessDeniedHttpException('Forbidden: missing required permission. ' . $permissions);
    }

    /**
     * @return array<string>
     */
    private function parsePermissions(string $permissions): array
    {
        return array_map(
            'trim',
            preg_split('/[,|]/', $permissions)
        );
    }

    private function resolveCompanyId(Request $request): ?string
    {
        if ($request->hasHeader('X-Company-Id')) {
            return $request->header('X-Company-Id');
        }

        $route = $request->route();

        if ($route) {
            $companyId = $route->parameter('company') ?? $route->parameter('companyId');

            if ($companyId) {
                return (string) $companyId;
            }
        }

        if ($request->has('company_id')) {
            return $request->query('company_id');
        }

        return null;
    }
}
