<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bayaran pelanggan kedai melalui akaun CHIP KEDAI SENDIRI (keputusan owner 31/10/2026):
 * duit terus ke kedai, AI Bijan tidak memegang duit pelanggan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_payment_gateways', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('provider', 20)->default('chip');
            $t->string('brand_id', 100);
            $t->text('api_key');                 // disulitkan (cast encrypted)
            $t->text('public_key')->nullable();  // kunci awam CHIP untuk sahkan callback
            $t->boolean('is_active')->default(true);
            $t->timestamp('verified_at')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'provider']);
        });

        Schema::create('payment_links', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('provider', 20)->default('chip');
            $t->string('payable_type', 40);       // pos_sale
            $t->unsignedBigInteger('payable_id');
            $t->string('provider_ref', 100)->nullable()->index(); // id pembelian CHIP
            $t->text('url')->nullable();
            $t->decimal('amount', 12, 2);
            $t->string('status', 20)->default('created'); // created | paid | failed | expired
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
            $t->index(['tenant_id', 'payable_type', 'payable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_links');
        Schema::dropIfExists('tenant_payment_gateways');
    }
};
