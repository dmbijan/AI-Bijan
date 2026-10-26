<?php

namespace App\Jobs\Blast;

use App\Modules\Blast\Models\BlastCampaign;
use App\Modules\Blast\Models\BlastMessage;
use App\Modules\Blast\Services\BlastService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Hantar satu kempen blast — dispatch semua mesej berantakan.
 *
 * Dipanggil oleh scheduler `blast-dispatch` dan selepas kempen diluluskan.
 */
class DispatchBlastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public int $campaignId)
    {
    }

    public function handle(BlastService $blast): void
    {
        $campaign = BlastCampaign::find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $tenant = \App\Models\Tenant::find($campaign->tenant_id);
        if ($tenant) {
            TenantContext::set($tenant);
        }

        if (! $campaign->bolehDihantar()) {
            Log::info('Blast: kempen tidak boleh dihantar, dispatch dibatalkan', [
                'campaign_id' => $campaign->id,
                'status' => $campaign->status,
            ]);

            return;
        }

        if ($campaign->status === BlastCampaign::STATUS_SCHEDULED) {
            $campaign->update([
                'status' => BlastCampaign::STATUS_RUNNING,
                'started_at' => now(),
            ]);
        }

        $messages = BlastMessage::query()
            ->where('tenant_id', $campaign->tenant_id)
            ->where('campaign_id', $campaign->id)
            ->where('status', BlastMessage::STATUS_QUEUED)
            ->limit((int) config('blast.quotas.batch_size'))
            ->get();

        foreach ($messages as $message) {
            SendBlastMessageJob::dispatch($message->id);
        }

        Log::info('Blast: dispatch mesej', [
            'campaign_id' => $campaign->id,
            'count' => $messages->count(),
        ]);
    }
}
