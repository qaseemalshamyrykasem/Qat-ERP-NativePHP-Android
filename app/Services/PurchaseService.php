<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * PurchaseService — handles purchases of stock from suppliers.
 *
 * For each purchase:
 *  - Insert header + flexible line items (description-driven)
 *  - Increase product stock + recompute weighted average cost
 *  - Auto journal entry: Dr Inventory / Cr Cash + Cr Payables
 *  - Recompute supplier balances from source (not incremental)
 *  - Create supplier_debt record for unpaid portion
 */
class PurchaseService
{
    public function __construct(
        protected SequenceService $sequences,
        protected AccountingService $accounting,
        protected FinancialTransactionService $financial,
    ) {}

    public function addPurchase(array $data): array
    {
        $items = $this->normalizeItems($data['items'] ?? []);
        if (empty($items)) {
            return ['success' => false, 'message' => 'يرجى إضافة عنصر شراء واحد على الأقل'];
        }

        $supplier = Supplier::find($data['supplier_id'] ?? null);
        if (! $supplier) {
            return ['success' => false, 'message' => 'المورد غير موجود'];
        }

        $totalAmount = array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_price'], $items));
        $paidAmount  = (float) ($data['paid_amount'] ?? 0);
        $paymentMethod = $data['payment_method'] ?? 'cash';

        try {
            $purchase = DB::transaction(function () use ($items, $data, $totalAmount, $paidAmount, $paymentMethod, $supplier) {
                $invoiceNo = $this->sequences->next('purchase', 'PUR', 6);

                $purchase = Purchase::create([
                    'invoice_no'     => $invoiceNo,
                    'supplier_id'    => $supplier->id,
                    'purchase_date'  => $data['purchase_date'] ?? now()->format('Y-m-d'),
                    'total_amount'   => $totalAmount,
                    'paid_amount'    => $paidAmount,
                    'payment_method' => $paymentMethod,
                    'wallet_type'    => $data['wallet_type'] ?? null,
                    'notes'          => $data['notes'] ?? null,
                    'created_by'     => $data['created_by'] ?? auth()->id(),
                ]);

                foreach ($items as $item) {
                    $lineTotal = round($item['quantity'] * $item['unit_price'], 2);
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id'  => $item['product_id'] ?? null,
                        'description' => $item['description'],
                        'quality'     => $item['quality'] ?? null,
                        'quantity'    => $item['quantity'],
                        'unit'        => $item['unit'] ?? 'حزمة',
                        'unit_price'  => $item['unit_price'],
                        'total_price' => $lineTotal,
                        'notes'       => $item['notes'] ?? null,
                    ]);

                    if (! empty($item['product_id'])) {
                        $product = Product::lockForUpdate()->find($item['product_id']);
                        if ($product) {
                            $product->recalcWeightedAverageCost($item['quantity'], $item['unit_price']);
                            $product->increment('quantity', $item['quantity']);
                        }
                        StockMovement::create([
                            'product_id'    => $item['product_id'],
                            'quantity'      => $item['quantity'],
                            'movement_type' => 'in',
                            'agent_id'      => null,
                            'user_id'       => $purchase->created_by,
                            'notes'         => 'شراء فاتورة ' . $invoiceNo,
                        ]);
                    }
                }

                // Supplier debt (if unpaid portion)
                $remaining = round($totalAmount - $paidAmount, 2);
                if ($remaining > 0) {
                    SupplierDebt::create([
                        'supplier_id'     => $supplier->id,
                        'purchase_id'     => $purchase->id,
                        'total_amount'    => $remaining,
                        'paid_amount'     => 0,
                        'remaining_amount'=> $remaining,
                        'status'          => 'pending',
                        'notes'           => 'دين من فاتورة شراء ' . $invoiceNo,
                    ]);
                }

                // Financial outflow for paid amount
                if ($paidAmount > 0) {
                    $this->financial->record([
                        'trans_date'     => $purchase->purchase_date,
                        'direction'      => 'out',
                        'amount'         => $paidAmount,
                        'payment_method' => $paymentMethod,
                        'wallet_type'    => $data['wallet_type'] ?? null,
                        'ref_type'       => 'purchase',
                        'ref_id'         => $purchase->id,
                        'entity_type'    => 'supplier',
                        'entity_id'      => $supplier->id,
                        'notes'          => 'سداد شراء فاتورة ' . $invoiceNo,
                        'created_by'     => $purchase->created_by,
                    ]);
                }

                // Auto accounting entry
                $this->accounting->postPurchaseEntry(
                    $purchase->id, $totalAmount, $paidAmount, $supplier->id,
                    $purchase->purchase_date, $purchase->created_by
                );

                // Recompute supplier balances (source-of-truth)
                $supplier->recomputeBalances();

                return $purchase->fresh('items');
            });

            return ['success' => true, 'message' => 'تم حفظ المشترى بنجاح', 'purchase' => $purchase];
        } catch (Throwable $e) {
            Log::error('PurchaseService::addPurchase failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()];
        }
    }

    public function deletePurchase(int $id): array
    {
        $purchase = Purchase::with('items')->find($id);
        if (! $purchase) return ['success' => false, 'message' => 'الفاتورة غير موجودة'];

        try {
            DB::transaction(function () use ($purchase) {
                foreach ($purchase->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->decrement('quantity', (float) $item->quantity);
                        StockMovement::create([
                            'product_id'    => $item->product_id,
                            'quantity'      => -$item->quantity,
                            'movement_type' => 'adjust',
                            'agent_id'      => null,
                            'user_id'       => auth()->id(),
                            'notes'         => 'إلغاء فاتورة شراء ' . $purchase->invoice_no,
                        ]);
                    }
                }
                // Void journal entries
                foreach ($purchase->supplierDebts as $debt) {
                    $debt->payments()->delete();
                    $debt->delete();
                }
                $purchase->delete();
            });
            return ['success' => true, 'message' => 'تم حذف الفاتورة'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    /**
     * Settle a supplier's outstanding balance (full or partial).
     */
    public function settleSupplier(int $supplierId, float $amount, string $method = 'cash', ?string $wallet = null, ?string $date = null): array
    {
        $supplier = Supplier::find($supplierId);
        if (! $supplier) return ['success' => false, 'message' => 'المورد غير موجود'];

        try {
            DB::transaction(function () use ($supplier, $amount, $method, $wallet, $date) {
                $debts = SupplierDebt::where('supplier_id', $supplier->id)
                    ->where('status', '!=', 'paid')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
                $remaining = $amount;
                foreach ($debts as $debt) {
                    if ($remaining <= 0) break;
                    $owed = (float) $debt->remaining_amount;
                    $pay = min($owed, $remaining);
                    $debt->increment('paid_amount', $pay);
                    $debt->decrement('remaining_amount', $pay);
                    $debt->update([
                        'status' => $debt->remaining_amount <= 0 ? 'paid' : ($debt->paid_amount > 0 ? 'partial' : 'pending'),
                    ]);
                    $debt->payments()->create([
                        'amount'         => $pay,
                        'payment_date'   => $date ?? now()->format('Y-m-d'),
                        'payment_method' => $method,
                        'wallet_type'    => $wallet,
                        'created_by'     => auth()->id(),
                    ]);
                    $remaining -= $pay;
                }
                $supplier->recomputeBalances();
            });
            return ['success' => true, 'message' => 'تم تسوية المورد بنجاح'];
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
            if ($desc === '' || $qty <= 0 || $price <= 0) continue;
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
