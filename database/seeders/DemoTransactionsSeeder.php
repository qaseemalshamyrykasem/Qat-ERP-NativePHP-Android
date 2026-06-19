<?php

namespace Database\Seeders;

use App\Services\AgentSettlementService;
use App\Services\DistributionService;
use App\Services\ExpenseService;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

class DemoTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        $today = now()->format('Y-m-d');

        // Sample purchases
        $purchaseService = app(PurchaseService::class);
        $purchaseService->addPurchase([
            'supplier_id'    => 1,
            'purchase_date'  => $today,
            'payment_method' => 'cash',
            'paid_amount'    => 80000,
            'notes'          => 'شراء تجريبي',
            'created_by'     => 1,
            'items' => [
                ['product_id' => 1, 'description' => 'قات صعدي ممتاز', 'quantity' => 5,  'unit_price' => 15000],
                ['product_id' => 2, 'description' => 'قات صعدي جيد',   'quantity' => 8,  'unit_price' => 12000],
            ],
        ]);

        // Sample distribution to agent 1
        $distService = app(DistributionService::class);
        $distService->addDistribution([
            'agent_id'           => 1,
            'distribution_date'  => $today,
            'created_by'         => 1,
            'items' => [
                ['product_id' => 1, 'description' => 'قات صعدي ممتاز', 'quantity' => 3, 'unit_price' => 16000],
                ['product_id' => 2, 'description' => 'قات صعدي جيد',   'quantity' => 5, 'unit_price' => 13000],
            ],
        ]);

        // Sample cash sale
        $saleService = app(SaleService::class);
        $saleService->addSale([
            'agent_id'       => 1,
            'payment_method' => 'cash',
            'paid_amount'    => 35000,
            'sale_date'      => $today,
            'created_by'     => 4,
            'items' => [
                ['product_id' => 1, 'description' => 'قات صعدي ممتاز', 'quantity' => 2, 'unit_price' => 18000],
            ],
        ]);

        // Sample credit sale
        $saleService->addSale([
            'agent_id'       => 1,
            'customer_id'    => 1,
            'payment_method' => 'credit',
            'paid_amount'    => 5000,
            'sale_date'      => $today,
            'created_by'     => 4,
            'items' => [
                ['product_id' => 2, 'description' => 'قات صعدي جيد', 'quantity' => 3, 'unit_price' => 15000],
            ],
        ]);

        // Sample expense
        $expService = app(ExpenseService::class);
        $expService->create([
            'category'      => 'مواصلات',
            'amount'        => 2500,
            'payment_method'=> 'cash',
            'expense_date'  => $today,
            'description'   => 'مواصلات الوكلاء',
            'created_by'    => 1,
        ]);
    }
}
