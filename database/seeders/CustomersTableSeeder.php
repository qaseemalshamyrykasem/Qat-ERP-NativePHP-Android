<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'أبو سامي', 'phone' => '771111111', 'address' => 'صنعاء - الزبيري', 'total_debt' => 15000, 'total_paid' => 12000, 'remaining' => 3000, 'last_payment_date' => '2026-05-28', 'status' => 'active', 'agent_id' => 1, 'notes' => 'عميل منتظم'],
            ['name' => 'أمين الكبسي', 'phone' => '771222222', 'address' => 'صنعاء - الستين', 'total_debt' => 8000, 'total_paid' => 5000, 'remaining' => 3000, 'last_payment_date' => '2026-05-25', 'status' => 'active', 'agent_id' => 1, 'notes' => 'يسدد بانتظام'],
            ['name' => 'خالد المطري', 'phone' => '771333333', 'address' => 'صنعاء - حدة', 'total_debt' => 25000, 'total_paid' => 10000, 'remaining' => 15000, 'last_payment_date' => '2026-05-20', 'status' => 'active', 'agent_id' => 2, 'notes' => 'عليه ديون متأخرة'],
            ['name' => 'أبو عبدالله', 'phone' => '771444444', 'address' => 'صنعاء - الجزائر', 'total_debt' => 5000, 'total_paid' => 4500, 'remaining' => 500, 'last_payment_date' => '2026-05-29', 'status' => 'active', 'agent_id' => 2, 'notes' => 'عميل جيد'],
            ['name' => 'فهد القحطاني', 'phone' => '771555555', 'address' => 'صنعاء - فوة', 'total_debt' => 0, 'total_paid' => 20000, 'remaining' => 0, 'last_payment_date' => '2026-05-30', 'status' => 'active', 'agent_id' => 3, 'notes' => 'يسدد فوراً'],
            ['name' => 'سعيد اليزيدي', 'phone' => '771666666', 'address' => 'صنعاء - بيت بوس', 'total_debt' => 35000, 'total_paid' => 5000, 'remaining' => 30000, 'last_payment_date' => '2026-04-15', 'status' => 'blocked', 'agent_id' => 1, 'notes' => 'ديون متأخرة جداً'],
            ['name' => 'مازن الشميري', 'phone' => '771777777', 'address' => 'ذمار', 'total_debt' => 12000, 'total_paid' => 8000, 'remaining' => 4000, 'last_payment_date' => '2026-05-27', 'status' => 'active', 'agent_id' => 4],
            ['name' => 'راشد العولقي', 'phone' => '771888888', 'address' => 'تعز', 'total_debt' => 7000, 'total_paid' => 6000, 'remaining' => 1000, 'last_payment_date' => '2026-05-30', 'status' => 'active', 'agent_id' => 5, 'notes' => 'عميل جديد'],
        ];
        foreach ($rows as $r) Customer::updateOrCreate(['phone' => $r['phone']], $r);
    }
}
