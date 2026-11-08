<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah lajur `settings` pada `dictation_sessions`.
 *
 * Diperlukan oleh integrasi OpenWhispr → VoiceStudio (narasi): rekod
 * `narasi_track_id` & `narasi_audio_url` disimpan di sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dictation_sessions') && ! Schema::hasColumn('dictation_sessions', 'settings')) {
            Schema::table('dictation_sessions', function (Blueprint $t) {
                $t->json('settings')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('dictation_sessions') && Schema::hasColumn('dictation_sessions', 'settings')) {
            Schema::table('dictation_sessions', function (Blueprint $t) {
                $t->dropColumn('settings');
            });
        }
    }
};
