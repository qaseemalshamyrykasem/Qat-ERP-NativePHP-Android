<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ===== Reference tables =====
        Schema::create('payment_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 50);
            $table->string('symbol', 10);
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ===== Parties =====
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('specialization', 100)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->decimal('total_purchases', 15, 2)->default(0.00);
            $table->decimal('total_paid', 15, 2)->default(0.00);
            $table->decimal('total_remaining', 15, 2)->default(0.00);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('name');
            $table->index('phone');
        });

        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('area', 100)->nullable();
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'area']);
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->decimal('total_debt', 15, 2)->default(0.00);
            $table->decimal('total_paid', 15, 2)->default(0.00);
            $table->decimal('remaining', 15, 2)->default(0.00);
            $table->date('last_payment_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('agents');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('payment_wallets');
    }
};
