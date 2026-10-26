# AI Bijan Architecture v2.4 — Master Consolidated Blueprint

> **Status:** Consolidated master blueprint  
> **Base:** v2.3 master blueprint retained, with the v2.4 Shared Agent Memory & Inter-Agent Collaboration extension  
> **Extension:** Universal SME Tools & Industry Packs + centrally managed AI supply, token metering, per-agent model selection, shared memory and governed agent collaboration  
> **Purpose:** One platform for diverse Malaysian businesses, with a shared core, specialised industry packs, and a secure central AI gateway that lets AI Bijan resell usage without exposing upstream credentials or internal provider identifiers.

## Master document structure

1. **Architecture v2.1 — Original blueprint:** preserved below without rewriting.
2. **v2.2 — Universal SME Tools & Industry Packs:** retained from the previous consolidated version.
3. **v2.3 — AI Provider & Token Reseller:** secure upstream gateway, token packages, model catalogue and usage billing.
4. **v2.4 — Shared Agent Memory & Inter-Agent Collaboration:** persistent memory, scoped shared knowledge, agent-to-agent workflows, permissions and traceability.
5. **Implementation rule:** this document defines intended product scope, not proof that each module, integration, or compliance requirement is already implemented or production-ready.

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


---

# Architecture v2.3 — Central AI Supply, Model Catalogue & Token Reseller

**Status:** Required product and technical specification  
**Business rule:** AI Bijan customers can use only AI models supplied and enabled by the AI Bijan platform owner. Customers must never receive the upstream provider API key or internal provider namespace.

## 42. Keputusan utama v2.3

1. **AI supply dikawal Admin HQ.** Platform Owner mengurus satu atau lebih upstream AI credentials, endpoint, model availability, internal rates, margins, limits and fallback rules.
2. **Pelanggan menggunakan AI Bijan sahaja.** Semua web/mobile apps, AI agents and integrations call the AI Bijan backend. The backend calls `https://serveras.click/v1` server-to-server.
3. **Upstream secret kekal rahsia.** The upstream `asai_sk_...` key is stored only as a server-side secret. Never embed it in browser JavaScript, mobile apps, frontend environment variables, public API responses, logs, analytics, source maps or customer-visible error messages.
4. **Nama model pelanggan ialah nama paparan sahaja.** The internal upstream model IDs such as `asai/gpt-6-astra` are never shown in the customer model picker, customer API responses, public logs or customer-facing documentation. The upstream `asai/` namespace is an internal implementation detail.
5. **Allowlist wajib.** Customers cannot send arbitrary upstream model IDs, override the upstream base URL, supply provider credentials, or bypass usage accounting. The backend maps a customer-facing model ID to an enabled internal upstream model ID.
6. **Usage-based resale.** Every request is metered by tenant/account and user, including input/output token counts, cached tokens if returned, model, timestamp, status and the pricing-rule version. Customer balances and platform supplier costs are separate ledgers.
7. **Hard limits before inference.** Check account status, verified email where applicable, plan entitlement, available token/credit balance, request rate, concurrency and model availability before forwarding a request.
8. **Settle after response.** Reserve a conservative amount before sending upstream; settle from actual usage when the upstream returns usage data. Release unused reservation. If usage is unavailable or streaming ends early, apply a documented, conservative settlement rule and flag the record for reconciliation.
9. **Fail closed for accounting.** If reservation or settlement cannot be made safely, reject the inference request instead of offering unmetered AI.
10. **Provider portability.** Keep a provider adapter boundary so another upstream supplier can be added later, but only Admin HQ may configure it. This release's customer-facing model catalogue is limited to the approved list in this section.

## 43. AI request architecture

### 43.1 Request flow

1. Customer signs in to AI Bijan and selects an enabled display model.
2. Client sends the request to the versioned AI Bijan backend using the customer's own session or scoped application token.
3. Backend authenticates the caller, resolves tenant and user, and checks subscription entitlements, model allowlist, account status, RPM/concurrency limits and available balance.
4. Backend estimates maximum usage and reserves the required balance in a database transaction.
5. The AI Gateway maps the public model alias to the internal upstream ID and sends the request to `https://serveras.click/v1` using the secret stored server-side.
6. Gateway normalizes the upstream response into the supported AI Bijan response format. Streaming must meter usage and settle partial output safely.
7. Usage ledger records actual tokens, internal supplier cost, customer charge, margin, status and pricing-rule version.
8. Customer UI updates balance and usage history; Admin HQ shows aggregated revenue, supplier cost, margin, failures and reconciliation status.

### 43.2 Supported upstream endpoints

Use the supplied compatibility endpoints, subject to provider account permissions and current provider behavior:

