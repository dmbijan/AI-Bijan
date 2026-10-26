# AI Bijan Architecture v2.2 — Master Consolidated Blueprint

> **Status:** Consolidated master blueprint  
> **Base:** AI Bijan Architecture v2.1, retained in full  
> **Extension:** Universal SME Tools & Industry Packs v2.2  
> **Purpose:** One platform for diverse Malaysian businesses, with a shared core, specialised industry packs, low-cost phased delivery, and controlled AI autonomy.

## Master document structure

1. **Architecture v2.1 — Original blueprint:** preserved below without rewriting.
2. **v2.2 — Universal SME Tools & Industry Packs:** added after the original v2.1 content.
3. **Implementation rule:** this document defines intended product scope, not proof that each module, integration, or compliance requirement is already implemented or production-ready.

---

# AI Bijan Command Center

## Dokumen Induk Seni Bina Produk dan Teknikal --- Architecture v2

**Tarikh rujukan:** 17 Oktober 2026\
**Pemilik konsep:** Bijan\
**Tagline:** *Your AI Team, in the Room.*

------------------------------------------------------------------------

## 1. Ringkasan eksekutif

AI Bijan ialah platform SaaS multi-tenant untuk membantu usahawan dan
PKS mengurus operasi melalui satu ruang kerja, pasukan ejen AI,
automasi, saluran jualan, kewangan, pemasaran dan business intelligence.

Platform menggunakan satu backend Laravel modular monolith, satu
identiti pengguna global, satu lapisan API berversi dan modul yang
diaktifkan mengikut pelan langganan setiap merchant. Web, aplikasi mudah
alih, panel HQ dan ejen AI menggunakan API yang sama. AI tidak menulis
terus ke pangkalan data; ia menggunakan tools/API dengan kebenaran
terhad dan setiap tindakan direkod.

**Keputusan seni bina utama** 1. Satu platform dan satu codebase
backend; bukan 18 aplikasi berasingan. 2. Modular monolith dahulu;
microservices hanya apabila keperluan operasi dan skala membuktikannya.
3. Setiap modul memiliki data dan kontraknya sendiri. 4. Modul
berkomunikasi melalui kontrak awam dan domain events, bukan query terus
ke jadual modul lain. 5. API-first dengan OpenAPI sebagai kontrak antara
backend, Next.js, Flutter, integrasi dan AI. 6. Keselamatan tenant,
audit dan kawalan tindakan AI dibina sejak awal. 7. Google Ads dan Meta
Ads dimasukkan sebagai integrasi rasmi dalam B-Marketing Ads Manager. 8.
Infrastruktur mahal seperti ClickHouse dan Qdrant diperkenalkan apabila
kes penggunaan memerlukannya.

## 2. Visi produk dan pengalaman

### 2.1 Pengalaman utama

-   **AI Team:** pasukan AI yang mempunyai peranan, matlamat, tools,
    memori dan had kebenaran.
-   **B-Boardroom:** perbincangan pelbagai ejen untuk menilai isu
    daripada sudut pemasaran, kewangan, operasi dan jualan.
-   **B-Hub:** ruang kerja bersepadu untuk data, aktiviti, tugasan dan
    integrasi.
-   **B-Flow / B-Trigger:** automasi berasaskan peristiwa, syarat dan
    tindakan.
-   **B-Scout / B-Intel:** penyelidikan pasaran, trend, pesaing dan
    peluang tempatan.
-   **B-Compliance:** rujukan pematuhan Malaysia yang bersumber dan
    bertarikh.
-   **Modul operasi:** POS, laman web, pautan jualan, marketplace,
    booking, kewangan, invois, HRMS dan logistik.

### 2.2 Pengguna sasaran

-   Usahawan solo dan freelancer.
-   PKS Malaysia.
-   Agensi digital dan penyedia perkhidmatan.
-   Syarikat sederhana yang memerlukan kawalan akses, audit dan
    integrasi.

### 2.3 Model komersial

-   Langganan bulanan/tahunan mengikut tenant.
-   Had penggunaan AI, automasi, pengguna, saluran dan integrasi
    mengikut pelan.
-   Modul tambahan berbayar.
-   Caj penggunaan bagi tindakan tertentu apabila sesuai.
-   White-label/partner plan sebagai fasa komersial kemudian.

------------------------------------------------------------------------

## 3. Pasukan AI maya --- AI Team Dynamics

### 3.1 Peranan ejen

  ------------------------------------------------------------------------
  Ejen                   Tanggungjawab          Contoh tools
  ---------------------- ---------------------- --------------------------
  AI CEO / Chief of      Menyelaraskan          create_task, assign_agent,
  Staff                  matlamat, delegasi dan summarize_workspace
                         ringkasan eksekutif    

  AI Executive Assistant Kalendar, e-mel, nota, calendar.read,
                         susulan dan jadual     email.draft, task.create

  AI CMO                 Strategi pemasaran,    campaign.draft, ads.read,
                         kandungan, kempen dan  content.generate
                         analisis saluran       

  AI CFO                 Aliran tunai, bajet,   finance.read,
                         invois, margin dan     report.generate,
                         amaran risiko          invoice.draft

  AI COO                 SOP, operasi,          inventory.read,
                         inventori, tugasan dan task.assign,
                         kapasiti               operations.report

  AI CSO                 Leads, pipeline        crm.read, lead.update,
                         jualan, follow-up dan  sales.report
                         affiliate              

  AI CHRO                Kakitangan, jadual,    hr.read, shift.draft,
                         cuti, onboarding dan   leave.review
                         HR                     

  AI Customer Support    Soalan pelanggan, FAQ, knowledge.search,
                         pesanan dan eskalasi   conversation.reply_draft

  AI Ads Specialist      Google Ads, Meta Ads   ads.performance.read,
                         dan pengoptimuman      campaign.draft,
                         kempen                 campaign.publish\*

  AI Researcher / Scout  Trend, pesaing dan     web.research,
                         sumber maklumat        competitor.compare

  AI Analyst             Analisis KPI, ramalan  analytics.query,
                         dan insight            forecast.generate
  ------------------------------------------------------------------------

`*` Tindakan menerbitkan kempen, mengubah bajet atau membuat perubahan
besar mesti melalui polisi kelulusan yang ditetapkan.

### 3.2 Konfigurasi ejen

Setiap ejen mempunyai: - ID dan versi konfigurasi. - Peranan, matlamat
dan skop tugas. - Senarai tools yang dibenarkan. - Had kos, token, masa
dan bilangan tindakan. - Polisi data yang boleh dibaca. - Memori tenant
yang dibenarkan. - Polisi kelulusan. - Log input/output, tool calls,
keputusan dan ralat. - Penilaian kualiti serta mekanisme untuk
menghentikan atau mengulang tugas.

### 3.3 B-Boardroom

Aliran cadangan: 1. Pemilik mengemukakan persoalan atau keputusan. 2.
Chief of Staff menentukan ejen yang relevan. 3. Ejen menerima konteks
minimum yang diperlukan. 4. Setiap ejen mengemukakan dapatan, andaian,
bukti dan risiko. 5. Orchestrator mengesan percanggahan dan meminta
semakan jika perlu. 6. AI CEO/Chief of Staff menyediakan ringkasan
keputusan dan pilihan. 7. Pemilik memilih tindakan; sistem merekod
keputusan dan susulan.

Boardroom tidak boleh menganggap persetujuan ejen sebagai bukti
kebenaran. Dapatan perlu membezakan fakta, anggaran dan andaian.

------------------------------------------------------------------------

## 4. B-Hub dan integrasi workspace

B-Hub ialah paparan bersepadu, bukan tempat menyimpan salinan data
secara tidak terkawal. Setiap integrasi mempunyai connector, skop OAuth,
status sync, log ralat dan pilihan untuk menyahhubung.

### Integrasi yang dirancang

-   Gmail dan Google Calendar.
-   WhatsApp Business Platform / Cloud API.
-   Facebook Pages, Instagram professional accounts dan Meta Marketing
    API.
-   Google Ads API.
-   TikTok dan TikTok Ads API apabila akses dan kelayakan tersedia.
-   Shopee Open Platform apabila akaun dan kelulusan API membenarkan.
-   Payment gateways dan bank feeds melalui penyedia yang dibenarkan.
-   Telegram, e-mel, SMS, push notifications.
-   Google Maps.
-   LHDN MyInvois.
-   Kurier atau agregator logistik.

**Nota integrasi:** kemampuan API, kelulusan aplikasi, skop data, had
kadar dan syarat komersial berbeza mengikut penyedia. Sistem mesti
memaparkan integrasi sebagai `available`, `connected`,
`needs_reauthorization`, `limited` atau `unavailable`, bukannya
menjanjikan akses yang belum disahkan.

### Model data connector

-   `integrations`
-   `integration_accounts`
-   `integration_credentials` (token disulitkan; rahsia tidak
    dipulangkan kepada klien)
-   `integration_sync_runs`
-   `integration_webhook_endpoints`
-   `integration_events`
-   `integration_errors`
-   `integration_scopes`

Token OAuth disimpan terenkripsi, mempunyai proses refresh/revoke dan
tidak dimasukkan ke log biasa.

------------------------------------------------------------------------

## 5. B-Flow / B-Trigger --- enjin automasi

### 5.1 Model workflow

Workflow terdiri daripada: - Trigger: domain event, webhook, jadual masa
atau tindakan manual. - Condition: penapis, nilai, status, had dan
cabang. - Action: panggilan API dalaman, connector atau tugas AI. -
Approval: kelulusan manusia apabila diperlukan. - Retry/error policy:
retry terhad, backoff, dead-letter queue. - Audit: input, output, masa,
pemilik dan keputusan.

### 5.2 Contoh

**Pesanan Shopee diterima** 1. Connector mengesahkan webhook atau
mengambil pesanan melalui API. 2. B-Trigger mengesahkan tandatangan,
timestamp dan idempotency key. 3. B-Commerce mengemas kini pesanan
melalui API dalaman. 4. B-Inventory memproses perubahan stok melalui
kontrak domain yang ditetapkan. 5. B-Invoicing menyediakan dokumen
jualan yang sesuai. 6. B-Omnichannel menghantar mesej pelanggan jika
dibenarkan. 7. B-Analytics menerima event untuk pelaporan.

