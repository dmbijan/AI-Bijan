<?php

namespace Tests\Feature;

use App\Filament\Resources\AiModels\AiModelResource;
use App\Filament\Resources\AiUsageEvents\AiUsageEventResource;
use App\Filament\Resources\AlertRules\AlertRuleResource;
use App\Filament\Resources\BillingSubscriptions\BillingSubscriptionResource;
use App\Filament\Resources\Boms\BomResource;
use App\Filament\Resources\BrainForecasts\ForecastResource;
use App\Filament\Resources\BrainInsights\InsightResource;
use App\Filament\Resources\BrainRecommendations\RecommendationResource;
use App\Filament\Resources\BusinessIdeas\BusinessIdeaResource;
use App\Filament\Resources\CommunityPosts\CommunityPostResource;
use App\Filament\Resources\CommunitySpaces\CommunitySpaceResource;
use App\Filament\Resources\DigitalCards\DigitalCardResource;
use App\Filament\Resources\Documents\DocumentResource;
use App\Filament\Resources\EntitlementGrants\EntitlementGrantResource;
use App\Filament\Resources\ExecutiveBriefings\ExecutiveBriefingResource;
use App\Filament\Resources\FlowDefinitions\FlowDefinitionResource;
use App\Filament\Resources\Items\ItemResource;
use App\Filament\Resources\LinkPages\LinkPageResource;
use App\Filament\Resources\LiveAvatars\LiveAvatarResource;
use App\Filament\Resources\LiveConsentRecords\LiveConsentRecordResource;
use App\Filament\Resources\LiveControls\LiveControlResource;
use App\Filament\Resources\LiveHostPersonalities\LiveHostPersonalityResource;
use App\Filament\Resources\LiveMessages\LiveMessageResource;
use App\Filament\Resources\LiveMetrics\LiveMetricResource;
use App\Filament\Resources\LiveModerationEvents\LiveModerationEventResource;
use App\Filament\Resources\LiveRenderJobs\LiveRenderJobResource;
use App\Filament\Resources\LiveSessions\LiveSessionResource;
use App\Filament\Resources\LiveStreamDestinations\LiveStreamDestinationResource;
use App\Filament\Resources\LiveSupportCalls\LiveSupportCallResource;
use App\Filament\Resources\LiveSupportChannels\LiveSupportChannelResource;
use App\Filament\Resources\McpAuditLogs\McpAuditLogResource;
use App\Filament\Resources\McpExecutions\McpExecutionResource;
use App\Filament\Resources\McpPrompts\McpPromptResource;
use App\Filament\Resources\McpResources\McpResourceResource;
use App\Filament\Resources\McpSessions\McpSessionResource;
use App\Filament\Resources\McpTools\McpToolResource;
use App\Filament\Resources\MediaProjects\MediaProjectResource;
use App\Filament\Resources\MediaTemplates\MediaTemplateResource;
use App\Filament\Resources\MetricSnapshots\MetricSnapshotResource;
use App\Filament\Resources\PlanLimits\PlanLimitResource;
use App\Filament\Resources\PovMetrics\PovMetricResource;
use App\Filament\Resources\PovReadings\PovReadingResource;
use App\Filament\Resources\ResellerCommissions\ResellerCommissionResource;
use App\Filament\Resources\Resellers\ResellerResource;
use App\Filament\Resources\RestoreDrills\RestoreDrillResource;
use App\Filament\Resources\SalesCopilotProspects\SalesCopilotProspectResource;
use App\Filament\Resources\Signatures\SignatureResource;
use App\Filament\Resources\SignDocuments\SignDocumentResource;
use App\Filament\Resources\SignTemplates\TemplateResource;
use App\Filament\Resources\TeamAiAssets\TeamAiAssetResource;
use App\Filament\Resources\TeamAiRepositories\TeamAiRepositoryResource;
use App\Filament\Resources\Tickets\TicketResource;
use App\Filament\Resources\UsageCounters\UsageCounterResource;
use App\Filament\Resources\Warehouses\WarehouseResource;
use App\Filament\Resources\Workflows\WorkflowResource;
use App\Filament\Resources\WorkflowStudioFlows\WorkflowStudioFlowResource;
use App\Filament\Resources\WorkflowTemplates\WorkflowTemplateResource;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioner;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Ujian UI Filament untuk 20 modul baki + BLive + McpStudio.
 *
 * Disusun selepas `Integration2026UiTest` (15 modul integrasi 2026).
 * Setiap entri di bawah mewakili satu resource Filament dalam modul
 * yang sebelum ini tiada UI sepenuhnya — kini semuanya boleh dirender
 * (HTTP 200) untuk pengguna tenant yang diberi kebenaran.
 */