- `POST https://serveras.click/v1/chat/completions` — OpenAI Chat Completions compatibility, streaming and non-streaming.
- `POST https://serveras.click/v1/responses` — Codex compatibility for supported `asai/gpt-*` aliases only; do not claim full Responses API parity.
- `POST https://serveras.click/v1/messages` — Anthropic Messages compatibility, streaming and non-streaming.
- `GET https://serveras.click/v1/models` — upstream catalogue discovery for internal synchronization only. Never expose its raw response directly to customers.

The gateway must choose the compatible endpoint/adapter based on the internal model configuration and client capability. Do not assume every model supports every endpoint, vision, video, tools, structured output or streaming. Store and test capability flags per model.

### 43.3 Customer-facing API

Recommended public API surface:

- `GET /api/v1/ai/models` — only enabled customer display aliases, supported capabilities, context/output limits and customer pricing/usage notes.
- `POST /api/v1/ai/chat/completions` — customer session/API token; accepts only an allowlisted public model alias.
- `POST /api/v1/ai/responses` — enabled only for models validated for the supported Codex compatibility flow.
- `POST /api/v1/ai/messages` — enabled only for models validated for the supported Anthropic Messages compatibility flow.
- `GET /api/v1/ai/usage` — caller's own usage history and balance.
- `GET /api/v1/ai/balance` — caller's available balance, reserved balance and package expiry.
- `GET /api/v1/ai/packages` — currently available customer token packages.
- `POST /api/v1/ai/purchases` — creates a purchase/payment intent; credit only after a verified payment callback or approved HQ action.

These are AI Bijan routes, not direct proxy access to the supplier. Use normal AI Bijan session authentication for the web app. If third-party API keys are offered later, issue **AI Bijan customer keys**, hash them at rest, scope and revoke them independently, and never reuse or return the upstream key. Customer API keys must be rate-limited and tied to a tenant and usage wallet.

### 43.4 Error normalization

Return a stable AI Bijan error envelope. Never forward upstream HTML, stack traces, credentials, raw provider headers or internal host details.

Map at least these upstream cases to customer-safe codes:

| Upstream condition | AI Bijan customer-safe code |
|---|---|
| Missing/invalid customer credential | `unauthorized` |
| Customer has insufficient balance | `insufficient_balance` |
| Customer/model entitlement expired | `entitlement_expired` |
| Disabled customer account | `account_disabled` |
| Unknown or not-enabled display model | `model_unavailable` |
| Customer rate limit reached | `rate_limit_exceeded` |
| Customer concurrency reached | `concurrency_limit_exceeded` |
| Upstream model disabled/unavailable | `ai_temporarily_unavailable` |
| Supplier/accounting unavailable | `ai_billing_temporarily_unavailable` |
| Invalid input or unsupported capability | `invalid_request` |

Keep the upstream status/code in restricted internal diagnostic records, with secrets and sensitive prompt content redacted. Do not promise that upstream failures are never charged: apply the supplier's actual billing/usage result and the published AI Bijan settlement policy.

## 44. Secret management and access control

### 44.1 Secret storage

- Store the supplier key in a production secret manager or encrypted server-side environment configuration.
- Local development uses an untracked `.env` value such as `AI_SUPPLIER_API_KEY`; commit only `.env.example` with an empty placeholder.
- Never prefix the key with `NEXT_PUBLIC_`, `VITE_`, `EXPO_PUBLIC_`, or any frontend-public environment convention.
- Never store the supplier key in MySQL as plaintext. If the platform needs editable credentials in HQ, encrypt at application level with a managed key and restrict decrypt permission to the gateway service.
- Never display the full saved key in Admin HQ. Show only a masked fingerprint and rotation date; provide replace/rotate, not reveal.
- Redact `Authorization`, cookies, customer secrets and sensitive prompt data from logs/traces.
- Support rotation, revocation, environment separation, incident response and emergency disable.

### 44.2 HQ permissions

- `platform_owner`: manage supplier connection, secret rotation, model mappings, upstream cost rules, customer sell rates, packages and emergency kill switch.
- `ai_operations`: view model health, usage, errors and reconciliation; cannot reveal or export secrets.
- `billing_support`: view customer charges and payment status; cannot view prompt content or upstream credentials.
- `tenant_owner`: manage their own members and AI budget within the plan.
- `member`: use AI within assigned permissions and limits.

Require MFA for privileged roles and append-only audit entries for changes to model availability, pricing, package rules, supplier credentials, limits and refunds/adjustments.

## 45. Customer-facing model catalogue

### 45.1 Display-name policy

Customer model names below are proposed display names. The `asai/` identifier is deliberately excluded from the customer catalogue. Platform Owner can change display names and enable/disable models without changing the upstream credential.

