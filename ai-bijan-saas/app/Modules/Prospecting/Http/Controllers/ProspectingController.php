<?php

namespace App\Modules\Prospecting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Prospecting\Models\Prospect;
use App\Modules\Prospecting\Models\ProspectAuditLog;
use App\Modules\Prospecting\Models\ProspectSource;
use App\Modules\Prospecting\Services\ConsentService;
use App\Modules\Prospecting\Services\CsvImporterService;
use App\Modules\Prospecting\Services\DedupeService;
use App\Modules\Prospecting\Services\ProspectScoringService;
use App\Modules\Prospecting\Services\PublicScraperService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProspectingController extends Controller
{
    public function __construct(
        private CsvImporterService $importer,
        private ConsentService $consent,
        private DedupeService $dedupe,
        private ProspectScoringService $scoring,
        private PublicScraperService $scraper
    ) {
    }

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Senarai prospect dengan penapis.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Prospect::query()->orderByDesc('score');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('consent_status')) {
            $query->where('consent_status', $request->string('consent_status')->toString());
        }

        if ($request->filled('source_id')) {
            $query->where('source_id', $request->integer('source_id'));
        }

        if ($request->filled('list_id')) {
            $query->where('list_id', $request->integer('list_id'));
        }

        if ($request->filled('min_score')) {
            $query->where('score', '>=', $request->integer('min_score'));
        }

        if ($request->filled('search')) {
            $search = '%'.trim($request->string('search')->toString()).'%';
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', $search)
                    ->orWhere('last_name', 'like', $search)
                    ->orWhere('company', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        $prospects = $query->paginate(min((int) $request->integer('per_page', 25), 100));

        return response()->json([
            'success' => true,
            'data' => $prospects->items(),
            'meta' => [
                'current_page' => $prospects->currentPage(),
                'last_page' => $prospects->lastPage(),
                'per_page' => $prospects->perPage(),
                'total' => $prospects->total(),
            ],
        ]);
    }

    /**
     * Butiran satu prospect.
     */
    public function show(int $id): JsonResponse
    {
        $prospect = Prospect::find($id);

        if (! $prospect) {
            return response()->json(['success' => false, 'message' => 'Prospect tidak dijumpai'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $prospect,
            'consent' => [
                'boleh_email' => $this->consent->bolehDihantar($prospect, 'email'),
                'boleh_sms' => $this->consent->bolehDihantar($prospect, 'sms'),
                'boleh_whatsapp' => $this->consent->bolehDihantar($prospect, 'whatsapp'),
            ],
        ]);
    }

    /**
     * Muat naik fail CSV dan import.
     */
    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'source_id' => ['nullable', 'integer', 'exists:prospect_sources,id'],
            'list_id' => ['nullable', 'integer', 'exists:prospect_lists,id'],
            'assume_consent' => ['nullable', 'boolean'],
        ]);

        $tenantId = $this->tenantId();

        $source = null;
        if ($request->filled('source_id')) {
            $source = ProspectSource::where('tenant_id', $tenantId)->find($request->integer('source_id'));
        }

        $path = $request->file('file')->getRealPath();

        $result = $this->importer->importFile(
            $path,
            $tenantId,
            $source,
            $request->integer('list_id') ?: null,
            (bool) $request->boolean('assume_consent')
        );

        if (! empty($result['errors']) && $result['imported'] === 0 && $result['skipped'] === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Import gagal',
                'errors' => $result['errors'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => sprintf(
                '%d diimport, %d dupe, %d dilangkau',
                $result['imported'],
                $result['duplicates'],
                $result['skipped']
            ),
            'data' => $result,
        ]);
    }

    /**
     * Daftar sumber prospek baru.
     */
    public function storeSource(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:csv,api,scrape,manual'],
            'connector_slug' => ['nullable', 'string', 'max:80'],
            'config' => ['nullable', 'array'],
        ]);

        $source = ProspectSource::create([
            'tenant_id' => $this->tenantId(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'connector_slug' => $validated['connector_slug'] ?? null,
            'config' => $validated['config'] ?? null,
            'status' => 'active',
        ]);

        return response()->json(['success' => true, 'data' => $source], 201);
    }

    /**
     * Senarai sumber.
     */
    public function sources(): JsonResponse
    {
        $sources = ProspectSource::where('tenant_id', $this->tenantId())
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $sources]);
    }

    /**
     * Rekod bukti consent.
     */
    public function recordConsent(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'source' => ['required', 'string', 'max:150'],
            'evidence' => ['required', 'string', 'max:500'],
            'valid_days' => ['nullable', 'integer', 'min:1', 'max:1095'],
        ]);

        $prospect = Prospect::find($id);

        if (! $prospect) {
            return response()->json(['success' => false, 'message' => 'Prospect tidak dijumpai'], 404);
        }

        $this->consent->rekodConsent(
            $prospect,
            $validated['source'],
            $validated['evidence'],
            $validated['valid_days'] ?? null
        );

        $this->audit($prospect, 'consent_recorded', [
            'source' => $validated['source'],
        ]);

        return response()->json(['success' => true, 'data' => $prospect->fresh()]);
    }

    /**
     * Tarik balik consent.
     */
    public function revokeConsent(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

        $prospect = Prospect::find($id);

        if (! $prospect) {
            return response()->json(['success' => false, 'message' => 'Prospect tidak dijumpai'], 404);
        }

        $this->consent->tarikConsent($prospect, $validated['reason'] ?? 'withdrawn');

        $this->audit($prospect, 'consent_revoked', [
            'reason' => $validated['reason'] ?? 'withdrawn',
        ]);

        return response()->json(['success' => true, 'data' => $prospect->fresh()]);
    }

    /**
     * Skor semula prospect.
     */
    public function rescore(int $id): JsonResponse
    {
        $prospect = Prospect::find($id);

        if (! $prospect) {
            return response()->json(['success' => false, 'message' => 'Prospect tidak dijumpai'], 404);
        }

        $this->scoring->skor($prospect);

        return response()->json([
            'success' => true,
            'data' => $prospect->fresh(),
            'label' => $this->scoring->label((int) $prospect->score),
        ]);
    }

    /**
     * Audit report consent — untuk pematuhan PDPA.
     */
    public function consentReport(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->consent->laporanConsent($this->tenantId()),
        ]);
    }

    /**
     * Semak URL sebelum scraping (dry-run) — berguna untuk audit.
     */
    public function checkUrl(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url'],
        ]);

        $url = $validated['url'];
        $allowed = $this->scraper->dibenarkanUrl($url);
        $robots = null;

        if ($allowed) {
            $robots = $this->scraper->robots($url);
            $path = parse_url($url, PHP_URL_PATH) ?: '/';
            $robotsAllowed = $robots->dibenarkan($path);
        } else {
            $robotsAllowed = false;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'url' => $url,
                'url_pattern_allowed' => $allowed,
                'robots_allowed' => $robotsAllowed,
                'permitted' => $allowed && $robotsAllowed,
            ],
        ]);
    }

    /**
     * Senarai jejak audit prospect.
     */
    public function auditLog(int $id): JsonResponse
    {
        $logs = ProspectAuditLog::where('tenant_id', $this->tenantId())
            ->where('prospect_id', $id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return response()->json(['success' => true, 'data' => $logs]);
    }

    /**
     * Catat jejak audit.
     */
    private function audit(Prospect $prospect, string $action, array $payload = []): void
    {
        try {
            ProspectAuditLog::create([
                'tenant_id' => $prospect->tenant_id,
                'prospect_id' => $prospect->id,
                'actor_type' => 'human',
                'actor_id' => auth()->id(),
                'action' => $action,
                'payload' => $payload,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 250),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Prospect audit log gagal', ['error' => $e->getMessage()]);
        }
    }
}
