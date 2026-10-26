<?php

namespace App\Modules\Blast\Services;

use App\Modules\Blast\Models\BlastCampaign;
use App\Modules\Blast\Models\BlastMessage;
use App\Modules\Blast\Services\Providers\ChannelProviderInterface;
use App\Modules\Blast\Services\Providers\EmailProvider;
use App\Modules\Blast\Services\Providers\SmsProvider;
use App\Modules\Blast\Services\Providers\WhatsAppProvider;
use App\Modules\Prospecting\Models\Prospect;
use App\Modules\Prospecting\Services\ConsentService;
use App\Modules\Prospecting\Services\DedupeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * BlastService — orkestrator penghantaran blast.
 *
 * ⚠️ RUJUKAN PEMATUHAN: Setiap mesej mesti lulus 4 pagar sebelum dihantar:
 * 1. Consent prospect (ConsentService)
 * 2. Bukan dalam senarai penindihan (OptOutService)
 * 3. Had kadar (RateLimiterService)
 * 4. Kempen diluluskan (BlastCampaign::bolehDihantar)
 */
class BlastService
{
    public function __construct(
        private ConsentService $consent,
        private OptOutService $optOut,
        private RateLimiterService $limiter,
        private DeliverabilityService $deliverability,
        private DedupeService $dedupe
    ) {
    }

    /**
     * Provider untuk channel tertentu.
     */
    public function provider(string $channel): ?ChannelProviderInterface
    {
        return match ($channel) {
            'email' => new EmailProvider(),
            'whatsapp' => new WhatsAppProvider(),
            'sms' => new SmsProvider(),
            default => null,
        };
    }

    /**
     * Bina mesej untuk kempen daripada senarai prospect.
     *
     * Mesej yang gagal consent DISKIP, bukan dihantar.
     *
     * @param  iterable  $prospects  Prospect atau senarai Prospect
     * @return array{queued: int, skipped: int, details: array}
     */
    public function binaPesanan(BlastCampaign $campaign, iterable $prospects, ?string $template = null): array
    {
        $queued = 0;
        $skipped = 0;
        $details = [];
        $tenantId = $campaign->tenant_id;
        $channel = $campaign->channel === BlastCampaign::CHANNEL_MULTI
            ? 'email'
            : $campaign->channel;

        $bodyTemplate = $template ?? $campaign->body ?? '';
        $unsubUrl = $this->unsubscribeUrl($tenantId);

        foreach ($prospects as $prospect) {
            // PAGAR 1: Consent
            if (! $this->consent->bolehDihantar($prospect, $channel)) {
                $skipped++;
                $details[] = [
                    'prospect_id' => $prospect->id,
                    'reason' => 'tiada_consent',
                ];

                continue;
            }

            // PAGAR 2: Penindihan
            $recipient = $channel === 'email' ? $prospect->email : $prospect->phone;

            if ($this->optOut->disekat($tenantId, $prospect->email, $prospect->phone, $channel)) {
                $skipped++;
                $details[] = [
                    'prospect_id' => $prospect->id,
                    'reason' => 'disekat',
                ];

                continue;
            }

            // PAGAR 3: Had kadar
            if (! $this->limiter->bolehHantar($tenantId, $channel)) {
                $skipped++;
                $details[] = [
                    'prospect_id' => $prospect->id,
                    'reason' => 'had_kadar',
                ];

                break; // stop seluruh senarai — had harian penuh
            }

            $merge = TemplateService::dataDaripadaProspect($prospect, $campaign);
            $merge['unsubscribe_link'] = $unsubUrl;

            $body = (new TemplateService())
                ->dengan($merge)
                ->denganUnsub($unsubUrl)
                ->render($bodyTemplate);

            $subject = (new TemplateService())
                ->dengan($merge)
                ->render($campaign->subject ?? '');

            BlastMessage::create([
                'tenant_id' => $tenantId,
                'campaign_id' => $campaign->id,
                'prospect_id' => $prospect->id,
                'channel' => $channel,
                'recipient' => $recipient,
                'subject' => $subject ?: null,
                'body' => $body,
                'status' => BlastMessage::STATUS_QUEUED,
            ]);

            $queued++;
        }

        return ['queued' => $queued, 'skipped' => $skipped, 'details' => $details];
    }

