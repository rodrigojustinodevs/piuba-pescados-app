<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Domain\Models\User;
use App\Domain\ValueObjects\TenantContext;
use App\Infrastructure\Security\CompanyContext;
use App\Infrastructure\Security\CompanyJwtService;
use App\Infrastructure\Security\PermissionResolver;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final readonly class CheckCompanyContext
{
    public function __construct(
        private CompanyJwtService $jwtService,
        private PermissionResolver $permissionResolver,
    ) {
    }

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        // 1. Extrai company_id do payload JWT
        //    NUNCA do request body/query (evita injeção de tenant)
        $companyId = $this->jwtService->extractCompanyId();

        if (! $companyId) {
            return response()->json([
                'error'   => 'Company context not found in token.',
                'message' => 'Select an active company via /auth/switch-company.',
                'code'    => 'NO_COMPANY_CONTEXT',
            ], SymfonyResponse::HTTP_FORBIDDEN);
        }

        $user = JWTAuth::parseToken()->authenticate();

        if (! $user instanceof User) {
            return response()->json([
                'error' => 'User not authenticated.',
                'code'  => 'UNAUTHENTICATED',
            ], SymfonyResponse::HTTP_UNAUTHORIZED);
        }

        try {
            // 2. Resolve permissions (com cache)
            $context = $this->permissionResolver->resolve($user, $companyId);
            // 3. Vincula no container DI — disponível em todo o request
            app()->instance(TenantContext::class, $context);

            // 4. Disponível também via request para conveniência
            $request->attributes->set('tenant_context', $context);

            CompanyContext::set(
                (string) $user->id,
                $companyId,
                $context->role->value()
            );
        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code'  => 'COMPANY_ACCESS_DENIED',
            ], SymfonyResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
