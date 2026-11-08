<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsAppSuite — automasi WhatsApp (asal: WasapBot MY).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_devices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('name', 120);
            $t->string('phone_number', 30);
            $t->string('session_id', 120)->nullable();
            $t->string('status', 20)->default('disconnected');
            $t->text('qr_code')->nullable();
            $t->timestamp('paired_at')->nullable();
            $t->timestamp('last_seen_at')->nullable();
            $t->boolean('warmup_enabled')->default(false);
            $t->timestamp('warmup_started_at')->nullable();
            $t->unsignedTinyInteger('warmup_stage')->default(0);
            $t->unsignedInteger('daily_sent_count')->default(0);
            $t->unsignedInteger('daily_limit')->default(200);
            $t->json('settings')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->unique(['tenant_id', 'phone_number']);
        });

        Schema::create('wa_contacts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->unsignedBigInteger('wa_device_id')->nullable();
            $t->string('phone_number', 30);
            $t->string('name', 120)->nullable();
            $t->string('email', 180)->nullable();
            $t->json('labels')->nullable();
            $t->string('status', 20)->default('unknown');
            $t->boolean('is_duplicate')->default(false);
            $t->string('source', 60)->nullable();
            $t->timestamp('last_messaged_at')->nullable();
            $t->unsignedInteger('message_count')->default(0);
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'phone_number']);
        });

        Schema::create('wa_campaigns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->unsignedBigInteger('wa_device_id');
            $t->string('name', 150);
            $t->text('message');
            $t->string('media_url', 500)->nullable();
            $t->json('target_labels')->nullable();
            $t->json('recipients')->nullable();
            $t->string('status', 20)->default('draft');
            $t->timestamp('scheduled_at')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->unsignedSmallInteger('delay_min_seconds')->default(30);
            $t->unsignedSmallInteger('delay_max_seconds')->default(120);
            $t->unsignedSmallInteger('pause_every')->default(20);
            $t->unsignedSmallInteger('pause_duration_seconds')->default(300);
            $t->unsignedInteger('total_recipients')->default(0);
            $t->unsignedInteger('sent_count')->default(0);
            $t->unsignedInteger('failed_count')->default(0);
            $t->unsignedInteger('skipped_count')->default(0);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });

        Schema::create('wa_auto_replies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->unsignedBigInteger('wa_device_id');
            $t->string('name', 120);
            $t->string('type', 20)->default('contains');
            $t->string('trigger', 500)->nullable();
            $t->text('reply');
            $t->boolean('use_ai')->default(false);
            $t->text('ai_instructions')->nullable();
            $t->string('send_media_url', 500)->nullable();
            $t->unsignedSmallInteger('priority')->default(0);
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('hit_count')->default(0);
            $t->timestamp('last_hit_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'wa_device_id', 'is_active']);
            $t->index(['wa_device_id', 'priority']);
        });

        Schema::create('wa_order_notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->unsignedBigInteger('wa_device_id');
            $t->string('name', 150);
            $t->string('trigger', 30)->default('thank_you');
            $t->string('source_platform', 40)->nullable();
            $t->text('message_template');
            $t->string('media_url', 500)->nullable();
            $t->unsignedSmallInteger('delay_minutes')->default(0);
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('send_count')->default(0);
            $t->timestamp('last_sent_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'trigger']);
            $t->index(['wa_device_id', 'is_active']);
        });

        Schema::create('wa_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->unsignedBigInteger('wa_device_id')->nullable();
            $t->unsignedBigInteger('wa_contact_id')->nullable();
            $t->unsignedBigInteger('wa_campaign_id')->nullable();
            $t->string('direction', 20)->default('outgoing');
            $t->string('kind', 20)->default('manual');
            $t->string('to_number', 30)->nullable();
            $t->string('from_number', 30)->nullable();
            $t->text('body')->nullable();
            $t->string('media_url', 500)->nullable();
            $t->string('status', 20)->default('queued');
            $t->string('external_id', 120)->nullable();
            $t->text('error_message')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'kind']);
            $t->index(['wa_campaign_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
        Schema::dropIfExists('wa_order_notifications');
        Schema::dropIfExists('wa_auto_replies');
        Schema::dropIfExists('wa_campaigns');
        Schema::dropIfExists('wa_contacts');
        Schema::dropIfExists('wa_devices');
    }
};
