<?php

namespace App\Providers;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(BroadcastManager $broadcast): void
    {
        $broadcast->routes(['middleware' => ['auth', 'web'], 'prefix' => 'broadcasting']);

        require base_path('routes/channels.php');
    }
}
