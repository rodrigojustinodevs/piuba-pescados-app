<?php

declare(strict_types=1);

return [
    App\Infrastructure\Providers\AppServiceProvider::class,
    App\Infrastructure\Providers\RouteServiceProvider::class,
    OwenIt\Auditing\AuditingServiceProvider::class,
];
