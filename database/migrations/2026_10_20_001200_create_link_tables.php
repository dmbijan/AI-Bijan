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
        Schema::create('link_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique()->nullable();
            $table->string('short_code')->unique();
            $table->text('description')->nullable();
            $table->string('type')->default('page'); // page, campaign, landing, event
            $table->json('content')->nullable();
            $table->string('redirect_url')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->string('status')->default('active'); // active, inactive, pending
            $table->date('expiry_date')->nullable();
            $table->boolean('password_protected')->default(false);
            $table->string('password_hash')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('unique_views_count')->default(0);
            $table->integer('conversions_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['short_code']);
        });

        Schema::create('digital_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->string('short_code')->unique();
            $table->string('holder_name');
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('template')->default('business'); // business, personal, minimal, creative
            $table->text('custom_css')->nullable();
            $table->longText('vcard_content')->nullable();
            $table->text('qr_code_data')->nullable();
            $table->string('status')->default('active'); // active, inactive, pending
            $table->date('expiry_date')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['short_code']);
        });

        Schema::create('link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('link_page_id')->nullable()->constrained('link_pages')->nullOnDelete();
            $table->foreignId('card_id')->nullable()->constrained('digital_cards')->nullOnDelete();
            $table->string('click_id')->unique();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referrer_url')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('device')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->boolean('converted')->default(false);
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['link_page_id', 'created_at']);
            $table->index(['card_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_clicks');
        Schema::dropIfExists('digital_cards');
        Schema::dropIfExists('link_pages');
    }
};