| Customer display name | Internal upstream mapping (HQ only) | Context window | Max output | Capabilities supplied |
|---|---|---:|---:|---|
| GPT-6 Astra | `asai/gpt-6-astra` | 1,050,000 | 16,384 | Text, vision |
| Claude Opus 4.6 | `asai/claude-opus-4.6` | 1,000,000 | 16,384 | Text, vision |
| Claude Opus 4.7 | `asai/claude-opus-4.7` | 1,000,000 | 16,384 | Text, vision |
| Claude Opus 4.8 | `asai/claude-opus-4.8` | 1,000,000 | 16,384 | Text, vision |
| Claude Opus 5 | `asai/claude-opus-5` | 1,000,000 | 16,384 | Text, vision |
| Claude Opus 5.5 | `asai/claude-opus-5.5` | 1,000,000 | 16,384 | Text, vision |
| Claude Sonnet 5 | `asai/claude-sonnet-5` | 1,000,000 | 16,384 | Text, vision |
| Claude Sonnet 5.5 | `asai/claude-sonnet-5.5` | 1,000,000 | 16,384 | Text, vision |
| GPT-5.5 | `asai/gpt-5.5` | 1,050,000 | 16,384 | Text, vision |
| GPT-5.6 Sol | `asai/gpt-5.6-sol` | 1,050,000 | 16,384 | Text, vision |
| GPT-6 Sol | `asai/gpt-6-sol` | 1,050,000 | 16,384 | Text, vision |
| GPT-6.1 Sol | `asai/gpt-6.1-sol` | 1,050,000 | 16,384 | Text, vision |
| Muse Wonder 1.0 | `asai/muse-wonder-1.0` | 1,048,576 | 16,384 | Text |
| Claude Sonnet 4.6 | `asai/claude-sonnet-4.6` | 1,000,000 | 16,384 | Text, vision |
| Kimi K3 | `asai/kimi-k3` | 1,000,000 | 16,384 | Text, vision, video |
| GPT-5.6 Terra | `asai/gpt-5.6-terra` | 1,050,000 | 16,384 | Text, vision |
| Grok 4.7 | `asai/grok-4.7` | 500,000 | 16,384 | Text, vision |
| Qwen 3.8 Max | `asai/qwen3.8-max` | 1,000,000 | 16,384 | Text, vision, video |
| GPT-5.6 Luna | `asai/gpt-5.6-luna` | 1,050,000 | 16,384 | Text, vision |
| GPT-6 Luna | `asai/gpt-6-luna` | 1,050,000 | 16,384 | Text, vision |
| GLM-5.1 | `asai/glm-5.1` | 200,000 | 16,384 | Text |
| GLM-5.2 | `asai/glm-5.2` | 1,000,000 | 16,384 | Text |
| GLM-5.3 | `asai/glm-5.3` | 1,000,000 | 16,384 | Text |
| GLM-5.3 FlashX | `asai/glm-5.3-flashx` | 1,000,000 | 16,384 | Text, vision, video |
| DeepSeek V4 Pro | `asai/deepseek-v4-pro` | 1,000,000 | 16,384 | Text, vision |
| DeepSeek V4 Pro 0813 | `asai/deepseek-v4-pro-0813` | 1,000,000 | 16,384 | Text, vision |
| Muse Spark 1.2 | `asai/muse-spark-1.2` | 1,000,000 | 16,384 | Text |
| Muse Spark 1.3 | `asai/muse-spark-1.3` | 1,048,576 | 16,384 | Text |
| GLM-5.0 Turbo | `asai/glm-5.0-turbo` | 200,000 | 16,384 | Text |
| GLM-5V Turbo | `asai/glm-5v-turbo` | 200,000 | 16,384 | Text, vision, video |
| HY4 | `asai/hy4` | 1,000,000 | 16,384 | Text |
| MiMo V2.6 Pro | `asai/mimo-v2.6-pro` | 262,144 | 16,384 | Text |
| Qwen 3.7 Max | `asai/qwen3.7-max` | 1,000,000 | 16,384 | Text |
| Kimi K2.7 | `asai/kimi-k2.7` | 256,000 | 16,384 | Text, vision |
| Claude Haiku 4.5 | `asai/claude-haiku-4.5` | 200,000 | 16,384 | Text, vision |
| Claude Haiku 5.5 | `asai/claude-haiku-5.5` | 1,000,000 | 16,384 | Text, vision |
| GLM-5.3 Flash | `asai/glm-5.3-flash` | 1,000,000 | 16,384 | Text, vision, video |
| DeepSeek V4 Flash | `asai/deepseek-v4-flash` | 1,000,000 | 16,384 | Text |
| DeepSeek V4 Flash 0731 | `asai/deepseek-v4-flash-0731` | 1,000,000 | 16,384 | Text |
| DeepSeek V4.1 Flash | `asai/deepseek-v4.1-flash` | 1,000,000 | 16,384 | Text |
| MiniMax M3 | `asai/minimax-m3` | 1,000,000 | 16,384 | Text, vision, video |

