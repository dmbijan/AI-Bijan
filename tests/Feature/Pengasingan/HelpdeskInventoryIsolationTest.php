<?php

namespace Tests\Feature\Pengasingan;

use App\Models\Item;
use App\Models\Ticket;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Helpdesk + Inventory (model teras `app/Models`).
 *
 * Ditambah 27 Okt 2026 (P1-1d).
 *
 * ⚠️ Kedua-dua modul (dan `Manufacturing`) TIDAK mempunyai model sendiri —
 * hanya service. Modelnya tinggal di `app/Models` (`Ticket`, `Item`, `Bom`,
 * `WorkOrder`). Ujian ini mengesahkan lapisan data yang dikongsi itu, kerana
 * kebocoran di sini menjejaskan tiga modul serentak.
 *
 * • Ticket: e-mel dan telefon pemohon (data peribadi PDPA).
 * • Item: harga belian/jualan dan tahap stok.
 */
class HelpdeskInventoryIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Helpdesk';

    protected array $model = [
        Ticket::class,
        Item::class,
    ];

    protected ?string $laluanApi = '/api/v1/helpdesk/tickets';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === Item::class) {
            return [
                'sku' => 'RAHSIA-' . $n,
                'name' => 'Item Rahsia Tenant Satu ' . $n,
                'uom' => 'unit',
            ];
        }

        return [
            'number' => 'TKT-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
            'subject' => 'Tiket Rahsia Tenant Satu ' . $n,
            'description' => 'Butiran sulit tenant satu.',
            'requester_name' => 'Rahsia Tenant Satu ' . $n,
            'requester_email' => 'rahsia' . $n . '@tiket-tenant-satu.test',
            'channel' => 'web',
            'priority' => 'sederhana',
            'status' => 'baru',
        ];
    }
}
