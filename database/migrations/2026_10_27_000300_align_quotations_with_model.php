<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P1-1b — `quotations` tidak sepadan dengan model `Quotation`.
 *
 * Ditemui 27 Okt 2026. Model & `InvoiceService` menjangka 16 kolum yang
 * **tidak wujud** dalam jadual:
 *
 *     customer_name, customer_email, customer_phone,
 *     issue_date, expiry_date, validity_days,
 *     tax_total, discount_total, shipping_total, grand_total, exchange_rate,
 *     accepted_at, rejected_at, converted_to_order_id, converted_at, created_by
 *
 * Kesannya: `InvoiceService::createQuotation()` **gagal sepenuhnya** —
 *
 *     SQLSTATE[HY000]: General error: 1 table quotations has no column
 *     named customer_name
 *
 * dan `Quotation::recalculateTotals()` (dipanggil oleh service itu) menulis
 * `grand_total` yang juga tiada. Modul sebut harga **tidak boleh digunakan**.
 *
 * PEMBETULAN: tambah kolum yang hilang sebagai nullable (tiada data sejarah
 * untuk diisi). Kolum `quotation_date`/`valid_until`/`total_amount` yang sedia
 * ada dikekalkan; `issue_date`/`expiry_date`/`grand_total` ialah alias yang
 * digunakan kod. Satu sumber kebenaran disyorkan kemudian (lihat nota bawah).
 *
 * ⚠️ NOTA TEKNIKAL — dua set nama untuk konsep sama:
 *     quotation_date ↔ issue_date
 *     valid_until    ↔ expiry_date
 *     total_amount   ↔ grand_total
 * Untuk elak migrasi data besar sekarang, kolum baharu ditambah dan
 * `Quotation` model dikemas kini untuk menyegerakkan keduanya (lihat
 * `booted()` dalam model). Bersihkan selepas tiada data lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotations')) {
            return;
        }

        $tambah = [];

        // [nama_kolum, jenis, nullable/default]
        $calon = [
            ['customer_name', 'string'],
            ['customer_email', 'string'],
            ['customer_phone', 'string'],
            ['issue_date', 'date'],
            ['expiry_date', 'date'],
            ['validity_days', 'integer'],
            ['tax_total', 'decimal'],
            ['discount_total', 'decimal'],
            ['shipping_total', 'decimal'],
            ['grand_total', 'decimal'],
            ['exchange_rate', 'decimal'],
            ['accepted_at', 'datetime'],
            ['rejected_at', 'datetime'],
            ['converted_to_order_id', 'integer'],
            ['converted_at', 'datetime'],
            ['created_by', 'integer'],
        ];

        foreach ($calon as [$kolum, $jenis]) {
            if (! Schema::hasColumn('quotations', $kolum)) {
                $tambah[] = [$kolum, $jenis];
            }
        }

        if ($tambah === []) {
            return;
        }

        Schema::table('quotations', function (Blueprint $t) use ($tambah) {
            foreach ($tambah as [$kolum, $jenis]) {
                match ($jenis) {
                    'date' => $t->date($kolum)->nullable(),
                    'datetime' => $t->dateTime($kolum)->nullable(),
                    'integer' => $t->unsignedBigInteger($kolum)->nullable(),
                    'decimal' => $t->decimal($kolum, 15, 2)->default(0),
                    default => $t->string($kolum)->nullable(),
                };
            }
        });
    }

    public function down(): void
    {
        // Tidak dibalikkan — membuang kolum akan memusnahkan data sebut harga.
    }
};
