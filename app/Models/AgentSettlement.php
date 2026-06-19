<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentSettlement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'settlement_no', 'agent_id', 'settlement_date', 'total_sales',
        'cash_sales', 'credit_sales', 'transfer_sales', 'debt_payments',
        'expenses', 'commission_amount', 'shaqa_amount', 'discounts',
        'shortages', 'net_due', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'settlement_date'   => 'date',
            'total_sales'       => 'decimal:2',
            'cash_sales'        => 'decimal:2',
            'credit_sales'      => 'decimal:2',
            'transfer_sales'    => 'decimal:2',
            'debt_payments'     => 'decimal:2',
            'expenses'          => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'shaqa_amount'      => 'decimal:2',
            'discounts'         => 'decimal:2',
            'shortages'         => 'decimal:2',
            'net_due'           => 'decimal:2',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
