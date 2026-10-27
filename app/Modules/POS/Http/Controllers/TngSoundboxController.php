<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\POSTngNotification;
use App\Modules\POS\Models\POSSale;
use App\Modules\POS\Services\TngSoundboxService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

/**
 * Notifikasi TnG soundbox (BAB 88 / T-82).
 */
class TngSoundboxController extends Controller
{
    public function __construct(private TngSoundboxService $tngService)
    {
    }

    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    /**
     * Terima notifikasi bayaran (dipanggil oleh listener/soundbox atau input manual).
     */
    public function receive(Request $request)
    {
        if (! $this->tngService->diaktifkan()) {
            return response()->json(['message' => 'TNG soundbox tidak diaktifkan.'], 409);
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:500',
        ]);

        $hasil = $this->tngService->rekod(
            $this->tenantId(),
            (float) $data['amount'],
            $data['reference'] ?? null,
            $data['message'] ?? null
        );

        return response()->json($hasil, $hasil['matched'] ? 200 : 202);
    }

    /** Notifikasi terkumpul yang belum dipadankan. */
    public function unclaimed()
    {
        return response()->json([
            'data' => POSTngNotification::where('status', POSTngNotification::STATUS_UNCLAIMED)
                ->orderByDesc('received_at')
                ->limit(100)
                ->get(),
        ]);
    }

    /** Padanan manual satu notifikasi → satu jualan. */
    public function match(Request $request, int $id)
    {
        $data = $request->validate(['sale_id' => 'required|integer']);

        $notifikasi = POSTngNotification::findOrFail($id);
        $jualan = POSSale::findOrFail($data['sale_id']);

        $this->tngService->matchManual($notifikasi, $jualan, (int) $request->user()->id, $this->tenantId());

        return response()->json(['message' => 'Bayaran TnG dipadankan.', 'sale_id' => $jualan->id]);
    }
}