**Catalogue rule:** The names and capabilities above reflect the supplied catalogue, not independent verification of upstream capability or model identity. At runtime, show only models that Admin HQ has enabled and health checks have marked usable. Do not expose the upstream model ID in HTML, browser network responses, client configuration, customer API responses or client-side logs. Since the server must send the ID upstream, it remains visible to privileged server-side code and must be protected there.

### 45.2 Internal-only rate data

The supplied model list also includes relative multipliers (for example, input/output/cache-like values such as `10x`, `2.5x`, `50x`). The original column labels and exact pricing unit were not provided in the request. Therefore:

- Preserve these supplied values as internal source metadata only until the upstream dashboard's column labels and billing formula are confirmed.
- Do not guess which multiplier corresponds to input, output, cache, image or video tokens.
- Store explicit `input_rate`, `output_rate`, `cached_input_rate`, other supported rate dimensions, currency/unit, source timestamp and pricing-rule version once verified.
- Customer-facing prices must be calculated from the verified rate card and the AI Bijan sell-rate policy, not from an unlabelled multiplier.
- Never display supplier multipliers, supplier costs, upstream provider name/namespace or internal margin calculations to customers.

## 46. Customer token packages and 30-day validity

The following are the requested **customer selling packages**. Each package is valid for 30 days, subject to the purchase terms shown at checkout.

| Package | Included token units | Selling price | Validity |
|---|---:|---:|---|
| AI Bijan Starter | 80,000,000 | RM80.00 | 30 days |
| AI Bijan Basic | 140,000,000 | RM110.00 | 30 days |
| AI Bijan Plus | 200,000,000 | RM160.00 | 30 days |
| AI Bijan Pro | 1,100,000,000 | RM600.00 | 30 days |
| AI Bijan Max | 2,250,000,000 | RM1,100.00 | 30 days |

The package labels are suggested names and may be changed in HQ. Clearly define in checkout whether the package starts at successful payment, whether unused units expire (recommended based on the requested 30-day validity), whether multiple active packages stack, and how refunds or chargebacks affect remaining balance. Never silently reset a customer's balance.

### 46.1 Supplier acquisition cost — Admin HQ only

These are internal purchase-cost figures supplied by the platform owner. They must not appear in customer pages, invoices, APIs, exports or support views without explicit privileged cost access.

| Token units | Internal acquisition cost | Validity |
|---:|---:|---|
| 80,000,000 | RM5.00 | 30 days |
| 140,000,000 | RM8.00 | 30 days |
| 200,000,000 | RM10.00 | 30 days |
| 1,100,000,000 | RM50.00 | 30 days |
| 2,250,000,000 | RM100.00 | 30 days |

### 46.2 Gross spread before other costs

Based only on the supplied acquisition cost and selling price:

| Token units | Sell price | Supplier cost | Gross spread | Gross margin on sales |
|---:|---:|---:|---:|---:|
| 80,000,000 | RM80.00 | RM5.00 | RM75.00 | 93.75% |
| 140,000,000 | RM110.00 | RM8.00 | RM102.00 | 92.73% |
| 200,000,000 | RM160.00 | RM10.00 | RM150.00 | 93.75% |
| 1,100,000,000 | RM600.00 | RM50.00 | RM550.00 | 91.67% |
| 2,250,000,000 | RM1,100.00 | RM100.00 | RM1,000.00 | 90.91% |

These are gross spreads, **not net profit**. They exclude payment gateway fees, taxes, hosting, refunds, fraud, support, unmetered/failed usage and any mismatch between the supplier's package units and the customer's token-metering units. Confirm that both sides define a “token” identically before launch.

## 47. Usage metering and financial ledger

### 47.1 Separate ledgers

Maintain distinct records for:

1. **Customer entitlement ledger:** purchased units, granted adjustments, reservations, actual consumption, releases, expiry and refunds.
2. **Supplier cost ledger:** upstream cost based on the supplier's actual billing/usage data and the applicable internal rate card.
3. **Customer revenue ledger:** payment-confirmed package sales, tax/fees, refunds and chargebacks.
4. **Reconciliation ledger:** mismatches between estimated and actual tokens, supplier usage, customer charge and payment settlement.

Never derive supplier cost from customer sell price, or customer charges directly from supplier cost without applying the active pricing rule.

### 47.2 Recommended records

