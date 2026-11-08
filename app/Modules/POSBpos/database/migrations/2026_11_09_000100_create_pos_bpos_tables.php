<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ciri POS lanjutan daripada B-POS (dmbijan/bpos) yang belum ada dalam
 * modul POS AI BIJAN.
 *
 * ⚠️ Wang disimpan sebagai integer SEN — elak ralat titik terapung.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_gift_cards', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('code', 60);
            $t->unsignedBigInteger('initial_balance_sen')->default(0);
            $t->unsignedBigInteger('balance_sen')->default(0);
            $t->string('currency', 3)->default('MYR');
            $t->string('status', 20)->default('active');
            $t->unsignedBigInteger('issued_to_customer_id')->nullable();
            $t->unsignedBigInteger('issued_sale_id')->nullable();
            $t->string('note', 500)->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('redeemed_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->unique(['tenant_id', 'code']);
        });

        Schema::create('pos_table_reservations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('reservation_no', 40);
            $t->unsignedBigInteger('dine_table_id')->nullable();
            $t->unsignedBigInteger('customer_id')->nullable();
            $t->string('guest_name', 150);
            $t->string('guest_phone', 40)->nullable();
            $t->string('guest_email', 180)->nullable();
            $t->unsignedSmallInteger('party_size')->default(2);
            $t->timestamp('reserved_at');
            $t->unsignedSmallInteger('duration_minutes')->default(90);
            $t->string('status', 20)->default('pending');
            $t->text('note')->nullable();
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamp('seated_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamp('cancelled_at')->nullable();
            $t->string('cancel_reason', 500)->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'reserved_at']);
            $t->unique(['tenant_id', 'reservation_no']);
        });

        Schema::create('pos_staff_attendances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('register_id')->nullable();
            $t->date('attendance_date');
            $t->timestamp('clock_in_at')->nullable();
            $t->timestamp('clock_out_at')->nullable();
            $t->string('status', 20)->default('present');
            $t->unsignedSmallInteger('minutes_late')->default(0);
            $t->unsignedSmallInteger('minutes_worked')->default(0);
            $t->string('note', 500)->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'attendance_date']);
            $t->unique(['tenant_id', 'user_id', 'attendance_date']);
        });

        Schema::create('pos_staff_roster_shifts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('register_id')->nullable();
            $t->date('shift_date');
            $t->string('start_time', 5);
            $t->string('end_time', 5);
            $t->string('role', 60)->nullable();
            $t->string('status', 20)->default('scheduled');
            $t->string('note', 500)->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'shift_date']);
            $t->index(['tenant_id', 'user_id']);
        });

        Schema::create('pos_food_courts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('name', 150);
            $t->string('code', 20)->nullable();
            $t->string('kv', 60)->nullable();
            $t->text('description')->nullable();
            $t->unsignedSmallInteger('capacity')->default(0);
            $t->boolean('is_active')->default(true);
            $t->json('settings')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'is_active']);
        });

        Schema::create('pos_food_court_tables', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->foreignId('food_court_id')->constrained('pos_food_courts')->cascadeOnDelete();
            $t->string('table_no', 30);
            $t->unsignedSmallInteger('seats')->default(4);
            $t->string('status', 20)->default('free');
            $t->unsignedBigInteger('current_dine_order_id')->nullable();
            $t->string('pairing_code', 20)->nullable();
            $t->string('qr_string', 255)->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();

            $t->index(['food_court_id', 'status']);
            $t->unique(['tenant_id', 'food_court_id', 'table_no']);
        });

        Schema::create('pos_queue_counters', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->unsignedBigInteger('food_court_id')->nullable();
            $t->unsignedBigInteger('register_id')->nullable();
            $t->date('counter_date');
            $t->string('shift_key', 40)->nullable();
            $t->string('prefix', 3)->default('A');
            $t->unsignedInteger('last_number')->default(0);
            $t->unsignedInteger('current_number')->default(0);
            $t->timestamp('called_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'counter_date']);
        });

        Schema::create('pos_push_subscriptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('device_key', 120)->nullable();
            $t->string('endpoint', 500);
            $t->string('public_key', 255);
            $t->string('auth_token', 255);
            $t->string('content_encoding', 20)->default('aesgcm');
            $t->string('user_agent', 255)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamp('last_used_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'is_active']);
            $t->unique(['tenant_id', 'endpoint']);
        });

        Schema::create('pos_delivery_syncs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('client_uuid', 64);
            $t->unsignedBigInteger('pos_sale_id')->nullable();
            $t->json('payload');
            $t->string('status', 20)->default('pending');
            $t->unsignedSmallInteger('attempts')->default(0);
            $t->text('last_error')->nullable();
            $t->timestamp('synced_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->unique(['tenant_id', 'client_uuid']);
        });

        Schema::create('pos_online_stores', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->unsignedBigInteger('register_id')->nullable();
            $t->string('name', 150);
            $t->string('slug', 180);
            $t->text('description')->nullable();
            $t->string('logo_url', 500)->nullable();
            $t->string('banner_url', 500)->nullable();
            $t->string('status', 20)->default('draft');
            $t->boolean('allow_pickup')->default(true);
            $t->boolean('allow_delivery')->default(false);
            $t->unsignedBigInteger('minimum_order_sen')->default(0);
            $t->unsignedBigInteger('delivery_fee_sen')->default(0);
            $t->json('opening_hours')->nullable();
            $t->json('settings')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->unique(['tenant_id', 'slug']);
        });

        Schema::create('pos_branch_invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('invoice_no', 40);
            $t->string('branch_name', 150);
            $t->unsignedBigInteger('register_id')->nullable();
            $t->date('billing_month');
            $t->unsignedBigInteger('amount_sen')->default(0);
            $t->unsignedBigInteger('amount_paid_sen')->default(0);
            $t->string('currency', 3)->default('MYR');
            $t->string('status', 20)->default('draft');
            $t->timestamp('due_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->string('payment_method', 40)->nullable();
            $t->string('chip_reference', 120)->nullable();
            $t->text('note')->nullable();
            $t->unsignedBigInteger('issued_by')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'billing_month']);
            $t->unique(['tenant_id', 'invoice_no']);
        });

        Schema::create('pos_subscription_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('client_uuid', 64);
            $t->foreignId('pos_branch_invoice_id')->nullable()->constrained('pos_branch_invoices')->nullOnDelete();
            $t->unsignedBigInteger('amount_sen')->default(0);
            $t->string('currency', 3)->default('MYR');
            $t->string('status', 20)->default('pending');
            $t->string('gateway', 40)->default('chip');
            $t->string('gateway_reference', 120)->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('refunded_at')->nullable();
            $t->json('payload')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->unique(['tenant_id', 'client_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_subscription_payments');
        Schema::dropIfExists('pos_branch_invoices');
        Schema::dropIfExists('pos_online_stores');
        Schema::dropIfExists('pos_delivery_syncs');
        Schema::dropIfExists('pos_push_subscriptions');
        Schema::dropIfExists('pos_queue_counters');
        Schema::dropIfExists('pos_food_court_tables');
        Schema::dropIfExists('pos_food_courts');
        Schema::dropIfExists('pos_staff_roster_shifts');
        Schema::dropIfExists('pos_staff_attendances');
        Schema::dropIfExists('pos_table_reservations');
        Schema::dropIfExists('pos_gift_cards');
    }
};
