<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_inboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 170)->nullable();
            $table->string('channel_type', 30)->default('chat');
            $table->string('provider', 60)->nullable();
            $table->text('description')->nullable();
            $table->string('assignment_strategy', 30)->default('round_robin');
            $table->unsignedInteger('sla_response_minutes')->default(60);
            $table->unsignedInteger('sla_resolution_minutes')->default(1440);
            $table->json('business_hours')->nullable();
            $table->boolean('working_hours_only')->default(false);
            $table->boolean('ai_enabled')->default(true);
            $table->decimal('ai_confidence_threshold', 4, 3)->default(0.750);
            $table->boolean('auto_assign_enabled')->default(true);
            $table->unsignedBigInteger('default_team_id')->nullable();
            $table->text('welcome_message')->nullable();
            $table->string('status', 20)->default('active');
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('inbox_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('shared_inbox_id')->constrained('shared_inboxes')->cascadeOnDelete();
            $table->string('conversation_no', 30);
            $table->string('contact_name', 120)->nullable();
            $table->string('contact_email', 180)->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->string('channel', 30)->default('chat');
            $table->string('subject', 250)->nullable();
            $table->string('status', 20)->default('open');
            $table->string('priority', 20)->default('normal');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->json('tags')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('sla_response_due_at')->nullable();
            $table->timestamp('sla_resolution_due_at')->nullable();
            $table->boolean('sla_response_breached')->default(false);
            $table->boolean('sla_resolution_breached')->default(false);
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->tinyInteger('rating')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            $table->index(['shared_inbox_id', 'status']);
            $table->index(['tenant_id', 'assigned_to']);
            $table->index(['tenant_id', 'sla_response_due_at']);
        });

        Schema::create('inbox_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('inbox_conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_type', 20)->default('contact');
            $table->string('type', 20)->default('incoming');
            $table->longText('content');
            $table->json('attachments')->nullable();
            $table->boolean('is_ai_generated')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'sent_at']);
        });

        Schema::create('canned_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('shared_inbox_id')->nullable()->constrained('shared_inboxes')->cascadeOnDelete();
            $table->string('short_code', 60);
            $table->string('title', 150)->nullable();
            $table->longText('content');
            $table->string('category', 60)->nullable();
            $table->string('language', 5)->default('ms');
            $table->unsignedInteger('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->index(['shared_inbox_id', 'short_code']);
        });

        Schema::create('inbox_macros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('shared_inbox_id')->constrained('shared_inboxes')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->json('actions');
            $table->unsignedInteger('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['shared_inbox_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_macros');
        Schema::dropIfExists('canned_responses');
        Schema::dropIfExists('inbox_messages');
        Schema::dropIfExists('inbox_conversations');
        Schema::dropIfExists('shared_inboxes');
    }
};
