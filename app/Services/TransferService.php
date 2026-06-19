<?php

namespace App\Services;

use App\Models\AccountTransfer;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * TransferService — transfer funds between accounts (with optional currency conversion).
 * Auto-creates a balanced journal entry.
 */
class TransferService
{
    public function __construct(
        protected SequenceService $sequences,
        protected AccountingService $accounting,
        protected FinancialTransactionService $financial,
    ) {}

    public function create(array $data): array
    {
        $from = ChartOfAccount::find($data['from_account_id']);
        $to = ChartOfAccount::find($data['to_account_id']);
        if (! $from || ! $to) return ['success' => false, 'message' => 'الحسابات غير موجودة'];
        if ($from->id === $to->id) return ['success' => false, 'message' => 'لا يمكن التحويل لنفس الحساب'];

        $amount = (float) $data['amount'];
        if ($amount <= 0) return ['success' => false, 'message' => 'المبلغ غير صحيح'];

        try {
            $transfer = DB::transaction(function () use ($from, $to, $data, $amount) {
                $no = $this->sequences->next('transfer', 'TRF', 6);
                $converted = null;
                if (! empty($data['exchange_rate']) && $data['exchange_rate'] != 1) {
                    $converted = round($amount * (float) $data['exchange_rate'], 2);
                }

                $transfer = AccountTransfer::create([
                    'transfer_no'      => $no,
                    'from_account_id'  => $from->id,
                    'to_account_id'    => $to->id,
                    'from_currency_id' => $data['from_currency_id'] ?? null,
                    'to_currency_id'   => $data['to_currency_id'] ?? null,
                    'amount'           => $amount,
                    'converted_amount' => $converted,
                    'exchange_rate'    => $data['exchange_rate'] ?? 1,
                    'transfer_date'    => $data['transfer_date'] ?? now()->format('Y-m-d'),
                    'description'      => $data['description'] ?? null,
                    'status'           => 'completed',
                    'created_by'       => $data['created_by'] ?? auth()->id(),
                ]);

                // Post journal entry: Dr to_account / Cr from_account
                $entry = $this->accounting->postJournalEntry([
                    'entry_date'     => $transfer->transfer_date,
                    'description'    => 'تحويل بين حسابات ' . $no,
                    'reference_type' => 'transfer',
                    'reference_id'   => $transfer->id,
                    'created_by'     => $transfer->created_by,
                ], [
                    ['account_code' => $to->code,   'debit' => $amount, 'credit' => 0, 'description' => 'إيداع'],
                    ['account_code' => $from->code, 'debit' => 0, 'credit' => $amount, 'description' => 'سحب'],
                ]);

                $transfer->update(['journal_entry_id' => $entry->id]);
                return $transfer;
            });

            return ['success' => true, 'message' => 'تم التحويل', 'transfer' => $transfer];
        } catch (Throwable $e) {
            Log::error('TransferService::create failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function delete(int $id): array
    {
        $t = AccountTransfer::find($id);
        if (! $t) return ['success' => false, 'message' => 'غير موجود'];
        $t->delete();
        return ['success' => true, 'message' => 'تم الحذف'];
    }
}
