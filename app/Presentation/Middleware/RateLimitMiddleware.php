<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    public function __construct(
        protected RateLimiter $limiter
    ) {
    }

    /**
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $key          = sha1((string) $request->ip());
        $maxAttempts  = 60;
        $decaySeconds = 60;

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit($key, $decaySeconds);

        return $next($request);
    }
}
