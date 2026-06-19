<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PrintController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Blade UI for traditional browser access.
| Mobile/API clients use /api/v1/* (see api.php).
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    return redirect()->route('login');
});

// Auth
Route::get ('login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('login',  [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Each module renders a Blade view that consumes /api/v1/* via fetch (no business logic in views).
    $modules = [
        'sales' => 'sales.index',
        'purchases' => 'purchases.index',
        'distributions' => 'distributions.index',
        'products' => 'products.index',
        'customers' => 'customers.index',
        'suppliers' => 'suppliers.index',
        'agents' => 'agents.index',
        'debts' => 'debts.index',
        'expenses' => 'expenses.index',
        'pos' => 'pos.index',
        'daily-session' => 'daily-session.index',
        'reports' => 'reports.index',
        'agent-settlements' => 'agent-settlements.index',
        'accounts' => 'accounts.index',
        'chart-of-accounts' => 'accounts.index',
        'journal-entries' => 'journal-entries.index',
        'trial-balance' => 'accounts.trial-balance',
        'income-statement' => 'accounts.income-statement',
        'balance-sheet' => 'accounts.balance-sheet',
        'general-ledger' => 'accounts.general-ledger',
        'receipt-vouchers' => 'receipt-vouchers.index',
        'payment-vouchers' => 'payment-vouchers.index',
        'transfers' => 'transfers.index',
        'currencies' => 'currencies.index',
        'reminders' => 'reminders.index',
        'notifications' => 'notifications.index',
        'settings' => 'settings.index',
        'users' => 'users.index',
        'users/permissions' => 'users.permissions',
        'whatsapp' => 'whatsapp.index',
    ];
    foreach ($modules as $path => $name) {
        Route::get($path, fn() => view(str_replace('.', '/', $name)))->name($name);
    }

    // Print routes
    Route::get('prints/sale-invoice/{sale}', [PrintController::class, 'saleInvoice'])->name('prints.sale-invoice');
    Route::get('prints/sale-invoice/{sale}/pdf', [PrintController::class, 'saleInvoicePdf'])->name('prints.sale-invoice.pdf');
    Route::get('prints/receipt-voucher/{voucher}', [PrintController::class, 'receiptVoucher'])->name('prints.receipt-voucher');
    Route::get('prints/receipt-voucher/{voucher}/pdf', [PrintController::class, 'receiptVoucherPdf'])->name('prints.receipt-voucher.pdf');
    Route::get('prints/payment-voucher/{voucher}', [PrintController::class, 'paymentVoucher'])->name('prints.payment-voucher');
    Route::get('prints/payment-voucher/{voucher}/pdf', [PrintController::class, 'paymentVoucherPdf'])->name('prints.payment-voucher.pdf');
});
