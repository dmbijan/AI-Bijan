<?php

/**
 * Penjana resource Filament pukal.
 *
 * Mencipta satu Resource + halaman senarai/cipta/edit ringkas untuk
 * setiap definisi di bawah. SENGAJA tidak menulis borang terperinci —
 * resource ini memberi LIPUTAN NAVIGASI + senarai/carian, dan boleh
 * diperkaya kemudian.
 *
 * Jalankan: php skrip/jana-resource.php
 */

// ⚠️ Perlukan autoload supaya `class_exists()` berfungsi.
require __DIR__.'/../vendor/autoload.php';

$asas = __DIR__.'/../app/Filament/Resources';

/**
 * Setiap entri: [folder, kelas, model, slug, label, labelPlural,
 *                  ikon, kumpulan, sort, namespacePermission, bolehCipta]
 */
$definisi = [
    // ===== TERAS SEDIA ADA (jangan padam semula!) =====
    ['Expenses', 'ExpenseResource', 'Expense',
        'perbelanjaan', 'Perbelanjaan', 'Perbelanjaan', 'heroicon-o-banknotes', 'Kewangan', 10, 'finance', true],

    ['Orders', 'OrderResource', 'UnifiedOrder',
        'pesanan', 'Pesanan', 'Pesanan', 'heroicon-o-shopping-cart', 'Jualan', 10, 'omnichannel', true],

    ['Customers', 'CustomerResource', 'Customer',
        'pelanggan', 'Pelanggan', 'Pelanggan', 'heroicon-o-user-group', 'Jualan', 11, 'crm', true],

    ['Invoices', 'InvoiceResource', 'Invoice',
        'invois', 'Invois', 'Invois', 'heroicon-o-document-text', 'Kewangan', 11, 'invoicing', true],

    ['Items', 'ItemResource', 'Item',
        'produk', 'Produk', 'Produk', 'heroicon-o-cube', 'Inventori', 10, 'inventory', true],

    ['ServiceProjects', 'ServiceProjectResource', 'ServiceProject',
        'projek-perkhidmatan', 'Projek', 'Projek Perkhidmatan', 'heroicon-o-briefcase', 'Perkhidmatan', 10, 'projects', true],

    ['ContentPieces', 'ContentPieceResource', 'ContentPiece',
        'kandungan', 'Kandungan', 'Kandungan', 'heroicon-o-newspaper', 'Pemasaran', 10, 'marketing', true],

    ['BusinessIdeas', 'BusinessIdeaResource', 'BiIdea',
        'idea-bisnes', 'Idea', 'Idea Bisnes', 'heroicon-o-light-bulb', 'Intelijen', 10, 'brainhub', true],

    // ===== KEWANGAN =====
    ['FinanceEntries', 'FinanceEntryResource', 'JournalEntry',
        'jurnal', 'Jurnal', 'Jurnal', 'heroicon-o-book-open', 'Kewangan', 30, 'finance', false],

    ['ChartOfAccounts', 'ChartOfAccountResource', 'ChartOfAccount',
        'carta-akaun', 'Akaun', 'Carta Akaun', 'heroicon-o-list-bullet', 'Kewangan', 31, 'finance', true],

    // ===== HR =====
    ['HrEmployees', 'HrEmployeeResource', 'Employee',
        'pekerja', 'Pekerja', 'Pekerja', 'heroicon-o-user-group', 'Sumber Manusia', 40, 'hrms', true],

    ['LeaveRequests', 'LeaveRequestResource', 'LeaveRequest',
        'cuti', 'Cuti', 'Permohonan Cuti', 'heroicon-o-calendar-days', 'Sumber Manusia', 41, 'hrms', false],

    ['Attendances', 'AttendanceResource', 'Attendance',
        'kehadiran', 'Kehadiran', 'Kehadiran', 'heroicon-o-clock', 'Sumber Manusia', 42, 'hrms', false],

    // ===== PAYROLL =====
    ['PayrollRuns', 'PayrollRunResource', 'PayrollRun',
        'gaji-proses', 'Proses Gaji', 'Proses Gaji', 'heroicon-o-banknotes', 'Sumber Manusia', 43, 'payroll', false],

    // ===== ASET =====
    ['Assets2', 'AssetResource', 'Asset',
        'aset', 'Aset', 'Aset', 'heroicon-o-cube', 'Operasi', 50, 'assets', true],

    // ===== PROJEK =====
    ['Projects2', 'ProjectResource', 'Project',
        'projek', 'Projek', 'Projek', 'heroicon-o-folder', 'Operasi', 51, 'projects', true],

    // ===== SCM =====
    ['PurchaseOrders', 'PurchaseOrderResource', 'PurchaseOrder',
        'pesanan-belian', 'Pesanan Belian', 'Pesanan Belian', 'heroicon-o-truck', 'Operasi', 52, 'scm', true],

    ['Suppliers', 'SupplierResource', 'Supplier',
        'pembekal', 'Pembekal', 'Pembekal', 'heroicon-o-building-office', 'Operasi', 53, 'scm', true],

    // ===== CRM =====
    ['Leads', 'LeadResource', 'Lead',
        'petunjuk', 'Petunjuk', 'Petunjuk Jualan', 'heroicon-o-user-plus', 'Jualan', 25, 'crm', true],

    ['Opportunities', 'OpportunityResource', 'Opportunity',
        'peluang', 'Peluang', 'Peluang Jualan', 'heroicon-o-arrow-trending-up', 'Jualan', 26, 'crm', true],

    // ===== BOOKING =====
    ['Appointments', 'AppointmentResource', 'Appointment',
        'temujanji', 'Temujanji', 'Temujanji', 'heroicon-o-calendar', 'Jualan', 27, 'booking', true],

    // ===== MARKETING =====
    ['Campaigns', 'CampaignResource', 'Campaign',
        'kempen', 'Kempen', 'Kempen', 'heroicon-o-megaphone', 'Pemasaran', 60, 'marketing', true],

    ['Vouchers', 'VoucherResource', 'Voucher',
        'baucar', 'Baucar', 'Baucar', 'heroicon-o-ticket', 'Pemasaran', 65, 'marketing', true],

    // ===== LOYALTY =====
    ['LoyaltyMembers', 'LoyaltyMemberResource', 'Member',
        'ahli-loyalty', 'Ahli', 'Ahli Loyalty', 'heroicon-o-star', 'Pemasaran', 61, 'loyalty', true],

    ['Rewards', 'RewardResource', 'Reward',
        'ganjaran', 'Ganjaran', 'Ganjaran', 'heroicon-o-gift', 'Pemasaran', 66, 'loyalty', true],

    // ===== AFFILIATE =====
    ['Affiliates', 'AffiliateResource', 'Affiliate',
        'affiliate', 'Affiliate', 'Affiliate', 'heroicon-o-share', 'Pemasaran', 62, 'affiliate', true],

    // ===== LOGISTICS =====
    ['Shipments', 'ShipmentResource', 'Shipment',
        'penghantaran', 'Penghantaran', 'Penghantaran', 'heroicon-o-truck', 'Operasi', 54, 'logistics', true],

    ['Warehouses2', 'WarehouseResource', 'Warehouse',
        'gudang', 'Gudang', 'Gudang', 'heroicon-o-home-modern', 'Operasi', 56, 'logistics', true],

    // ===== FLEET =====
    ['FleetVehicles', 'FleetVehicleResource', 'FleetVehicle',
        'kenderaan', 'Kenderaan', 'Kenderaan', 'heroicon-o-truck', 'Operasi', 57, 'logistics', true],

    // ===== DOKUMEN =====
    ['SignDocuments', 'SignDocumentResource', 'Document',
        'tandatangan', 'Dokumen', 'Tandatangan Dokumen', 'heroicon-o-pencil-square', 'Dokumen', 70, 'sign_docs', true],

    ['DocumentAis', 'DocumentAiResource', 'Document',
        'dokumen-ai', 'Dokumen', 'Dokumen AI', 'heroicon-o-document-magnifying-glass', 'Dokumen', 71, 'documents', true],

    // ===== SITE =====
    ['SitePages', 'SitePageResource', 'Page',
        'laman', 'Laman', 'Laman Web', 'heroicon-o-globe-alt', 'Pemasaran', 63, 'site', true],

    ['SiteForms', 'SiteFormResource', 'Form',
        'borang-laman', 'Borang', 'Borang Laman', 'heroicon-o-clipboard-document-list', 'Pemasaran', 67, 'site', true],

    // ===== SEO =====
    ['SeoKeywords', 'SeoKeywordResource', 'SeoKeyword',
        'seo-kata-kunci', 'Kata Kunci', 'Kata Kunci SEO', 'heroicon-o-magnifying-glass', 'Pemasaran', 64, 'marketing', true],

    // ===== REMINDER =====
    ['Reminders', 'ReminderResource', 'Reminder',
        'peringatan', 'Peringatan', 'Peringatan', 'heroicon-o-bell', 'Operasi', 58, 'reminder', true],

    // ===== FLOW =====
    ['FlowDefinitions', 'FlowDefinitionResource', 'WorkflowDefinition',
        'aliran-kerja', 'Aliran', 'Aliran Kerja', 'heroicon-o-arrow-path', 'Automasi', 80, 'workflow_studio', true],

    ['WorkflowStudioFlows', 'WorkflowStudioFlowResource', 'Workflow',
        'studio-aliran', 'Aliran', 'Studio Aliran', 'heroicon-o-squares-2x2', 'Automasi', 81, 'workflow_studio', true],

    // ===== AI =====
    ['AiAgents2', 'AiAgentResource', 'Agent',
        'ejen-ai', 'Ejen', 'Ejen AI', 'heroicon-o-cpu-chip', 'AI', 85, 'ai_agents', true],

    ['AgentBlueprints', 'AgentBlueprintResource', 'AgentBlueprint',
        'studio-ejen', 'Blueprint', 'Studio Ejen', 'heroicon-o-beaker', 'AI', 86, 'agent_studio', true],

    // ===== INTEL =====
    ['BiIdeas', 'BiIdeaResource', 'BiIdea',
        'idea-bisnes-2', 'Idea', 'Idea Bisnes', 'heroicon-o-light-bulb', 'Intelijen', 30, 'business_intel', true],

    ['BiCompetitors', 'BiCompetitorResource', 'BiCompetitor',
        'pesaing', 'Pesaing', 'Pesaing', 'heroicon-o-eye', 'Intelijen', 31, 'business_intel', true],

    // ===== PROSPECT =====
    ['Prospects', 'ProspectResource', 'Prospect',
        'prospek', 'Prospek', 'Prospek', 'heroicon-o-user-circle', 'Pemasaran', 68, 'marketing', true],

    ['BlastCampaigns', 'BlastCampaignResource', 'BlastCampaign',
        'kempen-blast', 'Kempen', 'Kempen Blast', 'heroicon-o-paper-airplane', 'Pemasaran', 69, 'marketing', false],

    // ===== CONTENT =====
    ['ContentIdeas', 'ContentIdeaResource', 'ContentIdea',
        'idea-kandungan', 'Idea', 'Idea Kandungan', 'heroicon-o-sparkles', 'Pemasaran', 70, 'content_studio', true],

    // ===== MEDIA =====
    ['MediaAssets', 'MediaAssetResource', 'MediaAsset',
        'media', 'Media', 'Media', 'heroicon-o-photo', 'Pemasaran', 71, 'media_studio', true],

    // ===== OMNICHANNEL =====
    ['OmniChannels', 'OmniChannelResource', 'Channel',
        'saluran', 'Saluran', 'Saluran Omnichannel', 'heroicon-o-signal', 'Pemasaran', 72, 'omnichannel', true],

    ['OmniConversations', 'OmniConversationResource', 'Conversation',
        'perbualan', 'Perbualan', 'Perbualan', 'heroicon-o-chat-bubble-left-right', 'Pemasaran', 73, 'omnichannel', false],

    // ===== HELP DESK =====
    ['Tickets', 'TicketResource', 'Ticket',
        'tiket', 'Tiket', 'Tiket Sokongan', 'heroicon-o-lifebuoy', 'Sokongan', 90, 'helpdesk', true],

    // ===== SERVICE =====
    ['ServiceClients', 'ServiceClientResource', 'ServiceClient',
        'klien-perkhidmatan', 'Klien', 'Klien Perkhidmatan', 'heroicon-o-users', 'Perkhidmatan', 74, 'service_os', true],

    ['ServiceContracts', 'ServiceContractResource', 'ServiceContract',
        'kontrak-perkhidmatan', 'Kontrak', 'Kontrak Perkhidmatan', 'heroicon-o-document-check', 'Perkhidmatan', 75, 'service_os', true],

    // ===== COD / MARKETPLACE =====
    ['CodOrders', 'CodOrderResource', 'CodOrder',
        'cod', 'COD', 'Pesanan COD', 'heroicon-o-banknotes', 'Operasi', 59, 'cod', true],

    ['MarketplaceProducts', 'MarketplaceProductResource', 'Product',
        'produk-marketplace', 'Produk', 'Produk Marketplace', 'heroicon-o-shopping-bag', 'Jualan', 28, 'marketplace', true],

    // ===== ANALYTICS =====
    ['Metrics', 'MetricResource', 'Metric',
        'metrik', 'Metrik', 'Metrik', 'heroicon-o-chart-bar', 'Intelijen', 32, 'analytics', true],

    // ===== DASHBOARD BOARDS =====
    ['Dashboards', 'DashboardResource', 'Dashboard',
        'papan-analitik', 'Papan', 'Papan Analitik', 'heroicon-o-presentation-chart-line', 'Intelijen', 33, 'analytics', true],

    // ===== BOARDROOM =====
    ['BoardroomSessions', 'BoardroomSessionResource', 'BoardroomSession',
        'boardroom', 'Sesi', 'Sesi Boardroom', 'heroicon-o-academic-cap', 'Intelijen', 34, 'boardroom', true],

    // ===== INDUSTRY =====
    ['IndustryProfiles', 'IndustryProfileResource', 'IndustryProfile',
        'profil-industri', 'Profil', 'Profil Industri', 'heroicon-o-building-storefront', 'Tetapan', 92, 'industry_packs', true],

    // ===== VISION =====
    ['CctvCameras', 'CctvCameraResource', 'CctvCamera',
        'kamera', 'Kamera', 'Kamera CCTV', 'heroicon-o-video-camera', 'Operasi', 60, 'vision', true],

    // ===== VIRTUAL OFFICE =====
    ['VoBoards', 'VoBoardResource', 'Board',
        'pejabat-maya', 'Papan', 'Pejabat Maya', 'heroicon-o-view-columns', 'Automasi', 82, 'virtual_office', true],

    // ===== PERSONAL AI =====
    ['PersonalTasks', 'PersonalTaskResource', 'PersonalTask',
        'tugasan-saya', 'Tugasan', 'Tugasan Saya', 'heroicon-o-check-circle', 'Automasi', 83, 'personal_ai', true],

    // ===== INTEGRASI 2026 (15 modul) =====
    // Setiap modul: 1-2 resource utama. Model '&' bermaksud "model ini".

    // 1. OpenGenAI — enjin generatif
    ['GenAiProviders', 'GenAiProviderResource', 'GenAiProvider',
        'penyedia-ai', 'Penyedia AI', 'Penyedia AI', 'heroicon-o-cpu-chip', 'Integrasi AI', 10, 'open_gen_ai', true],
    ['GenAiJobs', 'GenAiJobResource', 'GenAiJob',
        'penjanaan-ai', 'Penjanaan', 'Penjanaan AI', 'heroicon-o-sparkles', 'Integrasi AI', 11, 'open_gen_ai', false],

    // 2. VoiceStudio — enjin suara
    ['VoiceProfiles', 'VoiceProfileResource', 'VoiceProfile',
        'profil-suara', 'Profil Suara', 'Profil Suara', 'heroicon-o-microphone', 'Integrasi AI', 12, 'voice_studio', true],
    ['VoiceTracks', 'VoiceTrackResource', 'VoiceTrack',
        'rakaman-suara', 'Rakaman Suara', 'Rakaman Suara', 'heroicon-o-musical-note', 'Integrasi AI', 13, 'voice_studio', false],

    // 3. OpenWhispr — diktasi
    ['DictationSessions', 'DictationSessionResource', 'DictationSession',
        'sesi-diktasi', 'Sesi Diktasi', 'Sesi Diktasi', 'heroicon-o-hand-raised', 'Integrasi AI', 14, 'open_whispr', false],
    ['DictationVocabularies', 'DictationVocabularyResource', 'DictationVocabulary',
        'kamus-diktasi', 'Kamus', 'Kamus Diktasi', 'heroicon-o-book-open', 'Integrasi AI', 15, 'open_whispr', true],

    // 4. OpenSEO — intel kompetitif
    ['SeoProjects', 'SeoProjectResource', 'SeoProject',
        'projek-seo', 'Projek SEO', 'Projek SEO', 'heroicon-o-chart-bar', 'Integrasi AI', 16, 'open_seo', true],
    ['SeoKeywordResearch', 'SeoKeywordResearchResource', 'SeoKeywordResearch',
        'kata-kunci-seo', 'Kata Kunci', 'Kata Kunci SEO', 'heroicon-o-magnifying-glass', 'Integrasi AI', 17, 'open_seo', true],

    // 5. Workspace — dokumen kolaboratif
    ['WorkspaceSpaces', 'WorkspaceSpaceResource', 'WorkspaceSpace',
        'ruang-kerja', 'Ruang Kerja', 'Ruang Kerja', 'heroicon-o-square-3-stack-3d', 'Integrasi AI', 18, 'workspace', true],
    ['WorkspacePages', 'WorkspacePageResource', 'WorkspacePage',
        'halaman', 'Halaman', 'Halaman', 'heroicon-o-document-text', 'Integrasi AI', 19, 'workspace', true],

    // 6. WorkManagement — pengurusan kerja
    ['ProjectTimelines', 'ProjectTimelineResource', 'ProjectTimeline',
        'garis-masa', 'Garis Masa', 'Garis Masa Projek', 'heroicon-o-chart-pie', 'Operasi', 70, 'work_management', true],
    ['ProjectPortfolios', 'ProjectPortfolioResource', 'ProjectPortfolio',
        'portfolio-projek', 'Portfolio', 'Portfolio Projek', 'heroicon-o-rectangle-group', 'Operasi', 71, 'work_management', true],

    // 7. Scheduling — penjadualan
    ['SchedulingLinks', 'SchedulingLinkResource', 'SchedulingLink',
        'pautan-jadwal', 'Pautan Jadwal', 'Pautan Jadwal', 'heroicon-o-calendar-days', 'Operasi', 72, 'scheduling', true],
    ['SchedulingSlots', 'SchedulingSlotResource', 'SchedulingSlot',
        'slot-jadwal', 'Slot', 'Slot Jadwal', 'heroicon-o-clock', 'Operasi', 73, 'scheduling', false],

    // 8. SharedInbox — peti masuk bersama
    ['SharedInboxes', 'SharedInboxResource', 'SharedInbox',
        'peti-masuk', 'Peti Masuk', 'Peti Masuk Bersama', 'heroicon-o-inbox-stack', 'Sokongan', 90, 'shared_inbox', true],
    ['InboxConversations', 'InboxConversationResource', 'InboxConversation',
        'perbincangan', 'Perbincangan', 'Perbincangan', 'heroicon-o-chat-bubble-left-right', 'Sokongan', 91, 'shared_inbox', true],

    // 9. ScreenRecorder — rakaman skrin
    ['ScreenRecordings', 'ScreenRecordingResource', 'ScreenRecording',
        'rakaman-skrin', 'Rakaman Skrin', 'Rakaman Skrin', 'heroicon-o-video-camera', 'Integrasi AI', 20, 'screen_recorder', true],

    // 10. VideoStudio — orkestrasi video
    ['VideoProjects', 'VideoProjectResource', 'VideoProject',
        'projek-video', 'Projek Video', 'Projek Video', 'heroicon-o-film', 'Integrasi AI', 21, 'video_studio', true],
    ['VideoBrandKits', 'VideoBrandKitResource', 'VideoBrandKit',
        'jenama-video', 'Jenama Video', 'Jenama Video', 'heroicon-o-swatch', 'Integrasi AI', 22, 'video_studio', true],

    // 11. POSBpos — ciri POS lanjutan
    ['PosGiftCards', 'PosGiftCardResource', 'PosGiftCard',
        'kad-hadiah', 'Kad Hadiah', 'Kad Hadiah', 'heroicon-o-gift', 'Jualan', 25, 'pos', true],
    ['PosTableReservations', 'PosTableReservationResource', 'PosTableReservation',
        'tempahan-meja', 'Tempahan Meja', 'Tempahan Meja', 'heroicon-o-calendar', 'Jualan', 26, 'pos', true],
    ['PosFoodCourts', 'PosFoodCourtResource', 'PosFoodCourt',
        'food-court', 'Food Court', 'Food Court', 'heroicon-o-building-storefront', 'Jualan', 27, 'pos', true],
    ['PosOnlineStores', 'PosOnlineStoreResource', 'PosOnlineStore',
        'kedai-online', 'Kedai Online', 'Kedai Online', 'heroicon-o-globe-alt', 'Jualan', 28, 'pos', true],

    // 12. OmniOrder — pesanan omnichannel
    ['OmniOrders', 'OmniOrderResource', 'OmniOrder',
        'pesanan-omnichannel', 'Pesanan', 'Pesanan Omnichannel', 'heroicon-o-shopping-bag', 'Jualan', 29, 'omni_order', true],
    ['OmniCouriers', 'OmniCourierResource', 'OmniCourier',
        'kurier', 'Kurier', 'Kurier', 'heroicon-o-truck', 'Jualan', 30, 'omni_order', true],

    // 13. SocialBridge — terhindar silang
    ['SocialAccounts', 'SocialAccountResource', 'SocialAccount',
        'akaun-sosial', 'Akaun Sosial', 'Akaun Sosial', 'heroicon-o-share', 'Pemasaran', 64, 'social_bridge', true],
    ['SocialPosts', 'SocialPostResource', 'SocialPost',
        'catatan-sosial', 'Catatan', 'Catatan Sosial', 'heroicon-o-pencil-square', 'Pemasaran', 63, 'social_bridge', true],

    // 14. ContentForge — kandungan & SEO
    ['ContentDocuments', 'ContentDocumentResource', 'ContentDocument',
        'dokumen-kandungan', 'Dokumen', 'Dokumen Kandungan', 'heroicon-o-pencil', 'Pemasaran', 68, 'content_forge', true],
    ['ContentSites', 'ContentSiteResource', 'ContentSite',
        'laman-kandungan', 'Laman', 'Laman Kandungan', 'heroicon-o-globe-alt', 'Pemasaran', 69, 'content_forge', true],

    // 15. WhatsAppSuite — automasi WhatsApp
    ['WaDevices', 'WaDeviceResource', 'WaDevice',
        'peranti-wa', 'Peranti', 'Peranti WhatsApp', 'heroicon-o-device-phone-mobile', 'Sokongan', 92, 'whatsapp_suite', true],
    ['WaCampaigns', 'WaCampaignResource', 'WaCampaign',
        'kempen-wa', 'Kempen', 'Kempen WhatsApp', 'heroicon-o-megaphone', 'Sokongan', 93, 'whatsapp_suite', true],
    ['WaContacts', 'WaContactResource', 'WaContact',
        'kontak-wa', 'Kontak', 'Kontak WhatsApp', 'heroicon-o-users', 'Sokongan', 94, 'whatsapp_suite', true],

    // ===== UI MODUL BAKI (20 modul tanpa Filament) =====
    // 1. AIGateway — katalog model & wallet kredit
    ['AiModels', 'AiModelResource', 'AiModel',
        'model-ai', 'Model AI', 'Model AI', 'heroicon-o-cpu-chip', 'AI', 5, 'ai_gateway', false],
    ['AiUsageEvents', 'AiUsageEventResource', 'AiUsageEvent',
        'penggunaan-ai', 'Penggunaan AI', 'Penggunaan AI', 'heroicon-o-chart-bar', 'AI', 6, 'ai_gateway', false],

    // 2. Billing — langganan & reseller
    ['BillingSubscriptions', 'BillingSubscriptionResource', 'BillingSubscription',
        'langganan', 'Langganan', 'Langganan', 'heroicon-o-credit-card', 'Kewangan', 12, 'billing', false],
    ['Resellers', 'ResellerResource', 'Reseller',
        'reseller', 'Reseller', 'Reseller', 'heroicon-o-user-group', 'Kewangan', 13, 'billing', true],
    ['ResellerCommissions', 'ResellerCommissionResource', 'ResellerCommission',
        'komisen-reseller', 'Komisen', 'Komisen Reseller', 'heroicon-o-banknotes', 'Kewangan', 14, 'billing', false],

    // 3. BrainHub — insight, ramalan, cadangan
    ['BrainInsights', 'InsightResource', 'Insight',
        'insight', 'Insight', 'Insight', 'heroicon-o-light-bulb', 'Intelijen', 11, 'brainhub', false],
    ['BrainForecasts', 'ForecastResource', 'Forecast',
        'ramalan', 'Ramalan', 'Ramalan', 'heroicon-o-arrow-trending-up', 'Intelijen', 12, 'brainhub', false],
    ['BrainRecommendations', 'RecommendationResource', 'Recommendation',
        'cadangan', 'Cadangan', 'Cadangan', 'heroicon-o-sparkles', 'Intelijen', 13, 'brainhub', false],

    // 4. COGS — kos produk
    ['ProductCosts', 'ProductCostResource', 'ProductCost',
        'kos-produk', 'Kos Produk', 'Kos Produk', 'heroicon-o-calculator', 'Kewangan', 15, 'finance', false],
    ['CostComponents', 'CostComponentResource', 'CostComponent',
        'komponen-kos', 'Komponen Kos', 'Komponen Kos', 'heroicon-o-puzzle-piece', 'Kewangan', 16, 'finance', false],

    // 5. Community — ruang komuniti
    ['CommunitySpaces', 'CommunitySpaceResource', 'CommunitySpace',
        'ruang-komuniti', 'Ruang', 'Ruang Komuniti', 'heroicon-o-users', 'Komuniti', 75, 'community', true],
    ['CommunityPosts', 'CommunityPostResource', 'CommunityPost',
        'post-komuniti', 'Post', 'Post Komuniti', 'heroicon-o-chat-bubble-oval-left-ellipsis', 'Komuniti', 76, 'community', true],

    // 6. Entitlement — plan, grant, had
    ['EntitlementGrants', 'EntitlementGrantResource', 'EntitlementGrant',
        'grant-modul', 'Grant Modul', 'Grant Modul', 'heroicon-o-key', 'Tetapan', 95, 'entitlement', true],
    ['PlanLimits', 'PlanLimitResource', 'PlanLimit',
        'had-plan', 'Had Plan', 'Had Plan', 'heroicon-o-adjustments-horizontal', 'Tetapan', 96, 'entitlement', true],
    ['UsageCounters', 'UsageCounterResource', 'UsageCounter',
        'kaunter-guna', 'Kaunter', 'Kaunter Guna', 'heroicon-o-chart-pie', 'Tetapan', 97, 'entitlement', false],

    // 7. ExecutiveAI — ringkasan eksekutif
    ['ExecutiveBriefings', 'ExecutiveBriefingResource', 'ExecutiveBriefing',
        'ringkasan-eksekutif', 'Ringkasan', 'Ringkasan Eksekutif', 'heroicon-o-presentation-chart-bar', 'AI', 7, 'executive_ai', false],

    // 8. Fundamental — pemerSaatan & amaran
    ['MetricSnapshots', 'MetricSnapshotResource', 'MetricSnapshot',
        'metrik-pemantauan', 'Metrik', 'Metrik Pemantauan', 'heroicon-o-chart-bar', 'Tetapan', 98, 'fundamental', false],
    ['AlertRules', 'AlertRuleResource', 'AlertRule',
        'peraturan-amaran', 'Peraturan Amaran', 'Peraturan Amaran', 'heroicon-o-exclamation-triangle', 'Tetapan', 99, 'fundamental', true],
    ['RestoreDrills', 'RestoreDrillResource', 'RestoreDrill',
        'latihan-pulih', 'Latihan Pulih', 'Latihan Pemulihan', 'heroicon-o-arrow-path', 'Tetapan', 100, 'fundamental', false],

    // 9. Helpdesk — resource 'Tickets' sudah wujud dalam definisi atas
    // 10. Inventory — 'Items' juga sudah wujud; tambah Gudang sahaja
    ['Warehouses', 'WarehouseResource', 'Warehouse',
        'gudang', 'Gudang', 'Gudang', 'heroicon-o-building-storefront', 'Inventori', 12, 'inventory', true],

    // 11. Link — dynamic links & kad digital
    ['LinkPages', 'LinkPageResource', 'LinkPage',
        'laman-pautan', 'Laman Pautan', 'Laman Pautan', 'heroicon-o-link', 'Pemasaran', 67, 'link', true],
    ['DigitalCards', 'DigitalCardResource', 'DigitalCard',
        'kad-digital', 'Kad Digital', 'Kad Digital', 'heroicon-o-identification', 'Pemasaran', 62, 'link', true],

    // 12. LiveSupport — saluran & panggilan
    ['LiveSupportChannels', 'LiveSupportChannelResource', 'LiveSupportChannel',
        'saluran-sokongan', 'Saluran', 'Saluran Sokongan', 'heroicon-o-phone', 'Sokongan', 96, 'live_support', true],
    ['LiveSupportCalls', 'LiveSupportCallResource', 'LiveSupportCall',
        'panggilan-sokongan', 'Panggilan', 'Panggilan Sokongan', 'heroicon-o-device-phone-mobile', 'Sokongan', 97, 'live_support', false],

    // 13. Manufacturing — BOM & work order
    ['Boms', 'BomResource', 'Bom',
        'resen-pengeluaran', 'Resen', 'Resen Pengeluaran', 'heroicon-o-clipboard-document-list', 'Inventori', 13, 'manufacturing', true],
    ['WorkOrders', 'WorkOrderResource', 'WorkOrder',
        'perintah-kerja', 'Perintah Kerja', 'Perintah Kerja', 'heroicon-o-wrench-screwdriver', 'Inventori', 14, 'manufacturing', true],

    // 14. McpStudio — alat & sesi MCP
    ['McpTools', 'McpToolResource', 'McpTool',
        'alat-mcp', 'Alat', 'Alat MCP', 'heroicon-o-wrench', 'AI', 8, 'mcp_studio', true],
    ['McpSessions', 'McpSessionResource', 'McpSession',
        'sesi-mcp', 'Sesi', 'Sesi MCP', 'heroicon-o-server-stack', 'AI', 9, 'mcp_studio', false],

    // 15. MediaEditor — projek & template
    ['MediaProjects', 'MediaProjectResource', 'MediaProject',
        'projek-media', 'Projek Media', 'Projek Media', 'heroicon-o-film', 'Media', 20, 'media_editor', true],
    ['MediaTemplates', 'MediaTemplateResource', 'MediaTemplate',
        'templat-media', 'Templat', 'Templat Media', 'heroicon-o-swatch', 'Media', 21, 'media_editor', true],

    // 16. ProofOfValue — metrik hasil
    ['PovMetrics', 'PovMetricResource', 'PovMetric',
        'metrik-hasil', 'Metrik Hasil', 'Metrik Hasil', 'heroicon-o-chart-bar', 'Intelijen', 14, 'proof_of_value', true],
    ['PovReadings', 'PovReadingResource', 'PovReading',
        'bacaan-hasil', 'Bacaan', 'Bacaan Hasil', 'heroicon-o-book-open', 'Intelijen', 15, 'proof_of_value', false],

    // 17. SalesCopilot — brief prospek & analisis balasan
    ['SalesCopilotProspects', 'SalesCopilotProspectResource', 'Prospect',
        'prospekt-jualan', 'Prospek Jualan', 'Prospek Jualan', 'heroicon-o-sparkles', 'Jualan', 33, 'sales_copilot', false],

    // 18. SignDocs — dokumen & tandatangan
    ['Documents', 'DocumentResource', 'Document',
        'dokumen', 'Dokumen', 'Dokumen', 'heroicon-o-document-text', 'Dokumen', 12, 'sign_docs', true],
    ['Signatures', 'SignatureResource', 'Signature',
        'tandatangan', 'Tandatangan', 'Tandatangan', 'heroicon-o-pencil', 'Dokumen', 13, 'sign_docs', true],
    ['SignTemplates', 'TemplateResource', 'Template',
        'templat-tandatangan', 'Templat', 'Templat Tandatangan', 'heroicon-o-document-duplicate', 'Dokumen', 14, 'sign_docs', true],

    // 19. TeamAI — repositori & aset pasukan
    ['TeamAiRepositories', 'TeamAiRepositoryResource', 'TeamAiRepository',
        'repositori-pasukan', 'Repositori', 'Repositori Pasukan', 'heroicon-o-rectangle-group', 'AI', 10, 'team_ai', true],
    ['TeamAiAssets', 'TeamAiAssetResource', 'TeamAiAsset',
        'aset-pasukan', 'Aset', 'Aset Pasukan', 'heroicon-o-cube-transparent', 'AI', 11, 'team_ai', true],

    // 20. WorkflowStudio — aliran kerja
    ['Workflows', 'WorkflowResource', 'Workflow',
        'aliran-kerja', 'Aliran Kerja', 'Aliran Kerja', 'heroicon-o-puzzle-piece', 'Automasi', 84, 'workflow_studio', true],
    ['WorkflowTemplates', 'WorkflowTemplateResource', 'WorkflowTemplate',
        'templat-aliran', 'Templat Aliran', 'Templat Aliran Kerja', 'heroicon-o-document-duplicate', 'Automasi', 85, 'workflow_studio', true],

    // 21. BLive — host digital, sesi langsung & penstriman
    ['LiveAvatars', 'LiveAvatarResource', 'Avatar',
        'avatar-live', 'Avatar', 'Avatar Live', 'heroicon-o-user-circle', 'Live', 40, 'blive', true],
    ['LiveHostPersonalities', 'LiveHostPersonalityResource', 'HostPersonality',
        'personaliti-hos', 'Personaliti Hos', 'Personaliti Hos', 'heroicon-o-face-smile', 'Live', 41, 'blive', true],
    ['LiveSessions', 'LiveSessionResource', 'LiveSession',
        'sesi-live', 'Sesi Live', 'Sesi Live', 'heroicon-o-video-camera', 'Live', 42, 'blive', false],
    ['LiveStreamDestinations', 'LiveStreamDestinationResource', 'StreamDestination',
        'saluran-penarahan', 'Saluran', 'Saluran Penstriman', 'heroicon-o-signal', 'Live', 43, 'blive', true],
    ['LiveMessages', 'LiveMessageResource', 'LiveMessage',
        'mesej-live', 'Mesej', 'Mesej Live', 'heroicon-o-chat-bubble-left-right', 'Live', 44, 'blive', false],
    ['LiveModerationEvents', 'LiveModerationEventResource', 'ModerationEvent',
        'moderasi-live', 'Moderasi', 'Moderasi Live', 'heroicon-o-shield-check', 'Live', 45, 'blive', false],
    ['LiveRenderJobs', 'LiveRenderJobResource', 'RenderJob',
        'kerja-render', 'Kerja Render', 'Kerja Render', 'heroicon-o-cpu-chip', 'Live', 46, 'blive', false],
    ['LiveConsentRecords', 'LiveConsentRecordResource', 'ConsentRecord',
        'kebenaran-live', 'Kebenaran', 'Kebenaran Live', 'heroicon-o-clipboard-document-check', 'Live', 47, 'blive', false],
    ['LiveMetrics', 'LiveMetricResource', 'LiveMetric',
        'metrik-live', 'Metrik', 'Metrik Live', 'heroicon-o-chart-bar-square', 'Live', 48, 'blive', false],
    ['LiveControls', 'LiveControlResource', 'LiveControl',
        'kawalan-live', 'Kawalan', 'Kawalan Live', 'heroicon-o-adjustments-horizontal', 'Live', 49, 'blive', false],

    // 22. McpStudio — pelayan Model Context Protocol
    ['McpTools', 'McpToolResource', 'McpTool',
        'alat-mcp', 'Alat MCP', 'Alat MCP', 'heroicon-o-wrench-screwdriver', 'Integrasi', 50, 'mcp', true],
    ['McpResources', 'McpResourceResource', 'McpResource',
        'sumber-mcp', 'Sumber MCP', 'Sumber MCP', 'heroicon-o-circle-stack', 'Integrasi', 51, 'mcp', true],
    ['McpPrompts', 'McpPromptResource', 'McpPrompt',
        'prompt-mcp', 'Prompt', 'Prompt MCP', 'heroicon-o-command-line', 'Integrasi', 52, 'mcp', true],
    ['McpSessions', 'McpSessionResource', 'McpSession',
        'sesi-mcp', 'Sesi MCP', 'Sesi MCP', 'heroicon-o-arrow-path', 'Integrasi', 53, 'mcp', false],
    ['McpExecutions', 'McpExecutionResource', 'McpExecution',
        'pelaksanaan-mcp', 'Pelaksanaan', 'Pelaksanaan MCP', 'heroicon-o-play-circle', 'Integrasi', 54, 'mcp', false],
    ['McpAuditLogs', 'McpAuditLogResource', 'McpAuditLog',
        'log-mcp', 'Log Audit', 'Log Audit MCP', 'heroicon-o-clipboard-document-list', 'Integrasi', 55, 'mcp', false],
];

