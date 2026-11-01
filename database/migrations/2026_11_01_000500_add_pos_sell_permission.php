<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Juruwang tak boleh checkout kerana semua kerja kaunter dikunci di bawah pos.manage.
// pos.sell = kerja kaunter harian; diberi kepada Juruwang dan setiap peranan yang sudah ada pos.manage.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->insertOrIgnore(['key' => 'pos.sell', 'module' => 'pos', 'description' => 'Jual di kaunter: checkout, bayaran, buka/tutup syif, pesanan meja']);
        $sell = DB::table('permissions')->where('key', 'pos.sell')->value('id');
        $manage = DB::table('permissions')->where('key', 'pos.manage')->value('id');
        $roles = DB::table('roles')->where('key', 'cashier')->pluck('id')
            ->merge($manage ? DB::table('permission_role')->where('permission_id', $manage)->pluck('role_id') : [])->unique();
        foreach ($roles as $roleId) {
            DB::table('permission_role')->insertOrIgnore(['role_id' => $roleId, 'permission_id' => $sell]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->where('key', 'pos.sell')->delete();
    }
};
