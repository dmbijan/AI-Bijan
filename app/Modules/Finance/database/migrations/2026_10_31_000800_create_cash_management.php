<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIN-018 Pengurusan Tunai: peti tunai kecil (petty cash) & peti besi, kiraan ikut denominasi,
 * deposit tunai ke bank (dipadankan dalam rekonsiliasi bank). Laci POS kekal di modul POS (cash_shifts).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_boxes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name', 100);
            $t->string('type', 10)->default('petty');          // petty | safe
            $t->decimal('float_amount', 18, 2)->default(0);    // paras imprest
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('cash_box_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('cash_box_id')->constrained()->cascadeOnDelete();
            $t->date('entry_date');
            $t->string('type', 12);                  // in | out | deposit | adjust
            $t->decimal('amount', 18, 2);            // bertanda: + masuk peti, - keluar peti
            $t->string('category', 60)->nullable();  // cth. "Pengangkutan", "Alat tulis"
            $t->string('description', 255)->nullable();
            $t->string('reference', 60)->nullable(); // no resit / slip deposit
            $t->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete(); // deposit
            $t->foreignId('cash_shift_id')->nullable()->constrained('cash_shifts')->nullOnDelete(); // terima dari laci POS
            $t->boolean('is_reconciled')->default(false); // deposit dipadankan dengan penyata bank
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['tenant_id', 'cash_box_id', 'entry_date']);
        });

        Schema::create('cash_counts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('cash_box_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignId('cash_shift_id')->nullable()->constrained('cash_shifts')->nullOnDelete();
            $t->json('denominations');               // {"100":2,"50":1,"0.50":4}
            $t->decimal('counted_total', 18, 2);
            $t->decimal('expected_total', 18, 2);
            $t->decimal('variance', 18, 2);          // dikira - dijangka
            $t->string('note', 255)->nullable();
            $t->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_counts');
        Schema::dropIfExists('cash_box_entries');
        Schema::dropIfExists('cash_boxes');
    }
};
