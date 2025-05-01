<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Domain\Repositories\AuthRetositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckRole
{
    public function __construct(
        protected AuthRetositoryInterface $authRepository
    ) {
    }

    public function handle(Request $request, Closure $next, string $role): mixed
    {
        if (! $this->authRepository->userHasRole($role)) {
            throw new AccessDeniedHttpException('Forbidden: missing role ' . $role);
        }

        return $next($request);
    }
}
