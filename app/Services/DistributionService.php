<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentStock;
use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * DistributionService — distribute stock from main inventory to agents.
 *
 * Each distribution:
 *  - Decrement main inventory (products.quantity)
 *  - Increment agent_stock
 *  - Auto journal entry: Dr Agent Inventory / Cr Main Inventory
 *  - StockMovement audit trail (distribute)
 */
class DistributionService
{
    public function __construct(
        protected SequenceService $sequences,
        protected AccountingService $accounting,
    ) {}

    public function addDistribution(array $data): array
    {
        $items = $this->normalizeItems($data['items'] ?? []);
        if (empty($items)) {
            return ['success' => false, 'message' => 'يرجى إضافة عنصر توزيع واحد على الأقل'];
        }

        $agent = Agent::find($data['agent_id'] ?? null);
        if (! $agent) {
            return ['success' => false, 'message' => 'الوكيل غير موجود'];
        }

        // Pre-check stock
        foreach ($items as $item) {
            if (! empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if (! $product || $product->quantity < $item['quantity']) {
                    return ['success' => false, 'message' => "الكمية المطلوبة من [{$product?->name}] غير متوفرة في المخزون الرئيسي"];
                }
            }
        }

        try {
            $distribution = DB::transaction(function () use ($items, $data, $agent) {
                $distNo = $this->sequences->next('distribution', 'DST', 6);
                $totalAmount = array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_price'], $items));

                $distribution = Distribution::create([
                    'distribution_no'   => $distNo,
                    'agent_id'          => $agent->id,
                    'distribution_date' => $data['distribution_date'] ?? now()->format('Y-m-d'),
                    'total_amount'      => $totalAmount,
                    'notes'             => $data['notes'] ?? null,
                    'created_by'        => $data['created_by'] ?? auth()->id(),
                ]);

                foreach ($items as $item) {
                    $lineTotal = round($item['quantity'] * $item['unit_price'], 2);
                    DistributionItem::create([
                        'distribution_id' => $distribution->id,
                        'product_id'      => $item['product_id'] ?? null,
                        'description'     => $item['description'],
                        'quality'         => $item['quality'] ?? null,
                        'quantity'        => $item['quantity'],
                        'unit'            => $item['unit'] ?? 'حزمة',
                        'unit_price'      => $item['unit_price'],
                        'total_price'     => $lineTotal,
                        'notes'           => $item['notes'] ?? null,
                    ]);

                    if (! empty($item['product_id'])) {
                        $product = Product::lockForUpdate()->find($item['product_id']);
                        $product->decrement('quantity', $item['quantity']);

                        AgentStock::updateOrCreate(
                            ['agent_id' => $agent->id, 'product_id' => $item['product_id']],
                            ['quantity' => DB::raw('quantity + ' . $item['quantity']), 'distributed_at' => now()]
                        );

                        StockMovement::create([
                            'product_id'    => $item['product_id'],
                            'quantity'      => $item['quantity'],
                            'movement_type' => 'distribute',
                            'agent_id'      => $agent->id,
                            'user_id'       => $distribution->created_by,
                            'notes'         => 'توزيع رقم ' . $distNo,
                        ]);
                    }
                }

                $this->accounting->postDistributionEntry(
                    $distribution->id, $totalAmount, $agent->id,
                    $distribution->distribution_date, $distribution->created_by
                );

                return $distribution->fresh('items');
            });

            return ['success' => true, 'message' => 'تم التوزيع بنجاح', 'distribution' => $distribution];
        } catch (Throwable $e) {
            Log::error('DistributionService::addDistribution failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()];
        }
    }

    public function deleteDistribution(int $id): array
    {
        $distribution = Distribution::with('items')->find($id);
        if (! $distribution) return ['success' => false, 'message' => 'التوزيع غير موجود'];

        try {
            DB::transaction(function () use ($distribution) {
                foreach ($distribution->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->increment('quantity', (float) $item->quantity);
                        AgentStock::where('agent_id', $distribution->agent_id)
                            ->where('product_id', $item->product_id)
                            ->decrement('quantity', (float) $item->quantity);
                    }
                }
                $distribution->delete();
            });
            return ['success' => true, 'message' => 'تم حذف التوزيع'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    private function normalizeItems(array $raw): array
    {
        $items = [];
        foreach ($raw as $r) {
            $desc = trim((string) ($r['description'] ?? ''));
            $qty  = (float) ($r['quantity'] ?? 0);
            $price = (float) ($r['unit_price'] ?? 0);
            if ($desc === '' || $qty <= 0 || $price < 0) continue;
            $items[] = [
                'product_id' => ! empty($r['product_id']) ? (int) $r['product_id'] : null,
                'description'=> $desc,
                'quality'    => $r['quality'] ?? null,
                'quantity'   => $qty,
                'unit'       => $r['unit'] ?? 'حزمة',
                'unit_price' => $price,
                'notes'      => $r['notes'] ?? null,
            ];
        }
        return $items;
    }
}
