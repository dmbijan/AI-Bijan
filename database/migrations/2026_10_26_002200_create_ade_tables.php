<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BAB 92 — ADE (Agentic Development Environment).
 *
 * 4 fasa mengikut `obra/superpowers` (MIT):
 *   A1 Skills Engine       — skill boleh dipasang, dicetuskan, dikongsi
 *   A2 Worktree Isolation  — sandbox git worktree + baseline ujian
 *   A3 Sub-agent Coordinator — spawn N ejen serentak + semak silang
 *   A4 Review Surface      — diff walkthrough + test runner + preview
 *
 * Semua jadual prefix `ade_` untuk mengelakkan konflik dengan modul sedia ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── A1: SKILLS ENGINE ─────────────────────────────────────────────
        // Skill = kaedah kerja berulang (brainstorm → plan → TDD → review).
        // ⚠️ `key` BUKAN unique global — kunci yang sama boleh wujud sebagai
        // skill global (tenant_id NULL) DAN salinan tenant. K uniqueseness
        // dikendalikan oleh UNIQUE(tenant_id, key) — tetapi SQLite/MySQL
        // anggap NULL sebagai berbeza, jadi global skill perlu kunci berbeza.
        Schema::create('ade_skills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index(); // null = skill global (marketplace)
            $table->string('key')->index();                    // kunci skill (bukan unique global)
            $table->string('name');
            $table->string('category')->default('general');     // general|coding|content|ops|finance|marketing
            $table->text('description')->nullable();
            $table->text('system_prompt')->nullable();          // arahan untuk ejen
            $table->json('steps')->nullable();                   // urutan langkah (superpowers 7 langkah)
            $table->json('triggers')->nullable();                // pencetus automatik (kata kunci)
            $table->json('tools')->nullable();                   // alat dibenarkan
            $table->json('config')->nullable();
            $table->string('source')->default('builtin');       // builtin|marketplace|user|learned
            $table->string('origin_project')->default('obra/superpowers');
            $table->string('license')->default('MIT');
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_public')->default(false);        // tunjuk di marketplace
            $table->unsignedInteger('install_count')->default(0);
            $table->unsignedInteger('usage_count')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
            $table->index(['is_enabled', 'is_public']);
        });

        // Pencetus skill — rekod bila skill dicetuskan & hasilnya
        Schema::create('ade_skill_invocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('ade_skill_id')->index();
            $table->string('context_type')->default('manual');   // manual|automatic|workflow
            $table->string('context_ref')->nullable();
            $table->text('input_summary')->nullable();
            $table->text('output_summary')->nullable();
            $table->boolean('succeeded')->default(true);
            $table->decimal('score', 5, 2)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'ade_skill_id']);
        });

        // ── A2: WORKTREE ISOLATION ────────────────────────────────────────
        Schema::create('ade_sandboxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('type')->default('worktree');         // worktree|container|directory|none
            $table->string('status')->default('provisioning');    // provisioning|ready|in_use|dirty|cleaning|failed|removed
            $table->string('base_branch')->default('main');
            $table->string('branch_name')->nullable();
            $table->string('path')->nullable();                  // laluan mutlak worktree
            $table->string('repo_path')->nullable();
            $table->json('baseline')->nullable();                // keputusan ujian asas
            $table->json('meta')->nullable();
            $table->unsignedInteger('open_pr')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // ── A3: SUB-AGENT COORDINATOR ─────────────────────────────────────
        Schema::create('ade_coordinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name');
            $table->text('objective')->nullable();               // matlamat keseluruhan
            $table->string('status')->default('pending');         // pending|running|review|completed|failed|cancelled
            $table->unsignedTinyInteger('max_parallel')->default(4);
            $table->unsignedInteger('task_count')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->decimal('duration_ms', 12, 2)->nullable();
            $table->json('result')->nullable();                   // ringkasan keputusan
            $table->unsignedBigInteger('ade_sandbox_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('ade_coordination_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('ade_coordination_id')->index();
            $table->unsignedBigInteger('ade_sandbox_id')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('role')->default('implementer');       // implementer|tester|reviewer|researcher
            $table->string('status')->default('pending');          // pending|running|done|failed|skipped
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'ade_coordination_id']);
        });

        // ── A4: REVIEW SURFACE ────────────────────────────────────────────
        Schema::create('ade_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('ade_coordination_id')->nullable()->index();
            $table->unsignedBigInteger('ade_sandbox_id')->nullable()->index();
            $table->string('title');
            $table->string('status')->default('open');             // open|in_review|approved|changes_requested|rejected|merged
            $table->string('severity')->default('medium');         // low|medium|high|critical
            $table->json('diff')->nullable();                     // baris bertanda +/-
            $table->json('files')->nullable();                     // senarai fail berubah
            $table->json('test_results')->nullable();              // keputusan test runner
            $table->text('summary')->nullable();
            $table->json('findings')->nullable();                  // penemuan semakan
            $table->json('comments')->nullable();                  // komen baris
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->unsignedInteger('additions')->default(0);
            $table->unsignedInteger('deletions')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ade_reviews');
        Schema::dropIfExists('ade_coordination_tasks');
        Schema::dropIfExists('ade_coordinations');
        Schema::dropIfExists('ade_sandboxes');
        Schema::dropIfExists('ade_skill_invocations');
        Schema::dropIfExists('ade_skills');
    }
};
