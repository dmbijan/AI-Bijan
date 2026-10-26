<?php

namespace App\Modules\AgentStudio\Services;

use App\Modules\AgentStudio\Models\AgentBlueprint;
use App\Modules\AgentStudio\Models\AgentKnowledgeBinding;
use App\Modules\AgentStudio\Models\AgentRun;
use App\Modules\AgentStudio\Models\AgentTestRun;
use App\Modules\AgentStudio\Models\AgentToolBinding;
use Illuminate\Support\Facades\Log;

/**
 * AgentBlueprintService —  ejen AI tanpa kod (Zoho Agent Store model).
 *
 * IDEA #8 (BAB 84): Zoho Agent Store ada 16 ejen pra-bina yang popular.
 * AI BIJAN perluallow tenant membina ejen sendiri: persona + alat +
 * pengetahuan + otonomi + ujian, kemudian publish.
 *
 * Corak disalin: "templat -> sesuaikan -> ujian -> publish".
 */
class AgentBlueprintService
{
    /**
     * Tahap otonomi.
     */
    public const AUTONOMI_MANUAL = 0;        // semua tindakan perlu kelulusan
    public const AUTONOMI_CADANGAN = 1;      // ejen cadang, manusia pilih
    public const AUTONOMI_SEPARA = 2;       // ejen laksana, escalate jika perlu
    public const AUTONOMI_PENUH = 3;        // ejen laksana sepenuhnya

