<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Token QR meja: pelanggan imbas QR di meja untuk pesan sendiri (laluan awam /meja/{token}). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_dine_tables', function (Blueprint $t) {
            $t->string('qr_token', 40)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('pos_dine_tables', function (Blueprint $t) {
            $t->dropUnique(['qr_token']);
            $t->dropColumn('qr_token');
        });
    }
};
