<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * FinancialTransactionService — single source of truth for all cash/wallet movements.
 * Migrated from legacy FinancialTransactionService.php
 */
class FinancialTransactionService
{
    public function record(array $data): ?FinancialTransaction
    {
        try {
            return FinancialTransaction::create([
                'trans_date'     => $data['trans_date'] ?? now()->format('Y-m-d'),
                'direction'      => $data['direction'],
                'amount'         => (float) $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'wallet_type'    => $data['wallet_type'] ?? null,
                'ref_type'       => $data['ref_type'],
                'ref_id'         => $data['ref_id'] ?? null,
                'account_id'     => $data['account_id'] ?? null,
                'journal_entry_id'=> $data['journal_entry_id'] ?? null,
                'currency_id'    => $data['currency_id'] ?? null,
                'exchange_rate'  => $data['exchange_rate'] ?? 1.0,
                'entity_type'    => $data['entity_type'] ?? null,
                'entity_id'      => $data['entity_id'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'created_by'     => $data['created_by'] ?? auth()->id(),
            ]);
        } catch (\Throwable $e) {
            Log::error('FinancialTransactionService::record failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Aggregate cash position by date range.
     */
    public function cashPosition(?\DateTime $from = null, ?\DateTime $to = null): array
    {
        $q = FinancialTransaction::query();
        if ($from) $q->where('trans_date', '>=', $from->format('Y-m-d'));
        if ($to)   $q->where('trans_date', '<=', $to->format('Y-m-d'));

        return [
            'in'  => (float) (clone $q)->where('direction', 'in')->sum('amount'),
            'out' => (float) (clone $q)->where('direction', 'out')->sum('amount'),
        ];
    }
}