    /**
     * 16 templat ejen — mengambil inspiration daripada Zoho Agent Store
     * (SalesIQ, Zia, SDesk) + kes guna MY.
     */
    public const TEMPLAT_EJEN = [
        'sales_agent' => [
            'name' => 'Ejen Jualan AI',
            'category' => 'sales',
            'description' => 'Saring prospek, hantar mesej susulan, jadual demo dan hantar ringkasan rallied.',
            'persona' => 'Anda pembantu jualan yang Obligations, ringkas dan berorientasikan hasil.',
            'capabilities' => ['prospect_qualification', 'follow_up_email', 'meeting_scheduling', 'deal_brief'],
            'tools' => ['prospecting.list', 'prospecting.show', 'blast.send', 'booking.create', 'sales_copilot.brief'],
            'knowledge' => [['type' => 'document', 'ref' => 'sales_playbook']],
            'autonomy' => ['level' => self::AUTONOMI_CADANGAN, 'escalate_on' => ['discount_above', 'complaint']],
        ],
        'support_agent' => [
            'name' => 'Ejen Sokongan AI',
            'category' => 'support',
            'description' => 'Jawab soalan pelanggan, semak status pesanan, escalate kes rumit.',
            'persona' => 'Anda ejen sokongan yang sabar, tepat dan tidak pernah meneka.',
            'capabilities' => ['faq_answering', 'ticket_triage', 'order_status', 'escalation'],
            'tools' => ['helpdesk.list', 'helpdesk.show', 'helpdesk.create', 'cod.show', 'marketplace.list'],
            'knowledge' => [['type' => 'faq', 'ref' => 'faq_bank']],
            'autonomy' => ['level' => self::AUTONOMI_SEPARA, 'escalate_on' => ['legal_threat', 'refund_request']],
        ],
        'booking_agent' => [
            'name' => 'Ejen Tempahan AI',
            'category' => 'ops',
            'description' => 'Urus tempahan, hantar pengesahan, ingatkan pelanggan.',
            'persona' => 'Anda pembantu tempahan yang mesra dan cekap.',
            'capabilities' => ['booking_management', 'reminder', 'confirmation_sms'],
            'tools' => ['booking.list', 'booking.create', 'booking.show', 'blast.send'],
            'knowledge' => [],
            'autonomy' => ['level' => self::AUTONOMI_SEPARA, 'escalate_on' => ['group_booking']],
        ],
        'invoice_agent' => [
            'name' => 'Ejen Invois AI',
            'category' => 'finance',
            'description' => 'Sahkan invois, ingatkan bayaran, rekod kutipan.',
            'persona' => 'Anda ejen kewangan yang teliti dan berperaka tinggi.',
            'capabilities' => ['invoice_verification', 'payment_reminder', 'collection_tracking'],
            'tools' => ['invoicing.list', 'invoicing.show', 'finance.list', 'blast.send'],
            'knowledge' => [['type' => 'document', 'ref' => 'payment_terms']],
            'autonomy' => ['level' => self::AUTONOMI_CADANGAN, 'escalate_on' => ['write_off']],
        ],
        'logistics_agent' => [
            'name' => 'Ejen Logistik AI',
            'category' => 'ops',
            'description' => 'Jejak penghantaran, hubungi kurier, kemas kini pesanan.',
            'persona' => 'Anda ejen logistik yang pantas dan berorientasikan status.',
            'capabilities' => ['tracking', 'courier_coordination', 'delivery_update'],
            'tools' => ['logistics.list', 'logistics.show', 'cod.show', 'fleet.list'],
            'knowledge' => [],
            'autonomy' => ['level' => self::AUTONOMI_SEPARA, 'escalate_on' => ['delivery_failed']],
        ],
        'cod_agent' => [
            'name' => 'Ejen COD AI',
            'category' => 'finance',
            'description' => 'Semak kelayakan COD, sahkan alamat, kejar kutipan tertunggak.',
            'persona' => 'Anda ejen kutipan COD yang tegas tetapi sopan.',
            'capabilities' => ['cod_eligibility', 'address_verification', 'collection_followup'],
            'tools' => ['cod.check', 'cod.check-address', 'cod.orders', 'cod.collections'],
            'knowledge' => [['type' => 'document', 'ref' => 'cod_policy']],
            'autonomy' => ['level' => self::AUTONOMI_SEPARA, 'escalate_on' => ['high_value_order']],
        ],
        'marketing_agent' => [
            'name' => 'Ejen Pemasaran AI',
            'category' => 'growth',
            'description' => 'Rancang kempen, tulis salinan, analisis prestasi.',
            'persona' => 'Anda ejen pemasaran yang kreatif dan berpandukan data.',
            'capabilities' => ['campaign_planning', 'copywriting', 'performance_analysis'],
            'tools' => ['marketing.list', 'blast.campaigns', 'analytics.run', 'blast.stats'],
            'knowledge' => [['type' => 'document', 'ref' => 'brand_guidelines']],
            'autonomy' => ['level' => self::AUTONOMI_CADANGAN, 'escalate_on' => ['paid_budget']],
        ],
        'hr_agent' => [
            'name' => 'Ejen Sumber Manusia AI',
            'category' => 'hr',
            'description' => 'Jawab soalanopaedia calon, saring resume, jadual temu bual.',
            'persona' => 'Anda ejen HR yang ramah dan tidak diskriminator.',
            'capabilities' => ['candidate_qa', 'resume_screening', 'interview_scheduling'],
            'tools' => ['hrms.list', 'members.list', 'booking.create'],
            'knowledge' => [['type' => 'document', 'ref' => 'job_descriptions']],
            'autonomy' => ['level' => self::AUTONOMI_CADANGAN, 'escalate_on' => ['offer_decision']],
        ],
        'inventory_agent' => [
            'name' => 'Ejen Stok AI',
            'category' => 'ops',
            'description' => 'Per Watching stok, ramalkan permintaan, cadang pesanan replenish.',
            'persona' => 'Anda ejen inventori yang alert.',
            'capabilities' => ['stock_monitoring', 'demand_forecast', 'reorder_suggestion'],
            'tools' => ['inventory.list', 'inventory.count', 'analytics.run'],
            'knowledge' => [],
            'autonomy' => ['level' => self::AUTONOMI_CADANGAN, 'escalate_on' => ['po_creation']],
        ],
        'content_agent' => [
            'name' => 'Ejen Kandungan AI',
            'category' => 'growth',
            'description' => 'Hasilkan kandungan sosial, ringkaskan dokumen, terjemahan.',
            'persona' => 'Anda ejen kandungan yang mampu menyesuaikan gaya.',
            'capabilities' => ['social_post', 'summarization', 'translation', 'seo'],
            'tools' => ['documents.list', 'media.generate-video', 'blast.preview'],
            'knowledge' => [['type' => 'document', 'ref' => 'brand_guidelines']],
            'autonomy' => ['level' => self::AUTONOMI_PENUH, 'escalate_on' => []],
        ],
        'onboarding_agent' => [
            'name' => 'Ejen Onboarding AI',
            'category' => 'ops',
            'description' => 'Mulakan pelanggan baharu, hantar checklist, kumpul maklumat.',
            'persona' => 'Anda ejen onboarding yang mesra dan tersusun.',
            'capabilities' => ['onboarding_checklist', 'document_collection', 'welcome_email'],
            'tools' => ['crm.list', 'crm.show', 'documents.list', 'blast.send'],
            'knowledge' => [['type' => 'document', 'ref' => 'onboarding_checklist']],
            'autonomy' => ['level' => self::AUTONOMI_SEPARA, 'escalate_on' => ['setup_exception']],
        ],
        'compliance_agent' => [
            'name' => 'Ejen Pematuhan AI',
            'category' => 'finance',
            'description' => 'Semak pematuhan PDPA, GST/SST, halal, dokumen eksport.',
            'persona' => 'Anda ejen pematuhan yang teliti dan neutral.',
            'capabilities' => ['pdpa_check', 'tax_verification', 'document_audit'],
            'tools' => ['documents.list', 'documents.show', 'finance.list'],
            'knowledge' => [['type' => 'document', 'ref' => 'compliance_playbook']],
            'autonomy' => ['level' => self::AUTONOMI_MANUAL, 'escalate_on' => ['any_violation']],
        ],
        'finance_agent' => [
            'name' => 'Ejen Kewangan AI',
            'category' => 'finance',
            'description' => 'Rekod transaksi, saman bank, laporan aliran tunai, unjuran cashflow.',
            'persona' => 'Anda ejen kewangan yang teliti dan berdisiplin.',
            'capabilities' => ['transaction_categorisation', 'bank_reconciliation', 'cashflow_forecast'],
            'tools' => ['finance.list', 'finance.create', 'analytics.run'],
            'knowledge' => [['type' => 'document', 'ref' => 'chart_of_accounts']],
            'autonomy' => ['level' => self::AUTONOMI_CADANGAN, 'escalate_on' => ['reconciliation_gap']],
        ],
        'recruitment_agent' => [
            'name' => 'Ejen Pengambilan AI',
            'category' => 'hr',
            'description' => 'Sumber calon dari job board, saring, jadual temu bual.',
            'persona' => 'Anda ejen pengambilan yang adil dan cekap.',
            'capabilities' => ['candidate_sourcing', 'screening', 'scheduling'],
            'tools' => ['hrms.list', 'booking.create', 'blast.send'],
            'knowledge' => [['type' => 'document', 'ref' => 'hiring_process']],
            'autonomy' => ['level' => self::AUTONOMI_CADANGAN, 'escalate_on' => ['final_selection']],
        ],
        'field_agent' => [
            'name' => 'Ejen Medan AI',
            'category' => 'ops',
            'description' => 'Panduan kerja medan, semak laporan lawatan, GPS armada.',
            'persona' => 'Anda ejen medan yang ter organized dan praktikal.',
            'capabilities' => ['route_guidance', 'visit_report', 'fleet_check'],
            'tools' => ['fleet.list', 'fleet.map', 'logistics.list'],
            'knowledge' => [['type' => 'document', 'ref' => 'sop_lapangan']],
            'autonomy' => ['level' => self::AUTONOMI_SEPARA, 'escalate_on' => ['accident', 'breakdown']],
        ],
        'loyalty_agent' => [
            'name' => 'Ejen Kesetiaan AI',
            'category' => 'growth',
            'description' => 'Ubah suai tawaran, urus-member, jalankan kempen points.',
            'persona' => 'Anda ejen kesetiaan yang touch.',
            'capabilities' => ['tier_management', 'campaign_run', 'churn_save'],
            'tools' => ['loyalty.list', 'loyalty.show', 'blast.campaigns', 'marketing.list'],
            'knowledge' => [['type' => 'document', 'ref' => 'loyalty_tiers']],
            'autonomy' => ['level' => self::AUTONOMI_CADANGAN, 'escalate_on' => ['vip_complaint']],
        ],
    ];

