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
        // Marketing Campaigns
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['email', 'sms', 'whatsapp', 'social_media', 'paid_ads', 'multi_channel']);
            $table->json('channels')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'running', 'paused', 'completed', 'cancelled'])->default('draft');
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('actual_spend', 15, 2)->default(0);
            $table->json('goals')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
        });

        // Marketing Segments
        Schema::create('marketing_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['static', 'dynamic']);
            $table->json('criteria')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('contact_count')->default(0);
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
        });

        // Marketing Contacts
        Schema::create('marketing_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('crm_contact_id')->nullable()->constrained('crm_contacts')->onDelete('cascade');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('source')->nullable();
            $table->json('tags')->nullable();
            $table->json('custom_fields')->nullable();
            $table->boolean('opt_in_email')->default(false);
            $table->timestamp('opt_in_email_at')->nullable();
            $table->string('opt_in_email_method')->nullable();
            $table->boolean('opt_in_sms')->default(false);
            $table->timestamp('opt_in_sms_at')->nullable();
            $table->string('opt_in_sms_method')->nullable();
            $table->boolean('opt_in_whatsapp')->default(false);
            $table->timestamp('opt_in_whatsapp_at')->nullable();
            $table->string('opt_in_whatsapp_method')->nullable();
            $table->integer('total_opens')->default(0);
            $table->integer('total_clicks')->default(0);
            $table->integer('total_purchases')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('email');
            $table->index('source');
        });

        // Marketing Vouchers
        Schema::create('marketing_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('marketing_campaigns')->onDelete('set null');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed', 'free_shipping']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_purchase', 15, 2)->nullable();
            $table->date('valid_from');
            $table->date('valid_until');
            $table->integer('max_uses')->nullable();
            $table->integer('used_count')->default(0);
            $table->json('applicable_products')->nullable();
            $table->enum('status', ['draft', 'active', 'expired', 'inactive'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('code');
            $table->index('status');
        });

        // Voucher Usage
        Schema::create('marketing_voucher_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained('marketing_vouchers')->onDelete('cascade');
            $table->foreignId('contact_id')->nullable()->constrained('marketing_contacts')->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->decimal('discount_amount', 15, 2);
            $table->decimal('order_total', 15, 2);
            $table->timestamps();

            $table->index('voucher_id');
            $table->index('order_id');
        });

        // Marketing Deals
        Schema::create('marketing_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('contact_id')->constrained('marketing_contacts')->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('marketing_campaigns')->onDelete('set null');
            $table->foreignId('crm_deal_id')->nullable()->constrained('crm_deals')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('stage', ['prospect', 'qualified', 'proposal', 'negotiation', 'closed_won', 'closed_lost'])->default('prospect');
            $table->decimal('value', 15, 2)->nullable();
            $table->string('currency', 3)->default('MYR');
            $table->integer('probability')->default(10);
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            $table->text('lost_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('stage');
            $table->index('contact_id');
        });

        // Deal Activities
        Schema::create('marketing_deal_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('marketing_deals')->onDelete('cascade');
            $table->foreignId('contact_id')->constrained('marketing_contacts')->onDelete('cascade');
            $table->enum('type', ['call', 'meeting', 'email', 'note', 'task']);
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('deal_id');
        });

        // Marketing Sequences
        Schema::create('marketing_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('marketing_campaigns')->onDelete('set null');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('trigger', ['new_contact', 'form_submission', 'purchase', 'website_visit', 'email_open', 'email_click']);
            $table->json('trigger_conditions')->nullable();
            $table->json('steps');
            $table->boolean('is_active')->default(false);
            $table->boolean('stop_on_response')->default(false);
            $table->integer('max_repetitions')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index('tenant_id');
        });

        // Sequence Executions
        Schema::create('marketing_sequence_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained('marketing_sequences')->onDelete('cascade');
            $table->foreignId('contact_id')->constrained('marketing_contacts')->onDelete('cascade');
            $table->enum('status', ['pending', 'running', 'waiting', 'completed', 'stopped'])->default('pending');
            $table->integer('current_step')->default(0);
            $table->json('step_results')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['sequence_id', 'contact_id']);
            $table->index('status');
        });

        // Marketing Workflows
        Schema::create('marketing_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('trigger_type', ['new_contact', 'form_submission', 'purchase', 'website_visit', 'email_open', 'email_click', 'campaign_start', 'scheduled']);
            $table->json('trigger_config')->nullable();
            $table->json('steps');
            $table->boolean('is_active')->default(false);
            $table->integer('execution_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index('tenant_id');
            $table->index('is_active');
        });

        // Workflow Executions
        Schema::create('marketing_workflow_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('marketing_workflows')->onDelete('cascade');
            $table->foreignId('contact_id')->constrained('marketing_contacts')->onDelete('cascade');
            $table->foreignId('deal_id')->nullable()->constrained('marketing_deals')->onDelete('set null');
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'stopped'])->default('pending');
            $table->integer('current_step')->default(0);
            $table->json('step_results')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('workflow_id');
            $table->index('status');
        });

        // Lead Scores
        Schema::create('marketing_lead_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('contact_id')->constrained('marketing_contacts')->onDelete('cascade');
            $table->integer('demographic_score')->default(0);
            $table->integer('firmographic_score')->default(0);
            $table->integer('engagement_score')->default(0);
            $table->integer('behavioral_score')->default(0);
            $table->integer('transactional_score')->default(0);
            $table->integer('total_score')->default(0);
            $table->enum('grade', ['A', 'B', 'C', 'D', 'F'])->default('F');
            $table->enum('tier', ['hot', 'warm', 'lukewarm', 'cold', 'frozen'])->default('frozen');
            $table->foreignId('calculated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique('contact_id');
            $table->index('tenant_id');
            $table->index('grade');
            $table->index('tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_lead_scores');
        Schema::dropIfExists('marketing_workflow_executions');
        Schema::dropIfExists('marketing_workflows');
        Schema::dropIfExists('marketing_sequence_executions');
        Schema::dropIfExists('marketing_sequences');
        Schema::dropIfExists('marketing_deal_activities');
        Schema::dropIfExists('marketing_deals');
        Schema::dropIfExists('marketing_voucher_usage');
        Schema::dropIfExists('marketing_vouchers');
        Schema::dropIfExists('marketing_contacts');
        Schema::dropIfExists('marketing_segments');
        Schema::dropIfExists('marketing_campaigns');
    }
};
