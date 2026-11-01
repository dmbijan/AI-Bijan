<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIN-010 Cukai SST Malaysia: kod cukai bertarikh, tetapan pendaftaran, draf SST-02.
 * Kadar lalai disemai per tenant dan BOLEH diubah — sahkan dengan JKDM/MySST sebelum guna.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_codes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('code', 10);
            $t->string('name', 120);
            $t->string('type', 10);                    // sales | service | exempt | none
            $t->decimal('rate', 5, 2)->default(0);     // peratus
            $t->date('effective_from');
            $t->date('effective_to')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['tenant_id', 'code', 'effective_from']);
        });

        Schema::create('tax_settings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('sst_registration_no', 40)->nullable();
            $t->date('sst_registered_from')->nullable();
            $t->unsignedTinyInteger('period_start_month')->default(1); // bulan pertama kitaran 2-bulan
            $t->string('default_tax_code', 10)->nullable();
            $t->timestamps();
        });

        Schema::table('pos_sale_items', fn (Blueprint $t) => $t->string('tax_code', 10)->nullable());
        Schema::table('invoice_items', fn (Blueprint $t) => $t->string('tax_code', 10)->nullable());
    }

    public function down(): void
    {
        Schema::table('invoice_items', fn (Blueprint $t) => $t->dropColumn('tax_code'));
        Schema::table('pos_sale_items', fn (Blueprint $t) => $t->dropColumn('tax_code'));
        Schema::dropIfExists('tax_settings');
        Schema::dropIfExists('tax_codes');
    }
};