Setiap langkah mesti selamat untuk diproses semula. Jangan menganggap
penghantaran webhook hanya berlaku sekali.

### 5.3 Perlindungan automasi

-   Had tindakan per workflow dan per tenant.
-   Had belanja iklan dan had kewangan.
-   Dry-run / preview sebelum pengaktifan.
-   Approval gates.
-   Kill switch tenant dan global.
-   Retry yang tidak menghasilkan transaksi berganda.
-   Pemantauan kegagalan dan dead-letter queue.

------------------------------------------------------------------------

## 6. B-Scout, B-Intel dan B-Compliance

### B-Scout --- Local Market Intelligence

-   Trend carian dan kandungan yang tersedia melalui sumber sah.
-   Analisis topik mengikut kawasan atau demografi jika data platform
    menyokongnya.
-   Ringkasan sentimen dengan sampel dan had data dinyatakan.
-   Alert untuk lonjakan permintaan atau topik.

### B-Intel --- Competitor Intelligence

-   Perbandingan harga, promosi, katalog dan penawaran pesaing daripada
    sumber yang boleh diakses.
-   Pemantauan perubahan harga dengan timestamp dan URL sumber.
-   Analisis jurang produk/perkhidmatan.
-   Elakkan bypass kawalan akses, scraping yang melanggar syarat atau
    mengumpul data peribadi tanpa asas sah.

### B-Opportunity

-   Gabungkan data jualan tenant, trend dan pemerhatian pesaing.
-   Paparkan tahap keyakinan, data yang hilang dan andaian.
-   Berikan tindakan yang boleh diuji, bukan janji bahawa jualan pasti
    meningkat.

### B-Compliance

-   Rujukan LHDN MyInvois, PDPA, panduan halal dan peraturan berkaitan.
-   Setiap kandungan mempunyai sumber rasmi, tarikh disemak dan versi.
-   Bezakan maklumat umum daripada nasihat undang-undang/perakaunan.
-   Hantar isu kompleks kepada profesional berkelayakan.
-   Jadual kemas kini dan amaran apabila sumber rasmi berubah.

------------------------------------------------------------------------

## 7. B-Marketing Ads Manager --- Google Ads dan Meta Ads

**Google Ads dan Meta Ads ialah keperluan eksplisit platform dan mesti
menjadi bahagian rasmi B-Marketing.** Ia bukan sekadar laporan; sasaran
produk ialah analisis, penciptaan draf, penerbitan yang dibenarkan,
pemantauan dan pengoptimuman dengan kawalan manusia.

### 7.1 Google Ads

Sokongan dirancang, tertakluk kepada kelayakan akaun dan Google Ads
API: - Account dan campaign overview. - Search, Display, YouTube dan
Performance Max mengikut sokongan API/jenis kempen. - Bajet, status,
impressions, clicks, CTR, CPC, conversions, CPA dan conversion
value/ROAS apabila tracking tersedia. - Keyword/search-term insights dan
negative keyword workflow apabila endpoint dan jenis kempen
menyokongnya. - Draf campaign/ad group/ads, semakan polisi dan
penerbitan melalui API. - Change history, budget alerts dan rollback
plan apabila boleh.

### 7.2 Meta Ads

Sokongan dirancang, tertakluk kepada app review, permissions dan akses
Marketing API: - Ad account, campaign, ad set, ads dan insights. -
Facebook dan Instagram placements mengikut sokongan platform. - Reach,
impressions, clicks, CTR, CPC, CPM, leads, purchases, CPA dan ROAS
apabila event tracking tersedia. - Audience dan creative reporting
mengikut skop yang dibenarkan. - Draf copy, variasi creative dan
struktur kempen. - Penerbitan atau perubahan kempen hanya dengan skop
yang sesuai dan kelulusan.

### 7.3 Fungsi Ads Manager bersama

-   Satu dashboard merentas Google dan Meta.
-   Normalisasi metrik dengan definisi yang jelas.
-   Perbandingan spend, conversions, CPA dan ROAS.
-   Bajet harian/bulanan dan amaran.
-   UTM conventions dan conversion tracking status.
-   Creative library dan versi copy.
-   Cadangan AI dengan bukti, jangkaan kesan dan risiko.
-   Change log dan paparan perubahan sebelum/selepas.
-   Campaign naming conventions dan laporan eksport.

### 7.4 Aliran AI Ads

1.  Pemilik menetapkan objektif, kawasan, pelanggan sasaran, bajet dan
    had.
2.  AI CMO menyemak produk, laman pendaratan, penawaran dan data
    sejarah.
3.  AI Ads Specialist menyediakan strategi dan draf kempen.
4.  Validator menyemak bajet, tracking, polisi platform, destinasi dan
    parameter.
5.  Pemilik meluluskan penerbitan atau perubahan yang ditetapkan.
6.  Connector menghantar perubahan melalui API rasmi.
7.  Sistem memantau prestasi dan menghasilkan cadangan.
8.  Perubahan bajet, pause, publish atau tindakan berimpak tinggi
    tertakluk pada polisi kelulusan.

### 7.5 Guardrails pengiklanan

-   Had belanja keras per akaun, kempen dan tenant.
-   Had perubahan bajet automatik dalam tempoh tertentu.
-   Tiada penerbitan atau kenaikan bajet di luar mandat.
-   Approval untuk kempen baharu, perubahan sasaran penting dan
    perubahan bajet besar.
-   Rekod actor, approver, nilai sebelum/selepas dan API response.
-   Amaran jika conversion tracking rosak atau data tidak lengkap.
-   AI tidak menjamin kelulusan iklan atau hasil jualan.
-   Akaun iklan setiap merchant kekal terasing; token tidak dikongsi
    antara tenant.

### 7.6 Entiti tambahan

-   `ad_accounts`
-   `ad_campaigns`
-   `ad_creatives`
-   `ad_performance_snapshots`
-   `ad_change_requests`
-   `ad_budget_policies`
-   `ad_conversion_sources`
-   `ad_attribution_events`

Data prestasi boleh disalin ke stor analitik; sumber transaksi dan
konfigurasi sebenar kekal melalui connector dan API yang berautoriti.

------------------------------------------------------------------------

## 8. Modul produk B-

18 modul perniagaan di bawah ialah katalog fungsi. Modul platform teras
seperti Identity, Tenancy, Billing, Pay, Notify, Audit dan Integrations
ialah shared capabilities, bukan aplikasi tambahan yang perlu dijual
secara berasingan.

  -----------------------------------------------------------------------
  Modul                  Pemilikan domain dan   Event contoh
                         entiti                 
  ---------------------- ---------------------- -------------------------
  B-Virtual Office       Agent, Task, Board,    `TaskCompleted`
                         Schedule               

  B-HRMS                 Employee, Shift,       `PayrollApproved`
                         Leave, Payroll         

  B-Sign & Docs          Document, Template,    `DocumentSigned`
                         Signature              

  B-POS                  Register, Sale,        `SaleCompleted`
                         CashShift, Receipt     

  B-Site                 Site, Page, Theme,     `SitePublished`
                         Domain                 

  B-Link                 LinkPage, DigitalCard, `LinkClicked`
                         Click                  

  B-Marketplace          Listing, Cart, Review; `OrderPlaced`
                         order melalui Commerce 
                         Core                   

  B-Affiliate            Affiliate,             `CommissionEarned`
                         ReferralCode,          
                         Commission             

  B-Loyalty              Member, PointsLedger,  `PointsEarned`,
                         Reward, Tier           `RewardRedeemed`

  B-Omnichannel          Conversation, Channel, `MessageReceived`
                         Message                

  B-Booking              Service, Slot,         `AppointmentBooked`
                         Appointment, Provider  

  B-Marketing            Campaign, Segment,     `CampaignSent`,
                         Voucher, Ads Manager   `AdChangeApproved`

  B-Finance              Account, JournalEntry, `JournalPosted`
                         BankTransaction        

  B-Invoicing            Quote, Invoice,        `InvoiceIssued`,
                         CreditNote, MyInvois   `InvoicePaid`
                         status                 

  B-Logistics            Warehouse, StockMove,  `StockLow`,
                         Shipment               `ShipmentDelivered`

  B-Procurement          Supplier,              `PurchaseOrderApproved`
                         PurchaseOrder,         
                         SupplierBill           

  B-Analytics            Metric, Report,        Data products / metric
                         Dashboard              refresh

  B-Brain Hub            Insight, Forecast,     `InsightGenerated`
                         Recommendation         
  -----------------------------------------------------------------------

### Modul teras tambahan

-   **Core Identity & Tenancy:** akaun, organisasi, membership dan
    akses.
-   **Core Commerce:** pelanggan, produk, varian, cart/order dan order
    items yang dikongsi.
-   **B-Pay:** payment intent, payment, refund, settlement dan
    rekonsiliasi.
-   **B-Notify:** e-mel, WhatsApp, SMS, push dan template.
-   **B-Flow / B-Trigger:** workflow runtime dan event triggers.
-   **B-Intel / B-Scout / B-Compliance:** local intelligence dan
    compliance knowledge.
-   **B-Ads Manager:** operasi iklan merentas platform di dalam
    B-Marketing.

### Pemilikan data yang wajib

-   Core Commerce memiliki `customers`, `products`, `product_variants`,
    `orders`, `order_items`.
-   B-Marketplace memiliki listing dan pengalaman marketplace, bukan
    salinan order utama.
-   B-Pay memiliki rekod pembayaran/refund; tidak menganggap order
    sebagai payment.
-   B-Finance memiliki akaun perakaunan dan jurnal.
-   B-Invoicing memiliki dokumen invois dan status e-Invois.
-   B-Logistics memiliki stok fizikal, pergerakan stok dan penghantaran.
-   B-Marketing memiliki kempen pemasaran serta konfigurasi integrasi
    ads, bukan sumber tunggal transaksi jualan.

------------------------------------------------------------------------

## 9. Seni bina teknikal

