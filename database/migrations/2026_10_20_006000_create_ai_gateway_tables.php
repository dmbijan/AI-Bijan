<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AI Model Catalog (BAB 21.2)
        if (!Schema::hasTable('ai_model_catalog')) {
            Schema::create('ai_model_catalog', function (Blueprint $table) {
                $table->id();
                $table->string('provider_slug', 50)->index();
                $table->string('provider_model_id', 100);
                $table->string('display_name');
                $table->enum('tier', ['flash', 'standard', 'premium', 'ultra'])->default('standard')->index();
                $table->json('capabilities')->nullable();
                $table->unsignedInteger('max_context')->nullable();
                $table->decimal('input_rate', 10, 6)->nullable();
                $table->decimal('output_rate', 10, 6)->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->string('version_snapshot', 50)->nullable();
                $table->enum('availability_status', [
                    'available', 'beta', 'deprecated', 'sunset',
                ])->default('available')->index();
                $table->date('last_verified')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['provider_slug', 'provider_model_id'], 'uk_model_provider_id');
            });
        }

        // AI Credit Wallet (BAB 22)
        if (!Schema::hasTable('ai_credit_wallets')) {
            Schema::create('ai_credit_wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->unique()->constrained()->onDelete('cascade');
                $table->decimal('balance', 15, 2)->default(0);
                $table->decimal('period_quota', 15, 2)->default(0);
                $table->decimal('used_this_period', 15, 2)->default(0);
                $table->datetime('period_start')->nullable();
                $table->datetime('period_end')->nullable();
                $table->boolean('auto_recharge_enabled')->default(false);
                $table->string('auto_recharge_package', 100)->nullable();
                $table->char('currency', 3)->default('MYR');
                $table->timestamps();
            });
        }

        // AI Wallet Ledger
        if (!Schema::hasTable('ai_wallet_ledger')) {
            Schema::create('ai_wallet_ledger', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['top_up', 'deduction', 'reset', 'refund', 'adjustment'])->index();
                $table->decimal('amount', 15, 2);
                $table->decimal('balance_after', 15, 2);
                $table->string('reference')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'created_at']);
            });
        }

        // AI Usage Events (BAB 21.3 metering)
        if (!Schema::hasTable('ai_usage_events')) {
            Schema::create('ai_usage_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('agent_key', 50)->nullable()->index();
                $table->string('source_module', 50)->default('ai_gateway');
                $table->string('provider_model_id', 100)->index();
                $table->string('model_display_name');
                $table->string('tier', 20)->index();
                $table->string('task_type', 40)->nullable()->index();
                $table->unsignedInteger('input_tokens')->default(0);
                $table->unsignedInteger('output_tokens')->default(0);
                $table->unsignedInteger('total_tokens')->default(0);
                $table->unsignedInteger('credits_charged')->default(0);
                $table->decimal('cost_rm', 10, 4)->default(0);
                $table->unsignedInteger('latency_ms')->nullable();
                $table->enum('status', ['success', 'failed', 'cached', 'blocked'])->default('success')->index();
                $table->text('error_message')->nullable();
                $table->string('correlation_id', 64)->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'created_at']);
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'model_display_name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_events');
        Schema::dropIfExists('ai_wallet_ledger');
        Schema::dropIfExists('ai_credit_wallets');
        Schema::dropIfExists('ai_model_catalog');
    }
};
