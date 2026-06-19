<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['setting_key' => 'app_name',           'setting_value' => 'Qat ERP',                     'description' => 'اسم التطبيق'],
            ['setting_key' => 'default_currency',   'setting_value' => 'YER',                          'description' => 'العملة الافتراضية'],
            ['setting_key' => 'default_unit',       'setting_value' => 'حزمة',                         'description' => 'وحدة القياس الافتراضية'],
            ['setting_key' => 'company_name',       'setting_value' => 'مؤسسة تاجر القات',             'description' => 'اسم الشركة'],
            ['setting_key' => 'company_phone',      'setting_value' => '777000000',                    'description' => 'هاتف الشركة'],
            ['setting_key' => 'company_address',    'setting_value' => 'صنعاء - اليمن',                'description' => 'عنوان الشركة'],
            ['setting_key' => 'enable_whatsapp',    'setting_value' => '0',                            'description' => 'تفعيل تكامل واتساب'],
            ['setting_key' => 'enable_daily_session','setting_value' => '1',                           'description' => 'تفعيل الجلسة اليومية الإلزامية'],
            ['setting_key' => 'login_max_attempts', 'setting_value' => '5',                            'description' => 'حد محاولات الدخول'],
            ['setting_key' => 'login_lockout_mins', 'setting_value' => '15',                           'description' => 'مدة الققل بالدقائق'],
            ['setting_key' => 'stock_reservation_mins','setting_value' => '5',                        'description' => 'مدة حجز المخزون بالدقائق'],
        ];
        foreach ($settings as $s) {
            Setting::updateOrCreate(['setting_key' => $s['setting_key']], $s);
        }
    }
}
