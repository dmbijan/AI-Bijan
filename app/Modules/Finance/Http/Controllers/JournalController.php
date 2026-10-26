<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\Services\JournalService;
use App\Modules\Finance\Services\FinancialReportService;
use Illuminate\Http\Request;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    protected $journalService;

    /**
     * ID tenant semasa — MESTI dari konteks, BUKAN header klien.
     * ⚠️ 26 Okt 2026: kod lama guna `$request->header('X-Tenant-ID')` → IDOR.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * List journal entries with filters.
     * GET /api/finance/journal-entries
     */
    public function index(Request $request)
    {
        // ⚠️ 27 Okt 2026: DB::table()->with() tidak sah → guna Eloquent.
        $query = JournalEntry::query()
            ->where('tenant_id', $this->tenantId())
            ->with(['lines.account', 'approver'])
            ->orderBy('entry_date', 'desc');
        
        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('entry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('entry_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhere('reference_number', 'like', "%{$request->search}%")
                  ->orWhere('entry_number', 'like', "%{$request->search}%");
            });
        }
        
        return response()->json($query->paginate(20));
    }

    /**
     * Get single journal entry.
     * GET /api/finance/journal-entries/{id}
     */
    public function show($id, Request $request)
    {
        // ⚠️ 27 Okt 2026: DB::table()->with() tidak sah → guna Eloquent.
        $entry = JournalEntry::query()
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->with(['lines.account', 'approver'])
            ->first();
        
        if (! $entry) {
            return response()->json(['message' => 'Journal entry not found'], 404);
        }
        
        return response()->json($entry);
    }

    /**
     * Create a new draft journal entry.
     * POST /api/finance/journal-entries
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'narration_bm' => 'nullable|string|max:1000',
            'source_module' => 'nullable|string|max:100',
            'source_id' => 'nullable|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|integer',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ]);
        
        $validated['tenant_id'] = $this->tenantId();
        
        try {
            $entry = $this->journalService->createJournalEntry($validated);
            return response()->json($entry->load(['lines.account', 'approver']), 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Post (approve) a draft journal entry.
     * POST /api/finance/journal-entries/{id}/post
     */
    public function post($id, Request $request)
    {
        $entry = DB::table('journal_entries')
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->first();
        
        if (! $entry || $entry->status !== 'draft') {
            return response()->json(['message' => 'Invalid journal entry for posting'], 400);
        }
        
        try {
            $posted = $this->journalService->postJournalEntry(\App\Modules\Finance\Models\JournalEntry::find($id));
            return response()->json($posted->load(['lines.account', 'approver']));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel a journal entry.
     * POST /api/finance/journal-entries/{id}/cancel
     */
    public function cancel($id, Request $request)
    {
        $entry = \App\Modules\Finance\Models\JournalEntry::find($id);
        
        if (! $entry || $entry->tenant_id !== $this->tenantId()) {
            return response()->json(['message' => 'Journal entry not found'], 404);
        }
        
        try {
            $this->journalService->cancelJournalEntry($entry);
            return response()->json(['message' => 'Journal entry cancelled']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
