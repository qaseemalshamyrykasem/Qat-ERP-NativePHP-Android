<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ===== Purchases =====
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->nullable()->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->nullOnDelete();
            $table->date('purchase_date');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->enum('payment_method', ['cash', 'credit', 'transfer'])->default('cash');
            $table->string('wallet_type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['purchase_date', 'supplier_id']);
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('description', 255);
            $table->string('quality', 50)->nullable();
            $table->decimal('quantity', 12, 2)->default(0.00);
            $table->string('unit', 20)->default('حزمة');
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->index(['purchase_id', 'product_id']);
        });

        Schema::create('supplier_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('remaining_amount', 15, 2)->default(0.00);
            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['supplier_id', 'status']);
        });

        Schema::create('supplier_debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_debt_id')->constrained('supplier_debts')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'transfer', 'other'])->default('cash');
            $table->string('wallet_type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index('payment_date');
        });

        // ===== Distributions to agents =====
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->string('distribution_no', 30)->nullable()->unique();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->date('distribution_date');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['distribution_date', 'agent_id']);
        });

        Schema::create('distribution_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained('distributions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('description', 255);
            $table->string('quality', 50)->nullable();
            $table->decimal('quantity', 12, 2)->default(0.00);
            $table->string('unit', 20)->default('حزمة');
            $table->decimal('unit_price', 15, 2)->default(0.00)->comment('سعر التسليم');
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->index(['distribution_id', 'product_id']);
        });

        // ===== Sales =====
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->unique();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->date('sale_date');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('final_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->enum('payment_method', ['cash', 'credit', 'transfer'])->default('cash');
            $table->string('wallet_type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['sale_date', 'agent_id']);
            $table->index(['payment_method', 'wallet_type']);
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('description', 255);
            $table->string('quality', 50)->nullable();
            $table->decimal('quantity', 12, 2)->default(0.00);
            $table->string('unit', 20)->default('حزمة');
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->decimal('cogs_amount', 15, 2)->default(0);
            $table->decimal('weighted_average_cost_at_sale', 15, 4)->default(0);
            $table->text('notes')->nullable();
            $table->index(['sale_id', 'product_id']);
        });

        // ===== Debts =====
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('remaining_amount', 15, 2)->default(0.00);
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['customer_id', 'status']);
        });

        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained('debts')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'transfer', 'other'])->default('cash');
            $table->string('wallet_type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
        Schema::dropIfExists('debts');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('distribution_items');
        Schema::dropIfExists('distributions');
        Schema::dropIfExists('supplier_debt_payments');
        Schema::dropIfExists('supplier_debts');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