    /**
     * Jana ejen daripada templat.
     */
    public function janaDaripadaTemplat(int $tenantId, string $templateKey, ?string $nama = null): AgentBlueprint
    {
        if (! isset(self::TEMPLAT_EJEN[$templateKey])) {
            throw new \InvalidArgumentException("Templat ejen '{$templateKey}' tidak wujud");
        }

        $templat = self::TEMPLAT_EJEN[$templateKey];
        $key = $templateKey . '_' . substr(md5($tenantId . $templateKey . now()->timestamp), 0, 6);

        $agent = AgentBlueprint::create([
            'tenant_id' => $tenantId,
            'key' => $key,
            'name' => $nama ?? $templat['name'],
            'slug' => \Illuminate\Support\Str::slug($nama ?? $templat['name']),
            'description' => $templat['description'],
            'category' => $templat['category'],
            'persona' => $templat['persona'],
            'system_prompt' => $this-> binaSystemPrompt($templat),
            'capabilities' => $templat['capabilities'],
            'knowledge_sources' => $templat['knowledge'],
            'autonomy' => $templat['autonomy'],
            'escalation_rules' => ['triggers' => $templat['autonomy']['escalate_on'] ?? []],
            'status' => AgentBlueprint::STATUS_DRAFT,
            'is_published' => false,
            'created_by' => auth()->id(),
        ]);

        foreach ($templat['tools'] as $toolKey) {
            AgentToolBinding::create([
                'tenant_id' => $tenantId,
                'agent_blueprint_id' => $agent->id,
                'tool_key' => $toolKey,
                'tool_type' => str_starts_with($toolKey, 'mcp.') ? 'mcp' : 'internal',
                'permission_level' => str_contains($toolKey, '.create') || str_contains($toolKey, '.send')
                    ? AgentToolBinding::PERMISSION_WRITE
                    : AgentToolBinding::PERMISSION_READ,
                'is_enabled' => true,
            ]);
        }

        foreach ($templat['knowledge'] as $k) {
            AgentKnowledgeBinding::create([
                'tenant_id' => $tenantId,
                'agent_blueprint_id' => $agent->id,
                'source_type' => $k['type'],
                'source_ref' => $k['ref'],
                'title' => $k['ref'],
                'weight' => 1.00,
                'is_enabled' => true,
            ]);
        }

        Log::info('Agent Studio: ejen dijana daripada templat', [
            'tenant_id' => $tenantId,
            'template' => $templateKey,
            'agent_id' => $agent->id,
        ]);

        return $agent;
    }

