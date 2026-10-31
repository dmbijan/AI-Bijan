<?php

namespace App\Modules\AIAgents\Tools;

/** Pendaftar tunggal alat ejen sebenar. Orkestrator dalaman & MCP patut guna senarai yang sama. */
class AgentToolRegistry
{
    /** @var array<class-string<AgentTool>> */
    public const ALAT = [
        PosRingkasanHari::class,
        PosSemakanStok::class,
        StokRendah::class,
        InvoisBelumBayar::class,
        PelangganCari::class,
        ProdukUbahHarga::class,
        PosLaporanTutup::class,
        PenyediaanKedai::class,
    ];

    /** @return array<string, AgentTool> */
    public function all(): array
    {
        $out = [];
        foreach (self::ALAT as $c) {
            $t = app($c);
            $out[$t->name()] = $t;
        }

        return $out;
    }

    public function get(string $name): ?AgentTool
    {
        return $this->all()[$name] ?? null;
    }

    /** Untuk UI/MCP: definisi alat tanpa butiran dalaman. */
    public function definitions(): array
    {
        return array_values(array_map(fn (AgentTool $t) => [
            'name' => $t->name(), 'description' => $t->description(), 'input' => $t->rules(),
            'permission' => $t->permission(), 'risk' => $t->risk(),
        ], $this->all()));
    }
}
