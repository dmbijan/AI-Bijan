<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration DIPULIHKAN daripada DDL SQLite sebenar (T-109, 28 Okt 2026).
 *
 * Migrasi asal `2026_10_20_000800_create_site_tables` sudah dijalankan pada pangkalan data tetapi
 * failnya TIADA dalam repositori, jadi `migrate:fresh` tidak dapat membina
 * semula jadual: `site_domains`, `site_forms`, `site_form_submissions`, `site_pages`, `site_posts`, `site_post_comments`, `site_sections`, `sites`, `site_themes`.
 *
 * Skema dijana oleh `php skrip/jana-ims.sqlite.php --tulis` (DDL verbatim
 * daripada `sqlite_master`), kemudian disahkan dengan
 * `php skrip/jana-ims.sqlite.php --sahkan=<fail-db>`.
 *
 * ⚠️ FK silang-migration sengaja TIDAK diisytiharkan semula (jadual induk
 * dicipta oleh migration lain, mungkin selepas ini):
 *   - site_domains.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - site_forms.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - site_form_submissions.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - site_pages.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - site_pages.author_id → users.id (jadual induk tidak wujud)
 *   - site_posts.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - site_posts.author_id → users.id (jadual induk tidak wujud)
 *   - site_post_comments.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - site_post_comments.user_id → users.id (jadual induk tidak wujud)
 *   - site_sections.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - sites.tenant_id → tenants.id (jadual induk tidak wujud)
 *   - site_themes.tenant_id → tenants.id (jadual induk tidak wujud)
 *
 * JANGAN sunting DDL di bawah secara manual — tambah migration baharu untuk
 * sebarang perubahan skema.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Jadual anak dibina semula SEKALI dengan FK dalaman: bina tanpa FK dahulu
        // hanya jika induk dalam migration yang sama, kemudian DROP + CREATE semula
        // (SQLite tidak menyokong ADD CONSTRAINT).

        DB::unprepared('CREATE TABLE "site_domains" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "site_id" integer not null, "domain" varchar not null, "type" varchar not null default \'primary\', "is_primary" tinyint(1) not null default \'0\', "ssl_enabled" tinyint(1) not null default \'0\', "ssl_status" varchar not null default \'none\', "redirect_to" varchar, "verified" tinyint(1) not null default \'0\', "verification_code" varchar, "dns_records" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("site_id") references "sites"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "site_forms" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "site_id" integer not null, "name" varchar not null, "slug" varchar, "title" varchar not null, "description" text, "fields" text not null, "submit_action" varchar not null default \'email\', "success_message" text, "is_active" tinyint(1) not null default \'1\', "spam_protection" text, "notification_email" text, "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("site_id") references "sites"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "site_form_submissions" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "form_id" integer not null, "data" text not null, "ip_address" varchar, "user_agent" varchar, "is_spam" tinyint(1) not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("form_id") references "site_forms"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "site_pages" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "site_id" integer not null, "title" varchar not null, "slug" varchar, "template" varchar not null default \'default\', "status" varchar not null default \'draft\', "content" text, "sections" text, "meta_title" varchar, "meta_description" text, "meta_keywords" text, "og_image" varchar, "author_id" integer, "published_at" datetime, "order" integer not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("site_id") references "sites"("id") on delete cascade, foreign key("author_id") references "users"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "site_posts" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "site_id" integer not null, "title" varchar not null, "slug" varchar, "excerpt" text, "content" text, "featured_image" varchar, "status" varchar not null default \'draft\', "author_id" integer, "published_at" datetime, "meta_title" varchar, "meta_description" text, "tags" text, "views_count" integer not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("site_id") references "sites"("id") on delete cascade, foreign key("author_id") references "users"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "site_post_comments" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "post_id" integer not null, "parent_id" integer, "user_id" integer, "name" varchar, "email" varchar, "content" text not null, "status" varchar not null default \'pending\', "is_approved" tinyint(1) not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("post_id") references "site_posts"("id") on delete cascade, foreign key("parent_id") references "site_post_comments"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "site_sections" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "page_id" integer not null, "type" varchar not null, "name" varchar, "settings" text, "content" text, "styles" text, "order" integer not null default \'0\', "is_visible" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("page_id") references "site_pages"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "sites" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "name" varchar not null, "slug" varchar not null, "description" text, "logo_url" varchar, "favicon_url" varchar, "primary_domain" varchar, "secondary_domains" text, "theme" varchar not null default \'default\', "status" varchar not null default \'draft\', "is_live" tinyint(1) not null default \'0\', "maintenance_mode" tinyint(1) not null default \'0\', "coming_soon_mode" tinyint(1) not null default \'0\', "seo_title" varchar, "seo_description" text, "seo_keywords" text, "og_image" varchar, "google_analytics_id" varchar, "google_tag_manager_id" varchar, "facebook_pixel_id" varchar, "custom_head_code" text, "custom_body_code" text, "language" varchar not null default \'en\', "timezone" varchar not null default \'UTC\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade)');
        DB::unprepared('CREATE TABLE "site_themes" ("id" integer primary key autoincrement not null, "tenant_id" integer not null, "site_id" integer, "name" varchar not null, "slug" varchar, "version" varchar not null default \'1.0.0\', "author" varchar, "description" text, "preview_image" varchar, "settings" text, "is_active" tinyint(1) not null default \'0\', "is_default" tinyint(1) not null default \'0\', "created_at" datetime, "updated_at" datetime, foreign key("tenant_id") references "tenants"("id") on delete cascade, foreign key("site_id") references "sites"("id") on delete cascade)');

        DB::statement('CREATE INDEX IF NOT EXISTS "site_domains_tenant_id_site_id_index" on "site_domains" ("tenant_id", "site_id")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "site_form_submissions_form_id_created_at_index" on "site_form_submissions" ("form_id", "created_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "site_forms_tenant_id_is_active_index" on "site_forms" ("tenant_id", "is_active")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "site_pages_tenant_id_published_at_index" on "site_pages" ("tenant_id", "published_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "site_pages_tenant_id_status_index" on "site_pages" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "site_post_comments_post_id_is_approved_index" on "site_post_comments" ("post_id", "is_approved")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "site_posts_tenant_id_published_at_index" on "site_posts" ("tenant_id", "published_at")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "site_posts_tenant_id_status_index" on "site_posts" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "site_sections_page_id_type_index" on "site_sections" ("page_id", "type")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "site_themes_tenant_id_is_active_index" on "site_themes" ("tenant_id", "is_active")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "sites_tenant_id_slug_index" on "sites" ("tenant_id", "slug")');   // best-effort: mungkin sudah wujud
        DB::statement('CREATE INDEX IF NOT EXISTS "sites_tenant_id_status_index" on "sites" ("tenant_id", "status")');   // best-effort: mungkin sudah wujud
    }

    public function down(): void
    {
        Schema::dropIfExists('site_themes');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('site_sections');
        Schema::dropIfExists('site_post_comments');
        Schema::dropIfExists('site_posts');
        Schema::dropIfExists('site_pages');
        Schema::dropIfExists('site_form_submissions');
        Schema::dropIfExists('site_forms');
        Schema::dropIfExists('site_domains');
    }
};
