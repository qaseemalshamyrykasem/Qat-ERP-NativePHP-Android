<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierDebt extends Model
{
    protected $fillable = [
        'supplier_id', 'purchase_id', 'total_amount', 'paid_amount',
        'remaining_amount', 'status', 'due_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'     => 'decimal:2',
            'paid_amount'      => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'due_date'         => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierDebtPayment::class);
    }
}
