<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrenciesTableSeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'YER', 'name' => 'الريال اليمني',  'symbol' => 'ر.ي', 'exchange_rate' => 1.000000, 'is_default' => true,  'is_active' => true],
            ['code' => 'SAR', 'name' => 'الريال السعودي',  'symbol' => 'ر.س', 'exchange_rate' => 0.037000, 'is_default' => false, 'is_active' => true],
            ['code' => 'USD', 'name' => 'الدولار الأمريكي','symbol' => '$',  'exchange_rate' => 0.004000, 'is_default' => false, 'is_active' => true],
            ['code' => 'AED', 'name' => 'الدرهم الإماراتي', 'symbol' => 'د.إ','exchange_rate' => 0.015000, 'is_default' => false, 'is_active' => true],
        ];
        foreach ($currencies as $c) {
            Currency::updateOrCreate(['code' => $c['code']], $c);
        }
    }
}
