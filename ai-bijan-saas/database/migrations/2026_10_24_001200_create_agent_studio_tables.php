<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ejen — kad peribadi AI
        Schema::create('agent_blueprints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('key')->unique();                    // kunci unik ejen
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->default('general');     // sales|support|ops|finance|hr|growth
            $table->string('persona')->nullable();               // peribadi ejen
            $table->text('system_prompt')->nullable();
            $table->json('capabilities')->nullable();           // kebolekan
            $table->json('knowledge_sources')->nullable();      // sumber pengetahuan
            $table->json('autonomy')->nullable();               // tahap otonomi
            $table->json('escalation_rules')->nullable();
            $table->string('model')->default('default');         // model AI
            $table->decimal('temperature', 5, 2)->default(0.70);
            $table->unsignedInteger('max_tokens')->default(2048);
            $table->string('status')->default('draft');          // draft|active|paused|archived
            $table->boolean('is_published')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedInteger('run_count')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
        });

        // Ikatan alat — ejen boleh guna alat apa
        Schema::create('agent_tool_bindings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('agent_blueprint_id')->index();
            $table->string('tool_key');                          // kunci alat (MCP atau dalaman)
            $table->string('tool_type')->default('internal');    // internal|mcp|http
            $table->json('tool_config')->nullable();
            $table->string('permission_level')->default('read'); // read|write|admin
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'agent_blueprint_id', 'tool_key']);
        });

        // Ikatan pengetahuan — sumber RAG untuk ejen
        Schema::create('agent_knowledge_bindings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('agent_blueprint_id')->index();
            $table->string('source_type');                      // document|url|faq|text|vector_store
            $table->string('source_ref');                       // rujukan (id / url / kunci)
            $table->string('title')->nullable();
            $table->json('meta')->nullable();
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        // Ujian ejen — sejarah ujian & benchmark
        Schema::create('agent_test_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('agent_blueprint_id')->index();
            $table->string('name');
            $table->text('prompt')->nullable();
            $table->text('expected_output')->nullable();
            $table->text('actual_output')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('passed')->default(false);
            $table->json('metrics')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('tokens_used')->nullable();
            $table->string('status')->default('pending');         // pending|running|passed|failed
            $table->unsignedInteger('cost_credits')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // Pelarian ejen — rekod ejen
        Schema::create('agent_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('agent_blueprint_id')->index();
            $table->string('agent_key')->nullable();
            $table->string('trigger_type')->default('manual');   // manual|schedule|event|api
            $table->json('input')->nullable();
            $table->text('output')->nullable();
            $table->string('status')->default('pending');         // pending|running|completed|failed|escalated
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('cost_credits')->default(0);
            $table->json('tools_used')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('escalated')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_runs');
        Schema::dropIfExists('agent_test_runs');
        Schema::dropIfExists('agent_knowledge_bindings');
        Schema::dropIfExists('agent_tool_bindings');
        Schema::dropIfExists('agent_blueprints');
    }
};
