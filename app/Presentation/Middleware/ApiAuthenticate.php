<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ApiAuthenticate extends Middleware
{
    public function __construct(
        AuthFactory $auth,
        private readonly JWTAuth $jwt
    ) {
        parent::__construct($auth);
    }

    #[\Override]
    protected function redirectTo($request): ?string
    {
        return null;
    }

    #[\Override]
    public function handle($request, Closure $next, ...$guards)
    {
        if ($this->needsAuthentication($request)) {
            $this->jwt->parseToken();

            /** @var JWTSubject|null $user */
            $user = $this->jwt->authenticate();

            if (! $user) {
                throw new AccessDeniedHttpException('Unauthorized');
            }

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
