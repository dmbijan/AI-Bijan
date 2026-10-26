<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B-Executive AI (BAB 82 / Fasa 5) — preferensi, briefing, kelulusan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Preferensi eksekutif
        Schema::create('executive_preferences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('language', 8)->default('ms');
            // Tahap autonomy 0-4 (BAB 82.8)
            $t->integer('autonomy_level')->default(0);
            $t->string('briefing_time', 5)->default('07:30');
            $t->boolean('briefing_enabled')->default(true);
            $t->boolean('voice_enabled')->default(false);
            $t->json('notification_channels')->nullable();
            $t->json('preferences')->nullable();
            $t->timestamps();

            $t->unique(['tenant_id', 'user_id']);
        });

        // Briefing
        Schema::create('executive_briefings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->enum('type', ['daily', 'weekly', 'monthly'])->default('daily');
            $t->json('content');
            $t->timestamp('generated_at')->nullable();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'user_id', 'type']);
            $t->index(['tenant_id', 'generated_at']);
        });

        // Kelulusan tindakan AI (pintu kelulusan BAB 82.8.2)
        Schema::create('executive_approvals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action_type', 80);
            $t->string('target', 190)->nullable();
            $t->json('payload')->nullable();
            $t->enum('status', ['pending', 'approved', 'rejected', 'executed', 'expired'])->default('pending');
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('decided_at')->nullable();
            $t->text('reason')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('executive_approvals');
        Schema::dropIfExists('executive_briefings');
        Schema::dropIfExists('executive_preferences');
    }
};