### 9.1 Stack

  -----------------------------------------------------------------------
  Lapisan                 Pilihan                 Catatan
  ----------------------- ----------------------- -----------------------
  Backend                 Laravel, PHP 8.3+ atau  Modular monolith dan
                          versi yang disokong     REST API
                          ketika implementasi     

  Admin HQ                Filament                Platform operations dan
                                                  admin merchant

  Web                     Next.js + TypeScript +  Dashboard, B-Site,
                          Tailwind                B-Link dan portal

  Mobile                  Flutter                 Aplikasi
                                                  merchant/pelanggan jika
                                                  diperlukan

  Database transaksi      MySQL 8.x / versi       Source of truth
                          disokong                transaksi

  Cache/queue             Redis + Laravel Horizon Kerja latar belakang
                                                  dan cache

  Realtime                Laravel Reverb          Status, chat dan
                                                  notifikasi langsung

  Search                  Meilisearch             Carian katalog dan
                                                  pelanggan

  Object storage          Cloudflare R2 atau AWS  Fail, gambar, dokumen
                          S3                      

  AI Gateway              Lapisan dalaman         Claude API sebagai
                          provider-agnostic       provider awal; boleh
                                                  tambah model lain

  Vector search           Qdrant apabila          RAG dokumen tenant
                          diperlukan              

  Analytics               MySQL dahulu;           Laporan berat dan event
                          ClickHouse apabila      analytics
                          perlu                   

  Monitoring              Sentry, Laravel Pulse,  Ralat, prestasi dan
                          uptime checks           availability

  API contract            OpenAPI                 Kontrak klien dan
                                                  integrasi
  -----------------------------------------------------------------------

Versi tepat setiap dependency perlu dipatok dalam lockfiles dan diuji
untuk keserasian sebelum produksi. Jangan memilih versi berdasarkan
angka minimum sahaja.

### 9.2 Lapisan

1.  **Clients:** Next.js, Flutter, Filament Admin, AI agent runtime.
2.  **API edge:** authentication, rate limiting, tenant resolution,
    request validation.
3.  **Application modules:** tindakan perniagaan dan use cases.
4.  **Domain layer:** entiti, value objects, invariants dan domain
    events.
5.  **Infrastructure adapters:** payment, ads, messaging, storage, AI
    providers.
6.  **Data layer:** MySQL, Redis, object storage, search dan analytics.
7.  **Operations:** audit, metrics, traces, backups dan incident
    response.

### 9.3 Struktur repositori dicadangkan

``` text
ai-bijan/
  apps/
    web/                    # Next.js
    mobile/                 # Flutter
  backend/
    app/                    # Bootstrap, shared middleware
    modules/
      Core/
        Identity/
        Tenancy/
        Billing/
        Commerce/
        Pay/
        Notify/
        Integrations/
        Audit/
        Approval/
      Ops/
        VirtualOffice/
        Hrms/
        SignDocs/
      Sales/
        Pos/
        Site/
        Link/
        Marketplace/
        Affiliate/
      Customer/
        Loyalty/
        Omnichannel/
        Booking/
        Marketing/
      Finance/
        Ledger/
        Invoicing/
        Logistics/
        Procurement/
      Insight/
        Analytics/
        BrainHub/
        Scout/
        CompetitorIntel/
        Compliance/
      Ads/
        AdsManager/
        GoogleAds/
        MetaAds/
      Automation/
        Flow/
        Trigger/
    tests/
  infra/
    docker/
    deploy/
    monitoring/
  docs/
    architecture/
    api/
    runbooks/
```

Struktur sebenar boleh menggunakan package modular Laravel yang
dipersetujui pasukan. Nama folder tidak menggantikan sempadan domain
yang diuji.

------------------------------------------------------------------------

## 10. Multi-tenancy, identity dan access control

### 10.1 Identiti

-   Satu jadual `users` global.
-   `tenants` mewakili merchant/organisasi.
-   `tenant_user` mewakili membership dan status keahlian.
-   Seorang pengguna boleh mempunyai peranan berbeza dalam tenant
    berlainan.
-   Sanctum untuk sesi web/mobile; OAuth2 melalui Passport atau
    authorization server yang sesuai untuk pihak ketiga.

### 10.2 Peranan

Peranan awal: `owner`, `admin`, `manager`, `staff`, `vendor`,
`customer`, `ai_agent`, `platform_support`.

Gunakan permission granular dan tenant-scoped. Role bukan satu-satunya
pemeriksaan; setiap tindakan juga perlu polisi objek, status dan skop
organisasi.

### 10.3 Tenant resolution

-   Subdomain yang dipetakan secara sah, atau tenant context yang
    disahkan.
-   `X-Tenant` tidak boleh dipercayai semata-mata kerana hadir dalam
    header.
-   Backend mengesahkan pengguna ialah ahli tenant yang diminta.
-   Queue job, scheduled task, file URL, search query dan AI tool mesti
    membawa tenant context eksplisit.

### 10.4 Pengasingan

-   `tenant_id` pada setiap jadual yang menyimpan data tenant.
-   Global scopes sebagai kemudahan, bukan satu-satunya kawalan.
-   Policy checks pada setiap endpoint.
-   Composite indexes dan, jika sesuai, composite foreign keys untuk
    mengurangkan rujukan silang tenant.
-   Ujian negatif untuk IDOR dan kebocoran tenant.
-   Cache keys, storage paths, vector namespaces dan analytics filters
    mesti memasukkan tenant scope.

### 10.5 Langganan

Jadual: `plans`, `plan_features`, `subscriptions`, `tenant_modules`,
`usage_records`, `billing_events`.

Middleware dan domain services memeriksa status langganan, entitlement
dan kuota. UI menyembunyikan modul yang tidak aktif, tetapi kawalan
sebenar tetap di backend.

------------------------------------------------------------------------

## 11. Reka bentuk database

### 11.1 Jadual utama

-   `tenants`
-   `users`
-   `tenant_user`
-   `plans`, `subscriptions`, `tenant_modules`
-   `customers`
-   `products`, `product_variants`
-   `orders`, `order_items`
-   `payments`, `refunds`
-   `wallet_ledger` atau ledger khusus mengikut jenis dana
-   `outbox_events`
-   `activity_log`
-   `files`
-   `integrations`, `integration_accounts`
-   `approval_requests`
-   `agent_runs`, `agent_tool_calls`
-   `workflows`, `workflow_runs`, `workflow_steps`
-   `usage_records`

### 11.2 Peraturan data

-   Gunakan ULID atau UUID yang konsisten; ID bukan kawalan akses.
-   Wang sebagai integer dalam unit minor (contohnya sen) bersama ISO
    currency code.
-   Masa disimpan dalam UTC; dipaparkan mengikut zon waktu tenant, lalai
    `Asia/Kuala_Lumpur`.
-   Indeks disusun mengikut pola query sebenar; jadual tenant lazimnya
    bermula dengan `tenant_id`.
-   Soft delete hanya jika semantik domain dan polisi retention
    membenarkannya.
-   Data kewangan/ledger bersifat append-only dengan reversal entries,
    bukan mengubah sejarah tanpa jejak.
-   PII mempunyai polisi retention, export dan deletion yang terkawal.
-   Gunakan transaksi database untuk invariants yang mesti konsisten.

### 11.3 Nota penting tentang `customers`

"Rekod pelanggan dikongsi" bermaksud dikongsi dalam tenant yang sama.
Jangan dedahkan satu profil pelanggan merentas merchant tanpa asas
pemprosesan, persetujuan dan model identiti yang jelas. Global user
identity dan tenant customer profile ialah konsep berbeza.

------------------------------------------------------------------------

## 12. Domain events dan transactional outbox

### 12.1 Konvensyen

Nama event dalam bentuk lampau: `OrderPaid`, `InvoiceIssued`,
`AppointmentBooked`. Event membawa ID, tenant ID, timestamp, schema
version dan data minimum yang diperlukan.

### 12.2 Outbox

1.  Transaksi perniagaan mengubah rekod domain dan memasukkan event ke
    `outbox_events` dalam transaksi database yang sama.
2.  Worker membaca event belum dihantar.
3.  Event diterbitkan kepada queue.
4.  Consumer merekod idempotency key dan memproses event sekali secara
    logik.
5.  Kegagalan di-retry dengan backoff; selepas had tertentu, masuk
    dead-letter queue.
6.  Operator boleh replay event dengan audit.

### 12.3 Contoh `OrderPaid`

-   B-Pay mengesahkan pembayaran dan menerbitkan event.
-   B-Finance memproses jurnal melalui kontraknya.
-   B-Loyalty menambah mata jika order memenuhi syarat.
-   B-Logistics mencipta penghantaran jika diperlukan.
-   B-Analytics menerima salinan event untuk metrik.

Modul penerima tidak mengakses jadual pemilik event secara terus. Semua
consumer mesti idempotent.

------------------------------------------------------------------------

## 13. API dan integrasi

### 13.1 API

-   REST API berversi: `/api/v1`.
-   OpenAPI dijana dan diuji dalam CI.
-   Resource, pagination, error envelope dan idempotency conventions
    konsisten.
-   Rate limit mengikut token, tenant, endpoint dan pelan.
-   API luar menggunakan OAuth2 scopes dan lifecycle client credentials
    yang jelas.
-   Webhooks bertandatangan, timestamped dan mempunyai perlindungan
    replay.

### 13.2 Gateway pembayaran

Driver yang dirancang: Billplz, ToyyibPay, iPay88, Stripe dan DuitNow QR
melalui penyedia yang sesuai. Sokongan sebenar bergantung pada kontrak
merchant, dokumentasi API, jenis transaksi dan kelayakan.

Jangan menyimpan nombor kad. Gunakan hosted checkout atau token gateway.
Pembayaran, refund, settlement dan rekonsiliasi mesti idempotent serta
boleh diaudit.

### 13.3 Kurier

J&T, Pos Laju, Lalamove atau agregator seperti EasyParcel, tertakluk
kepada API/akaun. Setiap driver menterjemah status penyedia kepada
status dalaman standard.

### 13.4 Messaging

WhatsApp Cloud API, e-mel, SMS, push, Telegram, Facebook dan Instagram
mengikut API/permission. Patuhi consent, opt-out, template approval dan
polisi platform.

### 13.5 LHDN MyInvois

B-Invoicing mengurus penyediaan dokumen, submission, status,
UUID/validation response, rejection dan pembatalan mengikut aliran rasmi
yang berkenaan. Simpan audit dan rekod sumber. Keperluan dan jadual
berkuat kuasa perlu disahkan dengan dokumentasi rasmi terkini.

------------------------------------------------------------------------

## 14. Lapisan AI dan AI Gateway

### 14.1 AI Gateway

