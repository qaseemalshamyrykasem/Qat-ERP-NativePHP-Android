<?php

namespace Database\Seeders;

use App\Models\PaymentWallet;
use Illuminate\Database\Seeder;

class PaymentWalletsTableSeeder extends Seeder
{
    public function run(): void
    {
        $wallets = ['جيب', 'فلوسك', 'جوالي', 'محفظة أخرى'];
        foreach ($wallets as $name) {
            PaymentWallet::firstOrCreate(['name' => $name], ['status' => true]);
        }
    }
}
