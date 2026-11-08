<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 250);
            $table->string('slug', 280);
            $table->text('description')->nullable();
            $table->string('kind', 20)->default('document');
            $table->string('icon', 40)->nullable();
            $table->string('color', 20)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'kind']);
            $table->index('parent_id');
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('workspace_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('space_id')->constrained('workspace_spaces')->cascadeOnDelete();
            $table->string('title', 250)->default('Halaman tanpa tajuk');
            $table->string('slug', 280);
            $table->string('icon', 40)->nullable();
            $table->string('cover_url', 500)->nullable();
            $table->longText('content')->nullable();
            $table->boolean('is_full_width')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_edited_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'space_id']);
            $table->index(['space_id', 'sort_order']);
        });

        Schema::create('workspace_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('page_id')->constrained('workspace_pages')->cascadeOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('type', 20)->default('paragraph');
            $table->longText('content')->nullable();
            $table->json('data')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_checked')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['page_id', 'sort_order']);
            $table->index('parent_id');
        });

        Schema::create('workspace_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('page_id')->constrained('workspace_pages')->cascadeOnDelete();
            $table->unsignedBigInteger('block_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('body');
            $table->boolean('is_resolved')->default(false);
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'page_id']);
            $table->index(['page_id', 'is_resolved']);
        });

        Schema::create('workspace_page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('page_id')->constrained('workspace_pages')->cascadeOnDelete();
            $table->string('title', 250)->nullable();
            $table->longText('content')->nullable();
            $table->json('blocks_snapshot')->nullable();
            $table->integer('version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['page_id', 'version']);
        });

        Schema::create('workspace_databases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('space_id')->constrained('workspace_spaces')->cascadeOnDelete();
            $table->string('name', 250);
            $table->text('description')->nullable();
            $table->string('icon', 40)->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'space_id']);
        });

        Schema::create('workspace_database_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('database_id')->constrained('workspace_databases')->cascadeOnDelete();
            $table->string('name', 250);
            $table->string('type', 20)->default('grid');
            $table->json('columns')->nullable();
            $table->json('filters')->nullable();
            $table->json('sorts')->nullable();
            $table->string('group_by', 60)->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['database_id', 'sort_order']);
        });

        Schema::create('workspace_database_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('database_id')->constrained('workspace_databases')->cascadeOnDelete();
            $table->unsignedBigInteger('view_id')->nullable();
            $table->json('values')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['database_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_database_rows');
        Schema::dropIfExists('workspace_database_views');
        Schema::dropIfExists('workspace_databases');
        Schema::dropIfExists('workspace_page_versions');
        Schema::dropIfExists('workspace_comments');
        Schema::dropIfExists('workspace_blocks');
        Schema::dropIfExists('workspace_pages');
        Schema::dropIfExists('workspace_spaces');
    }
};
