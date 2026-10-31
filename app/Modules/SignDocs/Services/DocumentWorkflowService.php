<?php

namespace App\Modules\SignDocs\Services;

use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class DocumentWorkflowService
{
    /**
     * Create workflow for document
     */
    public function createWorkflow($documentId, $steps)
    {
        return DB::transaction(function () use ($documentId, $steps) {
            $document = \App\Modules\SignDocs\Models\Document::findOrFail($documentId);
            
            // Update document status to pending review if first step
            if ($document->status === 'draft') {
                $document->update(['status' => 'pending_review']);
            }

            // Create workflow steps
            foreach ($steps as $index => $step) {
                \App\Modules\SignDocs\Models\WorkflowStep::create([
                    'tenant_id' => app(TenantContext::class)->id(),
                    'document_id' => $documentId,
                    'step_number' => $index + 1,
                    'step_type' => $step['type'] ?? 'review',
                    'assigned_to' => $step['assigned_to'] ?? null,
                    'status' => 'pending',
                    'notes' => $step['notes'] ?? '',
                    'metadata' => $step['metadata'] ?? [],
                ]);
            }

            return true;
        });
    }

    /**
     * Get workflow steps for document
     */
    public function getWorkflowSteps($documentId)
    {
        return \App\Modules\SignDocs\Models\WorkflowStep::where('document_id', $documentId)
            ->orderBy('step_number')
            ->with('assignedUser')
            ->get();
    }

    /**
     * Start workflow step
     */
    public function startStep($stepId)
    {
        $step = \App\Modules\SignDocs\Models\WorkflowStep::findOrFail($stepId);
        $step->start();

        // Update document status
        $document = $step->document;
        if ($step->step_type === 'review') {
            $document->update(['status' => 'in_review']);
        } elseif ($step->step_type === 'approval') {
            $document->update(['status' => 'pending_approval']);
        } elseif ($step->step_type === 'signature') {
            $document->update(['status' => 'pending_signature']);
        }

        return $step;
    }

    /**
     * Complete workflow step
     */
    public function completeStep($stepId, $notes = '')
    {
        $step = \App\Modules\SignDocs\Models\WorkflowStep::findOrFail($stepId);
        $step->complete($notes);

        // Check if there are more steps
        $nextStep = \App\Modules\SignDocs\Models\WorkflowStep::where('document_id', $step->document_id)
            ->where('step_number', '>', $step->step_number)
            ->where('status', 'pending')
            ->orderBy('step_number')
            ->first();

        if ($nextStep) {
            $nextStep->start();
        } else {
            // All steps completed
            $document = $step->document;
            if (in_array($document->status, ['in_review', 'pending_approval'])) {
                $document->update(['status' => 'approved']);
            } elseif ($document->status === 'approved') {
                $document->update(['status' => 'pending_signature']);
            }
        }

        return $step;
    }

    /**
     * Reject workflow step
     */
    public function rejectStep($stepId, $reason = '')
    {
        $step = \App\Modules\SignDocs\Models\WorkflowStep::findOrFail($stepId);
        $step->reject($reason);

        // Update document status
        $step->document->update(['status' => 'rejected']);

        return $step;
    }

    /**
     * Get pending steps for user
     */
    public function getPendingForUser($userId)
    {
        return \App\Modules\SignDocs\Models\WorkflowStep::where('assigned_to', $userId)
            ->where('status', 'pending')
            ->orWhere('status', 'in_progress')
            ->with('document')
            ->get();
    }

    /**
     * Advance to next stage
     */
    public function advanceStage($documentId)
    {
        $document = \App\Modules\SignDocs\Models\Document::findOrFail($documentId);
        
        $stageFlow = [
            'draft' => 'pending_review',
            'pending_review' => 'in_review',
            'in_review' => 'pending_approval',
            'pending_approval' => 'approved',
            'approved' => 'pending_signature',
            'pending_signature' => 'signed',
        ];

        $currentStatus = $document->status;
        
        if (isset($stageFlow[$currentStatus])) {
            $document->update(['status' => $stageFlow[$currentStatus]]);
            return $document;
        }

        return $document;
    }
}
