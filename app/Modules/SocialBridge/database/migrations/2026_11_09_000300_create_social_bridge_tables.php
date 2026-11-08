<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SocialBridge — siaran silang sosial (asal: Post Bridge).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->string('platform', 40);
            $t->string('account_name', 120);
            $t->string('handle', 80)->nullable();
            $t->string('external_id', 120)->nullable();
            $t->string('avatar_url', 500)->nullable();
            $t->text('access_token')->nullable();
            $t->text('refresh_token')->nullable();
            $t->timestamp('token_expires_at')->nullable();
            $t->json('scopes')->nullable();
            $t->string('status', 20)->default('active');
            $t->boolean('is_brand')->default(false);
            $t->timestamp('last_posted_at')->nullable();
            $t->json('settings')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'platform']);
            $t->index(['tenant_id', 'status']);
        });

        Schema::create('social_posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->uuid('uuid');
            $t->string('title', 250)->nullable();
            $t->text('body');
            $t->string('type', 20)->default('text');
            $t->json('media')->nullable();
            $t->string('status', 20)->default('draft');
            $t->timestamp('scheduled_at')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->string('timezone', 60)->default('Asia/Kuala_Lumpur');
            $t->json('per_platform_overrides')->nullable();
            $t->json('tags')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'scheduled_at']);
            $t->unique(['tenant_id', 'uuid']);
        });

        Schema::create('social_post_targets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $t->foreignId('social_post_id')->constrained('social_posts')->cascadeOnDelete();
            $t->foreignId('social_account_id')->nullable()->constrained('social_accounts')->nullOnDelete();
            $t->string('platform', 40);
            $t->string('status', 20)->default('pending');
            $t->string('external_post_id', 120)->nullable();
            $t->string('external_url', 500)->nullable();
            $t->text('content')->nullable();
            $t->unsignedSmallInteger('attempts')->default(0);
            $t->text('last_error')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->json('metrics')->nullable();
            $t->timestamps();

            $t->index(['social_post_id', 'status']);
            $t->index(['tenant_id', 'platform']);
            $t->unique(['social_post_id', 'social_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_post_targets');
        Schema::dropIfExists('social_posts');
        Schema::dropIfExists('social_accounts');
    }
};