$ditulis = 0;
$dilangkau = [];

foreach ($definisi as $d) {
    [$folder, $kelas, $modelPendek, $slug, $label, $plural, $ikon, $kumpulan, $sort, $ns, $bolehCipta] = $d;

    // ⚠️ Sahkan ikon benar-benar wujud dalam blade-heroicons. Ikon yang
    // tidak sah menyebabkan 500 pada render Filament — semak di sini
    // supaya penjana gagal cepat dan bukannya menapis render.
    $ikonFail = __DIR__.'/../vendor/blade-ui-kit/blade-heroicons/resources/svg/o-'.substr($ikon, strlen('heroicon-o-')).'.svg';
    if (! file_exists($ikonFail)) {
        $ikonBenar = 'heroicon-o-cube';
        $dilangkau[] = "{$kelas} ⚠ ikon tidak sah: {$ikon} → {$ikonBenar}";
        $ikon = $ikonBenar;
    }

    // Cari kelas penuh: App\Modules\<Modul>\Models\<Model>
    // ⚠️ Guna `file_exists`, BUKAN `class_exists` — model boleh gagal dimuat
    // di luar konteks Laravel (trait/contract), jadi semakan fail lebih kukuh.
    $model = null;
    foreach (glob(__DIR__.'/../app/Modules/*/Models/'.$modelPendek.'.php') as $f) {
        // ⚠️ Laluan glob mengandungi `skrip/../` — normalkan DAHULU sebelum
        // memecah, jika tidak nama modul menjadi "skrip/../app/Modules".
        $f = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $f);
        $f = realpath($f) ?: $f;

        // .../app/Modules/<Modul>/Models/<Model>.php
        $modul = basename(dirname(dirname($f)));

        $model = 'App\\Modules\\'.$modul.'\\Models\\'.$modelPendek;
        break;
    }

    if ($model === null && file_exists(__DIR__.'/../app/Models/'.$modelPendek.'.php')) {
        $model = 'App\\Models\\'.$modelPendek;
    }

    // Sahkan model wujud — jangan jana resource yang rosak.
    if ($model === null) {
        $dilangkau[] = "{$kelas} → model {$modelPendek} TIADA";

        continue;
    }

    $dir = $asas.'/'.$folder;
    @mkdir($dir.'/Pages', 0777, true);

    $cipta = $bolehCipta ? 'true' : 'false';

    // Halaman `create` hanya didaftarkan bila resource membenarkan cipta.
    $halamanCipta = $bolehCipta
        ? "            'create' => Pages\\Create{$folder}::route('/create'),"
        : "            // tiada 'create' — dikawal servis domain";

    // Nama pendek untuk rujukan dalam kelas (kerana `use` sudah diimport).
    $modelPendekKelas = substr((string) strrchr($model, '\\'), 1) ?: $model;

    // ⚠️ Kaedah `canCreate()` HANYA dikeluarkan bila resource TIDAK boleh
    // cipta. Jika boleh cipta, biarkan trait `HasModulePermission` mengawal.
    $kaedahCanCreate = $bolehCipta ? '' : <<<'BLOCK'

    /**
     * ⚠️ Resource ini TIDAK boleh dicipta melalui UI — rekod dikawal oleh
     * servis domain (cth. `SaleService`, `CashShiftService`). Menulis
     * `return true` di sini akan memintas trait kebenaran.
     */
    public static function canCreate(): bool
    {
        return false;
    }
