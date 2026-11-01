<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** FIN-005 FP&A: bajet tahunan ikut akaun & bulan, dibanding dengan jurnal yang diposting. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name', 120);
            $t->unsignedSmallInteger('fiscal_year');
            $t->string('status', 12)->default('draft'); // draft | approved
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'fiscal_year', 'name']);
        });

        Schema::create('budget_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $t->foreignId('account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $t->unsignedTinyInteger('month'); // 1-12
            $t->decimal('amount', 18, 2);
            $t->timestamps();
            $t->unique(['budget_id', 'account_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
        Schema::dropIfExists('budgets');
    }
};
