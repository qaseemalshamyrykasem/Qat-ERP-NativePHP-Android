<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;

class AgentsTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'محمد سالم البكري', 'phone' => '777456789', 'area' => 'صنعاء - شارع الزبيري', 'balance' => 12500, 'status' => 'active', 'commission_rate' => 5.00, 'notes' => 'وكيل نشط'],
            ['name' => 'علي حسين الشمري',  'phone' => '777567890', 'area' => 'صنعاء - شارع الستين',  'balance' => 8200,  'status' => 'active', 'commission_rate' => 4.50, 'notes' => 'وكيل ممتاز'],
            ['name' => 'يحيى عبدالرحمن',   'phone' => '777678901', 'area' => 'صنعاء - حدة',          'balance' => 5300,  'status' => 'active', 'commission_rate' => 4.00, 'notes' => 'وكيل جديد'],
            ['name' => 'عبدالملك الحمزي',   'phone' => '777789012', 'area' => 'ذمار',                 'balance' => 0,     'status' => 'inactive', 'commission_rate' => 3.50, 'notes' => 'غير نشط'],
            ['name' => 'صالح الأحمدي',      'phone' => '777890123', 'area' => 'تعز',                  'balance' => -2500, 'status' => 'active', 'commission_rate' => 5.00, 'notes' => 'عليه مبلغ مستحق'],
        ];
        foreach ($rows as $r) Agent::updateOrCreate(['name' => $r['name']], $r);
    }
}
