# AI BIJAN SaaS --- Master Business Ecosystem Blueprint

**Versi:** 3.0\
**Status:** Master blueprint untuk perancangan produk dan pembangunan\
**Pasaran permulaan:** Malaysia\
**Bahasa dokumen:** Bahasa Melayu\
**Nama produk:** AI BIJAN SaaS

------------------------------------------------------------------------

## 1. Visi Produk

AI BIJAN ialah platform SaaS modular untuk membantu pelbagai jenis
perniagaan mengurus operasi, jualan, pelanggan, kewangan, pekerja,
inventori, projek dan automasi AI dalam satu ekosistem.

Matlamatnya bukan membina satu aplikasi generik yang memaksa semua
bisnes menggunakan fungsi sama. Matlamatnya ialah menyediakan:

1.  **Shared Core** yang digunakan bersama oleh semua modul.
2.  **Business Modules** dengan fungsi dan peraturan tersendiri.
3.  **Industry Modules** untuk aliran kerja khusus industri.
4.  **Industry Blueprint Engine** untuk mengkonfigurasi sistem mengikut
    jenis bisnes.
5.  **B-Brain Hub** sebagai lapisan AI yang boleh membantu pengguna dan
    modul dengan kawalan akses.
6.  **SaaS Commercial Engine** untuk subscription, add-on, token AI,
    pakej dan penggunaan.

Prinsip utama: **satu platform, banyak sistem khusus, satu akaun
perniagaan, modul yang boleh dipilih, data yang terasing dengan
selamat.**

AI BIJAN tidak boleh mendakwa menyokong sesuatu industri sepenuhnya
hanya kerana modul asas seperti invois atau dashboard tersedia. Setiap
industri mesti mempunyai aliran kerja, konfigurasi, pengujian dan
dokumentasi penerimaan tersendiri.

------------------------------------------------------------------------

## 2. Prinsip Seni Bina

### 2.1 Modular, bukan satu sistem bercampur

Setiap modul mesti mempunyai: - Tujuan dan skop yang jelas. - Pengguna
dan peranan yang sesuai. - Model data/domain sendiri. - Peraturan
perniagaan tersendiri. - Halaman dan aliran kerja khusus. - Permission
matrix. - API/event contract. - Validasi, audit dan ujian. - Integrasi
yang didokumenkan dengan modul lain.

Modul boleh menggunakan infrastruktur bersama, tetapi tidak boleh
mengambil alih tanggungjawab modul lain.

### 2.2 Sumber data rasmi

Contoh: - B-POS merekod transaksi jualan. - B-Inventory ialah sumber
rekod pergerakan stok. - B-Loyalty ialah sumber rekod mata ganjaran. -
B-Booking ialah sumber rekod tempahan. - B-HRMS ialah sumber rekod
pekerja dan shift. - B-Finance ialah sumber rekod perakaunan. -
B-Invoicing mengurus dokumen invois dan status pengebilan. - B-Logistics
mengurus penghantaran dan bukti serahan. - B-Brain Hub memberikan
analisis dan cadangan; ia tidak boleh mengubah data kritikal tanpa
kebenaran serta pengesahan yang sesuai.

Integrasi hendaklah menggunakan API/event yang jelas, idempotency, log,
pengendalian ralat dan mekanisme retry. Elakkan pendua rekod akibat
event dihantar lebih daripada sekali.

### 2.3 Multi-tenant dari awal

Setiap data perniagaan mesti dikaitkan dengan organisasi/tenant yang
betul. Pengasingan mesti dikuatkuasakan di backend dan pangkalan data,
bukan sekadar menyembunyikan menu di UI.

Entiti teras yang dicadangkan: - `tenant` / `organization` - `user` -
`membership` - `role` - `permission` - `subscription` -
`module_entitlement` - `usage_record` - `audit_event`

Semua query, job latar, eksport, fail, webhook dan panggilan AI mesti
memeriksa tenant serta permission. Ujian mesti mengesahkan Tenant A
tidak boleh membaca atau mengubah data Tenant B.

### 2.4 Modul pilihan

Pelanggan tidak diwajibkan membeli semua modul. Admin platform boleh
menetapkan: - Modul termasuk dalam pelan. - Modul add-on. - Had
pengguna, cawangan, transaksi, storan atau API. - Tempoh percubaan. -
Harga dan kitaran langganan. - Kelayakan token AI. - Hak akses mengikut
role.

------------------------------------------------------------------------

## 3. Peta Lapisan Platform

### Lapisan A --- Shared Core

-   B-Auth & Security
-   Tenant & Organization Management
-   Membership, Role & Permission
-   Subscription & Entitlement
-   Billing & Usage Metering
-   Notifications
-   File & Document Storage
-   Audit Trail
-   API foundation
-   Localization, timezone, currency and tax configuration

### Lapisan B --- Business Modules

-   B-Virtual Office
-   B-HRMS
-   B-Sign & Docs
-   B-Projects
-   B-Assets
-   B-POS System
-   B-Site
-   B-Link
-   B-Marketplace
-   B-Affiliate
-   B-Loyalty
-   B-Omnichannel
-   B-Booking
-   B-Marketing
-   B-Helpdesk / B-Desk
-   B-COGS Engine
-   B-Inventory & Warehouse
-   B-BOM & Manufacturing
-   B-Finance
-   B-Invoicing
-   B-Procurement
-   B-Logistics
-   B-Connect / API Gateway
-   B-Analytics
-   B-Brain Hub

### Lapisan C --- Industry Modules