- `ai_providers` — internal provider name, endpoint, status and adapter type; secret values excluded.
- `ai_provider_credentials` — encrypted credentials or secret-manager references, rotation metadata and restricted access.
- `ai_models` — internal upstream ID, customer display name, public alias, context/output limit, capability flags, endpoint compatibility, status and health.
- `ai_model_rate_cards` — internal input/output/cache/vision/video rates, unit, currency, effective dates and source.
- `ai_customer_pricing_rules` — sell-rate/margin rule, effective dates, tenant/plan overrides and version.
- `ai_token_packages` — package size, sell price, validity, availability and terms version.
- `ai_wallets` — tenant/customer balance and currency/unit.
- `ai_wallet_ledger` — append-only credit, reserve, consume, release, expire, refund and adjustment entries.
- `ai_usage_records` — tenant, user, request ID, model alias, token counts by dimension, status, latency and pricing version.
- `ai_supplier_cost_records` — supplier usage/cost and reconciliation reference; privileged access only.
- `ai_purchase_orders` / `ai_package_purchases` — package checkout and payment lifecycle.
- `ai_usage_reservations` — reservation amount, expiry, settlement and release state.
- `ai_rate_limit_buckets` — or an equivalent Redis-based mechanism for RPM and concurrency.
- `ai_model_health_checks` — availability, latency and last successful test.
- `ai_pricing_audit_logs` — immutable before/after values and actor.

Use integer token counts and decimal/integer minor currency units; do not use floating-point arithmetic for money. Ledger entries are append-only. Corrections must be reversal/adjustment entries.

### 47.3 Define what “token units” means

Do not assume the package's advertised 80M/140M/etc. units are directly comparable across models or token types. Before production, define one of these explicitly:

- **Raw tokens:** charge input and output tokens according to model-specific rates; or
- **AI Bijan credits:** normalize model input/output/cache/vision/video usage to a platform-defined unit using a published conversion formula.

Recommended approach: use **AI Bijan credits** as the customer's wallet unit if different models have different supplier multipliers. The UI can explain that different models consume credits at different rates. Store raw supplier token counts separately. Show an estimated credit cost before a request where technically feasible, and always show the final settled amount after usage is known.

The exact formula must be configured from verified upstream rate units. Do not implement a made-up conversion based solely on the model list's unlabeled multipliers.

### 47.4 Reservation and settlement invariants

- Reserve enough balance before any upstream call; reject if the available balance is insufficient.
- Make reservations and settlements idempotent using a unique request ID.
- Prevent two concurrent requests from spending the same balance.
- Settle actual usage and release unused reservation after a completed request.
- Apply the documented partial-stream policy when the client disconnects or upstream fails after output begins.
- Store raw input/output/cached token counts when provided; record unknown dimensions as unknown, not zero.
- Expire packages and reservations through scheduled jobs with idempotency protection.
- Make admin adjustments require a reason, permission and audit record.
- Never allow negative balances unless a specifically approved enterprise credit policy exists.
- Do not refund tokens and money twice when a payment webhook is replayed.

## 48. Admin HQ AI Supply & Reseller Console

Add these pages to the existing Filament Admin HQ:

1. **AI Overview:** requests, tokens/credits consumed, supplier cost, customer revenue, gross spread, failure rate, latency and balance liabilities.
2. **Provider Connections:** endpoint, connection health, masked key fingerprint, rotation date and test connection. Never reveal the saved key.
3. **Model Catalogue:** customer display name, internal mapping (privileged HQ only), capability flags, context/output limits, health, enable/disable and compatible endpoint.
4. **Internal Supplier Rates:** verified supplier input/output/cache/other rates, currency/unit, effective dates and source evidence.
5. **Customer Pricing:** credit conversion, customer sell rates, plan overrides, margin alerts, version history and preview calculator.
6. **Token Packages:** package units, price, validity, tax/terms configuration, enable/disable and sales summary.
7. **Usage Explorer:** filters by tenant, user, model, date, request ID, usage, charge, status and latency. Prompt/response content is excluded by default and only accessible under a separate, justified, audited support policy if retention is enabled.
8. **Supplier Reconciliation:** compare local usage records with supplier usage/cost reports; flag discrepancies.
9. **Customer Wallets:** balance, reservations, expiry, purchase history and audited adjustments.
10. **Rate Limits & Safety:** RPM, concurrency, maximum input/output, per-request credit cap, daily/monthly spend cap, abuse controls and global kill switch.
11. **Audit & Incidents:** pricing changes, secret rotations, disabled models, usage adjustments and supplier outages.

The dashboard must distinguish gross spread from net profit and from unused customer balance liabilities.

## 49. Security, privacy and abuse controls for AI resale

- Authenticate every inference request; public model listing contains display aliases only.
- Use a strict model allowlist and server-side model mapping.
- Enforce tenant isolation on usage, balance, purchase, key and log queries.
- Apply per-user, per-tenant and global request-rate/concurrency limits.
- Set input size, output token, timeout and total-cost ceilings.
- Restrict file/image/video payload size and verify supported model capabilities.
- Validate upstream URLs from server configuration only; never accept a customer-provided proxy URL.
- Use timeouts, bounded retries, circuit breakers and idempotent accounting.
- Never retry a potentially billable request blindly after output may have started; distinguish safe pre-delivery retries from uncertain partial delivery.
- Do not log raw API keys, auth headers or full sensitive prompts by default.
- Define prompt/response retention, export and deletion policies; provide notice about the external AI supplier and applicable data processing.
- Do not claim that hiding an upstream model name makes the integration impossible to reverse-engineer. The security boundary is the server-side credential and gateway, not obscurity of a display label.
- Verify the supplier's terms permit resale, multi-customer use, and the planned branding/white-label arrangement before commercial launch.

