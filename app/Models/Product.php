<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'type', 'buy_price', 'weighted_average_cost',
        'sell_price', 'quantity', 'unit', 'min_quantity',
        'supplier_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'buy_price'              => 'decimal:2',
            'weighted_average_cost'  => 'decimal:4',
            'sell_price'             => 'decimal:2',
            'quantity'               => 'decimal:2',
            'min_quantity'           => 'decimal:2',
            'status'                 => 'boolean',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function dailyPrices(): HasMany
    {
        return $this->hasMany(DailyPrice::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function distributionItems(): HasMany
    {
        return $this->hasMany(DistributionItem::class);
    }

    public function agentStock(): HasMany
    {
        return $this->hasMany(AgentStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Update weighted average cost after a new purchase batch.
     * WAC = (current_stock_value + new_purchase_value) / (current_qty + new_qty)
     */
    public function recalcWeightedAverageCost(float $addedQty, float $addedUnitCost): void
    {
        $currentValue = (float) $this->quantity * (float) $this->weighted_average_cost;
        $addedValue   = $addedQty * $addedUnitCost;
        $newQty       = (float) $this->quantity + $addedQty;

        $this->weighted_average_cost = $newQty > 0
            ? round(($currentValue + $addedValue) / $newQty, 4)
            : 0;

        $this->save();
    }
}
