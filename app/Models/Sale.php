<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sale extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'invoice_no', 'agent_id', 'customer_id', 'sale_date',
        'total_amount', 'discount_amount', 'final_amount', 'paid_amount',
        'payment_method', 'wallet_type', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date'        => 'date',
            'total_amount'     => 'decimal:2',
            'discount_amount'  => 'decimal:2',
            'final_amount'     => 'decimal:2',
            'paid_amount'      => 'decimal:2',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'entity');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'reference_id')
            ->where('reference_type', 'sale');
    }
}
