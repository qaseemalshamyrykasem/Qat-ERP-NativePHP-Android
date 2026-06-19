<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AccountingService — double-entry accounting engine.
 *
 * Ported from legacy AccountingService.php but reorganized around
 * Eloquent + DB transactions with proper locking and strict balance checks.
 *
 * Account code conventions (Yemeni Qat trader COA):
 *  1101 Cash (main till)              1103 Wallets (legacy)
 *  1201 Receivables (credit customers)
 *  1301 Main Inventory                1302 Agent Inventory
 *  2101 Payables (credit suppliers)
 *  3000 Equity                        4000 Revenue
 *  4100 Sales Revenue                 5100 COGS
 *  5200 Operating Expenses
 */
class AccountingService
{
    public const ACC_CASH             = '1101';
    public const ACC_WALLET           = '1103';
    public const ACC_RECEIVABLES      = '1201';
    public const ACC_MAIN_INVENTORY   = '1301';
    public const ACC_AGENT_INVENTORY  = '1302';
    public const ACC_PAYABLES         = '2101';
    public const ACC_EQUITY           = '3000';
    public const ACC_REVENUE          = '4000';
    public const ACC_SALES            = '4100';
    public const ACC_COGS             = '5100';
    public const ACC_EXPENSES         = '5200';

    public function __construct(
        protected SequenceService $sequences,
    ) {}

