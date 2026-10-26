<?php

namespace App\Modules\Marketing\Services;

use App\Modules\Marketing\Models\Contact;
use App\Modules\Marketing\Models\Sequence;
use App\Modules\Marketing\Models\SequenceExecution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SequenceService
{
    /**
     * Get all sequences for a tenant.
     */
    public function getSequences(int $tenantId, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Sequence::where('tenant_id', $tenantId);

        if (isset($filters['active_only'])) {
            $query->active();
        }

        if (isset($filters['trigger'])) {
            $query->byTrigger($filters['trigger']);
        }

        if (isset($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get a sequence by ID.
     */
    public function getSequenceById(int $sequenceId, int $tenantId): ?Sequence
    {
        return Sequence::where('id', $sequenceId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Get a sequence by slug.
     */
    public function getSequenceBySlug(string $slug, int $tenantId): ?Sequence
    {
        return Sequence::where('slug', $slug)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Create a new sequence.
     */
    public function createSequence(array $data): Sequence
    {
        if (empty($data['slug'])) {
            $data['slug'] = Sequence::generateSlug($data['name']);
        }

        return DB::transaction(function () use ($data) {
            return Sequence::create($data);
        });
    }

    /**
     * Update a sequence.
     */
    public function updateSequence(Sequence $sequence, array $data): Sequence
    {
        return DB::transaction(function () use ($sequence, $data) {
            $sequence->update($data);
            return $sequence->fresh();
        });
    }

    /**
     * Delete a sequence.
     */
    public function deleteSequence(Sequence $sequence): bool
    {
        return $sequence->delete();
    }

    /**
     * Toggle sequence active status.
     */
    public function toggleActive(Sequence $sequence): Sequence
    {
        return $sequence->update(['is_active' => !$sequence->is_active]) ? $sequence->fresh() : null;
    }

    /**
     * Start sequence execution for a contact.
     */
    public function startExecution(Sequence $sequence, int $contactId): ?SequenceExecution
    {
        // Check if already running
        $existing = SequenceExecution::where('sequence_id', $sequence->id)
            ->where('contact_id', $contactId)
            ->whereIn('status', ['running', 'pending', 'waiting'])
            ->first();

        if ($existing) {
            return null;
        }

        return DB::transaction(function () use ($sequence, $contactId) {
            return SequenceExecution::create([
                'tenant_id' => $sequence->tenant_id,
                'sequence_id' => $sequence->id,
                'contact_id' => $contactId,
                'current_step' => 0,
                'status' => 'pending',
                'started_at' => now(),
            ]);
        });
    }

    /**
     * Advance to next step in sequence.
     */
    public function advanceStep(SequenceExecution $execution): ?SequenceExecution
    {
        $sequence = $execution->sequence;
        $steps = $sequence->steps ?? [];
        $nextStepIndex = $execution->current_step + 1;

        if ($nextStepIndex >= count($steps)) {
            // Sequence complete
            return $this->completeExecution($execution);
        }

        // Check max repetitions
        if ($sequence->max_repetitions && $nextStepIndex > $sequence->max_repetitions) {
            return $this->stopExecution($execution, 'max_repetitions_reached');
        }

        return DB::transaction(function () use ($execution, $nextStepIndex) {
            $execution->update([
                'current_step' => $nextStepIndex,
                'status' => 'pending',
            ]);
            return $execution->fresh();
        });
    }

    /**
     * Complete a sequence execution.
     */
    public function completeExecution(SequenceExecution $execution): SequenceExecution
    {
        return $execution->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]) ? $execution->fresh() : null;
    }

    /**
     * Stop a sequence execution.
     */
    public function stopExecution(SequenceExecution $execution, string $reason): SequenceExecution
    {
        return $execution->update([
            'status' => 'stopped',
            'stopped_at' => now(),
            'stop_reason' => $reason,
        ]) ? $execution->fresh() : null;
    }

    /**
     * Execute sequence steps based on trigger.
     */
    public function executeSequence(Sequence $sequence, int $contactId): array
    {
        $results = [];
        $execution = $this->startExecution($sequence, $contactId);

        if (!$execution) {
            return ['success' => false, 'message' => 'Execution already exists or failed to start.'];
        }

        $steps = $sequence->steps ?? [];
        
        foreach ($steps as $index => $step) {
            try {
                // Simulate step execution (email send, SMS, etc.)
                $result = $this->executeStep($sequence, $execution, $step);
                $results[] = $result;

                if (!$result['success']) {
                    $this->stopExecution($execution, 'step_failed');
                    break;
                }

                // Handle delay if specified
                if (isset($step['delay_hours'])) {
                    // In production, this would be handled by queue/worker
                    usleep(($step['delay_hours'] * 3600) * 1000000); // For testing only
                }

                // Check if should stop on response
                if ($sequence->stop_on_response && $this->hasContactResponded($execution->contact_id)) {
                    $this->stopExecution($execution, 'contact_responded');
                    break;
                }

                $this->advanceStep($execution);

            } catch (\Exception $e) {
                $results[] = ['success' => false, 'error' => $e->getMessage()];
                $this->stopExecution($execution, 'exception');
                break;
            }
        }

        return [
            'success' => true,
            'execution_id' => $execution->id,
            'steps_executed' => count($results),
            'results' => $results,
        ];
    }

    /**
     * Execute a single step.
     *
     * Guna Blast Engine (BAB 82 Fasa 2) — bukan placeholder.
     * Jenis step: send_email, send_sms, send_whatsapp, wait.
     */
    protected function executeStep(Sequence $sequence, SequenceExecution $execution, array $step): array
    {
        $type = $step['type'] ?? '';
        $properties = $step['properties'] ?? [];

        if ($type === 'wait') {
            // Tunggu langkah seterusnya (diuruskan oleh scheduler).
            return ['success' => true, 'step' => $type, 'deferred' => true];
        }

        $providerClass = match ($type) {
            'send_email' => \App\Modules\Blast\Services\Providers\EmailProvider::class,
            'send_sms' => \App\Modules\Blast\Services\Providers\SmsProvider::class,
            'send_whatsapp' => \App\Modules\Blast\Services\Providers\WhatsAppProvider::class,
            default => null,
        };

        if (! $providerClass) {
            return ['success' => false, 'step' => $type, 'error' => 'Jenis step tidak disokong'];
        }

        $contact = $execution->contact ?? Contact::find($execution->contact_id);

        if (! $contact) {
            return ['success' => false, 'step' => $type, 'error' => 'Kontak tidak dijumpai'];
        }

        // Recipient mengikut channel
        $recipient = match ($type) {
            'send_email' => $contact->email,
            default => $contact->phone,
        };

        $body = $properties['body'] ?? $properties['message'] ?? '';

        if (! $recipient || ! $body) {
            return ['success' => false, 'step' => $type, 'error' => 'Recipient atau badan mesej kosong'];
        }

        $provider = app($providerClass);

        if (! $provider->tersedia()) {
            return ['success' => false, 'step' => $type, 'error' => 'Provider tidak dikonfigurasi'];
        }

        $hasil = $provider->hantar($recipient, $body, [
            'subject' => $properties['subject'] ?? '',
            'from' => config('blast.from_address'),
            'from_name' => config('blast.from_name'),
            'reply_to' => config('blast.reply_to'),
            'template' => $properties['template'] ?? null,
            'parameters' => $properties['parameters'] ?? null,
        ]);

        if (! $hasil['ok']) {
            Log::error('Sequence: penghantaran gagal', [
                'sequence_id' => $sequence->id,
                'step' => $type,
                'error' => $hasil['error'],
            ]);

            return ['success' => false, 'step' => $type, 'error' => $hasil['error']];
        }

        // Rekod dalam blast_messages untuk jejak & analytics
        try {
            \App\Modules\Blast\Models\BlastMessage::create([
                'tenant_id' => $sequence->tenant_id,
                'campaign_id' => $sequence->campaign_id ?? 0,
                'contact_id' => $contact->id,
                'channel' => match ($type) {
                    'send_email' => 'email',
                    'send_sms' => 'sms',
                    default => 'whatsapp',
                },
                'recipient' => $recipient,
                'subject' => $properties['subject'] ?? null,
                'body' => $body,
                'status' => \App\Modules\Blast\Models\BlastMessage::STATUS_SENT,
                'provider_message_id' => $hasil['message_id'],
                'sent_at' => now(),
                'metadata' => ['source' => 'sequence', 'sequence_id' => $sequence->id],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Sequence: gagal rekod blast message', ['error' => $e->getMessage()]);
        }

        return ['success' => true, 'step' => $type, 'message_id' => $hasil['message_id']];
    }

    /**
     * Check if contact has responded.
     */
    protected function hasContactResponded(int $contactId): bool
    {
        // Check for replies, clicks, form submissions, etc.
        return false; // Placeholder
    }

    /**
     * Get sequence statistics.
     */
    public function getSequenceStats(int $tenantId): array
    {
        $totalSequences = Sequence::where('tenant_id', $tenantId)->count();
        $activeSequences = Sequence::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();
        $totalExecutions = SequenceExecution::whereHas('sequence', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->count();
        $completedExecutions = SequenceExecution::whereHas('sequence', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->where('status', 'completed')->count();

        return [
            'total_sequences' => $totalSequences,
            'active_sequences' => $activeSequences,
            'total_executions' => $totalExecutions,
            'completed_executions' => $completedExecutions,
        ];
    }
}
