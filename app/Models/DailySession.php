<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySession extends Model
{
    protected $fillable = [
        'session_date', 'opening_balance', 'total_sales', 'total_cash',
        'total_credit', 'total_transfers', 'total_expenses', 'total_debt_payments',
        'net_profit', 'expected_balance', 'actual_balance', 'difference',
        'status', 'notes', 'opened_by', 'opened_at', 'closed_by', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'session_date'        => 'date',
            'opening_balance'     => 'decimal:2',
            'total_sales'         => 'decimal:2',
            'total_cash'          => 'decimal:2',
            'total_credit'        => 'decimal:2',
            'total_transfers'     => 'decimal:2',
            'total_expenses'      => 'decimal:2',
            'total_debt_payments' => 'decimal:2',
            'net_profit'          => 'decimal:2',
            'expected_balance'    => 'decimal:2',
            'actual_balance'      => 'decimal:2',
            'difference'          => 'decimal:2',
            'opened_at'           => 'datetime',
            'closed_at'           => 'datetime',
        ];
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