-   B-Hospitality
-   B-Clinic & Care
-   B-Education
-   B-Construction
-   B-Property
-   B-Automotive
-   B-Fleet
-   B-Agriculture
-   B-Event & Rental
-   B-Quality & Compliance
-   B-Subscription Business
-   B-Insurance & Claims (tertakluk kepada skop, lesen dan pematuhan
    yang berkaitan)

### Lapisan D --- Industry Blueprint Engine

Memilih konfigurasi, modul, workflow, borang, istilah, KPI, role dan
laporan berdasarkan industri serta model operasi organisasi.

### Lapisan E --- AI Intelligence

B-Brain Hub, model AI yang dibenarkan admin, AI agents, memori,
orkestrasi, token wallet, guardrails dan audit.

------------------------------------------------------------------------

## 4. Katalog Modul Perniagaan

## 4.1 Operasi Dalaman & Pasukan

### B-Virtual Office

**Tujuan:** pusat operasi harian dan koordinasi kerja. - AI agents dan
arahan kerja. - Task, board, checklist, due date dan assignment. -
Kalendar, mesyuarat dan jadual pasukan. - Pengumuman dan komunikasi
dalaman. - Approval workflow. - Status kerja dan ringkasan operasi.

**Perbezaan daripada B-Projects:** Virtual Office mengurus kerja harian
dan koordinasi umum; B-Projects mengurus projek formal dengan skop,
bajet, milestone, dependency dan timesheet.

### B-HRMS

**Tujuan:** pengurusan sumber manusia. - Profil pekerja, jabatan,
jawatan dan dokumen. - Kehadiran, shift, cuti, overtime dan timesheet. -
Pengiraan gaji, elaun, potongan dan slip gaji. - Onboarding/offboarding
dan rekod prestasi. - Komisen dan jadual kerja apabila dikonfigurasi.

Keperluan Malaysia seperti payroll, caruman, cukai, cuti dan rekod
pekerjaan mesti menggunakan peraturan terkini yang disahkan sebelum
production. Jangan hardcode kadar yang boleh berubah.

### B-Sign & Docs

**Tujuan:** dokumen perniagaan dan proses kelulusan. - Template,
dokumen, versi dan folder. - Quotation, kontrak, surat tawaran dan
borang. - Approval, e-signature dan status dokumen. - Audit trail,
access control dan expiry. - PDF export dan integrasi storan.

### B-Projects

**Tujuan:** pengurusan projek formal. - Project, client, scope,
milestone dan deliverable. - Task, dependency, Gantt dan Kanban. -
Bajet, kos, timesheet dan billable hours. - Risiko, isu, perubahan skop
dan laporan. - Keuntungan projek melalui integrasi dengan
B-Finance/B-Invoicing.

### B-Assets

**Tujuan:** pengurusan aset fizikal dan bukan stok jualan. - Asset
register, kategori, tag dan nombor siri. - Lokasi, pemegang aset dan
status. - Susut nilai mengikut polisi yang dikonfigurasi. - Jadual
maintenance, warranty dan sejarah servis. - Pelupusan dan audit aset.

**Perbezaan daripada B-Inventory:** Assets mengurus aset jangka panjang;
Inventory mengurus stok, bahan dan barang yang bergerak atau dijual.

------------------------------------------------------------------------

## 4.2 Jualan, Saluran & Pertumbuhan

### B-POS System

**Tujuan:** transaksi jualan di kaunter dan outlet. - Product lookup,
barcode, variasi dan harga. - Cart, diskaun, cukai dan pelbagai kaedah
bayaran. - Cash register, cash shift, cash-in/out dan reconciliation. -
Resit, void, refund dan return dengan permission. - Multi-outlet,
juruwang dan laporan shift. - Integrasi dengan Inventory, Loyalty,
Finance dan Invoicing. - Mod offline hanya jika direka dengan
penyegerakan dan kawalan konflik yang selamat.

### B-Site

**Tujuan:** website dan kandungan digital. - Website/page builder dan
template. - Theme, SEO, metadata dan sitemap. - Custom domain, SSL
status dan publishing. - Landing page, borang lead dan kandungan. -
Integrasi katalog, booking, marketplace dan analytics.

### B-Link

**Tujuan:** pautan digital dan identiti perniagaan. - Digital business
card dan link-in-bio. - QR code, portfolio dan social links. -
WhatsApp/contact actions dan lead capture. - Click analytics dan
campaign tracking. - Pengurusan pautan, redirect dan status.

### B-Marketplace

**Tujuan:** jual beli produk atau servis melalui katalog dan pesanan. -
Seller/vendor onboarding. - Listing, kategori, variasi dan harga. -
Cart, checkout, order dan fulfilment. - Platform fee, komisen, refund
dan seller settlement. - Penghantaran untuk produk atau integrasi
B-Booking bagi servis. - Moderation, dispute dan polisi seller.

### B-Affiliate

**Tujuan:** pengesanan referral dan komisen pemasaran. - Affiliate
account, referral code dan tracking link. - Attribution rules dan
attribution window. - Commission rules, pending/approved/rejected. -
Fraud checks, payout batch dan laporan. - Integrasi dengan Marketplace,
Site dan Marketing. - Komisen tidak boleh diluluskan hanya berdasarkan
klik; gunakan transaksi yang sah dan polisi yang jelas.

------------------------------------------------------------------------

## 4.3 Pelanggan, Pemasaran & Sokongan

### B-Loyalty

