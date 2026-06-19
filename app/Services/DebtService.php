<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Debt;
use App\Models\DebtPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * DebtService — customer debt tracking & payment registration.
 *
 * For each payment:
 *  - Apply payment to debt(s), FIFO
 *  - Update debt status
 *  - Recompute customer balances from source (not incremental)
 *  - Record financial transaction
 */
class DebtService
{
    public function __construct(
        protected FinancialTransactionService $financial,
        protected AccountingService $accounting,
    ) {}

    public function registerPayment(int $debtId, float $amount, string $method = 'cash', ?string $wallet = null, ?string $date = null, ?int $userId = null): array
    {
        $debt = Debt::with('customer')->lockForUpdate()->find($debtId);
        if (! $debt) {
            return ['success' => false, 'message' => 'الدين غير موجود'];
        }
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'المبلغ غير صحيح'];
        }
        if ($amount > (float) $debt->remaining_amount) {
            $amount = (float) $debt->remaining_amount;
        }

        try {
            DB::transaction(function () use ($debt, $amount, $method, $wallet, $date, $userId) {
                $debt->increment('paid_amount', $amount);
                $debt->decrement('remaining_amount', $amount);
                $debt->update([
                    'status' => $debt->remaining_amount <= 0 ? 'paid' : 'partial',
                ]);

                DebtPayment::create([
                    'debt_id'        => $debt->id,
                    'amount'         => $amount,
                    'payment_date'   => $date ?? now()->format('Y-m-d'),
                    'payment_method' => $method,
                    'wallet_type'    => $wallet,
                    'created_by'     => $userId ?? auth()->id(),
                ]);

                if ($debt->customer_id) {
                    Customer::find($debt->customer_id)?->recomputeBalances();
                }

                $this->financial->record([
                    'trans_date'     => $date ?? now()->format('Y-m-d'),
                    'direction'      => 'in',
                    'amount'         => $amount,
                    'payment_method' => $method,
                    'wallet_type'    => $wallet,
                    'ref_type'       => 'debt_payment',
                    'ref_id'         => $debt->id,
                    'entity_type'    => 'customer',
                    'entity_id'      => $debt->customer_id,
                    'notes'          => 'سداد دين',
                    'created_by'     => $userId ?? auth()->id(),
                ]);
            });
            return ['success' => true, 'message' => 'تم تسجيل السداد بنجاح'];
        } catch (Throwable $e) {
            Log::error('DebtService::registerPayment failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function getPaymentHistory(int $debtId): array
    {
        $payments = DebtPayment::where('debt_id', $debtId)->orderByDesc('payment_date')->get();
        return ['success' => true, 'payments' => $payments];
    }

    /**
     * Mark overdue debts (status = 'overdue' where due_date < today and remaining > 0).
     */
    public function markOverdue(): int
    {
        return Debt::where('status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->where('remaining_amount', '>', 0)
            ->update(['status' => 'overdue']);
    }
}