    /**
     * Create a balanced journal entry with multiple lines.
     *
     * @param array{entry_date:string,description:?string,reference_type:?string,reference_id:?int,created_by:?int} $header
     * @param array<int,array{account_code:string,debit:float,credit:float,description:?string,entity_type:?string,entity_id:?int}> $lines
     */
    public function postJournalEntry(array $header, array $lines): JournalEntry
    {
        // Validate balanced: total debit == total credit
        $totalDebit  = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));
        if (bccomp((string) $totalDebit, (string) $totalCredit, 2) !== 0) {
            throw new \DomainException("Unbalanced journal entry: debit {$totalDebit} vs credit {$totalCredit}");
        }

        return DB::transaction(function () use ($header, $lines, $totalDebit, $totalCredit) {
            $entryNo = $header['entry_no'] ?? $this->sequences->nextDated('journal_entry', 'JE', $header['entry_date'] ?? null);

            $entry = JournalEntry::create([
                'entry_no'       => $entryNo,
                'entry_date'     => $header['entry_date'] ?? now()->format('Y-m-d'),
                'description'    => $header['description'] ?? null,
                'reference_type' => $header['reference_type'] ?? null,
                'reference_id'   => $header['reference_id'] ?? null,
                'total_debit'    => $totalDebit,
                'total_credit'   => $totalCredit,
                'status'         => 'posted',
                'created_by'     => $header['created_by'] ?? auth()->id(),
            ]);

            $accountCache = [];
            foreach ($lines as $line) {
                $code = $line['account_code'];
                if (! isset($accountCache[$code])) {
                    $accountCache[$code] = ChartOfAccount::where('code', $code)->first()
                        ?? throw new \DomainException("Account code {$code} not found in chart of accounts");
                }
                $account = $accountCache[$code];

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $account->id,
                    'debit'            => $line['debit'] ?? 0,
                    'credit'           => $line['credit'] ?? 0,
                    'description'      => $line['description'] ?? null,
                    'entity_type'      => $line['entity_type'] ?? null,
                    'entity_id'        => $line['entity_id'] ?? null,
                ]);

                // Update current_balance per account
                $this->applyAccountBalance($account, (float) ($line['debit'] ?? 0), (float) ($line['credit'] ?? 0));
            }

            return $entry;
        });
    }

    private function applyAccountBalance(ChartOfAccount $account, float $debit, float $credit): void
    {
        // Debit accounts: balance increases on debit, decreases on credit
        // Credit accounts: balance increases on credit, decreases on debit
        if ($account->balance_direction === 'debit') {
            $account->increment('current_balance', $debit - $credit);
        } else {
            $account->increment('current_balance', $credit - $debit);
        }
    }

    // ===== Convenience: pre-built entry helpers =====

    /**
     * Record a cash sale:
     *   Dr Cash           = final_amount
     *   Cr Sales Revenue  = final_amount
     *   Dr COGS           = sum(cogs_amount)
     *   Cr Main Inventory = sum(cogs_amount)
     */
    public function postSaleEntry(int $saleId, float $finalAmount, float $cogsTotal, ?string $saleDate = null, ?int $userId = null): JournalEntry
    {
        $date = $saleDate ?? now()->format('Y-m-d');
        $lines = [
            ['account_code' => self::ACC_CASH,          'debit' => $finalAmount, 'credit' => 0, 'description' => 'إيراد بيع نقدي'],
            ['account_code' => self::ACC_SALES,         'debit' => 0, 'credit' => $finalAmount, 'description' => 'إيراد مبيعات'],
        ];

        if ($cogsTotal > 0) {
            $lines[] = ['account_code' => self::ACC_COGS,            'debit' => $cogsTotal, 'credit' => 0, 'description' => 'تكلفة البضاعة المباعة'];
            $lines[] = ['account_code' => self::ACC_MAIN_INVENTORY,  'debit' => 0, 'credit' => $cogsTotal, 'description' => 'إخراج بضاعة من المخزون'];
        }

        return $this->postJournalEntry([
            'entry_date'     => $date,
            'description'    => 'قيد بيع رقم #' . $saleId,
            'reference_type' => 'sale',
            'reference_id'   => $saleId,
            'created_by'     => $userId,
        ], $lines);
    }

    /**
     * Record a credit sale:
     *   Dr Receivables    = final_amount
     *   Cr Sales Revenue  = final_amount
     *   Dr COGS / Cr Inventory
     */
    public function postCreditSaleEntry(int $saleId, float $finalAmount, float $cogsTotal, int $customerId, ?string $saleDate = null, ?int $userId = null): JournalEntry
    {
        $date = $saleDate ?? now()->format('Y-m-d');
        $lines = [
            ['account_code' => self::ACC_RECEIVABLES, 'debit' => $finalAmount, 'credit' => 0, 'description' => 'عملاء آجلين', 'entity_type' => 'customer', 'entity_id' => $customerId],
            ['account_code' => self::ACC_SALES,       'debit' => 0, 'credit' => $finalAmount, 'description' => 'إيراد مبيعات آجلة'],
        ];
        if ($cogsTotal > 0) {
            $lines[] = ['account_code' => self::ACC_COGS,           'debit' => $cogsTotal, 'credit' => 0, 'description' => 'تكلفة البضاعة المباعة'];
            $lines[] = ['account_code' => self::ACC_MAIN_INVENTORY, 'debit' => 0, 'credit' => $cogsTotal, 'description' => 'إخراج بضاعة'];
        }
        return $this->postJournalEntry([
            'entry_date' => $date, 'description' => 'قيد بيع آجل #' . $saleId,
            'reference_type' => 'sale', 'reference_id' => $saleId, 'created_by' => $userId,
        ], $lines);
    }

    /**
     * Record a purchase of stock (cash):
     *   Dr Main Inventory  = total
     *   Cr Cash            = paid
     *   Cr Payables        = total - paid (if any credit portion)
     */
    public function postPurchaseEntry(int $purchaseId, float $total, float $paid, int $supplierId, ?string $date = null, ?int $userId = null): JournalEntry
    {
        $date = $date ?? now()->format('Y-m-d');
        $lines = [
            ['account_code' => self::ACC_MAIN_INVENTORY, 'debit' => $total, 'credit' => 0, 'description' => 'شراء بضاعة'],
        ];
        if ($paid > 0) {
            $lines[] = ['account_code' => self::ACC_CASH, 'debit' => 0, 'credit' => $paid, 'description' => 'سداد نقدي'];
        }
        $credit = round($total - $paid, 2);
        if ($credit > 0) {
            $lines[] = ['account_code' => self::ACC_PAYABLES, 'debit' => 0, 'credit' => $credit, 'description' => 'موردين آجلين', 'entity_type' => 'supplier', 'entity_id' => $supplierId];
        }
        return $this->postJournalEntry([
            'entry_date' => $date, 'description' => 'قيد شراء #' . $purchaseId,
            'reference_type' => 'purchase', 'reference_id' => $purchaseId, 'created_by' => $userId,
        ], $lines);
    }

    /**
     * Record a distribution to an agent:
     *   Dr Agent Inventory  = total
     *   Cr Main Inventory   = total
     */
    public function postDistributionEntry(int $distributionId, float $total, int $agentId, ?string $date = null, ?int $userId = null): JournalEntry
    {
        $date = $date ?? now()->format('Y-m-d');
        return $this->postJournalEntry([
            'entry_date' => $date, 'description' => 'قيد توزيع #' . $distributionId,
            'reference_type' => 'distribution', 'reference_id' => $distributionId, 'created_by' => $userId,
        ], [
            ['account_code' => self::ACC_AGENT_INVENTORY, 'debit' => $total, 'credit' => 0, 'description' => 'صرف بضاعة للوكيل', 'entity_type' => 'agent', 'entity_id' => $agentId],
            ['account_code' => self::ACC_MAIN_INVENTORY,  'debit' => 0, 'credit' => $total, 'description' => 'إخراج من المخزون الرئيسي'],
        ]);
    }

    /**
     * Record an expense:
     *   Dr Operating Expenses  = amount
     *   Cr Cash / Wallet
     */
    public function postExpenseEntry(int $expenseId, float $amount, ?string $date = null, ?int $userId = null): JournalEntry
    {
        $date = $date ?? now()->format('Y-m-d');
        return $this->postJournalEntry([
            'entry_date' => $date, 'description' => 'قيد مصروف #' . $expenseId,
            'reference_type' => 'expense', 'reference_id' => $expenseId, 'created_by' => $userId,
        ], [
            ['account_code' => self::ACC_EXPENSES, 'debit' => $amount, 'credit' => 0, 'description' => 'مصروف تشغيلي'],
            ['account_code' => self::ACC_CASH,     'debit' => 0, 'credit' => $amount, 'description' => 'سداد نقدي'],
        ]);
    }

    /**
     * Void a journal entry (reverses the balance effect).
     */
    public function voidEntry(JournalEntry $entry, ?string $reason = null): JournalEntry
    {
        return DB::transaction(function () use ($entry, $reason) {
            foreach ($entry->lines as $line) {
                $account = $line->account;
                if ($account->balance_direction === 'debit') {
                    $account->decrement('current_balance', (float) $line->debit - (float) $line->credit);
                } else {
                    $account->decrement('current_balance', (float) $line->credit - (float) $line->debit);
                }
            }
            $entry->update(['status' => 'voided', 'description' => $entry->description . ' [VOID: ' . $reason . ']']);
            return $entry->fresh();
        });
    }

    // ===== Reports =====

    /**
     * Trial Balance: list all active accounts with their net debit/credit balance.
     */
    public function trialBalance(?\DateTime $asOf = null): array
    {
        $asOf = $asOf ?? now();
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        $rows = [];
        $totalDebit = $totalCredit = 0;

        foreach ($accounts as $acc) {
            $balance = (float) $acc->current_balance;
            if ($acc->balance_direction === 'credit') {
                $balance = -$balance;
            }
            if ($balance == 0) continue;

            $isDebit = $balance > 0;
            $debit  = $isDebit ? abs($balance) : 0;
            $credit = $isDebit ? 0 : abs($balance);

            $rows[] = [
                'code'        => $acc->code,
                'name'        => $acc->name,
                'type'        => $acc->account_type,
                'debit'       => $debit,
                'credit'      => $credit,
            ];
            $totalDebit  += $debit;
            $totalCredit += $credit;
        }

        return [
            'as_of'       => $asOf->format('Y-m-d'),
            'rows'        => $rows,
            'total_debit' => $totalDebit,
            'total_credit'=> $totalCredit,
            'balanced'    => bccomp((string) $totalDebit, (string) $totalCredit, 2) === 0,
        ];
    }

    /**
     * Income Statement: revenue - COGS - operating_expenses = net profit
     */
    public function incomeStatement(?\DateTime $from = null, ?\DateTime $to = null): array
    {
        $to = $to ?? now();
        $from = $from ?? now()->startOfYear();

        $revenue   = (float) ChartOfAccount::where('account_type', 'revenue')->sum('current_balance');
        $cogs      = (float) ChartOfAccount::where('code', self::ACC_COGS)->value('current_balance');
        $expenses  = (float) ChartOfAccount::where('code', self::ACC_EXPENSES)->value('current_balance');

        $grossProfit = $revenue - $cogs;
        $netProfit   = $grossProfit - $expenses;

        return [
            'from'           => $from->format('Y-m-d'),
            'to'             => $to->format('Y-m-d'),
            'revenue'        => $revenue,
            'cogs'           => $cogs,
            'gross_profit'   => $grossProfit,
            'operating'      => $expenses,
            'net_profit'     => $netProfit,
        ];
    }

    /**
     * Balance Sheet: assets, liabilities, equity.
     */
    public function balanceSheet(?\DateTime $asOf = null): array
    {
        $asOf = $asOf ?? now();
        $assets     = ChartOfAccount::where('account_type', 'asset')->sum('current_balance');
        $liabilities= ChartOfAccount::where('account_type', 'liability')->sum('current_balance');
        $equity     = ChartOfAccount::where('account_type', 'equity')->sum('current_balance');
        $netProfit  = $this->incomeStatement() ['net_profit'];

        return [
            'as_of'        => $asOf->format('Y-m-d'),
            'assets'       => (float) $assets,
            'liabilities'  => (float) $liabilities,
            'equity'       => (float) $equity,
            'net_profit'   => $netProfit,
            'total_liab_equity' => (float) ($liabilities + $equity + $netProfit),
            'balanced'     => bccomp((string) $assets, (string) ($liabilities + $equity + $netProfit), 2) === 0,
        ];
    }

    /**
     * General Ledger: per-account line listing.
     */
    public function generalLedger(?string $from = null, ?string $to = null, ?int $accountId = null): array
    {
        $query = JournalEntryLine::with(['journalEntry', 'account'])
            ->whereHas('journalEntry', function ($q) use ($from, $to) {
                $q->where('status', 'posted');
                if ($from) $q->where('entry_date', '>=', $from);
                if ($to)   $q->where('entry_date', '<=', $to);
            });
        if ($accountId) $query->where('account_id', $accountId);

        $lines = $query->orderBy('journal_entry_id')->get();
        $rows = [];
        $running = [];

        foreach ($lines as $line) {
            $accId = $line->account_id;
            if (! isset($running[$accId])) $running[$accId] = 0;
            $running[$accId] += (float) $line->debit - (float) $line->credit;

            $rows[] = [
                'entry_no'   => $line->journalEntry->entry_no,
                'entry_date'  => $line->journalEntry->entry_date->format('Y-m-d'),
                'account_code'=> $line->account->code,
                'account_name'=> $line->account->name,
                'description' => $line->description,
                'debit'       => (float) $line->debit,
                'credit'      => (float) $line->credit,
                'balance'     => $running[$accId],
            ];
        }
        return $rows;
    }
}
