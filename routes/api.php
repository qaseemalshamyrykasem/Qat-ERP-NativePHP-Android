<?php

use App\Http\Controllers\Api\V1\AccountingController;
use App\Http\Controllers\Api\V1\AgentController;
use App\Http\Controllers\Api\V1\AgentSettlementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\CurrencyController;
use App\Http\Controllers\Api\V1\DailySessionController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DebtController;
use App\Http\Controllers\Api\V1\DistributionController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\ReminderController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\StockController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\TransferController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VoucherController;
use App\Http\Controllers\Api\V1\WhatsAppController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1) — Stateless JSON endpoints for mobile/web/Flutter/React Native
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public auth
    Route::post('auth/login',  [AuthController::class, 'login']);

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get ('auth/me',     [AuthController::class, 'me']);

        // Dashboard
        Route::get('dashboard/overview', [DashboardController::class, 'overview']);

        // CRUD resources
        Route::apiResource('users',           UserController::class);
        Route::apiResource('suppliers',       SupplierController::class);
        Route::apiResource('customers',       CustomerController::class);
        Route::apiResource('agents',          AgentController::class);
        Route::apiResource('products',        ProductController::class);
        Route::apiResource('sales',           SaleController::class);
        Route::apiResource('purchases',       PurchaseController::class);
        Route::apiResource('distributions',   DistributionController::class);
        Route::apiResource('debts',           DebtController::class)->only(['index','show','destroy']);
        Route::apiResource('expenses',        ExpenseController::class)->only(['index','show','store','update','destroy']);
        Route::apiResource('currencies',      CurrencyController::class);
        Route::apiResource('reminders',       ReminderController::class);
        Route::apiResource('agent-settlements', AgentSettlementController::class)->only(['index','show','destroy']);
        Route::apiResource('daily-sessions',  DailySessionController::class)->only(['index','show']);

        // Sales - extra actions
        Route::post('debts/pay',         [DebtController::class, 'pay']);
        Route::get ('debts/{id}/history',[DebtController::class, 'history']);
        Route::post('debts/mark-overdue',[DebtController::class, 'markOverdue']);

        // Purchases - settle supplier
        Route::post('purchases/settle-supplier', [PurchaseController::class, 'settleSupplier']);

        // Agent settlements - calculate & save
        Route::post('agent-settlements/calculate', [AgentSettlementController::class, 'calculate']);
        Route::post('agent-settlements/save',      [AgentSettlementController::class, 'save']);

        // Daily sessions
        Route::post('daily-sessions/open',         [DailySessionController::class, 'open']);
        Route::post('daily-sessions/{id}/close',   [DailySessionController::class, 'close']);
        Route::get ('daily-sessions/stats',        [DailySessionController::class, 'stats']);

        // Accounting
        Route::apiResource('chart-of-accounts', AccountingController::class);
        Route::get ('journal-entries',          [AccountingController::class, 'journalIndex']);
        Route::post('journal-entries',          [AccountingController::class, 'journalStore']);
        Route::get ('journal-entries/{id}',     [AccountingController::class, 'journalShow']);
        Route::delete('journal-entries/{id}',   [AccountingController::class, 'journalDestroy']);
        Route::get ('trial-balance',            [AccountingController::class, 'trialBalance']);
        Route::get ('income-statement',         [AccountingController::class, 'incomeStatement']);
        Route::get ('balance-sheet',            [AccountingController::class, 'balanceSheet']);
        Route::get ('general-ledger',           [AccountingController::class, 'generalLedger']);

        // Vouchers
        Route::get ('receipt-vouchers',         [VoucherController::class, 'receiptsIndex']);
        Route::post('receipt-vouchers',         [VoucherController::class, 'receiptsStore']);
        Route::delete('receipt-vouchers/{id}',  [VoucherController::class, 'receiptsDestroy']);
        Route::get ('payment-vouchers',         [VoucherController::class, 'paymentsIndex']);
        Route::post('payment-vouchers',         [VoucherController::class, 'paymentsStore']);
        Route::delete('payment-vouchers/{id}',  [VoucherController::class, 'paymentsDestroy']);

        // Transfers
        Route::apiResource('transfers', TransferController::class);

        // Notifications
        Route::get ('notifications',             [NotificationController::class, 'index']);
        Route::get ('notifications/unread-count',[NotificationController::class, 'unreadCount']);
        Route::post('notifications/{id}/mark-read', [NotificationController::class, 'markRead']);
        Route::post('notifications/mark-all-read',  [NotificationController::class, 'markAllRead']);

        // WhatsApp
        Route::post('whatsapp/generate-link', [WhatsAppController::class, 'generateLink']);
        Route::get ('whatsapp/templates', [WhatsAppController::class, 'templates']);
        Route::get ('whatsapp/preview', [WhatsAppController::class, 'preview']);
        Route::get ('whatsapp/debt/{id}/reminder', [WhatsAppController::class, 'debtReminder']);
        Route::get ('whatsapp/sale/{id}/invoice', [WhatsAppController::class, 'saleInvoice']);

        // Global Search
        Route::get('search', [SearchController::class, 'search']);

        // Settings
        Route::get ('settings',         [SettingController::class, 'index']);
        Route::get ('settings/{key}',   [SettingController::class, 'show']);
        Route::post('settings',         [SettingController::class, 'update']);
        Route::put ('settings',         [SettingController::class, 'update']);

        // Currencies - set default
        Route::post('currencies/{id}/set-default', [CurrencyController::class, 'setDefault']);

        // Stock operations
        Route::post('stock/adjust',         [StockController::class, 'adjust']);
        Route::post('stock/restock',        [StockController::class, 'restock']);
        Route::get ('stock/low-stock',      [StockController::class, 'lowStock']);
        Route::get ('stock/agent/{agentId}',[StockController::class, 'agentStock']);

        // Reports
        Route::get('reports/daily',                          [ReportController::class, 'daily']);
        Route::get('reports/monthly',                        [ReportController::class, 'monthly']);
        Route::get('reports/supplier-statement/{supplierId}',[ReportController::class, 'supplierStatement']);
        Route::get('reports/agent-statement/{agentId}',      [ReportController::class, 'agentStatement']);
        Route::get('reports/debts',                          [ReportController::class, 'debts']);
    });
});
