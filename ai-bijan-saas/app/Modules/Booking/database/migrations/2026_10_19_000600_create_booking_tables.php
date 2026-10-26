<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // booking_services table
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->default(30);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->foreignId('provider_id')->nullable()->constrained('booking_service_providers')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->string('image_url')->nullable();
            $table->integer('max_capacity')->default(1);
            $table->boolean('requires_prep')->default(false);
            $table->integer('prep_time_minutes')->default(0);
            $table->text('cancellation_policy')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'is_active']);
        });

        // booking_service_providers table
        Schema::create('booking_service_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('specialization')->nullable();
            $table->text('bio')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('max_concurrent_appointments')->default(1);
            $table->integer('break_time_minutes')->default(15);
            $table->timestamps();
            
            $table->index('tenant_id');
            $table->index(['tenant_id', 'is_active']);
        });

        // bookings_slots table
        Schema::create('booking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('booking_services')->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('booking_service_providers')->nullOnDelete();
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['available', 'booked', 'blocked', 'maintenance'])->default('available');
            $table->integer('capacity')->default(1);
            $table->integer('booked_count')->default(0);
            $table->timestamps();
            
            $table->index(['tenant_id', 'slot_date']);
            $table->index(['provider_id', 'slot_date']);
        });

        // bookings_appointments table
        Schema::create('bookings_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('appointment_no')->unique();
            $table->foreignId('service_id')->nullable()->constrained('booking_services')->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('booking_service_providers')->nullOnDelete();
            $table->foreignId('slot_id')->nullable()->constrained('booking_slots')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('crm_clients')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show', 'rescheduled'])->default('draft');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('actual_start_time')->nullable();
            $table->dateTime('actual_end_time')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->dateTime('reminder_sent_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->integer('rescheduled_from')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['provider_id', 'start_time']);
            $table->index(['customer_id', 'start_time']);
        });

        // booking_reminders table
        Schema::create('booking_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('appointment_id')->constrained('bookings_appointments')->cascadeOnDelete();
            $table->enum('channel', ['sms', 'whatsapp', 'email', 'push'])->default('whatsapp');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->dateTime('scheduled_at');
            $table->dateTime('sent_at')->nullable();
            $table->string('template')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            
            $table->index(['appointment_id']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_reminders');
        Schema::dropIfExists('bookings_appointments');
        Schema::dropIfExists('booking_slots');
        Schema::dropIfExists('booking_service_providers');
        Schema::dropIfExists('booking_services');
    }
};
