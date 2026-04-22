<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Infrastructure\Security\CompanyContext;
use App\Infrastructure\Security\PermissionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class CheckPermission
{
    public function __construct(
        private PermissionResolver $resolver,
    ) {
    }

    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user      = $request->user();
        $companyId = CompanyContext::requireCompanyId();

        foreach ($permissions as $permission) {
            if (! $this->resolver->hasPermission($user, $companyId, $permission)) {
                return response()->json([
                    'message'    => 'Acesso negado.',
                    'permission' => $permission,
                ], 403);
            }
        }

        return $next($request);
    }
}
