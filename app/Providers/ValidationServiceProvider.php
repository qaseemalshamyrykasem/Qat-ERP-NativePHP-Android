<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class ValidationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Password::defaults(function () {
            $rule = Password::min(8)->mixedCase()->numbers()->symbols();
            return app()->isProduction() ? $rule->uncompromised() : $rule;
        });
    }
}
