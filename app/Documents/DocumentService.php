<?php

namespace App\Documents;

use App\Documents\Renderers\HtmlRenderer;
use App\Documents\Renderers\SheetRenderer;
use App\Documents\Renderers\WordRenderer;
use App\Models\Document;
use App\Models\Setting;
use App\Models\StoredFile;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentService
{
    public const MIME = [
        'pdf' => 'application/pdf', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'odt' => 'application/vnd.oasis.opendocument.text',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'ods' => 'application/vnd.oasis.opendocument.spreadsheet', 'csv' => 'text/csv; charset=UTF-8',
        'html' => 'text/html; charset=UTF-8', 'json' => 'application/json',
    ];

    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    /** Maklumat pengeluar dokumen dari tetapan syarikat (company.*). */
    public function issuer(): array
    {
        $s = Setting::whereIn('key', ['company.legal_name', 'company.reg_no', 'company.sst_no', 'company.tin', 'company.address', 'company.phone', 'company.email', 'company.logo', 'documents.theme'])->pluck('value', 'key');
        $i = ['name' => $s['company.legal_name'] ?? $this->ctx()->tenant()->name, 'reg_no' => $s['company.reg_no'] ?? null, 'sst_no' => $s['company.sst_no'] ?? null,
            'address' => $s['company.address'] ?? null, 'phone' => $s['company.phone'] ?? null, 'email' => $s['company.email'] ?? null];
        if (! empty($s['company.logo']) && ($f = StoredFile::where('ulid', $s['company.logo'])->first()) && str_starts_with($f->mime, 'image/') && Storage::disk($f->disk)->exists($f->path)) {
            $i['logo_path'] = Storage::disk($f->disk)->path($f->path);
            $i['logo_data'] = 'data:'.$f->mime.';base64,'.base64_encode(Storage::disk($f->disk)->get($f->path));
        }

        return $i;
    }

    public function theme(?string $key): array
    {
        $tema = config('aibijan_dokumen.themes');
        $key ??= Setting::where('key', 'documents.theme')->value('value') ?? config('aibijan_dokumen.default_theme');

        return ($tema[$key] ?? $tema[config('aibijan_dokumen.default_theme')]) + ['key' => isset($tema[$key]) ? $key : config('aibijan_dokumen.default_theme')];
    }

    public function validate(Template $t, array $data): array
    {
        return Validator::make($data, $t->rules())->validate();
    }

    /** Keluarkan dokumen: validasi → nombor siri (kunci baris) → simpan salinan data + cap SHA-256 → audit. */
    public function issue(string $type, array $data, string $locale = 'ms', ?string $theme = null, ?int $subjectUserId = null): Document
    {
        $t = Registry::get($type);
        $data = $this->validate($t, $data);
        $tema = $this->theme($theme);

        return DB::transaction(function () use ($t, $data, $locale, $tema, $subjectUserId) {
            $tahun = (int) date('Y');
            $tid = $this->ctx()->id();
            DB::table('document_sequences')->insertOrIgnore(['tenant_id' => $tid, 'type' => $t->key(), 'year' => $tahun, 'last' => 0]);
            $seq = DB::table('document_sequences')->where(['tenant_id' => $tid, 'type' => $t->key(), 'year' => $tahun])->lockForUpdate()->first();
            $n = $seq->last + 1;
            DB::table('document_sequences')->where('id', $seq->id)->update(['last' => $n]);
            $number = sprintf('%s-%d-%04d', $t->prefix(), $tahun, $n);
            $doc = Document::create(['type' => $t->key(), 'number' => $number, 'title' => $t->name($locale), 'locale' => $locale, 'theme' => $tema['key'],
                'template_version' => $t->key().'@'.$t->version(), 'data' => ['input' => $data, 'issuer' => $this->issuer()], 'sha256' => hash('sha256', json_encode($data).'|'.$number),
                'subject_user_id' => $subjectUserId, 'issued_by' => auth()->id(), 'status' => 'dikeluarkan']);
            app(AuditLogger::class)->log('documents.issued', $doc, null, ['type' => $t->key(), 'number' => $number, 'template' => $doc->template_version]);

            return $doc;
        });
    }

    public function model(Document $doc): DocumentModel
    {
        $t = Registry::get($doc->type);
        $issuer = $doc->data['issuer'];
        // Logo semasa (tidak disimpan dalam salinan data supaya rekod kecil).
        $semasa = $this->issuer();
        foreach (['logo_path', 'logo_data'] as $k) {
            if (isset($semasa[$k])) {
                $issuer[$k] = $semasa[$k];
            }
        }
        unset($issuer['logo_data_cache']);
        $m = $t->build($doc->data['input'], $issuer, $doc->number, $doc->locale);
        if ($doc->status === 'dibatalkan') {
            $m->title .= ' — '.($doc->locale === 'en' ? 'VOID' : 'DIBATALKAN');
        }

        return $m;
    }

    /** Medan peribadi disamarkan dalam semua eksport (No. KP, no. akaun bank). */
    public const MEDAN_SAMAR = ['ic_no', 'account', 'passport_no'];

    public static function samarkan(array $d): array
    {
        foreach ($d as $k => $v) {
            $d[$k] = is_array($v) ? self::samarkan($v) : (in_array($k, self::MEDAN_SAMAR, true) && is_string($v) ? Format::mask($v) : $v);
        }

        return $d;
    }

    /** Hasilkan kandungan dalam format diminta. Format mesti dibenarkan untuk jenis dokumen. */
    public function render(Document $doc, string $format, string $paper = 'A4'): array
    {
        $t = Registry::get($doc->type);
        abort_unless(in_array($format, $t->formats(), true), 422, 'Format '.$format.' tidak tersedia untuk '.$t->name().'.');
        $m = $this->model($doc);
        $tema = $this->theme($doc->theme);
        $body = match ($format) {
            'pdf' => (new HtmlRenderer)->pdf($m, $tema, $paper),
            'html' => (new HtmlRenderer)->html($m, $tema, $paper),
            'docx', 'odt' => (new WordRenderer)->render($m, $tema, $format, $paper),
            'xlsx', 'ods', 'csv' => (new SheetRenderer)->render($m, $tema, $format),
            'json' => json_encode(['number' => $doc->number, 'type' => $doc->type, 'template' => $doc->template_version, 'status' => $doc->status, 'issued_at' => $doc->created_at, 'sha256' => $doc->sha256, 'data' => self::samarkan($doc->data['input'])], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        };

        return [$body, self::MIME[$format], $t->filename($doc->data['input'], $doc->number).($doc->status === 'dibatalkan' ? '_DIBATALKAN' : '').'.'.$format];
    }
}
