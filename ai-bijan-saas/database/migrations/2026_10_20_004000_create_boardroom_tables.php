<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // B-Boardroom Sessions (BAB 20)
        if (!Schema::hasTable('boardroom_sessions')) {
            Schema::create('boardroom_sessions', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
                $table->text('question');
                $table->string('category', 50)->default('default')->index();
                $table->json('context')->nullable();
                $table->unsignedTinyInteger('current_step')->default(1);
                $table->json('agents_involved')->nullable();
                $table->string('status', 40)->default('draft')->index();
                $table->boolean('conflict_detected')->default(false);
                $table->json('conflict_details')->nullable();
                $table->unsignedTinyInteger('review_round')->default(0);
                $table->json('summary')->nullable();
                $table->json('options')->nullable();
                $table->string('decision', 40)->nullable()->index();
                $table->foreignId('decision_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('decision_reason')->nullable();
                $table->datetime('decision_at')->nullable();
                $table->datetime('closed_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'created_at']);
                $table->index(['tenant_id', 'current_step']);
            });
        }

        // B-Boardroom Findings (dapatan ejen)
        if (!Schema::hasTable('boardroom_findings')) {
            Schema::create('boardroom_findings', function (Blueprint $table) {
                $table->id();
                $table->foreignUlid('session_id')->constrained('boardroom_sessions')->onDelete('cascade');
                $table->string('agent_key', 50)->index();
                $table->enum('finding_type', [
                    'fact', 'estimate', 'assumption', 'forecast', 'recommendation',
                ])->index();
                $table->string('title')->nullable();
                $table->text('content');
                $table->json('assumptions')->nullable();
                $table->json('evidence')->nullable();
                $table->json('risks')->nullable();
                $table->string('source_reference')->nullable();
                $table->decimal('confidence_score', 3, 2)->default(0.80);
                $table->boolean('requires_review')->default(false)->index();
                $table->datetime('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['session_id', 'finding_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('boardroom_findings');
        Schema::dropIfExists('boardroom_sessions');
    }
};