## 50. Rollout and acceptance criteria for v2.3

### Phase AI-1 — Secure gateway foundation
- Server-side supplier adapter and secret storage.
- Customer session authentication, tenant scope and model allowlist.
- Safe error normalization and capability validation.
- Tests prove the upstream key is absent from frontend bundles, browser responses, mobile packages and logs.

### Phase AI-2 — Catalogue and metering
- Admin HQ model management and public display aliases.
- Usage record schema, wallet ledger, reservation/settlement and rate-limit enforcement.
- Tests for concurrency overspend, duplicate requests, failed requests, partial streaming and settlement replay.

### Phase AI-3 — Packages and payments
- Customer package UI, checkout, payment callback verification and 30-day expiry.
- Grant credits only once after verified payment.
- Refund/chargeback and expired-balance policy implemented and displayed before purchase.

### Phase AI-4 — Pricing and reconciliation
- Verify supplier rate units and the meaning of each model multiplier.
- Configure model-specific credit conversion and customer sell-rate rules.
- Compare local records with supplier usage; alert on discrepancies.
- Confirm unit economics including fees, taxes, support, failed/partial requests and package utilization.

### Phase AI-5 — Production release gate
Do not enable paid resale until all of the following pass:

- [ ] Supplier contract/terms allow resale and multi-tenant use.
- [ ] Supplier key is stored server-side and can be rotated.
- [ ] Customers see display aliases only; internal IDs are not returned in public catalogue/API.
- [ ] Unknown models and client-supplied upstream IDs are rejected.
- [ ] Token/credit reservations prevent concurrent overspending.
- [ ] Supplier cost and customer usage are recorded separately.
- [ ] Payment webhooks, package grants, refunds and expiry are idempotent.
- [ ] Customer balances, internal costs and revenue reconcile.
- [ ] Limits and global kill switch have been tested.
- [ ] Privacy notice and AI supplier disclosure have been reviewed.
- [ ] Model capabilities and endpoints have been tested rather than assumed.
- [ ] Monitoring, alerting, backup and incident runbooks are operational.

## 51. v2.3 final rule

**AI Bijan is the AI service provider from the customer's perspective.** Customers select AI Bijan's approved model display names, buy AI Bijan packages and see their own usage and balance. The platform owner alone controls upstream supply, credentials, internal model mappings, supplier costs and pricing rules. Every request passes through the AI Bijan gateway and every billable usage event is accounted for.

The upstream endpoint and model list in this addendum are configuration inputs supplied for the design. They are not a guarantee of current availability, contractual resale permission, model identity or feature parity. Verify these items before production.


---

# v2.4 Extension — Shared AI Agent Memory & Inter-Agent Collaboration

## 52. Product decision and core principle

All AI Agents in AI Bijan must have memory appropriate to their role and must be able to collaborate with other authorised agents. Agents must not behave as isolated chat windows that forget relevant context between sessions. Memory and collaboration are platform capabilities, not features tied to a specific model vendor.

Customers buy a shared token package first, then choose an allowed model for each agent. Each agent may use a different model, but memory access, permissions, tool access, task history and collaboration rules are governed by AI Bijan. Changing an agent's model must not erase that agent's memory or change its access permissions.

## 53. Memory types and retention boundaries

Implement memory as distinct types with explicit scope, owner, purpose, retention and deletion rules:

1. **Working memory:** temporary context for the current conversation or task; expires according to session/task policy.
2. **Agent memory:** durable, role-specific preferences, instructions, decisions and useful summaries belonging to one agent and its owner/tenant.
3. **Customer/tenant memory:** approved facts and preferences shared across the customer's agents, such as business profile, tone of voice, approved service catalogue and operating procedures.
4. **Project/task memory:** shared context for a defined project, case, campaign or workflow, accessible only to agents and users assigned to it.
5. **Operational memory:** task status, prior actions, outcomes, failures and idempotency references required to continue work safely. Do not store secrets or raw credentials here.
6. **Knowledge sources:** uploaded documents, connected data and approved records. Keep source references, permissions, timestamps and provenance so answers can be traced to their origin.

Memory must not mean indiscriminately storing every conversation forever. Apply data minimisation: extract useful, bounded facts or summaries, retain source references where needed, and avoid storing unnecessary sensitive information. Do not infer or save sensitive personal traits as durable memory unless there is a lawful, explicit and necessary basis and the product policy permits it.

## 54. Memory lifecycle and controls

