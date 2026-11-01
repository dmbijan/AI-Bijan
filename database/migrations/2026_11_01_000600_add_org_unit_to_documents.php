<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// OAM skop data untuk jualan & kewangan: dokumen ditanda dengan unit pencipta (cawangan/jabatan).
return new class extends Migration
{
    private const TABLES = ['invoices', 'quotations', 'sales_orders', 'customers', 'leads', 'opportunities', 'journal_entries'];

    public function up(): void
    {
        foreach (self::TABLES as $tbl) {
            if (Schema::hasTable($tbl) && ! Schema::hasColumn($tbl, 'org_unit_id')) {
                Schema::table($tbl, function (Blueprint $t) {
                    $t->foreignId('org_unit_id')->nullable()->constrained('org_units')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tbl) {
            if (Schema::hasColumn($tbl, 'org_unit_id')) {
                Schema::table($tbl, fn (Blueprint $t) => $t->dropConstrainedForeignId('org_unit_id'));
            }
        }
    }
};
