<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyPrice extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'product_id', 'supplier_id', 'price_date', 'buy_price', 'sell_price',
        'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'price_date' => 'date',
            'buy_price'  => 'decimal:2',
            'sell_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
