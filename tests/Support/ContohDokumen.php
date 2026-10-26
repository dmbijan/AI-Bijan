<?php

namespace Tests\Support;

/** Data contoh (rekaan, bukan data sebenar) untuk setiap templat. */
class ContohDokumen
{
    public static function untuk(string $key): array
    {
        $penerima = ['name' => 'Nurul Aisyah binti Ahmad', 'ic_no' => '900101145678', 'staff_id' => 'B002', 'address' => "No. 12, Jalan Melur 3\n43000 Kajang, Selangor", 'position' => 'Eksekutif Jualan'];
        $ttd = ['name' => 'Bijan bin Ali', 'position' => 'Pengarah Urusan'];
        $pihak = ['name' => 'Syarikat Contoh Sdn. Bhd.', 'reg_no' => '202001012345 (1234567-A)', 'tin' => 'C1234567890', 'address' => "Lot 5, Jalan Industri 2\n40000 Shah Alam, Selangor", 'phone' => '03-5512 3456', 'email' => 'akaun@contoh.my'];
        $barang = [['description' => 'Hiasan pelamin set A', 'qty' => 1, 'uom' => 'set', 'unit_price' => 3500, 'tax_rate' => 8], ['description' => 'Bunga segar meja tetamu', 'qty' => 20, 'uom' => 'pasu', 'unit_price' => 45.5, 'discount' => 10, 'tax_rate' => 8]];

        return match ($key) {
            'slip_gaji' => ['employee' => ['name' => 'Nurul Aisyah binti Ahmad', 'staff_id' => 'B002', 'ic_no' => '900101145678', 'position' => 'Eksekutif Jualan', 'department' => 'Jualan', 'epf_no' => '12345678', 'socso_no' => 'A1234567'],
                'period' => '2026-09', 'pay_date' => '2026-09-30', 'earnings' => [['label' => 'Gaji pokok', 'amount' => 2800], ['label' => 'Elaun tetap', 'amount' => 200], ['label' => 'Lebih masa (6 jam)', 'amount' => 121.15]],
                'deductions' => [['label' => 'KWSP pekerja', 'amount' => 330], ['label' => 'PERKESO pekerja', 'amount' => 14.75], ['label' => 'SIP pekerja', 'amount' => 5.9], ['label' => 'PCB', 'amount' => 0]],
                'employer' => [['label' => 'KWSP majikan', 'amount' => 390], ['label' => 'PERKESO majikan', 'amount' => 51.65], ['label' => 'SIP majikan', 'amount' => 5.9]],
                'bank' => ['name' => 'Maybank', 'account' => '512345678901'], 'payment_ref' => 'IBG-0930-002'],
            'laporan_payroll' => ['period' => '2026-09', 'rows' => [['staff_id' => 'B002', 'name' => 'Nurul Aisyah', 'gross' => 3121.15, 'epf_ee' => 330, 'socso_ee' => 14.75, 'eis_ee' => 5.9, 'pcb' => 0, 'other_ded' => 0, 'epf_er' => 390, 'socso_er' => 51.65, 'eis_er' => 5.9],
                ['staff_id' => 'B003', 'name' => 'Ali bin Abu', 'gross' => 6500, 'epf_ee' => 715, 'socso_ee' => 29.75, 'eis_ee' => 11.9, 'pcb' => 240.5, 'other_ded' => 50, 'epf_er' => 780, 'socso_er' => 104.15, 'eis_er' => 11.9]]],
            'surat_tawaran' => ['date' => '2026-10-01', 'recipient' => $penerima, 'signatory' => $ttd, 'cc' => ['Fail peribadi'],
                'offer' => ['position' => 'Eksekutif Jualan', 'department' => 'Jualan', 'start_date' => '2026-11-01', 'basic_salary' => 2800, 'allowances' => [['label' => 'Elaun perjalanan', 'amount' => 200]],
                    'probation_months' => 3, 'work_hours' => 'Isnin hingga Jumaat, 9.00 pagi hingga 6.00 petang', 'work_location' => 'Kajang, Selangor', 'annual_leave_days' => 12, 'sick_leave_days' => 14, 'notice_period' => '1 bulan', 'reply_by' => '2026-10-15']],
            'surat_pengesahan_jawatan' => ['date' => '2026-10-01', 'recipient' => $penerima, 'signatory' => $ttd, 'position' => 'Eksekutif Jualan', 'effective_date' => '2026-10-01', 'new_salary' => 3000],
            'surat_kenaikan_gaji' => ['date' => '2026-10-01', 'recipient' => $penerima, 'signatory' => $ttd, 'effective_date' => '2026-11-01', 'old_salary' => 2800, 'new_salary' => 3100],
            'surat_amaran' => ['date' => '2026-10-01', 'recipient' => $penerima, 'signatory' => $ttd, 'matter' => 'kehadiran lewat', 'incident_date' => '2026-09-20', 'details' => 'Hadir lewat lebih 30 minit sebanyak 5 kali dalam bulan September 2026.', 'expected' => 'hadir tepat pada waktu bekerja yang ditetapkan.', 'level' => 'pertama'],
            'surat_tunjuk_sebab' => ['date' => '2026-10-01', 'recipient' => $penerima, 'signatory' => $ttd, 'matter' => 'ketidakhadiran tanpa kebenaran', 'allegations' => ['Tidak hadir bertugas pada 22 dan 23 September 2026 tanpa kebenaran atau makluman.'], 'reply_days' => 7],
            'surat_kelulusan_cuti' => ['date' => '2026-10-01', 'recipient' => $penerima, 'signatory' => $ttd, 'leave_type' => 'Cuti Tahunan', 'from' => '2026-10-20', 'to' => '2026-10-21', 'days' => 2, 'decision' => 'lulus', 'balance' => 8],
            'surat_pengesahan_perkhidmatan' => ['date' => '2026-10-01', 'recipient' => $penerima, 'signatory' => $ttd, 'position' => 'Eksekutif Jualan', 'start_date' => '2024-03-01', 'salary' => 3000, 'purpose' => 'permohonan pinjaman perumahan'],
            'surat_terima_peletakan_jawatan' => ['date' => '2026-10-01', 'recipient' => $penerima, 'signatory' => $ttd, 'notice_date' => '2026-09-30', 'last_day' => '2026-10-31'],
            'sebut_harga', 'invois', 'nota_kredit' => ['date' => '2026-10-01', 'due_date' => '2026-10-31', 'reference' => 'PO-ABC-77', 'party' => $pihak, 'items' => $barang, 'tax_label' => 'Cukai Perkhidmatan 8%', 'payment_terms' => '30 hari dari tarikh invois', 'bank' => 'Maybank 5123 4567 8901 (AI Bijan Demo Sdn. Bhd.)'],
            'resit' => ['date' => '2026-10-01', 'reference' => 'INV-2026-0001', 'party' => $pihak, 'items' => [['description' => 'Bayaran bagi INV-2026-0001', 'qty' => 1, 'unit_price' => 4291.4]], 'paid_amount' => 4291.4, 'payment_method' => 'DuitNow'],
            'pesanan_belian' => ['date' => '2026-10-01', 'due_date' => '2026-10-10', 'party' => $pihak, 'items' => [['description' => 'Kotak pembungkus 10x10cm', 'qty' => 500, 'uom' => 'unit', 'unit_price' => 0.85], ['description' => 'Riben satin merah', 'qty' => 20, 'uom' => 'gulung', 'unit_price' => 6.5]], 'payment_terms' => 'COD'],
            'nota_penghantaran', 'nota_terima_barang' => ['date' => '2026-10-01', 'reference' => 'PO-2026-0001', 'party' => $pihak, 'items' => [['description' => 'Kotak pembungkus 10x10cm', 'qty' => 500, 'uom' => 'unit'], ['description' => 'Riben satin merah', 'qty' => 20, 'uom' => 'gulung']]],
            'perjanjian_kerahsiaan' => ['date' => '2026-10-01', 'party' => $pihak + ['signatory' => 'Siti binti Rahman', 'position' => 'Pengarah'], 'purpose' => 'penilaian kerjasama pembekalan hiasan majlis', 'term_years' => 2, 'mutual' => true, 'signatory' => $ttd],
        };
    }
}
