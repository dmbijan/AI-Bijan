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
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('affiliate_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('website')->nullable();
            $table->json('social_media')->nullable();
            $table->json('payment_method')->nullable();
            $table->text('bio')->nullable();
            $table->string('status')->default('pending'); // pending, active, suspended, inactive
            $table->string('tier')->default('standard'); // standard, silver, gold, platinum
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->decimal('total_payouts', 15, 2)->default(0);
            $table->decimal('pending_balance', 15, 2)->default(0);
            $table->integer('total_commissions')->default(0);
            $table->integer('approved_commissions')->default(0);
            $table->integer('rejected_commissions')->default(0);
            $table->integer('total_clicks')->default(0);
            $table->integer('total_conversions')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->string('rejected_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'tier']);
        });

        Schema::create('affiliate_referral_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->string('referral_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('default'); // default, campaign, social, email
            $table->string('url')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('usage_count')->default(0);
            $table->integer('conversion_count')->default(0);
            $table->integer('total_clicks')->default(0);
            $table->integer('total_conversions')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->string('status')->default('active'); // active, inactive, pending
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['affiliate_id', 'status']);
        });

        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->foreignId('referral_code_id')->constrained('affiliate_referral_codes')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('referred_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('referral_code');
            $table->string('status')->default('pending'); // pending, converted, rejected
            $table->string('conversion_type')->nullable(); // sale, subscription, upgrade, renewal, first_purchase
            $table->decimal('conversion_value', 15, 2)->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['affiliate_id', 'status']);
            $table->index(['referral_code']);
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->foreignId('referral_code_id')->nullable()->constrained('affiliate_referral_codes')->nullOnDelete();
            $table->foreignId('referral_id')->nullable()->constrained('affiliate_referrals')->nullOnDelete();
            $table->string('commission_code')->unique();
            $table->string('order_id')->nullable();
            $table->string('invoice_id')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('commission_type'); // sale, subscription, upgrade, renewal, first_purchase
            $table->decimal('base_value', 15, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->string('status')->default('pending'); // pending, approved, paid, rejected
            $table->foreignId('payout_id')->nullable()->constrained('affiliate_payouts')->nullOnDelete();
            $table->string('currency')->default('MYR');
            $table->text('description')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejected_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['affiliate_id', 'status']);
            $table->index(['payout_id']);
        });

        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->foreignId('referral_code_id')->constrained('affiliate_referral_codes')->cascadeOnDelete();
            $table->string('click_id')->unique();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referrer_url')->nullable();
            $table->text('landing_url')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('device')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->boolean('converted')->default(false);
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['affiliate_id', 'created_at']);
            $table->index(['referral_code_id']);
        });

        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->string('payout_code')->unique();
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('MYR');
            $table->string('payment_method')->nullable(); // bank_transfer, paypal, stripe, wire
            $table->json('payment_details')->nullable();
            $table->string('status')->default('pending'); // pending, processed, cancelled
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['affiliate_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_payouts');
        Schema::dropIfExists('affiliate_clicks');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliate_referrals');
        Schema::dropIfExists('affiliate_referral_codes');
        Schema::dropIfExists('affiliates');
    }
};
