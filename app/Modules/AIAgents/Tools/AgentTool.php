<?php

namespace App\Modules\AIAgents\Tools;

/**
 * Alat ejen AI yang SEBENAR (Master §11.3).
 *
 * - Memanggil servis/model domain sedia ada, bukan SQL terus.
 * - `permission()` disemak terhadap pengguna yang memanggil ejen: ejen tak pernah lebih berkuasa.
 * - `risk() === 'tinggi'` → masuk Peti Kelulusan dahulu (pagar owner: wang/harga, hantar keluar,
 *   padam, data pelanggan). Hanya dilaksanakan selepas diluluskan.
 * - `verify()` membaca semula keadaan selepas tindakan; ejen tak boleh lapor "siap" tanpa bukti.
 */
abstract class AgentTool
{
    public const RENDAH = 'rendah';

    public const TINGGI = 'tinggi';

    abstract public function name(): string;

    abstract public function description(): string;

    /** Peraturan validasi Laravel untuk input. */
    abstract public function rules(): array;

    /** Kebenaran OAM yang diperlukan (module.action). */
    abstract public function permission(): string;

    abstract public function handle(array $p): array;

    public function risk(): string
    {
        return self::RENDAH;
    }

    /** Ringkasan manusia untuk Peti Kelulusan. */
    public function summary(array $p): string
    {
        return $this->description();
    }

    /** Sahkan hasil benar-benar berlaku. Lalai: tindakan baca sahaja, tiada apa perlu disahkan. */
    public function verify(array $p, array $hasil): bool
    {
        return true;
    }
}
