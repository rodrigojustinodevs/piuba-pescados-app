<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        Auth::viaRequest('api', function ($request) {
            $token = $request->bearerToken();

            if (! $token) {
                return null;
            }

            try {
                return auth('api')->user();
            } catch (\Exception) {
                return null;
            }
        });
    }
}