Every memory item should support, where applicable:

- `tenant_id`, `owner_user_id`, `agent_id` (nullable for tenant-wide memory), `project_id` (nullable), `memory_type`, `content` or encrypted payload reference, `source_type`, `source_id`, `created_at`, `updated_at`, `expires_at`, `sensitivity`, `confidence`, `status`, `retention_policy_id` and `deleted_at`.
- Capture: store only useful information, with source/provenance and a reason for retention.
- Validation: deduplicate, resolve contradictions by recency and trusted source, and ask the user when a material conflict cannot be resolved safely.
- Retrieval: fetch only relevant memories that the current user, agent and task are authorised to access; rank by relevance, freshness and trust.
- Update: retain history for audit where required, but never allow stale memory to silently override a newer explicit instruction.
- Expiry: apply configurable TTLs and scheduled cleanup for temporary memory and expired task context.
- User control: provide UI to inspect, correct, pin, disable, export where appropriate, and delete memory within the tenant's permitted scope.
- Tenant offboarding/deletion: execute a documented deletion or retention workflow for database records, vector indexes, caches, derived summaries and backups according to the published policy and legal requirements.

Memory retrieval must be permission-filtered before content is sent to a model. Vector similarity is not an authorisation mechanism. Do not place credentials, API keys, payment secrets or authentication tokens in prompts, embeddings or ordinary memory records.

## 55. Shared memory without cross-tenant leakage

Agents in the same tenant may share tenant-wide memory only when their role and task permissions allow it. Agent-specific private memory remains private by default. Project memory is shared only with project members. Cross-tenant memory sharing is prohibited by default, including via vector search, cache keys, logs, analytics and agent handoffs.

Enforce tenant and permission filters in the database/service layer, not just in prompts. Include tenant and scope identifiers in retrieval filters and cache keys. Use access-control tests that deliberately attempt to retrieve another tenant's memories. A model must never be trusted to enforce tenancy on its own.

## 56. Agent identity, roles and collaboration protocol

Each agent has a stable platform identity and configuration:

- `agent_id`, tenant/owner, name, role, purpose, system instructions, allowed model alias, allowed tools, memory scopes, collaboration policy, budget/usage limit, status and version.
- Each agent may select a model from the Admin HQ allowlist. Public APIs accept AI Bijan model aliases only; never accept arbitrary upstream provider model IDs from clients.
- Agents can discover and request help from other agents only through the AI Bijan orchestration service.
- Every handoff includes a structured task brief: objective, relevant authorised context references, constraints, expected output schema, deadline/timeout, budget ceiling and correlation ID.
- The receiving agent returns a structured result with status, findings, source references, tool/action log references, uncertainty and recommended next step.
- The orchestrator decides whether to accept, validate, retry, delegate again or return the result to the user. Agents cannot grant themselves permissions or elevate another agent's permissions.

Example collaboration: a Business Planner creates a campaign brief; a Research Agent gathers evidence; a Content Agent drafts material; a Compliance/Reviewer Agent checks it; a Coordinator presents the consolidated result. This is an example workflow, not a claim that these agents exist until configured and tested.

## 57. Orchestration, loops and safe execution

Implement a central orchestration layer with explicit workflow states such as `queued`, `running`, `waiting_for_agent`, `needs_user_approval`, `succeeded`, `failed`, `cancelled` and `timed_out`.

Required controls:

- Maximum delegation depth, agent count, retries, wall-clock duration and token/credit budget per workflow.
- Detect repeated handoffs, circular delegation and agents returning equivalent work without progress; terminate safely when limits are reached.
- Use idempotency keys for tool actions and billing events. A retry must not duplicate a payment, message, booking, record update or charge.
- Require user approval for high-impact or externally visible actions according to configurable policy; collaboration alone is not consent.
- Read-only by default for newly created agents until tools and permissions are explicitly granted.
- Separate planning from execution. Validate structured arguments and tool permissions server-side before executing any action.
- Support cancellation, pause/resume, timeout, partial failure and a human handoff.
- Keep model-generated instructions, uploaded content and retrieved documents as untrusted data. They cannot override platform policy, system instructions or access controls.

## 58. Context assembly and model portability

Before each model call, assemble only the context needed for the current task: system policy, agent role, user request, relevant working memory, authorised long-term memories, approved knowledge excerpts, and handoff/task data. Apply token budgets and prioritise current instructions over stale summaries.

Memory and orchestration formats must be model-neutral. A model switch must preserve agent identity, memory records, task history and tool permissions. Where a model lacks a capability (for example, vision input or tool calling), disable or adapt that capability transparently; do not pretend every model supports the same features.

The model provider gateway remains responsible for server-side supplier credentials, capability validation, usage metering and normalised errors. Store supplier IDs and credentials only in server-side configuration. The customer-facing UI displays AI Bijan model names/aliases and verified capabilities, not internal supplier identifiers.

