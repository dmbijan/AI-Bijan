<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// OAM skop data: tautkan gudang, kaunter POS dan pekerja kepada unit organisasi (cawangan/jabatan).
// Rekod tanpa unit = "ibu pejabat / belum ditetapkan": hanya kelihatan kepada ahli berskop seluruh organisasi.
return new class extends Migration
{
    public function up(): void
    {
        foreach (['warehouses', 'pos_registers', 'hrms_employees'] as $tbl) {
            Schema::table($tbl, function (Blueprint $t) {
                $t->foreignId('org_unit_id')->nullable()->constrained('org_units')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['warehouses', 'pos_registers', 'hrms_employees'] as $tbl) {
            Schema::table($tbl, fn (Blueprint $t) => $t->dropConstrainedForeignId('org_unit_id'));
        }
    }
};