-   Satu pintu untuk provider LLM.
-   Model routing mengikut tugas, latency, kualiti dan kos.
-   Log penggunaan dan kos mengikut tenant/agent.
-   Budget caps dan rate limits.
-   Redaction/minimization PII sebelum menghantar prompt kepada
    provider.
-   Retry, timeout, fallback dan circuit breaker.
-   Prompt/template versioning.
-   Evaluations dan regression tests.
-   Provider adapters supaya Claude atau model lain boleh ditukar tanpa
    mengubah modul domain.

### 14.2 Agent runtime

Ejen tidak diberi akses SQL atau rahsia database. Setiap tool memanggil
endpoint/API yang disahkan, dengan input schema, permission check,
timeout dan audit.

### 14.3 B-Memory dan RAG

-   Memori profil ejen, keputusan organisasi dan pengetahuan merchant
    disimpan secara berasingan.
-   Dokumen tenant diindeks untuk retrieval dengan metadata tenant dan
    access policy.
-   Qdrant collection/namespace diasingkan mengikut tenant atau
    menggunakan strategi partitioning yang terbukti selamat.
-   Retrieval mesti memeriksa hak akses dokumen sebelum kandungan
    dimasukkan ke konteks model.
-   Prompt injection daripada dokumen atau web dianggap input tidak
    dipercayai.
-   Tetapkan retention, delete propagation dan rebuild index.

### 14.4 AI governance

-   Least privilege.
-   Human approval untuk tindakan sensitif.
-   Tool allowlists dan parameter validation.
-   Had per-run dan per-tenant.
-   Audit input, output, tools, keputusan dan approver.
-   Data tenant tidak boleh dicampur.
-   AI mesti menyatakan ketidakpastian dan sumber bagi jawapan fakta.
-   Tindakan yang tidak boleh diundur memerlukan pengesahan khusus.

------------------------------------------------------------------------

## 15. Keselamatan, privasi dan pematuhan

1.  HTTPS/TLS untuk semua sambungan.
2.  Encrypt secrets dan PII sensitif at rest; urus kunci melalui secret
    manager/KMS apabila tersedia.
3.  2FA/MFA wajib untuk owner, admin dan akses platform berprivilege.
4.  Login throttling, session security, token rotation dan revocation.
5.  Tenant isolation tests untuk API, queues, cache, storage, search,
    analytics dan AI.
6.  Audit log append-oriented dengan actor, tenant, action, resource,
    timestamp, request ID dan approval.
7.  Backup automatik, point-in-time recovery jika disokong, serta
    latihan restore.
8.  Retention/deletion policy untuk PII dan data log.
9.  Incident response plan, breach assessment dan notification workflow.
10. PDPA: notis, tujuan pemprosesan, akses/pembetulan, retention,
    keselamatan dan kewajipan yang berkenaan; semak pindaan serta garis
    panduan terkini dengan penasihat undang-undang.
11. e-Invois: implementasi berpandukan dokumentasi rasmi LHDN yang
    terkini.
12. Kad pembayaran: kurangkan skop PCI dengan hosted/tokenized gateway
    dan jangan simpan PAN/CVV.
13. Vendor review untuk LLM, analytics, cloud dan connector termasuk
    lokasi data, subprocessor dan retention.

Dokumen ini ialah reka bentuk teknikal, bukan pendapat undang-undang
atau pengesahan pematuhan.

------------------------------------------------------------------------

## 16. Admin HQ dan operasi merchant

### Admin HQ (Filament)

-   Tenant/merchant management.
-   Pelan, langganan, modul dan usage limits.
-   Pengurusan connector dan status integrasi.
-   Agent/tool policies dan kos AI.
-   Workflow runs, queue failures dan dead-letter queue.
-   Approval queue untuk tindakan sensitif.
-   Audit explorer dan security alerts.
-   Support access yang terhad, berjangka dan diaudit.
-   Feature flags dan release controls.

### Merchant workspace

-   Dashboard dan KPI.
-   B-Hub integrasi.
-   AI Team dan Boardroom.
-   Task, calendar, documents dan memory.
-   B-Flow workflow builder.
-   Business modules yang dilanggan.
-   Ads Manager untuk Google/Meta jika diaktifkan.
-   Usage, billing, member roles dan data export.

------------------------------------------------------------------------

## 17. DevOps dan infrastruktur

### 17.1 Fasa awal

-   Docker Compose untuk local/staging dan deployment kecil.
-   VPS di rantau Malaysia atau Singapura jika sesuai dengan latency,
    kos, kontrak dan data residency.
-   MySQL terurus jika bajet membenarkan; backup automatik.
-   Redis + worker Horizon.
-   Cloudflare dan R2/S3 untuk aset.
-   Satu deployment backend modular monolith.
-   Queue worker diasingkan secara logik mengikut jenis kerja.

### 17.2 Apabila berkembang

-   Worker khusus untuk AI, ads sync, messaging, reports dan webhook.
-   Read replicas jika pola baca memerlukannya.
-   ClickHouse apabila volum analytics memerlukannya.
-   Qdrant apabila RAG menjadi keperluan produksi.
-   Multi-node deployment/orchestration apabila keperluan availability
    dan scaling membenarkan.
-   Kubernetes bukan keperluan MVP; gunakan hanya jika kompleksiti
    operasi berbaloi.

### 17.3 CI/CD

GitHub Actions: 1. Format/lint. 2. Unit dan feature tests (Pest). 3.
PHPStan/static analysis. 4. Architecture boundary tests. 5. Tenant
isolation/security tests. 6. OpenAPI validation. 7. Build
artifacts/container. 8. Deploy staging. 9. Smoke tests dan migration
checks. 10. Production deploy dengan rollback plan.

### 17.4 Pemantauan

-   Sentry untuk exceptions.
-   Laravel Pulse untuk prestasi.
-   Uptime checks.
-   Structured logs dengan request/correlation ID.
-   Queue depth, retry, webhook failures dan connector token expiry.
-   AI latency, token usage, spend dan tool error rate.
-   Amaran ke Telegram atau saluran operasi lain.
-   Runbooks untuk incident utama.

### 17.5 Persekitaran

`local`, `staging`, `production` menggunakan database, credential,
bucket dan AI keys berasingan. Rahsia tidak disimpan dalam repository.

------------------------------------------------------------------------

## 18. Strategi ujian

-   Unit tests untuk domain invariants.
-   Feature/API tests untuk endpoint dan policy.
-   Architecture tests untuk larangan dependency antara modul.
-   Multi-tenant isolation tests dengan sekurang-kurangnya dua tenant.
-   Contract tests untuk connector dan webhook.
-   Idempotency tests untuk payment, order dan event consumers.
-   Queue retry/dead-letter tests.
-   AI tool permission tests dan prompt-injection tests.
-   Ads budget/approval tests menggunakan mock/sandbox, bukan akaun
    live.
-   Load tests untuk endpoint utama dan workflow.
-   Restore test backup berkala.
-   Accessibility dan mobile responsiveness tests untuk UI.

------------------------------------------------------------------------

## 19. Roadmap pembangunan

### Fasa 0 --- Foundation

-   Tetapkan domain boundaries, coding standards dan OpenAPI.
-   Repo, Docker, CI, environments, secrets dan monitoring.
-   Identity, tenant membership, permission policies dan audit.
-   Modul entitlement dan subscription skeleton.

**Exit criteria:** tenant A tidak boleh membaca atau mengubah data
tenant B; pipeline CI lulus.

### Fasa 1 --- AI Virtual Office MVP

-   B-Hub asas, B-Task, B-Workspace, B-Memory.
-   AI Gateway, Chief of Staff dan ejen CMO/CFO/COO/CSO.
-   B-Boardroom.
-   Human approval dan agent audit.

**Exit criteria:** ejen boleh melengkapkan tugasan melalui API dengan
permission checks dan audit lengkap.

### Fasa 2 --- Automation

-   B-Flow/B-Trigger, event outbox, workflow runs dan retry.
-   Integrasi e-mel/kalendar pertama.
-   Webhook signing dan idempotency.

**Exit criteria:** workflow boleh diproses semula tanpa menggandakan
tindakan perniagaan.

### Fasa 3 --- Marketing & Ads

-   B-Marketing, Google Ads connector dan Meta Ads connector.
-   OAuth, account linking, read-only insights dahulu.
-   Ads draft builder, approval gates, budget policies dan change log.
-   Kemudian aktifkan create/update/publish selepas pengesahan API dan
    ujian keselamatan.

**Exit criteria:** tiada perubahan bajet atau penerbitan tanpa
polisi/kelulusan yang sah; akaun setiap tenant terasing.

### Fasa 4 --- Core business operations

-   Commerce Core, POS, Site, Link, Marketplace.
-   Booking, Loyalty dan Omnichannel.
-   Invoicing, Finance dan Procurement.
-   HRMS, Sign & Docs dan Logistics.

**Exit criteria:** ownership data, event contracts, rekonsiliasi dan
audit lengkap bagi aliran utama.

### Fasa 5 --- Intelligence

-   B-Scout, Competitor Intel, B-Compliance.
-   Analytics pipeline dan ClickHouse jika berbaloi.
-   Qdrant/RAG dengan access control.
-   Forecast dan recommendation evaluation.

### Fasa 6 --- SaaS scaling

-   Plan/billing automation, usage metering dan self-service onboarding.
-   Partner/white-label jika diperlukan.
-   High availability, scaling workers, disaster recovery dan security
    review.

------------------------------------------------------------------------

## 20. Risiko utama dan mitigasi

  ---------------------------------------------------------------------
  Risiko                             Mitigasi
  ---------------------------------- ----------------------------------
  Skop terlalu besar                 MVP berfasa dan exit criteria

  Kebocoran tenant                   Polisi berlapis, composite
                                     constraints dan ujian negatif

  Event hilang/berganda              Transactional outbox, idempotency
                                     dan replay

  AI membuat tindakan salah          Least privilege, approval gates,
                                     caps dan audit

  Kos AI meningkat                   Routing model, quotas, caching
                                     yang selamat dan usage alerts

  API platform berubah               Connector adapters, contract tests
                                     dan health checks

  Data iklan tidak lengkap           Paparkan attribution window,
                                     tracking status dan sumber metrik

  Ejen memberi fakta tidak tepat     Evidence links, confidence labels
                                     dan human review

  Terlalu banyak servis awal         Modular monolith, tambah infra
                                     berdasarkan metrik

  Konflik data antara modul          Pemilikan data tunggal dan kontrak
                                     awam
  ---------------------------------------------------------------------

