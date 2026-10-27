<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_ai_repositories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('owner', 39);
            $table->string('repository', 100);
            $table->string('branch', 200)->default('main');
            $table->text('access_token_ciphertext')->nullable();
            $table->string('last_commit_sha', 64)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'owner', 'repository'], 'team_ai_repo_tenant_identity');
        });

        Schema::create('team_ai_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('team_ai_repository_id');
            $table->string('path', 500);
            $table->string('category', 40)->index();
            $table->string('title', 180);
            $table->string('git_sha', 64)->nullable();
            $table->longText('content');
            $table->timestamps();

            $table->unique(['team_ai_repository_id', 'path'], 'team_ai_asset_repo_path');
            $table->foreign('team_ai_repository_id')
                ->references('id')
                ->on('team_ai_repositories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_ai_assets');
        Schema::dropIfExists('team_ai_repositories');
    }
};