BLOCK;

    $resource = <<<PHP
<?php

namespace App\\Filament\\Resources\\{$folder};

use App\\Filament\\Concerns\\HasModulePermission;
use {$model};
use Filament\\Resources\\Resource;
use Filament\\Tables;
use Filament\\Tables\\Table;

/**
 * Resource Filament — {$plural}.
 *
 * Dijana oleh `skrip/jana-resource.php`. Boleh diperkaya dengan borang
 * terperinci tanpa mengubah kontrak kebenaran.
 */
class {$kelas} extends Resource
{
    use HasModulePermission;

    protected static function permissionNamespace(): string
    {
        return '{$ns}';
    }

    protected static ?string \$model = {$modelPendekKelas}::class;

    protected static ?string \$slug = '{$slug}';

    protected static ?string \$navigationIcon = '{$ikon}';

    protected static ?string \$navigationGroup = '{$kumpulan}';

    protected static ?string \$navigationLabel = '{$plural}';

    protected static ?string \$modelLabel = '{$label}';

    protected static ?string \$pluralModelLabel = '{$plural}';

    protected static ?int \$navigationSort = {$sort};
{$kaedahCanCreate}
    public static function table(Table \$table): Table
    {
        return \$table
            ->columns(self::kolum())
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\\Actions\\ViewAction::make(),
                Tables\\Actions\\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    /**
     * Borang generik — medan teks/nombor/tarikh yang biasa.
     *
     * ⚠️ Hanya medan yang BENAR-BENAR wujud pada jadual dimasukkan, supaya
     * resource tidak pecah apabila skema berubah. Ganti dengan borang
     * eksplisit bila keperluan domain jelas.
     */
    public static function form(\\Filament\\Forms\\Form \$form): \\Filament\\Forms\\Form
    {
        \$model = new (static::\$model);
        \$lajur = \\Illuminate\\Support\\Facades\\Schema::getColumnListing(\$model->getTable());

        // Jangan dedah medan sistem.
        \$haram = ['id', 'tenant_id', 'created_at', 'updated_at', 'deleted_at', 'ulid'];

        \$komponen = [];

        foreach (\$lajur as \$l) {
            if (in_array(\$l, \$haram, true)) {
                continue;
            }

            \$label = ucfirst(str_replace('_', ' ', \$l));

            \$komponen[] = match (true) {
                \$l === 'notes', \$l === 'description' => \\Filament\\Forms\\Components\\Textarea::make(\$l)->label(\$label)->rows(2)->columnSpanFull(),
                str_ends_with(\$l, '_at'), str_ends_with(\$l, '_date'), \$l === 'date' => \\Filament\\Forms\\Components\\DateTimePicker::make(\$l)->label(\$label),
                str_starts_with(\$l, 'is_'), str_starts_with(\$l, 'has_') => \\Filament\\Forms\\Components\\Toggle::make(\$l)->label(\$label),
                str_contains(\$l, 'amount'), str_contains(\$l, 'total'), str_contains(\$l, 'price') => \\Filament\\Forms\\Components\\TextInput::make(\$l)->label(\$label)->numeric()->prefix('RM'),
                default => \\Filament\\Forms\\Components\\TextInput::make(\$l)->label(\$label)->maxLength(255),
            };
        }

        return \$form->schema(\$komponen)->columns(2);
    }

    /**
     * Kolum generik: ambil 4-6 medan teks pertama yang ada pada jadual.
     *
     * ⚠️ Dibina secara dinamik supaya resource ini tidak pecah apabila
     * skema berubah. Ganti dengan kolum eksplisit bila borang diperkaya.
     *
     * @return array<int, Tables\\Columns\\Column>
     */
    private static function kolum(): array
    {
        \$model = new (static::\$model);
        \$kolum = [];

        // ⚠️ Urutan PENTING — `array_slice(0, 6)` hanya mengambil 6 pertama.
        // Nombor rujukan (expense_no/invoice_number/order_no/...) mesti
        // mendahului medan hiasan supaya ia sentiasa kelihatan.
        \$medan = [
            'expense_no', 'invoice_number', 'order_no', 'sale_no', 'ticket_no',
            'code', 'number', 'no',
            'name', 'title', 'subject',
            'vendor_name', 'customer_name', 'supplier_name',
            'status', 'total_amount', 'grand_total', 'total', 'amount',
            'date', 'issue_date', 'created_at',
        ];

        \$lajur = \\Illuminate\\Support\\Facades\\Schema::getColumnListing(\$model->getTable());

        foreach (\$medan as \$m) {
            if (! in_array(\$m, \$lajur, true)) {
                continue;
            }

            \$kolum[] = Tables\\Columns\\TextColumn::make(\$m)
                ->label(ucfirst(str_replace('_', ' ', \$m)))
                ->searchable()
                ->sortable()
                ->limit(40);
        }

        if (\$kolum === []) {
            \$kolum[] = Tables\\Columns\\TextColumn::make('id')->label('ID')->sortable();
        }

        return array_slice(\$kolum, 0, 6);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\\List{$folder}::route('/'),
{$halamanCipta}
            'view' => Pages\\View{$folder}::route('/{record}'),
            'edit' => Pages\\Edit{$folder}::route('/{record}/edit'),
        ];
    }
}
PHP;

    file_put_contents($dir.'/'.$kelas.'.php', $resource);

    // ===== Halaman Senarai =====
    $aksiCipta = $bolehCipta
        ? '        return [\\Filament\\Actions\\CreateAction::make()];'
        : "        // Tiada \"Cipta\" — rekod ini dikawal oleh servis domain.\n        return [];";

    file_put_contents($dir."/Pages/List{$folder}.php", <<<PHP
<?php

namespace App\\Filament\\Resources\\{$folder}\\Pages;

use App\\Filament\\Resources\\{$folder}\\{$kelas};
use Filament\\Resources\\Pages\\ListRecords;

class List{$folder} extends ListRecords
{
    protected static string \$resource = {$kelas}::class;

    protected function getHeaderActions(): array
    {
{$aksiCipta}
    }
}
PHP);

    // ===== Halaman Lihat =====
    file_put_contents($dir."/Pages/View{$folder}.php", <<<PHP
<?php

namespace App\\Filament\\Resources\\{$folder}\\Pages;

use App\\Filament\\Resources\\{$folder}\\{$kelas};
use Filament\\Resources\\Pages\\ViewRecord;

class View{$folder} extends ViewRecord
{
    protected static string \$resource = {$kelas}::class;
}
PHP);

    // ===== Halaman Edit =====
    file_put_contents($dir."/Pages/Edit{$folder}.php", <<<PHP
<?php

namespace App\\Filament\\Resources\\{$folder}\\Pages;

use App\\Filament\\Resources\\{$folder}\\{$kelas};
use Filament\\Resources\\Pages\\EditRecord;

class Edit{$folder} extends EditRecord
{
    protected static string \$resource = {$kelas}::class;

    protected function getRedirectUrl(): string
    {
        return \$this->getResource()::getUrl('index');
    }
}
PHP);

    // ===== Halaman Cipta =====
    file_put_contents($dir."/Pages/Create{$folder}.php", <<<PHP
<?php

namespace App\\Filament\\Resources\\{$folder}\\Pages;

use App\\Filament\\Resources\\{$folder}\\{$kelas};
use Filament\\Resources\\Pages\\CreateRecord;

class Create{$folder} extends CreateRecord
{
    protected static string \$resource = {$kelas}::class;

    protected function getRedirectUrl(): string
    {
        return \$this->getResource()::getUrl('index');
    }
}
PHP);

    $ditulis++;
}

echo "Resource ditulis: {$ditulis}".PHP_EOL;

if ($dilangkau !== []) {
    echo PHP_EOL.'DILANGKAU (model tiada):'.PHP_EOL;
    foreach ($dilangkau as $d) {
        echo '  - '.$d.PHP_EOL;
    }
}
