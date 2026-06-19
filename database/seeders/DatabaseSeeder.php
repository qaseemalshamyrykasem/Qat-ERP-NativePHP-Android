<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Core reference data (run first)
            CurrenciesTableSeeder::class,
            PaymentWalletsTableSeeder::class,
            ChartOfAccountsSeeder::class,
            DocumentSequencesSeeder::class,
            PermissionsSeeder::class,
            SettingsTableSeeder::class,

            // Parties
            AgentsTableSeeder::class,
            SuppliersTableSeeder::class,
            CustomersTableSeeder::class,

            // Products
            ProductsTableSeeder::class,

            // Users
            UsersTableSeeder::class,

            // Demo transactions (optional, useful for local testing)
            DemoTransactionsSeeder::class,
        ]);
    }
}
