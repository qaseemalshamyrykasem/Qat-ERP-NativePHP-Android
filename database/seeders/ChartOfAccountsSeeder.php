<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Code, Name, EN, Parent, Type, Level, Direction
            ['1100', 'الأصول المتداولة',      'Current Assets',     null, 'asset', 1, 'debit'],
            ['1101', 'الصندوق الرئيسي',       'Main Cash',          1,    'asset', 2, 'debit'],
            ['1103', 'المحافظ الإلكترونية',   'E-Wallets',          1,    'asset', 2, 'debit'],
            ['1200', 'الذمم المدينة',          'Receivables',        null, 'asset', 1, 'debit'],
            ['1201', 'عملاء آجلين',            'Credit Customers',   2,    'asset', 2, 'debit'],
            ['1300', 'المخزون',                'Inventory',          null, 'asset', 1, 'debit'],
            ['1301', 'المخزون الرئيسي',        'Main Inventory',     3,    'asset', 2, 'debit'],
            ['1302', 'مخزون الوكلاء',          'Agent Inventory',    3,    'asset', 2, 'debit'],
            ['2100', 'الخصوم المتداولة',       'Current Liabilities',null, 'liability', 1, 'credit'],
            ['2101', 'موردين آجلين',           'Credit Suppliers',   4,    'liability', 2, 'credit'],
            ['3000', 'حقوق الملكية',           'Equity',             null, 'equity',   1, 'credit'],
            ['4000', 'الإيرادات',              'Revenue',            null, 'revenue',  1, 'credit'],
            ['4100', 'إيرادات المبيعات',       'Sales Revenue',      5,    'revenue',  2, 'credit'],
            ['5000', 'التكاليف والمصروفات',    'Costs & Expenses',   null, 'expense',  1, 'debit'],
            ['5100', 'تكلفة البضاعة المباعة',  'COGS',               6,    'expense',  2, 'debit'],
            ['5200', 'مصروفات تشغيلية',        'Operating Expenses', 6,    'expense',  2, 'debit'],
        ];

        // Map by code for parent resolution
        $byCode = [];
        foreach ($rows as $row) {
            [$code, $name, $nameEn, $parentCode, $type, $level, $direction] = $row;
            $parentId = $parentCode ? ($byCode[$parentCode]?->id ?? null) : null;
            $acc = ChartOfAccount::updateOrCreate(
                ['code' => $code],
                [
                    'name'             => $name,
                    'name_en'          => $nameEn,
                    'parent_id'        => $parentId,
                    'account_type'     => $type,
                    'level'            => $level,
                    'balance_direction'=> $direction,
                    'is_active'        => true,
                    'current_balance'  => 0,
                ]
            );
            $byCode[$code] = $acc;
        }
    }
}
