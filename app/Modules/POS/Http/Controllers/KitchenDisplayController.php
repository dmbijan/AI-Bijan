<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\POSSale;
use App\Modules\POS\Services\KitchenService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

/**
 * Paparan Dapur (KDS) (BAB 88 / T-76).
 */
class KitchenDisplayController extends Controller
{
    public function __construct(private KitchenService $kitchenService)
    {
    }

    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    /** Tiket aktif untuk dapur — paling lama dahulu. */
    public function index()
    {
        $tiket = $this->kitchenService->tiketAktif($this->tenantId());

        return response()->json([
            'enabled' => $this->kitchenService->diaktifkan(),
            'data' => $tiket->map(fn (POSSale $s) => [
                'id' => $s->id,
                'sale_no' => $s->sale_no,
                'order_type' => $s->order_type,
                'table' => $s->dineTable?->name,
                'kitchen_status' => $s->kitchen_status,
                'queued_at' => $s->kitchen_queued_at?->toIso8601String(),
                'waiting_minutes' => $s->kitchen_queued_at
                    ? (int) $s->kitchen_queued_at->diffInMinutes(now())
                    : null,
                'items' => $s->items->map(fn ($i) => [
                    'name' => $i->product_name,
                    'quantity' => $i->quantity,
                    'modifiers' => $i->modifiers,
                    'notes' => $i->notes ?? null,
                ])->values(),
            ])->values(),
        ]);
    }

    /** Alih tiket ke status baharu (new|preparing|ready|served). */
    public function updateStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', KitchenService::ALIRAN),
        ]);

        $sale = POSSale::findOrFail($id);
        $this->kitchenService->moveTo($sale, $data['status']);

        return response()->json([
            'message' => 'Status dapur dikemas kini.',
            'kitchen_status' => $sale->kitchen_status,
            'next' => $this->kitchenService->next($sale->kitchen_status),
        ]);
    }
}
