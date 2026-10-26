<?php

namespace App\Modules\PersonalAssistant\Services;

use Illuminate\Support\Facades\DB;

/**
 * Proactive Alerts (BAB 17.6)
 *
 * Setiap alert mesti menunjukkan 7 perkara:
 * 1. Apa yang berlaku
 * 2. Data/rekod asas
 * 3. Tahap kepastian
 * 4. Kesan mungkin
 * 5. Tindakan dicadangkan
 * 6. Sama ada boleh automatik
 * 7. Cara meluluskan/menolak/membetulkan
 */
class ProactiveAlertService
{
    /**
     * Senarai jenis alert yang tersedia
     */
    public function getAlertTypes(): array
    {
        return config('personal_assistant.proactive_alerts', []);
    }

    /**
     * Jana semua alert aktif untuk tenant
     */
    public function generateForTenant(int $tenantId): array
    {
        $alerts = [];

        $alerts = array_merge($alerts, $this->checkOverdueInvoices($tenantId));
        $alerts = array_merge($alerts, $this->checkStaleLeads($tenantId));
        $alerts = array_merge($alerts, $this->checkAiUsageLimit($tenantId));

        return $alerts;
    }

    /**
     * Invois melebihi tarikh matang
     */
    public function checkOverdueInvoices(int $tenantId): array
    {
        if (!$this->tableExists('invoices')) {
            return [];
        }

        $overdue = DB::table('invoices')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['issued', 'sent', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->select('id', 'invoice_number', 'customer_id', 'total_amount', 'due_date')
            ->limit(20)
            ->get();

        return $overdue->map(function ($inv) {
            $daysOverdue = now()->diffInDays($inv->due_date);

            return [
                'type' => 'invoice_overdue',
                'title' => "Invois {$inv->invoice_number} lewat {$daysOverdue} hari",
                'what_happened' => 'Invois belum dibayar selepas tarikh matang.',
                'data_reference' => ['invoice_id' => $inv->id],
                'confidence' => 'high',
                'potential_impact' => 'Aliran tunai terjejas; risiko hutang lapuk.',
                'suggested_action' => 'Hantar peringatan pembayaran.',
                'can_automate' => true,
                'actions' => ['approve' => 'Hantar peringatan', 'reject' => 'Abaikan', 'correct' => 'Semak invois'],
                'priority' => 'high',
            ];
        })->toArray();
    }

    /**
     * Lead tidak dihubungi dalam tempoh ditetapkan
     */
    public function checkStaleLeads(int $tenantId): array
    {
        if (!$this->tableExists('crm_leads')) {
            return [];
        }

        $threshold = config('personal_assistant.proactive_alerts.lead_not_contacted.default_threshold_days', 3);
        $cutoff = now()->subDays($threshold);

        $stale = DB::table('crm_leads')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['new', 'contacted'])
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_contacted_at')->orWhere('last_contacted_at', '<', $cutoff);
            })
            ->select('id', 'name', 'company', 'created_at', 'last_contacted_at')
            ->limit(20)
            ->get();

        return $stale->map(fn ($lead) => [
            'type' => 'lead_not_contacted',
            'title' => "Lead '{$lead->name}' belum dihubungi",
            'what_happened' => "Lead tidak dihubungi lebih {$threshold} hari.",
            'data_reference' => ['lead_id' => $lead->id],
            'confidence' => 'high',
            'potential_impact' => 'Peluang jualan mungkin hilang kepada pesaing.',
            'suggested_action' => 'Hubungi lead dan rekod hasil.',
            'can_automate' => false,
            'actions' => ['approve' => 'Cipta tugasan follow-up', 'reject' => 'Abaikan', 'correct' => 'Kemas kini status'],
            'priority' => 'medium',
        ])->toArray();
    }

    /**
     * Penggunaan AI hampir had
     */
    public function checkAiUsageLimit(int $tenantId): array
    {
        if (!$this->tableExists('ai_credit_wallets')) {
            return [];
        }

        $warningThreshold = config('personal_assistant.proactive_alerts.ai_usage_limit.default_threshold_percent', 80);

        $wallet = DB::table('ai_credit_wallets')
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$wallet) {
            return [];
        }

        $used = (float) ($wallet->used_this_period ?? 0);
        $quota = (float) ($wallet->period_quota ?? 0);

        if ($quota <= 0) {
            return [];
        }

        $percent = ($used / $quota) * 100;

        if ($percent < $warningThreshold) {
            return [];
        }

        return [[
            'type' => 'ai_usage_limit',
            'title' => 'Penggunaan AI hampir had (' . round($percent) . '%)',
            'what_happened' => 'Penggunaan kredit AI melebihi ' . round($percent) . '% kuota.',
            'data_reference' => ['wallet_id' => $wallet->id ?? null],
            'confidence' => 'high',
            'potential_impact' => 'AI mungkin terhenti jika kuota habis.',
            'suggested_action' => 'Beli top-up atau tingkatkan pelan.',
            'can_automate' => true,
            'actions' => ['approve' => 'Beli top-up', 'reject' => 'Abaikan', 'correct' => 'Semak penggunaan'],
            'priority' => 'medium',
        ]];
    }

    /**
     * Semak kewujudan jadual secara selamat
     */
    protected function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }
}
