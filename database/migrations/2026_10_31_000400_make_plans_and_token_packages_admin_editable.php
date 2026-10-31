<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Harga & pakej langganan diurus sepenuhnya oleh Super Admin (keputusan owner 31/10/2026).
 * Sebelum ini harga Billing dan pakej token AI ditetapkan dalam config kod.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $t) {
            $t->boolean('token_required')->default(true);
        });

        Schema::create('token_packages', function (Blueprint $t) {
            $t->id();
            $t->string('code', 40)->unique();
            $t->string('name');
            $t->unsignedBigInteger('tokens');
            $t->unsignedBigInteger('usage_limit')->nullable();   // had guna sebenar (boleh > token dijual)
            $t->decimal('price', 12, 2);
            $t->decimal('supplier_cost', 12, 2)->nullable();       // kos pembekal AI — dalaman, untuk margin
            $t->unsignedSmallInteger('validity_days')->default(30);
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_packages');
        Schema::table('plans', function (Blueprint $t) {
            $t->dropColumn('token_required');
        });
    }
};