class Remaining20UiTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected ?User $user = null;

    protected ?Tenant $tenant = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAkses();

        // ⚠️ Kebenaran modul ini mesti wujud dalam katalog SEBELUM
        // tenant didaftar — `tenant_owner` menerima semua kebenaran
        // yang ada pada masa pendaftaran (Provisioner::create).
        TenantProvisioner::syncPermissions();

        // Plan percubaan + langganan mesti wujud, jika tidak `bolehModul`
        // menolak (deny-by-default) dan Filament jadi 403.
        $this->seed(DatabaseSeeder::class);

        [$this->user, $this->tenant] = $this->daftar(
            'ui-baki@aibijan.test',
            'UI Baki Sdn Bhd'
        );

        // Login sebenar (bukan `actingAs`) — `ResolveTenant` memerlukan
        // session `tenant_ulid` untuk menentukan tenant semasa.
        $this->sebagai($this->user, $this->tenant);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function resourceProvider(): array
    {
        return [
            // 1. AIGateway
            'AIGateway — Model AI' => [AiModelResource::class],
            'AIGateway — Penggunaan' => [AiUsageEventResource::class],

            // 2. Billing
            'Billing — Langganan' => [BillingSubscriptionResource::class],
            'Billing — Reseller' => [ResellerResource::class],
            'Billing — Komisen' => [ResellerCommissionResource::class],

            // 3. BrainHub
            'BrainHub — Insight' => [InsightResource::class],
            'BrainHub — Ramalan' => [ForecastResource::class],
            'BrainHub — Cadangan' => [RecommendationResource::class],
            'BrainHub — Idea Bisnes' => [BusinessIdeaResource::class],

            // 4. Community
            'Community — Ruang' => [CommunitySpaceResource::class],
            'Community — Post' => [CommunityPostResource::class],

            // 5. Entitlement
            'Entitlement — Geran' => [EntitlementGrantResource::class],
            'Entitlement — Had Plan' => [PlanLimitResource::class],
            'Entitlement — Kaunter' => [UsageCounterResource::class],

            // 6. ExecutiveAI
            'ExecutiveAI — Ringkasan' => [ExecutiveBriefingResource::class],

            // 7. Fundamental
            'Fundamental — Amaran' => [AlertRuleResource::class],
            'Fundamental — Metrik' => [MetricSnapshotResource::class],
            'Fundamental — Latihan Pemulihan' => [RestoreDrillResource::class],

            // 8. Helpdesk
            'Helpdesk — Tiket' => [TicketResource::class],

            // 9. Inventory
            'Inventory — Item' => [ItemResource::class],
            'Inventory — Gudang' => [WarehouseResource::class],

            // 10. Link
            'Link — Kad Digital' => [DigitalCardResource::class],
            'Link — Halaman' => [LinkPageResource::class],

            // 11. LiveSupport
            'LiveSupport — Saluran' => [LiveSupportChannelResource::class],
            'LiveSupport — Panggilan' => [LiveSupportCallResource::class],

            // 12. Manufacturing
            'Manufacturing — BOM' => [BomResource::class],
            'Manufacturing — Pesanan Kerja' => [WorkOrderResource::class],

            // 13. McpStudio
            'McpStudio — Alat' => [McpToolResource::class],
            'McpStudio — Sumber' => [McpResourceResource::class],
            'McpStudio — Prompt' => [McpPromptResource::class],
            'McpStudio — Sesi' => [McpSessionResource::class],
            'McpStudio — Pelaksanaan' => [McpExecutionResource::class],
            'McpStudio — Log Audit' => [McpAuditLogResource::class],

            // 14. MediaEditor
            'MediaEditor — Projek' => [MediaProjectResource::class],
            'MediaEditor — Templat' => [MediaTemplateResource::class],

            // 15. ProofOfValue
            'ProofOfValue — Metrik' => [PovMetricResource::class],
            'ProofOfValue — Bacaan' => [PovReadingResource::class],

            // 16. SalesCopilot
            'SalesCopilot — Prospek' => [SalesCopilotProspectResource::class],

            // 17. SignDocs
            'SignDocs — Dokumen' => [DocumentResource::class],
            'SignDocs — Tandatangan' => [SignatureResource::class],
            'SignDocs — Dokumen Tandatangan' => [SignDocumentResource::class],
            'SignDocs — Templat' => [TemplateResource::class],

            // 18. TeamAI
            'TeamAI — Repositori' => [TeamAiRepositoryResource::class],
            'TeamAI — Aset' => [TeamAiAssetResource::class],

            // 19. WorkflowStudio
            'WorkflowStudio — Aliran' => [WorkflowResource::class],
            'WorkflowStudio — Definisi Aliran' => [FlowDefinitionResource::class],
            'WorkflowStudio — Aliran Studio' => [WorkflowStudioFlowResource::class],
            'WorkflowStudio — Templat' => [WorkflowTemplateResource::class],

            // 20. BLive
            'BLive — Avatar' => [LiveAvatarResource::class],
            'BLive — Personaliti Hos' => [LiveHostPersonalityResource::class],
            'BLive — Sesi' => [LiveSessionResource::class],
            'BLive — Saluran' => [LiveStreamDestinationResource::class],
            'BLive — Mesej' => [LiveMessageResource::class],
            'BLive — Moderasi' => [LiveModerationEventResource::class],
            'BLive — Kerja Render' => [LiveRenderJobResource::class],
            'BLive — Kebenaran' => [LiveConsentRecordResource::class],
            'BLive — Metrik' => [LiveMetricResource::class],
            'BLive — Kawalan' => [LiveControlResource::class],
        ];
    }

    #[DataProvider('resourceProvider')]
    public function test_resource_dapat_dirender(string $resourceClass): void
    {
        $this->assertTrue(class_exists($resourceClass), $resourceClass.' tidak wujud.');

        $url = $resourceClass::getUrl('index');

        $respons = $this->sebagai($this->user, $this->tenant)->get($url);

        if ($respons->status() === 403) {
            $this->fail(
                $resourceClass.' → 403: '.substr(strip_tags($respons->getContent()), 0, 300)
            );
        }

        $respons->assertSuccessful();
    }

    public function test_semua_nama_pada_provider_kepunyaan_modul_yang_benar(): void
    {
        // Liputan modul: setiap namespace kebenaran moduls individu mesti
        // mempunyai sekurang-kurangnya satu resource Filament.
        $expected = [
            'ai_gateway', 'billing', 'brainhub', 'community', 'entitlement',
            'executive_ai', 'fundamental', 'helpdesk', 'inventory', 'link',
            'live_support', 'manufacturing', 'mcp', 'media_editor',
            'proof_of_value', 'sales_copilot', 'sign_docs', 'team_ai',
            'workflow_studio', 'blive',
        ];

        $ditemui = [];

        foreach (static::resourceProvider() as $args) {
            $kelas = $args[0];
            $refleksi = new \ReflectionClass($kelas);
            $metod = $refleksi->getMethod('permissionNamespace');
            $metod->setAccessible(true);
            // permissionNamespace() ialah kaedah statik.
            $ditemui[$metod->invoke(null)] = true;
        }

        foreach ($expected as $ns) {
            $this->assertArrayHasKey($ns, $ditemui, "Modul `{$ns}` tiada resource Filament dalam provider.");
        }
    }
}
