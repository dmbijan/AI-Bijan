<?php

namespace App\Modules\BrainHub\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BrainHub\Services\RecommendationService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * List recommendations
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'priority', 'pending']);
        
        return response()->json($this->recommendationService->getRecommendations(
            app(TenantContext::class)->id(),
            $request->user()->id,
            $filters
        ));
    }

    /**
     * Show single recommendation
     */
    public function show(Request $request, int $recId)
    {
        $rec = \App\Modules\BrainHub\Models\Recommendation::where('tenant_id', app(TenantContext::class)->id())
            ->find($recId);

        if (!$rec) {
            return response()->json(['message' => 'Recommendation not found'], 404);
        }

        return response()->json($rec);
    }

    /**
     * Accept recommendation
     */
    public function accept(Request $request, int $recId)
    {
        $success = $this->recommendationService->acceptRecommendation(
            app(TenantContext::class)->id(),
            $recId,
            $request->user()->id
        );

        if (!$success) {
            return response()->json(['message' => 'Recommendation not found or already accepted'], 404);
        }

        return response()->json(['message' => 'Recommendation accepted']);
    }

    /**
     * Complete recommendation
     */
    public function complete(Request $request, int $recId)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $success = $this->recommendationService->completeRecommendation(
            app(TenantContext::class)->id(),
            $recId,
            $validated['notes'] ?? ''
        );

        if (!$success) {
            return response()->json(['message' => 'Recommendation not found'], 404);
        }

        return response()->json(['message' => 'Recommendation completed']);
    }

    /**
     * Provide feedback on recommendation
     */
    public function provideFeedback(Request $request, int $recId)
    {
        $validated = $request->validate([
            'score' => 'required|integer|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);

        $success = $this->recommendationService->provideFeedback(
            app(TenantContext::class)->id(),
            $recId,
            $validated['score'],
            $validated['notes'] ?? ''
        );

        if (!$success) {
            return response()->json(['message' => 'Recommendation not found'], 404);
        }

        return response()->json(['message' => 'Feedback recorded']);
    }

    /**
     * Get recommendation statistics
     */
    public function getStatistics(Request $request)
    {
        return response()->json($this->recommendationService->getStatistics(app(TenantContext::class)->id()));
    }

    /**
     * Bulk generate recommendations
     */
    public function bulkGenerate(Request $request)
    {
        $result = $this->recommendationService->bulkGenerate(app(TenantContext::class)->id());

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
}
