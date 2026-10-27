<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Cod\Models\CodOrder;
use App\Modules\SignDocs\Models\Document;
use App\Modules\SignDocs\Models\Template;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul SignDocs (dokumen tandatangan) + COD (bayar
 * semasa terima).
 *
 * Ditambah 27 Okt 2026 (P1-1d).
 *
 * • SignDocs: dokumen kontrak dan templat — kebocoran bermakna tenant lain
 *   boleh membaca atau memuat turun perjanjian sulit pelanggan.
 * • COD: nama, telefon dan alamat penghantaran penerima — data peribadi PDPA.
 */
class SignDocsCodIsolationTest extends UjianPengasingan
{
    protected string $modul = 'SignDocs';

    protected array $model = [
        Document::class,
        Template::class,
        CodOrder::class,
    ];

    protected ?string $laluanApi = '/api/v1/sign-docs/documents';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === Template::class) {
            return [
                'user_id' => $this->u->id,
                'name' => 'Templat Rahsia Tenant Satu ' . $n,
                'category' => 'other',
            ];
        }

        if ($this->rekodSemasa === CodOrder::class) {
            return [
                'cod_no' => 'COD-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
                'customer_name' => 'Rahsia Tenant Satu ' . $n,
                'customer_phone' => '6012345' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
                'delivery_address' => 'No 1, Jalan Rahsia',
                'order_amount' => 100,
                'total_amount' => 100,
                'amount_to_collect' => 100,
            ];
        }

        return [
            'user_id' => $this->u->id,
            'title' => 'Dokumen Rahsia Tenant Satu ' . $n,
            'file_path' => 'dokumen/rahsia-' . $n . '.pdf',
            'file_type' => 'pdf',
            'category' => 'other',
            'status' => 'draft',
        ];
    }
}
