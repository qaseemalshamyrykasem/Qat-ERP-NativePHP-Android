<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<string, string>
     */
    protected $policies = [
        \App\Models\User::class          => \App\Policies\UserPolicy::class,
        \App\Models\Supplier::class      => \App\Policies\SupplierPolicy::class,
        \App\Models\Customer::class      => \App\Policies\CustomerPolicy::class,
        \App\Models\Agent::class         => \App\Policies\AgentPolicy::class,
        \App\Models\Product::class       => \App\Policies\ProductPolicy::class,
        \App\Models\Sale::class          => \App\Policies\SalePolicy::class,
        \App\Models\Purchase::class      => \App\Policies\PurchasePolicy::class,
        \App\Models\Distribution::class  => \App\Policies\DistributionPolicy::class,
        \App\Models\Debt::class          => \App\Policies\DebtPolicy::class,
        \App\Models\Expense::class       => \App\Policies\ExpensePolicy::class,
        \App\Models\JournalEntry::class  => \App\Policies\JournalEntryPolicy::class,
        \App\Models\ReceiptVoucher::class => \App\Policies\ReceiptVoucherPolicy::class,
        \App\Models\PaymentVoucher::class => \App\Policies\PaymentVoucherPolicy::class,
        \App\Models\AccountTransfer::class => \App\Policies\AccountTransferPolicy::class,
        \App\Models\AgentSettlement::class => \App\Policies\AgentSettlementPolicy::class,
        \App\Models\DailySession::class  => \App\Policies\DailySessionPolicy::class,
        \App\Models\Reminder::class      => \App\Policies\ReminderPolicy::class,
        \App\Models\Currency::class      => \App\Policies\CurrencyPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gate: admin wildcard
        Gate::before(function ($user, $ability) {
            if ($user instanceof \App\Models\User && $user->isAdmin()) {
                return true;
            }
        });
    }
}
