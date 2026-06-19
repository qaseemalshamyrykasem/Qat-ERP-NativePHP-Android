<?php

namespace App\Services;

use App\Models\AgentStock;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * StockService — operations on main & agent stock.
 */
class StockService
{
    public function adjustStock(int $productId, float $newQuantity, ?string $reason = null): array
    {
        $product = Product::lockForUpdate()->find($productId);
        if (! $product) return ['success' => false, 'message' => 'المنتج غير موجود'];

        $diff = $newQuantity - (float) $product->quantity;
        try {
            DB::transaction(function () use ($product, $newQuantity, $diff, $reason) {
                $product->update(['quantity' => $newQuantity]);
                StockMovement::create([
                    'product_id'    => $product->id,
                    'quantity'      => $diff,
                    'movement_type' => 'adjust',
                    'user_id'       => auth()->id(),
                    'notes'         => $reason ?? 'تسوية مخزون',
                ]);
            });
            return ['success' => true, 'message' => 'تم تعديل المخزون'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function restock(int $productId, float $quantity, ?float $unitCost = null, ?string $reason = null): array
    {
        if ($quantity <= 0) return ['success' => false, 'message' => 'الكمية غير صحيحة'];

        $product = Product::lockForUpdate()->find($productId);
        if (! $product) return ['success' => false, 'message' => 'المنتج غير موجود'];

        try {
            DB::transaction(function () use ($product, $quantity, $unitCost, $reason) {
                if ($unitCost && $unitCost > 0) {
                    $product->recalcWeightedAverageCost($quantity, $unitCost);
                }
                $product->increment('quantity', $quantity);
                StockMovement::create([
                    'product_id'    => $product->id,
                    'quantity'      => $quantity,
                    'movement_type' => 'restock',
                    'user_id'       => auth()->id(),
                    'notes'         => $reason ?? 'تزويد مخزون',
                ]);
            });
            return ['success' => true, 'message' => 'تم التزويد بنجاح'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function agentStockSummary(int $agentId): array
    {
        $rows = AgentStock::with('product')
            ->where('agent_id', $agentId)
            ->where('quantity', '>', 0)
            ->get();
        return ['success' => true, 'rows' => $rows];
    }

    public function lowStockReport(): array
    {
        $products = Product::whereColumn('quantity', '<=', 'min_quantity')
            ->where('status', true)
            ->get();
        return ['success' => true, 'rows' => $products];
    }
}
