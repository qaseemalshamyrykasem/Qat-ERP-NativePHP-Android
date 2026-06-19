<?php

namespace App\Services;

use App\Models\PaymentVoucher;
use App\Models\ReceiptVoucher;
use App\Models\Supplier;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * VoucherService — issue receipt (in) and payment (out) vouchers.
 * Both auto-create journal entries against the specified account.
 */
class VoucherService
{
    public function __construct(
        protected SequenceService $sequences,
        protected AccountingService $accounting,
        protected FinancialTransactionService $financial,
    ) {}

    public function addReceipt(array $data): array
    {
        try {
            $receipt = DB::transaction(function () use ($data) {
                $no = $this->sequences->next('receipt_voucher', 'RV', 6);

                $receipt = ReceiptVoucher::create([
                    'voucher_no'    => $no,
                    'voucher_date'  => $data['voucher_date'] ?? now()->format('Y-m-d'),
                    'account_id'    => $data['account_id'],
                    'amount'        => (float) $data['amount'],
                    'payment_method'=> $data['payment_method'] ?? 'cash',
                    'wallet_type'   => $data['wallet_type'] ?? null,
                    'customer_id'   => $data['customer_id'] ?? null,
                    'description'   => $data['description'] ?? null,
                    'created_by'    => $data['created_by'] ?? auth()->id(),
                ]);

                $account = $receipt->account;
                $entry = $this->accounting->postJournalEntry([
                    'entry_date'     => $receipt->voucher_date,
                    'description'    => 'سند قبض ' . $no,
                    'reference_type' => 'receipt_voucher',
                    'reference_id'   => $receipt->id,
                    'created_by'     => $receipt->created_by,
                ], [
                    ['account_code' => AccountingService::ACC_CASH, 'debit' => $receipt->amount, 'credit' => 0, 'description' => 'استلام نقدية'],
                    ['account_code' => $account->code,              'debit' => 0, 'credit' => $receipt->amount, 'description' => 'سند قبض'],
                ]);

                $receipt->update(['journal_entry_id' => $entry->id]);

                $this->financial->record([
                    'trans_date'     => $receipt->voucher_date,
                    'direction'      => 'in',
                    'amount'         => $receipt->amount,
                    'payment_method' => $receipt->payment_method,
                    'wallet_type'    => $receipt->wallet_type,
                    'ref_type'       => 'receipt_voucher',
                    'ref_id'         => $receipt->id,
                    'account_id'     => $account->id,
                    'entity_type'    => $receipt->customer_id ? 'customer' : null,
                    'entity_id'      => $receipt->customer_id,
                    'notes'          => 'سند قبض ' . $no,
                    'created_by'     => $receipt->created_by,
                ]);

                if ($receipt->customer_id) {
                    Customer::find($receipt->customer_id)?->recomputeBalances();
                }

                return $receipt;
            });
            return ['success' => true, 'message' => 'تم حفظ السند', 'receipt' => $receipt];
        } catch (Throwable $e) {
            Log::error('VoucherService::addReceipt failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function addPayment(array $data): array
    {
        try {
            $payment = DB::transaction(function () use ($data) {
                $no = $this->sequences->next('payment_voucher', 'PV', 6);

                $payment = PaymentVoucher::create([
                    'voucher_no'    => $no,
                    'voucher_date'  => $data['voucher_date'] ?? now()->format('Y-m-d'),
                    'account_id'    => $data['account_id'],
                    'amount'        => (float) $data['amount'],
                    'payment_method'=> $data['payment_method'] ?? 'cash',
                    'wallet_type'   => $data['wallet_type'] ?? null,
                    'supplier_id'   => $data['supplier_id'] ?? null,
                    'description'   => $data['description'] ?? null,
                    'created_by'    => $data['created_by'] ?? auth()->id(),
                ]);

                $account = $payment->account;
                $entry = $this->accounting->postJournalEntry([
                    'entry_date'     => $payment->voucher_date,
                    'description'    => 'سند صرف ' . $no,
                    'reference_type' => 'payment_voucher',
                    'reference_id'   => $payment->id,
                    'created_by'     => $payment->created_by,
                ], [
                    ['account_code' => $account->code,              'debit' => $payment->amount, 'credit' => 0, 'description' => 'سند صرف'],
                    ['account_code' => AccountingService::ACC_CASH, 'debit' => 0, 'credit' => $payment->amount, 'description' => 'صرف نقدية'],
                ]);

                $payment->update(['journal_entry_id' => $entry->id]);

                $this->financial->record([
                    'trans_date'     => $payment->voucher_date,
                    'direction'      => 'out',
                    'amount'         => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'wallet_type'    => $payment->wallet_type,
                    'ref_type'       => 'payment_voucher',
                    'ref_id'         => $payment->id,
                    'account_id'     => $account->id,
                    'entity_type'    => $payment->supplier_id ? 'supplier' : null,
                    'entity_id'      => $payment->supplier_id,
                    'notes'          => 'سند صرف ' . $no,
                    'created_by'     => $payment->created_by,
                ]);

                if ($payment->supplier_id) {
                    Supplier::find($payment->supplier_id)?->recomputeBalances();
                }

                return $payment;
            });
            return ['success' => true, 'message' => 'تم حفظ السند', 'payment' => $payment];
        } catch (Throwable $e) {
            Log::error('VoucherService::addPayment failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function deleteReceipt(int $id): array
    {
        $r = ReceiptVoucher::find($id);
        if (! $r) return ['success' => false, 'message' => 'غير موجود'];
        $r->delete();
        return ['success' => true, 'message' => 'تم الحذف'];
    }

    public function deletePayment(int $id): array
    {
        $p = PaymentVoucher::find($id);
        if (! $p) return ['success' => false, 'message' => 'غير موجود'];
        $p->delete();
        return ['success' => true, 'message' => 'تم الحذف'];
    }
}
