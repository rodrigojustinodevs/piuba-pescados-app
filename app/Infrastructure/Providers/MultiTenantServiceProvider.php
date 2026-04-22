<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Infrastructure\Security\CompanyJwtService;
use App\Infrastructure\Security\PermissionResolver;
use App\Presentation\Middleware\CheckCompanyContext;
use App\Presentation\Middleware\CheckPermission;
use App\Presentation\Middleware\EnsureMasterAdmin;
use App\Presentation\Policies\BatchPolicy;
use App\Presentation\Policies\SalePolicy;
use App\Presentation\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class MultiTenantServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        // Singleton do PermissionResolver (mantém cache em memória entre chamadas)
        $this->app->singleton(PermissionResolver::class, fn($app): PermissionResolver => new PermissionResolver(
            cache: $app->make(\Illuminate\Contracts\Cache\Repository::class),
        ));

        $this->app->singleton(CompanyJwtService::class);
    }

    public function boot(): void
    {
        // Registra middlewares nomeados
        $this->app->make(\Illuminate\Routing\Router::class)->aliasMiddleware(
            'company.context',
            CheckCompanyContext::class
        );
        $this->app->make(\Illuminate\Routing\Router::class)->aliasMiddleware(
            'permission',
            CheckPermission::class
        );
        $this->app->make(\Illuminate\Routing\Router::class)->aliasMiddleware(
            'master.admin',
            EnsureMasterAdmin::class
        );

        // Registra Policies
        Gate::policy(\App\Domain\Models\Batch::class, BatchPolicy::class);
        Gate::policy(\App\Domain\Models\Sale::class, SalePolicy::class);
        Gate::policy(\App\Domain\Models\User::class, UserPolicy::class);

        // Gate "before": master_admin bypassa todas as verificações
        Gate::before(function (\App\Domain\Models\User $user, string $ability): ?bool {
            if ($user->isMasterAdmin()) {
                return true; // Curto-circuita todas as policies
            }

            return null; // Continua verificação normal
        });
    }
}
