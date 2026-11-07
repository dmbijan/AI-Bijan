<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduling_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('slug', 60)->unique();
            $table->text('description')->nullable();
            $table->json('service_ids')->nullable();
            $table->json('provider_ids')->nullable();
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->unsignedInteger('interval_minutes')->default(15);
            $table->unsignedInteger('buffer_before_minutes')->default(0);
            $table->unsignedInteger('buffer_after_minutes')->default(0);
            $table->unsignedInteger('minimum_notice_minutes')->default(120);
            $table->unsignedInteger('max_booking_days')->default(60);
            $table->string('timezone', 60)->default('Asia/Kuala_Lumpur');
            $table->json('availability_rules')->nullable();
            $table->string('routing_type', 30)->default('collective');
            $table->string('routing_strategy', 30)->default('round_robin');
            $table->unsignedInteger('max_bookings_per_day')->default(8);
            $table->boolean('requires_confirmation')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->boolean('hide_booking_details')->default(false);
            $table->boolean('require_phone')->default(false);
            $table->boolean('require_name')->default(true);
            $table->string('redirect_url', 500)->nullable();
            $table->json('utm_params')->nullable();
            $table->unsignedInteger('booking_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('scheduling_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('scheduling_link_id')->constrained('scheduling_links')->cascadeOnDelete();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_available')->default(true);
            $table->boolean('is_booked')->default(false);
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('blocked_reason', 200)->nullable();
            $table->timestamps();

            $table->index(['scheduling_link_id', 'starts_at']);
            $table->index(['tenant_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduling_slots');
        Schema::dropIfExists('scheduling_links');
    }
};
