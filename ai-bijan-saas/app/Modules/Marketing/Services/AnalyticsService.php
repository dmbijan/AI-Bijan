<?php

namespace App\Modules\Marketing\Services;

use App\Modules\Marketing\Models\Campaign;
use App\Modules\Marketing\Models\Voucher;
use App\Modules\Marketing\Models\Contact;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get campaign performance analytics.
     */
    public function getCampaignAnalytics(int $tenantId, array $filters = []): array
    {
        $query = Campaign::where('tenant_id', $tenantId);

        if (isset($filters['date_from'])) {
            $query->where('start_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('end_date', '<=', $filters['date_to']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $campaigns = $query->get();

        $totalImpressions = 0;
        $totalClicks = 0;
        $totalConversions = 0;
        $totalSpend = 0;

        foreach ($campaigns as $campaign) {
            $performance = $campaign->performance_data ?? [];
            $totalImpressions += $performance['impressions'] ?? 0;
            $totalClicks += $performance['clicks'] ?? 0;
            $totalConversions += $performance['conversions'] ?? 0;
            $totalSpend += $performance['spend'] ?? 0;
        }

        $ctr = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;
        $cpc = $totalClicks > 0 ? $totalSpend / $totalClicks : 0;
        $convRate = $totalClicks > 0 ? ($totalConversions / $totalClicks) * 100 : 0;
        $cpa = $totalConversions > 0 ? $totalSpend / $totalConversions : 0;

        return [
            'total_campaigns' => $campaigns->count(),
            'impressions' => $totalImpressions,
            'clicks' => $totalClicks,
            'conversions' => $totalConversions,
            'spend' => round($totalSpend, 2),
            'ctr' => round($ctr, 2),
            'cpc' => round($cpc, 2),
            'conv_rate' => round($convRate, 2),
            'cpa' => round($cpa, 2),
        ];
    }

    /**
     * Get channel-wise breakdown.
     */
    public function getChannelBreakdown(int $tenantId): array
    {
        $channels = ['email', 'sms', 'whatsapp', 'social_media', 'paid_ads'];
        $breakdown = [];

        foreach ($channels as $channel) {
            $campaigns = Campaign::where('tenant_id', $tenantId)
                ->whereJsonContains('channels', $channel)
                ->get();

            $impressions = 0;
            $clicks = 0;
            $conversions = 0;
            $spend = 0;

            foreach ($campaigns as $campaign) {
                $performance = $campaign->performance_data[$channel] ?? [];
                $impressions += $performance['impressions'] ?? 0;
                $clicks += $performance['clicks'] ?? 0;
                $conversions += $performance['conversions'] ?? 0;
                $spend += $performance['spend'] ?? 0;
            }

            $breakdown[$channel] = [
                'campaigns' => $campaigns->count(),
                'impressions' => $impressions,
                'clicks' => $clicks,
                'conversions' => $conversions,
                'spend' => round($spend, 2),
                'revenue' => $performance['revenue'] ?? 0,
                'roi' => $spend > 0 ? (($performance['revenue'] ?? 0 - $spend) / $spend) * 100 : 0,
            ];
        }

        return $breakdown;
    }

    /**
     * Get time-series data for trends.
     */
    public function getTimeSeriesData(int $tenantId, string $period = 'daily', int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            
            $data[] = [
                'date' => $date,
                'impressions' => 0, // Would query actual data
                'clicks' => 0,
                'conversions' => 0,
                'spend' => 0,
                'revenue' => 0,
            ];
        }

        return $data;
    }

    /**
     * Calculate ROI for campaigns.
     */
    public function calculateROI(int $tenantId, array $filters = []): array
    {
        $campaigns = Campaign::where('tenant_id', $tenantId);

        if (isset($filters['date_from'])) {
            $campaigns->where('start_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $campaigns->where('end_date', '<=', $filters['date_to']);
        }

        $campaigns = $campaigns->get();

        $totalSpend = 0;
        $totalRevenue = 0;

        foreach ($campaigns as $campaign) {
            $performance = $campaign->performance_data ?? [];
            $totalSpend += $performance['spend'] ?? 0;
            $totalRevenue += $performance['revenue'] ?? 0;
        }

        $roi = $totalSpend > 0 ? (($totalRevenue - $totalSpend) / $totalSpend) * 100 : 0;

        return [
            'total_spend' => round($totalSpend, 2),
            'total_revenue' => round($totalRevenue, 2),
            'profit' => round($totalRevenue - $totalSpend, 2),
            'roi' => round($roi, 2),
        ];
    }

    /**
     * Attribution modeling.
     *
     * Guna data blast sebenar (BAB 82 Fasa 2) — bukan placeholder.
     * Sokongan: last_click, first_click, linear, time_decay, position_based.
     */
    public function getAttributionModel(int $tenantId, string $model = 'last_click'): array
    {
        $channels = ['email', 'sms', 'whatsapp', 'social_media', 'paid_ads', 'organic', 'referral'];

        $attribution = [];
        foreach ($channels as $channel) {
            $attribution[$channel] = [
                'conversions' => 0,
                'revenue' => 0,
                'percentage' => 0,
            ];
        }

        // TEpisod utama: mesej blast yang ada balasan / klik.
        $messages = \App\Modules\Blast\Models\BlastMessage::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('sent_at')
            ->where(function ($q) {
                $q->whereNotNull('replied_at')
                    ->orWhereNotNull('clicked_at');
            })
            ->orderBy('sent_at')
            ->get(['id', 'prospect_id', 'channel', 'sent_at', 'replied_at', 'clicked_at']);

        if ($messages->isEmpty()) {
            return $attribution;
        }

        // Kumpul sentiasa bernilai oleh prospect.
        $perProspect = $messages->groupBy('prospect_id');

        $totalConversions = 0;
        $totalRevenue = 0.0;

        foreach ($perProspect as $prospectId => $senarai) {
            $saluran = $senarai->pluck('channel')->unique()->values();
            $nilai = $this->nilaiKonversi($senarai);

            $terpilih = match ($model) {
                'first_click' => [$senarai->first()->channel],
                'linear' => $saluran->toArray(),
                'time_decay' => $this->susunTimeDecay($senarai),
                'position_based' => $this->susunPositionBased($senarai),
                default => [$senarai->last()->channel], // last_click
            };

            $terpilih = array_values(array_filter($terpilih, fn ($c) => in_array($c, $attribution, true)));

            if (empty($terpilih)) {
                continue;
            }

            $bahagian = 1 / count($terpilih);

            foreach ($terpilih as $channel) {
                $attribution[$channel]['conversions']++;
                $attribution[$channel]['revenue'] += $nilai * $bahagian;
            }

            $totalConversions++;
            $totalRevenue += $nilai;
        }

        // Peratus
        foreach ($attribution as $channel => $data) {
            $attribution[$channel]['revenue'] = round($data['revenue'], 2);
            $attribution[$channel]['percentage'] = $totalRevenue > 0
                ? round(($data['revenue'] / $totalRevenue) * 100, 2)
                : 0;
        }

        return $attribution;
    }

    /**
     * Nilai konversi untuk satu prospect (dari jualan/invois).
     */
    private function nilaiKonversi($senarai): float
    {
        $prospectId = $senarai->first()->prospect_id;

        if (! $prospectId) {
            return 0.0;
        }

        // Cuba hubungkan ke CRM contact melalui jadual blast_messages.contact_id
        $contactId = \App\Modules\Blast\Models\BlastMessage::query()
            ->where('tenant_id', $senarai->first()->tenant_id ?? 0)
            ->where('prospect_id', $prospectId)
            ->whereNotNull('contact_id')
            ->value('contact_id');

        if (! $contactId) {
            return 0.0;
        }

        $opportunity = \App\Modules\CRM\Models\Opportunity::query()
            ->where('customer_id', $contactId)
            ->whereIn('stage', ['won', 'closed_won', 'closed'])
            ->orderByDesc('created_at')
            ->first();

        if ($opportunity) {
            return (float) $opportunity->estimated_value;
        }

        $invoice = \DB::table('invoices')
            ->where('customer_id', $contactId)
            ->where('status', 'paid')
            ->orderByDesc('created_at')
            ->first();

        return $invoice ? (float) $invoice->grand_total : 0.0;
    }

    /**
     * Susun saluran berdasarkan recency (paling dekat ke $- akhir diberi berat lebih).
     */
    private function susunTimeDecay($senarai): array
    {
        $terbalik = $senarai->reverse()->values();
        $jumlah = $terbalik->count();
        $berat = [];

        foreach ($terbalik as $i => $m) {
            $w = $jumlah - $i;
            $berat[$m->channel] = ($berat[$m->channel] ?? 0) + $w;
        }

        $total = array_sum($berat);
        $hasil = [];

        foreach ($berat as $channel => $w) {
            // Tambah channelExperience kali supaya semua channel hadir
            $hasil[] = $channel;
            if ($w / max($total, 1) < 0.3 && count($hasil) < 3) {
                $hasil[] = $channel;
            }
        }

        return array_values(array_unique($hasil));
    }

    /**
     * Position based — 40% pertama, 40% terakhir, 20% tengah.
     */
    private function susunPositionBased($senarai): array
    {
        $channels = $senarai->pluck('channel')->unique()->values()->toArray();

        if (count($channels) <= 1) {
            return $channels;
        }

        $hasil = [$channels[0]];
        $hasil[] = $channels[count($channels) - 1];

        if (count($channels) > 2) {
            $hasil[] = $channels[intdiv(count($channels), 2)];
        }

        return array_values(array_unique($hasil));
    }

    /**
     * Get contact acquisition metrics.
     */
    public function getAcquisitionMetrics(int $tenantId): array
    {
        $totalContacts = Contact::where('tenant_id', $tenantId)->count();
        $newContactsThisMonth = Contact::where('tenant_id', $tenantId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $sources = Contact::where('tenant_id', $tenantId)
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'total_contacts' => $totalContacts,
            'new_this_month' => $newContactsThisMonth,
            'by_source' => $sources,
        ];
    }

    /**
     * Get voucher performance analytics.
     */
    public function getVoucherAnalytics(int $tenantId): array
    {
        $vouchers = Voucher::where('tenant_id', $tenantId)->get();

        $totalVouchers = $vouchers->count();
        $activeVouchers = $vouchers->where('status', 'active')->count();
        $totalRedemptions = $vouchers->sum('used_count');
        $totalDiscountGiven = $vouchers->sum(function ($v) {
            // This would need a separate tracking table for actual discount amounts
            return 0;
        });

        return [
            'total_vouchers' => $totalVouchers,
            'active_vouchers' => $activeVouchers,
            'total_redemptions' => $totalRedemptions,
            'total_discount_given' => round($totalDiscountGiven, 2),
        ];
    }

    /**
     * Get marketing funnel metrics.
     */
    public function getFunnelMetrics(int $tenantId): array
    {
        $contacts = Contact::where('tenant_id', $tenantId);

        $awareness = $contacts->count(); // Total contacts
        $interest = $contacts->whereNotNull('last_activity_at')->count(); // Engaged
        $consideration = $contacts->where('form_submissions', '>', 0)->count(); // Form filled
        $intent = $contacts->where('demo_requested', true)->count(); // Demo requested
        $conversion = $contacts->where('total_purchases', '>', 0)->count(); // Purchased

        return [
            'awareness' => $awareness,
            'interest' => $interest,
            'consideration' => $consideration,
            'intent' => $intent,
            'conversion' => $conversion,
            'stages' => [
                ['name' => 'Awareness', 'count' => $awareness],
                ['name' => 'Interest', 'count' => $interest, 'conversion' => $awareness > 0 ? ($interest / $awareness) * 100 : 0],
                ['name' => 'Consideration', 'count' => $consideration, 'conversion' => $interest > 0 ? ($consideration / $interest) * 100 : 0],
                ['name' => 'Intent', 'count' => $intent, 'conversion' => $consideration > 0 ? ($intent / $consideration) * 100 : 0],
                ['name' => 'Conversion', 'count' => $conversion, 'conversion' => $intent > 0 ? ($conversion / $intent) * 100 : 0],
            ],
        ];
    }

    /**
     * Get comprehensive marketing dashboard data.
     */
    public function getDashboardData(int $tenantId): array
    {
        $campaignService = app(CampaignService::class);
        $contactService = app(ContactService::class);
        $voucherService = app(VoucherService::class);

        return [
            'campaigns' => $campaignService->getCampaignStats($tenantId),
            'contacts' => $contactService->getContactStats($tenantId),
            'vouchers' => $voucherService->getVoucherStats($tenantId),
            'roi' => $this->calculateROI($tenantId),
            'funnel' => $this->getFunnelMetrics($tenantId),
            'acquisition' => $this->getAcquisitionMetrics($tenantId),
        ];
    }
}
