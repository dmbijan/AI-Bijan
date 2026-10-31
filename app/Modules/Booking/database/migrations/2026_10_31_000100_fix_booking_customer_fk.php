<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi asal Booking merujuk `crm_clients` yang tak wujud, jadi temujanji dengan pelanggan gagal disimpan.
 * Halakan semula ke jadual sebenar `customers`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings_appointments', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings_appointments', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('crm_clients')->nullOnDelete();
        });
    }
};
