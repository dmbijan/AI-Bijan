<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Content Studio & Omnichannel Publishing (BAB 86 / Idea #21).
 *
 * ✅ Didapati daripada audit:
 * - Shopify ada "TikTok" (16,011 review) + "Dondy WhatsApp AI" (828) — Social Commerce
 * - ReStructure AI menjalankan "Content department": research, drafts, publishing
 *   across every channel, run by agents that learn your voice
 * - OpenClaw/Hermes: "gateway + 20+ channels" — satu pintu untuk semua
 *
 * Ciri:
 * - Content calendar + content pieces (draf → semak → jadual → terbit)
 * - Auto-generate dengan AI (brief → draft, Platform-specific format)
 * - Publish ke N platform (Facebook, Instagram, TikTok, LinkedIn, X, WhatsApp, YouTube…)
 * - Comment inbox terpusat (semua platform) + AI reply suggestion + auto-reply
 * - Chat & comment moderation (blacklist, sentiment, toxicity, auto-hide)
 * - Platform analytics (reach, engagement, followers growth)
 * - Voice/brand profile supaya AI tulis "seperti anda"
 */
return new class extends Migration
{
    public function up(): void
    {
        // Platform sokongan
        Schema::create('content_platforms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('platform');                        // facebook|instagram|tiktok|linkedin|x|whatsapp|youtube|google_business|telegram
            $t->string('display_name')->nullable();
            $t->string('account_id')->nullable();
            $t->string('account_name')->nullable();
            $t->json('credentials')->nullable();           // token disekat — jangan dipulangkan API
            $t->json('capabilities')->nullable();          // post|story|reel|video|carousel|comment|message
            $t->boolean('is_active')->default(true);
            $t->boolean('auto_publish')->default(false);    // publish tanpa semakan manusia
            $t->boolean('auto_reply')->default(false);
            $t->unsignedInteger('max_daily_posts')->default(10);
            $t->timestamp('last_posted_at')->nullable();
            $t->timestamp('last_synced_at')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->unique(['tenant_id', 'platform', 'account_id']);
        });

        // Brand voice — supaya AI tulis sepadan
        Schema::create('content_brand_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name')->default('Default');
            $t->text('tone')->nullable();                   //(cte: friendly, profesional, jenaka)
            $t->text('persona')->nullable();
            $t->text('do_and_dont')->nullable();            // apa yang patut / tak patut
            $t->json('key_phrases')->nullable();
            $t->json('banned_words')->nullable();
            $t->string('language', 10)->default('ms-MY');
            $t->string('default_cta')->nullable();
            $t->unsignedInteger('postings_per_week')->default(3);
            $t->boolean('is_default')->default(false);
            $t->json('sample_posts')->nullable();
            $t->timestamps();
        });

        // Content pieces
        Schema::create('content_pieces', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('body')->nullable();
            $t->text('hook')->nullable();                   // pembuka
            $t->text('cta')->nullable();
            $t->string('content_type')->default('post');    // post|story|reel|video|carousel|article|thread
            $t->string('status')->default('draft');         // idea|draft|in_review|approved|scheduled|published|archived
            $t->unsignedBigInteger('brand_profile_id')->nullable();
            $t->json('media_assets')->nullable();
            $t->json('hashtags')->nullable();
            $t->json('ai_meta')->nullable();                // model, prompt, token
            $t->timestamp('scheduled_at')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->unsignedInteger('view_count')->default(0);
            $t->unsignedInteger('engagement_count')->default(0);
            $t->unsignedInteger('comment_count')->default(0);
            $t->unsignedInteger('share_count')->default(0);
            $t->unsignedInteger('like_count')->default(0);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'scheduled_at']);
        });

        // Jadwal ke setiap platform (publish queue)
        Schema::create('content_publications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('content_piece_id')->index();
            $t->unsignedBigInteger('content_platform_id')->index();
            $t->string('status')->default('queued');       // queued|publishing|published|failed|skipped
            $t->string('platform_post_id')->nullable();
            $t->string('platform_url')->nullable();
            $t->text('platform_payload')->nullable();        // JSON yang dihantar
            $t->text('error_message')->nullable();
            $t->unsignedInteger('attempts')->default(0);
            $t->timestamp('scheduled_at')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });

        // Comment inbox terpusat
        Schema::create('content_comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('content_platform_id')->index();
            $t->unsignedBigInteger('content_piece_id')->nullable();
            $t->string('external_id');                      // ID komen di platform
            $t->string('platform_post_id')->nullable();
            $t->string('author_name');
            $t->string('author_id')->nullable();
            $t->string('author_avatar')->nullable();
            $t->text('body');
            $t->string('sentiment')->nullable();            // positive|neutral|negative|toxic
            $t->decimal('sentiment_score', 5, 2)->nullable();
            $t->string('status')->default('new');           // new|flagged|replied|hidden|resolved
            $t->text('ai_reply_suggestion')->nullable();
            $t->decimal('ai_confidence', 5, 2)->nullable();
            $t->text('reply_body')->nullable();
            $t->timestamp('replied_at')->nullable();
            $t->unsignedInteger('like_count')->default(0);
            $t->timestamp('posted_at')->nullable();
            $t->boolean('is_from_ai')->default(false);
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->unique(['tenant_id', 'content_platform_id', 'external_id']);
            $t->index(['tenant_id', 'status']);
        });

        // Chat messages (DM) dari platform
        Schema::create('content_chats', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('content_platform_id')->index();
            $t->string('external_conversation_id')->nullable();
            $t->string('customer_name')->nullable();
            $t->string('customer_id')->nullable();
            $t->string('customer_avatar')->nullable();
            $t->text('last_message')->nullable();
            $t->string('last_message_from')->nullable();    // customer|ai|human
            $t->string('status')->default('open');           // open|pending|resolved|closed
            $t->unsignedInteger('unread_count')->default(0);
            $t->unsignedInteger('message_count')->default(0);
            $t->string('sentiment')->nullable();
            $t->boolean('requires_human')->default(false);
            $t->text('ai_summary')->nullable();
            $t->timestamp('last_message_at')->nullable();
            $t->timestamp('resolved_at')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'requires_human']);
        });

        // Chat messages individu
        Schema::create('content_chat_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('content_chat_id')->index();
            $t->string('external_id')->nullable();
            $t->string('from');                             // customer|ai|human
            $t->text('body');
            $t->timestamp('sent_at')->nullable();
            $t->boolean('is_read')->default(false);
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'content_chat_id']);
        });

        // Sekatan / moderation
        Schema::create('content_moderation_rules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('type')->default('keyword');         // keyword|toxicity|spam|regex|blocklist
            $t->text('pattern')->nullable();                 // kata kunci / regex / senarai
            $t->string('action')->default('hide');           // hide|flag|block|auto_reply|escalate
            $t->string('severity')->default('medium');       // low|medium|high
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('trigger_count')->default(0);
            $t->timestamps();
        });

        // Analytics platform
        Schema::create('content_platform_metrics', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('content_platform_id')->index();
            $t->date('metric_date');
            $t->unsignedBigInteger('followers')->default(0);
            $t->unsignedBigInteger('followers_gained')->default(0);
            $t->unsignedBigInteger('impressions')->default(0);
            $t->unsignedBigInteger('reach')->default(0);
            $t->unsignedBigInteger('engagements')->default(0);
            $t->unsignedBigInteger('clicks')->default(0);
            $t->unsignedBigInteger('posts_published')->default(0);
            $t->unsignedBigInteger('comments_received')->default(0);
            $t->unsignedBigInteger('negative_comments')->default(0);
            $t->decimal('engagement_rate', 6, 3)->default(0);
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->unique(['tenant_id', 'content_platform_id', 'metric_date']);
        });

        // Idea bank — idea content untuk AI jana
        Schema::create('content_ideas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('notes')->nullable();
            $t->string('source')->default('manual');       // manual|ai|research|competitor|customer
            $t->string('category')->nullable();
            $t->json('keywords')->nullable();
            $t->string('status')->default('new');          // new|used|dismissed
            $t->unsignedBigInteger('used_in_piece_id')->nullable();
            $t->decimal('score', 5, 2)->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_ideas');
        Schema::dropIfExists('content_platform_metrics');
        Schema::dropIfExists('content_moderation_rules');
        Schema::dropIfExists('content_chat_messages');
        Schema::dropIfExists('content_chats');
        Schema::dropIfExists('content_comments');
        Schema::dropIfExists('content_publications');
        Schema::dropIfExists('content_pieces');
        Schema::dropIfExists('content_brand_profiles');
        Schema::dropIfExists('content_platforms');
    }
};
