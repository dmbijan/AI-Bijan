<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blast Engine (BAB 82 / Fasa 2) — kempen, mesej, penindihan, deliverability.
 *
 * Prinsip: Tiada mesej boleh dihantar tanpa (1) consent prospect,
 * (2) kelulusan kempen, (3) had kadar, (4) senarai penindihan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Kempen blast
        Schema::create('blast_campaigns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name', 180);
            $t->enum('channel', ['email', 'sms', 'whatsapp', 'video', 'multi'])->default('email');
            $t->string('subject', 250)->nullable();
            $t->longText('body')->nullable();
            $t->unsignedBigInteger('media_asset_id')->nullable();
            $t->foreignId('sequence_id')->nullable();

            $t->enum('status', [
                'draft', 'review', 'approved', 'scheduled',
                'running', 'paused', 'completed', 'cancelled',
            ])->default('draft');

            // Kelulusan — berasingan daripada status kempen.
            $t->enum('approval_state', ['not_required', 'pending', 'approved', 'rejected'])->default('not_required');
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();

            $t->timestamp('scheduled_at')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();

            // Tahap autonomy tenant semasa kempen dicipta.
            $t->integer('autonomy_level')->default(0);

            $t->json('stats')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'scheduled_at']);
        });

        // Mesej blast
        Schema::create('blast_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('campaign_id')->constrained('blast_campaigns')->cascadeOnDelete();
            $t->foreignId('prospect_id')->nullable()->constrained('prospects')->nullOnDelete();
            $t->foreignId('contact_id')->nullable();
            $t->enum('channel', ['email', 'sms', 'whatsapp', 'video'])->index();
            $t->string('recipient', 190);
            $t->string('subject', 250)->nullable();
            $t->longText('body')->nullable();
            $t->unsignedBigInteger('media_asset_id')->nullable();

            $t->enum('status', [
                'queued', 'sending', 'sent', 'delivered', 'bounced',
                'failed', 'suppressed', 'skipped', 'unsubscribed',
            ])->default('queued')->index();

            $t->string('provider_message_id', 190)->nullable();
            $t->text('error')->nullable();
            $t->integer('attempts')->default(0);
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamp('opened_at')->nullable();
            $t->timestamp('clicked_at')->nullable();
            $t->timestamp('replied_at')->nullable();
            $t->string('bounce_reason', 120)->nullable();
            $t->timestamp('complaint_at')->nullable();
            $t->integer('latency_ms')->nullable();
            $t->json('metadata')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['campaign_id', 'status']);
            $t->index(['tenant_id', 'recipient']);
            $t->index('provider_message_id');
        });

        // Senarai penindihan
        Schema::create('blast_suppressions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('hash', 64);
            $t->enum('channel', ['email', 'sms', 'whatsapp', 'video'])->default('email');
            $t->string('email', 190)->nullable();
            $t->string('phone', 30)->nullable();
            $t->enum('reason', ['bounce', 'complaint', 'unsubscribe', 'manual', 'hard_bounce'])->default('manual');
            $t->string('source', 80)->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'hash']);
            $t->unique(['tenant_id', 'hash', 'channel']);
        });

        // Langganan
        Schema::create('blast_unsubscribes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('contact_id')->nullable();
            $t->foreignId('prospect_id')->nullable()->constrained('prospects')->nullOnDelete();
            $t->enum('channel', ['email', 'sms', 'whatsapp', 'video'])->default('email');
            $t->string('email', 190)->nullable();
            $t->string('phone', 30)->nullable();
            $t->string('email_hash', 64)->nullable();
            $t->string('phone_hash', 64)->nullable();
            $t->string('token', 80)->nullable()->unique();
            $t->string('reason', 250)->nullable();
            $t->string('source', 80)->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'email_hash']);
            $t->index(['tenant_id', 'phone_hash']);
        });

        // Deliverability per domain
        Schema::create('blast_deliverability', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('domain', 190);
            $t->integer('daily_quota')->default(500);
            $t->integer('sent')->default(0);
            $t->integer('delivered')->default(0);
            $t->integer('bounced')->default(0);
            $t->integer('complaints')->default(0);
            $t->integer('opened')->default(0);
            $t->integer('clicked')->default(0);
            $t->integer('reputation_score')->default(100);
            $t->timestamp('last_reset_at')->nullable();
            $t->boolean('is_paused')->default(false);
            $t->string('pause_reason', 250)->nullable();
            $t->timestamps();

            $t->unique(['tenant_id', 'domain']);
        });

        // Webhook events
        Schema::create('blast_webhook_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('provider', 60);
            $t->string('event_type', 80);
            $t->string('event_id', 190)->nullable();
            $t->json('payload')->nullable();
            $t->boolean('signature_valid')->default(false);
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'processed_at']);
            $t->index(['provider', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blast_webhook_events');
        Schema::dropIfExists('blast_deliverability');
        Schema::dropIfExists('blast_unsubscribes');
        Schema::dropIfExists('blast_suppressions');
        Schema::dropIfExists('blast_messages');
        Schema::dropIfExists('blast_campaigns');
    }
};
