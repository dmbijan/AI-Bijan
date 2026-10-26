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
        // Loyalty Tiers
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('min_points')->default(0);
            $table->json('benefits')->nullable();
            $table->string('color_hex')->default('#808080');
            $table->string('icon')->default('star');
            $table->decimal('point_multiplier', 4, 2)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'is_active']);
        });

        // Loyalty Members
        Schema::create('loyalty_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('tier_id')->nullable()->constrained('loyalty_tiers')->onDelete('set null');
            $table->foreignId('crm_contact_id')->nullable()->constrained('crm_contacts')->onDelete('set null');
            $table->string('member_code')->unique();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->integer('current_balance')->default(0);
            $table->integer('total_points_earned')->default(0);
            $table->integer('total_points_redeemed')->default(0);
            $table->integer('total_points_expired')->default(0);
            $table->decimal('lifetime_value', 15, 2)->default(0);
            $table->date('last_activity_date')->nullable();
            $table->date('birthday')->nullable();
            $table->string('referral_code')->unique()->nullable();
            $table->foreignId('referred_by_member_id')->nullable()->constrained('loyalty_members')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'member_code']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'tier_id']);
            $table->index(['tenant_id', 'current_balance']);
        });

        // Points Ledger
        Schema::create('loyalty_points_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('loyalty_members')->onDelete('cascade');
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->integer('points');
            $table->integer('balance_after');
            $table->string('type');
            $table->text('description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'member_id']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'expires_at']);
        });

        // Loyalty Rewards
        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['discount', 'free_shipping', 'product', 'cashback', 'experience']);
            $table->integer('cost_points');
            $table->integer('max_redemptions')->nullable();
            $table->integer('total_redemptions')->default(0);
            $table->string('image_url')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'type']);
        });

        // Redemptions
        Schema::create('loyalty_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('loyalty_members')->onDelete('cascade');
            $table->foreignId('reward_id')->nullable()->constrained('loyalty_rewards')->onDelete('set null');
            $table->integer('points_spent');
            $table->enum('status', ['pending', 'fulfilled', 'cancelled', 'expired'])->default('pending');
            $table->string('redemption_code')->unique();
            $table->text('fulfillment_notes')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_redemptions');
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('loyalty_points_ledger');
        Schema::dropIfExists('loyalty_members');
        Schema::dropIfExists('loyalty_tiers');
    }
};
