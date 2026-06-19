<?php

namespace App\Services;

use App\Models\DailySession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * DailySessionService — open/close the daily cash session.
 *
 * Open  : record opening_balance (carried over from last closed session).
 * Close : compute totals from sales/expenses/debt_payments for the day,
 *         record expected vs actual, lock the session.
 */
class DailySessionService
{
    public function __construct(
        protected FinancialTransactionService $financial,
    ) {}

    public function open(float $openingBalance, ?string $date = null, ?int $userId = null): array
    {
        $date = $date ?? now()->format('Y-m-d');
        if (DailySession::where('session_date', $date)->where('status', 'open')->exists()) {
            return ['success' => false, 'message' => 'يوجد جلسة مفتوحة لهذا اليوم'];
        }

        try {
            $session = DailySession::create([
                'session_date'    => $date,
                'opening_balance' => $openingBalance,
                'status'          => 'open',
                'opened_by'       => $userId ?? auth()->id(),
                'opened_at'       => now(),
            ]);
            return ['success' => true, 'message' => 'تم فتح الجلسة', 'session' => $session];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function close(int $sessionId, float $actualBalance, ?string $notes = null, ?int $userId = null): array
    {
        $session = DailySession::find($sessionId);
        if (! $session) return ['success' => false, 'message' => 'الجلسة غير موجودة'];
        if ($session->status === 'closed') return ['success' => false, 'message' => 'الجلسة مغلقة بالفعل'];

        try {
            $stats = $this->computeStats($session->session_date);
            $expected = (float) $session->opening_balance
                + $stats['total_cash']
                + $stats['total_transfers']
                - $stats['total_expenses']
                - $stats['total_debt_payments'];

            $diff = round($actualBalance - $expected, 2);

            $session->update([
                'total_sales'         => $stats['total_sales'],
                'total_cash'          => $stats['total_cash'],
                'total_credit'        => $stats['total_credit'],
                'total_transfers'     => $stats['total_transfers'],
                'total_expenses'      => $stats['total_expenses'],
                'total_debt_payments' => $stats['total_debt_payments'],
                'net_profit'          => $stats['net_profit'],
                'expected_balance'    => $expected,
                'actual_balance'      => $actualBalance,
                'difference'          => $diff,
                'status'              => 'closed',
                'closed_by'           => $userId ?? auth()->id(),
                'closed_at'           => now(),
                'notes'               => $notes,
            ]);
            return ['success' => true, 'message' => 'تم إغلاق الجلسة', 'session' => $session->fresh()];
        } catch (Throwable $e) {
            Log::error('DailySessionService::close failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function computeStats(string $date): array
    {
        $sales = \App\Models\Sale::whereDate('sale_date', $date)->get();
        $expenses = (float) \App\Models\Expense::whereDate('expense_date', $date)->sum('amount');
        $debtPayments = (float) \App\Models\DebtPayment::whereDate('payment_date', $date)->sum('amount');
        $purchases = (float) \App\Models\Purchase::whereDate('purchase_date', $date)->sum('paid_amount');

        $totalSales   = (float) $sales->sum('final_amount');
        $totalCash    = (float) $sales->where('payment_method', 'cash')->sum('paid_amount');
        $totalCredit  = (float) $sales->where('payment_method', 'credit')->sum('final_amount') - (float) $sales->where('payment_method', 'credit')->sum('paid_amount');
        $totalTransfers = (float) $sales->where('payment_method', 'transfer')->sum('paid_amount');

        $revenue  = $totalSales;
        $cogs     = (float) \App\Models\SaleItem::whereHas('sale', fn($q) => $q->whereDate('sale_date', $date))->sum('cogs_amount');
        $netProfit = $revenue - $cogs - $expenses;

        return [
            'total_sales'         => $totalSales,
            'total_cash'          => $totalCash,
            'total_credit'        => $totalCredit,
            'total_transfers'     => $totalTransfers,
            'total_expenses'      => $expenses,
            'total_debt_payments' => $debtPayments,
            'total_purchases'     => $purchases,
            'net_profit'          => $netProfit,
        ];
    }
}
