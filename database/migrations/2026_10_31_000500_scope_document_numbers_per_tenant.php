<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nombor dokumen dijana per syarikat (skop tenant: SALE-2026-000001 bermula semula untuk setiap syarikat),
 * tetapi lajurnya unik untuk SELURUH sistem. Akibatnya syarikat kedua gagal membuka kaunter, membuat jualan
 * atau invois pertama (pelanggaran unik). Jadikan unik per (tenant_id, nombor).
 * Invoicing sudah dibetulkan oleh 2026_10_27_000100 (corak sama); ini melengkapkan POS.
 * Jadual lain dengan corak sama disenaraikan dalam Master §2 untuk dibetulkan berperingkat.
 */
return new class extends Migration
{
    private array $lajur = [
        'pos_registers' => 'code',
        'pos_sales' => 'sale_no',
        'pos_sale_returns' => 'return_no',
    ];

    public function up(): void
    {
        foreach ($this->lajur as $jadual => $kol) {
            Schema::table($jadual, function (Blueprint $t) use ($kol) {
                $t->dropUnique([$kol]);
                $t->unique(['tenant_id', $kol]);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->lajur as $jadual => $kol) {
            Schema::table($jadual, function (Blueprint $t) use ($kol) {
                $t->dropUnique(['tenant_id', $kol]);
                $t->unique([$kol]);
            });
        }
    }
};
