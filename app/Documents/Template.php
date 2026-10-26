<?php

namespace App\Documents;

/** Asas templat dokumen. Setiap templat berversi; versi direkod pada dokumen yang dikeluarkan. */
abstract class Template
{
    public const KATEGORI = ['hr' => 'HR & Pekerjaan', 'kewangan' => 'Kewangan & Jualan', 'operasi' => 'Operasi', 'korporat' => 'Undang-undang / Korporat'];

    abstract public function key(): string;

    abstract public function version(): int;

    abstract public function category(): string;

    abstract public function name(string $locale = 'ms'): string;

    /** Awalan nombor siri (cth "SG" → SG-2026-0001). */
    abstract public function prefix(): string;

    /** Peraturan validasi Laravel untuk data. */
    abstract public function rules(): array;

    abstract public function build(array $data, array $issuer, string $number, string $locale): DocumentModel;

    /** Data peribadi/gaji: hanya peranan berizin atau pemilik dokumen boleh muat turun. */
    public function sensitive(): bool
    {
        return false;
    }

    /** Format yang sesuai untuk jenis dokumen ini (bukan semua format sesuai untuk semua dokumen). */
    public function formats(): array
    {
        return ['pdf', 'docx', 'odt', 'html', 'json'];
    }

    /** Nama fail: elak data peribadi penuh (tiada No. KP). */
    public function filename(array $data, string $number): string
    {
        return str_replace(' ', '-', ucwords(str_replace('_', ' ', $this->key()))).'_'.$number;
    }

    /** Status semakan profesional (perlu disemak peguam/akauntan sebelum guna rasmi). */
    public function review(): array
    {
        return ['status' => 'perlu_semakan', 'note' => 'Templat produk; perlu disemak oleh profesional yang layak sebelum digunakan secara rasmi.', 'references' => []];
    }

    protected function t(string $locale, string $ms, string $en): string
    {
        return $locale === 'en' ? $en : $ms;
    }

    protected function footer(string $number, string $locale): string
    {
        return $this->t($locale, 'Dokumen dijana oleh komputer', 'Computer-generated document').' · '.$number.' · '.$this->key().' v'.$this->version().' · AI Bijan';
    }
}
