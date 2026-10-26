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
        Schema::create('industry_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('industry_key')->unique(); // e.g., 'retail', 'manufacturing'
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('enabled_modules')->nullable(); // Which modules are enabled
            $table->string('dashboard_layout')->nullable(); // Default dashboard type
            $table->json('settings')->nullable(); // Industry-specific settings
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('industry_profiles');
    }
};