**Tujuan:** program keahlian dan ganjaran. - Member profile, QR/member
code dan tier. - Points ledger, earn, redeem, expiry dan adjustment. -
Rewards, vouchers dan referral rewards. - Polisi ganjaran mengikut
produk, outlet atau kempen. - Audit untuk perubahan mata dan pencegahan
penebusan berganda. - Integrasi POS, Marketplace dan Marketing.

### B-Omnichannel

**Tujuan:** menyatukan komunikasi pelanggan. - Unified inbox untuk
saluran yang benar-benar disambungkan. - Conversation, channel, message
dan customer context. - Assignment, team inbox, tags dan SLA ringkas. -
Template mesej dan automation. - Consent, opt-out, retention dan log
penghantaran. - Integrasi saluran pihak ketiga tertakluk kepada API,
kebenaran dan polisi penyedia.

### B-Booking

**Tujuan:** tempahan berasaskan masa, kapasiti atau ketersediaan. -
Service, provider, staff, slot dan availability. - Appointment,
reschedule, cancellation dan waitlist. - Deposit, bayaran, refund rules
dan reminder. - Resource/room booking jika diperlukan. - Service area,
lokasi dan recurring appointment. - Integrasi B-HRMS untuk jadual staf
dan B-Finance/B-Invoicing untuk transaksi.

### B-Marketing

**Tujuan:** kempen dan pertumbuhan pelanggan. - Customer segments dan
consent management. - Campaign, voucher, offer dan promotion. - E-mel,
SMS atau saluran lain yang diintegrasikan. - Trigger-based automation
dan campaign calendar. - Conversion, ROI dan attribution. - Ads Manager
sebagai submodul berasingan untuk akaun iklan yang disokong; bukan
menggantikan platform iklan pihak ketiga.

### B-Helpdesk / B-Desk

**Tujuan:** menyelesaikan aduan dan kes sokongan. - Ticket, category,
priority, status dan assignment. - SLA, escalation dan response
templates. - Knowledge base dan FAQ. - Customer satisfaction dan
resolution metrics. - Internal notes dan audit. - **Perbezaan daripada
B-Omnichannel:** Omnichannel mengurus komunikasi; Helpdesk mengurus kes
sehingga selesai.

------------------------------------------------------------------------

## 4.4 Stok, Pengeluaran & COGS

### B-COGS Engine

**Tujuan:** kos barang dijual dan analisis margin. - Product cost
calculation. - Material, purchase cost, production cost dan kos
berkaitan yang layak. - Kaedah kos yang dipilih dan dikonfigurasi. -
Margin kasar, contribution dan analisis variasi kos. - Integrasi
Inventory, BOM, POS dan Finance. - Semua formula dan andaian kos mesti
boleh diaudit.

### B-Inventory & Warehouse

**Tujuan:** kawalan stok dan pergerakan barang. - Product, SKU, barcode
dan unit of measure. - Stock movement ledger. - Warehouse, bin/location
dan stock transfer. - Batch, lot, serial number dan expiry date. -
Reorder point, stock count, adjustment dan reservation. - Receiving,
returns dan stock valuation. - Setiap perubahan stok mesti mempunyai
sumber, masa, pelaku dan sebab.

### B-BOM & Manufacturing

**Tujuan:** mengubah bahan menjadi produk siap. - Bill of Materials dan
versi resipi. - Work order dan production planning. - Material
issue/return dan finished goods. - Yield, wastage, scrap dan
by-product. - Batch/lot traceability dan quality checkpoints. - Actual
vs standard production cost. - Integrasi Inventory, COGS, Procurement
dan Assets.

------------------------------------------------------------------------

## 4.5 Kewangan, Pembelian & Logistik

### B-Finance

**Tujuan:** rekod perakaunan dan laporan kewangan. - Chart of
accounts. - Double-entry journal entries. - Accounts
receivable/payable. - Bank transactions dan reconciliation. - General
ledger, trial balance, P&L, balance sheet dan cash flow. - Period close,
approval dan audit. - Integrasi POS, Invoicing, Procurement, Inventory
dan Payroll. - Catatan automatik mesti idempotent dan boleh dijejak ke
transaksi asal.

### B-Invoicing

**Tujuan:** dokumen bil dan kutipan bayaran. - Quote, invoice, recurring
invoice dan credit note. - Customer billing profile, tax, due date dan
payment status. - PDF, reminder dan payment link. - Deposit, partial
payment dan reconciliation reference. - MyInvois/e-Invoice integration
apabila diperlukan, disokong dan disahkan mengikut peraturan semasa
Malaysia. - **Perbezaan daripada B-Finance:** Invoicing mengurus dokumen
bil; Finance mengurus catatan perakaunan.

### B-Procurement

**Tujuan:** pembelian daripada pembekal. - Supplier, purchase request
dan approval. - Request for quotation dan price comparison. - Purchase
order, goods receipt dan supplier bill. - Matching PO/receipt/bill. -
Supplier performance dan purchase history. - Integrasi Inventory dan
Finance.

### B-Logistics

**Tujuan:** penghantaran dan pergerakan pesanan. - Shipment, consignment
dan delivery job. - Carrier/courier integration. - Tracking events dan
delivery status. - Driver assignment, proof of delivery dan failed
delivery. - Return shipment dan shipping cost. - **Perbezaan daripada
Inventory:** Inventory mengurus kuantiti stok; Logistics mengurus
penghantaran dari asal ke destinasi.

------------------------------------------------------------------------

## 4.6 Infrastruktur & Integrasi

### B-Connect / API Gateway

