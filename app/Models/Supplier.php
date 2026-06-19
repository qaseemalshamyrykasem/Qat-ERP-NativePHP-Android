<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'address', 'specialization', 'notes',
        'balance', 'total_purchases', 'total_paid', 'total_remaining', 'status',
    ];

    protected function casts(): array
    {
        return [
            'balance'         => 'decimal:2',
            'total_purchases' => 'decimal:2',
            'total_paid'      => 'decimal:2',
            'total_remaining' => 'decimal:2',
            'status'          => 'boolean',
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function supplierDebts(): HasMany
    {
        return $this->hasMany(SupplierDebt::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function paymentVouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class);
    }

    /**
     * Recompute supplier balances from source-of-truth (purchases & payments).
     * Replaces legacy incremental update pattern.
     */
    public function recomputeBalances(): static
    {
        $this->total_purchases = (float) $this->purchases()->sum('total_amount');
        $this->total_paid      = (float) $this->purchases()->sum('paid_amount')
                                + (float) $this->supplierDebts()->sum('paid_amount');
        $this->total_remaining = $this->total_purchases - $this->total_paid;
        $this->balance         = -$this->total_remaining; // negative = supplier owed
        $this->save();
        return $this;
    }
}
