<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('billing_wallets')) {
            Schema::create('billing_wallets', function (Blueprint $table) {
                $table->uuid('wallet_uuid')->primary();
                $table->uuid('domain_uuid')->unique()->index();
                $table->string('account_number')->unique();
                $table->string('billing_type')->default('PREPAID'); // PREPAID, POSTPAID, HYBRID
                $table->string('currency')->default('USD');
                $table->decimal('available_balance', 12, 4)->default(0.0000);
                $table->decimal('reserved_balance', 12, 4)->default(0.0000);
                $table->decimal('credit_limit', 12, 4)->default(0.0000);
                $table->decimal('credit_exposure', 12, 4)->default(0.0000);
                $table->boolean('auto_recharge_enabled')->default(false);
                $table->decimal('auto_recharge_threshold', 10, 2)->default(10.00);
                $table->decimal('auto_recharge_amount', 10, 2)->default(50.00);
                $table->string('status')->default('ACTIVE'); // ACTIVE, SUSPENDED, GRACE_PERIOD
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('billing_ledger_entries')) {
            Schema::create('billing_ledger_entries', function (Blueprint $table) {
                $table->uuid('entry_uuid')->primary();
                $table->uuid('wallet_uuid')->index();
                $table->uuid('domain_uuid')->index();
                $table->string('transaction_type'); // DEPOSIT, CALL_USAGE, RESERVATION_HOLD, RESERVATION_RELEASE, ADJUSTMENT, REFUND
                $table->string('direction'); // CREDIT, DEBIT
                $table->decimal('amount', 12, 4);
                $table->decimal('balance_before', 12, 4);
                $table->decimal('balance_after', 12, 4);
                $table->string('idempotency_key')->unique()->index();
                $table->string('reference_id')->nullable()->index();
                $table->string('description');
                $table->json('meta')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('billing_tariff_plans')) {
            Schema::create('billing_tariff_plans', function (Blueprint $table) {
                $table->uuid('tariff_plan_uuid')->primary();
                $table->uuid('domain_uuid')->nullable()->index();
                $table->string('name');
                $table->string('currency')->default('USD');
                $table->integer('initial_interval')->default(60); // 60s
                $table->integer('subsequent_interval')->default(6); // 6s
                $table->decimal('connect_fee', 8, 4)->default(0.0000);
                $table->decimal('markup_percentage', 5, 2)->default(15.00);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('billing_tariff_rates')) {
            Schema::create('billing_tariff_rates', function (Blueprint $table) {
                $table->uuid('rate_uuid')->primary();
                $table->uuid('tariff_plan_uuid')->index();
                $table->string('destination_name');
                $table->string('prefix')->index();
                $table->decimal('rate_per_minute', 8, 4);
                $table->decimal('custom_connect_fee', 8, 4)->nullable();
                $table->integer('custom_initial_interval')->nullable();
                $table->integer('custom_subsequent_interval')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('billing_invoices')) {
            Schema::create('billing_invoices', function (Blueprint $table) {
                $table->uuid('invoice_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('wallet_uuid')->index();
                $table->string('invoice_number')->unique();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('subtotal', 10, 2);
                $table->decimal('tax_amount', 10, 2)->default(0.00);
                $table->decimal('total_amount', 10, 2);
                $table->decimal('amount_paid', 10, 2)->default(0.00);
                $table->string('status')->default('ISSUED'); // DRAFT, ISSUED, PAID, OVERDUE
                $table->json('line_items');
                $table->date('due_date');
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_invoices');
        Schema::dropIfExists('billing_tariff_rates');
        Schema::dropIfExists('billing_tariff_plans');
        Schema::dropIfExists('billing_ledger_entries');
        Schema::dropIfExists('billing_wallets');
    }
};
