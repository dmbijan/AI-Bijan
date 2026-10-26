<?php

namespace App\Jobs\Blast;

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
 * Hantar satu mesej blast.
 *
 * Job beratur — boleh retry dengan backoff. Kalau gagal 3 kali, tandakan failed.
 */
class SendBlastMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(public int $blastMessageId)
    {
    }

    public function handle(BlastService $blast): void
    {
        $message = BlastMessage::find($this->blastMessageId);

        if (! $message) {
            return;
        }

        // Tenant context wajib untuk scope query
        $tenant = \App\Models\Tenant::find($message->tenant_id);
        if ($tenant) {
            TenantContext::set($tenant);
        }

        try {
            $blast->hantarPesanan($message);
        } catch (\Throwable $e) {
            Log::error('Blast job gagal', [
                'message_id' => $this->blastMessageId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
