<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppDomainServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Strict mode in development, lenient in production
        Model::shouldBeStrict(! app()->isProduction());
        Model::unguard(); // We use Form Requests for mass-assignment protection (simpler than $fillable everywhere)
    }
}
