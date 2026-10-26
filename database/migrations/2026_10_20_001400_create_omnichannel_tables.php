<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omnichannel_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // whatsapp, email, facebook_messenger, instagram, telegram, live_chat, sms, twitter_dm
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false);
            $table->string('display_name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email_address')->nullable();
            $table->string('social_handle')->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->unsignedInteger('rate_limit')->nullable();
            $table->string('status')->default('disconnected'); // connected, disconnected, connecting, error
            $table->timestamp('last_sync_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tenant_id');
            $table->index('type');
            $table->index('is_active');
            $table->index('is_primary');
        });

        Schema::create('omnichannel_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('omnichannel_channels')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('omnichannel_contacts')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->string('status')->default('open'); // open, pending, archived
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('source_contact_identifier')->nullable();
            $table->string('source_contact_name')->nullable();
            $table->string('source_contact_email')->nullable();
            $table->string('source_contact_phone')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('tags')->nullable();
            $table->json('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tenant_id');
            $table->index('status');
            $table->index('priority');
            $table->index('channel_id');
            $table->index('agent_id');
            $table->index(['source_contact_email', 'source_contact_phone']);
        });

        Schema::create('omnichannel_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('omnichannel_conversations')->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('omnichannel_channels')->nullOnDelete();
            $table->string('sender_type'); // contact, agent, bot, system
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('direction'); // inbound, outbound
            $table->string('message_type')->default('text'); // text, image, video, audio, document, sticker, location, template, interactive
            $table->json('content');
            $table->json('attachments')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status')->default('sent'); // sent, delivered, read, failed
            $table->string('external_message_id')->nullable();
            $table->string('external_reply_to')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('conversation_id');
            $table->index('direction');
            $table->index('message_type');
            $table->index('status');
            $table->index('tenant_id');
        });

        Schema::create('omnichannel_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('crm_contact_id')->nullable();
            $table->foreignId('source_channel_id')->nullable()->constrained('omnichannel_channels')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('social_handle')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('company')->nullable();
            $table->json('tags')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->unsignedInteger('total_conversations')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tenant_id');
            $table->index('email');
            $table->index('phone_number');
            $table->index('source_channel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omnichannel_contacts');
        Schema::dropIfExists('omnichannel_messages');
        Schema::dropIfExists('omnichannel_conversations');
        Schema::dropIfExists('omnichannel_channels');
    }
};
