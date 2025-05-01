<?php

declare(strict_types=1);

namespace App\Presentation\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInputMiddleware
{
    /**
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $sanitized = collect($request->all())->map(function ($value) {
            if (is_string($value)) {
                $value = trim($value);

                return $value === '' ? null : $value;
            }

            return $value;
        })->toArray();

        $request->merge($sanitized);

        return $next($request);
    }
}
