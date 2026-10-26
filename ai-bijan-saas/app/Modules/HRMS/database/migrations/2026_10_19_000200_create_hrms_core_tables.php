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
        Schema::create('hrms_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('hrms_departments')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
            $table->unique(['tenant_id', 'code'], 'hrms_dept_tenant_code_unique');
        });

        Schema::create('hrms_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_no')->unique(); // e.g., EMP-2026-0001
            $table->string('ic_number')->nullable(); // NRIC
            $table->string('passport_number')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('nationality')->default('Malaysian');
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->default('Malaysia');
            
            // Employment details
            $table->foreignId('department_id')->nullable()->constrained('hrms_departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('hrms_designations')->nullOnDelete();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'freelance', 'intern'])->default('full_time');
            $table->date('date_joined')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->enum('status', ['active', 'on_leave', 'suspended', 'terminated', 'resigned'])->default('active');
            $table->date('last_worked_date')->nullable();
            $table->text('termination_reason')->nullable();
            
            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            
            // Bank & statutory
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('epf_no')->nullable();
            $table->string('soeso_no')->nullable();
            $table->string('eis_no')->nullable();
            $table->string('pcb_ref')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
            $table->index('department_id');
        });

        Schema::create('hrms_designations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('hrms_departments')->nullOnDelete();
            $table->integer('level')->default(1); // Job level
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('hrms_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->integer('working_hours')->default(8);
            $table->json('working_days')->default('[1,2,3,4,5]'); // Mon-Fri as default (1=Mon, 7=Sun)
            $table->enum('type', ['standard', 'rotating', 'flexible', 'night'])->default('standard');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('hrms_employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('hrms_shifts')->cascadeOnDelete();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'effective_from']);
        });

        Schema::create('hrms_employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $table->string('document_type'); // IC, passport, certificate, contract, etc.
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrms_employee_documents');
        Schema::dropIfExists('hrms_employee_shifts');
        Schema::dropIfExists('hrms_shifts');
        Schema::dropIfExists('hrms_designations');
        Schema::dropIfExists('hrms_employees');
        Schema::dropIfExists('hrms_departments');
    }
};