------------------------------------------------------------------------

## 21. Keputusan yang perlu dimuktamadkan sebelum coding

1.  Nama rasmi produk dan domain.
2.  Model SaaS dan pelan harga/kuota.
3.  Modul MVP dan modul yang ditangguhkan.
4.  Laravel package structure dan standard domain events.
5.  OAuth strategy untuk integrasi pihak ketiga.
6.  Provider AI pertama, had kos dan polisi data.
7.  Gateway pembayaran Malaysia yang akan digunakan dahulu.
8.  Akaun developer dan kelayakan Google Ads/Meta API.
9.  Hosting, backup, RPO/RTO dan bajet operasi.
10. Data retention, PDPA roles dan proses incident response.

------------------------------------------------------------------------

## 22. Ringkasan keputusan seni bina

AI Bijan ialah satu platform SaaS multi-tenant dengan Laravel modular
monolith sebagai backend, Next.js sebagai web client, Flutter untuk
mobile apabila diperlukan, dan Filament untuk HQ/merchant admin. Modul
perniagaan berkongsi Core Identity, Commerce, Pay, Notify, Integrations,
Audit dan Approval. Modul berkomunikasi melalui kontrak awam dan event
yang tahan kegagalan.

AI Team, B-Boardroom, B-Hub, B-Flow, B-Scout, B-Intel, B-Compliance dan
Ads Manager semuanya berada dalam platform yang sama. Google Ads dan
Meta Ads diurus melalui connector rasmi, dengan pemisahan tenant,
tracking, bajet, approval dan audit. AI hanya bertindak melalui API yang
dibenarkan.

**Prinsip pelaksanaan:** bina asas keselamatan dan identiti dahulu;
buktikan AI Virtual Office; tambah automasi; kemudian Google/Meta Ads
dan modul perniagaan; selepas itu intelligence dan penskalaan. Jangan
membina semua 18 modul serentak, dan jangan menambah infrastruktur
sebelum beban sebenar memerlukannya.

**Status dokumen:** blueprint seni bina untuk semakan dan pelaksanaan
berperingkat. Ia bukan bukti bahawa connector, akaun API, kelayakan
platform atau pematuhan undang-undang telah diluluskan.

------------------------------------------------------------------------

# Architecture v2.1 --- Production Readiness Addendum

**Tarikh kemas kini:** 17 Oktober 2026

Bahagian ini melengkapkan Architecture v2 dengan komponen operasi SaaS,
governance ejen, bil dan penggunaan, sokongan pelanggan, atribusi iklan
serta kriteria pelancaran produksi.

## 23. B-Security & AI Governance

### Kawalan keselamatan tambahan

-   MFA untuk pemilik, pentadbir dan akses berprivilege.
-   Session/device management, revoke semua sesi, rotation token dan
    alert log masuk luar biasa.
-   Secret manager/KMS untuk API keys, OAuth tokens dan credentials;
    rahsia tidak disimpan dalam source code atau log.
-   Approval dan authorization diperiksa di backend pada setiap tool
    call, bukan berdasarkan prompt sahaja.
-   Tool allowlist mengikut ejen, tenant, peranan dan jenis tindakan.
-   Had bajet, had bilangan tool calls, timeout, circuit breaker dan
    kill switch.
-   Sanitasi/validasi output model sebelum digunakan dalam SQL, HTML,
    shell, URL atau sistem luar.
-   Perlindungan prompt injection untuk kandungan daripada e-mel,
    dokumen, web dan connector.
-   RAG access control sebelum dokumen dimasukkan ke konteks model.
-   Ujian adversarial untuk kebocoran data, privilege escalation,
    excessive agency dan kos tanpa had.
-   Security incident register, vulnerability management, dependency
    scanning dan patch policy.

### Model approval

Setiap tindakan mempunyai tahap risiko: - **Rendah:** baca laporan,
ringkaskan, draf teks --- boleh berjalan automatik dalam skop yang
dibenarkan. - **Sederhana:** kemas kini rekod dalaman atau jadual ---
polisi tenant boleh mewajibkan pengesahan. - **Tinggi:** publish iklan,
ubah bajet, hantar kempen pukal, refund, ubah harga atau menghantar
dokumen bertandatangan --- approval mengikut polisi. - **Terlarang:**
tindakan di luar skop, melanggar polisi atau tidak mempunyai kebenaran
--- sentiasa disekat.

Approval perlu terikat pada payload/versi tindakan yang tepat. Jika
payload berubah selepas diluluskan, kelulusan lama tidak sah.

## 24. B-Billing, Usage Metering & SaaS Operations

### Keperluan

-   Pelan, add-on, trial, kupon, invois langganan, cukai dan renewal.
-   Meter penggunaan AI token, workflow runs, storage, seats, connector
    sync dan mesej.
-   Soft limits, hard limits, overage rules dan amaran sebelum had
    dicapai.
-   Payment failure, retry schedule, grace period, suspend/reactivate
    dan pembatalan.
-   Proration untuk naik/turun pelan jika disokong oleh model bil.
-   Rekonsiliasi transaksi dan laporan MRR, ARR, churn, activation dan
    retention.
-   Usage ledger append-only untuk menjejak bagaimana setiap caj dikira.
-   Self-service export dan pembatalan langganan.
-   Pemisahan invois SaaS platform daripada invois jualan merchant.

### Entiti tambahan

`plan_features`, `usage_records`, `usage_ledger`, `subscription_items`,
`billing_invoices`, `billing_payments`, `billing_adjustments`,
`trial_events`, `entitlement_snapshots`.

## 25. B-Agent Runtime & Control Plane

Control plane mengurus konfigurasi dan polisi; runtime melaksanakan
tugasan. Jangan gabungkan konfigurasi ejen dengan rekod run.

### Entiti tambahan

-   `agent_definitions`
-   `agent_versions`
-   `agent_tool_permissions`
-   `agent_runs`
-   `agent_run_steps`
-   `agent_tool_calls`
-   `agent_approvals`
-   `agent_evaluations`
-   `agent_memory_items`
-   `agent_cost_records`

Setiap run menyimpan tenant, peminta, agent version, model, prompt
version, tools yang digunakan, status, token/cost, tempoh, hasil, error
dan approver. Rahsia dan PII sensitif perlu disunting daripada log.

### Keupayaan runtime

-   Queue dan concurrency limits.
-   Timeout dan cancellation.
-   Retry policy mengikut jenis ralat.
-   Resume/replay yang selamat untuk langkah idempotent.
-   Human handoff.
-   Run-level cost budget.
-   Trace lengkap dari arahan pengguna hingga tool calls.
-   Version pinning dan rollback untuk prompt/tool.
-   Evaluations sebelum agent version dipromosikan ke production.

## 26. B-Integration Hub

Setiap connector perlu mempunyai: - Nama, versi, pemilik dan status
sokongan. - OAuth scopes atau mekanisme credential yang diperlukan. -
Status akaun: disconnected, connected, limited, expired, revoked,
error. - Webhook verification, polling/sync schedule dan cursor. -
Rate-limit handling, retry/backoff dan health status. - Data mapping dan
versioned schemas. - Proses revoke/disconnect serta pemadaman token. -
Sandbox/mock mode jika penyedia menyediakannya. - Dokumentasi had API
dan syarat kelulusan.

Connector tidak boleh diiklankan sebagai tersedia penuh sebelum
credential, permission dan API access disahkan.

## 27. B-CRM dan Revenue Attribution

B-CRM boleh dimulakan sebagai fungsi asas sebelum menjadi modul penuh: -
Lead, pipeline, activity, follow-up, source dan consent. - Hubungan
lead-to-customer/order menggunakan ID yang stabil dan polisi privasi. -
UTM capture dan conversion events. - Import/offline conversion apabila
dibenarkan oleh platform. - Pemetaan conversion window, timezone,
currency dan attribution model. - Bezakan platform-reported conversions
daripada transaksi yang disahkan dalam Core Commerce. - Paparkan missing
tracking, duplicate events dan tahap keyakinan atribusi.

ROAS dikira daripada revenue yang dikenal pasti mengikut definisi
laporan dibahagi spend. UI perlu menyatakan sumber revenue, tempoh,
attribution window dan sama ada revenue kasar atau bersih digunakan.

## 28. B-Customer Support & Service Reliability

-   Help centre dan dokumentasi merchant.
-   Support tickets, kategori, priority, SLA dan escalation.
-   Incident/status page.
-   Queue failure console dan retry dengan permission.
-   Data export, account deletion request dan privacy request workflow.
-   Runbooks untuk pembayaran gagal, connector revoked, event backlog,
    AI runaway dan kebocoran tenant.
-   Backup restore drill dan disaster recovery test.
-   Tetapkan RPO/RTO berdasarkan kemampuan sebenar dan bajet, bukan
    angka pemasaran.

## 29. Release Gates --- Syarat sebelum produksi

Sistem tidak boleh dianggap production-ready sehingga: 1. Tenant
isolation tests lulus untuk semua permukaan data. 2. Permission tests
lulus untuk manusia dan ejen AI. 3. Payment/webhook/event idempotency
diuji. 4. Backup dipulihkan dalam latihan restore. 5. Alert dan incident
runbooks diuji. 6. Secrets tidak muncul dalam repository atau log. 7.
Data retention, export dan deletion workflows diuji. 8. OAuth
revoke/refresh dan connector failure diuji. 9. Ads publishing/budget
controls diuji dalam sandbox atau test account sebelum live. 10. Kos AI
dan penggunaan per tenant boleh dipantau serta dibatasi. 11. Migrations
mempunyai strategi rollback/forward recovery. 12. Polisi privasi, terma
perkhidmatan dan perjanjian pemprosesan data disemak mengikut peranan
sebenar organisasi.

## 30. Rujukan rasmi yang perlu dipantau

-   Google Ads API access and productionization:
    https://developers.google.com/google-ads/api/docs/productionize/access-levels
-   Meta developer documentation dan Marketing API access/review:
    https://developers.facebook.com/docs/marketing-apis/
