<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ExpenseService — operational expense recording.
 * Each expense:
 *  - Auto journal entry: Dr Operating Expense / Cr Cash
 *  - Record financial outflow
 */
class ExpenseService
{
    public function __construct(
        protected AccountingService $accounting,
        protected FinancialTransactionService $financial,
    ) {}

    public function create(array $data): array
    {
        try {
            $expense = DB::transaction(function () use ($data) {
                $expense = Expense::create([
                    'category'       => $data['category'],
                    'amount'         => (float) $data['amount'],
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'wallet_type'    => $data['wallet_type'] ?? null,
                    'expense_date'   => $data['expense_date'] ?? now()->format('Y-m-d'),
                    'description'    => $data['description'] ?? null,
                    'created_by'     => $data['created_by'] ?? auth()->id(),
                ]);

                $this->financial->record([
                    'trans_date'     => $expense->expense_date,
                    'direction'      => 'out',
                    'amount'         => $expense->amount,
                    'payment_method' => $expense->payment_method,
                    'wallet_type'    => $expense->wallet_type,
                    'ref_type'       => 'expense',
                    'ref_id'         => $expense->id,
                    'notes'          => 'مصروف: ' . $expense->category,
                    'created_by'     => $expense->created_by,
                ]);

                $this->accounting->postExpenseEntry($expense->id, (float) $expense->amount, $expense->expense_date, $expense->created_by);

                return $expense;
            });
            return ['success' => true, 'message' => 'تم حفظ المصروف', 'expense' => $expense];
        } catch (Throwable $e) {
            Log::error('ExpenseService::create failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function delete(int $id): array
    {
        $expense = Expense::find($id);
        if (! $expense) return ['success' => false, 'message' => 'غير موجود'];
        $expense->delete();
        return ['success' => true, 'message' => 'تم الحذف'];
    }
}
