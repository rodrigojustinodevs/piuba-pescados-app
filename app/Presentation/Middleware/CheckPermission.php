<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Domain\Repositories\AuthRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckPermission
{
    public function __construct(
        protected AuthRepositoryInterface $authRepository
    ) {
    }

    public function handle(Request $request, Closure $next, string $permissions): mixed
    {
        $permissionsArray = explode(',', $permissions);

        foreach ($permissionsArray as $permission) {
            $permission = trim($permission);

            if ($permission && $this->authRepository->userHasPermission($permission)) {
                return $next($request);
            }
        }

        throw new AccessDeniedHttpException('Forbidden: missing required permission. ' . $permissions);
    }
}