-   OWASP GenAI Security Project:
    https://owasp.org/www-project-top-10-for-large-language-model-applications/
-   LHDN e-Invois guidelines:
    https://www.hasil.gov.my/e-invois/rujukan-pelaksanaan-e-invois/garis-panduan/
-   Jabatan Perlindungan Data Peribadi Malaysia: https://www.pdp.gov.my/

Akses API, kelulusan aplikasi, versi garis panduan, kewajipan
undang-undang dan tarikh kuat kuasa boleh berubah. Semak sumber rasmi
semasa sebelum konfigurasi produksi.

## 31. Keputusan akhir untuk pelaksanaan

Architecture v2 + addendum v2.1 ialah blueprint yang lebih lengkap,
tetapi bukan pengganti spesifikasi implementasi per modul. Sebelum
coding setiap modul, sediakan: - Domain model dan data ownership. -
Migration dan indeks. - API contract/OpenAPI. - Events dan schema
version. - Authorization policies. - Error handling/idempotency. - Audit
requirements. - Tests dan acceptance criteria. - Monitoring/runbook. -
Feature flag dan rollout plan.

Urutan yang disyorkan: **Foundation & tenancy → SaaS billing/usage → AI
Virtual Office & governance → B-Flow → read-only Ads integrations →
approved Ads publishing → core commerce/business modules → intelligence
→ scale.**

---

# AI Bijan Architecture v2.2 — Universal SME Tools & Industry Packs

**Status:** Product scope addendum  
**Matlamat:** Menjadikan AI Bijan sesuai untuk sepuluh kategori bisnes Malaysia melalui satu platform modular, tanpa menggandakan domain, data atau enjin automasi.

## 32. Keputusan produk v2.2

AI Bijan bukan sepuluh sistem berasingan. Ia ialah satu **Business Operating System** dengan:

1. **Shared Core:** keupayaan yang dikongsi semua tenant.
2. **Industry Packs:** konfigurasi dan modul khusus mengikut model perniagaan.
3. **Integration Hub:** connector kepada penyedia luaran yang benar-benar disokong.
4. **AI Control Plane:** ejen, tools, polisi, kos, approval dan audit.
5. **Subscription Entitlements:** setiap ciri boleh diaktifkan mengikut pelan, add-on dan kategori pelanggan.

Setiap fungsi mesti diletakkan dalam satu domain pemilik. Industry Pack tidak boleh mencipta CRM, invois, pembayaran, notifikasi, inventori atau workflow kedua jika fungsi teras sudah wujud.

### 32.1 Prinsip reka bentuk

- **Universal by core, specialised by pack:** fungsi asas dikongsi; fungsi industri ditambah sebagai modul atau konfigurasi.
- **One source of truth:** satu rekod pelanggan, satu rekod order, satu rekod pembayaran dan satu sumber stok yang berautoriti.
- **AI uses tools, not direct database access:** AI hanya boleh bertindak melalui API/use case yang mempunyai pengesahan kebenaran.
- **Human approval by risk:** draf dan ringkasan boleh automatik; transaksi, penerbitan, penghantaran pukal, perubahan harga/bajet dan tindakan sukar dibalikkan memerlukan approval mengikut polisi.
- **Connector truthfulness:** connector dipaparkan sebagai unavailable, available, connected, limited, expired, revoked atau error; jangan menjanjikan API yang belum diluluskan.
- **Feature flags and entitlements:** fungsi boleh disediakan dalam codebase tanpa perlu diaktifkan untuk semua pelan.
- **Build only after evidence:** jangan membina semua fungsi industri pada MVP. Lengkapkan peta produk sekarang, tetapi laksanakan mengikut permintaan dan kriteria keluar fasa.

## 33. Shared Core — tools yang patut boleh digunakan oleh semua industri

Shared Core ialah asas platform. Ia bukan sepuluh salinan aplikasi; setiap domain mempunyai kontrak, pemilik data dan API sendiri.

| ID | Tool / domain | Fungsi minimum | Pemilik data utama |
|---|---|---|---|
| CORE-01 | Identity & Tenancy | Akaun, organisasi, membership, role, permission, tenant isolation, MFA dan sesi | Core Identity |
| CORE-02 | Subscription & Entitlements | Pelan, add-on, trial, kuota, status langganan, grace period dan had penggunaan | Core Billing |
| CORE-03 | Usage Metering | Rekod penggunaan AI, workflow, storage, seats, mesej dan connector sync | Usage Ledger |
| CORE-04 | AI Command Center | Arahan bahasa semula jadi, konteks workspace, task routing dan ringkasan | AI Control Plane |
| CORE-05 | AI Agent Runtime | Agent definitions/versions, tool allowlist, limits, run status, timeout, cancel, retry dan cost tracking | AI Control Plane |
| CORE-06 | Approval Center | Queue kelulusan, payload yang diluluskan, approver, expiry, rejection dan audit | Core Approval |
| CORE-07 | Task & Project Workspace | Tugasan, owner, due date, status, priority, recurring task, checklist dan komen | B-Virtual Office |
| CORE-08 | Calendar & Scheduling | Acara, availability, reminders, calendar sync dan timezone | B-Virtual Office; B-Booking memiliki slot pelanggan |
| CORE-09 | CRM & Customer 360 | Lead, contact, company, pipeline, activity, consent, source dan follow-up | B-CRM |
| CORE-10 | Unified Inbox | Conversation, assignment, tags, internal notes, templates, SLA dan escalation | B-Omnichannel |
| CORE-11 | Communication Delivery | E-mel, SMS, WhatsApp, push dan notifikasi melalui connector yang tersedia | B-Notify |
| CORE-12 | Document Studio | Template, PDF, dokumen, versi, approval, e-sign integration dan retention | B-Sign & Docs |
| CORE-13 | Quote & Invoicing | Sebut harga, invois, credit note, status, numbering dan e-Invois adapter | B-Invoicing |
| CORE-14 | Payment Hub | Payment intent, status, refund, settlement, reconciliation dan gateway adapter | B-Pay |
| CORE-15 | Finance & Cashflow | Kategori akaun, jurnal, perbelanjaan, bajet, cashflow dan reconciliation workflow | B-Finance |
| CORE-16 | Commerce Core | Product, variant, customer order, order items, discounts dan order status | Core Commerce |
| CORE-17 | Inventory & Procurement | Stock ledger, stock movement, reorder, supplier, purchase order dan supplier bill | B-Logistics / B-Procurement |
| CORE-18 | Booking & Resource Scheduling | Service, staff/provider, availability, appointment, deposit dan cancellation policy | B-Booking |
| CORE-19 | Marketing Studio | Content calendar, campaign, audience/segment, voucher, landing page brief dan creative library | B-Marketing |
| CORE-20 | Ads Manager | Google/Meta account connection, insights, drafts, budget policy, change request dan approval | B-Marketing Ads Manager |
| CORE-21 | Website & Digital Presence | Website pages, forms, landing pages, business profile, link page dan domain status | B-Site / B-Link |
| CORE-22 | Analytics & Reports | KPI definitions, report schedules, exports, dashboards dan source lineage | B-Analytics |
| CORE-23 | Knowledge & SOP | FAQ, policies, SOP, file indexing, access-aware retrieval dan versioning | B-Knowledge |
| CORE-24 | Workflow Automation | Trigger, condition, action, schedule, approval, retries, idempotency dan dead-letter queue | B-Flow / B-Trigger |
| CORE-25 | Integration Hub | OAuth/credentials, scopes, sync cursor, webhook, health, retry, revoke dan connector logs | Core Integrations |
| CORE-26 | Customer Support | Ticket, priority, SLA, help centre, incident status dan privacy requests | B-Support |
| CORE-27 | Files & Media | Upload, access control, object storage, thumbnails, retention dan malware scanning where available | Core Files |
| CORE-28 | Audit & Security | Activity log, security events, tenant checks, session revocation dan incident register | Core Audit |
| CORE-29 | Import / Export | CSV/XLSX imports, mapping preview, validation errors, export and deletion workflow | Core Data Services |
| CORE-30 | Localisation | BM/English, MYR, Asia/Kuala_Lumpur, Malaysian phone/address formats, tax configuration and business hours | Shared Platform |
| CORE-31 | Product & Pricing Rules | Price lists, variants, discounts, packages, taxes and effective dates | Commerce / domain owner |
| CORE-32 | Customer Feedback & Reputation | Feedback requests, review tracking and response drafts where APIs/permissions permit | B-CRM / B-Marketing |
| CORE-33 | Business Health Dashboard | Tasks overdue, leads, sales, cashflow, customer service, automation failures and AI spend | B-Analytics |
| CORE-34 | Data Governance | Consent, retention, access/correction/export/deletion request and processing records | Core Governance |
| CORE-35 | Platform Admin HQ | Tenant management, billing support, connector health, approval oversight, feature flags and support access | Platform Operations |

### 33.1 Tools yang wajib mempunyai definisi implementasi

Untuk setiap tool, spesifikasi implementasi mesti mengandungi:
- Domain owner dan rekod sumber.
- Entiti dan hubungan utama.
- API routes/use cases dan OpenAPI contract.
- Role/permission dan tenant isolation rules.
- Events yang diterbitkan/digunakan.
- Validation, error states, idempotency dan retry rules.
- Audit fields dan data retention.
- Unit, feature, integration, security dan acceptance tests.
- Monitoring, runbook, feature flag dan rollout/rollback plan.

## 34. Pemetaan sepuluh Industry Packs

Industry Pack ialah gabungan modul teras, konfigurasi, medan tambahan, templat, workflow dan connector. Pack tidak semestinya bermaksud satu codebase atau satu database berasingan.

### 34.1 Freelancer, agensi & servis profesional

**Tujuan:** urus prospek, proposal, projek, kapasiti pasukan, deliverables, retainer dan keuntungan setiap pelanggan.

Tools/keupayaan:
- CRM pipeline, lead source, qualification, contact history dan follow-up.
- Proposal, quotation, scope of work, contract/e-sign connector dan invoice.
- Project, task, milestones, deliverables, approval pelanggan dan change request.
- Timesheet, billable/non-billable hours, retainer, project budget dan margin.
- Client portal, file sharing berpermission dan feedback/approval.
- Meeting notes, action items, calendar dan recurring client reports.
- Expense allocation, invoice reminders dan overdue receivables.
- AI proposal/brief/SOP generator, meeting summary dan project-risk alerts.

