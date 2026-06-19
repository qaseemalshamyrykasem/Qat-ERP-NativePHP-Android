<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'قات صعدي ممتاز', 'type' => 'صعدي', 'buy_price' => 15000, 'sell_price' => 18000, 'quantity' => 50,  'unit' => 'حزمة', 'min_quantity' => 5,  'supplier_id' => 1, 'weighted_average_cost' => 15000, 'status' => 1],
            ['name' => 'قات صعدي جيد',   'type' => 'صعدي', 'buy_price' => 12000, 'sell_price' => 15000, 'quantity' => 80,  'unit' => 'حزمة', 'min_quantity' => 10, 'supplier_id' => 1, 'weighted_average_cost' => 12000, 'status' => 1],
            ['name' => 'قات صعدي عادي',  'type' => 'صعدي', 'buy_price' => 8000,  'sell_price' => 10000, 'quantity' => 100, 'unit' => 'حزمة', 'min_quantity' => 15, 'supplier_id' => 5, 'weighted_average_cost' => 8000,  'status' => 1],
            ['name' => 'قات ذماري ممتاز','type' => 'ذماري','buy_price' => 10000, 'sell_price' => 13000, 'quantity' => 60,  'unit' => 'حزمة', 'min_quantity' => 8,  'supplier_id' => 2, 'weighted_average_cost' => 10000, 'status' => 1],
            ['name' => 'قات ذماري عادي', 'type' => 'ذماري','buy_price' => 6000,  'sell_price' => 8000,  'quantity' => 90,  'unit' => 'حزمة', 'min_quantity' => 12, 'supplier_id' => 2, 'weighted_average_cost' => 6000,  'status' => 1],
            ['name' => 'قات تعزي',       'type' => 'تعزي', 'buy_price' => 11000, 'sell_price' => 14000, 'quantity' => 40,  'unit' => 'حزمة', 'min_quantity' => 5,  'supplier_id' => 3, 'weighted_average_cost' => 11000, 'status' => 1],
            ['name' => 'قات إبي',        'type' => 'إبي',  'buy_price' => 9000,  'sell_price' => 12000, 'quantity' => 45,  'unit' => 'حزمة', 'min_quantity' => 6,  'supplier_id' => 4, 'weighted_average_cost' => 9000,  'status' => 1],
            ['name' => 'قات حجاجي',      'type' => 'حجاجي','buy_price' => 7000,  'sell_price' => 9500,  'quantity' => 70,  'unit' => 'حزمة', 'min_quantity' => 10, 'supplier_id' => 6, 'weighted_average_cost' => 7000,  'status' => 1],
        ];
        foreach ($rows as $r) Product::updateOrCreate(['name' => $r['name']], $r);
    }
}
