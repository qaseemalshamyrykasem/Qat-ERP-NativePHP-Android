<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'address', 'total_debt', 'total_paid',
        'remaining', 'last_payment_date', 'status', 'agent_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_debt'        => 'decimal:2',
            'total_paid'        => 'decimal:2',
            'remaining'         => 'decimal:2',
            'last_payment_date' => 'date',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function receiptVouchers(): HasMany
    {
        return $this->hasMany(ReceiptVoucher::class);
    }

    /**
     * Recompute customer balances from source-of-truth (debts & payments).
     */
    public function recomputeBalances(): static
    {
        $this->total_debt = (float) $this->debts()->sum('total_amount');
        $this->total_paid = (float) $this->debts()->sum('paid_amount');
        $this->remaining  = $this->total_debt - $this->total_paid;
        $this->save();
        return $this;
    }
}
