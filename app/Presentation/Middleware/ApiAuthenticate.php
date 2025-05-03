<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiAuthenticate extends Middleware
{
    public function __construct(
        private JWTAuth $jwt
    ) {}

    #[\Override]
    protected function redirectTo($request): ?string
    {
        return null;
    }

    #[\Override]
    public function handle($request, Closure $next, ...$guards)
    {
        if ($this->needsAuthentication($request)) {
            $this->jwt->parseToken()->authenticate();

            $this->authenticate($request, $guards);
        }

        return $next($request);
    }

    private function needsAuthentication(Request $request): bool
    {
        $route = $request->route();

        if (! $route) {
            return false;
        }

        $middlewares = $route->gatherMiddleware();

        return in_array('auth:api', $middlewares, true);
    }
}
