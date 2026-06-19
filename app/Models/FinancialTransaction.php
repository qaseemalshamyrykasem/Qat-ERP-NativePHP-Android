<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'trans_date', 'direction', 'amount', 'payment_method', 'wallet_type',
        'ref_type', 'ref_id', 'account_id', 'journal_entry_id',
        'currency_id', 'exchange_rate', 'entity_type', 'entity_id',
        'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'trans_date'    => 'date',
            'amount'        => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'created_at'    => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
