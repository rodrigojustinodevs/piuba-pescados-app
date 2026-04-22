<?php

declare(strict_types=1);

return [
    App\Infrastructure\Providers\AppServiceProvider::class,
    App\Infrastructure\Providers\EventServiceProvider::class,
    App\Infrastructure\Providers\RouteServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    OwenIt\Auditing\AuditingServiceProvider::class,
];
