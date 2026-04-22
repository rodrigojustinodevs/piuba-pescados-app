<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Domain\ValueObjects\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class EnsureMasterAdmin
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        /** @var TenantContext|null $context */
        $context = app()->bound(TenantContext::class) ? app(TenantContext::class) : null;

        if (! $context?->isGlobal()) {
            return response()->json([
                'error' => 'Access exclusive for Master Admin.',
                'code'  => 'MASTER_ADMIN_REQUIRED',
            ], SymfonyResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