-   REST API dan versi API.
-   Webhooks, signing, retries dan delivery logs.
-   OAuth/client credentials dan API keys.
-   Rate limit, quota, scopes dan access policy.
-   Idempotency keys dan request tracing.
-   Developer documentation dan sandbox.
-   Secrets tidak boleh dipaparkan kepada pengguna atau dimasukkan ke
    dalam log.

### B-Auth & Security

-   Authentication, MFA/2FA dan session management.
-   Tenant, organization, membership dan invitations.
-   RBAC/permission dan least privilege.
-   Audit log dan security events.
-   Encryption in transit/at rest mengikut keperluan.
-   Password reset, account recovery dan suspicious login controls.
-   Data export/deletion workflows mengikut polisi dan kewajipan
    undang-undang.
-   Tenant isolation test dan access review.

------------------------------------------------------------------------

## 4.7 Dashboard & Intelligence

### B-Analytics

-   KPI, dashboard dan laporan berasaskan role.
-   Filter tarikh, cawangan, produk dan segmen.
-   Export CSV/PDF dengan permission.
-   Metric definitions yang konsisten.
-   Drill-down ke rekod sumber jika dibenarkan.
-   Data freshness, lineage dan indikator kualiti data.

### B-Brain Hub

**Tujuan:** lapisan AI dan agent untuk membantu operasi. - AI agent
builder dan agent profile. - Model catalog yang dikawal Admin HQ. -
Model selection per agent. - Agent memory, task context dan knowledge
sources. - Tool permissions, approval gate dan audit. - Multi-agent
collaboration/orchestration. - Insight, forecasting, recommendation dan
report generation. - Token wallet, usage metering, limits dan billing. -
Human approval untuk tindakan berisiko seperti pembayaran, refund,
payroll, penghapusan atau perubahan rekod kewangan. - AI tidak boleh
membaca tenant lain atau memintas permission pengguna.

------------------------------------------------------------------------

## 5. Modul Khusus Industri

Modul ini melengkapi modul generik. Ia bukan sekadar tema UI; ia mesti
mempunyai data dan workflow khusus.

### B-Hospitality

Untuk hotel, homestay, resort dan penginapan: - Property, room/unit,
room type dan rate plan. - Availability, reservation,
check-in/check-out. - Guest profile, deposit dan cancellation policy. -
Housekeeping, room maintenance dan occupancy report. - Integrasi
Booking, Invoicing, Finance dan Omnichannel.

### B-Clinic & Care

Untuk klinik dan penyedia penjagaan yang layak: - Patient/client
records, appointment dan consent. - Rekod konsultasi/rawatan yang sesuai
dengan skop. - Staff/practitioner scheduling. - Billing dan rekod
follow-up. - Kawalan akses lebih ketat, audit, retention dan
perlindungan data kesihatan. - Tidak menggantikan sistem klinikal yang
diwajibkan atau mengesahkan pematuhan secara automatik. Keperluan lesen
dan undang-undang perlu disahkan.

### B-Education

Untuk tadika, sekolah, pusat tuisyen dan latihan: - Student, guardian,
class, course dan enrolment. - Timetable, attendance dan teacher
allocation. - Fees, payment plans dan arrears. - Assessment, result,
certificate dan progress. - Parent/student portal dan komunikasi. -
Consent dan kawalan data kanak-kanak apabila berkenaan.

### B-Construction

Untuk kontraktor, subkontraktor dan projek pembinaan: - Tender, estimate
dan project budget. - Site, work package dan subcontractor. - BOQ,
variation order dan progress claim. - Site diary, material request dan
equipment. - Safety checklist, inspection dan document control. -
Retention sum, claim status dan cost-to-complete. - Integrasi Projects,
Procurement, Assets, Invoicing dan Finance.

### B-Property

Untuk agensi hartanah, pemilik aset dan pengurusan bangunan: - Property,
building, unit dan ownership/management assignment. - Listing, viewing
dan enquiry. - Tenancy, lease dates, deposit dan rent schedule. -
Maintenance request dan contractor assignment. - Occupancy, arrears dan
renewal reminders. - Akses kepada data pemilik/penyewa perlu dikawal.

### B-Automotive

Untuk bengkel, pusat servis, tayar, detailing dan pusat automotif: -
Vehicle profile, plate number, make/model dan odometer. -
Customer-vehicle relationship dan service history. - Job card,
inspection checklist dan diagnosis notes. - Parts/labour estimate,
approval dan work status. - Warranty, parts usage dan service
reminder. - Integrasi Booking, Inventory, POS, Invoicing dan Finance.

### B-Fleet

Untuk syarikat kenderaan, penghantaran dan pengangkutan: - Vehicle,
driver, licence/permit records. - Trip, route, assignment dan mileage. -
Fuel, toll, maintenance dan downtime. - Inspection, insurance/road tax
reminders. - Delivery/trip cost and utilization reports. - Integrasi
Logistics, Assets, HRMS dan Finance.

### B-Agriculture

Untuk pertanian, ternakan, akuakultur dan nurseri: - Farm, plot, crop,
season dan planting schedule. - Input, fertilizer, feed, treatment dan
equipment. - Livestock/batch, health records dan breeding cycles apabila
relevan. - Harvest, yield, grading, loss dan traceability. -
Weather/data integrations hanya jika disambungkan dan tersedia. -
Integrasi Inventory, Procurement, COGS, Logistics dan Finance.

### B-Event & Rental

