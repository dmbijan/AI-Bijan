<?php

namespace App\Modules\HRMS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use App\Modules\HRMS\Models\BiometricAccessLog;
use App\Modules\HRMS\Models\BiometricConsent;
use App\Modules\HRMS\Models\BiometricEmbedding;
use App\Modules\HRMS\Services\BiometricGateService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

/**
 * Pagar consent biometrik (BAB 56C.6A).
 *
 * Semua endpoint di sini mengawal face recognition. Tiada padanan wajah
 * dibenarkan selagi pagar tidak lengkap.
 */
class BiometricController extends Controller
{
    public function __construct(private BiometricGateService $gate) {}

    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    /** Konfigurasi pagar + status kelengkapan. */
    public function settings()
    {
        $s = $this->gate->tetapan();
        $gagal = $s ? $s->pagarBelumLengkap() : (new \App\Modules\HRMS\Models\BiometricSetting)->pagarBelumLengkap();

        return response()->json([
            'settings' => $s,
            'pending_gates' => $gagal,
            'can_enable' => $gagal === [],
            'allowed_purposes' => BiometricGateService::tujuanDibenarkan(),
            'forbidden_purposes' => BiometricGateService::tujuanDilarang(),
        ]);
    }

    /** Simpan pagar. Menghidupkan tanpa pagar lengkap → 422. */
    public function simpanSettings(Request $request)
    {
        $d = $request->validate([
            'enabled' => 'sometimes|boolean',
            'alternative_method' => 'nullable|in:'.implode(',', \App\Modules\HRMS\Models\BiometricSetting::ALTERNATIF),
            'dpo_name' => 'nullable|string|max:150',
            'dpo_email' => 'nullable|email|max:150',
            'retention_days' => 'nullable|integer|min:1|max:365',
            'consent_version' => 'nullable|string|max:20',
            'legal_review_date' => 'nullable|date',
            'privacy_notice' => 'nullable|string|max:5000',
        ]);

        return response()->json($this->gate->simpanTetapan($d));
    }

    /** Pekerja menandatangani consent biometrik. */
    public function beriConsent(Request $request)
    {
        $d = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'document_path' => 'nullable|string|max:255',
        ]);

        abort_unless(
            TenantUser::where('user_id', $d['user_id'])->exists(),
            422,
            'Pengguna bukan ahli syarikat ini.'
        );

        $s = $this->gate->tetapan();
        abort_unless($s && $s->consent_version, 422, 'Versi consent belum ditetapkan oleh syarikat.');

        $c = BiometricConsent::create([
            'user_id' => $d['user_id'],
            'consent_version' => $s->consent_version,
            'signed_at' => now(),
            'document_path' => $d['document_path'] ?? null,
            'ip_address' => $request->ip(),
        ]);

        return response()->json($c, 201);
    }

    /** Tarik balik consent → embedding dipadam. */
    public function tarikConsent(Request $request)
    {
        $d = $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $c = BiometricConsent::aktifUntuk($d['user_id']);

        abort_unless($c, 404, 'Tiada consent aktif untuk pengguna ini.');

        $c->tarikBalik();

        return response()->json(['message' => 'Consent ditarik balik dan embedding dipadam.']);
    }

    /** Siapa sudah / belum beri consent. */
    public function statusConsent()
    {
        $ahli = TenantUser::with('user:id,name,email')->get();

        $baris = $ahli->map(function ($m) {
            $c = BiometricConsent::aktifUntuk($m->user_id);

            return [
                'user_id' => $m->user_id,
                'name' => $m->user?->name,
                'email' => $m->user?->email,
                'consent' => $c ? ['version' => $c->consent_version, 'signed_at' => $c->signed_at] : null,
                'has_embedding' => BiometricEmbedding::where('user_id', $m->user_id)->exists(),
            ];
        });

        return response()->json([
            'total' => $baris->count(),
            'with_consent' => $baris->whereNotNull('consent')->count(),
            'rows' => $baris->values(),
        ]);
    }

    /** Audit log padanan wajah. */
    public function audit(Request $request)
    {
        $q = BiometricAccessLog::query()->orderByDesc('id');

        if ($u = $request->query('user_id')) {
            $q->where('user_id', $u);
        }
        if ($outcome = $request->query('outcome')) {
            $q->where('outcome', $outcome);
        }

        return response()->json($q->paginate(50));
    }
}