    /**
     * Bina system prompt daripada templat.
     */
    private function binaSystemPrompt(array $templat): string
    {
        $baris = [
            'Anda adalah ' . strtolower($templat['name']) . ' dalam platform AI BIJAN.',
            $templat['persona'],
            '',
            'Kemahiran: ' . implode(', ', $templat['capabilities']),
        ];

        if (! empty($templat['autonomy']['escalate_on'])) {
            $baris[] = '';
            $baris[] = 'Esekskalasi kepada manusia apabila: ' . implode(', ', $templat['autonomy']['escalate_on']) . '.';
        }

        $baris[] = '';
        $baris[] = 'Guna Bahasa Malaysia atau Bahasa Inggeris mengikut bahasa pengguna. Sentiasa rujuk data sebenar sistem sebelum menjawab.';

        return implode("\n", $baris);
    }

    /**
     * Senarai templat tersedia.
     */
    public function senaraiTemplat(): array
    {
        $hasil = [];

        foreach (self::TEMPLAT_EJEN as $key => $t) {
            $hasil[] = [
                'key' => $key,
                'name' => $t['name'],
                'category' => $t['category'],
                'description' => $t['description'],
                'capabilities' => $t['capabilities'],
                'tool_count' => count($t['tools']),
            ];
        }

        return $hasil;
    }