Untuk event planner, dewan, vendor dan penyewaan: - Event, venue,
date/time dan capacity. - Package, vendor, quotation dan event
timeline. - Equipment inventory, rental availability dan deposit. -
Setup/teardown schedule dan assigned crew. - Contract, milestone
payment, cancellation dan refund rules. - Integrasi Booking,
Marketplace, Projects, Assets dan Invoicing.

### B-Quality & Compliance

Untuk operasi yang memerlukan pemeriksaan atau kawalan kualiti: - SOP,
checklist, inspection dan non-conformance. - Corrective/preventive
action (CAPA). - Batch quality, document versions dan approval. -
Incident register, training records dan audit preparation. - Retention
dan approval policies. - Sistem membantu mengurus bukti dan proses; ia
tidak menjamin pensijilan atau pematuhan secara automatik.

### B-Subscription Business

Untuk SaaS, gym, membership dan servis berulang: - Plan, billing cycle
dan renewal. - Subscription status, trial, upgrade/downgrade. - Usage
limits, add-ons, proration rules jika disokong. - Payment retry, dunning
dan cancellation. - Customer portal dan invoice history. - Perlu
dibezakan daripada subscription AI BIJAN sendiri: modul ini boleh
digunakan pelanggan untuk mengurus langganan mereka.

### B-Insurance & Claims (pilihan strategik)

Untuk aliran kerja insurans yang sah dan sesuai: - Policy/customer
records, claim intake dan document checklist. - Claim status, task
assignment dan audit. - Partner/provider directory. - Role dan data
restrictions yang ketat. - Sebarang aktiviti yang memerlukan lesen atau
nasihat profesional perlu diserahkan kepada pihak yang diberi kuasa.
Jangan anggap platform generik boleh menjalankan aktiviti terkawal tanpa
kelulusan.

------------------------------------------------------------------------

## 6. Industry Blueprint Engine

### 6.1 Tujuan

Industry Blueprint Engine membina konfigurasi permulaan untuk sesuatu
perniagaan berdasarkan: - Industri utama. - Subindustri. - Model
operasi. - Saiz perniagaan. - Bilangan cawangan. - Produk, servis atau
kedua-duanya. - Keperluan pekerja, stok, booking, projek atau
pengeluaran. - Negara, mata wang, bahasa dan tax configuration.

Ia mengesyorkan modul dan tetapan; ia tidak boleh mengaktifkan integrasi
berbayar atau melakukan tindakan luaran tanpa kebenaran.

### 6.2 Contoh blueprint

**Kafe/restoran** - Teras: POS, Inventory, Finance, Invoicing. - Khusus:
menu/resipi, kitchen orders, table service atau takeaway. - Pilihan:
Loyalty, Marketing, HRMS, Procurement, COGS. - KPI: sales, average order
value, food cost, wastage, stock variance.

**Salon/barber** - Teras: Booking, POS atau Invoicing, Finance. -
Khusus: stylist/barber schedule, service duration, commission. -
Pilihan: Loyalty, Marketing, HRMS, Inventory. - KPI: occupancy, repeat
booking, revenue per staff, no-show rate.

**Freelancer/agensi** - Teras: Projects, Invoicing, Finance, Sign &
Docs. - Khusus: billable time, retainer dan deliverable. - Pilihan:
Site, Link, Marketing, Brain Hub. - KPI: utilization, project margin,
overdue invoices.

**Kilang** - Teras: Inventory, Procurement, Finance, Assets. - Khusus:
BOM, Work Orders, Quality & Compliance, COGS. - Pilihan: HRMS,
Logistics, Analytics. - KPI: yield, scrap, downtime, material variance,
unit cost.

**Kontraktor** - Teras: Projects, Invoicing, Finance, Procurement. -
Khusus: BOQ, variation order, progress claim, site diary. - Pilihan:
Assets, HRMS, Sign & Docs, Quality. - KPI: project margin,
cost-to-complete, claim aging, milestone delay.

**Pusat tuisyen** - Teras: Invoicing, Finance, HRMS. - Khusus:
Education, student enrolment, class scheduling, attendance. - Pilihan:
Booking, Marketing, Omnichannel, Site. - KPI: enrolment, attendance, fee
collection, retention.

**Hotel/homestay** - Teras: Finance, Invoicing, Omnichannel. - Khusus:
Hospitality, room availability, check-in/out, housekeeping. - Pilihan:
Site, Marketing, Booking, Assets. - KPI: occupancy, average daily rate,
cancellation rate, revenue per available room jika data mencukupi.

### 6.3 Blueprint configuration schema

Setiap blueprint patut mentakrifkan: - `industry_id` -
`subindustry_id` - `recommended_modules` - `required_modules` -
`optional_modules` - `default_roles` - `default_workflows` -
`custom_fields` - `terminology_map` - `dashboard_widgets` -
`report_templates` - `integration_recommendations` -
`localization_profile` - `version` - `acceptance_tests`

Blueprint mesti mempunyai versi dan boleh dikemas kini tanpa mengubah
data pelanggan secara tidak sengaja.

------------------------------------------------------------------------

## 7. Model Peranan dan Pengguna

Role boleh dikonfigurasi mengikut tenant. Cadangan asas:

-   **Platform Owner / Admin HQ:** mengurus platform, katalog modul,
    pelan, tenant, penggunaan dan konfigurasi global.
-   **Business Owner:** pemilik organisasi; memilih modul dan mengurus
    langganan serta akses.
