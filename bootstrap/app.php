<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions as ExceptionsConfig;
use Illuminate\Foundation\Configuration\Middleware as MiddlewareConfig;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (MiddlewareConfig $middleware): void {
        $middleware->use([
            Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            Illuminate\Http\Middleware\TrustProxies::class,
            Illuminate\Http\Middleware\HandleCors::class,
            Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            Illuminate\Http\Middleware\ValidatePostSize::class,
            App\Presentation\Middleware\TrimStrings::class,
            Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        $middleware->group('api', [
            App\Presentation\Middleware\CorsMiddleware::class,
            App\Presentation\Middleware\ApiAuthenticate::class,
            App\Presentation\Middleware\ForceJsonResponse::class,
            App\Presentation\Middleware\RateLimitMiddleware::class,
            App\Presentation\Middleware\SanitizeInputMiddleware::class,
            App\Presentation\Middleware\SecurityHeadersMiddleware::class,
            'throttle:api',
            Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'jwt.auth'    => Tymon\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.refresh' => Tymon\JWTAuth\Http\Middleware\RefreshToken::class,
            'role'        => App\Presentation\Middleware\CheckRole::class,
            'permission'  => App\Presentation\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (ExceptionsConfig $exceptions): void {
    })
    ->create();

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Presentation\Exceptions\Handler::class
);

return $app;
