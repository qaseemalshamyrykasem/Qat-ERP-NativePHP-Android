<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Note: chart_of_accounts must be created BEFORE financial_transactions and vouchers,
 * since they FK to chart_of_accounts. This migration runs first.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('كود الحساب مثل 1100');
            $table->string('name', 100);
            $table->string('name_en', 100)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->unsignedTinyInteger('level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->enum('balance_direction', ['debit', 'credit'])->default('debit');
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->timestamps();
            $table->index('account_type');
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_no', 30)->unique();
            $table->date('entry_date');
            $table->text('description')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('total_debit', 15, 2)->default(0.00);
            $table->decimal('total_credit', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'posted', 'voided'])->default('posted');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['entry_date', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->decimal('debit', 15, 2)->default(0.00);
            $table->decimal('credit', 15, 2)->default(0.00);
            $table->string('description', 255)->nullable();
            $table->string('entity_type', 30)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->index(['account_id', 'journal_entry_id']);
            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('receipt_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 30)->unique();
            $table->date('voucher_date');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->enum('payment_method', ['cash', 'transfer', 'check'])->default('cash');
            $table->string('wallet_type', 50)->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->text('description')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('voucher_date');
        });

        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 30)->unique();
            $table->date('voucher_date');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->enum('payment_method', ['cash', 'transfer', 'check'])->default('cash');
            $table->string('wallet_type', 50)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->text('description')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('voucher_date');
        });

        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no', 30)->unique();
            $table->foreignId('from_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->foreignId('to_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->foreignId('from_currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignId('to_currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->decimal('converted_amount', 15, 2)->nullable();
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->date('transfer_date');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('completed');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('transfer_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transfers');
        Schema::dropIfExists('payment_vouchers');
        Schema::dropIfExists('receipt_vouchers');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
    }
};
