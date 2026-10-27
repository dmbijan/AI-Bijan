<?php

namespace App\Modules\POS\Services;

use App\Models\Item;
use App\Modules\POS\Models\POSModifierOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Modifier / add-on (BAB 88 / T-77).
 *
 * Dipindah daripada B-POS `ModifierService`, dengan perbezaan penting:
 * B-POS menyimpan `option_id` dalam snapshot. AI BIJAN menyimpan juga NAMA dan
 * HARGA pada masa jualan, supaya menukar harga modifier kemudian tidak
 * mengubah sejarah resit (keperluan audit/e-Invois).
 */
class ModifierService
{
    /**
     * Sahkan pilihan modifier dan bina snapshot.
     *
     * @param  array<int, int|string>  $optionIds
     * @return array{modifiers: array<int, array<string, mixed>>, total: float, key: string|null}
     */
    public function resolve(Item $item, array $optionIds): array
    {
        $kumpulan = $this->kumpulanUntuk($item);
        $optionIds = collect($optionIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($kumpulan->isEmpty()) {
            if ($optionIds->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'modifiers' => "{$item->name} tiada pilihan tambahan.",
                ]);
            }

            return ['modifiers' => [], 'total' => 0.0, 'key' => null];
        }

        $pilihan = POSModifierOption::query()
            ->whereIn('id', $optionIds)
            ->whereIn('modifier_group_id', $kumpulan->pluck('id'))
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($pilihan->count() !== $optionIds->count()) {
            throw ValidationException::withMessages([
                'modifiers' => "Sebahagian pilihan tidak tersedia untuk {$item->name}.",
            ]);
        }

        foreach ($kumpulan as $g) {
            $dipilih = $pilihan->where('modifier_group_id', $g->id)->count();

            if ($dipilih < $g->min_select) {
                throw ValidationException::withMessages([
                    'modifiers' => "Sila pilih {$g->name} untuk {$item->name}.",
                ]);
            }

            if ($g->max_select > 0 && $dipilih > $g->max_select) {
                throw ValidationException::withMessages([
                    'modifiers' => "Maksimum {$g->max_select} pilihan bagi {$g->name}.",
                ]);
            }
        }

        $namaKumpulan = $kumpulan->pluck('name', 'id');

        $snapshot = $pilihan
            ->sortBy([
                fn ($o) => $kumpulan->search(fn ($g) => $g->id === $o->modifier_group_id),
                fn ($o) => $o->sort_order,
                fn ($o) => $o->id,
            ])
            ->map(fn (POSModifierOption $o) => [
                'option_id' => $o->id,
                'group' => $namaKumpulan[$o->modifier_group_id] ?? null,
                'name' => $o->name,
                'price' => (float) $o->price,
            ])
            ->values()
            ->all();

        $total = array_sum(array_column($snapshot, 'price'));

        return [
            'modifiers' => $snapshot,
            'total' => round((float) $total, 2),
            // Kunci stabil untuk menggabungkan baris yang sama dalam cart
            'key' => $snapshot ? sha1(implode(',', $optionIds->sort()->all())) : null,
        ];
    }

    /**
     * Kumpulan modifier aktif untuk satu item (melalui pivot `pos_product_modifiers`).
     */
    public function kumpulanUntuk(Item $item)
    {
        $id = DB::table('pos_product_modifiers')
            ->where('tenant_id', $item->tenant_id)
            ->where('item_id', $item->id)
            ->orderBy('sort_order')
            ->pluck('modifier_group_id');

        if ($id->isEmpty()) {
            return collect();
        }

        return \App\Modules\POS\Models\POSModifierGroup::query()
            ->whereIn('id', $id)
            ->where('is_active', true)
            ->with(['options' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Muatan kumpulan + pilihan untuk pemilih POS/QR menu.
     *
     * @param  iterable<Item>  $items
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function untukItem(iterable $items): array
    {
        $keluar = [];

        foreach ($items as $item) {
            $kumpulan = $this->kumpulanUntuk($item);

            if ($kumpulan->isEmpty()) {
                continue;
            }

            $keluar[$item->id] = $kumpulan->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'min' => $g->min_select,
                'max' => $g->max_select,
                'options' => $g->options->map(fn ($o) => [
                    'id' => $o->id,
                    'name' => $o->name,
                    'price' => (float) $o->price,
                ])->values()->all(),
            ])->values()->all();
        }

        return $keluar;
    }
}
