<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierDebtPayment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'supplier_debt_id', 'amount', 'payment_date', 'payment_method',
        'wallet_type', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function supplierDebt(): BelongsTo
    {
        return $this->belongsTo(SupplierDebt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
