<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Platform teras AI Bijan SaaS: tenant, keahlian, RBAC, langganan, modul, audit, tetapan, fail, kunci API, kelulusan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_platform_admin')->default(false)->after('password');
            $t->string('phone', 30)->nullable()->after('email');
            $t->string('locale', 5)->default('ms')->after('phone');
            $t->text('two_factor_secret')->nullable();
            $t->timestamp('two_factor_confirmed_at')->nullable();
            $t->timestamp('last_login_at')->nullable();
            $t->boolean('is_active')->default(true);
        });

        Schema::create('plans', function (Blueprint $t) {
            $t->id();
            $t->string('code', 40)->unique();
            $t->string('name');
            $t->unsignedInteger('max_users')->nullable();
            $t->unsignedInteger('max_locations')->nullable();
            $t->unsignedBigInteger('storage_mb')->nullable();
            $t->unsignedBigInteger('ai_credits_monthly')->default(0);
            $t->json('modules')->nullable();
            $t->decimal('price_monthly', 12, 2)->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('tenants', function (Blueprint $t) {
            $t->id();
            $t->ulid('ulid')->unique();
            $t->string('name');
            $t->string('slug', 60)->unique();
            $t->enum('status', ['trial', 'active', 'past_due', 'suspended', 'cancelled'])->default('trial');
            $t->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $t->string('timezone', 40)->default('Asia/Kuala_Lumpur');
            $t->char('currency', 3)->default('MYR');
            $t->string('locale', 5)->default('ms');
            $t->timestamp('trial_ends_at')->nullable();
            $t->timestamps();
        });

        Schema::create('tenant_users', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->enum('status', ['invited', 'active', 'disabled'])->default('active');
            $t->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('invite_token_hash', 64)->nullable()->unique();
            $t->timestamp('invite_expires_at')->nullable();
            $t->timestamp('joined_at')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'user_id']);
        });

        Schema::create('permissions', function (Blueprint $t) {
            $t->id();
            $t->string('key', 80)->unique();
            $t->string('module', 40);
            $t->string('description')->nullable();
        });

        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('key', 40);
            $t->string('name');
            $t->boolean('is_system')->default(false);
            $t->timestamps();
            $t->unique(['tenant_id', 'key']);
        });

        Schema::create('permission_role', function (Blueprint $t) {
            $t->foreignId('role_id')->constrained()->cascadeOnDelete();
            $t->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $t->primary(['role_id', 'permission_id']);
        });

        Schema::create('role_tenant_user', function (Blueprint $t) {
            $t->foreignId('tenant_user_id')->constrained('tenant_users')->cascadeOnDelete();
            $t->foreignId('role_id')->constrained()->cascadeOnDelete();
            $t->primary(['tenant_user_id', 'role_id']);
        });

        Schema::create('subscriptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('plan_id')->constrained('plans');
            $t->enum('status', ['trial', 'active', 'past_due', 'suspended', 'cancelled']);
            $t->timestamp('starts_at');
            $t->timestamp('ends_at')->nullable();
            $t->timestamps();
        });

        Schema::create('tenant_modules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('module', 40);
            $t->boolean('enabled')->default(true);
            $t->timestamps();
            $t->unique(['tenant_id', 'module']);
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('key', 80);
            $t->json('value')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'key']);
        });

        Schema::create('files', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->ulid('ulid')->unique();
            $t->string('disk', 20)->default('local');
            $t->string('path');
            $t->string('original_name');
            $t->string('mime', 100);
            $t->unsignedBigInteger('size');
            $t->char('sha256', 64);
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->nullableMorphs('attachable');
            $t->timestamps();
        });

        Schema::create('api_keys', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('prefix', 12);
            $t->char('hash', 64)->unique();
            $t->json('scopes');
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('revoked_at')->nullable();
            $t->timestamps();
        });

        Schema::create('approval_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('type', 60);
            $t->json('payload');
            $t->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('decided_at')->nullable();
            $t->string('reason')->nullable();
            $t->timestamps();
            $t->index(['tenant_id', 'status']);
        });

        Schema::create('support_access_grants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
            $t->string('reason');
            $t->timestamp('expires_at');
            $t->timestamp('revoked_at')->nullable();
            $t->timestamps();
        });

        // Audit: tiada updated_at; aplikasi tidak menyediakan kemas kini/padam.
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action', 80);
            $t->string('subject_type', 120)->nullable();
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->json('before')->nullable();
            $t->json('after')->nullable();
            $t->char('ip_hash', 24)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'support_access_grants', 'approval_requests', 'api_keys', 'files', 'settings', 'tenant_modules', 'subscriptions', 'role_tenant_user', 'permission_role', 'roles', 'permissions', 'tenant_users', 'tenants', 'plans'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['is_platform_admin', 'phone', 'locale', 'two_factor_secret', 'two_factor_confirmed_at', 'last_login_at', 'is_active']));
    }
};
