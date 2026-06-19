<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind domain services as singletons (DI-friendly).
        $this->app->singleton(\App\Services\AuthService::class);
        $this->app->singleton(\App\Services\SaleService::class);
        $this->app->singleton(\App\Services\PurchaseService::class);
        $this->app->singleton(\App\Services\DistributionService::class);
        $this->app->singleton(\App\Services\DebtService::class);
        $this->app->singleton(\App\Services\ExpenseService::class);
        $this->app->singleton(\App\Services\AccountingService::class);
        $this->app->singleton(\App\Services\StockService::class);
        $this->app->singleton(\App\Services\StockReservationService::class);
        $this->app->singleton(\App\Services\SequenceService::class);
        $this->app->singleton(\App\Services\ReportService::class);
        $this->app->singleton(\App\Services\NotificationService::class);
        $this->app->singleton(\App\Services\FinancialTransactionService::class);
        $this->app->singleton(\App\Services\VoucherService::class);
        $this->app->singleton(\App\Services\TransferService::class);
        $this->app->singleton(\App\Services\DailySessionService::class);
        $this->app->singleton(\App\Services\AgentSettlementService::class);
        $this->app->singleton(\App\Services\DashboardService::class);
        $this->app->singleton(\App\Services\CurrencyService::class);
        $this->app->singleton(\App\Services\DocumentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force JSON for API routes
        if (request()->is('api/*')) {
            request()->headers->set('Accept', 'application/json');
        }

        // Money formatting helper (Arabic + Latin numerals)
        \Illuminate\Support\Number::macro('money', function (float|int|string $amount, string $currency = 'YER', int $precision = 2): string {
            $value = number_format((float) $amount, $precision, '.', ',');
            return $value . ' ' . $currency;
        });
    }
}