Workflow utama:
`Lead → discovery → proposal → approval/contract → project → timesheet/expenses → invoice → payment → review/renewal`.

### 34.2 Kedai runcit, pemborong & e-dagang

**Tujuan:** jualan, katalog, harga, stok, pembelian, pesanan dan saluran jualan.

Tools/keupayaan:
- Product catalogue, SKU, barcode, variants, price lists dan bundles.
- POS, cashier shift, cash drawer, receipt, discount controls dan returns.
- Multi-location stock, stock ledger, stock count, transfers dan reorder alerts.
- Supplier, purchase order, goods received, supplier invoice dan landed cost.
- Order management, packing, shipping, tracking, cancellation dan returns.
- Marketplace connectors (contohnya Shopee) hanya apabila akses/API diluluskan.
- Customer segments, loyalty, voucher, abandoned cart jika platform menyokong.
- Gross margin, stock turn, slow-moving stock, sales by SKU/location dan reorder suggestions.
- Offline POS hanya jika keperluan dan reka bentuk sync konflik benar-benar ditangani.

Workflow utama:
`Order/sale → payment status → inventory movement → fulfilment → invoice/receipt → reconciliation → customer follow-up`.

### 34.3 Restoran, kafe & F&B

**Tujuan:** meja, pesanan, menu, dapur, bahan mentah, pembaziran dan prestasi outlet.

Tools/keupayaan:
- Menu, modifiers, add-ons, set meals, price-by-channel dan availability.
- Table/seat management, waitlist, reservations dan walk-ins.
- Dine-in, takeaway, pickup dan delivery order types.
- POS, split bill, tips/service charges/tax configuration where applicable, void/refund approval.
- Kitchen Display System (KDS), kitchen tickets, preparation status dan order routing.
- Recipe/BOM, ingredient stock, portion cost, yield, wastage dan expiry/batch tracking when needed.
- Supplier purchasing, receiving, outlet transfer dan stock count.
- Staff shift, role access, daily closing, cash reconciliation dan outlet comparison.
- Menu QR dan online ordering melalui B-Site/Commerce.
- Food safety/temperature checklist dan SOP logs jika sesuai dengan operasi pelanggan.
- AI demand forecast dan prep suggestions hanya selepas data jualan mencukupi.

Workflow utama:
`Order → kitchen ticket → prepared → served/collected → payment → ingredient deduction → daily close`.

### 34.4 Barber, salon, spa & wellness

**Tujuan:** appointment, staff/provider, servis, pakej, komisen dan pelanggan berulang.

Tools/keupayaan:
- Service catalogue, duration, price, add-ons dan staff skill mapping.
- Calendar/slot availability, resource/room allocation, deposits dan cancellation rules.
- Booking web/link, reminders, waitlist, reschedule dan no-show records.
- Customer profile, consent, service history, preferences dan notes with access restrictions.
- Packages, memberships, prepaid credits and gift vouchers with liability tracking.
- POS/retail products, stock use and aftercare recommendations.
- Staff commission rules, tips and payout reports with approval.
- Repeat booking, customer retention, referral and loyalty.
- Forms/waivers where appropriate; sensitive notes restricted by role and retention policy.
- AI appointment assistant, service descriptions and reminder drafts.

Workflow utama:
`Booking → reminder → check-in → service → payment/package deduction → commission record → rebooking`.

### 34.5 Servis rumah & kontraktor

**Tujuan:** lead, site visit, quotation, kerja lapangan, bahan, pekerja, bukti kerja dan kutipan.

Tools/keupayaan:
- Lead capture, service area, job type, urgency and qualification.
- Site visit booking, calendar, technician/crew assignment and route planning.
- Quote builder with labour, material, equipment, transport, margin and validity period.
- Work order, job card, checklist, before/after photos, customer sign-off and attachments.
- Job costing, timesheets, mileage, materials consumed, subcontractor and change orders.
- Deposits, progress claims, milestone billing, invoice and overdue collection.
- Warranty, defects, callbacks and maintenance schedules.
- Safety checklist and incident records where applicable.
- Map/geolocation only with appropriate consent and access policy.
- AI quote drafts, job summaries, checklist generation and overdue-job alerts.

Workflow utama:
`Lead → site inspection → quote → approval/deposit → work order → field execution → sign-off → invoice/claim → warranty follow-up`.

### 34.6 Tutor, pendidikan & latihan

**Tujuan:** pelajar, kelas, jadual, kehadiran, yuran, bahan pembelajaran dan kemajuan.

Tools/keupayaan:
- Student/guardian records, consent and emergency contacts where appropriate.
- Course, cohort, class, lesson, trainer and room/resource scheduling.
- Enrollment, waitlist, attendance, make-up class and cancellation rules.
- Learning materials, assignment, rubric, quiz and progress records.
- Online class link integrations and reminders.
- Tuition fee plans, installments, invoice, payment tracking and arrears reminders.
- Certificate generation and course completion rules.
- Trainer allocation, class capacity and utilization reports.
- Parent/student portal with role-based visibility.
- AI lesson-plan drafts, quiz drafts and learning-material summaries with teacher review.
- Safeguards for minors, access to student data and retention must be explicit.

Workflow utama:
`Enquiry → enrolment → class schedule → attendance → progress → fee collection → completion/certificate`.

### 34.7 Hartanah & pengurusan sewaan

**Tujuan:** listing, unit, viewing, tenant, tenancy, rent collection, maintenance dan pemilik aset.

Tools/keupayaan:
- Property, building, unit, ownership and occupancy records.
- Listing, media, availability, enquiries and viewing calendar.
- Applicant pipeline, screening checklist and documented consent.
- Tenancy/lease templates, document versions, deposit records and renewal reminders.
- Rent schedule, arrears, receipts, owner statements and expense allocation.
- Maintenance requests, vendor assignment, quotations, photos and work completion.
- Move-in/move-out inspection, inventory checklist and handover records.
- Utility/service charge records and reconciliation where relevant.
- Property owner/tenant portal with separated permissions.
- Portfolio occupancy, rental yield and maintenance-cost reporting.
- AI listing drafts, enquiry triage and lease-expiry reminders; legal documents need human/professional review.

Workflow utama:
`Listing → enquiry → viewing → application → tenancy approval/signing → deposit/rent schedule → maintenance → renewal/move-out`.

### 34.8 Pengilangan & pemborongan

**Tujuan:** bahan mentah, BOM, perancangan pengeluaran, work orders, QC, batch dan penghantaran.

Tools/keupayaan:
- Raw material, components, finished goods, units of measure and SKU.
- BOM/recipe, routing, operations, work centres and production steps.
- Production order, material reservation, issue/return and WIP status.
- Batch/lot, serial numbers, expiry and traceability where applicable.
- Supplier lead time, purchase planning, receiving and inspection.
- Quality control checkpoints, defects, rework, non-conformance and corrective action.
- Machine/maintenance schedules where required.
- Labour and machine time, scrap/waste, standard vs actual costing.
- Warehouse/bin locations, transfers, pick/pack and delivery.
- Wholesale price lists, customer-specific terms, minimum order quantities and credit controls.
- Demand/capacity planning only after reliable transactional data exists.

Workflow utama:
`Sales forecast/order → material planning → purchase → receiving/QC → production order → QC → finished goods → fulfilment → cost analysis`.

### 34.9 Perakaunan & konsultasi

**Tujuan:** client portfolio, engagements, compliance calendar, documents, workpapers, review and billing.

Tools/keupayaan:
- Client onboarding, engagement letter, scope, fee arrangement and conflict-check workflow.
- Client/task portfolio, deadlines, recurring compliance calendar and workload.
- Secure document request portal, checklist, versioning and evidence trail.
- Workpapers, review notes, sign-off and restricted client access.
- Time tracking, fixed-fee/retainer billing, invoice and collections.
- Client communication log, meeting notes and deliverable approval.
- Tax/accounting document intake, OCR extraction and reconciliation suggestions with human verification.
- Standard operating procedures and source/version-tracked compliance knowledge.
- Audit trail, confidentiality, retention and access restrictions.
- AI may draft summaries and identify anomalies; it must not claim professional certification or automatically submit sensitive filings without explicit authority and review.

Workflow utama:
`Client onboarding → engagement approval → document collection → preparation → professional review → client approval/filing → invoice → archive/renewal`.

### 34.10 Content creator & produk digital

**Tujuan:** idea, production pipeline, asset library, publishing, products, digital delivery, affiliate and revenue tracking.

Tools/keupayaan:
- Content calendar, idea backlog, briefs, scripts, captions and campaign variants.
- Asset library, versioning, brand kit, rights/usage notes and approval.
- Multi-channel publishing connector status; publish only where API permission permits.
- UTM links, campaign tracking, audience insights and content performance.
- Digital product catalogue, bundles, licence/usage terms and price tests.
- Checkout, payment, secure download/entitlement, expiring links and delivery logs.
- Course/membership access, subscriptions and customer support where applicable.
- Affiliate/referral codes, commission rules and fraud review.
- Email list, consent, unsubscribe and customer segmentation.
- AI repurposing into formats, subtitles/translations, content briefs and draft responses.
- Copyright, disclosure, music/image licensing and platform policies remain human review responsibilities.

Workflow utama:
`Idea → brief/script → asset → review → publish → measure → digital sale → secure delivery → support/upsell`.

## 35. Cross-industry tools that are missing or must be made explicit

Architecture v2.1 already names many major modules. The following need explicit scope or stronger boundaries so they do not become gaps during implementation:

