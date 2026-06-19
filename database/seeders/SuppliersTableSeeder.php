<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SuppliersTableSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'أبو خالد للقات الصعدي', 'phone' => '773111111', 'address' => 'صنعاء - شارع الستين', 'specialization' => 'قات صعدي', 'notes' => 'مورد رئيسي', 'balance' => -45000, 'total_purchases' => 285000, 'total_paid' => 240000, 'total_remaining' => 45000, 'status' => 1],
            ['name' => 'مزارع الذماري',         'phone' => '773222222', 'address' => 'ذمار - الحيمة',    'specialization' => 'قات ذماري', 'notes' => 'جودة عالية', 'balance' => -30000, 'total_purchases' => 195000, 'total_paid' => 165000, 'total_remaining' => 30000, 'status' => 1],
            ['name' => 'أبو ياسر للقات',        'phone' => '773333333', 'address' => 'تعز - جبل صبر',   'specialization' => 'قات تعزي',  'notes' => 'ممتاز', 'balance' => -20000, 'total_purchases' => 120000, 'total_paid' => 100000, 'total_remaining' => 20000, 'status' => 1],
            ['name' => 'مزرعة الأمل',            'phone' => '773444444', 'address' => 'إب - السياني',    'specialization' => 'قات إبي',   'notes' => 'متنوع', 'balance' => -15000, 'total_purchases' => 85000,  'total_paid' => 70000,  'total_remaining' => 15000, 'status' => 1],
            ['name' => 'الوادي الأخضر',          'phone' => '773555555', 'address' => 'صنعاء - بني الحارث','specialization' => 'قات صعدي','notes' => 'مورد جديد', 'balance' => -5000, 'total_purchases' => 35000,  'total_paid' => 30000,  'total_remaining' => 5000,  'status' => 1],
            ['name' => 'أبو فهد',                'phone' => '773666666', 'address' => 'حجة - المحابشة',  'specialization' => 'قات حجاجي', 'notes' => 'أسعار منافسة', 'balance' => 0, 'total_purchases' => 15000, 'total_paid' => 15000, 'total_remaining' => 0, 'status' => 1],
        ];
        foreach ($rows as $r) Supplier::updateOrCreate(['name' => $r['name']], $r);
    }
}
