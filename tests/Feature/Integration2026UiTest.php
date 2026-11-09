<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentDocuments\ContentDocumentResource;
use App\Filament\Resources\ContentSites\ContentSiteResource;
use App\Filament\Resources\DictationSessions\DictationSessionResource;
use App\Filament\Resources\DictationVocabularies\DictationVocabularyResource;
use App\Filament\Resources\GenAiJobs\GenAiJobResource;
use App\Filament\Resources\GenAiProviders\GenAiProviderResource;
use App\Filament\Resources\InboxConversations\InboxConversationResource;
use App\Filament\Resources\OmniCouriers\OmniCourierResource;
use App\Filament\Resources\OmniOrders\OmniOrderResource;
use App\Filament\Resources\PosFoodCourts\PosFoodCourtResource;
use App\Filament\Resources\PosGiftCards\PosGiftCardResource;
use App\Filament\Resources\PosOnlineStores\PosOnlineStoreResource;
use App\Filament\Resources\PosTableReservations\PosTableReservationResource;
use App\Filament\Resources\ProjectPortfolios\ProjectPortfolioResource;
use App\Filament\Resources\ProjectTimelines\ProjectTimelineResource;
use App\Filament\Resources\SchedulingLinks\SchedulingLinkResource;
use App\Filament\Resources\ScreenRecordings\ScreenRecordingResource;
use App\Filament\Resources\SeoKeywordResearch\SeoKeywordResearchResource;
use App\Filament\Resources\SeoProjects\SeoProjectResource;
use App\Filament\Resources\SharedInboxes\SharedInboxResource;
use App\Filament\Resources\SocialAccounts\SocialAccountResource;
use App\Filament\Resources\SocialPosts\SocialPostResource;
use App\Filament\Resources\VideoBrandKits\VideoBrandKitResource;
use App\Filament\Resources\VideoProjects\VideoProjectResource;
use App\Filament\Resources\VoiceProfiles\VoiceProfileResource;
use App\Filament\Resources\WaCampaigns\WaCampaignResource;
use App\Filament\Resources\WaContacts\WaContactResource;
use App\Filament\Resources\WaDevices\WaDeviceResource;
use App\Filament\Resources\WorkspacePages\WorkspacePageResource;
use App\Filament\Resources\WorkspaceSpaces\WorkspaceSpaceResource;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioner;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Ujian UI Filament untuk 15 modul integrasi 2026.
 *
 * Menyakinkan setiap resource baharu boleh dirender (HTTP 200) untuk
 * pengguna tenant yang diberi kebenaran — bukan sekadar kelas wujud.
 */
class Integration2026UiTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected ?User $user = null;

    protected ?Tenant $tenant = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAkses();

        // ⚠️ Kebenaran modul integrasi 2026 mesti wujud dalam katalog
        // SEBELUM tenant didaftar — `tenant_owner` mendapat semua
        // kebenaran yang ada pada masa pendaftaran (Provisioner::create).
        // Tanpa ini, Filament tolak dengan 403.
        TenantProvisioner::syncPermissions();

        // Plan percubaan + langganan mesti wujud, jika tidak `bolehModul`
        // menolak (deny-by-default) dan Filamen jadi 403.
        $this->seed(DatabaseSeeder::class);

        [$this->user, $this->tenant] = $this->daftar(
            'ui-2026@aibijan.test',
            'UI 2026 Sdn Bhd'
        );

        // Login sebenar (bukan `actingAs`) — `ResolveTenant` memerlukan
        // session `tenant_ulid` untuk멘entukan tenant semasa.
        $this->sebagai($this->user, $this->tenant);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function resourceProvider(): array
    {
        return [
            'OpenGenAI — Penyedia AI' => [GenAiProviderResource::class],
            'OpenGenAI — Penjanaan' => [GenAiJobResource::class],
            'VoiceStudio — Profil Suara' => [VoiceProfileResource::class],
            'OpenWhispr — Sesi Diktasi' => [DictationSessionResource::class],
            'OpenWhispr — Kamus' => [DictationVocabularyResource::class],
            'OpenSEO — Projek' => [SeoProjectResource::class],
            'OpenSEO — Kata Kunci' => [SeoKeywordResearchResource::class],
            'Workspace — Ruang Kerja' => [WorkspaceSpaceResource::class],
            'Workspace — Halaman' => [WorkspacePageResource::class],
            'WorkManagement — Garis Masa' => [ProjectTimelineResource::class],
            'WorkManagement — Portfolio' => [ProjectPortfolioResource::class],
            'Scheduling — Pautan' => [SchedulingLinkResource::class],
            'SharedInbox — Peti Masuk' => [SharedInboxResource::class],
            'SharedInbox — Perbincangan' => [InboxConversationResource::class],
            'ScreenRecorder — Rakaman' => [ScreenRecordingResource::class],
            'VideoStudio — Projek' => [VideoProjectResource::class],
            'VideoStudio — Jenama' => [VideoBrandKitResource::class],
            'POSBpos — Kad Hadiah' => [PosGiftCardResource::class],
            'POSBpos — Tempahan Meja' => [PosTableReservationResource::class],
            'POSBpos — Food Court' => [PosFoodCourtResource::class],
            'POSBpos — Kedai Online' => [PosOnlineStoreResource::class],
            'OmniOrder — Pesanan' => [OmniOrderResource::class],
            'OmniOrder — Kurier' => [OmniCourierResource::class],
            'SocialBridge — Akaun' => [SocialAccountResource::class],
            'SocialBridge — Catatan' => [SocialPostResource::class],
            'ContentForge — Dokumen' => [ContentDocumentResource::class],
            'ContentForge — Laman' => [ContentSiteResource::class],
            'WhatsAppSuite — Peranti' => [WaDeviceResource::class],
            'WhatsAppSuite — Kempen' => [WaCampaignResource::class],
            'WhatsAppSuite — Kontak' => [WaContactResource::class],
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
}
