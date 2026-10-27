<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\POSModifierGroup;
use App\Modules\POS\Services\ModifierService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Kumpulan modifier + pilihan (BAB 88 / T-77).
 */
class ModifierController extends Controller
{
    public function __construct(private ModifierService $modifierService)
    {
    }

    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    /** Senarai kumpulan beserta pilihan. */
    public function index()
    {
        $kumpulan = POSModifierGroup::where('is_active', true)
            ->with(['options' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $kumpulan]);
    }

    /** Cipta kumpulan (pilihan boleh dihantar serentak). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'min_select' => 'nullable|integer|min:0|max:20',
            'max_select' => 'nullable|integer|min:0|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'options' => 'nullable|array',
            'options.*.name' => 'required|string|max:100',
            'options.*.price' => 'nullable|numeric|min:0',
            'options.*.sort_order' => 'nullable|integer|min:0',
        ]);

        if (($data['max_select'] ?? 1) > 0 && ($data['max_select'] ?? 1) < ($data['min_select'] ?? 0)) {
            return response()->json([
                'message' => 'max_select tidak boleh kurang daripada min_select.',
            ], 422);
        }

        $kumpulan = POSModifierGroup::create([
            'tenant_id' => $this->tenantId(),
            'name' => $data['name'],
            'min_select' => $data['min_select'] ?? 0,
            'max_select' => $data['max_select'] ?? 1,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        foreach ($data['options'] ?? [] as $o) {
            $kumpulan->options()->create([
                'tenant_id' => $this->tenantId(),
                'name' => $o['name'],
                'price' => $o['price'] ?? 0,
                'sort_order' => $o['sort_order'] ?? 0,
                'is_active' => true,
            ]);
        }

        return response()->json(['data' => $kumpulan->fresh('options')], 201);
    }

    public function update(Request $request, int $id)
    {
        $kumpulan = POSModifierGroup::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'min_select' => 'sometimes|integer|min:0|max:20',
            'max_select' => 'sometimes|integer|min:0|max:20',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $kumpulan->update($data);

        return response()->json(['data' => $kumpulan->fresh('options')]);
    }

    /** Lampir kumpulan modifier pada item menu. */
    public function attach(Request $request)
    {
        $data = $request->validate([
            'item_id' => [
                'required',
                Rule::exists('items', 'id')->where('tenant_id', $this->tenantId()),
            ],
            'modifier_group_id' => [
                'required',
                Rule::exists('pos_modifier_groups', 'id')->where('tenant_id', $this->tenantId()),
            ],
            'sort_order' => 'nullable|integer|min:0',
        ]);

        \Illuminate\Support\Facades\DB::table('pos_product_modifiers')->updateOrInsert(
            [
                'item_id' => $data['item_id'],
                'modifier_group_id' => $data['modifier_group_id'],
            ],
            [
                'tenant_id' => $this->tenantId(),
                'sort_order' => $data['sort_order'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json(['message' => 'Kumpulan modifier dilampirkan pada item.']);
    }

    /** Kumpulan modifier untuk sekumpulan item (untuk pemilih POS). */
    public function forItems(Request $request)
    {
        $ids = array_values(array_filter((array) $request->input('item_ids', [])));
        $items = \App\Models\Item::whereIn('id', $ids)->get();

        return response()->json(['data' => $this->modifierService->untukItem($items)]);
    }
}
