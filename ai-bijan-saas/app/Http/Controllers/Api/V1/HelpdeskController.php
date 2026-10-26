<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\SlaPolicy;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Modules\Helpdesk\HelpdeskService;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HelpdeskController extends Controller
{
    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    /** Ejen biasa nampak tiket ditugaskan kepadanya + belum ditugaskan; pengurus nampak semua. */
    private function skop($q)
    {
        return $this->ctx()->can('helpdesk.manage') ? $q : $q->where(fn ($w) => $w->where('assignee_id', auth()->id())->orWhereNull('assignee_id'));
    }

    private function tiket(int $id): Ticket
    {
        return $this->skop(Ticket::query())->findOrFail($id);
    }

    public function index(Request $r)
    {
        return response()->json($this->skop(Ticket::query())->when($r->query('status'), fn ($q, $s) => $q->where('status', $s))->when($r->query('breached'), fn ($q) => $q->where(fn ($w) => $w->where('response_breached', true)->orWhere('resolution_breached', true)))
            ->orderByRaw("FIELD(priority,'kritikal','tinggi','sederhana','rendah')")->orderBy('resolution_due')->paginate(50));
    }

    public function store(Request $r, HelpdeskService $svc)
    {
        $d = $r->validate(['subject' => 'required|string|max:200', 'description' => 'required|string|max:10000', 'requester_name' => 'required|string|max:120', 'requester_email' => 'nullable|email|max:150',
            'requester_phone' => 'nullable|string|max:30', 'channel' => 'nullable|in:web,emel,telefon,whatsapp,kaunter,dalaman', 'priority' => 'nullable|in:rendah,sederhana,tinggi,kritikal',
            'category_id' => ['nullable', 'integer', Rule::exists('ticket_categories', 'id')->where('tenant_id', $this->ctx()->id())]]);
        [$t, $token] = $svc->create($d);

        return response()->json(['ticket' => $t, 'portal_token' => $token], 201);
    }

    public function show(int $id)
    {
        $t = $this->tiket($id);

        return response()->json(['ticket' => $t, 'messages' => $t->messages()->orderBy('id')->get()]);
    }

    public function reply(Request $r, int $id, HelpdeskService $svc)
    {
        $d = $r->validate(['body' => 'required|string|max:10000', 'internal' => 'boolean']);

        return response()->json($svc->reply($this->tiket($id), $d['body'], auth()->id(), $d['internal'] ?? false), 201);
    }

    public function update(Request $r, int $id, HelpdeskService $svc)
    {
        $t = $this->tiket($id);
        $d = $r->validate(['status' => 'nullable|in:baru,dibuka,menunggu_pelanggan,selesai,ditutup', 'priority' => 'nullable|in:rendah,sederhana,tinggi,kritikal', 'assignee_id' => 'nullable|integer']);
        if (array_key_exists('assignee_id', $d)) {
            abort_unless($this->ctx()->can('helpdesk.manage'), 403, 'Hanya pengurus boleh menugaskan semula.');
            abort_if($d['assignee_id'] && ! TenantUser::where('user_id', $d['assignee_id'])->exists(), 422);
            $t->update(['assignee_id' => $d['assignee_id']]);
        }
        if (! empty($d['priority'])) {
            $svc->setPriority($t, $d['priority']);
        }
        if (! empty($d['status'])) {
            $svc->setStatus($t, $d['status']);
        }

        return response()->json($t->fresh());
    }

    public function slaPolicies()
    {
        return response()->json(collect(HelpdeskService::SLA_LALAI)->map(fn ($v, $p) => ['priority' => $p, 'first_response_minutes' => $v[0], 'resolution_minutes' => $v[1]])
            ->merge(SlaPolicy::all()->keyBy('priority'))->values());
    }

    public function saveSla(Request $r)
    {
        $d = $r->validate(['policies' => 'required|array', 'policies.*.priority' => 'required|in:rendah,sederhana,tinggi,kritikal', 'policies.*.first_response_minutes' => 'required|integer|min:1', 'policies.*.resolution_minutes' => 'required|integer|min:1']);
        foreach ($d['policies'] as $p) {
            abort_if($p['resolution_minutes'] < $p['first_response_minutes'], 422, 'Masa penyelesaian mesti ≥ masa respons.');
            SlaPolicy::updateOrCreate(['priority' => $p['priority']], $p);
        }
        app(AuditLogger::class)->log('helpdesk.sla_updated', null, null, $d);

        return response()->json(['ok' => true]);
    }

    public function categories()
    {
        return response()->json(TicketCategory::orderBy('name')->get());
    }

    public function saveCategory(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:80', 'default_assignee_id' => 'nullable|integer']);

        return response()->json(TicketCategory::create($d), 201);
    }

    public function articles(Request $r)
    {
        return response()->json(KbArticle::when($r->query('q'), fn ($q, $s) => $q->where('title', 'like', "%$s%"))->orderByDesc('id')->get());
    }

    public function saveArticle(Request $r, ?int $id = null)
    {
        $d = $r->validate(['title' => 'required|string|max:200', 'body' => 'required|string|max:50000', 'category_id' => 'nullable|integer', 'published' => 'boolean']);
        $a = $id ? KbArticle::findOrFail($id) : new KbArticle(['slug' => Str::limit(Str::slug($d['title']), 100, '').'-'.Str::lower(Str::random(4))]);
        $a->fill($d)->save();
        app(AuditLogger::class)->log('helpdesk.article_saved', $a, null, ['title' => $a->title, 'published' => $a->published]);

        return response()->json($a, $id ? 200 : 201);
    }

    // ---------------- Portal pelanggan (awam, token peribadi; tiada log masuk)
    private function tenantAwam(string $slug): Tenant
    {
        $t = Tenant::where('slug', $slug)->first();
        abort_unless($t && $t->isUsable(), 404);
        $this->ctx()->set($t);

        return $t;
    }

    public function publicCreate(Request $r, string $slug, HelpdeskService $svc)
    {
        $this->tenantAwam($slug);
        $d = $r->validate(['subject' => 'required|string|max:200', 'description' => 'required|string|max:5000', 'requester_name' => 'required|string|max:120',
            'requester_email' => 'required_without:requester_phone|nullable|email|max:150', 'requester_phone' => 'nullable|string|max:30', 'consent' => 'accepted']);
        [$t, $token] = $svc->create(collect($d)->except('consent')->all() + ['channel' => 'web', 'priority' => 'sederhana']);

        return response()->json(['number' => $t->number, 'token' => $token, 'note' => 'Simpan pautan/token ini untuk semak status tiket.'], 201);
    }

    private function tiketAwam(string $slug, string $token): Ticket
    {
        $this->tenantAwam($slug);

        return Ticket::where('access_token_hash', hash('sha256', $token))->firstOrFail();
    }

    public function publicShow(string $slug, string $token)
    {
        $t = $this->tiketAwam($slug, $token);

        return response()->json(['number' => $t->number, 'subject' => $t->subject, 'status' => $t->status, 'created_at' => $t->created_at,
            'messages' => $t->messages()->where('internal', false)->orderBy('id')->get(['body', 'user_id', 'created_at'])->map(fn ($m) => ['from' => $m->user_id ? 'staf' : 'anda', 'body' => $m->body, 'at' => $m->created_at])]);
    }

    public function publicReply(Request $r, string $slug, string $token, HelpdeskService $svc)
    {
        $d = $r->validate(['body' => 'required|string|max:5000']);
        $svc->reply($this->tiketAwam($slug, $token), $d['body'], null);

        return response()->json(['ok' => true], 201);
    }

    public function publicArticles(string $slug)
    {
        $this->tenantAwam($slug);

        return response()->json(KbArticle::where('published', true)->orderBy('title')->get(['title', 'slug', 'body', 'updated_at']));
    }
}
