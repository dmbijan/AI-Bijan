<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Kaedah bayaran jadi string supaya kaedah baharu (cth. DuitNow QR) tak perlu ubah enum lagi. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sale_payments', function (Blueprint $table) {
            $table->string('payment_method', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('pos_sale_payments', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'card', 'ewallet', 'credit', 'voucher', 'split'])->change();
        });
    }
};
