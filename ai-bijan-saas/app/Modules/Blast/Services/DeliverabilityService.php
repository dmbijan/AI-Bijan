<?php

namespace App\Modules\Blast\Services;

use App\Modules\Blast\Models\BlastCampaign;
use App\Modules\Blast\Models\BlastDeliverability;
use App\Modules\Blast\Models\BlastMessage;
use Illuminate\Support\Facades\Log;

/**
 * DeliverabilityService — pantau bounce & complaint, jeda kempen automatik.
 *
 * ⚠️ Ambang ini adalah penaga Deliverability. Melewati bounce 5% atau
 * complaint 0.1% akan menyebabkan reputasi domain rosak dan akaun
 * email provider disekat.
 */
class DeliverabilityService
{
    public function __construct(private RateLimiterService $limiter) {}

    /**
     * Rekod keputusan hantaran (sent/delivered/bounce/complaint).
     */
    public function rekod(int $tenantId, string $domain, string $status): ?BlastDeliverability
    {
        $record = $this->limiter->deliverability($tenantId, $domain);

        match ($status) {
            BlastMessage::STATUS_SENT => $record->increment('sent'),
            BlastMessage::STATUS_DELIVERED => $record->increment('delivered'),
            BlastMessage::STATUS_BOUNCED => $record->increment('bounced'),
            default => null,
        };

        $record->refresh();
        $this->kiraReputasi($record);
        $record->save();

        return $record;
    }

    /**
     * Kira skor reputasi (0-100) dan jeda jika perlu.
     *
     * Menyimpan sendiri supaya boleh dipanggil secara terus.
     */
    public function kiraReputasi(BlastDeliverability $record): int
    {
        $score = 100;

        // Bounce: -5 setiap 1% bounce
        $bounceRate = $record->bounceRate();
        $score -= (int) round($bounceRate * 5);

        // Complaint: -50 setiap 0.1% complaint (sangat berat)
        $complaintRate = $record->complaintRate();
        $score -= (int) round($complaintRate * 500);

        // Hard bounce atau bounce tinggi → jeda
        $bounceThreshold = (float) config('blast.deliverability.bounce_threshold', 5.0);
        $complaintThreshold = (float) config('blast.deliverability.complaint_threshold', 0.1);

        $shouldPause = $record->bounceRate() > $bounceThreshold
            || $record->complaintRate() > $complaintThreshold;

        if ($shouldPause && ! $record->is_paused) {
            $reason = sprintf(
                'Auto-pause: bounce %.2f%% (had %.1f%%), complaint %.3f%% (had %.1f%%)',
                $record->bounceRate(),
                $bounceThreshold,
                $record->complaintRate(),
                $complaintThreshold
            );

            $record->is_paused = true;
            $record->pause_reason = $reason;

            $this->jedaKempenUntukDomain($record->tenant_id, $record->domain, $reason);

            Log::warning('Blast: domain dijeda kerana deliverability', [
                'tenant_id' => $record->tenant_id,
                'domain' => $record->domain,
                'reason' => $reason,
            ]);
        }

        // Pulihkan jika sudah baik
        if (! $shouldPause && $record->is_paused && $record->bounceRate() < 1.0 && $record->complaintRate() < 0.01) {
            $record->is_paused = false;
            $record->pause_reason = null;
        }

        $record->reputation_score = max(0, min(100, $score));
        $record->save();

        return $record->reputation_score;
    }

    /**
     * Jeda semua kempen yang menggunakan domain ini.
     */
    public function jedaKempenUntukDomain(int $tenantId, string $domain, string $reason): int
    {
        $campaigns = BlastCampaign::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [BlastCampaign::STATUS_APPROVED, BlastCampaign::STATUS_SCHEDULED, BlastCampaign::STATUS_RUNNING])
            ->get();

        $count = 0;

        foreach ($campaigns as $campaign) {
            // Domain dikira dari reply_to config
            if (config('blast.reply_to') && str_ends_with(config('blast.reply_to'), '@'.$domain)) {
                $campaign->update([
                    'status' => BlastCampaign::STATUS_PAUSED,
                    'notes' => trim(($campaign->notes ?? '')."\n[Auto-pause] ".$reason),
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Semak rekod DNS (SPF/DKIM/DMARC) untuk domain.
     */
    public function semakDns(string $domain): array
    {
        $results = [
            'domain' => $domain,
            'spf' => false,
            'dkim' => null,
            'dmarc' => null,
            'checked_at' => now()->toIso8601String(),
        ];

        // SPF — cari rekod TXT
        $txt = @dns_get_record($domain, DNS_TXT);
        if ($txt) {
            foreach ($txt as $record) {
                if (isset($record['txt']) && str_starts_with(strtoupper($record['txt']), 'V=SPF1')) {
                    $results['spf'] = true;
                    break;
                }
            }
        }

        // DMARC
        $dmarc = @dns_get_record('_dmarc.'.$domain, DNS_TXT);
        if ($dmarc) {
            foreach ($dmarc as $record) {
                if (isset($record['txt']) && str_contains($record['txt'], 'v=DMARC1')) {
                    $results['dmarc'] = $record['txt'];
                    break;
                }
            }
        }

        // DKIM — cari selektor biasa
        foreach (['default', 'google', 'mail', 'selector1', 'selector2'] as $selector) {
            $dkim = @dns_get_record("{$selector}._domainkey.{$domain}", DNS_TXT);
            if ($dkim) {
                $results['dkim'] = $selector;
                break;
            }
        }

        return $results;
    }
}
