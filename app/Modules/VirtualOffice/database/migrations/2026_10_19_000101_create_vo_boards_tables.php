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
        Schema::create('vo_boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('visibility', ['private', 'team', 'public'])->default('team');
            $table->string('color')->default('#3B82F6'); // Default blue
            $table->json('settings')->nullable(); // Board-specific settings
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('vo_board_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('vo_boards')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['owner', 'editor', 'viewer'])->default('editor');
            $table->timestamps();

            $table->unique(['board_id', 'user_id']);
        });

        Schema::create('vo_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('vo_boards')->cascadeOnDelete();
            $table->string('name');
            $table->integer('position')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('board_id');
        });

        Schema::create('vo_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('vo_boards')->cascadeOnDelete();
            $table->foreignId('column_id')->nullable()->constrained('vo_columns')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('vo_agents')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['todo', 'in_progress', 'review', 'done', 'cancelled'])->default('todo');
            $table->date('due_date')->nullable();
            $table->integer('position')->default(0);
            $table->json('labels')->nullable(); // Tags/labels array
            $table->json('attachments')->nullable(); // File attachments metadata
            $table->json('checklist')->nullable(); // Checklist items
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('board_id');
            $table->index('column_id');
            $table->index('assigned_to');
            $table->index('status');
        });

        Schema::create('vo_card_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('vo_cards')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index('card_id');
        });

        Schema::create('vo_card_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('vo_cards')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // created, moved, status_changed, assigned, etc.
            $table->json('changes')->nullable(); // Before/after values
            $table->timestamps();

            $table->index('card_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vo_card_history');
        Schema::dropIfExists('vo_card_comments');
        Schema::dropIfExists('vo_cards');
        Schema::dropIfExists('vo_columns');
        Schema::dropIfExists('vo_board_members');
        Schema::dropIfExists('vo_boards');
    }
};
