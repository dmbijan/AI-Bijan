<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create overtime_records table.
     * 
     * Overtime calculation per Akta Kerja 1955 (Employment Act 1955):
     * - Regular day: 1.5x hourly rate
     * - Rest day: 2x hourly rate  
     * - Public holiday: 3x hourly rate
     */
    public function up(): void
    {
        Schema::create('overtime_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            
            // Overtime details
            $table->date('date');
            $table->enum('day_type', ['regular', 'rest_day', 'public_holiday'])->default('regular');
            $table->decimal('hours_worked', 5, 2)->comment('Hours worked for OT');
            
            // Rate calculation
            $table->decimal('hourly_rate', 10, 2)->comment('Base hourly rate = basic_salary / 26 / 8');
            $table->decimal('multiplier', 3, 2)->comment('1.5x regular, 2x rest day, 3x public holiday');
            $table->decimal('ot_pay', 12, 2)->comment('Calculated OT pay = hourly_rate × multiplier × hours');
            
            // Approval workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'date']);
            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_records');
    }
};
