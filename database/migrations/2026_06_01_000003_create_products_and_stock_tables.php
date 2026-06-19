<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('type', 50)->nullable();
            $table->decimal('buy_price', 15, 2)->default(0.00);
            $table->decimal('weighted_average_cost', 15, 4)->default(0);
            $table->decimal('sell_price', 15, 2)->default(0.00);
            $table->decimal('quantity', 12, 2)->default(0.00);
            $table->string('unit', 20)->default('حزمة');
            $table->decimal('min_quantity', 12, 2)->default(0.00);
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'status']);
        });

        Schema::create('daily_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('price_date');
            $table->decimal('buy_price', 15, 2)->default(0.00);
            $table->decimal('sell_price', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['product_id', 'price_date']);
        });

        // ===== Stock =====
        Schema::create('agent_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2)->default(0.00);
            $table->timestamp('distributed_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->index(['agent_id', 'product_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2)->default(0.00);
            $table->enum('movement_type', ['in', 'out', 'distribute', 'return', 'adjust', 'restock'])->default('adjust');
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['product_id', 'movement_type']);
        });

        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('session_id', 128);
            $table->enum('status', ['active', 'completed', 'expired', 'cancelled'])->default('active');
            $table->dateTime('expires_at');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['product_id', 'agent_id']);
            $table->index(['status', 'expires_at']);
        });

        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 50);
            $table->string('prefix', 20)->default('');
            $table->unsignedInteger('current_number')->default(0);
            $table->unsignedTinyInteger('padding_length')->default(4);
            $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate();
            $table->unique('doc_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('agent_stock');
        Schema::dropIfExists('daily_prices');
        Schema::dropIfExists('products');
    }
};
