<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sale_id', 'product_id', 'description', 'quality',
        'quantity', 'unit', 'unit_price', 'total_price',
        'cogs_amount', 'weighted_average_cost_at_sale', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'                      => 'decimal:2',
            'unit_price'                    => 'decimal:2',
            'total_price'                   => 'decimal:2',
            'cogs_amount'                   => 'decimal:2',
            'weighted_average_cost_at_sale' => 'decimal:4',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