1. **B-Projects & Time Tracking:** essential for agencies, consultants, contractors and internal work. May begin inside B-Virtual Office, then become a richer feature set.
2. **B-Expenses & Receipt OCR:** capture receipt, extract fields, attach to job/project, review and post to Finance. OCR output must be confirmed before accounting impact.
3. **B-Inventory & Stock Ledger:** explicitly model SKU, locations, transfers, batches, reservations and adjustments. Avoid stock values being separately owned by POS and Commerce.
4. **B-Booking & Resource Scheduling:** support people, rooms, equipment, appointment buffers, recurring classes and cancellation/deposit rules.
5. **B-Field Service & Work Orders:** job card, technician assignment, proof of work, materials, sign-off, warranty and service history.
6. **B-Manufacturing / Production:** BOM, routing, production order, WIP, QC and batch traceability; later-phase module, not MVP.
7. **B-Learning / Course Operations:** class cohorts, attendance, materials, assessments and certificates; use existing CRM, Commerce, Billing and Notify.
8. **B-Property Operations:** units, tenancies, rent schedules, maintenance and owner statements; do not treat property leases as generic CRM notes.
9. **B-Customer Portal:** controlled view for client/student/tenant/customer to see documents, orders, bookings, payments and support requests.
10. **B-Import & Migration:** import customers/products/opening stock/invoices from CSV/XLSX with preview, validation, duplicate handling and rollback report.
11. **B-Returns, Refunds & Warranty:** standardised workflow linked to original order/payment, with approval and inventory disposition.
12. **B-Consent & Preference Centre:** contact consent, channel preference, opt-out, purpose and timestamps for marketing communications.
13. **B-Data Quality & Reconciliation:** duplicate detection, unmatched payments, stock variance, missing tax/identity fields and inconsistent status alerts.
14. **B-Product/Service Catalogue:** one catalogue model with type-specific extensions; do not force appointments, manufacturing BOMs and digital files into identical fields.
15. **B-Recurring Billing & Contracts:** subscription/retainer/rent/course installment schedules; a shared schedule engine with domain-specific billing rules.
16. **B-Quality & Compliance Checklists:** configurable checklist engine, evidence, assignee, expiry and review history; specialised compliance rules remain industry-specific.
17. **B-Notification Preferences:** customer and staff channel preference, quiet hours, templates and delivery status.
18. **B-Backup/Restore & Disaster Recovery Operations:** documented backup, restore drill, retention, recovery targets and incident communication.
19. **B-Observability & Cost Control:** per-tenant AI spend, queue backlog, connector health, failed workflows and usage thresholds.
20. **B-Accessibility & Responsive UX:** mobile-first web experience, keyboard accessibility, readable forms and usable interfaces for small business owners.

## 36. Clear module boundaries

| Domain | Owns | Does not own |
|---|---|---|
| Core Commerce | Customer orders, order items, product/variant master | Payment ledger, accounting journal, stock movement ledger |
| B-Pay | Payment intent, gateway transaction, refund, settlement | Sales invoice, accounting journal, order fulfilment |
| B-Finance | Chart of accounts, journal, reconciliation, cashflow reports | Operational order state or invoice document master |
| B-Invoicing | Quotes, invoices, credit notes, e-Invoice submission/status | Payment provider transaction or accounting ledger |
| B-Inventory / Logistics | Stock locations, reservations, movements, batches, shipment | Order master or payment status |
| B-CRM | Lead pipeline, customer relationship activity, source and consent | Duplicate master orders/invoices |
| B-Omnichannel | Conversation, channel messages, assignment and delivery state | CRM lead master or notification delivery infrastructure |
| B-Notify | Delivery, template, provider response and retries | Business conversation history as its master |
| B-Marketing | Campaigns, segments, content, ads configuration and attribution metadata | Actual order/payment source of truth |
| B-Booking | Appointment, service slot, resource availability and cancellation | General staff task list or payment ledger |
| B-Virtual Office | Tasks, projects, meetings, notes and internal work | Appointment booking master or finance journal |
| Industry Pack | Specialised fields, rules, screens and workflows | A second copy of any shared core domain |
| B-Analytics | Read models, metrics and historical snapshots | Authoritative writes to operational records |
| AI Control Plane | Agent policy, versions, tool calls, runs, evaluations and costs | Direct business data writes outside authorised APIs |

### 36.1 Shared engines vs domain-specific rules

Use shared engines where the mechanics are the same:
- Approval engine.
- Workflow engine.
- Notification delivery.
- Document templates/versioning.
- Pricing/tax configuration primitives.
- Recurring schedule engine.
- Import/export engine.
- Search and file storage.
- Usage metering and billing.

Keep rules in their correct domain:
- Appointment availability in Booking.
- Recipe and ingredient consumption in F&B operations.
- BOM and work-in-progress in Manufacturing.
- Rent schedule and tenancy terms in Property.
- Course attendance and completion in Learning.
- Client workpapers and professional review in Accounting/Consulting.

## 37. Industry Pack configuration model

Each pack should define:
- `industry_pack_id`, version, status and supported plan tiers.
- Enabled modules and feature flags.
- Navigation/menu layout and dashboard widgets.
- Optional fields, validation rules and document templates.
- Default roles and permission presets.
- Workflow templates and approval thresholds.
- Reports/KPI definitions with formula and source.
- Connector recommendations and availability state.
- Data import templates and sample data.
- Pack-specific acceptance tests and migration/upgrade path.

Do not create separate backend forks per industry. Store pack configuration as versioned metadata; implement genuinely different domain behavior in bounded modules.

## 38. SaaS plans and monetisation boundaries

Price the platform by **capability + seats + measured usage**, not by pretending every integration is free.

Suggested packaging logic (price amounts are deliberately not set until cost research and customer validation):

- **Starter:** Shared Core, limited AI usage, basic CRM, task workspace, documents, basic reports and a small number of workflows.
- **Business:** higher AI/workflow limits, more connectors, client/customer pipelines, automation templates and enhanced reports.
- **Team/Professional:** seats, roles, approval policies, project/workload management, audit and collaboration.
- **Industry Pack add-ons:** POS/Retail, F&B, Booking & Wellness, Field Service, Learning, Property, Manufacturing, Professional Services, Creator/Digital Commerce.
- **Usage add-ons:** extra AI usage, messaging, storage, automation runs and premium connectors where provider costs apply.
- **Enterprise/Partner later:** custom controls, SSO where needed, partner management, white-label and negotiated limits.

Billing must clearly distinguish AI Bijan subscription invoices from the merchant's own sales invoices. Every metered charge needs an auditable usage record and understandable limit/overage behavior.

## 39. Delivery roadmap and release gates

### Release A — Commercial SaaS Foundation
- Identity, tenant isolation, role/permissions, MFA and audit.
- Plan/entitlement, subscription lifecycle and usage ledger.
- Admin HQ, feature flags, import/export foundation and customer support workflow.
- AI Gateway, agent runtime, cost caps and approval center.
- CI/CD, backups, restore drill, monitoring and incident runbooks.

**Gate:** tenant-isolation, permission, subscription, cost-limit and restore tests pass.

### Release B — Universal Business Workspace
- Task/project basics, calendar, CRM, documents, knowledge/SOP and reports.
- Basic B-Flow with scheduled/manual triggers and safe retries.
- First email/calendar connectors where credentials are available.
- Complete lead-to-quote-to-follow-up workflow.

**Gate:** a pilot customer can complete a real workflow without spreadsheet workarounds for every step; audit and failure handling work.

### Release C — Customer Communications & Marketing
- Unified inbox foundation, communication preferences and delivery status.
- Marketing Studio, content calendar and landing/link tools.
- Google Ads/Meta Ads read-only reporting first; then drafts; then approved writes after API access and tests.
- Attribution definitions and tracking-health warnings.

**Gate:** consent, account isolation, attribution source, publish approval and budget limits are verified.

### Release D — Commerce, Payment & Invoicing
- Product/order core, payment adapters, invoice/quote, return/refund and reconciliation.
- One or two payment providers first, based on verified merchant access and demand.
- MyInvois integration according to current official requirements.
- Stock ledger and basic purchasing.

**Gate:** payment/webhook idempotency, refunds, invoice state, ledger reconciliation and duplicate-order tests pass.

### Release E — First Industry Packs
Choose the first two or three packs based on paid pilot demand. Recommended candidates for broad reusability are:
1. Freelancer/Agency/Professional Services.
2. Retail/Wholesale/E-commerce.
3. Booking-based services (Barber/Salon/Spa), if local demand confirms.

Other packs remain specified but are not advertised as complete until their own workflows and acceptance tests pass.

**Gate:** each pack has a demonstrable end-to-end workflow, data import template, permission matrix, reports and support runbook.

### Release F — Specialist Operations
- F&B KDS/recipe costing.
- Field service/job costing.
- Learning/class operations.
- Property/tenancy operations.
- Manufacturing/BOM/WIP/QC.
- Accounting-client workpapers and restricted review flows.
- Creator digital delivery, membership and affiliate.

**Gate:** specialist domain logic is validated with real operators and does not weaken core data ownership.

### Release G — Intelligence & Scale
- B-Scout/B-Intel/B-Compliance with sourced, timestamped information.
- Forecasting only where historical data quality is sufficient.
- RAG/vector search, ClickHouse and specialised workers only when measured load and use cases justify them.
- White-label/partner, voice AI and advanced autonomous workflows after safety evaluations.

**Gate:** production reliability, unit economics, incident response and AI evaluation meet documented thresholds.

## 40. Standard acceptance checklist for every tool

No tool is “complete” merely because its UI exists. Each release must verify:

- [ ] User can complete the advertised task from start to finish.
- [ ] API, validation, permission and tenant scope work server-side.
- [ ] Empty, loading, success, partial failure and retry states are implemented.
- [ ] Duplicate submissions and webhook replays do not duplicate business effects.
- [ ] Data owner and source of truth are documented.
- [ ] Audit log records actor, tenant, action, resource, timestamp and outcome.
- [ ] Sensitive actions invoke the approval policy.
- [ ] Data export, retention and deletion are handled where applicable.
- [ ] Unit, feature, integration, authorization and tenant-isolation tests pass.
- [ ] Monitoring, alerts, support instructions and rollback/disable controls exist.
- [ ] Usage/cost is attributed to the correct tenant.
- [ ] Connector status and API limitations are truthful in the UI.
- [ ] Keyboard/mobile usability and accessible labels are checked.
- [ ] User-facing help and a short onboarding path exist.

## 41. Final product rule

**AI Bijan must be comprehensive in its product map, but selective in what is built and activated at launch.**

The ten Industry Packs must all be represented in the product specification, each with its workflows, data requirements, tools, reports, permissions and acceptance criteria. The first commercial release should activate only the shared core and a small number of complete workflows. Expand based on paid customer evidence, not on the number of modules listed in the architecture.

A module is production-ready only after the implementation, connector access, operational controls and release gates have been verified. This document defines intended scope; it does not claim that the modules or external integrations have already been implemented or approved.
