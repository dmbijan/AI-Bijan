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
        // AI Agents table - tenant-specific agent configurations
        Schema::create('ai_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('agent_key')->index(); // e.g., 'ceo', 'cfo', 'cmo'
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('role', 50)->nullable();
            $table->text('system_prompt')->nullable();
            $table->json('tools_json')->nullable();
            $table->json('budget_limits_json')->nullable();
            $table->json('approval_required_json')->nullable();
            $table->json('data_access_json')->nullable();
            $table->boolean('memory_enabled')->default(true);
            $table->boolean('proactive_alerts')->default(false);
            $table->enum('status', ['active', 'disabled', 'deprecated'])->default('active');
            $table->string('current_model')->nullable();
            $table->unsignedBigInteger('total_runs')->default(0);
            $table->decimal('total_cost_usd', 10, 4)->default(0);
            $table->unsignedBigInteger('total_tokens')->default(0);
            $table->timestamps();
            
            $table->unique(['tenant_id', 'agent_key']);
        });

        // AI Agent Sessions table - tracks multi-agent sessions
        Schema::create('ai_agent_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('task_type')->index();
            $table->text('objective')->nullable();
            $table->json('agents_involved_json');
            $table->json('session_context_json')->nullable();
            $table->enum('status', ['running', 'completed', 'failed', 'cancelled'])->default('running');
            $table->text('result_summary')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->decimal('cost_usd', 10, 4)->default(0);
            $table->unsignedInteger('tokens_used')->default(0);
            $table->timestamps();
            
            $table->index(['tenant_id', 'created_at']);
        });

        // AI Agent Executions table - individual agent task executions
        Schema::create('ai_agent_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('session_id')->nullable()->constrained('ai_agent_sessions')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('agent_key')->index();
            $table->string('task_type')->index();
            $table->text('input_description')->nullable();
            $table->json('input_data')->nullable();
            $table->text('output')->nullable();
            $table->enum('status', ['queued', 'running', 'completed', 'failed', 'cancelled'])->default('queued');
            $table->text('error_message')->nullable();
            $table->decimal('cost_usd', 10, 4)->default(0);
            $table->unsignedInteger('tokens_used')->default(0);
            $table->unsignedInteger('execution_time_ms')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'agent_key', 'created_at']);
        });

        // AI Tool Executions table - logs all tool calls
        Schema::create('ai_tool_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('execution_id')->nullable()->constrained('ai_agent_executions')->onDelete('set null');
            $table->string('agent_key')->index();
            $table->string('tool_name')->index();
            $table->json('parameters_json')->nullable();
            $table->text('output')->nullable();
            $table->text('error')->nullable();
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->unsignedInteger('execution_time_ms')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'tool_name', 'created_at']);
        });

        // AI Conversations table - conversation history
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('agent_key')->index();
            $table->json('messages_json');
            $table->unsignedInteger('message_count')->default(0);
            $table->decimal('cost_usd', 10, 4)->default(0);
            $table->unsignedInteger('tokens_used')->default(0);
            $table->timestamps();
            
            $table->index(['tenant_id', 'agent_key', 'created_at']);
        });

        // AI Business Profiles table - agent memory context
        Schema::create('ai_business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('business_name')->nullable();
            $table->string('industry_code')->nullable();
            $table->string('business_model')->default('retail');
            $table->json('products_json')->nullable();
            $table->json('customers_summary_json')->nullable();
            $table->json('org_structure_json')->nullable();
            $table->text('custom_instructions')->nullable();
            $table->string('language_pref')->default('ms');
            $table->string('tone_pref')->default('formal');
            $table->string('report_format')->default('standard');
            $table->string('preferred_model')->nullable();
            $table->timestamps();
            
            $table->unique('tenant_id');
        });

        // AI Derived Insights table - learned patterns and insights
        Schema::create('ai_derived_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('agent_key')->index();
            $table->string('insight_type')->index(); // e.g., 'trend', 'anomaly', 'opportunity'
            $table->json('data_json');
            $table->decimal('confidence_score', 3, 2)->default(0.8);
            $table->string('source')->default('ai_agent');
            $table->boolean('reviewed')->default(false);
            $table->text('review_notes')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'insight_type', 'created_at']);
        });

        // AI Governance Audits table - audit trail for governance
        Schema::create('ai_governance_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('agent_key')->index();
            $table->string('action')->index();
            $table->enum('result', ['allowed', 'blocked', 'violation'])->default('allowed');
            $table->json('details_json')->nullable();
            $table->string('violation_reason')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'result', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_governance_audits');
        Schema::dropIfExists('ai_derived_insights');
        Schema::dropIfExists('ai_business_profiles');
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('ai_tool_executions');
        Schema::dropIfExists('ai_agent_executions');
        Schema::dropIfExists('ai_agent_sessions');
        Schema::dropIfExists('ai_agents');
    }
};