    /**
     * Ikat alat pada ejen.
     */
    public function ikatAlat(int $tenantId, int $agentId, string $toolKey, string $toolType = 'internal', string $permission = 'read', array $config = []): AgentToolBinding
    {
        $binding = AgentToolBinding::updateOrCreate(
            ['tenant_id' => $tenantId, 'agent_blueprint_id' => $agentId, 'tool_key' => $toolKey],
            [
                'tool_type' => $toolType,
                'tool_config' => $config ?: null,
                'permission_level' => $permission,
                'is_enabled' => true,
            ]
        );

        return $binding;
    }

    /**
     * Ikat sumber pengetahuan pada ejen.
     */
    public function ikatPengetahuan(int $tenantId, int $agentId, string $sourceType, string $sourceRef, ?string $title = null, float $weight = 1.0): AgentKnowledgeBinding
    {
        return AgentKnowledgeBinding::updateOrCreate(
            ['tenant_id' => $tenantId, 'agent_blueprint_id' => $agentId, 'source_type' => $sourceType, 'source_ref' => $sourceRef],
            [
                'title' => $title ?? $sourceRef,
                'weight' => $weight,
                'is_enabled' => true,
            ]
        );
    }

    /**
     * Jalankan ejen (dengan rekod).
     */
    public function jalankan(int $tenantId, int $agentId, array $input = [], string $triggerType = 'manual'): AgentRun
    {
        $agent = AgentBlueprint::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($agentId);

        $mula = microtime(true);

        $run = AgentRun::create([
            'tenant_id' => $tenantId,
            'agent_blueprint_id' => $agent->id,
            'agent_key' => $agent->key,
            'trigger_type' => $triggerType,
            'input' => $input ?: null,
            'status' => AgentRun::STATUS_RUNNING,
        ]);

        try {
            $hasil = $this->lancarkanEjen($agent, $input);

            $run->update([
                'output' => $hasil['output'],
                'status' => $hasil['escalated'] ? AgentRun::STATUS_ESCALATED : AgentRun::STATUS_COMPLETED,
                'escalated' => $hasil['escalated'],
                'tools_used' => $hasil['tools'],
                'duration_ms' => (int) round((microtime(true) - $mula) * 1000),
                'cost_credits' => $hasil['credits'],
            ]);

            $agent->increment('run_count');
            $jumlah = $agent->run_count;
            $berjaya = $agent->runs()->where('status', AgentRun::STATUS_COMPLETED)->count();
            $agent->update(['success_rate' => $jumlah > 0 ? round(($berjaya / $jumlah) * 100, 2) : 0]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => AgentRun::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'duration_ms' => (int) round((microtime(true) - $mula) * 1000),
            ]);

            Log::error('Agent Studio: ejen gagal', [
                'tenant_id' => $tenantId,
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);
        }

        return $run->fresh();
    }

