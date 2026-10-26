<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenance;
use App\Models\AssetMovement;
use App\Models\TenantUser;
use App\Modules\Assets\AssetService;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    private function tid(): int
    {
        return app(TenantContext::class)->id();
    }

    public function categories()
    {
        return response()->json(AssetCategory::orderBy('name')->get());
    }

    public function saveCategory(Request $r, ?int $id = null)
    {
        $d = $r->validate(['name' => 'required|string|max:100', 'useful_life_months' => 'required|integer|min:1|max:600', 'method' => 'required|in:garis_lurus,baki_berkurangan', 'residual_pct' => 'nullable|numeric|min:0|max:100']);
        $c = $id ? AssetCategory::findOrFail($id) : new AssetCategory;
        $c->fill($d)->save();
        app(AuditLogger::class)->log('assets.category_saved', $c, null, $d);

        return response()->json($c, $id ? 200 : 201);
    }

    public function index(Request $r, AssetService $svc)
    {
        return response()->json(Asset::with('category:id,name')->when($r->query('status'), fn ($q, $s) => $q->where('status', $s))->orderBy('tag')->get()
            ->map(fn ($a) => $a->toArray() + ['book_value' => $svc->bookValue($a)]));
    }

    public function store(Request $r, AssetService $svc)
    {
        $d = $r->validate(['tag' => ['required', 'string', 'max:30', Rule::unique('assets')->where('tenant_id', $this->tid())], 'name' => 'required|string|max:150',
            'category_id' => ['required', 'integer', Rule::exists('asset_categories', 'id')->where('tenant_id', $this->tid())], 'serial_no' => 'nullable|string|max:80',
            'location' => 'nullable|string|max:150', 'custodian_id' => 'nullable|integer', 'supplier' => 'nullable|string|max:150', 'purchase_date' => 'required|date',
            'depreciation_start' => 'nullable|date|after_or_equal:purchase_date', 'cost' => 'required|numeric|gt:0', 'residual_value' => 'nullable|numeric|min:0|lt:cost',
            'useful_life_months' => 'nullable|integer|min:1|max:600', 'method' => 'nullable|in:garis_lurus,baki_berkurangan', 'warranty_until' => 'nullable|date']);
        abort_if(! empty($d['custodian_id']) && ! TenantUser::where('user_id', $d['custodian_id'])->exists(), 422, 'Penjaga bukan ahli syarikat.');
        $c = AssetCategory::find($d['category_id']);
        $a = Asset::create($d + ['depreciation_start' => $d['depreciation_start'] ?? $d['purchase_date'], 'useful_life_months' => $d['useful_life_months'] ?? $c->useful_life_months,
            'method' => $d['method'] ?? $c->method, 'residual_value' => $d['residual_value'] ?? round($d['cost'] * $c->residual_pct / 100, 2), 'status' => 'aktif']);
        $svc->buildSchedule($a);
        app(AuditLogger::class)->log('assets.created', $a, null, ['tag' => $a->tag, 'cost' => $a->cost]);

        return response()->json($a, 201);
    }

    public function show(int $id, AssetService $svc)
    {
        $a = Asset::with('category')->findOrFail($id);

        return response()->json(['asset' => $a, 'book_value' => $svc->bookValue($a), 'schedule' => $a->depreciations()->orderBy('period')->get(),
            'maintenances' => AssetMaintenance::where('asset_id', $a->id)->orderByDesc('scheduled_on')->get(), 'movements' => AssetMovement::where('asset_id', $a->id)->orderByDesc('id')->get()]);
    }

    public function postDepreciation(Request $r, AssetService $svc)
    {
        $d = $r->validate(['period' => 'required|date_format:Y-m']);

        return response()->json($svc->postUntil($d['period']));
    }

    public function move(Request $r, int $id, AssetService $svc)
    {
        $d = $r->validate(['to_location' => 'nullable|string|max:150', 'to_custodian_id' => 'nullable|integer', 'moved_on' => 'nullable|date', 'note' => 'nullable|string|max:255']);
        abort_if(! empty($d['to_custodian_id']) && ! TenantUser::where('user_id', $d['to_custodian_id'])->exists(), 422, 'Penjaga bukan ahli syarikat.');

        return response()->json($svc->move(Asset::findOrFail($id), $d), 201);
    }

    public function dispose(Request $r, int $id, AssetService $svc)
    {
        $d = $r->validate(['date' => 'required|date', 'proceeds' => 'required|numeric|min:0', 'reason' => 'required|string|min:5|max:255']);

        return response()->json($svc->dispose(Asset::findOrFail($id), $d['date'], $d['proceeds'], $d['reason']));
    }

    public function scheduleMaintenance(Request $r, int $id)
    {
        $a = Asset::findOrFail($id);
        $d = $r->validate(['type' => 'required|in:pencegahan,pembaikan,pemeriksaan', 'scheduled_on' => 'required|date', 'vendor' => 'nullable|string|max:150', 'notes' => 'nullable|string|max:2000', 'repeat_months' => 'nullable|integer|min:1|max:120']);
        $m = AssetMaintenance::create($d + ['asset_id' => $a->id, 'status' => 'dijadual']);
        if ($d['type'] === 'pembaikan') {
            $a->update(['status' => 'dalam_baik_pulih']);
        }
        app(AuditLogger::class)->log('assets.maintenance_scheduled', $m, null, $d);

        return response()->json($m, 201);
    }

    public function completeMaintenance(Request $r, int $id, AssetService $svc)
    {
        $d = $r->validate(['done_on' => 'nullable|date', 'cost' => 'nullable|numeric|min:0', 'notes' => 'nullable|string|max:2000']);
        $m = $svc->completeMaintenance(AssetMaintenance::findOrFail($id), $d);
        Asset::where('id', $m->asset_id)->where('status', 'dalam_baik_pulih')->update(['status' => 'aktif']);

        return response()->json($m);
    }

    public function due(Request $r)
    {
        return response()->json([
            'maintenance' => AssetMaintenance::where('status', 'dijadual')->where('scheduled_on', '<=', now()->addDays((int) $r->query('days', 14))->toDateString())->orderBy('scheduled_on')->get(),
            'warranty_expiring' => Asset::where('status', '!=', 'dilupus')->whereNotNull('warranty_until')->whereBetween('warranty_until', [now()->toDateString(), now()->addDays(60)->toDateString()])->get(['id', 'tag', 'name', 'warranty_until']),
        ]);
    }
}