    /**
     * Hantar satu mesej (dipanggil oleh job).
     */
    public function hantarPesanan(BlastMessage $message): bool
    {
        $campaign = $message->campaign;

        if (! $campaign) {
            $message->update(['status' => BlastMessage::STATUS_FAILED, 'error' => 'Kempen tidak dijumpai']);

            return false;
        }

        // PAGAR 4: Kempen mesti diluluskan
        if (! $campaign->bolehDihantar()) {
            $message->update([
                'status' => BlastMessage::STATUS_SKIPPED,
                'error' => 'Kempen tidak berstatus boleh-hantar ('.$campaign->status.')',
            ]);

            return false;
        }

        $provider = $this->provider($message->channel);

        if (! $provider) {
            $message->update([
                'status' => BlastMessage::STATUS_FAILED,
                'error' => 'Provider tidak disokong: '.$message->channel,
            ]);

            return false;
        }

        if (! $provider->tersedia()) {
            $message->update([
                'status' => BlastMessage::STATUS_SKIPPED,
                'error' => 'Provider tidak dikonfigurasi: '.$provider->nama(),
            ]);

            return false;
        }

        // Semak semula penindihan (boleh berubah sejak queue)
        $prospect = $message->prospect;
        if ($prospect) {
            if (! $this->consent->bolehDihantar($prospect, $message->channel)) {
                $message->update([
                    'status' => BlastMessage::STATUS_SKIPPED,
                    'error' => 'Consent ditolak semasa penghantaran',
                ]);

                return false;
            }

            if ($this->optOut->disekat($message->tenant_id, $prospect->email, $prospect->phone, $message->channel)) {
                $message->update([
                    'status' => BlastMessage::STATUS_SUPPRESSED,
                    'error' => 'Penerima disekat',
                ]);

                return false;
            }
        }

        $started = microtime(true);
        $message->update(['status' => BlastMessage::STATUS_SENDING, 'attempts' => $message->attempts + 1]);

        $result = $provider->hantar($message->recipient, (string) $message->body, [
            'subject' => $message->subject,
            'from' => config('blast.from_address'),
            'from_name' => config('blast.from_name'),
            'reply_to' => config('blast.reply_to'),
            'unsubscribe_url' => $this->unsubscribeUrl($message->tenant_id),
            'template' => $message->metadata['template'] ?? null,
            'parameters' => $message->metadata['parameters'] ?? null,
        ]);

        $latency = (int) round((microtime(true) - $started) * 1000);

        if (! $result['ok']) {
            $message->update([
                'status' => BlastMessage::STATUS_FAILED,
                'error' => $result['error'],
                'latency_ms' => $latency,
            ]);

            Log::warning('Blast: penghantaran gagal', [
                'message_id' => $message->id,
                'channel' => $message->channel,
                'error' => $result['error'],
            ]);

            return false;
        }

        $message->update([
            'status' => BlastMessage::STATUS_SENT,
            'provider_message_id' => $result['message_id'],
            'sent_at' => now(),
            'latency_ms' => $latency,
            'error' => null,
        ]);

        // Rekod deliverability
        $domain = $this->domainDaripada($message);
        if ($domain) {
            $this->deliverability->rekod($message->tenant_id, $domain, BlastMessage::STATUS_SENT);
        }

        // Tandakan prospect sebagai contacted
        if ($message->prospect_id) {
            $sentAt = now();

            Prospect::query()
                ->where('tenant_id', $message->tenant_id)
                ->where('id', $message->prospect_id)
                ->update([
                    'status' => Prospect::STATUS_CONTACTED,
                    'first_touched_at' => DB::raw("COALESCE(first_touched_at, '".$sentAt->toDateTimeString()."')"),
                    'last_touched_at' => $sentAt,
                ]);
        }

        return true;
    }

    /**
     * Domain penghantar untuk pantau deliverability.
     */
    private function domainDaripada(BlastMessage $message): ?string
    {
        $replyTo = config('blast.reply_to') ?: config('blast.from_address');

        if (! $replyTo || ! str_contains($replyTo, '@')) {
            return null;
        }

        return Str::after($replyTo, '@');
    }

    /**
     * URL langganan untuk penyisipan dalam badan mesej.
     */
    public function unsubscribeUrl(int $tenantId): string
    {
        return rtrim((string) config('blast.app_url'), '/').'/blast/unsubscribe/'.$tenantId;
    }
}
