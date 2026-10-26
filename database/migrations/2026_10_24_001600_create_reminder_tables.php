<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reminder & Notification Center (BAB 86 / Idea #22).
 *
 * ✅ Didapati daripada audit:
 * - ReStructure AI: "agent teams" nudge themselves; Hermes Agent: "scheduled
 *   automations with delivery to any platform" + "agent-curated memory with
 *   periodic nudges"
 * - AI BIJAN sudah ada `PersonalTask` (BAB 17) — TAPI tiada pusat peringatan
 *   merentas modul. Tenant tidak perlu menyemak 7 jadual untuk "bila nak hubungi pelanggan?".
 *
 * Ciri:
 * - Reminder terpusat: jenis (pesanan, bayaran, kontrak, invois, stok, follow-up)
 * - Setiap peringatan bolehikat kepada objek mana-mana modul (polymorphic)
 * - Saluran penghantaran (in-app, e-mel, WhatsApp, SMS, push)
 * - Jadwal berulang (cron), snooze, eskalasi
 * - Kotak masuk AI: AI pilihAPA yang penting
 * - Ketinggalan (overdue) dikesan automatik
 */
return new class extends Migration
{
    public function up(): void
    {
        // Definisi jenis peringatan (tenant boleh tambah sendiri)
        Schema::create('reminder_types', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('key');                              // payment_due|follow_up|contract_renewal|...
            $t->string('name');
            $t->string('category')->default('general');      // sales|finance|ops|hr|legal|compliance
            $t->string('source_module')->nullable();         // modul yang mencetuskan
            $t->text('description')->nullable();
            $t->string('default_priority')->default('normal'); // low|normal|high|urgent
            $t->integer('default_lead_hours')->default(24);  // berapa jam lebih awal
            $t->boolean('is_active')->default(true);
            $t->boolean('requires_ai_triage')->default(false);
            $t->json('default_channels')->nullable();         // in_app,email,whatsapp,sms,push
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->unique(['tenant_id', 'key']);
        });

        // Peringatan
        Schema::create('reminders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reminder_type_id')->nullable()->index();
            $t->string('type_key');                          // snapshot key (bukan bergantung FK)
            $t->string('title');
            $t->text('description')->nullable();
            $t->text('action_note')->nullable();             // apa yang perlu dibuat

            // Objek yang berkaitan (polymorphic)
            $t->string('subject_type')->nullable();          // model class
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->string('subject_label')->nullable();          // label paparan (cth: AO-2026-00001)

            // siapa perlu tahu
            $t->unsignedBigInteger('assigned_to')->nullable();
            $t->string('assigned_role')->nullable();

            // Kanal
            $t->json('channels')->nullable();                // ["in_app","email"]
            $t->string('status')->default('pending');         // pending|snoozed|sent|acknowledged|completed|cancelled|dismissed
            $t->string('priority')->default('normal');        // low|normal|high|urgent
            $t->timestamp('due_at')->nullable();
            $t->timestamp('snoozed_until')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('acknowledged_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamp('escalated_at')->nullable();
            $t->unsignedInteger('escalation_count')->default(0);
            $t->unsignedInteger('reminder_count')->default(1);  // berapa kali berulang

            // AI triage
            $t->decimal('ai_priority_score', 5, 2)->nullable();
            $t->text('ai_reasoning')->nullable();
            $t->boolean('ai_escalated')->default(false);

            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'due_at']);
            $t->index(['tenant_id', 'subject_type', 'subject_id']);
            $t->index(['tenant_id', 'assigned_to']);
        });

        // Jadwal berulang
        Schema::create('reminder_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reminder_type_id')->nullable();
            $t->string('type_key');
            $t->string('name');
            $t->string('frequency')->default('daily');      // once|daily|weekly|monthly|quarterly|annually
            $t->time('time_of_day')->nullable();
            $t->unsignedInteger('interval_count')->default(1);
            $t->unsignedInteger('day_of_week')->nullable();  // 1=Monday
            $t->unsignedInteger('day_of_month')->nullable();
            $t->string('timezone')->default('Asia/Kuala_Lumpur');
            $t->json('channels')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamp('last_run_at')->nullable();
            $t->timestamp('next_run_at')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        // Kotak masuk AI — satu lifetime untuk semua peringatan
        Schema::create('reminder_inbox', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reminder_id')->nullable()->index();
            $t->string('headline');
            $t->text('summary')->nullable();
            $t->string('urgency')->default('normal');
            $t->string('suggested_action')->nullable();
            $t->json('context')->nullable();
            $t->boolean('is_read')->default(false);
            $t->timestamp('read_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'is_read']);
        });

        // Rekod penghantaran
        Schema::create('reminder_deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reminder_id')->index();
            $t->string('channel');                           // in_app|email|whatsapp|sms|push
            $t->string('status')->default('sent');           // sent|delivered|failed|bounced
            $t->string('recipient')->nullable();
            $t->text('content')->nullable();
            $t->text('error_message')->nullable();
            $t->unsignedInteger('duration_ms')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'reminder_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_deliveries');
        Schema::dropIfExists('reminder_inbox');
        Schema::dropIfExists('reminder_schedules');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('reminder_types');
    }
};
