<?php

namespace App\Documents;

class Registry
{
    public const TEMPLATES = [
        Templates\SlipGaji::class, Templates\LaporanPayroll::class, Templates\SuratTawaran::class, Templates\SuratPengesahanJawatan::class,
        Templates\SuratKenaikanGaji::class, Templates\SuratAmaran::class, Templates\SuratTunjukSebab::class, Templates\SuratKelulusanCuti::class,
        Templates\SuratPengesahanPerkhidmatan::class, Templates\SuratTerimaPeletakanJawatan::class,
        Templates\SebutHarga::class, Templates\Invois::class, Templates\Resit::class, Templates\NotaKredit::class,
        Templates\PesananBelian::class, Templates\NotaTerimaBarang::class, Templates\NotaPenghantaran::class,
        Templates\PerjanjianKerahsiaan::class,
    ];

    /** @return array<string, Template> */
    public static function all(): array
    {
        static $semua = null;
        if ($semua === null) {
            $semua = [];
            foreach (self::TEMPLATES as $c) {
                $t = new $c;
                $semua[$t->key()] = $t;
            }
        }

        return $semua;
    }

    public static function get(string $key): Template
    {
        return self::all()[$key] ?? abort(404, 'Jenis dokumen tidak wujud.');
    }
}
