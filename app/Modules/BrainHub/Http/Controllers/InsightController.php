<?php

namespace App\Modules\BrainHub\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BrainHub\Services\InsightService;
use Illuminate\Http\Request;

class InsightController extends Controller
{
    protected $insightService;

    public function __construct(InsightService $insightService)
    {
        $this->insightService = $insightService;
    }

    /**
     * List insights
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'type', 'severity', 'unread', 'undismissed']);
        
        return response()->json($this->insightService->getInsights(
            app(TenantContext::class)->id(),
            $request->user()->id,
            $filters
        ));
    }

    /**
     * Show single insight
     */
    public function show(Request $request, int $insightId)
    {
        $insight = \App\Modules\BrainHub\Models\Insight::where('tenant_id', app(TenantContext::class)->id())
            ->find($insightId);

        if (!$insight) {
            return response()->json(['message' => 'Insight not found'], 404);
        }

        return response()->json($insight);
    }

    /**
     * Mark as read
     */
    public function markAsRead(Request $request, int $insightId)
    {
        $this->insightService->markAsRead(app(TenantContext::class)->id(), $insightId);
        return response()->json(['message' => 'Insight marked as read']);
    }

    /**
     * Dismiss insight
     */
    public function dismiss(Request $request, int $insightId)
    {
        $this->insightService->dismissInsight(
            app(TenantContext::class)->id(),
            $insightId,
            $request->user()->id
        );
        return response()->json(['message' => 'Insight dismissed']);
    }

    /**
     * Record action taken
     */
    public function recordAction(Request $request, int $insightId)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->insightService->recordAction(
            app(TenantContext::class)->id(),
            $insightId,
            $validated['notes'] ?? ''
        );

        return response()->json(['message' => 'Action recorded']);
    }

    /**
     * Generate insights manually
     */
    public function generate(Request $request)
    {
        $result = $this->insightService->bulkGenerate(app(TenantContext::class)->id());

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }

    /**
     * Get insight statistics
     */
    public function getStatistics(Request $request)
    {
        return response()->json($this->insightService->getStatistics(app(TenantContext::class)->id()));
    }
}
