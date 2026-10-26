<?php

namespace App\Documents;

/**
 * Struktur dokumen neutral format. Setiap templat membina model ini; setiap renderer (PDF/DOCX/XLSX/…) melukisnya.
 * Bahagian (blocks): ['type' => 'heading'|'paragraph'|'kv'|'table'|'totals'|'signatures'|'note', ...]
 */
class DocumentModel
{
    public array $blocks = [];

    public function __construct(
        public string $title,
        public string $number,
        public string $date,
        public array $issuer,          // name, reg_no, sst_no, address, phone, email, logo_path?
        public ?array $recipient = null, // label, name, lines[]
        public array $meta = [],         // [label => value]
        public string $footer = '',
        public bool $confidential = false,
    ) {}

    public function heading(string $text): static
    {
        $this->blocks[] = ['type' => 'heading', 'text' => $text];

        return $this;
    }

    public function paragraph(string $text): static
    {
        $this->blocks[] = ['type' => 'paragraph', 'text' => $text];

        return $this;
    }

    /** Senarai label → nilai (cth maklumat pekerja). */
    public function kv(array $rows, ?string $title = null, int $cols = 1): static
    {
        $this->blocks[] = ['type' => 'kv', 'title' => $title, 'rows' => $rows, 'cols' => $cols];

        return $this;
    }

    /** Jadual: columns = [['label' => .., 'align' => 'left|right', 'money' => bool]], rows = [[..]]. */
    public function table(array $columns, array $rows, ?string $title = null): static
    {
        $this->blocks[] = ['type' => 'table', 'title' => $title, 'columns' => $columns, 'rows' => $rows];

        return $this;
    }

    /** Jumlah: [[label, amount, bold?]]. */
    public function totals(array $rows): static
    {
        $this->blocks[] = ['type' => 'totals', 'rows' => $rows];

        return $this;
    }

    public function signatures(array $slots): static
    {
        $this->blocks[] = ['type' => 'signatures', 'slots' => $slots];

        return $this;
    }

    public function note(string $text): static
    {
        $this->blocks[] = ['type' => 'note', 'text' => $text];

        return $this;
    }
}
