<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pengguna dicipta melalui jemputan belum ada kata laluan sendiri; mesti tetapkan semasa terima jemputan.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->boolean('must_set_password')->default(false)->after('password'));
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('must_set_password'));
    }
};
