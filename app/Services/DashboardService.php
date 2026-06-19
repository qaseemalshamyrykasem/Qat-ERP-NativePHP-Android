<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\PaymentWallet;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

/**
 * DashboardService — KPI roll-ups for the dashboard view.
 */
class DashboardService
{
    public function overview(): array
    {
        $today = now()->format('Y-m-d');
        $month = now()->format('Y-m');

        return [
            'today_sales'      => (float) Sale::whereDate('sale_date', $today)->sum('final_amount'),
            'today_purchases'  => (float) Purchase::whereDate('purchase_date', $today)->sum('total_amount'),
            'today_expenses'   => (float) Expense::whereDate('expense_date', $today)->sum('amount'),
            'today_cogs'       => (float) SaleItem::whereHas('sale', fn($q) => $q->whereDate('sale_date', $today))->sum('cogs_amount'),

            'month_sales'      => (float) Sale::whereYear('sale_date', substr($month, 0, 4))->whereMonth('sale_date', substr($month, 5, 2))->sum('final_amount'),
            'month_expenses'   => (float) Expense::whereYear('expense_date', substr($month, 0, 4))->whereMonth('expense_date', substr($month, 5, 2))->sum('amount'),

            'debts_total'      => (float) Debt::sum('remaining_amount'),
            'debts_overdue'    => Debt::where('status', 'overdue')->count(),

            'products_low_stock' => Product::whereColumn('quantity', '<=', 'min_quantity')->count(),
            'products_count'   => Product::count(),

            'agents_count'     => Agent::where('status', 'active')->count(),
            'suppliers_count'  => Supplier::where('status', true)->count(),
            'customers_count'  => Customer::where('status', 'active')->count(),
            'wallets_count'    => PaymentWallet::where('status', true)->count(),
        ];
    }
}
