<?php

return [
    'currency_default' => env('QAT_DEFAULT_CURRENCY', 'YER'),
    'login_max_attempts' => (int) env('QAT_LOGIN_MAX_ATTEMPTS', 5),
    'login_lockout_minutes' => (int) env('QAT_LOGIN_LOCKOUT_MINUTES', 15),
    'stock_reservation_minutes' => (int) env('QAT_STOCK_RESERVATION_MINUTES', 5),
    'date_format' => env('QAT_DATE_FORMAT', 'Y-m-d'),
    'money_precision' => (int) env('QAT_MONEY_PRECISION', 2),
    'default_unit' => 'حزمة',
    'payment_methods' => ['cash', 'credit', 'transfer'],
    'wallet_types' => ['جيب', 'فلوسك', 'جوالي', 'محفظة أخرى'],
    'expense_categories' => ['مواصلات', 'أجور', 'تحميل وتنزيل', 'كهرباء', 'إيجار', 'خسائر', 'تلف', 'أخرى'],
    'modules' => [
        'auth', 'dashboard', 'pos', 'sales', 'purchases', 'products', 'customers',
        'agents', 'suppliers', 'reports', 'expenses', 'debts', 'distributions',
        'settings', 'users', 'accounts', 'chart_of_accounts', 'journal_entries',
        'transfers', 'vouchers', 'reminders', 'notifications', 'currencies',
        'daily_session', 'agent_settlements', 'whatsapp',
    ],
];
