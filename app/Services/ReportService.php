<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Debt;
use App\Models\Agent;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\AgentSettlement;
use Illuminate\Support\Facades\DB;

/**
 * ReportService — generates daily / monthly / supplier / agent / debt reports.
 * All queries use Eloquent + indexes; no business logic in views.
 */
class ReportService
{
    public function daily(string $date): array
    {
        $sales = Sale::with('items', 'agent')->whereDate('sale_date', $date)->get();
        $expenses = Expense::whereDate('expense_date', $date)->get();
        $purchases = Purchase::with('supplier')->whereDate('purchase_date', $date)->get();
        $debts = Debt::whereHas('payments', fn($q) => $q->whereDate('payment_date', $date))->get();
        $settlements = AgentSettlement::whereDate('settlement_date', $date)->get();

        return [
            'date'         => $date,
            'sales_total'  => (float) $sales->sum('final_amount'),
            'sales_cash'   => (float) $sales->where('payment_method', 'cash')->sum('final_amount'),
            'sales_credit' => (float) $sales->where('payment_method', 'credit')->sum('final_amount'),
            'sales_transfer' => (float) $sales->where('payment_method', 'transfer')->sum('final_amount'),
            'expenses_total' => (float) $expenses->sum('amount'),
            'purchases_total'=> (float) $purchases->sum('total_amount'),
            'debts_collected'=> (float) $debts->sum('paid_amount'),
            'settlements_count' => $settlements->count(),
            'net_profit'   => (float) $sales->sum('final_amount') - (float) $sales->sum(fn($s) => $s->items->sum('cogs_amount')) - (float) $expenses->sum('amount'),
            'agents_performance' => $this->agentsPerformanceForDate($date),
            'top_customers' => $this->topCustomersForDate($date, 10),
            'sales_rows'   => $sales,
            'expenses_rows'=> $expenses,
            'purchases_rows'=> $purchases,
        ];
    }

    public function monthly(string $month): array
    {
        // $month = 'YYYY-MM'
        [$y, $m] = explode('-', $month);
        $sales = Sale::whereYear('sale_date', $y)->whereMonth('sale_date', $m)->get();
        $expenses = Expense::whereYear('expense_date', $y)->whereMonth('expense_date', $m)->get();
        $purchases = Purchase::whereYear('purchase_date', $y)->whereMonth('purchase_date', $m)->get();
        $debts = Debt::whereYear('created_at', $y)->whereMonth('created_at', $m)->get();

        return [
            'month' => $month,
            'sales_total'   => (float) $sales->sum('final_amount'),
            'expenses_total'=> (float) $expenses->sum('amount'),
            'purchases_total' => (float) $purchases->sum('total_amount'),
            'debts_total'   => (float) $debts->sum('total_amount'),
            'debts_paid'    => (float) $debts->sum('paid_amount'),
            'debts_remaining' => (float) $debts->sum('remaining_amount'),
            'sales_breakdown' => [
                'cash'     => (float) $sales->where('payment_method', 'cash')->sum('final_amount'),
                'credit'   => (float) $sales->where('payment_method', 'credit')->sum('final_amount'),
                'transfer' => (float) $sales->where('payment_method', 'transfer')->sum('final_amount'),
            ],
        ];
    }

    public function supplierStatement(int $supplierId, ?string $from = null, ?string $to = null): array
    {
        $supplier = Supplier::with(['purchases' => fn($q) => $q->when($from, fn($q2) => $q2->where('purchase_date', '>=', $from))->when($to, fn($q2) => $q2->where('purchase_date', '<=', $to))])
            ->findOrFail($supplierId);

        return [
            'supplier'  => $supplier,
            'purchases' => $supplier->purchases,
            'debts'     => \App\Models\SupplierDebt::where('supplier_id', $supplierId)->get(),
            'balance'   => (float) $supplier->balance,
            'total_purchases' => (float) $supplier->total_purchases,
            'total_paid' => (float) $supplier->total_paid,
            'total_remaining' => (float) $supplier->total_remaining,
        ];
    }

    public function agentStatement(int $agentId, ?string $from = null, ?string $to = null): array
    {
        $agent = Agent::with([
            'sales'    => fn($q) => $q->when($from, fn($q2) => $q2->where('sale_date', '>=', $from))->when($to, fn($q2) => $q2->where('sale_date', '<=', $to)),
            'distributions' => fn($q) => $q->when($from, fn($q2) => $q2->where('distribution_date', '>=', $from))->when($to, fn($q2) => $q2->where('distribution_date', '<=', $to)),
            'settlements' => fn($q) => $q->when($from, fn($q2) => $q2->where('settlement_date', '>=', $from))->when($to, fn($q2) => $q2->where('settlement_date', '<=', $to)),
        ])->findOrFail($agentId);

        return [
            'agent' => $agent,
            'total_sales' => (float) $agent->sales->sum('final_amount'),
            'cash_sales'  => (float) $agent->sales->where('payment_method', 'cash')->sum('final_amount'),
            'credit_sales'=> (float) $agent->sales->where('payment_method', 'credit')->sum('final_amount'),
            'transfer_sales' => (float) $agent->sales->where('payment_method', 'transfer')->sum('final_amount'),
            'total_distributions' => (float) $agent->distributions->sum('total_amount'),
            'settlements' => $agent->settlements,
        ];
    }

    public function debtsOverview(): array
    {
        return [
            'total_debts' => (float) Debt::sum('total_amount'),
            'total_paid'  => (float) Debt::sum('paid_amount'),
            'total_remaining' => (float) Debt::sum('remaining_amount'),
            'overdue_count' => Debt::where('status', 'overdue')->count(),
            'pending_count' => Debt::where('status', 'pending')->count(),
            'partial_count' => Debt::where('status', 'partial')->count(),
            'top_debtors' => Customer::withSum('debts as total_remaining', 'remaining_amount')
                ->orderByDesc('total_remaining')
                ->limit(15)
                ->get(),
        ];
    }

    private function agentsPerformanceForDate(string $date): array
    {
        return Agent::with(['sales' => fn($q) => $q->whereDate('sale_date', $date)])
            ->get()
            ->map(fn($a) => [
                'agent_id' => $a->id,
                'agent_name' => $a->name,
                'total_sales' => (float) $a->sales->sum('final_amount'),
                'cash_sales'  => (float) $a->sales->where('payment_method', 'cash')->sum('final_amount'),
                'credit_sales'=> (float) $a->sales->where('payment_method', 'credit')->sum('final_amount'),
                'transfer_sales' => (float) $a->sales->where('payment_method', 'transfer')->sum('final_amount'),
                'count' => $a->sales->count(),
            ])
            ->where('total_sales', '>', 0)
            ->values()
            ->all();
    }

    private function topCustomersForDate(string $date, int $limit = 10): array
    {
        return Customer::with(['sales' => fn($q) => $q->whereDate('sale_date', $date)])
            ->get()
            ->map(fn($c) => [
                'customer_id' => $c->id,
                'name' => $c->name,
                'total' => (float) $c->sales->sum('final_amount'),
            ])
            ->where('total', '>', 0)
            ->sortByDesc('total')
            ->take($limit)
            ->values()
            ->all();
    }
}
