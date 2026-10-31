<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIN-017 Rekonsiliasi Bank + AUT-013 suapan bank (import penyata CSV).
 * Baca sahaja: tiada bayaran dimulakan dari sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name', 100);                 // "Maybank Operasi"
            $t->string('bank_name', 60)->nullable();
            $t->string('account_last4', 4)->nullable(); // hanya 4 digit akhir disimpan
            $t->foreignId('gl_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $t->string('currency', 3)->default('MYR');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('bank_statement_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $t->date('txn_date');
            $t->string('description', 500)->nullable();
            $t->string('reference', 120)->nullable();
            $t->decimal('amount', 18, 2);            // + masuk, - keluar
            $t->decimal('balance', 18, 2)->nullable();
            $t->char('line_hash', 64);               // elak import berganda
            $t->string('import_batch', 26)->index();
            $t->string('status', 12)->default('unmatched'); // unmatched | matched | ignored
            $t->string('matched_type', 20)->nullable();     // payment | payment_link
            $t->unsignedBigInteger('matched_id')->nullable();
            $t->string('match_rule', 20)->nullable();       // reference | amount_date | manual
            $t->foreignId('matched_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('matched_at')->nullable();
            $t->timestamps();
            $t->unique(['bank_account_id', 'line_hash']);
            $t->index(['tenant_id', 'bank_account_id', 'status']);
            $t->index(['matched_type', 'matched_id']);
        });

        Schema::table('payment_links', function (Blueprint $t) {
            $t->boolean('is_reconciled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('payment_links', fn (Blueprint $t) => $t->dropColumn('is_reconciled'));
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_accounts');
    }
};
