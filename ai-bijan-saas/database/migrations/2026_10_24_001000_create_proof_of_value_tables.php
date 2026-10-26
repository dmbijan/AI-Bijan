<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Proof of Value (BAB 84 / Idea #5) — metrik hasil per modul.
 *
 * ✅ IDEA #5: ServiceTitan Pro Products/results boleh diukur
 * ("+20% average ticket", "2x faster revenue growth", "75% reduction in
 * accident risk"). AI BIJAN belum ada corak ini — ini yang membezakan
 * "produk bagus" daripada "platform yang boleh dibuktikan".
 *
 * Ciri:
 * - Metrik hasil per modul (setiap modul AI BIJAN)
 * - Baseline vs semasa (banding sebelum/sesudah)
 * - Garis dasar (baseline) yang direkod
 * - Rating bukti (sudah disahkan / belum)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Definisi metrik hasil (catalogue)
        Schema::create('pov_metrics', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('module', 60);
            $t->string('key', 80);
            $t->string('name', 180);
            $t->text('description')->nullable();
            // unit: percent|rm|count|minutes|ratio
            $t->enum('unit', ['percent', 'rm', 'count', 'minutes', 'ratio', 'days'])->default('percent');
            // Arah yang baik: up (lebih tinggi lebih baik) | down (lebih rendah lebih baik)
            $t->enum('direction', ['up', 'down'])->default('up');
            // Sasaran (target) untuk tenant ini
            $t->decimal('target_value', 14, 2)->nullable();
            $t->boolean('is_active')->default(true);
            // Adakah metrik ini disahkan dengan data sebenar?
            $t->boolean('is_verified')->default(false);
            $t->string('verification_note', 250)->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'module']);
            $t->unique(['tenant_id', 'module', 'key']);
        });

        // Bacaan metrik (time series)
        Schema::create('pov_readings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('metric_id')->constrained('pov_metrics')->cascadeOnDelete();
            $t->date('reading_date');
            $t->decimal('value', 14, 2);
            // Peratus perubahan berbanding bacaan sebelum
            $t->decimal('change_percent', 10, 2)->nullable();
            $t->string('source', 80)->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'reading_date']);
            $t->unique(['metric_id', 'reading_date']);
        });

        // Baseline — titik mula pengiraan
        Schema::create('pov_baselines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('metric_id')->constrained('pov_metrics')->cascadeOnDelete();
            $t->date('baseline_date');
            $t->decimal('baseline_value', 14, 2);
            $t->string('source', 80)->nullable();
            $t->text('note')->nullable();
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['tenant_id', 'baseline_date']);
        });

        // Bukti jualan (untuk PDF "Proof of Value" kepada pelanggan)
        Schema::create('pov_exports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name', 180);
            $t->date('period_start');
            $t->date('period_end');
            $t->json('metrics')->nullable();
            $t->enum('status', ['draft', 'generated', 'sent'])->default('draft');
            $t->string('file_path', 500)->nullable();
            $t->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('sent_to')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pov_exports');
        Schema::dropIfExists('pov_baselines');
        Schema::dropIfExists('pov_readings');
        Schema::dropIfExists('pov_metrics');
    }
};
