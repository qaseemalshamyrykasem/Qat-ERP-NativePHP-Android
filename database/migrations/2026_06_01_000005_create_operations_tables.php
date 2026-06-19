<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ===== Expenses =====
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50);
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->enum('payment_method', ['cash', 'transfer'])->default('cash');
            $table->string('wallet_type', 50)->nullable();
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['expense_date', 'category']);
        });

        // ===== Financial transactions (unified ledger) =====
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('trans_date');
            $table->enum('direction', ['in', 'out']);
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->enum('payment_method', ['cash', 'transfer', 'credit'])->default('cash');
            $table->string('wallet_type', 50)->nullable();
            $table->string('ref_type', 50);
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->string('entity_type', 30)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['trans_date', 'direction', 'ref_type']);
            $table->index(['entity_type', 'entity_id']);
        });

        // ===== Agent settlements =====
        Schema::create('agent_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_no', 30)->nullable()->unique();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->date('settlement_date');
            $table->decimal('total_sales', 15, 2)->default(0.00);
            $table->decimal('cash_sales', 15, 2)->default(0.00);
            $table->decimal('credit_sales', 15, 2)->default(0.00);
            $table->decimal('transfer_sales', 15, 2)->default(0.00);
            $table->decimal('debt_payments', 15, 2)->default(0.00);
            $table->decimal('expenses', 15, 2)->default(0.00);
            $table->decimal('commission_amount', 15, 2)->default(0.00);
            $table->decimal('shaqa_amount', 15, 2)->default(0.00);
            $table->decimal('discounts', 15, 2)->default(0.00);
            $table->decimal('shortages', 15, 2)->default(0.00);
            $table->decimal('net_due', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['agent_id', 'settlement_date'], 'uniq_agent_settlement_date');
        });

        // ===== Daily sessions =====
        Schema::create('daily_sessions', function (Blueprint $table) {
            $table->id();
            $table->date('session_date')->unique();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('total_sales', 15, 2)->default(0.00);
            $table->decimal('total_cash', 15, 2)->default(0.00);
            $table->decimal('total_credit', 15, 2)->default(0.00);
            $table->decimal('total_transfers', 15, 2)->default(0.00);
            $table->decimal('total_expenses', 15, 2)->default(0.00);
            $table->decimal('total_debt_payments', 15, 2)->default(0.00);
            $table->decimal('net_profit', 15, 2)->default(0.00);
            $table->decimal('expected_balance', 15, 2)->default(0.00);
            $table->decimal('actual_balance', 15, 2)->default(0.00);
            $table->decimal('difference', 15, 2)->default(0.00);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('opened_at')->useCurrent();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // ===== Settings =====
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value')->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // ===== Message templates =====
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['debt_reminder', 'payment_confirmation', 'account_statement', 'custom'])->default('custom');
            $table->text('content');
            $table->string('variables', 255)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // ===== Activity log =====
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('module', 50);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->text('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['module', 'action']);
            $table->index('ip_address');
        });

        // ===== Notifications =====
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // ===== In-app notifications (legacy) =====
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['info', 'warning', 'success', 'danger', 'reminder', 'error', 'debt', 'payment', 'sale', 'purchase', 'expense', 'system'])->default('info');
            $table->string('title', 200);
            $table->text('message')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('read_at')->nullable();
            $table->index(['user_id', 'is_read']);
        });

        // ===== Reminders =====
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->enum('entity_type', ['customer', 'supplier', 'agent', 'debt', 'general'])->default('general');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->enum('reminder_type', ['payment', 'receipt', 'event', 'debt_due'])->default('payment');
            $table->string('title', 200);
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('due_date');
            $table->text('notes')->nullable();
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('sms_sent')->default(false);
            $table->timestamp('sms_sent_at')->nullable();
            $table->boolean('repeat_daily')->default(false);
            $table->enum('status', ['pending', 'sent', 'dismissed', 'expired'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });

        // ===== Documents =====
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 50)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->string('description', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['entity_type', 'entity_id']);
        });

        // ===== Cache / Jobs / Failed Jobs =====
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('daily_sessions');
        Schema::dropIfExists('agent_settlements');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('expenses');
    }
};
