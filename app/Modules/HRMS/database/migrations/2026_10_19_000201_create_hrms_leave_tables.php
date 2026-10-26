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
        Schema::create('hrms_leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('code')->unique(); // annual, medical, etc.
            $table->string('name');
            $table->integer('max_days_per_year')->nullable();
            $table->boolean('encashable')->default(false);
            $table->boolean('requires_document')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('hrms_leave_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('hrms_leave_types')->cascadeOnDelete();
            $table->integer('total_days');
            $table->integer('used_days')->default(0);
            $table->integer('carried_over_days')->default(0);
            $table->year('year');
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });

        Schema::create('hrms_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('hrms_leave_types')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_requested');
            $table->enum('half_day', ['full', 'morning', 'afternoon'])->default('full');
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'cancelled'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('status');
        });

        Schema::create('hrms_leave_request_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('hrms_leave_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // submitted, approved, rejected, cancelled
            $table->json('changes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('leave_request_id');
        });

        Schema::create('hrms_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave', 'holiday'])->default('present');
            $table->integer('minutes_late')->default(0);
            $table->decimal('overtime_hours', 5, 2)->nullable();
            $table->string('check_in_method')->nullable(); // manual, biometric, gps, web
            $table->string('check_in_location')->nullable();
            $table->string('check_out_location')->nullable();
            $table->string('check_in_photo')->nullable();
            $table->string('check_out_photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index('date');
        });

        Schema::create('hrms_overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('hours_requested', 5, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'cancelled'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('status');
        });

        Schema::create('hrms_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->date('date');
            $table->enum('type', ['public', 'company', 'observance'])->default('public');
            $table->string('state_applicable')->nullable(); // 'all', 'selangor', etc.
            $table->boolean('is_recurring')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrms_holidays');
        Schema::dropIfExists('hrms_overtime_requests');
        Schema::dropIfExists('hrms_attendance');
        Schema::dropIfExists('hrms_leave_request_history');
        Schema::dropIfExists('hrms_leave_requests');
        Schema::dropIfExists('hrms_leave_allocations');
        Schema::dropIfExists('hrms_leave_types');
    }
};