-   **Organization Admin:** konfigurasi operasi harian.
-   **Manager:** akses operasi berdasarkan modul/cawangan.
-   **Finance Officer:** fungsi kewangan yang dibenarkan.
-   **Staff/Operator:** tugas operasi tertentu.
-   **External Customer:** akses portal pelanggan yang dibenarkan.
-   **Supplier/Vendor:** akses pesanan, invois atau fulfilment tertentu.
-   **Affiliate/Partner:** akses link, referral dan komisen sendiri.
-   **AI Agent:** identiti servis dengan permission minimum, bukan akaun
    manusia dengan akses menyeluruh.

Gunakan RBAC bersama skop data seperti tenant, branch, department,
assigned record dan module entitlement. Role sahaja tidak mencukupi
untuk semua kawalan akses.

------------------------------------------------------------------------

## 8. Model Komersial SaaS

AI BIJAN boleh menyokong: - Pelan asas platform. - Modul individu
sebagai add-on. - Pakej industri. - Had pengguna, cawangan, transaksi,
storage dan API. - Trial dan pelan berbayar. - White-label/enterprise
jika disediakan. - Token AI prabayar dan usage-based billing. - Caj
integrasi atau penggunaan tambahan yang dinyatakan dengan jelas.

### Prinsip subscription

-   Paparkan harga, had dan status langganan secara telus.
-   Entitlement ditentukan di backend.
-   Apabila langganan tamat, ikut polisi akses yang jelas; jangan padam
    data pelanggan secara automatik.
-   Simpan sejarah invois, transaksi, refund dan perubahan pelan.
-   Refund dan payment callbacks mesti disahkan secara server-side.
-   Sediakan kawalan anti-duplicate untuk webhook dan pembayaran.

------------------------------------------------------------------------

## 9. B-Brain Hub: AI Agents, Model dan Token

### 9.1 Model AI dibekalkan Admin HQ

-   Admin HQ mengurus provider/model yang diluluskan.
-   Pelanggan hanya boleh memilih model yang tersedia dalam katalog.
-   API key provider, secret, internal provider ID dan credentials mesti
    disimpan di server dan tidak dihantar ke browser/app pelanggan.
-   Gateway server-side memanggil provider yang dibenarkan.
-   Log tidak boleh mendedahkan API key, token rahsia atau kandungan
    sensitif tanpa keperluan.

### 9.2 Token wallet

-   Pelanggan membeli jumlah token/kredit terlebih dahulu.
-   Selepas pembelian, pelanggan memilih model daripada katalog yang
    dibenarkan.
-   Setiap AI agent boleh mempunyai model pilihan sendiri.
-   Semua agent dalam organisasi menggunakan wallet yang sama, dengan
    usage records yang boleh ditapis mengikut agent/model.
-   Paparkan anggaran kos sebelum tugas apabila boleh.
-   Rekod usage, model, input/output metering, harga efektif, status dan
    correlation ID.
-   Sediakan had harian/bulanan, amaran baki rendah dan polisi
    kegagalan/refund.
-   Bezakan token provider daripada kredit jualan dalaman; kadar
    penukaran mesti dikonfigurasi dan didokumenkan.
-   Jika kuota dibeli sah selama 30 hari, sistem mesti merekod expiry,
    baki dan aturan penggunaan/luput dengan jelas sebelum checkout.

### 9.3 Memori agent

-   Setiap agent mempunyai memori khusus berdasarkan tujuan dan
    kebenaran.
-   Bezakan short-term task context, long-term agent memory,
    organization knowledge dan shared project memory.
-   Memori mesti mempunyai tenant ownership, ACL, retention, sumber,
    tarikh dan provenance.
-   Pengguna boleh melihat/mengurus memori yang dibenarkan.
-   Jangan berkongsi memori antara tenant.
-   Jangan menyimpan rahsia, API key atau data sensitif secara tidak
    perlu.
-   Sediakan kaedah untuk memadam atau melupakan memori mengikut polisi
    produk dan kewajipan retention.

### 9.4 Kerjasama multi-agent

-   Orchestrator menentukan agent yang sesuai untuk sesuatu tugasan.
-   Agent menghantar hasil berstruktur dan sumber/context yang
    diperlukan, bukan keseluruhan memori tanpa sebab.
-   Setiap handoff direkodkan.
-   Hadkan tool, model, kos, masa dan bilangan agent bagi setiap
    workflow.
-   Elakkan gelung agent dan perbelanjaan tidak terkawal.
-   Tindakan kritikal memerlukan approval manusia.
-   Pengguna boleh menetapkan model setiap agent jika model itu
    dibenarkan Admin HQ.

### 9.5 AI safety dan permission

-   Agent hanya boleh mengakses tool dan data yang diberikan.
-   Permission diperiksa semula pada setiap tindakan.
-   AI tidak boleh memintas role, tenant isolation atau subscription
    entitlement.
-   Operasi seperti pembayaran, refund, payroll, pemadaman, perubahan
    akaun dan perubahan kewangan perlu confirmation/approval yang
    sesuai.
-   Audit prompt/tool action secara berhati-hati tanpa mendedahkan
    secret.
-   Sediakan rate limits, budget limits, abuse detection dan kill
    switch.

------------------------------------------------------------------------

## 10. Integrasi Antara Modul

Contoh aliran integrasi yang betul:

### Aliran jualan retail

1.  B-POS merekod jualan.
2.  B-Inventory merekod stock movement.
3.  B-Loyalty menambah mata jika syarat dipenuhi.
4.  B-Invoicing menghasilkan dokumen yang diperlukan.
5.  B-Finance menerima journal entries yang berkaitan.
6.  B-Analytics mengemas kini metrik daripada sumber yang sah.
7.  B-Brain Hub boleh merumus prestasi jika pengguna mempunyai
    permission.

