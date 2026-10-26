<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Business Intelligence — AI Self-Improve, Business Study & Competitor Auto-Audit
 * (BAB 86 / Idea #23, #24, #25).
 *
 * ✅ Didapati daripada audit:
 * - ReStructure AI: "agent teams run on proprietary datasets … get sharper with every cycle"
 * - Hermes Agent: "closed learning loop — creates skills from experience, improves them
 *   during use, nudges itself to persist knowledge"
 * - Kalodata: competitor insight + trending product + creator analytics
 * - AI BIJAN janji "AI self-improve business" + "AI auto kaji pesaing dan berikan idea"
 *
 * Ciri:
 * - Health Score perniagaan (0-100) merentas 6 dimensi
 * - Kajian bisnes automatik (AI cadang penambahbaikan + idea pasaran)
 * - Baseline & trend (AI belajar dari data sendiri)
 * - Competitor watch (dari data awam + input tenant)
 * - Idea feed — cadangan konkrit berasaskan data
 * - Learning loop: rekod cadangan → terima/tolak → AI belajar
 */
return new class extends Migration
{
    public function up(): void
    {
        // Skor kesihatan perniagaan (snapshot harian)
        Schema::create('bi_health_snapshots', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->date('snapshot_date');
            $t->decimal('overall_score', 5, 2)->default(0);
            $t->string('grade')->default('C');              // A|B|C|D|E
            // 6 dimensi
            $t->decimal('sales_score', 5, 2)->default(0);
            $t->decimal('cashflow_score', 5, 2)->default(0);
            $t->decimal('operations_score', 5, 2)->default(0);
            $t->decimal('customer_score', 5, 2)->default(0);
            $t->decimal('marketing_score', 5, 2)->default(0);
            $t->decimal('compliance_score', 5, 2)->default(0);
            $t->json('metrics')->nullable();                  // nilai mentah setiap dimensi
            $t->json('insights')->nullable();                 // AI insight ringkas
            $t->timestamps();

            $t->unique(['tenant_id', 'snapshot_date']);
            $t->index(['tenant_id', 'snapshot_date']);
        });

        // Kajian bisnes (AI)
        Schema::create('bi_studies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('study_type')->default('general');     // general|pricing|product|market|operations|customer
            $t->string('status')->default('draft');           // draft|running|completed|failed
            $t->text('question')->nullable();                  // soalan kajian
            $t->json('data_snapshot')->nullable();            // data mentah yang dianalisis
            $t->text('summary')->nullable();
            $t->json('findings')->nullable();                 // senarai penemuan
            $t->json('recommendations')->nullable();          // senarai cadangan
            $t->json('risks')->nullable();
            $t->json('opportunities')->nullable();
            $t->decimal('confidence', 5, 2)->default(0);
            $t->unsignedInteger('credits_used')->default(0);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });

        // Idea feed — cadangan konkrit
        Schema::create('bi_ideas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('bi_study_id')->nullable();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('category')->default('growth');        // growth|cost|product|pricing|marketing|ops|customer
            $t->string('source')->default('ai');              // ai|competitor|data|manual|customer
            $t->string('impact')->default('medium');          // low|medium|high
            $t->string('effort')->default('medium');          // low|medium|high
            $t->decimal('impact_score', 5, 2)->default(0);    // 0-100
            $t->decimal('confidence', 5, 2)->default(0);
            $t->text('evidence')->nullable();                 // bukti data
            $t->text('expected_outcome')->nullable();
            $t->string('status')->default('new');             // new|accepted|rejected|in_progress|done
            $t->timestamp('accepted_at')->nullable();
            $t->timestamp('done_at')->nullable();
            $t->decimal('actual_outcome', 12, 2)->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
            $t->index(['tenant_id', 'category']);
        });

        // Competitor watch
        Schema::create('bi_competitors', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('website')->nullable();
            $t->string('industry')->nullable();
            $t->string('country')->default('MY');
            $t->json('channels')->nullable();                 // social handles, marketplace
            $t->json('products')->nullable();                 // produk/perkhidmatan utama
            $t->json('pricing')->nullable();                  // harga yang diperhatikan
            $t->text('strengths')->nullable();
            $t->text('weaknesses')->nullable();
            $t->text('positioning')->nullable();
            $t->string('threat_level')->default('medium');    // low|medium|high
            $t->boolean('is_active')->default(true);
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'is_active']);
        });

        // Snapshot/audit pesaing
        Schema::create('bi_competitor_snapshots', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('bi_competitor_id')->index();
            $t->date('snapshot_date');
            $t->json('observed_data')->nullable();            // apa yang diperhatikan
            $t->json('changes')->nullable();                  // perubahan vs snapshot lepas
            $t->text('ai_analysis')->nullable();
            $t->json('threats')->nullable();
            $t->json('opportunities')->nullable();
            $t->string('source_type')->default('manual');     // manual|public_scrape|api|tenant_input
            $t->string('source_url')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'bi_competitor_id']);
        });

        // Market intelligence (trend, keyword, kategori)
        Schema::create('bi_market_signals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('signal_type');                        // trend|keyword|category|season|regulation
            $t->string('subject');                            // topik/nama produk/kata kunci
            $t->string('direction')->default('up');           // up|down|stable
            $t->decimal('strength', 5, 2)->default(0);        // 0-100
            $t->text('summary')->nullable();
            $t->json('evidence')->nullable();
            $t->string('source')->default('ai');              // ai|analytics|competitor|external
            $t->date('observed_at');
            $t->timestamps();

            $t->index(['tenant_id', 'signal_type']);
        });

        // Learning loop — jejak keberkesanan cadangan
        Schema::create('bi_learnings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('learning_type');                      // pattern|outcome|correction|preference
            $t->string('subject_type')->nullable();
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->text('insight');                              // apa yang AI belajar
            $t->decimal('confidence', 5, 2)->default(0);
            $t->json('evidence')->nullable();
            $t->unsignedInteger('applied_count')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bi_learnings');
        Schema::dropIfExists('bi_market_signals');
        Schema::dropIfExists('bi_competitor_snapshots');
        Schema::dropIfExists('bi_competitors');
        Schema::dropIfExists('bi_ideas');
        Schema::dropIfExists('bi_studies');
        Schema::dropIfExists('bi_health_snapshots');
    }
};
