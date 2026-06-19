<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * SaleService — handles POS / sale creation, including:
 *  - flexible line items (description-driven, not strict product catalog)
 *  - stock check + decrement (main inventory) OR agent stock
 *  - COGS calculation (uses weighted average cost snapshot)
 *  - auto journal entry (revenue + COGS) inside the same DB transaction
 *  - debt creation for credit sales
 *  - customer balance recomputation from source (not incremental)
 */
class SaleService
{
    public function __construct(
        protected SequenceService $sequences,
        protected AccountingService $accounting,
        protected FinancialTransactionService $financial,
    ) {}

    /**
     * Create a sale with multiple line items.
     *
     * @param array $data {
     *   items: array<int, {product_id:?int, description:string, quality:?string, quantity:float, unit:string, unit_price:float, notes:?string}>,
     *   payment_method: 'cash'|'credit'|'transfer',
     *   wallet_type: ?string,
     *   discount: float,
     *   paid_amount: float,
     *   sale_date: string,
     *   agent_id: ?int,
     *   customer_id: ?int,
     *   notes: ?string,
     *   created_by: ?int
     * }
     * @return array{success:bool, message:string, sale?:Sale}
     */
    public function addSale(array $data): array
    {
        $items = $this->normalizeItems($data['items'] ?? []);
        if (empty($items)) {
            return ['success' => false, 'message' => 'يرجى إضافة عنصر واحد على الأقل'];
        }

        $paymentMethod = $data['payment_method'] ?? 'cash';
        if (! in_array($paymentMethod, ['cash', 'credit', 'transfer'], true)) {
            return ['success' => false, 'message' => 'طريقة الدفع غير صحيحة'];
        }

        $discount = (float) ($data['discount'] ?? 0);
        if ($discount < 0) {
            return ['success' => false, 'message' => 'الخصم غير صحيح'];
        }

        $totalAmount = array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_price'], $items));
        $finalAmount = max(0, $totalAmount - $discount);
        $paidAmount  = min((float) ($data['paid_amount'] ?? 0), $finalAmount);

        if ($paymentMethod === 'cash' && $paidAmount < $finalAmount) {
            $paidAmount = $finalAmount; // Cash sales must be fully paid
        }

        $agentId    = ! empty($data['agent_id']) ? (int) $data['agent_id'] : null;
        $customerId = ! empty($data['customer_id']) ? (int) $data['customer_id'] : null;

        if ($paymentMethod === 'credit' && ! $customerId) {
            return ['success' => false, 'message' => 'يجب اختيار عميل للبيع الآجل'];
        }

        // Stock pre-check (fail-fast)
        foreach ($items as $item) {
            if (! empty($item['product_id'])) {
                $available = $this->availableQuantity($item['product_id'], $agentId);
                if ($available < $item['quantity']) {
                    $prod = Product::find($item['product_id']);
                    return ['success' => false, 'message' => "الكمية المطلوبة من [{$prod?->name}] غير متوفرة. المتوفر: {$available}"];
                }
            }
        }

        try {
            $sale = DB::transaction(function () use (
                $items, $paymentMethod, $discount, $totalAmount, $finalAmount,
                $paidAmount, $agentId, $customerId, $data
            ) {
                $invoiceNo = $this->sequences->next('invoice', 'INV', 6);

                /** @var Sale $sale */
                $sale = Sale::create([
                    'invoice_no'      => $invoiceNo,
                    'agent_id'        => $agentId,
                    'customer_id'     => $customerId,
                    'sale_date'       => $data['sale_date'] ?? now()->format('Y-m-d'),
                    'total_amount'    => $totalAmount,
                    'discount_amount' => $discount,
                    'final_amount'    => $finalAmount,
                    'paid_amount'     => $paidAmount,
                    'payment_method'  => $paymentMethod,
                    'wallet_type'     => $data['wallet_type'] ?? null,
                    'notes'           => $data['notes'] ?? null,
                    'created_by'      => $data['created_by'] ?? auth()->id(),
                ]);

                $cogsTotal = 0;
                foreach ($items as $item) {
                    $qty   = (float) $item['quantity'];
                    $price = (float) $item['unit_price'];
                    $lineTotal = round($qty * $price, 2);

                    $cogs = 0;
                    $wac  = 0;
                    if (! empty($item['product_id'])) {
                        $product = Product::lockForUpdate()->find($item['product_id']);
                        $wac = (float) $product->weighted_average_cost;
                        $cogs = round($qty * $wac, 2);

                        // Decrement stock
                        $product->decrement('quantity', $qty);
                        if ($agentId) {
                            \App\Models\AgentStock::where('agent_id', $agentId)
                                ->where('product_id', $product->id)
                                ->decrement('quantity', $qty);
                        }

                        StockMovement::create([
                            'product_id'    => $product->id,
                            'quantity'      => -$qty,
                            'movement_type' => 'out',
                            'agent_id'      => $agentId,
                            'user_id'       => $data['created_by'] ?? auth()->id(),
                            'notes'         => 'بيع فاتورة ' . $invoiceNo,
                        ]);
                    }

                    $cogsTotal += $cogs;

                    SaleItem::create([
                        'sale_id'                       => $sale->id,
                        'product_id'                    => $item['product_id'] ?? null,
                        'description'                   => $item['description'],
                        'quality'                       => $item['quality'] ?? null,
                        'quantity'                      => $qty,
                        'unit'                          => $item['unit'] ?? 'حزمة',
                        'unit_price'                    => $price,
                        'total_price'                   => $lineTotal,
                        'cogs_amount'                   => $cogs,
                        'weighted_average_cost_at_sale' => $wac,
                        'notes'                         => $item['notes'] ?? null,
                    ]);
                }

                // Financial transaction record
                $this->financial->record([
                    'trans_date'     => $sale->sale_date,
                    'direction'      => 'in',
                    'amount'         => $paidAmount,
                    'payment_method' => $paymentMethod === 'credit' ? 'credit' : $paymentMethod,
                    'wallet_type'    => $data['wallet_type'] ?? null,
                    'ref_type'       => 'sale',
                    'ref_id'         => $sale->id,
                    'entity_type'    => $customerId ? 'customer' : null,
                    'entity_id'      => $customerId ?: null,
                    'notes'          => 'تحصيل بيع فاتورة ' . $invoiceNo,
                    'created_by'     => $sale->created_by,
                ]);

                // Auto journal entry — cash sale or credit sale
                if ($paymentMethod === 'credit') {
                    $this->accounting->postCreditSaleEntry($sale->id, $finalAmount, $cogsTotal, $customerId, $sale->sale_date, $sale->created_by);
                    // Create debt for the un-paid portion
                    $remaining = $finalAmount - $paidAmount;
                    if ($remaining > 0) {
                        Debt::create([
                            'customer_id'     => $customerId,
                            'sale_id'         => $sale->id,
                            'agent_id'        => $agentId,
                            'total_amount'    => $remaining,
                            'paid_amount'     => 0,
                            'remaining_amount'=> $remaining,
                            'status'          => 'pending',
                            'notes'           => 'دين من فاتورة ' . $invoiceNo,
                        ]);
                    }
                } else {
                    $this->accounting->postSaleEntry($sale->id, $finalAmount, $cogsTotal, $sale->sale_date, $sale->created_by);
                }

                // Recompute customer balances (not incremental)
                if ($customerId) {
                    Customer::find($customerId)?->recomputeBalances();
                }

                return $sale->fresh('items');
            });

            return ['success' => true, 'message' => 'تم حفظ البيع بنجاح', 'sale' => $sale];
        } catch (Throwable $e) {
            Log::error('SaleService::addSale failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ['success' => false, 'message' => 'حدث خطأ أثناء حفظ البيع: ' . $e->getMessage()];
        }
    }

    public function deleteSale(int $saleId, ?int $agentId = null, bool $isAdmin = false): array
    {
        $sale = Sale::with('items', 'debts')->find($saleId);
        if (! $sale) {
            return ['success' => false, 'message' => 'البيع غير موجود'];
        }
        if (! $isAdmin && $agentId && $sale->agent_id !== $agentId) {
            return ['success' => false, 'message' => 'لا يمكنك حذف بيع لا تملكه'];
        }

        try {
            DB::transaction(function () use ($sale) {
                // Restore stock
                foreach ($sale->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->increment('quantity', (float) $item->quantity);
                        if ($sale->agent_id) {
                            \App\Models\AgentStock::where('agent_id', $sale->agent_id)
                                ->where('product_id', $item->product_id)
                                ->increment('quantity', (float) $item->quantity);
                        }
                        StockMovement::create([
                            'product_id'    => $item->product_id,
                            'quantity'      => $item->quantity,
                            'movement_type' => 'return',
                            'agent_id'      => $sale->agent_id,
                            'user_id'       => auth()->id(),
                            'notes'         => 'إلغاء فاتورة ' . $sale->invoice_no,
                        ]);
                    }
                }

                // Void related journal entries
                foreach ($sale->journalEntries as $je) {
                    if ($je->status === 'posted') {
                        $this->accounting->voidEntry($je, 'حذف البيع #' . $sale->id);
                    }
                }

                // Soft delete
                $sale->delete();
            });
            return ['success' => true, 'message' => 'تم حذف البيع بنجاح'];
        } catch (Throwable $e) {
            Log::error('SaleService::deleteSale failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()];
        }
    }

    public function getSale(int $saleId, ?int $agentId = null): array
    {
        $sale = Sale::with('items.product', 'agent', 'customer', 'creator')->find($saleId);
        if (! $sale) {
            return ['success' => false, 'message' => 'البيع غير موجود'];
        }
        if ($agentId && $sale->agent_id !== $agentId) {
            return ['success' => false, 'message' => 'غير مصرح'];
        }
        return ['success' => true, 'sale' => $sale];
    }

    public function getProductInfo(int $productId, ?int $agentId = null): array
    {
        $product = Product::find($productId);
        if (! $product) {
            return ['success' => false, 'message' => 'المنتج غير موجود'];
        }
        return [
            'success' => true,
            'product' => [
                'id'            => $product->id,
                'name'          => $product->name,
                'unit'          => $product->unit,
                'sell_price'    => (float) $product->sell_price,
                'quantity'      => $this->availableQuantity($productId, $agentId),
                'weighted_avg_cost' => (float) $product->weighted_average_cost,
            ],
        ];
    }

    public function getCustomersByAgent(?int $agentId): array
    {
        $q = Customer::where('status', 'active');
        if ($agentId) $q->where('agent_id', $agentId);
        return ['success' => true, 'customers' => $q->limit(500)->get(['id', 'name', 'phone'])];
    }

    // ===== Internals =====

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

    private function availableQuantity(int $productId, ?int $agentId): float
    {
        if ($agentId) {
            $row = \App\Models\AgentStock::where('agent_id', $agentId)
                ->where('product_id', $productId)
                ->first();
            return (float) ($row?->quantity ?? 0);
        }
        return (float) (Product::find($productId)?->quantity ?? 0);
    }
}
