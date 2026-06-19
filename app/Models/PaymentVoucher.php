<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentVoucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'voucher_no', 'voucher_date', 'account_id', 'amount',
        'payment_method', 'wallet_type', 'supplier_id', 'description',
        'journal_entry_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'voucher_date' => 'date',
            'amount'       => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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
