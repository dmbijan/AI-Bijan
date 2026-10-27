<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Lead;
use App\Modules\CRM\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Display a listing of leads.
     */
    public function index(Request $request)
    {
        $query = Lead::with(['assignee', 'opportunity'])
            ->where('tenant_id', app(TenantContext::class)->id())
            ->latest();
        
        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        
        // Filter by priority
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }
        
        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        
        // Search by name
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('company', 'like', '%' . $request->search . '%')
                  ->orWhere('lead_number', 'like', '%' . $request->search . '%');
            });
        }
        
        $leads = $query->paginate(15);
        
        return response()->json($leads);
    }

    /**
     * Store a newly created lead.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:100',
            'requirements' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => 'nullable|exists:users,id',
        ]);
        
        $lead = $this->leadService->createLead(array_merge($validated, [
            'tenant_id' => app(TenantContext::class)->id(),
        ]));
        
        return response()->json($lead, 201);
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead)
    {
        return response()->json($lead->load(['assignee', 'opportunity']));
    }

    /**
     * Update the specified lead.
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'source' => 'nullable|string|max:100',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:100',
            'requirements' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => 'nullable|exists:users,id',
            'status' => ['nullable', Rule::in(['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])],
            'loss_reason' => 'nullable|string',
        ]);
        
        // Handle status update
        if (isset($validated['status'])) {
            $lead = $this->leadService->updateLead($lead, $validated['status'], [
                'loss_reason' => $validated['loss_reason'] ?? null,
            ]);
        } else {
            $lead->update(array_filter($validated));
        }
        
        return response()->json($lead->load(['assignee', 'opportunity']));
    }

    /**
     * Convert lead to opportunity.
     */
    public function convert(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'probability' => 'nullable|numeric|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'owner_id' => 'nullable|exists:users,id',
        ]);
        
        $opportunity = $this->leadService->convertToOpportunity($lead, $validated);
        
        return response()->json($opportunity->load(['customer']), 201);
    }

    /**
     * Get lead statistics.
     */
    public function statistics()
    {
        $stats = $this->leadService->getLeadStatistics(app(TenantContext::class)->id());
        
        return response()->json($stats);
    }

    public function convertToOpportunity(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'convertToOpportunity']);
    }

    public function assign(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'assign']);
    }
}
