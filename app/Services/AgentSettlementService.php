<?php

namespace App\Services;

use App\Models\AgentSettlement;
use App\Models\Debt;
use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * AgentSettlementService — daily settlement with agents.
 *
 * Server-side recompute of all monetary totals (legacy 4.3 fix):
 *  - total sales (cash / credit / transfer) from sales table
 *  - debt payments collected today
 *  - expenses incurred by agent today
 *  - commission + shaqa + discounts + shortages entered manually by manager
 *  - net_due = total_sales - debt_payments - expenses - commission - shaqa - discounts - shortages
 */
class AgentSettlementService
{
    public function __construct(
        protected SequenceService $sequences,
        protected FinancialTransactionService $financial,
    ) {}

    public function calculate(int $agentId, string $date): array
    {
        $sales = Sale::where('agent_id', $agentId)->whereDate('sale_date', $date)->get();
        $totalSales = (float) $sales->sum('final_amount');
        $cashSales = (float) $sales->where('payment_method', 'cash')->sum('final_amount');
        $creditSales = (float) $sales->where('payment_method', 'credit')->sum('final_amount');
        $transferSales = (float) $sales->where('payment_method', 'transfer')->sum('final_amount');

        $debtPayments = (float) Debt::where('agent_id', $agentId)
            ->whereHas('payments', function ($q) use ($date) {
                $q->whereDate('payment_date', $date);
            })->sum('paid_amount');

        $expenses = (float) Expense::whereDate('expense_date', $date)->sum('amount');

        return [
            'agent_id'       => $agentId,
            'settlement_date'=> $date,
            'total_sales'    => $totalSales,
            'cash_sales'     => $cashSales,
            'credit_sales'   => $creditSales,
            'transfer_sales' => $transferSales,
            'debt_payments'  => $debtPayments,
            'expenses'       => $expenses,
        ];
    }

    public function save(array $data): array
    {
        $calc = $this->calculate((int) $data['agent_id'], $data['settlement_date']);

        // Merge manual inputs
        $commission = (float) ($data['commission_amount'] ?? 0);
        $shaqa = (float) ($data['shaqa_amount'] ?? 0);
        $discounts = (float) ($data['discounts'] ?? 0);
        $shortages = (float) ($data['shortages'] ?? 0);

        $netDue = $calc['total_sales']
                - $calc['debt_payments']
                - $calc['expenses']
                - $commission
                - $shaqa
                - $discounts
                - $shortages;

        try {
            $settlement = DB::transaction(function () use ($calc, $data, $commission, $shaqa, $discounts, $shortages, $netDue) {
                $no = $this->sequences->next('agent_settlement', 'STL', 6);

                return AgentSettlement::updateOrCreate(
                    ['agent_id' => $calc['agent_id'], 'settlement_date' => $calc['settlement_date']],
                    [
                        'settlement_no'    => $no,
                        'total_sales'      => $calc['total_sales'],
                        'cash_sales'       => $calc['cash_sales'],
                        'credit_sales'     => $calc['credit_sales'],
                        'transfer_sales'   => $calc['transfer_sales'],
                        'debt_payments'    => $calc['debt_payments'],
                        'expenses'         => $calc['expenses'],
                        'commission_amount'=> $commission,
                        'shaqa_amount'     => $shaqa,
                        'discounts'        => $discounts,
                        'shortages'        => $shortages,
                        'net_due'          => $netDue,
                        'notes'            => $data['notes'] ?? null,
                        'created_by'       => $data['created_by'] ?? auth()->id(),
                    ]
                );
            });

            return ['success' => true, 'message' => 'تم حفظ التحاسب', 'settlement' => $settlement];
        } catch (Throwable $e) {
            Log::error('AgentSettlementService::save failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function delete(int $id): array
    {
        $s = AgentSettlement::find($id);
        if (! $s) return ['success' => false, 'message' => 'غير موجود'];
        $s->delete();
        return ['success' => true, 'message' => 'تم الحذف'];
    }
}
