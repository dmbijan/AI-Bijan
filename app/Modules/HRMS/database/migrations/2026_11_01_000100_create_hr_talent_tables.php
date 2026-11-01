<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Kelompok 2 HR: ATS (HR-004), prestasi (HR-009), ganjaran (HR-010), kemahiran (HR-011),
// suara pekerja (HR-012), faedah & tuntutan (HR-013), masuk/keluar pekerja (HR-014).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hrms_vacancies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->foreignId('department_id')->nullable()->constrained('hrms_departments')->nullOnDelete();
            $t->string('employment_type', 20)->default('full_time');
            $t->unsignedSmallInteger('openings')->default(1);
            $t->decimal('salary_min', 12, 2)->nullable();
            $t->decimal('salary_max', 12, 2)->nullable();
            $t->text('description')->nullable();
            $t->string('status', 12)->default('open'); // draft, open, closed
            $t->date('closes_on')->nullable();
            $t->timestamps();
            $t->index(['tenant_id', 'status']);
        });

        Schema::create('hrms_applicants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('vacancy_id')->constrained('hrms_vacancies')->cascadeOnDelete();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone', 30)->nullable();
            $t->string('source', 40)->nullable();
            $t->string('stage', 12)->default('applied'); // applied, screening, interview, offer, hired, rejected
            $t->unsignedTinyInteger('score')->nullable();
            $t->text('notes')->nullable();
            $t->dateTime('interview_at')->nullable();
            $t->decimal('offer_salary', 12, 2)->nullable();
            $t->foreignId('employee_id')->nullable()->constrained('hrms_employees')->nullOnDelete();
            $t->timestamp('stage_changed_at')->nullable();
            $t->timestamps();
            $t->index(['tenant_id', 'vacancy_id', 'stage']);
        });

        Schema::create('hrms_goals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $t->string('period', 12); // 2026-H2, 2026-Q4, 2026
            $t->string('title');
            $t->decimal('target', 14, 2)->default(100);
            $t->decimal('actual', 14, 2)->default(0);
            $t->unsignedTinyInteger('weight')->default(100);
            $t->date('due_on')->nullable();
            $t->timestamps();
            $t->index(['tenant_id', 'employee_id', 'period']);
        });

        Schema::create('hrms_reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $t->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('period', 12);
            $t->unsignedTinyInteger('rating')->nullable(); // 1-5
            $t->decimal('goal_score', 5, 1)->nullable();   // % pencapaian sasaran berwajaran
            $t->text('strengths')->nullable();
            $t->text('improvements')->nullable();
            $t->string('status', 12)->default('draft'); // draft, submitted, acknowledged
            $t->timestamp('submitted_at')->nullable();
            $t->timestamps();
            $t->unique(['employee_id', 'period']);
        });

        Schema::create('hrms_salary_bands', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('grade', 20);
            $t->string('name');
            $t->decimal('min_salary', 12, 2);
            $t->decimal('max_salary', 12, 2);
            $t->timestamps();
            $t->unique(['tenant_id', 'grade']);
        });

        Schema::create('hrms_compensation_changes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $t->string('type', 12); // increment, bonus, allowance, promotion
            $t->decimal('current_salary', 12, 2)->nullable();
            $t->decimal('new_salary', 12, 2)->nullable();
            $t->decimal('amount', 12, 2)->default(0);
            $t->date('effective_on');
            $t->string('reason')->nullable();
            $t->string('status', 12)->default('pending'); // pending, approved, rejected
            $t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('decided_at')->nullable();
            $t->timestamps();
            $t->index(['tenant_id', 'status']);
        });

        Schema::create('hrms_skills', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name', 80);
            $t->string('category', 40)->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'name']);
        });

        Schema::create('hrms_employee_skills', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $t->foreignId('skill_id')->constrained('hrms_skills')->cascadeOnDelete();
            $t->unsignedTinyInteger('level')->default(1); // 0 tiada, 1 asas, 2 boleh sendiri, 3 mahir, 4 boleh ajar
            $t->date('certified_until')->nullable();
            $t->timestamps();
            $t->unique(['employee_id', 'skill_id']);
        });

        Schema::create('hrms_designation_skills', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('designation_id')->constrained('hrms_designations')->cascadeOnDelete();
            $t->foreignId('skill_id')->constrained('hrms_skills')->cascadeOnDelete();
            $t->unsignedTinyInteger('required_level')->default(2);
            $t->unique(['designation_id', 'skill_id']);
        });

        Schema::create('hrms_surveys', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->json('questions'); // [{id, text, type: scale|text}]
            $t->boolean('anonymous')->default(true);
            $t->string('status', 12)->default('open'); // open, closed
            $t->date('closes_on')->nullable();
            $t->timestamps();
        });

        Schema::create('hrms_survey_responses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('survey_id')->constrained('hrms_surveys')->cascadeOnDelete();
            $t->foreignId('employee_id')->nullable()->constrained('hrms_employees')->nullOnDelete(); // null bila tanpa nama
            $t->string('respondent_hash', 64); // elak jawab dua kali tanpa simpan siapa
            $t->json('answers');
            $t->timestamps();
            $t->unique(['survey_id', 'respondent_hash']);
        });

        Schema::create('hrms_recognitions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $t->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('value_tag', 40)->nullable();
            $t->string('message', 500);
            $t->timestamps();
        });

        Schema::create('hrms_benefit_plans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('type', 20)->default('medical'); // medical, dental, optical, insurance, other
            $t->decimal('annual_limit', 12, 2);
            $t->decimal('per_claim_limit', 12, 2)->nullable();
            $t->unsignedSmallInteger('eligible_after_days')->default(0);
            $t->json('panel_clinics')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('hrms_benefit_claims', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $t->foreignId('plan_id')->constrained('hrms_benefit_plans');
            $t->date('claim_date');
            $t->decimal('amount', 12, 2);
            $t->decimal('approved_amount', 12, 2)->nullable();
            $t->string('provider')->nullable();
            $t->string('receipt_ref', 60)->nullable();
            $t->string('status', 12)->default('pending'); // pending, approved, rejected
            $t->string('notes')->nullable();
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('decided_at')->nullable();
            $t->timestamps();
            $t->index(['tenant_id', 'status']);
            $t->index(['employee_id', 'plan_id', 'claim_date']);
        });

        Schema::create('hrms_checklist_templates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('type', 12); // onboarding, offboarding
            $t->string('name');
            $t->json('items'); // [{title, owner, due_days}]
            $t->timestamps();
        });

        Schema::create('hrms_lifecycle_tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $t->string('type', 12); // onboarding, offboarding
            $t->string('title');
            $t->string('owner', 40)->nullable(); // HR, IT, Pengurus, Kewangan
            $t->date('due_on')->nullable();
            $t->timestamp('done_at')->nullable();
            $t->foreignId('done_by')->nullable()->constrained('users')->nullOnDelete();
            $t->unsignedSmallInteger('sort')->default(0);
            $t->timestamps();
            $t->index(['tenant_id', 'type', 'done_at']);
        });

        Schema::create('hrms_exit_interviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_id')->constrained('hrms_employees')->cascadeOnDelete();
            $t->string('reason', 40); // gaji, kerjaya, pengurus, beban, peribadi, lain
            $t->boolean('would_rehire')->nullable();
            $t->text('feedback')->nullable();
            $t->timestamps();
            $t->unique('employee_id');
        });
    }

    public function down(): void
    {
        foreach (['hrms_exit_interviews', 'hrms_lifecycle_tasks', 'hrms_checklist_templates', 'hrms_benefit_claims', 'hrms_benefit_plans', 'hrms_recognitions',
            'hrms_survey_responses', 'hrms_surveys', 'hrms_designation_skills', 'hrms_employee_skills', 'hrms_skills', 'hrms_compensation_changes',
            'hrms_salary_bands', 'hrms_reviews', 'hrms_goals', 'hrms_applicants', 'hrms_vacancies'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
