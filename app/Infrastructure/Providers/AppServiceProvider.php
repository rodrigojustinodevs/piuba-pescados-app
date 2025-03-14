<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Enums\Can;
use App\Domain\Models\User;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\BatcheRepository;
use App\Infrastructure\Persistence\CompanyRepository;
use App\Infrastructure\Persistence\TankRepository;
use App\Infrastructure\Persistence\UserRepository;
use App\Presentation\Exceptions\Handler as CustomHandler;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
use Override;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(TankRepositoryInterface::class, TankRepository::class);
        $this->app->bind(BatcheRepositoryInterface::class, BatcheRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ExceptionHandler::class, CustomHandler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->setupLogViewer();
        $this->configModels();
        $this->configCommands();
        $this->configUrls();
        $this->configDate();
        $this->configGates();
    }

    /**
     * Sets up the LogViewer authentication to restrict access
     * based on whether the authenticated user is an admin.
     */
    private function setupLogViewer(): void
    {
        LogViewer::auth(function (Request $request): bool {
            /** @var User|null $user */
            $user = $request->user();

            return $user !== null && $user->is_admin;
        });
    }

    /**
     * Configures Eloquent models by disabling the requirement for defining
     * the fillable property and enforcing strict checking to ensure that
     * all accessed properties exist within the model.
     */
    private function configModels(): void
    {
        // --
        // Remove the need of the property fillable on each model
        Model::unguard();

        // --
        // Make sure that all properties being called exists in the model
        Model::shouldBeStrict();
    }

    /**
     * Configures database commands to prohibit execution of destructive statements
     * when the application is running in a production environment.
     */
    private function configCommands(): void
    {
        DB::prohibitDestructiveCommands(
            app()->isProduction()
        );
    }

    /**
     * Configures the application URLs to enforce HTTPS protocol for all routes.
     */
    private function configUrls(): void
    {
        URL::forceHttps();
    }

    /**
     * Configures the application to use CarbonImmutable for date and time handling.
     */
    private function configDate(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure Gates based on Can enum permissions.
     */
    private function configGates(): void
    {
        foreach (Can::cases() as $permission) {
            Gate::define(
                $permission->value,
                function (User $user) use ($permission) {
                    /** @var User $user */
                    $check = $user
                        ->permissions()
                        ->whereName($permission->value)
                        ->exists();

                    Log::info(
                        'Checking permission: ' . $permission->value,
                        [
                            'user'  => $user->id,
                            'check' => $check ? 'true' : 'false',
                        ]
                    );

                    return $check;
                }
            );
        }
    }
}
