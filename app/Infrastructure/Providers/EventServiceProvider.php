<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Application\Listeners\GenerateStockingHistory;
use App\Domain\Events\FeedingCreated;
use App\Domain\Events\MortalityRecorded;
use App\Domain\Events\SaleProcessed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        FeedingCreated::class => [
            GenerateStockingHistory::class . '@handleFeedingCreated',
        ],

        MortalityRecorded::class => [
            GenerateStockingHistory::class . '@handleMortalityRecorded',
        ],

        SaleProcessed::class => [
            GenerateStockingHistory::class . '@handleSaleProcessed',
        ],
    ];
}
