<?php

namespace App\Support\WhatsApp;

/**
 * Pautan wa.me (Fasa 1.5a): buka WhatsApp dengan mesej siap ditaip. Tiada API, tiada kos,
 * pengguna sendiri tekan Hantar — jadi tiada mesej keluar tanpa tindakan manusia (pagar owner #3).
 */
class PautanWhatsApp
{
    /** Normalkan nombor Malaysia: "012-345 6789" / "+60 12 3456789" / "0060..." → "60123456789". */
    public static function nombor(?string $telefon): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $telefon);
        if ($d === '') {
            return null;
        }
        if (str_starts_with($d, '00')) {
            $d = substr($d, 2);
        }
        if (str_starts_with($d, '0')) {
            $d = '6'.$d;              // 012… → 6012…
        } elseif (str_starts_with($d, '1') && strlen($d) <= 10) {
            $d = '60'.$d;             // 12… → 6012…
        }

        return (strlen($d) >= 10 && strlen($d) <= 15) ? $d : null;
    }

    public static function url(?string $telefon, string $teks): string
    {
        $n = self::nombor($telefon);

        return 'https://wa.me/'.($n ?? '').'?text='.rawurlencode($teks);
    }

    /** Resit ringkas jualan POS untuk dihantar kepada pelanggan. */
    public static function teksResit(\App\Modules\POS\Models\POSSale $s, string $syarikat): string
    {
        $rm = fn ($n) => 'RM '.number_format((float) $n, 2);
        $b = ["*{$syarikat}*", "Resit: {$s->sale_no}", 'Tarikh: '.optional($s->completed_at ?? $s->created_at)->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i'), ''];
        foreach ($s->items as $i) {
            $q = rtrim(rtrim(number_format((float) $i->quantity, 3, '.', ''), '0'), '.');
            $b[] = "{$q} × {$i->product_name} — {$rm($i->line_total)}";
        }
        $b[] = '';
        $b[] = "*Jumlah: {$rm($s->total_amount)}*";
        if ($s->payment_method) {
            $label = ['cash' => 'Tunai', 'card' => 'Kad', 'ewallet' => 'E-Wallet', 'tng' => "Touch 'n Go", 'duitnow' => 'DuitNow QR',
                'credit' => 'Kredit', 'voucher' => 'Baucar', 'split' => 'Berpisah'];
            $b[] = 'Bayaran: '.($label[$s->payment_method] ?? $s->payment_method);
        }
        $b[] = '';
        $b[] = 'Terima kasih! 🙏';

        return implode("\n", $b);
    }
}
