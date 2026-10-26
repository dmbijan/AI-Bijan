<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// B-Projects, B-Assets, B-Helpdesk.
return new class extends Migration
{
    public function up(): void
    {
        // ---------------- B-Projects
        Schema::create('projects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('code', 30);
            $t->string('name');
            $t->text('description')->nullable();
            $t->string('client')->nullable();
            $t->enum('status', ['perancangan', 'aktif', 'ditangguh', 'selesai', 'dibatalkan'])->default('perancangan');
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->decimal('budget', 18, 2)->nullable();
            $t->decimal('hourly_rate', 18, 2)->nullable();
            $t->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['tenant_id', 'code']);
        });
        Schema::create('project_members', function (Blueprint $t) {
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->primary(['project_id', 'user_id']);
        });
        Schema::create('tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('tasks')->nullOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->enum('status', ['todo', 'doing', 'review', 'done'])->default('todo');
            $t->enum('priority', ['rendah', 'sederhana', 'tinggi', 'kritikal'])->default('sederhana');
            $t->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $t->date('start_date')->nullable();
            $t->date('due_date')->nullable();
            $t->decimal('estimate_hours', 8, 2)->nullable();
            $t->boolean('is_milestone')->default(false);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->index(['tenant_id', 'project_id', 'status']);
        });
        Schema::create('task_dependencies', function (Blueprint $t) {
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $t->foreignId('depends_on_id')->constrained('tasks')->cascadeOnDelete();
            $t->primary(['task_id', 'depends_on_id']);
        });
        Schema::create('task_comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('task_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->text('body');
            $t->string('file_ulid', 26)->nullable();
            $t->timestamps();
        });
        Schema::create('timesheets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->constrained();
            $t->date('work_date');
            $t->decimal('hours', 6, 2);
            $t->string('note')->nullable();
            $t->boolean('billable')->default(true);
            $t->enum('status', ['draf', 'dihantar', 'diluluskan', 'ditolak'])->default('dihantar');
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['tenant_id', 'user_id', 'work_date']);
        });

        // ---------------- B-Assets
        Schema::create('asset_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unsignedInteger('useful_life_months');
            $t->enum('method', ['garis_lurus', 'baki_berkurangan'])->default('garis_lurus');
            $t->decimal('residual_pct', 6, 2)->default(0);
            $t->timestamps();
        });
        Schema::create('assets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('tag', 30);
            $t->string('name');
            $t->foreignId('category_id')->constrained('asset_categories');
            $t->string('serial_no', 80)->nullable();
            $t->string('location')->nullable();
            $t->foreignId('custodian_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('supplier')->nullable();
            $t->date('purchase_date');
            $t->date('depreciation_start');
            $t->decimal('cost', 18, 2);
            $t->decimal('residual_value', 18, 2)->default(0);
            $t->unsignedInteger('useful_life_months');
            $t->enum('method', ['garis_lurus', 'baki_berkurangan']);
            $t->date('warranty_until')->nullable();
            $t->enum('status', ['aktif', 'dalam_baik_pulih', 'dilupus'])->default('aktif');
            $t->date('disposed_on')->nullable();
            $t->decimal('disposal_proceeds', 18, 2)->nullable();
            $t->decimal('disposal_gain_loss', 18, 2)->nullable();
            $t->string('disposal_reason')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'tag']);
        });
        Schema::create('asset_depreciations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $t->char('period', 7); // YYYY-MM
            $t->decimal('amount', 18, 2);
            $t->decimal('accumulated', 18, 2);
            $t->decimal('book_value', 18, 2);
            $t->boolean('posted')->default(false);
            $t->timestamp('posted_at')->nullable();
            $t->unique(['asset_id', 'period']);
        });
        Schema::create('asset_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $t->string('from_location')->nullable();
            $t->string('to_location')->nullable();
            $t->foreignId('from_custodian_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('to_custodian_id')->nullable()->constrained('users')->nullOnDelete();
            $t->date('moved_on');
            $t->string('note')->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('asset_maintenances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['pencegahan', 'pembaikan', 'pemeriksaan']);
            $t->date('scheduled_on');
            $t->date('done_on')->nullable();
            $t->decimal('cost', 18, 2)->nullable();
            $t->string('vendor')->nullable();
            $t->text('notes')->nullable();
            $t->unsignedInteger('repeat_months')->nullable();
            $t->enum('status', ['dijadual', 'selesai', 'dibatalkan'])->default('dijadual');
            $t->timestamps();
            $t->index(['tenant_id', 'status', 'scheduled_on']);
        });

        // ---------------- B-Helpdesk
        Schema::create('sla_policies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->enum('priority', ['rendah', 'sederhana', 'tinggi', 'kritikal']);
            $t->unsignedInteger('first_response_minutes');
            $t->unsignedInteger('resolution_minutes');
            $t->unique(['tenant_id', 'priority']);
        });
        Schema::create('ticket_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->foreignId('default_assignee_id')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::create('tickets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('number', 30);
            $t->string('subject');
            $t->text('description');
            $t->string('requester_name');
            $t->string('requester_email')->nullable();
            $t->string('requester_phone', 30)->nullable();
            $t->enum('channel', ['web', 'emel', 'telefon', 'whatsapp', 'kaunter', 'dalaman'])->default('web');
            $t->foreignId('category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $t->enum('priority', ['rendah', 'sederhana', 'tinggi', 'kritikal'])->default('sederhana');
            $t->enum('status', ['baru', 'dibuka', 'menunggu_pelanggan', 'selesai', 'ditutup'])->default('baru');
            $t->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('first_response_due')->nullable();
            $t->timestamp('resolution_due')->nullable();
            $t->timestamp('first_responded_at')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->boolean('response_breached')->default(false);
            $t->boolean('resolution_breached')->default(false);
            $t->unsignedTinyInteger('escalation_level')->default(0);
            $t->char('access_token_hash', 64)->nullable(); // portal pelanggan (token peribadi)
            $t->timestamps();
            $t->unique(['tenant_id', 'number']);
            $t->index(['tenant_id', 'status', 'resolution_due']);
        });
        Schema::create('ticket_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // null = pelanggan
            $t->boolean('internal')->default(false);
            $t->text('body');
            $t->timestamps();
        });
        Schema::create('kb_articles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('slug', 120);
            $t->text('body');
            $t->foreignId('category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $t->boolean('published')->default(false);
            $t->timestamps();
            $t->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        foreach (['kb_articles', 'ticket_messages', 'tickets', 'ticket_categories', 'sla_policies', 'asset_maintenances', 'asset_movements', 'asset_depreciations', 'assets', 'asset_categories',
            'timesheets', 'task_comments', 'task_dependencies', 'tasks', 'project_members', 'projects'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
