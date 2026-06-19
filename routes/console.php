<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('qat:seed-defaults', function () {
    $this->info('Seeding default chart of accounts, currencies, wallets...');
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ChartOfAccountsSeeder']);
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CurrenciesTableSeeder']);
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PaymentWalletsTableSeeder']);
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PermissionsSeeder']);
    $this->info('Done.');
})->purpose('Seed default reference data (COA, currencies, wallets, permissions).');

Artisan::command('qat:mark-overdue-debts', function () {
    $count = app(\App\Services\DebtService::class)->markOverdue();
    $this->info("Marked {$count} debts as overdue.");
})->purpose('Mark overdue debts (due_date < today).');

Artisan::command('qat:purge-expired-reservations', function () {
    $count = app(\App\Services\StockReservationService::class)->purgeExpired();
    $this->info("Purged {$count} expired stock reservations.");
})->purpose('Purge expired stock reservations.');
