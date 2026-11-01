<?php

namespace App\Modules\AIAgents\Services;

use Illuminate\Support\Facades\DB;

class ToolExecutor
{
    /**
     * Execute a tool call with validation
     */
    public function execute($agentKey, $toolName, $params = [])
    {
        // Alat sebenar (Master §11): kebenaran pengguna, kelulusan risiko tinggi, pengesahan hasil.
        if (app(\App\Modules\AIAgents\Tools\AgentToolRegistry::class)->get($toolName)) {
            $r = app(\App\Modules\AIAgents\Tools\AgentToolRunner::class)->jalan((string) $agentKey, $toolName, (array) $params, auth()->id());

            return ['success' => $r['berjaya'], 'status' => $r['status'], 'result' => $r['hasil'] ?? null] + $r;
        }

        // Validate tool exists
        if (!$this->toolExists($toolName)) {
            throw new \InvalidArgumentException("Tool '{$toolName}' does not exist.");
        }

        // Validate agent has permission for this tool
        if (!$this->agentHasToolPermission($agentKey, $toolName)) {
            throw new \InvalidArgumentException("Agent '{$agentKey}' is not allowed to use tool '{$toolName}'.");
        }

        // Validate parameters
        if (!$this->validateParameters($toolName, $params)) {
            throw new \InvalidArgumentException("Invalid parameters for tool '{$toolName}'.");
        }

        // Log tool execution
        $executionId = DB::table('ai_tool_executions')->insertGetId([
            'tenant_id' => app(\App\Support\Tenancy\TenantContext::class)->id() ?? 0,
            'agent_key' => $agentKey,
            'tool_name' => $toolName,
            'parameters_json' => json_encode($params),
            'status' => 'running',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            // Execute the tool
            $result = $this->dispatchTool($toolName, $params);

            // Update execution record
            DB::table('ai_tool_executions')
                ->where('id', $executionId)
                ->update([
                    'status' => 'completed',
                    'output' => json_encode($result),
                    'updated_at' => now(),
                ]);

            return [
                'success' => true,
                'execution_id' => $executionId,
                'result' => $result,
            ];
        } catch (\Exception $e) {
            // Update execution record with error
            DB::table('ai_tool_executions')
                ->where('id', $executionId)
                ->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'updated_at' => now(),
                ]);

            return [
                'success' => false,
                'execution_id' => $executionId,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if tool exists in registry
     */
    protected function toolExists($toolName)
    {
        $tools = config('ai_agents.tools', []);
        return isset($tools[$toolName]);
    }

    /**
     * Check if agent has permission for tool
     */
    protected function agentHasToolPermission($agentKey, $toolName)
    {
        $agents = config('ai_agents.agents', []);
        if (!isset($agents[$agentKey])) {
            return false;
        }

        $allowedTools = $agents[$agentKey]['tools'] ?? [];
        return in_array($toolName, $allowedTools);
    }

    /**
     * Validate tool parameters
     */
    protected function validateParameters($toolName, $params)
    {
        $tools = config('ai_agents.tools', []);
        if (!isset($tools[$toolName])) {
            return true; // No schema defined, allow all
        }

        $schema = $tools[$toolName]['parameters'] ?? [];
        
        foreach ($schema as $param => $rules) {
            if (($rules['required'] ?? false) && !isset($params[$param])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Dispatch tool to appropriate handler
     */
    protected function dispatchTool($toolName, $params)
    {
        // Pengendali lama di bawah ialah STUB yang memulangkan "berjaya" tanpa membuat apa-apa
        // (cth. email.send → email_sent=true tanpa emel). Itu lebih bahaya daripada gagal:
        // ejen akan mengaku siap. Sehingga setiap alat diganti dengan kelas dalam
        // AIAgents/Tools (servis domain + kebenaran + pengesahan), alat ini gagal secara jelas.
        throw new \RuntimeException("Alat '{$toolName}' belum dilaksanakan (not_implemented). Tiada tindakan diambil.");

        // Map tools to their handlers
        $handlers = [
            // CEO tools
            'create_task' => fn($p) => $this->handleCreateTask($p),
            'assign_agent' => fn($p) => $this->handleAssignAgent($p),
            'summarize_workspace' => fn($p) => $this->handleSummarizeWorkspace($p),
            
            // Executive Assistant tools
            'calendar.read' => fn($p) => $this->handleCalendarRead($p),
            'calendar.write' => fn($p) => $this->handleCalendarWrite($p),
            'email.draft' => fn($p) => $this->handleEmailDraft($p),
            'email.send' => fn($p) => $this->handleEmailSend($p),
            'task.create' => fn($p) => $this->handleTaskCreate($p),
            'task.update' => fn($p) => $this->handleTaskUpdate($p),
            'notes.create' => fn($p) => $this->handleNotesCreate($p),
            'reminders.set' => fn($p) => $this->handleRemindersSet($p),
            
            // CMO tools
            'campaign.draft' => fn($p) => $this->handleCampaignDraft($p),
            'campaign.publish' => fn($p) => $this->handleCampaignPublish($p),
            'ads.read' => fn($p) => $this->handleAdsRead($p),
            'ads.optimize' => fn($p) => $this->handleAdsOptimize($p),
            'content.generate' => fn($p) => $this->handleContentGenerate($p),
            'audience.segment' => fn($p) => $this->handleAudienceSegment($p),
            'analytics.read' => fn($p) => $this->handleAnalyticsRead($p),
            'seo.audit' => fn($p) => $this->handleSeoAudit($p),
            
            // CFO tools
            'finance.read' => fn($p) => $this->handleFinanceRead($p),
            'report.generate' => fn($p) => $this->handleReportGenerate($p),
            'invoice.draft' => fn($p) => $this->handleInvoiceDraft($p),
            'cashflow.forecast' => fn($p) => $this->handleCashflowForecast($p),
            'budget.monitor' => fn($p) => $this->handleBudgetMonitor($p),
            'risk.alert' => fn($p) => $this->handleRiskAlert($p),
            'reconciliation.check' => fn($p) => $this->handleReconciliationCheck($p),
            
            // COO tools
            'inventory.read' => fn($p) => $this->handleInventoryRead($p),
            'inventory.write' => fn($p) => $this->handleInventoryWrite($p),
            'task.assign' => fn($p) => $this->handleTaskAssign($p),
            'operations.report' => fn($p) => $this->handleOperationsReport($p),
            'sop.create' => fn($p) => $this->handleSopCreate($p),
            'capacity.plan' => fn($p) => $this->handleCapacityPlan($p),
            'vendor.manage' => fn($p) => $this->handleVendorManage($p),
            
            // CSO tools
            'crm.read' => fn($p) => $this->handleCrmRead($p),
            'crm.write' => fn($p) => $this->handleCrmWrite($p),
            'lead.update' => fn($p) => $this->handleLeadUpdate($p),
            'lead.score' => fn($p) => $this->handleLeadScore($p),
            'sales.report' => fn($p) => $this->handleSalesReport($p),
            'pipeline.analyze' => fn($p) => $this->handlePipelineAnalyze($p),
            'affiliate.manage' => fn($p) => $this->handleAffiliateManage($p),
            'quote.draft' => fn($p) => $this->handleQuoteDraft($p),
            
            // CHRO tools
            'hr.read' => fn($p) => $this->handleHrRead($p),
            'hr.write' => fn($p) => $this->handleHrWrite($p),
            'shift.draft' => fn($p) => $this->handleShiftDraft($p),
            'leave.review' => fn($p) => $this->handleLeaveReview($p),
            'onboarding.create' => fn($p) => $this->handleOnboardingCreate($p),
            'policy.qa' => fn($p) => $this->handlePolicyQa($p),
            'performance.track' => fn($p) => $this->handlePerformanceTrack($p),
            
            // Support tools
            'knowledge.search' => fn($p) => $this->handleKnowledgeSearch($p),
            'conversation.reply_draft' => fn($p) => $this->handleConversationReplyDraft($p),
            'ticket.classify' => fn($p) => $this->handleTicketClassify($p),
            'order.lookup' => fn($p) => $this->handleOrderLookup($p),
            'refund.process' => fn($p) => $this->handleRefundProcess($p),
            'escalate.ticket' => fn($p) => $this->handleEscalateTicket($p),
            
            // Ads Specialist tools
            'ads.performance.read' => fn($p) => $this->handleAdsPerformanceRead($p),
            'budget.optimize' => fn($p) => $this->handleBudgetOptimize($p),
            'audience.test' => fn($p) => $this->handleAudienceTest($p),
            'creative.generate' => fn($p) => $this->handleCreativeGenerate($p),
            'conversion.track' => fn($p) => $this->handleConversionTrack($p),
            
            // Researcher tools
            'web.research' => fn($p) => $this->handleWebResearch($p),
            'competitor.compare' => fn($p) => $this->handleCompetitorCompare($p),
            'trend.analyze' => fn($p) => $this->handleTrendAnalyze($p),
            'market.scan' => fn($p) => $this->handleMarketScan($p),
            'source.validate' => fn($p) => $this->handleSourceValidate($p),
            
            // Analyst tools
            'analytics.query' => fn($p) => $this->handleAnalyticsQuery($p),
            'forecast.generate' => fn($p) => $this->handleForecastGenerate($p),
            'trend.analyze' => fn($p) => $this->handleTrendAnalyze($p),
            'dashboard.create' => fn($p) => $this->handleDashboardCreate($p),
            'metric.compare' => fn($p) => $this->handleMetricCompare($p),
            'insight.generate' => fn($p) => $this->handleInsightGenerate($p),
            'report.export' => fn($p) => $this->handleReportExport($p),
        ];

        if (isset($handlers[$toolName])) {
            return $handlers[$toolName]($params);
        }

        throw new \RuntimeException("No handler registered for tool '{$toolName}'.");
    }

    // ===== Handler Methods (placeholder implementations) =====
    
    protected function handleCreateTask($params) { return ['task_created' => true]; }
    protected function handleAssignAgent($params) { return ['agent_assigned' => true]; }
    protected function handleSummarizeWorkspace($params) { return ['summary' => '']; }
    protected function handleCalendarRead($params) { return ['events' => []]; }
    protected function handleCalendarWrite($params) { return ['event_created' => true]; }
    protected function handleEmailDraft($params) { return ['draft_created' => true]; }
    protected function handleEmailSend($params) { return ['email_sent' => true]; }
    protected function handleTaskCreate($params) { return ['task_created' => true]; }
    protected function handleTaskUpdate($params) { return ['task_updated' => true]; }
    protected function handleNotesCreate($params) { return ['note_created' => true]; }
    protected function handleRemindersSet($params) { return ['reminder_set' => true]; }
    protected function handleCampaignDraft($params) { return ['campaign_draft' => true]; }
    protected function handleCampaignPublish($params) { return ['campaign_published' => true]; }
    protected function handleAdsRead($params) { return ['ads_data' => []]; }
    protected function handleAdsOptimize($params) { return ['optimized' => true]; }
    protected function handleContentGenerate($params) { return ['content' => '']; }
    protected function handleAudienceSegment($params) { return ['segments' => []]; }
    protected function handleAnalyticsRead($params) { return ['analytics' => []]; }
    protected function handleSeoAudit($params) { return ['audit' => []]; }
    protected function handleFinanceRead($params) { return ['finance_data' => []]; }
    protected function handleReportGenerate($params) { return ['report' => '']; }
    protected function handleInvoiceDraft($params) { return ['invoice_draft' => true]; }
    protected function handleCashflowForecast($params) { return ['forecast' => []]; }
    protected function handleBudgetMonitor($params) { return ['monitoring' => true]; }
    protected function handleRiskAlert($params) { return ['alerts' => []]; }
    protected function handleReconciliationCheck($params) { return ['reconciled' => true]; }
    protected function handleInventoryRead($params) { return ['inventory' => []]; }
    protected function handleInventoryWrite($params) { return ['inventory_updated' => true]; }
    protected function handleTaskAssign($params) { return ['task_assigned' => true]; }
    protected function handleOperationsReport($params) { return ['report' => '']; }
    protected function handleSopCreate($params) { return ['sop_created' => true]; }
    protected function handleCapacityPlan($params) { return ['plan' => []]; }
    protected function handleVendorManage($params) { return ['vendor_managed' => true]; }
    protected function handleCrmRead($params) { return ['crm_data' => []]; }
    protected function handleCrmWrite($params) { return ['crm_updated' => true]; }
    protected function handleLeadUpdate($params) { return ['lead_updated' => true]; }
    protected function handleLeadScore($params) { return ['score' => 0]; }
    protected function handleSalesReport($params) { return ['report' => '']; }
    protected function handlePipelineAnalyze($params) { return ['analysis' => []]; }
    protected function handleAffiliateManage($params) { return ['managed' => true]; }
    protected function handleQuoteDraft($params) { return ['quote_draft' => true]; }
    protected function handleHrRead($params) { return ['hr_data' => []]; }
    protected function handleHrWrite($params) { return ['hr_updated' => true]; }
    protected function handleShiftDraft($params) { return ['shift_draft' => true]; }
    protected function handleLeaveReview($params) { return ['leave_reviewed' => true]; }
    protected function handleOnboardingCreate($params) { return ['onboarding_created' => true]; }
    protected function handlePolicyQa($params) { return ['answer' => '']; }
    protected function handlePerformanceTrack($params) { return ['tracking' => true]; }
    protected function handleKnowledgeSearch($params) { return ['results' => []]; }
    protected function handleConversationReplyDraft($params) { return ['reply_draft' => '']; }
    protected function handleTicketClassify($params) { return ['classified' => true]; }
    protected function handleOrderLookup($params) { return ['order' => null]; }
    protected function handleRefundProcess($params) { return ['refunded' => true]; }
    protected function handleEscalateTicket($params) { return ['escalated' => true]; }
    protected function handleAdsPerformanceRead($params) { return ['performance' => []]; }
    protected function handleBudgetOptimize($params) { return ['optimized' => true]; }
    protected function handleAudienceTest($params) { return ['test_created' => true]; }
    protected function handleCreativeGenerate($params) { return ['creative' => '']; }
    protected function handleConversionTrack($params) { return ['tracked' => true]; }
    protected function handleWebResearch($params) { return ['research' => []]; }
    protected function handleCompetitorCompare($params) { return ['comparison' => []]; }
    protected function handleTrendAnalyze($params) { return ['trends' => []]; }
    protected function handleMarketScan($params) { return ['scan' => []]; }
    protected function handleSourceValidate($params) { return ['validated' => true]; }
    protected function handleAnalyticsQuery($params) { return ['data' => []]; }
    protected function handleForecastGenerate($params) { return ['forecast' => []]; }
    protected function handleDashboardCreate($params) { return ['dashboard_created' => true]; }
    protected function handleMetricCompare($params) { return ['comparison' => []]; }
    protected function handleInsightGenerate($params) { return ['insights' => []]; }
    protected function handleReportExport($params) { return ['export_url' => '']; }
}