### Aliran booking servis

1.  B-Booking menyemak availability.
2.  B-HRMS menyediakan jadual staf jika disambungkan.
3.  B-Invoicing merekod deposit/invois.
4.  B-Omnichannel menghantar notifikasi melalui channel yang
    disambungkan.
5.  B-Loyalty mengurus ganjaran selepas transaksi yang sah.
6.  B-Analytics melaporkan booking, cancellation dan revenue.

### Aliran pembelian/pengeluaran

1.  B-Procurement membuat PO.
2.  B-Inventory merekod penerimaan bahan.
3.  B-BOM menghasilkan work order.
4.  B-Inventory merekod bahan digunakan dan produk siap.
5.  B-COGS mengira kos berdasarkan rekod yang tersedia.
6.  B-Finance merekod kesan perakaunan yang betul.
7.  B-Quality & Compliance merekod pemeriksaan jika digunakan.

Semua aliran mesti mempunyai idempotency, retry, audit dan cara
membetulkan transaksi yang gagal.

------------------------------------------------------------------------

## 11. Dashboard dan UX

### Dashboard mengikut peranan

Dashboard tidak boleh memaparkan semua menu kepada semua pengguna.
Paparkan: - Modul yang dilanggan dan dibenarkan. - Tindakan utama
mengikut role. - KPI yang relevan dengan industri. - Notifikasi,
approval dan tugasan tertunggak. - Status integrasi, penggunaan dan
subscription. - Quick actions yang permission-aware.

### UX utama

-   Responsive web app/PWA.
-   Navigasi modular yang boleh disesuaikan.
-   Search global dengan permission filtering.
-   Form dan istilah mengikut blueprint industri.
-   Empty states, loading states, error recovery dan confirmation.
-   Aksesibiliti papan kekunci dan kontras yang sesuai.
-   Locale, timezone, format nombor dan mata wang mengikut organisasi.
-   Jangan paparkan ciri sebagai aktif jika modul belum dibeli, belum
    dikonfigurasi atau integrasinya belum disambungkan.

------------------------------------------------------------------------

## 12. Data, Audit dan Keselamatan

Keperluan asas: - Tenant isolation pada semua endpoint dan background
jobs. - RBAC/permission checks di backend. - Encryption in transit dan
perlindungan data at rest. - Secret management dan rotation. - Audit log
untuk perubahan sensitif. - Rate limiting dan abuse prevention. -
Validasi input, output encoding dan CSRF/session protection mengikut
stack. - Backup dan restore yang diuji. - Monitoring, alerting dan error
tracking. - Data retention dan deletion policies. - Export yang
permission-aware. - Webhook signature verification. - Idempotency untuk
pembayaran dan event. - Security tests termasuk IDOR, privilege
escalation dan cross-tenant access. - Data perubatan, kewangan, pekerja
dan kanak-kanak memerlukan kawalan tambahan jika modul berkaitan
digunakan.

Pematuhan kepada undang-undang Malaysia dan peraturan industri perlu
disahkan secara khusus. Dokumen ini ialah blueprint produk, bukan
nasihat undang-undang, cukai atau perakaunan.

------------------------------------------------------------------------

## 13. Reporting dan KPI mengikut industri

  -----------------------------------------------------------------------
  Industri                            Contoh KPI
  ----------------------------------- -----------------------------------
  Retail                              Sales, gross margin, stock
                                      turnover, shrinkage, basket size

  F&B                                 Sales per outlet, food cost,
                                      wastage, average order value

  Salon/Booking                       Occupancy, repeat bookings, no-show
                                      rate, revenue per staff

  Manufacturing                       Yield, scrap, downtime, material
                                      variance, unit cost

  Construction                        Project margin, claim aging,
                                      cost-to-complete, delay

  Education                           Enrolment, attendance, fee
                                      collection, retention

  Hospitality                         Occupancy, cancellation, average
                                      daily rate

  Healthcare                          Appointment utilization dan
                                      operational metrics yang
                                      dibenarkan; data klinikal dikawal
                                      ketat

  Logistics/Fleet                     On-time delivery, cost per trip,
                                      utilization, fuel variance

  Agriculture                         Yield, loss, input cost, batch
                                      traceability

  E-commerce                          Conversion, order value, return
                                      rate, fulfilment time

  Agency/Freelancer                   Utilization, billable hours,
                                      project margin, overdue invoice

  Property                            Occupancy, rent arrears,
                                      maintenance response time

  Automotive                          Job completion time, parts margin,
                                      repeat service rate

  Events/Rental                       Booking utilization, event margin,
                                      equipment utilization
  -----------------------------------------------------------------------

Definisi KPI mesti konsisten, didokumenkan dan mempunyai sumber data
yang jelas.

------------------------------------------------------------------------

## 14. Pelan Pembangunan Berfasa

### Fasa 0 --- Product Discovery

-   Sahkan sasaran pasaran awal dan MVP yang hendak dijual.
-   Sediakan katalog modul, domain boundaries dan dependency map.
-   Sediakan role/permission matrix.
-   Tentukan pricing, trial, token billing dan entitlement.
-   Tulis acceptance criteria setiap modul.

### Fasa 1 --- Shared Core

-   Authentication, tenant, organization dan membership.
-   RBAC, subscription, entitlement dan usage metering.
-   Audit log, notifications, files dan platform administration.
-   Test tenant isolation dan permission.

