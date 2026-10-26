<?php

namespace App\Http\Controllers\Api\V1;

use App\Documents\DocumentService;
use App\Documents\Registry;
use App\Documents\Renderers\HtmlRenderer;
use App\Documents\Template;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\TenantUser;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    private function svc(): DocumentService
    {
        return app(DocumentService::class);
    }

    /** Boleh akses: dokumen sendiri (subjek), atau kebenaran ikut tahap sulit. */
    private function bolehAkses(Document $d): bool
    {
        if ($d->subject_user_id && $d->subject_user_id === auth()->id()) {
            return true;
        }

        return Registry::get($d->type)->sensitive() ? $this->ctx()->can('documents.sensitive') : $this->ctx()->can('documents.view');
    }

    private function bolehKeluar(Template $t): bool
    {
        return $this->ctx()->can('documents.issue') && (! $t->sensitive() || $this->ctx()->can('documents.sensitive'));
    }

    public function catalog()
    {
        return response()->json([
            'templates' => collect(Registry::all())->map(fn (Template $t) => ['key' => $t->key(), 'name' => $t->name('ms'), 'name_en' => $t->name('en'), 'category' => $t->category(),
                'version' => $t->version(), 'formats' => $t->formats(), 'sensitive' => $t->sensitive(), 'review' => $t->review(), 'can_issue' => $this->bolehKeluar($t)])->values(),
            'categories' => Template::KATEGORI,
            'themes' => collect(config('aibijan_dokumen.themes'))->map(fn ($v, $k) => ['key' => $k, 'name' => $v['name'], 'accent' => $v['accent']])->values(),
            'papers' => array_keys(config('aibijan_dokumen.paper')),
        ]);
    }

    public function index(Request $r)
    {
        $sulit = collect(Registry::all())->filter(fn ($t) => $t->sensitive())->keys()->all();
        $q = Document::query()->when($r->query('type'), fn ($q, $t) => $q->where('type', $t))->orderByDesc('id');
        if (! $this->ctx()->can('documents.sensitive')) {
            $q->where(fn ($w) => $w->whereNotIn('type', $sulit)->orWhere('subject_user_id', auth()->id()));
        }
        if (! $this->ctx()->can('documents.view') && ! $this->ctx()->can('documents.sensitive')) {
            $q->where('subject_user_id', auth()->id());
        }

        return response()->json($q->paginate(50, ['ulid', 'type', 'number', 'title', 'locale', 'theme', 'template_version', 'status', 'subject_user_id', 'created_at']));
    }

    public function store(Request $r)
    {
        $d = $r->validate(['type' => 'required|string', 'data' => 'required|array', 'locale' => 'nullable|in:ms,en', 'theme' => 'nullable|string|max:20', 'subject_user_id' => 'nullable|integer']);
        $t = Registry::get($d['type']);
        abort_unless($this->bolehKeluar($t), 403, 'Anda tiada kebenaran untuk mengeluarkan dokumen ini.');
        if (! empty($d['subject_user_id'])) {
            abort_unless(TenantUser::where('user_id', $d['subject_user_id'])->exists(), 422, 'Penerima bukan ahli syarikat ini.');
        }
        $doc = $this->svc()->issue($t->key(), $d['data'], $d['locale'] ?? 'ms', $d['theme'] ?? null, $d['subject_user_id'] ?? null);

        return response()->json(['ulid' => $doc->ulid, 'number' => $doc->number, 'formats' => $t->formats()], 201);
    }

    /** Pratonton HTML tanpa menyimpan (untuk borang). */
    public function preview(Request $r)
    {
        $d = $r->validate(['type' => 'required|string', 'data' => 'required|array', 'locale' => 'nullable|in:ms,en', 'theme' => 'nullable|string|max:20']);
        $t = Registry::get($d['type']);
        abort_unless($this->bolehKeluar($t), 403);
        $data = $this->svc()->validate($t, $d['data']);
        $m = $t->build($data, $this->svc()->issuer(), $t->prefix().'-PRATONTON', $d['locale'] ?? 'ms');
        $m->title .= ' (PRATONTON)';

        return response((new HtmlRenderer)->html($m, $this->svc()->theme($d['theme'] ?? null)), 200, ['Content-Type' => 'text/html; charset=UTF-8', 'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; img-src data:"]);
    }

    public function show(string $ulid)
    {
        $doc = Document::where('ulid', $ulid)->firstOrFail();
        abort_unless($this->bolehAkses($doc), 403, 'Dokumen sulit.');

        return response()->json($doc->only(['ulid', 'type', 'number', 'title', 'locale', 'theme', 'template_version', 'status', 'void_reason', 'sha256', 'created_at']) + ['formats' => Registry::get($doc->type)->formats()]);
    }

    public function download(Request $r, string $ulid, string $format)
    {
        $doc = Document::where('ulid', $ulid)->firstOrFail();
        abort_unless($this->bolehAkses($doc), 403, 'Dokumen sulit.');
        $paper = in_array($r->query('paper'), ['A4', 'Letter'], true) ? $r->query('paper') : 'A4';
        [$body, $mime, $nama] = $this->svc()->render($doc, strtolower($format), $paper);
        app(AuditLogger::class)->log('documents.downloaded', $doc, null, ['number' => $doc->number, 'format' => $format]);
        $inline = $format === 'html' || ($format === 'pdf' && $r->boolean('inline'));

        return response($body, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.$nama.'"',
            'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ] + ($format === 'html' ? ['Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; img-src data:"] : []));
    }

    public function void(Request $r, string $ulid)
    {
        $d = $r->validate(['reason' => 'required|string|min:5|max:255']);
        $doc = Document::where('ulid', $ulid)->firstOrFail();
        abort_unless($this->ctx()->can('documents.void') && (! Registry::get($doc->type)->sensitive() || $this->ctx()->can('documents.sensitive')), 403);
        abort_if($doc->status === 'dibatalkan', 409, 'Dokumen sudah dibatalkan.');
        $doc->update(['status' => 'dibatalkan', 'void_reason' => $d['reason']]);
        app(AuditLogger::class)->log('documents.voided', $doc, ['status' => 'dikeluarkan'], ['status' => 'dibatalkan', 'reason' => $d['reason']]);

        return response()->json(['ok' => true]);
    }
}
