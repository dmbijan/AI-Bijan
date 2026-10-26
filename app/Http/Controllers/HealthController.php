<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Health & readiness (BAB 61 — Observability).
 *
 * `/health`  — liveness: proses hidup sahaja (tiada I/O).
 * `/ready`   — readiness: DB, cache, storan, queue dan migrasi.
 *
 * Endpoint readiness TIDAK mendedahkan kredensial atau laluan fail.
 * Boleh dipantau UptimeRobot / BetterStack tanpa auth.
 */
class HealthController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'env' => app()->environment(),
            'time' => now()->toIso8601String(),
        ]);
    }

    public function ready(): JsonResponse
    {
        $semak = [
            'database' => $this->semakDatabase(),
            'cache' => $this->semakCache(),
            'storage' => $this->semakStorage(),
            'queue' => $this->semakQueue(),
            'migrations' => $this->semakMigrasi(),
        ];

        $gagal = array_filter($semak, fn ($s) => ! $s['ok']);

        return response()->json([
            'status' => $gagal === [] ? 'ready' : 'degraded',
            'checks' => $semak,
            'failed' => array_keys($gagal),
            'time' => now()->toIso8601String(),
        ], $gagal === [] ? 200 : 503);
    }

    /** Ringkasan metrik ringan untuk papan pemuka admin (BAB 61 SLO). */
    public function metrics(): JsonResponse
    {
        $mula = microtime(true);

        $tenant = DB::table('tenants')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $pendingJobs = DB::table('jobs')->count();

        return response()->json([
            'tenants' => $tenant,
            'queue' => ['pending' => $pendingJobs, 'failed' => $failedJobs],
            'db_latency_ms' => round((microtime(true) - $mula) * 1000, 2),
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'time' => now()->toIso8601String(),
        ]);
    }

    /** @return array{ok: bool, detail?: string, latency_ms?: float} */
    private function semakDatabase(): array
    {
        try {
            $t = microtime(true);
            DB::select('select 1');
            $ms = round((microtime(true) - $t) * 1000, 2);

            return ['ok' => true, 'latency_ms' => $ms, 'driver' => DB::connection()->getDriverName()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'detail' => 'database_unreachable'];
        }
    }

    /** @return array{ok: bool, detail?: string} */
    private function semakCache(): array
    {
        try {
            $kunci = 'health:'.bin2hex(random_bytes(4));
            Cache::put($kunci, '1', 5);
            $nilai = Cache::get($kunci);
            Cache::forget($kunci);

            return $nilai === '1' ? ['ok' => true, 'store' => config('cache.default')] : ['ok' => false, 'detail' => 'cache_mismatch'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'detail' => 'cache_unreachable'];
        }
    }

    /** @return array{ok: bool, detail?: string} */
    private function semakStorage(): array
    {
        try {
            $disk = config('filesystems.default');
            Storage::disk($disk)->put('health/.probe', 'ok');

            return ['ok' => Storage::disk($disk)->exists('health/.probe'), 'disk' => $disk];
        } catch (\Throwable $e) {
            return ['ok' => false, 'detail' => 'storage_unwritable'];
        }
    }

    /** @return array{ok: bool, pending?: int, failed?: int} */
    private function semakQueue(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();

            // Antrian menimbun >1000 atau ada kegagalan → bukan sedia.
            return [
                'ok' => $failed === 0 && $pending < 1000,
                'pending' => $pending,
                'failed' => $failed,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false];
        }
    }

    /** @return array{ok: bool, pending?: int} */
    private function semakMigrasi(): array
    {
        try {
            $migrator = app('migrator');
            if (! $migrator->repositoryExists()) {
                return ['ok' => true, 'pending' => 0];
            }
            $dijalankan = $migrator->getRepository()->getRan();
            $semua = array_map(
                fn ($f) => basename($f, '.php'),
                array_merge(
                    glob(database_path('migrations/*.php')) ?: [],
                    glob(base_path('app/Modules/*/database/migrations/*.php')) ?: []
                )
            );
            $belum = array_diff($semua, $dijalankan);

            return ['ok' => $belum === [], 'pending' => count($belum)];
        } catch (\Throwable $e) {
            return ['ok' => false];
        }
    }
}
