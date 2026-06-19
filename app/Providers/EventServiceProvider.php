<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Register domain event listeners here
        // \App\Events\SaleCreated::class => [\App\Listeners\NotifyAgent::class],
    ];

    public function boot(): void
    {
        //
    }
}
