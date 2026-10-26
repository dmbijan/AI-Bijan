<?php

namespace App\Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Analytics\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * List all reports
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'active']);
        
        return response()->json($this->reportService->listReports(
            app(TenantContext::class)->id(),
            $filters
        ));
    }

    /**
     * Generate report on demand
     */
    public function generate(Request $request, int $reportId)
    {
        $overrides = $request->only(['date_range', 'filters', 'metrics']);

        $result = $this->reportService->generateReport($reportId, $overrides);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Get report runs history
     */
    public function runs(Request $request, int $reportId)
    {
        $limit = $request->input('limit', 10);
        $runs = $this->reportService->getReportRuns($reportId, $limit);

        return response()->json($runs);
    }

    /**
     * Schedule report
     */
    public function schedule(Request $request, int $reportId)
    {
        $validated = $request->validate([
            'schedule' => 'required|array',
            'schedule.interval' => 'required|in:hourly,daily,weekly,monthly',
            'schedule.time' => 'required|string',
            'recipients' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $this->reportService->scheduleReport($reportId, $validated['schedule']);

        return response()->json(['message' => 'Report scheduled successfully']);
    }

    /**
     * Cancel scheduled report
     */
    public function cancelSchedule(Request $request, int $reportId)
    {
        $this->reportService->cancelScheduledReport($reportId);

        return response()->json(['message' => 'Report scheduling cancelled']);
    }

    /**
     * Download report file
     */
    public function download(Request $request, string $filename)
    {
        return $this->reportService->downloadReport($filename);
    }
}
