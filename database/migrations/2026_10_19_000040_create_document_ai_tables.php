<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Penanda (T-109, 28 Okt 2026) — migration asal TIADA dalam repositori tetapi
 * sudah tercatat sebagai `Ran` pada pangkalan data sedia ada.
 *
 * Migration asal: `2026_10_19_000040_create_document_ai_tables`
 *
 * **Tiada DDL di sini dengan sengaja.** Jadual yang dicipta oleh migration asal
 * (`document_templates`, `document_extraction_history`, `document_sequences`,
 * `document_shares`, `document_versions`) kini dimiliki oleh
 * `2026_10_27_000100_create_missing_model_tables.php` (dan skema penuh disahkan
 * sepadan dengan DB rujukan — `php skrip/jana-ims.sqlite.php --sahkan=<db>`).
 *
 * Fail ini wujud supaya:
 *   1. `php skrip/jana-migrasi-hilang.php` melaporkan **0** (DB ↔ fail sepadan);
 *   2. `migrate:fresh` pada DB baharu menghasilkan skema yang sama;
 *   3. sejarah migration tidak "hilang" secara senyap.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tiada perubahan skema — lihat docblock.
    }

    public function down(): void
    {
        // Tiada apa untuk dibatalkan.
    }
};
