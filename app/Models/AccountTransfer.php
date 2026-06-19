<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransfer extends Model
{
    protected $fillable = [
        'transfer_no', 'from_account_id', 'to_account_id',
        'from_currency_id', 'to_currency_id', 'amount', 'converted_amount',
        'exchange_rate', 'transfer_date', 'description', 'status',
        'journal_entry_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'converted_amount' => 'decimal:2',
            'exchange_rate'    => 'decimal:6',
            'transfer_date'    => 'date',
        ];
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'to_account_id');
    }

    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
