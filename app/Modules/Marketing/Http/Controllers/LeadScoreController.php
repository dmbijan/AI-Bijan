<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Services\LeadScoreService;
use App\Modules\Marketing\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeadScoreController extends Controller
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
    protected LeadScoreService $leadScoreService;

    public function __construct(LeadScoreService $leadScoreService)
    {
        $this->leadScoreService = $leadScoreService;
    }

    /**
     * List all lead scores.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $filters = $request->only(['grade', 'tier', 'hot_leads_only']);

        $scores = $this->leadScoreService->getLeadScores($tenantId, $filters);

        return response()->json([
            'success' => true,
            'data' => $scores,
        ]);
    }

    /**
     * Get a single lead score.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $score = $this->leadScoreService->getLeadScoreById($id, $tenantId);

        if (!$score) {
            return response()->json([
                'success' => false,
                'message' => 'Lead score not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $score,
        ]);
    }

    /**
     * Get lead score for a contact.
     */
    public function showForContact(Request $request, int $contactId): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $score = $this->leadScoreService->getLeadScoreForContact($contactId, $tenantId);

        if (!$score) {
            return response()->json([
                'success' => false,
                'message' => 'Lead score not calculated yet.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $score,
        ]);
    }

    /**
     * Calculate and update lead score for a contact.
     */
    public function calculate(Request $request, int $contactId): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $contact = Contact::where('id', $contactId)->where('tenant_id', $tenantId)->first();
        
        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found.',
            ], 404);
        }

        $score = $this->leadScoreService->recalculateContactScore($contact);

        return response()->json([
            'success' => true,
            'data' => $score,
            'message' => 'Lead score calculated successfully.',
        ]);
    }

    /**
     * Update lead score components manually.
     */
    public function update(Request $request, int $contactId): JsonResponse
    {
        $request->validate([
            'demographic' => 'nullable|integer|min:0|max:100',
            'firmographic' => 'nullable|integer|min:0|max:100',
            'engagement' => 'nullable|integer|min:0|max:100',
            'behavioral' => 'nullable|integer|min:0|max:100',
            'transactional' => 'nullable|integer|min:0|max:100',
        ]);

        $tenantId = app(TenantContext::class)->id();

        $score = $this->leadScoreService->upsertLeadScore(
            $contactId,
            $tenantId,
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => $score,
            'message' => 'Lead score updated.',
        ]);
    }

    /**
     * Get top leads.
     */
    public function topLeads(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $limit = $request->limit ?? 10;

        $leads = $this->leadScoreService->getTopLeads($tenantId, $limit);

        return response()->json([
            'success' => true,
            'data' => $leads,
        ]);
    }

    /**
     * Get leads by grade.
     */
    public function byGrade(Request $request): JsonResponse
    {
        $request->validate([
            'grade' => 'required|in:A,B,C,D,F',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $limit = $request->limit ?? 50;

        $leads = $this->leadScoreService->getLeadsByGrade($request->grade, $tenantId, $limit);

        return response()->json([
            'success' => true,
            'data' => $leads,
        ]);
    }

    /**
     * Get score distribution.
     */
    public function distribution(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $distribution = $this->leadScoreService->getScoreDistribution($tenantId);

        return response()->json([
            'success' => true,
            'data' => $distribution,
        ]);
    }

    /**
     * Get average scores by category.
     */
    public function averages(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $averages = $this->leadScoreService->getAverageScores($tenantId);

        return response()->json([
            'success' => true,
            'data' => $averages,
        ]);
    }

    /**
     * Bulk recalculate all scores.
     */
    public function bulkRecalculate(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $count = $this->leadScoreService->bulkRecalculateScores($tenantId);

        return response()->json([
            'success' => true,
            'data' => ['recalculated' => $count],
            'message' => "Recalculated {$count} lead scores.",
        ]);
    }

    /**
     * Get lead score statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->leadScoreService->getLeadScoreStats($tenantId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