    /**
     * Pelaksanaan ejen sebenar —Uses AI Gateway.
     */
    private function lancarkanEjen(AgentBlueprint $agent, array $input): array
    {
        $prompt = $input['prompt'] ?? $input['message'] ?? '';

        if (trim((string) $prompt) === '') {
            throw new \InvalidArgumentException("Prompt diperlukan untuk menjalankan ejen");
        }

        $tools = $agent->tools()->where('is_enabled', true)->pluck('tool_key')->toArray();

        // Semak peraturan eskalasi
        $escalated = $this->perluEskalasi($agent, $input);

        // Panggil AI Gateway jika tersedia
        $output = '';
        $credits = 0;

        try {
            $gateway = app(\App\Modules\AIGateway\Services\GatewayService::class);

            $messages = [];
            if (! empty($agent->system_prompt)) {
                $messages[] = ['role' => 'system', 'content' => $agent->system_prompt];
            }
            $messages[] = ['role' => 'user', 'content' => (string) $prompt];

            $resultat = $gateway->call(
                $agent->tenant_id,
                $messages,
                'agent_studio',
                [
                    'model' => $agent->model === 'default' ? null : $agent->model,
                    'temperature' => (float) $agent->temperature,
                    'max_output_tokens' => (int) $agent->max_tokens,
                    'source_module' => 'agent_studio',
                    'agent_key' => $agent->key,
                ]
            );

            $output = $resultat['content'] ?? '';

            // Mod simulasi gateway tidak memahami konteks — gabungkan dengan
            // konteks ejen supaya output tetap berguna untuk ujian & pratonton.
            if (empty($output) || str_starts_with((string) $output, '[SIMULASI]')) {
                $output .= "\n" . $this->jawabDeterministik($agent, (string) $prompt);
            }

            $credits = (int) ($resultat['credits'] ?? 0);
        } catch (\Throwable $e) {
            // Gateway tidak tersedia — gunakan mod deterministik untuk ujian
            $output = "[Ejen {$agent->name}] " . $this->jawabDeterministik($agent, (string) $prompt);
            Log::warning('Agent Studio: AI Gateway tidak tersedia, guna mod fallback', [
                'agent_id' => $agent->id,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'output' => $output,
            'escalated' => $escalated,
            'tools' => $tools,
            'credits' => $credits,
        ];
    }

    /**
     * Semak peraturan eskalasi.
     */
    private function perluEskalasi(AgentBlueprint $agent, array $input): bool
    {
        $triggers = $agent->escalation_rules['triggers'] ?? [];

        if (empty($triggers)) {
            return false;
        }

        foreach ($triggers as $trigger) {
            if (isset($input['trigger']) && $input['trigger'] === $trigger) {
                return true;
            }
        }

        // Otonomi manual sentiasa eskalasi tindakan menulis
        if ($agent->perluKelulusan() && ! empty($input['requires_write'])) {
            return true;
        }

        return false;
    }

    /**
     * Jawapan deterministik (fallback tanpa AI Gateway).
     */
    private function jawabDeterministik(AgentBlueprint $agent, string $prompt): string
    {
        $baris = [
            'Ejen: ' . $agent->name,
            'Kategori: ' . $agent->category,
            'Kemahiran: ' . implode(', ', $agent->capabilities ?? []),
            'Tahap otonomi: ' . $agent->tahapOtonomi(),
        ];

        $baris[] = 'Input diterima: ' . mb_substr($prompt, 0, 200);
        $baris[] = 'Nota: AI Gateway tidak tersedia, jawapan terhad kepada metadata ejen.';

        return implode("\n", $baris);
    }

    /**
     * Jalankan ujian ejen.
     */
    public function jalankanUjian(int $tenantId, int $agentId, string $name, ?string $prompt = null, ?string $expected = null): AgentTestRun
    {
        $agent = AgentBlueprint::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($agentId);

        $testRun = AgentTestRun::create([
            'tenant_id' => $tenantId,
            'agent_blueprint_id' => $agent->id,
            'name' => $name,
            'prompt' => $prompt,
            'expected_output' => $expected,
            'status' => AgentTestRun::STATUS_RUNNING,
        ]);

        $mula = microtime(true);

        try {
            $hasil = $this->lancarkanEjen($agent, ['prompt' => $prompt ?? $name]);
            $actual = $hasil['output'];

            $skor = $expected !== null && $expected !== ''
                ? $this->skorKesamaan($actual, $expected)
                : null;

            $testRun->update([
                'actual_output' => $actual,
                'score' => $skor,
                'passed' => $skor !== null ? $skor >= 70 : true,
                'status' => ($skor !== null && $skor >= 70) ? AgentTestRun::STATUS_PASSED : AgentTestRun::STATUS_FAILED,
                'duration_ms' => (int) round((microtime(true) - $mula) * 1000),
                'cost_credits' => $hasil['credits'],
                'metrics' => ['escalated' => $hasil['escalated'], 'tools' => count($hasil['tools'])],
            ]);
        } catch (\Throwable $e) {
            $testRun->update([
                'status' => AgentTestRun::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'duration_ms' => (int) round((microtime(true) - $mula) * 1000),
            ]);
        }

        return $testRun->fresh();
    }

    /**
     * Skor kesamaan mudah (perkataan sama + substring).
     */
    private function skorKesamaan(string $actual, string $expected): float
    {
        $actualNorm = mb_strtolower(trim($actual));
        $expectedNorm = mb_strtolower(trim($expected));

        if ($actualNorm === $expectedNorm) {
            return 100.0;
        }

        if (str_contains($actualNorm, $expectedNorm) || str_contains($expectedNorm, $actualNorm)) {
            return 85.0;
        }

        $kataA = preg_split('/\s+/', $actualNorm, -1, PREG_SPLIT_NO_EMPTY);
        $kataB = preg_split('/\s+/', $expectedNorm, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($kataA) || empty($kataB)) {
            return 0.0;
        }

        $sepilih = array_intersect($kataA, $kataB);
        $dice = (2 * count($sepilih)) / (count($kataA) + count($kataB));

        return round($dice * 100, 2);
    }

    /**
     * Publish ejen.
     */
    public function publish(int $tenantId, int $agentId): AgentBlueprint
    {
        $agent = AgentBlueprint::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($agentId);

        // Sahkan ada sekurang-kurangnya satu alat & ujian lulus
        if ($agent->tools()->where('is_enabled', true)->count() === 0) {
            throw new \RuntimeException('Ejen mestilah mempunyai sekurang-kurangnya satu alat');
        }

        $lulus = $agent->testRuns()->where('status', AgentTestRun::STATUS_PASSED)->count();
        if ($lulus === 0) {
            throw new \RuntimeException('Ejen mestilah mempunyai sekurang-kurangnya satu ujian yang lulus');
        }

        $agent->update([
            'status' => AgentBlueprint::STATUS_ACTIVE,
            'is_published' => true,
        ]);

        Log::info('Agent Studio: ejen diterbitkan', ['tenant_id' => $tenantId, 'agent_id' => $agentId]);

        return $agent->fresh();
    }

    /**
     * Statistik ejen.
     */
    public function statistik(int $tenantId): array
    {
        $jumlah = AgentBlueprint::query()->where('tenant_id', $tenantId)->count();
        $aktif = AgentBlueprint::query()->where('tenant_id', $tenantId)->where('status', AgentBlueprint::STATUS_ACTIVE)->count();
        $published = AgentBlueprint::query()->where('tenant_id', $tenantId)->where('is_published', true)->count();

        $run = AgentRun::query()->where('tenant_id', $tenantId)->count();
        $escalated = AgentRun::query()->where('tenant_id', $tenantId)->where('escalated', true)->count();

        $tests = AgentTestRun::query()->where('tenant_id', $tenantId)->count();
        $passed = AgentTestRun::query()->where('tenant_id', $tenantId)->where('passed', true)->count();

        return [
            'jumlah_ejen' => $jumlah,
            'aktif' => $aktif,
            'dipublish' => $published,
            'jumlah_run' => $run,
            'escalated' => $escalated,
            'kadar_eskalasi' => $run > 0 ? round(($escalated / $run) * 100, 1) : 0.0,
            'jumlah_ujian' => $tests,
            'ujian_lulus' => $passed,
            'kadar_ujian_lulus' => $tests > 0 ? round(($passed / $tests) * 100, 1) : 0.0,
            'templat_tersedia' => count(self::TEMPLAT_EJEN),
        ];
    }
}
