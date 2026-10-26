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
        Schema::create('vo_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->enum('type', ['human', 'ai', 'hybrid'])->default('human');
            $table->string('avatar_url')->nullable();
            $table->string('role')->nullable(); // e.g., "Developer", "Designer"
            $table->enum('status', ['idle', 'active', 'busy', 'offline'])->default('idle');
            $table->json('metadata')->nullable(); // Additional agent data
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->unique(['tenant_id', 'email'], 'vo_agents_tenant_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vo_agents');
    }
};
