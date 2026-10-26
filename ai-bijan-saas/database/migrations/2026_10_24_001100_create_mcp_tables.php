<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MCP Tools — alat yang boleh dipanggil oleh AI (MCP protocol)
        Schema::create('mcp_tools', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name')->unique();                 // nama alat MCP
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('source_module')->nullable();     // modul AI BIJAN sumber (contoh: inventory)
            $table->string('action')->nullable();             // pada modul
            $table->string('method')->nullable();             // method service
            $table->json('parameters_schema')->nullable();    // JSON schema parameter
            $table->json('permission_scope')->nullable();     // skop kebenaran
            $table->string('status')->default('draft');       // draft|active|archived
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_destructive')->default(false);
            $table->unsignedInteger('call_count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });

        // MCP Resources — sumber data boleh dibaca AI
        Schema::create('mcp_resources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('uri')->unique();                  // crm://customers/123
            $table->string('name');
            $table->string('mime_type')->default('application/json');
            $table->string('source_table')->nullable();
            $table->json('columns')->nullable();
            $table->json('row_filter')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('access_count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'uri']);
        });

        // MCP Prompts — templat prompt
        Schema::create('mcp_prompts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('template');                         // templat prompt
            $table->json('arguments_schema')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('use_count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });

        // MCP Sessions — sesi interaksi AI
        Schema::create('mcp_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('client_name')->nullable();        // Claude Desktop, AI BIJAN AI, dll
            $table->string('client_version')->nullable();
            $table->string('transport')->default('http');      // http|stdio
            $table->string('status')->default('active');      // active|closed
            $table->json('capabilities')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        // MCP Executions — jejak setiap panggilan alat
        Schema::create('mcp_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('mcp_tool_id')->nullable()->index();
            $table->unsignedBigInteger('mcp_session_id')->nullable()->index();
            $table->string('tool_name');
            $table->json('arguments')->nullable();
            $table->json('result')->nullable();
            $table->string('status')->default('pending');     // pending|awaiting_approval|approved|rejected|executed|failed
            $table->string('approval_status')->default('not_required'); // not_required|pending|approved|rejected
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // MCP Audit Logs — audit trail
        Schema::create('mcp_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('mcp_execution_id')->nullable()->index();
            $table->string('action');                         // tool_called|tool_approved|tool_rejected|tool_executed|resource_read
            $table->string('actor_type')->default('ai');      // ai|human|system
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('context')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcp_audit_logs');
        Schema::dropIfExists('mcp_executions');
        Schema::dropIfExists('mcp_sessions');
        Schema::dropIfExists('mcp_prompts');
        Schema::dropIfExists('mcp_resources');
        Schema::dropIfExists('mcp_tools');
    }
};
