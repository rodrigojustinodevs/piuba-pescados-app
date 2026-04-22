<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions as ExceptionsConfig;
use Illuminate\Foundation\Configuration\Middleware as MiddlewareConfig;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        api:      __DIR__ . '/../routes/api.php',       // ← adicionar rota API dedicada
        commands: __DIR__ . '/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (MiddlewareConfig $middleware): void {
        // ── Stack global ──────────────────────────────────────────────────────
        $middleware->use([
            Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            Illuminate\Http\Middleware\TrustProxies::class,
            Illuminate\Http\Middleware\HandleCors::class,
            Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            Illuminate\Http\Middleware\ValidatePostSize::class,
            App\Presentation\Middleware\TrimStrings::class,
            Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        // ── Grupo api ─────────────────────────────────────────────────────────
        // ApiAuthenticate foi removido do grupo: cada rota declara jwt.auth
        // explicitamente, evitando que rotas públicas (login) sejam bloqueadas.
        $middleware->group('api', [
            App\Presentation\Middleware\CorsMiddleware::class,
            App\Presentation\Middleware\ForceJsonResponse::class,
            App\Presentation\Middleware\RateLimitMiddleware::class,
            App\Presentation\Middleware\SanitizeInputMiddleware::class,
            App\Presentation\Middleware\SecurityHeadersMiddleware::class,
            'throttle:api',
            Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // ── Aliases ───────────────────────────────────────────────────────────
        $middleware->alias([
            // JWT (existentes)
            'jwt.auth'    => PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.refresh' => PHPOpenSourceSaver\JWTAuth\Http\Middleware\RefreshToken::class,

            // Multi-tenant (new)
            'company.context' => App\Presentation\Middleware\CheckCompanyContext::class,
            // RBAC (existentes)
            'role'       => App\Presentation\Middleware\CheckRole::class,
            'permission' => App\Presentation\Middleware\CheckPermission::class,
        ]);

        // ── Prioridade de execução ────────────────────────────────────────────
        // company.context deve rodar DEPOIS de jwt.auth e ANTES de permission/role
        $middleware->priority([
            Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            Illuminate\Http\Middleware\HandleCors::class,
            PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class, // jwt.auth
            App\Presentation\Middleware\CheckCompanyContext::class,          // company.context
            App\Presentation\Middleware\CheckRole::class,                    // role
            App\Presentation\Middleware\CheckPermission::class,              // permission
            Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (ExceptionsConfig $exceptions): void {
        // Toda a lógica de tratamento de exceções está em:
        // App\Presentation\Exceptions\Handler
    })
    ->create();

// Handler customizado registrado aqui — toda exceção passa por ele
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Presentation\Exceptions\Handler::class,
);

return $app;
