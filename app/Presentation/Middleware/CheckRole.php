<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Domain\Enums\RolesEnum;
use App\Infrastructure\Security\CompanyContext;
use App\Infrastructure\Security\PermissionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class CheckRole
{
    public function __construct(
        private PermissionResolver $resolver,
    ) {
    }

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user      = $request->user();
        $companyId = CompanyContext::getCompanyId();

        try {
            $context = $this->resolver->resolve($user, $companyId);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        // master_admin passa sempre
        if ($context->role->value() === RolesEnum::MASTER_ADMIN->value) {
            return $next($request);
        }

        foreach ($roles as $required) {
            if ($context->role->isAtLeast(RolesEnum::from($required))) {
                return $next($request);
            }
        }

        return response()->json([
            'message'        => 'Forbidden: insufficient role.',
            'required_roles' => $roles,
            'current_role'   => $context->role->value(),
        ], 403);
    }
}
