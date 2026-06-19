<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'distribution_id', 'product_id', 'description', 'quality',
        'quantity', 'unit', 'unit_price', 'total_price', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'    => 'decimal:2',
            'unit_price'  => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