### Fasa 2 --- MVP Perniagaan Teras

Pilih subset berdasarkan pelanggan pertama. Cadangan umum: -
B-Invoicing - B-Finance (scope asas yang boleh diaudit) - B-Inventory -
B-POS atau B-Booking, bergantung pada pasaran awal - B-Analytics asas

Jangan bina semua industri serentak sebelum satu aliran kerja lengkap
dan stabil.

### Fasa 3 --- Customer & Growth

-   B-Loyalty
-   B-Marketing
-   B-Site
-   B-Link
-   B-Omnichannel dan B-Helpdesk mengikut keperluan.

### Fasa 4 --- Team & Operations

-   B-HRMS
-   B-Projects
-   B-Virtual Office
-   B-Sign & Docs
-   B-Assets.

### Fasa 5 --- Commerce & Supply Chain

-   B-Procurement
-   B-Logistics
-   B-COGS
-   B-BOM & Manufacturing
-   B-Marketplace
-   B-Affiliate.

### Fasa 6 --- Industry Modules

Pilih industri mengikut permintaan sebenar: - Hospitality - Education -
Automotive/Fleet - Construction/Property - Event & Rental -
Agriculture - Clinic & Care - Quality & Compliance.

### Fasa 7 --- B-Brain Hub

-   AI Gateway dan katalog model.
-   Token wallet, usage metering dan billing.
-   Agent builder dan per-agent model choice.
-   Memory boundaries dan multi-agent orchestration.
-   Tool permissions, approvals dan AI audit.
-   Ujian kos, keselamatan, kebocoran data dan reliability.

AI boleh dibangunkan lebih awal untuk use case yang kecil, tetapi jangan
benarkan agent mengambil tindakan production tanpa permission dan
kawalan yang diperlukan.

------------------------------------------------------------------------

## 15. Definition of Done untuk Setiap Modul

Satu modul hanya dianggap siap apabila:

1.  Skop dan non-goals didokumenkan.
2.  Model data dan migration disemak.
3.  Permission matrix dilaksanakan di backend.
4.  Semua operasi terikat kepada tenant yang betul.
5.  UI mempunyai loading, empty, success dan error states.
6.  Validasi dan business rules diuji.
7.  Audit untuk tindakan sensitif tersedia.
8.  API/event contract dan error handling didokumenkan.
9.  Integrasi tidak menghasilkan transaksi pendua.
10. Unit/integration/security tests lulus.
11. Ujian role dan cross-tenant access lulus.
12. Laporan, export dan KPI mempunyai definisi yang tepat.
13. Dokumentasi pengguna/admin disediakan.
14. Monitoring, backup dan recovery dipertimbangkan.
15. Acceptance tests industri lulus sebelum modul dipasarkan untuk
    industri itu.

------------------------------------------------------------------------

## 16. Perkara yang Tidak Boleh Dilakukan

-   Jangan membina semua fungsi dalam satu controller atau satu domain
    yang bercampur.
-   Jangan gunakan UI hiding sebagai pengganti backend authorization.
-   Jangan biarkan tenant membaca data tenant lain.
-   Jangan dedahkan provider API keys kepada pelanggan atau browser.
-   Jangan benarkan AI agent menggunakan tool tanpa permission.
-   Jangan kira token atau komisen tanpa usage/transaction ledger.
-   Jangan buat pembayaran, refund, payroll atau perubahan kewangan
    sensitif tanpa pengesahan yang sesuai.
-   Jangan menganggap semua industri mempunyai aliran kerja sama.
-   Jangan menganggap integrasi pihak ketiga tersedia sebelum diuji.
-   Jangan mengiklankan modul industri sebagai lengkap sebelum
    acceptance tests khusus industri diluluskan.
-   Jangan memadam data secara automatik hanya kerana subscription
    tamat.
-   Jangan hardcode peraturan cukai, payroll atau pematuhan yang boleh
    berubah.

------------------------------------------------------------------------

## 17. Ringkasan Keputusan Produk

AI BIJAN dibina dengan strategi berikut:

1.  **Shared Core** yang selamat dan multi-tenant.
2.  **Business Modules** yang mempunyai fungsi dan domain berasingan.
3.  **Industry Modules** untuk keperluan khusus sektor.
4.  **Industry Blueprint Engine** untuk mengkonfigurasi pengalaman
    mengikut bisnes.
5.  **Module Entitlement** supaya pelanggan hanya menggunakan modul yang
    dibenarkan.
6.  **B-Brain Hub** sebagai lapisan AI dengan model dibekalkan Admin HQ,
    token wallet, memori agent dan kolaborasi terkawal.
7.  **Integration Contracts** supaya modul berkongsi data tanpa
    pertindihan tanggungjawab.
8.  **Phased delivery** supaya setiap modul boleh diuji, dijual dan
    disenggara sebelum peluasan.

### Keputusan akhir

AI BIJAN tidak perlu membina satu produk berasingan untuk setiap jenis
perniagaan. Ia perlu menyediakan platform bersama dengan modul khusus
yang boleh digabungkan. Dengan pendekatan ini, AI BIJAN boleh berkembang
untuk menyokong retail, F&B, servis, profesional, pembuatan, pembinaan,
hartanah, kesihatan, pendidikan, logistik, pertanian, e-commerce,
automotif, acara dan industri lain dari semasa ke semasa.

**Prinsip produk:** *One platform. Specialized business systems.
Configured for each industry. Powered by AI.*
