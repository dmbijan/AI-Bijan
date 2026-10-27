<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * T-111 (28 Okt 2026): `boardroom_findings` tiada `tenant_id` — dapatan sesi
 * hanya dikaitkan melalui `session_id`. Sebarang query terus
 * `BoardroomFinding::where(...)` merentas tenant.
 *
 * Nilai diisi daripada sesi induk (backfill), kemudian kolum dikuatkuasakan
 * melalui trait `BelongsToTenant` pada model.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('boardroom_findings', 'tenant_id')) {
            return;
        }

        Schema::table('boardroom_findings', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
        });

        // Backfill daripada sesi induk — satu statement (SQLite & MySQL serasi).
        DB::statement('
            UPDATE boardroom_findings
            SET tenant_id = (
                SELECT boardroom_sessions.tenant_id
                FROM boardroom_sessions
                WHERE boardroom_sessions.id = boardroom_findings.session_id
            )
            WHERE tenant_id IS NULL
        ');
    }

    public function down(): void
    {
        if (Schema::hasColumn('boardroom_findings', 'tenant_id')) {
            Schema::table('boardroom_findings', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
