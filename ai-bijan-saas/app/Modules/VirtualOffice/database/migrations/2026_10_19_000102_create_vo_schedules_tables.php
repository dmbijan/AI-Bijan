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
        Schema::create('vo_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('vo_agents')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('type', ['meeting', 'standup', 'review', 'planning', 'social', 'other'])->default('meeting');
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_pattern')->nullable(); // RRULE format
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->json('attendees')->nullable(); // User IDs or agent IDs
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('agent_id');
            $table->index(['start_time', 'end_time']);
        });

        Schema::create('vo_schedule_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('vo_schedules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('response', ['pending', 'accepted', 'declined', 'tentative'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vo_schedule_attendees');
        Schema::dropIfExists('vo_schedules');
    }
};
