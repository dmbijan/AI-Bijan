<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\CRM\Services\OpportunityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OpportunityController extends Controller
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
    protected $opportunityService;

    public function __construct(OpportunityService $opportunityService)
    {
        $this->opportunityService = $opportunityService;
    }

    /**
     * Display a listing of opportunities.
     */
    public function index(Request $request)
    {
        $query = Opportunity::with(['customer', 'owner'])
            ->where('tenant_id', app(TenantContext::class)->id());
        
        // Filter by stage
        if ($request->filled('stage')) {
            $query->byStage($request->stage);
        }
        
        // Filter by owner
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }
        
        // Search by title
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('opportunity_number', 'like', '%' . $request->search . '%');
            });
        }
        
        $opportunities = $query->paginate(15);
        
        return response()->json($opportunities);
    }

    /**
     * Store a newly created opportunity.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'probability' => 'nullable|numeric|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'forecast_category' => ['nullable', Rule::in(['best_case', 'commit', 'oodled', 'pipeline'])],
            'competitors' => 'nullable|string|max:255',
            'win_strategy' => 'nullable|string',
            'next_action' => 'nullable|string',
            'next_action_date' => 'nullable|date',
            'owner_id' => 'nullable|exists:users,id',
        ]);
        
        $opportunity = $this->opportunityService->createOpportunity(array_merge($validated, [
            'tenant_id' => app(TenantContext::class)->id(),
        ]));
        
        return response()->json($opportunity->load(['customer', 'owner']), 201);
    }

    /**
     * Display the specified opportunity.
     */
    public function show(Opportunity $opportunity)
    {
        return response()->json($opportunity->load(['customer', 'owner', 'quotations']));
    }

    /**
     * Update the specified opportunity.
     */
    public function update(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'probability' => 'nullable|numeric|min:0|max:100',
            'stage' => ['nullable', Rule::in(['prospecting', 'qualification', 'needs_analysis', 'proposal', 'negotiation', 'closed_won', 'closed_lost'])],
            'expected_close_date' => 'nullable|date',
            'forecast_category' => ['nullable', Rule::in(['best_case', 'commit', 'oodled', 'pipeline'])],
            'competitors' => 'nullable|string|max:255',
            'win_strategy' => 'nullable|string',
            'loss_reason' => 'nullable|string',
            'next_action' => 'nullable|string',
            'next_action_date' => 'nullable|date',
            'owner_id' => 'nullable|exists:users,id',
        ]);
        
        if (isset($validated['stage'])) {
            $opportunity = $this->opportunityService->updateStage($opportunity, $validated['stage'], [
                'loss_reason' => $validated['loss_reason'] ?? null,
                'next_action' => $validated['next_action'] ?? null,
                'next_action_date' => $validated['next_action_date'] ?? null,
            ]);
        } else {
            $opportunity = $this->opportunityService->updateDetails($opportunity, $validated);
        }
        
        return response()->json($opportunity->load(['customer', 'owner']));
    }

    /**
     * Assign opportunity to salesperson.
     */
    public function assign(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'owner_id' => 'required|exists:users,id',
        ]);
        
        $opportunity = $this->opportunityService->assignOpportunity($opportunity, $validated['owner_id']);
        
        return response()->json($opportunity->load(['customer', 'owner']));
    }

    /**
     * Get pipeline summary.
     */
    public function getPipelineSummary()
    {
        $summary = $this->opportunityService->getPipelineSummary(app(TenantContext::class)->id());
        
        return response()->json($summary);
    }

    /**
     * Get upcoming follow-ups.
     */
    public function getFollowUps(Request $request)
    {
        $days = $request->input('days', 7);
        
        $followUps = $this->opportunityService->getUpcomingFollowUps(app(TenantContext::class)->id(), $days);
        
        return response()->json($followUps);
    }
}