## 59. Memory-aware usage metering and cost controls

All model calls made during a multi-agent workflow must be attributed to the tenant, initiating user, workflow, calling agent, target agent, model alias and request/correlation ID. Record input, cached input and output usage separately where the supplier provides reliable data, alongside estimated and final charge, supplier cost, status and reconciliation data.

- Reserve budget before each model call and delegation; settle against actual usage after completion.
- Apply per-agent, per-workflow, per-user and tenant-wide budgets and rate limits.
- Include retrieval, summarisation, reviewer passes and agent-to-agent messages in usage reporting when they invoke billable model calls.
- Do not charge for failed or cancelled calls unless supplier billing and the disclosed customer policy justify a charge; make partial/streamed response rules explicit.
- Provide a usage view showing which agents and workflows consumed the balance, with clear timestamps and model display names.
- Never silently switch to a more expensive model when a selected model fails. Follow the configured fallback policy and disclose material changes where required.

## 60. Admin HQ and customer UI requirements

### Customer workspace

- Create, rename, pause, archive and delete agents according to permissions.
- Configure each agent's role, instructions, allowed model, memory mode, shared-memory scopes, tools and spending limits.
- View and search agent memories; edit, pin, disable or delete items within policy.
- Choose whether an agent may collaborate, which agents it may contact, and what project/tenant memory it can access.
- View workflow timeline, agent handoffs, final consolidated answer, usage breakdown and approval requests.
- Display clear indicators when an agent is using memory, delegating work, waiting for another agent, or awaiting user approval.

### Admin HQ

- Manage model catalogue, display aliases, capabilities, rates, supplier mapping and global availability.
- Configure memory retention defaults, maximum context/retrieval limits and supported data regions where applicable.
- Configure collaboration policies, allowed agent types, workflow limits, global spending limits and kill switches.
- Audit memory access, edits/deletions, cross-agent handoffs, tool execution, model calls and budget settlements.
- Monitor failed retrievals, suspected tenant-scope violations, loops, unusual usage spikes and supplier reconciliation differences.
- Use restricted support access with reason, scope and audit trail; no unrestricted browsing of customer memories by default.

## 61. Suggested technical modules and data entities

Keep this capability modular and testable. Suggested entities/tables (adapt to the existing v2.3 database conventions rather than creating duplicate concepts):

- `ai_agents`
- `ai_agent_model_assignments` or a versioned model field on `ai_agents`
- `ai_memory_items`
- `ai_memory_sources`
- `ai_memory_access_events`
- `ai_projects` and `ai_project_members`
- `ai_workflows`
- `ai_workflow_steps`
- `ai_agent_handoffs`
- `ai_tool_permissions`
- `ai_tool_executions`
- `ai_usage_reservations` and `ai_usage_ledger` (reuse v2.3 wallet/ledger if present; do not create a competing balance system)

Use the existing identity, tenant, wallet and audit systems as the source of truth. Add indexes for tenant/scope/owner/status/expiry and correlation IDs. For semantic retrieval, the vector store is an index, not the source of truth; preserve source records and access checks in the primary data layer.

## 62. v2.4 security, privacy and acceptance tests

Before production release, test at minimum:

- Agent A can retrieve approved tenant-wide memory but cannot retrieve Agent B's private memory without permission.
- Tenant A cannot retrieve Tenant B's memory through keyword search, vector search, cache, exports or agent delegation.
- A user can inspect and correct stored memories and invoke deletion; deleted records are excluded from retrieval and are purged from derived indexes according to policy.
- New explicit instructions supersede conflicting stale memories, and ambiguous material conflicts trigger clarification or safe abstention.
- A malicious prompt in a document or agent handoff cannot override system policy, reveal secrets or grant tools.
- Delegation loops, runaway retries and budget exhaustion stop within configured limits.
- A cancelled/failed workflow does not double-charge or repeat non-idempotent actions.
- Each billable sub-agent call is attributable and reconciled; hidden supplier IDs and API keys never appear in frontend responses or client logs.
- Switching an agent's model preserves its memory and permissions while adapting to the new model's verified capabilities.
- Tool execution requires server-side permission checks and approval when policy requires it.
- Memory retention, export, deletion and tenant offboarding follow documented policy and applicable legal obligations.

## 63. v2.4 final rule

**Every AI Agent in AI Bijan has role-appropriate memory and can collaborate with other authorised agents through a governed orchestration layer.** Memory is scoped, traceable, editable and subject to retention controls. Collaboration is budgeted, permission-checked and auditable. Shared memory never means unrestricted access, and agent-to-agent communication never bypasses tenant security, user approval or the central AI gateway.

This blueprint defines the intended architecture. Implementation, model capability, supplier behaviour and compliance must be verified with tests and operational review before being represented as production-ready.
