<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Betulkan jadual `journal_entry_lines` — tambah kolum `created_at` & `updated_at`.
 *
 * ❗ Model `JournalEntryLine` menggunakan `HasFactory` (timestamps aktif) tetapi
 * migration asal tidak pernah menambah kolum timestamp. Setiap cuba post jurnal
 * (contoh: B-Expense `postKeJurnal()`) akan gagal dengan:
 *   "table journal_entry_lines has no column named updated_at"
 *
 * Ini pepatch backwards-compatible — migration asal tidak diubah supaya
 * deployment yang sudah ada tidak rosak.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('journal_entry_lines')) {
            return;
        }

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            if (! Schema::hasColumn('journal_entry_lines', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('journal_entry_lines', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('journal_entry_lines')) {
            return;
        }

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entry_lines', 'updated_at')) {
                $table->dropColumn('updated_at');
            }

            if (Schema::hasColumn('journal_entry_lines', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
