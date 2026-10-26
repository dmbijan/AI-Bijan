<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;

/**
 * IdempotencyMiddleware — pastikan request berulang tidak menghasilkan
 * dua rekod / dua bayaran / dua post jurnal.
 *
 * AUDIT BAB 90 (S6): model `IdempotencyKey` sedia ada tetapi TIDAK ada
 * middleware global — setiap modul kena implement sendiri (tidak konsisten).
 *
 * Mekanisme:
 * - Klien hantar header `Idempotency-Key: <uuid>` pada request mutasi kritikal.
 * - Hash request body + response disimpan dalam `result` (JSON).
 * - Request berulang dengan kunci sama → pulangkan response tersimpan.
 * - Kunci sama dengan body BERBEZA → 422 conflict.
 *
 * Skema sedia ada: `idempotency_keys(tenant_id, key, scope, result)`
 * dengan unique `(tenant_id, scope, key)`. `scope` = method + path.
 */
class IdempotencyMiddleware
{
    public const HEADER = 'Idempotency-Key';

    public const METHOD_DILINDUNGI = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next)
    {
        $kunci = trim((string) $request->header(self::HEADER, ''));

        if ($kunci === '' || ! in_array($request->method(), self::METHOD_DILINDUNGI, true)) {
            return $next($request);
        }

        $kunci = substr($kunci, 0, 100);
        $tenantId = (int) app(TenantContext::class)->id();

        // Tiada konteks tenant (middleware ini dijalankan sebelum `tenant`) —
        // biarkan request diteruskan; pemeriksa idempotensi akan berlaku
        // selepas konteks tenant ditetapkan.
        if ($tenantId === 0) {
            return $next($request);
        }

        $scope = $request->method().' '.$request->path();
        $hashBadan = hash('sha256', $request->getContent() ?: '');

        $existing = IdempotencyKey::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('scope', $scope)
            ->where('key', $kunci)
            ->first();

        if ($existing) {
            $tersimpan = (array) ($existing->result ?? []);

            if (($tersimpan['request_hash'] ?? null) !== $hashBadan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Idempotency-Key ini telah digunakan untuk permintaan lain.',
                ], 422);
            }

            if (isset($tersimpan['response_status'], $tersimpan['response_body'])) {
                $response = response(
                    json_decode((string) $tersimpan['response_body'], true) ?? [],
                    (int) $tersimpan['response_status']
                );
                $response->headers->set('Idempotent-Replay', 'true');

                return $response;
            }

            return response()->json([
                'success' => false,
                'message' => 'Permintaan identik sedang diproses. Sila tunggu.',
            ], 409);
        }

        // Claim kunci — unique constraint (tenant, scope, key) mengawal race.
        try {
            IdempotencyKey::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'key' => $kunci,
                'scope' => $scope,
                'result' => ['request_hash' => $hashBadan, 'status' => 'processing'],
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan identik sedang diproses.',
            ], 409);
        }

        $response = $next($request);

        try {
            IdempotencyKey::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('scope', $scope)
                ->where('key', $kunci)
                ->update([
                    'result' => [
                        'request_hash' => $hashBadan,
                        'response_status' => $response->getStatusCode(),
                        'response_body' => $response->getContent(),
                        'status' => 'completed',
                        'completed_at' => now()->toIso8601String(),
                    ],
                ]);
        } catch (\Throwable $e) {
            // Gagal simpan response tidak menggagalkan request asal.
        }

        return $response;
    }
}