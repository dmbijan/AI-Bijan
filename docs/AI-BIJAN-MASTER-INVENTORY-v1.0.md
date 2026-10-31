# AI BIJAN — OFFICIAL MASTER BUSINESS SYSTEMS INVENTORY
## Unified Business Operating System | Registry & Integration Architecture

**Versi:** 1.0 — Consolidated Baseline
**Tarikh:** 31 Oktober 2026
**Status:** Master inventory / proposed architecture; bukan pengesahan implementasi
**Platform:** AI BIJAN — The Business Growth Operating System
**Tagline:** Every Business Deserves a Boardroom.

> **Keputusan penggabungan:** Daftar kategori asal 210 sistem dikekalkan sebagai *Legacy Register Reference* dengan julat ID dan kategori asal. Sistem yang dinamakan secara jelas dalam perbualan ini dan sistem tambahan yang diperlukan dimasukkan dalam *Canonical Registry* menggunakan ID domain baharu. Oleh sebab nama individu asal 1–210 tidak dapat dipulihkan sepenuhnya daripada daftar yang tersedia, tiada nama asal direka atau dipadankan secara andaian. `MES #117` dikekalkan sebagai satu-satunya padanan legacy yang disahkan secara khusus.

## 1. Tujuan dan prinsip

Inventori ini menjadi sumber rujukan rasmi untuk skop sistem AI BIJAN: fungsi, submodul, sempadan domain, data ownership, integrasi, OAM, AI agents, migration dan status pembangunan.

Prinsip reka bentuk:
- Satu platform dan identiti; bukan 100+ aplikasi berasingan.
- Setiap domain mempunyai bounded context, kontrak API/event dan system of record.
- Sistem yang bertindih digabung sebagai capability atau dipisahkan berdasarkan tanggungjawab sebenar.
- Subscription entitlement menentukan modul tersedia; OAM menentukan siapa boleh melakukan apa.
- AI agents menggunakan service identity, scoped tools, quota, approval dan audit.
- Integrasi dua hala mesti mempunyai pemilik data dan conflict policy.
- Status `VERIFIED READY` hanya selepas ujian penerimaan, keselamatan dan integrasi.

## 2. Status pembangunan

| Status | Takrif |
|---|---|
| PLANNED | Dirancang, belum dibangunkan |
| IN PROGRESS | Sedang dibangunkan |
| IMPLEMENTED | Kod/fungsi wujud tetapi belum disahkan sepenuhnya |
| VERIFIED READY | Lulus acceptance, security dan integration tests |
| EXTERNAL INTEGRATION | Bergantung pada vendor/API/lesen luar |
| DEPRECATED | Diganti atau tidak lagi disyorkan |

**Status semasa inventori ini:** Semua item baharu dianggap `PLANNED` atau `TO BE VERIFIED` sehingga bukti pembangunan disemak. Dokumen blueprint bukan bukti produk sudah siap.

## 3. Legacy Register — 210 sistem asal

| Kategori asal | Legacy ID range | Count | Status nama individu |
|---|---:|---:|---|
| Business Management & Operations | 1–15 | 15 | Nama individu perlu daftar asal |
| Finance, Accounting & Fintech | 16–34 | 19 | Nama individu perlu daftar asal |
| Sales, Marketing & Customer Experience | 35–54 | 20 | Nama individu perlu daftar asal |
| HR & Workforce | 55–69 | 15 | Nama individu perlu daftar asal |
| Inventory, Warehouse & Supply Chain | 70–84 | 15 | Nama individu perlu daftar asal |
| Technology, Data, Integration & Security | 85–102 | 18 | Nama individu perlu daftar asal |
| Corporate Governance, Risk & Compliance | 103–116 | 14 | Nama individu perlu daftar asal |
| Manufacturing & Industrial Systems | 117–136 | 20 | MES #117 diketahui; nama lain perlu daftar asal |
| Healthcare & Medical Systems | 137–148 | 12 | Nama individu perlu daftar asal |
| Restaurant, Food & Hospitality Systems | 149–160 | 12 | Nama individu perlu daftar asal |
| Retail & E-Commerce Systems | 161–170 | 10 | Nama individu perlu daftar asal |
| Construction & Engineering Systems | 171–180 | 10 | Nama individu perlu daftar asal |
| Transport & Logistics Systems | 181–190 | 10 | Nama individu perlu daftar asal |
| Events, Booking & Services Systems | 191–204 | 14 | Nama individu perlu daftar asal |
| Education & Learning Systems | 205–210 | 6 | Nama individu perlu daftar asal |
| **JUMLAH** | **1–210** | **210** | **Legacy numbering preserved** |

### 3.1 Polisi legacy ID
- Legacy ID tidak dipadam, digunakan semula atau ditukar.
- Nama legacy hanya boleh diisi daripada dokumen daftar asal yang sah.
- Sistem canonical baharu mendapat ID `BOS`, `FIN`, `GRO`, `HR`, `SUP`, `TEC`, `GRC`, `MFG`, `HLT`, `HOS`, `RET`, `CON`, `LOG`, `EVT` atau `CORE`.
- Jadual crosswalk legacy-to-canonical perlu mempunyai `legacy_id`, `legacy_name`, `canonical_system_id`, `mapping_type`, `confidence`, `reviewer`, `decision_date`.
- `MES #117` dipelihara sebagai rujukan khusus: Manufacturing Execution System.

## 4. Canonical Master Registry

**Jumlah sistem/capability berdaftar dalam baseline canonical ini: 179.** Ini ialah registry gabungan yang dibina daripada senarai sistem yang diberikan dan peluasan domain yang relevan. Ia tidak mendakwa sebagai pemulihan nama 210 legacy secara automatik.

| ID | Kod | Nama sistem | Fungsi utama | Submodul utama | Sempadan sistem | Integrasi utama |
|---|---|---|---|---|---|---|
| BOS-001 | CRM | Customer Relationship Management | Pelanggan, akaun, interaksi dan peluang jualan. | Accounts; Contacts; Leads; Activities; Opportunities; Segments | Tidak menjadi ledger kewangan atau master inventory. | ERP; CPQ; Helpdesk; CDP; OMS; BI |
| BOS-002 | ERP | Enterprise Resource Planning | Penyelarasan transaksi dan sumber merentas fungsi syarikat. | General Ledger integration; Procurement; Inventory; Sales; Production; Projects | Bukan satu modul tanpa sempadan; ERP ialah payung integrasi domain. | Finance; HRMS; SCM; IMS; MES; BI |
| BOS-003 | BPM | Business Process Management | Pemodelan, pelaksanaan dan pemantauan proses perniagaan. | Process Designer; Workflow; SLA; Escalation; Process Analytics | Mengurus aliran proses, bukan pemilik rekod domain transaksi. | OAM; ERP; CRM; HRMS; Approval; Automation |
| BOS-004 | BMS | Business Management System | Paparan pengurusan menyeluruh dan konfigurasi operasi bisnes. | Business Profile; KPI; Goals; Policies; Management Dashboard | BMS ialah lapisan pengurusan; bukan pengganti ERP/CRM/HRMS. | ERP; BI; BPM; EPM; OAM |
| BOS-005 | OMS | Order Management System | Kitar hayat pesanan merentas saluran. | Order Capture; Validation; Allocation; Fulfilment; Returns; Order Status | Pemilik lifecycle order; bukan pemilik stok atau ledger pembayaran. | POS; E-commerce; IMS; WMS; Finance; Logistics |
| BOS-006 | POS | Point of Sale | Transaksi jualan di kaunter atau titik jualan. | Checkout; Discounts; Taxes; Cash Drawer; Shift; Refund; Receipt | Channel transaksi; stok/ledger kekal pada domain pemilik masing-masing. | OMS; IMS; Payment; Finance; Loyalty |
| BOS-007 | OSM | Operations Management System | Perancangan dan kawalan operasi harian. | Operations Board; Work Queue; SOP; Capacity; Exception Handling | Tidak menggantikan BPM atau task/project management. | BPM; WFM; PMS-PRJ; Inventory; BI |
| BOS-008 | PMS-PRJ | Project Management System | Skop, jadual, kos dan deliverable projek. | Projects; Tasks; Milestones; Gantt; Risks; Timesheets; Budget | Mengurus projek, bukan portfolio hartanah atau hotel. | Finance; HRMS; Procurement; DMS; BI |
| BOS-009 | WFM | Workforce Management | Penjadualan tenaga kerja mengikut permintaan operasi. | Rostering; Shift; Skills; Availability; Labour Forecast; Attendance interface | Mengurus kapasiti dan jadual; HRMS kekal rekod pekerja. | HRMS; T&A; Payroll; POS; OSM |
| BOS-010 | EPM | Enterprise Performance Management | Perancangan, sasaran dan prestasi organisasi. | Budgeting; Forecasting; Scorecards; Consolidation; Strategy Maps | Analisis prestasi dan perancangan; bukan transaksi GL. | Finance; BI; ERP; HRMS |
| BOS-011 | BPA | Business Process Automation | Automasi tugasan berasaskan trigger dan rules. | Triggers; Rules; Queues; Human Tasks; Retry; Monitoring | Bukan enjin AI reasoning atau pemilik rekod domain. | BPM; iPaaS; API; RPA; AI Agents |
| BOS-012 | Task & Work Management | Work Management System | Tugasan individu dan pasukan. | Tasks; Checklists; Assignment; Due Dates; Comments; Workload | Tidak mengurus projek kompleks sepenuhnya atau workflow transaksi. | PMS-PRJ; OSM; HRMS; Notifications |
| BOS-013 | Enterprise Content Portal | Corporate Portal | Portal dalaman untuk maklumat dan perkhidmatan pekerja. | Announcements; Knowledge; Forms; Links; Service Catalogue | Portal pengalaman pengguna; bukan repository dokumen utama. | DMS; HRMS; ITSM; OAM |
| BOS-014 | Business Continuity Management | BCM System | Kesinambungan operasi dan pemulihan gangguan. | BIA; Continuity Plans; Recovery Exercises; Crisis Contacts | Mengurus kesiapsiagaan; bukan backup engine. | Risk; ITSM; Security; Audit |
| BOS-015 | Strategy Execution Management | Strategy Management | Penterjemahan strategi kepada sasaran dan inisiatif. | Objectives; OKR; Initiatives; KPI; Reviews | Tidak menjadi sumber data KPI; membaca daripada domain lain. | EPM; BI; PMS-PRJ |
| FIN-001 | Accounting System | Accounting Management System | Perakaunan dan rekod jurnal kewangan. | Chart of Accounts; Journals; Trial Balance; Period Close; Statements | Subdomain accounting; FMS boleh menjadi payung UI bukan duplicate ledger. | AP; AR; GL; Tax; Billing; ERP |
| FIN-002 | FMS | Financial Management System | Kawalan kewangan, bajet, cash flow dan pelaporan. | Financial Dashboard; Budget; Cash Flow; Controls; Forecast | Payung pengurusan kewangan; ledger dan subledger kekal domain khusus. | Accounting; AP; AR; Treasury; EPM |
| FIN-003 | AP | Accounts Payable | Hutang pembekal dan pembayaran keluar. | Supplier Bills; Matching; Due Dates; Payment Proposal; Aging | Tidak mengurus procurement sourcing atau bank rails. | Procurement; Accounting; Payment; Treasury |
| FIN-004 | AR | Accounts Receivable | Hutang pelanggan dan kutipan. | Customer Invoices; Aging; Collections; Credit Notes; Dunning | Tidak mengesahkan settlement bank secara bersendirian. | Billing; CRM; Payment; Accounting |
| FIN-005 | FP&A | Financial Planning & Analysis | Perancangan, bajet dan analisis kewangan. | Budget; Forecast; Scenario; Variance; Management Reports | Bukan ledger transaksi atau tax filing engine. | FMS; EPM; BI; ERP |
| FIN-006 | Treasury Management | Treasury System | Kecairan, bank position dan cash forecasting. | Cash Position; Bank Accounts; Cash Forecast; Bank Reconciliation | Tidak menyimpan credentials bank tanpa vault dan tidak menggantikan bank. | Accounting; Payment; Banking APIs |
| FIN-007 | Billing System | Billing Management | Pengiraan caj dan penerbitan bil/invois. | Price Plans; Usage Rating; Invoice Generation; Credit Notes | Bil bukan bukti pembayaran diterima. | OMS; Subscription; AR; Payment |
| FIN-008 | Payment Management | Payment Orchestration | Inisiasi, status dan rekonsiliasi pembayaran. | Payment Intent; Provider Routing; Refund; Webhooks; Reconciliation | Tidak menyimpan dana atau menjadi bank/e-money issuer tanpa lesen. | POS; Billing; AR; Gateway; Treasury |
| FIN-009 | Expense Management | Expense System | Tuntutan dan kawalan perbelanjaan. | Claims; Receipts; Policy Checks; Approvals; Reimbursement | Bukan AP supplier invoice atau payroll. | HRMS; AP; Accounting; OAM |
| FIN-010 | Tax Management | Tax System | Pengiraan, pelaporan dan pematuhan cukai. | Tax Codes; SST/VAT; Withholding; Tax Returns; E-Invoice mapping | Peraturan cukai mesti dikonfigurasi mengikut bidang kuasa dan tarikh. | Accounting; Billing; ERP; E-invoice |
| FIN-011 | General Ledger | GL | Buku besar dan akaun kawalan. | Journal Posting; Period; Trial Balance; Consolidation | Sumber rasmi baki perakaunan; modul lain tidak menulis terus tanpa kontrak. | AP; AR; Assets; Payroll; POS |
| FIN-012 | Fixed Asset Accounting | Fixed Asset System | Aset tetap dan susut nilai. | Register; Depreciation; Disposal; Revaluation; Asset Ledger | Bezakan daftar aset operasi daripada nilai buku perakaunan. | EAM; Accounting; Procurement |
| FIN-013 | Revenue Management | Revenue Recognition | Pengiktirafan hasil mengikut kontrak dan polisi. | Contracts; Performance Obligations; Deferred Revenue; Schedules | Tidak sama dengan invoicing atau payment collection. | CRM; CPQ; Billing; GL |
| FIN-014 | Credit & Collections | Credit Management | Had kredit, risiko pelanggan dan kutipan. | Credit Limits; Dunning; Disputes; Promise-to-Pay | Keputusan kredit memerlukan polisi dan data yang sah. | AR; CRM; Risk; Payment |
| FIN-015 | Financial Consolidation | Consolidation System | Penyatuan kewangan kumpulan syarikat. | Intercompany; Eliminations; FX; Consolidated Statements | Tidak menggantikan GL setiap entiti. | GL; ERP; Treasury; EPM |
| FIN-016 | E-Invoice Management | E-Invoicing | Penyediaan, penghantaran dan status invois elektronik. | Document Mapping; Validation; Submission; Rejection; Status | Adapter pematuhan; keperluan Malaysia berubah mengikut peraturan rasmi. | Billing; Tax; AR; API |
| FIN-017 | Bank Reconciliation | Bank Reconciliation System | Padanan transaksi bank dengan rekod dalaman. | Statement Import; Matching Rules; Exceptions; Reconciliation | Tidak menginisiasi pembayaran atau menyimpan wang. | Treasury; Payment; GL |
| FIN-018 | Cash Management | Cash Management System | Tunai di kaunter, petty cash dan cash forecast ringkas. | Cashbook; Float; Cash Count; Variance | Tidak menggantikan treasury korporat. | POS; Accounting; Treasury |
| GRO-001 | SFA | Sales Force Automation | Aktiviti dan produktiviti pasukan jualan. | Territories; Visits; Pipeline; Forecast; Sales Tasks | CRM mengurus rekod pelanggan; SFA mengoptimumkan aktiviti jualan. | CRM; CPQ; Maps; BI |
| GRO-002 | MAP | Marketing Automation Platform | Automasi kempen dan nurture. | Campaigns; Journeys; Segments; Triggers; Attribution | Tidak menggantikan CDP atau platform penerbitan sosial sepenuhnya. | CRM; CDP; Email; WhatsApp; Analytics |
| GRO-003 | CDP | Customer Data Platform | Penyatuan profil pelanggan daripada pelbagai sumber. | Identity Resolution; Events; Segments; Consent; Profile | Bukan CRM; CDP menyatukan data, CRM mengurus hubungan dan aktiviti. | CRM; POS; E-commerce; Support; BI |
| GRO-004 | DMP | Data Management Platform | Pengurusan audience/data pemasaran bersegmen. | Audience Taxonomy; Activation; Data Partnerships | Tidak menjadi master data pelanggan dalaman atau consent ledger. | CDP; MAP; Ad Platforms |
| GRO-005 | CMS | Content Management System | Pengurusan halaman dan kandungan digital. | Pages; Posts; Templates; Versioning; Publishing | Tidak sama dengan DAM yang mengurus aset media asal. | SEO; DAM; Website; Omnichannel |
| GRO-006 | DAM | Digital Asset Management | Penyimpanan, metadata dan lifecycle aset digital. | Asset Library; Metadata; Rights; Renditions; Approval | Bukan CMS; CMS menggunakan aset daripada DAM. | CMS; Creative Studio; Social Publishing |
| GRO-007 | Loyalty Management | Loyalty Management System | Mata ganjaran, tier dan redemption pelanggan. | Points; Tiers; Rewards; Vouchers; Liability | Jangan kelirukan dengan Learning Management System. | POS; CRM; OMS; Payment |
| GRO-008 | PRM | Partner Relationship Management | Pengurusan rakan niaga dan channel partner. | Partner Portal; Deal Registration; MDF; Enablement | Bukan affiliate tracking pengguna; fokus partner B2B. | CRM; CPQ; Affiliate |
| GRO-009 | Affiliate Management | Affiliate System | Pautan rujukan, atribusi dan komisen affiliate. | Tracking; Attribution; Commission; Payout Approval; Fraud | Komisen dikira di sini; payout disahkan Finance/Payment. | CRM; OMS; Payment; BI |
| GRO-010 | CPQ | Configure Price Quote | Konfigurasi produk, harga dan sebut harga. | Product Rules; Pricing; Discounts; Quote; Approval | Tidak menjadi kontrak atau order sehingga diterima dan ditukar. | CRM; ERP; Billing; CLM; OMS |
| GRO-011 | Helpdesk | Customer Support System | Tiket, SLA dan penyelesaian isu pelanggan. | Tickets; Queues; SLA; Knowledge; Escalation | Bukan contact centre telephony atau CRM account master. | CRM; Contact Centre; Knowledge |
| GRO-012 | Contact Centre | Contact Centre System | Pengendalian panggilan dan interaksi omnichannel. | Voice; IVR; Routing; Recording; Agent Desktop | Bukan system of record untuk customer profile. | CRM; Helpdesk; WhatsApp; Analytics |
| GRO-013 | Customer Success Management | CSM | Onboarding dan kesihatan pelanggan selepas jualan. | Health Score; Adoption; Renewals; QBR; Churn Risk | Tidak menggantikan CRM sales pipeline. | CRM; Subscription; Support; BI |
| GRO-014 | Social Media Management | SMM | Perancangan, penerbitan dan pemantauan media sosial. | Calendar; Publishing; Inbox; Comments; Analytics | API platform menentukan fungsi yang boleh dilaksanakan. | CMS; DAM; MAP; CRM |
| GRO-015 | SEO & Metadata Management | SEO System | Metadata, indexability dan prestasi carian organik. | Meta; Schema; Sitemap; Redirects; Search Console | Tidak menjamin ranking dan tidak mengurus iklan berbayar. | CMS; Website; Analytics |
| GRO-016 | Reputation Management | Online Reputation System | Ulasan, maklum balas dan reputasi jenama. | Review Inbox; Response Approval; Sentiment; Alerts | Tidak boleh memalsukan review atau memanipulasi platform. | CRM; Helpdesk; Social |
| GRO-017 | Proposal Management | Proposal System | Dokumen cadangan komersial. | Templates; Pricing; Versioning; Approval; E-sign handoff | CPQ mengira konfigurasi/harga; proposal membentangkan tawaran. | CRM; CPQ; CLM; DMS |
| GRO-018 | Customer Experience Management | CXM | Pengukuran pengalaman pelanggan merentas touchpoint. | Surveys; Journey; Feedback; NPS; Experience Analytics | Bukan pengganti CRM/CDP; mengukur pengalaman. | CDP; CRM; Support; BI |
| GRO-019 | Event Marketing Automation | Event Marketing | Promosi dan komunikasi acara. | Registration Campaigns; Reminders; Segmentation; Attribution | Pengurusan acara sebenar kekal pada Event Management. | Event; CRM; MAP; Payment |
| GRO-020 | Conversational Commerce | Conversational Sales | Jualan melalui WhatsApp/chat dan saluran perbualan. | Catalog; Cart; Agent Handoff; Payment Link; Order Capture | Mesej bukan rekod order sehingga disahkan OMS. | WhatsApp; CRM; OMS; Payment |
| HR-001 | HRMS | Human Resource Management System | Pengurusan proses sumber manusia. | Employee Records; Leave; Benefits; HR Cases; Org Data | Payung HR; submodul khusus boleh berkongsi master employee. | Payroll; ATS; T&A; OAM; Finance |
| HR-002 | HRIS | Human Resource Information System | Rekod induk maklumat pekerja. | Employee Profile; Employment; Position; Documents | HRIS ialah data pekerja; HRMS boleh menjadi payung fungsi HR. | Payroll; WFM; LMS-LRN; Access |
| HR-003 | HCM | Human Capital Management | Strategi tenaga kerja dan modal insan. | Talent; Workforce Planning; Succession; Compensation | Payung strategik, bukan semestinya sistem transaksi berasingan. | HRMS; ATS; Performance; EPM |
| HR-004 | ATS | Applicant Tracking System | Pengambilan calon pekerja. | Vacancies; Applications; Screening; Interviews; Offers | Lifecycle calon sehingga hire; selepas hire HRIS mengambil alih. | HRMS; Calendar; Email |
| HR-005 | LMS-LRN | Learning Management System | Latihan, kursus dan kompetensi pekerja/pelajar. | Courses; Enrolment; Assessments; Certificates; Learning Paths | Bezakan kod daripada Loyalty Management System. | HRMS; Skills; Compliance |
| HR-006 | Payroll | Payroll Management | Pengiraan gaji dan potongan. | Pay Runs; Allowances; Deductions; Statutory; Payslips | Payroll mengira; GL merekod jurnal; bank membayar. | HRMS; T&A; Tax; GL; Payment |
| HR-007 | T&A | Time & Attendance | Masa kerja dan kehadiran. | Clock In/Out; Timesheets; Leave interface; Exceptions | Tidak menentukan polisi HR keseluruhan atau mengira payroll sepenuhnya. | WFM; Payroll; HRMS |
| HR-008 | ESS | Employee Self-Service | Portal urusan kendiri pekerja. | Profile Updates; Leave Requests; Claims; Payslips | UI/portal pekerja; rekod rasmi berada dalam HRMS/Payroll. | HRMS; Expense; Payroll |
| HR-009 | Performance Management | Performance System | Penilaian dan sasaran pekerja. | Goals; Reviews; 360 Feedback; PIP; Calibration | Bukan EPM korporat walaupun berkongsi KPI. | HRMS; HCM; LMS-LRN |
| HR-010 | Compensation Management | Compensation System | Gaji asas, bonus dan ganjaran. | Salary Bands; Bonus; Incentives; Equity | Tidak memproses payroll payment. | HRMS; Payroll; Finance |
| HR-011 | Skills & Competency Management | Skills Management | Peta kemahiran dan jurang kompetensi. | Skill Taxonomy; Assessments; Certifications; Matrix | Bukan LMS; LMS menyampaikan latihan. | HRMS; LMS-LRN; WFM |
| HR-012 | Employee Engagement | Engagement System | Penglibatan, survey dan maklum balas pekerja. | Pulse Surveys; Recognition; Sentiment; Action Plans | Data survey sensitif; akses agregat dan privasi diperlukan. | HRMS; BI; Communications |
| SUP-001 | IMS | Inventory Management System | Kuantiti, kos dan pergerakan stok. | Item Master; Stock Ledger; Transfers; Counts; Lots; Serials | Sumber kebenaran stok jika ditetapkan; WMS mengurus operasi gudang. | OMS; POS; WMS; Procurement; Finance |
| SUP-002 | WMS | Warehouse Management System | Operasi gudang dan lokasi simpanan. | Receiving; Putaway; Picking; Packing; Cycle Count; Wave | WMS mengurus execution gudang; IMS/ledger menentukan stok kewangan. | IMS; OMS; TMS; ERP |
| SUP-003 | SCM | Supply Chain Management | Perancangan dan penyelarasan rantaian bekalan. | Demand; Supply; Planning; Supplier; Network Visibility | Payung rantaian bekalan, bukan satu ledger transaksi. | MRP; SRM; Procurement; WMS; TMS |
| SUP-004 | MRP | Material Requirements Planning | Keperluan bahan untuk pengeluaran. | BOM Explosion; Netting; Planned Orders; Shortage | Merancang bahan; tidak menjalankan shop-floor execution. | MES; IMS; Procurement; ERP |
| SUP-005 | SRM | Supplier Relationship Management | Profil, prestasi dan hubungan pembekal. | Supplier Master; Scorecards; RFQ; Contracts; Risk | Hubungan/pemilihan pembekal; procurement melaksanakan pembelian. | Procurement; AP; SCM; CLM |
| SUP-006 | TMS-LOG | Transportation Management System | Perancangan dan pelaksanaan pengangkutan barang. | Load Planning; Carrier; Routing; Freight; Proof of Delivery | TMS logistik bukan pengurusan fleet aset dalaman. | WMS; OMS; Fleet; ERP |
| SUP-007 | YMS | Yard Management System | Pergerakan lori dan trailer di kawasan yard. | Gate; Dock Scheduling; Yard Map; Check-in/out | Mengurus yard; bukan gudang atau pengangkutan keseluruhan. | WMS; TMS-LOG; RFID |
| SUP-008 | Procurement | Procurement Management | Permintaan, tender, PO dan pembelian. | Requisition; RFQ; Bid; PO; Receiving; Supplier Award | Procurement tidak memiliki AP invoice payment ledger. | SRM; AP; IMS; CLM |
| SUP-009 | Fleet Management | Fleet System | Kenderaan, pemandu dan penyelenggaraan fleet. | Vehicles; Drivers; Fuel; Maintenance; Telematics | Fleet ialah aset operasi; TMS merancang penghantaran. | TMS-LOG; EAM; HRMS |
| SUP-010 | Asset Management | Asset Management System | Daftar aset dan lifecycle aset organisasi. | Asset Register; Assignment; Warranty; Depreciation link | Bezakan aset fizikal operasi daripada fixed asset accounting. | EAM; Finance; Procurement |
| SUP-011 | Demand Planning | Demand Planning System | Ramalan permintaan dan replenishment. | Forecast; Seasonality; Promotions; Consensus Planning | Ramalan bukan pesanan pembelian atau stok sebenar. | OMS; POS; SCM; BI |
| SUP-012 | Supplier Portal | Supplier Collaboration Portal | Kolaborasi pembekal dengan syarikat. | PO Acknowledgement; ASN; Invoice Submission; Quality Docs | Portal akses; rekod PO/AP kekal dalam domain pemilik. | Procurement; SRM; AP; WMS |
| SUP-013 | Product Information Management | PIM | Maklumat produk merentas saluran. | Attributes; Variants; Taxonomy; Enrichment; Syndication | PIM kandungan produk; IMS kuantiti stok. | E-commerce; POS; CMS; IMS |
| TEC-001 | BI | Business Intelligence | Analisis dan visualisasi prestasi. | Semantic Model; Dashboards; Reports; Alerts; Drilldown | BI membaca data; bukan pemilik transaksi. | DWH; ERP; CRM; Finance; all systems |
| TEC-002 | DWH | Data Warehouse | Penyimpanan data analitik bersejarah. | Ingestion; Dimensional Models; ELT; Data Marts | Analitik, bukan operational database. | BI; ETL; Source Systems |
| TEC-003 | MDM | Master Data Management | Governance master data merentas domain. | Golden Records; Matching; Stewardship; Hierarchies | Tidak bermaksud semua domain menyerahkan ownership; domain source tetap berkuasa. | ERP; CRM; PIM; CDP |
| TEC-004 | iPaaS | Integration Platform as a Service | Orkestrasi sambungan antara aplikasi. | Connectors; Mapping; Routing; Monitoring; Retry | Platform integrasi; bukan pemilik data domain. | API; Webhooks; Migration Hub |
| TEC-005 | API Management | API Management System | Pengurusan lifecycle dan akses API. | Gateway; Keys; OAuth; Rate Limits; Developer Portal | Mengawal akses API; tidak menggantikan domain services. | All modules; iPaaS |
| TEC-006 | IAM | Identity & Access Management | Identiti, authentication dan authorization. | SSO; MFA; Sessions; Policies; Provisioning | OAM mengurus konteks organisasi/membership; IAM ialah capability identity/security. | OAM; HRMS; all apps |
| TEC-007 | SIEM | Security Information & Event Management | Pengumpulan dan korelasi log keselamatan. | Log Ingestion; Correlation; Detection; Incident Alerts | Bukan audit perniagaan atau endpoint protection. | IAM; SIEM agents; Audit; ITSM |
| TEC-008 | ITSM | IT Service Management | Service desk dan lifecycle perkhidmatan IT. | Incidents; Problems; Changes; Requests; CMDB | IT service workflows; bukan customer helpdesk. | SIEM; CMDB; Asset; BPM |
| TEC-009 | RPA | Robotic Process Automation | Automasi UI bagi sistem tanpa API memadai. | Bots; Schedules; Credentials; Run Logs; Exception Queue | Pilihan terakhir berbanding API; tidak boleh memintas kawalan akses. | BPM; iPaaS; ITSM |
| TEC-010 | DMS | Document Management System | Dokumen, versi, metadata dan retention. | Repository; Versioning; OCR; Search; Retention; Access | Bukan DAM media asset library atau CMS publishing. | CLM; HRMS; Finance; QMS |
| TEC-011 | ETL/ELT | Data Integration & Transformation | Pemindahan dan transformasi data analitik/operasi. | Pipelines; Schedules; Quality Checks; Lineage | Bukan migration project governance sepenuhnya. | DWH; MDM; Migration Hub |
| TEC-012 | Data Governance | Data Governance System | Polisi, pemilik, klasifikasi dan lineage data. | Catalog; Glossary; Owners; Classification; Lineage | Menetapkan governance; tidak mengambil alih database execution. | MDM; DWH; Security |
| TEC-013 | Observability | Application Observability | Metrics, logs, traces dan health aplikasi. | APM; Distributed Tracing; Alerts; SLO | Operasi teknikal; bukan BI perniagaan. | All services; ITSM |
| TEC-014 | Secrets Management | Secrets Vault | Penyimpanan credential dan secrets. | Vault; Rotation; Access Policy; Audit | Tidak menyimpan secret dalam plaintext atau tenant-shared scope. | Connectors; API; CI/CD |
| TEC-015 | Backup & Disaster Recovery | Backup/DR System | Backup, restore dan pemulihan bencana. | Backup Jobs; Snapshots; Restore Tests; DR Plans | Bukan migration data atau version control. | Infrastructure; BCM; Security |
| TEC-016 | Configuration Management | CMDB & Configuration | Inventori konfigurasi aset IT dan dependency. | CI Registry; Relationships; Change History | CMDB IT tidak sama dengan product/asset inventory komersial. | ITSM; Observability; Security |
| TEC-017 | DevSecOps | DevSecOps Platform | Pipelines pembangunan dan keselamatan kod. | CI/CD; SAST; DAST; Dependency Scan; Release Gates | Toolchain engineering dalaman, bukan tenant business app. | Git; Security; Observability |
| TEC-018 | B-Migration Hub | Data Migration & Integration Hub | Migrasi, mapping, validation, sync dan reconciliation. | Connectors; Extraction; Mapping; Trial; Batch; CDC; Conflict; Audit | Tidak menjamin semua vendor boleh diekstrak; bergantung API/lesen/akses. | ERP; CRM; HRMS; Accounting; Databases |
| GRC-001 | GRC | Governance Risk & Compliance | Tadbir urus, risiko dan pematuhan. | Controls; Risk Register; Obligations; Evidence; Reviews | Payung governance; modul khusus menyimpan transaksi sumber. | ERM; Audit; Policy; QMS |
| GRC-002 | QMS | Quality Management System | Kawalan kualiti produk/proses. | Nonconformance; CAPA; Inspection; Documents; Supplier Quality | Quality domain; tidak menggantikan MES execution. | MES; Procurement; DMS; Audit |
| GRC-003 | EHS | Environment Health & Safety | Kesihatan, keselamatan dan persekitaran kerja. | Incidents; Risk Assessments; Permits; Training; Inspections | Operasi EHS; EMS mengurus aspek alam sekitar lebih khusus. | HRMS; QMS; CMMS |
| GRC-004 | EMS | Environmental Management System | Pengurusan impak alam sekitar. | Aspects; Emissions; Waste; Compliance; Objectives | Bukan EHS penuh atau energy grid control. | EHS; Facilities; BI |
| GRC-005 | CLM | Contract Lifecycle Management | Kitar hayat kontrak. | Draft; Review; Clause Library; Approval; Signature; Renewal | Kontrak bukan invois atau rekod order. | CRM; Procurement; DMS; Finance |
| GRC-006 | ERM | Enterprise Risk Management | Risiko perusahaan dan mitigasi. | Risk Register; Appetite; Controls; KRIs; Treatment | Risiko merentas organisasi; GRC menyediakan umbrella controls. | GRC; BI; Audit |
| GRC-007 | Audit Management | Audit System | Perancangan dan pelaksanaan audit. | Audit Plan; Workpapers; Findings; Actions; Evidence | Audit trail sistem bukan pengganti audit engagement. | GRC; DMS; QMS |
| GRC-008 | Policy Management | Policy System | Polisi, acknowledgement dan versioning. | Policy Library; Approvals; Attestation; Review Cycle | Tidak menggantikan workflow enforcement dalam aplikasi. | DMS; HRMS; GRC |
| GRC-009 | Privacy Management | Privacy & Data Protection | Pengurusan hak subjek data dan privacy controls. | Data Inventory; Consent; DSAR; Retention; Breach Register | Keperluan undang-undang perlu disemak pakar; bukan jaminan compliance automatik. | IAM; CDP; DMS; Security |
| GRC-010 | Regulatory Compliance | Compliance Management | Kewajipan kawal selia mengikut industri/negara. | Obligations; Evidence; Calendar; Regulatory Change | Rules perlu dikemas kini dan disahkan; tidak auto-certify. | GRC; Tax; Healthcare; Finance |
| GRC-011 | Fraud & AML Monitoring | Financial Crime Monitoring | Pengesanan transaksi mencurigakan mengikut skop/lesen. | Rules; Alerts; Case Management; Screening | Tidak menjadikan platform entiti berlesen; peranan ditentukan undang-undang. | Payment; Finance; KYC provider |
| MFG-001 | MES | Manufacturing Execution System | Pelaksanaan dan pemantauan pengeluaran di shop floor. | Production Orders; Work Orders; Scheduling; WIP; OEE; Batch/Lot; Quality; Downtime | MES execution; tidak menggantikan PLC atau machine safety controls. | ERP; MRP; WMS; QMS; CMMS; PLM; SCADA |
| MFG-002 | CMMS | Computerized Maintenance Management System | Penyelenggaraan aset dan mesin. | Work Orders; PM Schedules; Spare Parts; Downtime; Technician | Penyelenggaraan; EAM mengurus lifecycle aset enterprise. | MES; EAM; IMS; IoT |
| MFG-003 | EAM | Enterprise Asset Management | Lifecycle aset fizikal perusahaan. | Asset Hierarchy; Lifecycle; Maintenance Strategy; Reliability | Lebih luas daripada CMMS; CMMS execution maintenance. | Finance; Procurement; CMMS; IoT |
| MFG-004 | PLM | Product Lifecycle Management | Data produk dari reka bentuk ke end-of-life. | CAD Links; BOM; Revisions; Engineering Change; Specs | Product definition; MES executes production. | CAD; ERP; MES; QMS |
| MFG-005 | CAD/CAM | Computer-Aided Design/Manufacturing | Reka bentuk teknikal dan arahan pembuatan. | 3D/2D Design; Toolpaths; Drawings; NC Programs | Engineering authoring; bukan order/production execution. | PLM; MES; DMS |
| MFG-006 | SCADA | Supervisory Control & Data Acquisition | Pemantauan dan kawalan proses industri. | Telemetry; Alarms; Historian; HMI | Sistem kawalan industri; jangan jalankan kawalan keselamatan melalui BOS. | PLC; MES; IoT; Historian |
| MFG-007 | PLC/Industrial Control | Programmable Logic Control | Kawalan mesin dan proses masa nyata. | Logic; I/O; Interlocks; Safety Interfaces | Kekal pada sistem OT khusus; AI BIJAN hanya integrasi terhad dan selamat. | SCADA; MES |
| MFG-008 | OEE Analytics | Overall Equipment Effectiveness | Keberkesanan mesin dan line pengeluaran. | Availability; Performance; Quality; Loss Analysis | Analitik; MES/SCADA sumber data operasi. | MES; SCADA; BI |
| MFG-009 | QMS Manufacturing | Manufacturing Quality | Kualiti semasa proses pembuatan. | Inspection Plans; SPC; NCR; CAPA; Traceability | Boleh menjadi industry profile QMS umum, bukan duplicate product. | MES; PLM; ERP |
| MFG-010 | APS | Advanced Planning & Scheduling | Jadual pengeluaran terhad kapasiti. | Finite Capacity; Sequencing; Constraints; What-if | Perancangan; MES melaksanakan work orders. | ERP; MRP; MES |
| HLT-001 | HIS | Hospital Information System | Operasi hospital dan pentadbiran klinikal. | Patient Admin; Admissions; Billing; Departments; Bed Management | Hospital operations; EMR clinical record khusus. | EMR; LIS; RIS; Finance |
| HLT-002 | EMR | Electronic Medical Records | Rekod klinikal pesakit. | Encounters; Notes; Diagnoses; Medication; Allergies | Rekod klinikal; akses ketat dan audit. | HIS; LIS; RIS; Pharmacy |
| HLT-003 | EHR | Electronic Health Records | Rekod kesihatan longitudinal merentas penjagaan. | Longitudinal Record; Interoperability; Consent | Lebih luas daripada EMR satu fasiliti. | EMR; HIS; Labs; APIs |
| HLT-004 | LIS | Laboratory Information System | Pengurusan ujian makmal. | Orders; Specimens; Results; QC; Lab Workflow | Keputusan makmal; bukan rekod klinikal penuh. | HIS; EMR |
| HLT-005 | RIS | Radiology Information System | Aliran kerja radiologi. | Orders; Scheduling; Reporting; Modality Worklist | PACS menyimpan imej; RIS mengurus workflow. | HIS; EMR; PACS |
| HLT-006 | PACS | Picture Archiving & Communication System | Penyimpanan dan paparan imej perubatan. | Imaging Archive; Viewer; DICOM Routing | Imej diagnostik; bukan RIS workflow. | RIS; EMR |
| HLT-007 | Pharmacy Management | Pharmacy System | Preskripsi, dispensing dan inventori farmasi. | Medication Orders; Dispensing; Stock; Interactions | Bukan diagnosis atau rekod klinikal keseluruhan. | EMR; IMS; Payment |
| HLT-008 | Practice Management | Clinic Practice Management | Operasi klinik dan appointment. | Appointments; Registration; Billing; Claims | Pentadbiran klinik; EMR menyimpan rekod klinikal. | EMR; CRM; Finance |
| HLT-009 | Telehealth | Telemedicine Platform | Konsultasi jarak jauh. | Video Visit; Consent; Scheduling; Clinical Handoff | Komunikasi klinikal; rekod klinikal kekal EMR. | EMR; Appointment; Payment |
| HLT-010 | RCM | Revenue Cycle Management | Kitar hasil fasiliti kesihatan. | Eligibility; Claims; Coding; Billing; Denials | Kewangan healthcare; bukan clinical record. | HIS; EMR; Finance |
| HOS-001 | Restaurant Management | Restaurant Operations System | Operasi restoran hujung ke hujung. | Menu; Table; Kitchen; Recipe; Orders; Food Cost; Shift | Industry bundle; POS/IMS/OMS kekal domain teras. | POS; OMS; IMS; Procurement; Finance |
| HOS-002 | KDS | Kitchen Display System | Paparan dan routing pesanan dapur. | Tickets; Stations; Prep Times; Bump Bar | Execution dapur; bukan order master. | POS; OMS; Restaurant |
| HOS-003 | Recipe & Food Cost | Recipe Management | Resipi, BOM bahan dan kos menu. | Recipes; Yield; Portions; Allergens; Costing | Master recipe dan costing; stok sebenar IMS. | IMS; Procurement; POS |
| HOS-004 | Hotel PMS | Property Management System - Hotel | Operasi penginapan hotel. | Reservations; Rooms; Check-in/out; Housekeeping; Folio | Hotel PMS bukan Project PMS atau Property Real Estate PMS. | Booking; Payment; Housekeeping; Finance |
| HOS-005 | Channel Manager | Hotel Channel Management | Penyelarasan inventori bilik dan kadar saluran. | Rate Plans; Availability; OTA Sync; Restrictions | Tidak menjadi hotel reservation/folio system. | Hotel PMS; Booking; OTA |
| HOS-006 | Revenue Management Hotel | Hotel Revenue Management | Harga dan hasil bilik hotel. | Demand Forecast; Rate Optimization; Yield | Cadangan harga; hotel PMS sumber tempahan sebenar. | Hotel PMS; BI; Channel Manager |
| HOS-007 | Housekeeping Management | Housekeeping System | Jadual dan status pembersihan bilik. | Room Status; Tasks; Inspections; Linen | Operasi housekeeping; bukan room inventory master. | Hotel PMS; WFM |
| HOS-008 | Food Safety & HACCP | Food Safety Management | Rekod keselamatan makanan. | Temperature Logs; Allergens; Hygiene; Traceability | Kawalan keselamatan makanan; bukan QMS korporat penuh. | QMS; Restaurant; IMS |
| RET-001 | E-commerce Platform | Commerce System | Katalog, storefront dan checkout online. | Catalog; Cart; Checkout; Promotions; Customer Account | Channel jualan; OMS mengurus order keseluruhan. | PIM; OMS; Payment; IMS; CRM |
| RET-002 | Marketplace Management | Marketplace Integration | Pengurusan jualan di marketplace. | Listings; Orders; Fees; Settlement; Sync | Connector/channel layer; marketplace kekal platform luar. | OMS; IMS; Finance |
| RET-003 | PIM | Product Information Management | Katalog dan atribut produk. | Attributes; Variants; Media; Taxonomy; Syndication | Maklumat produk bukan stok kuantiti. | E-commerce; POS; DAM; IMS |
| RET-004 | Retail Merchandising | Merchandising System | Assortment, range dan product placement. | Assortment; Planograms; Pricing; Promotions | Perancangan merchandising; POS melaksanakan jualan. | PIM; POS; BI |
| RET-005 | Returns & Warranty | Returns Management | Pemulangan, pertukaran dan jaminan. | RMA; Inspection; Refund Routing; Warranty | OMS order return; IMS stock disposition; Finance refund. | OMS; IMS; Payment; CRM |
| RET-006 | Subscription Commerce | Subscription Commerce | Pesanan berulang produk/perkhidmatan. | Plans; Renewals; Dunning; Pause; Usage | Billing memegang caj; payment memproses transaksi. | Billing; Payment; OMS; CRM |
| CON-001 | Construction Management | Construction Project System | Pengurusan projek pembinaan. | Projects; Site Diary; Progress; Claims; Variations; Handover | Industry project execution; PMS umum boleh jadi shared core. | PMS-PRJ; Procurement; Finance; DMS |
| CON-002 | BIM | Building Information Modeling | Model maklumat bangunan dan koordinasi reka bentuk. | Models; Clash Detection; Revisions; Quantities | Model digital; bukan schedule/cost system. | CAD; PLM; Construction |
| CON-003 | Estimating & BOQ | Construction Estimating | Anggaran kos dan Bill of Quantities. | Takeoff; Rates; BOQ; Tender Estimate | Anggaran; procurement/finance rekod komitmen sebenar. | BIM; Procurement; Finance |
| CON-004 | Site Management | Construction Site Management | Operasi harian tapak. | Site Diary; Labour; Equipment; Inspections; RFIs | Pelaksanaan tapak; bukan contract lifecycle. | Construction; EHS; WFM |
| CON-005 | Project Controls | Project Controls System | Kos, masa dan kemajuan projek. | Baseline; Earned Value; Schedule; Cost Forecast | Kawalan projek; bukan general ledger. | PMS-PRJ; Finance; BI |
| CON-006 | Facilities Management | Facilities Management System | Operasi dan penyelenggaraan bangunan. | Work Orders; Space; Utilities; Inspections | Bangunan operasi selepas siap; CMMS/EAM boleh shared. | BMS-BLD; CMMS; EAM |
| LOG-001 | Transport Management | Transport Operations System | Pengurusan operasi pengangkutan penumpang/barang mengikut domain. | Routes; Trips; Dispatch; Driver; Schedule | Bezakan daripada TMS-LOG freight planning dan Fleet. | Fleet; Booking; WFM; GPS |
| LOG-002 | Fleet Telematics | Telematics System | Lokasi dan data kenderaan. | GPS; Sensors; Fuel; Driver Behaviour | Data telematik; bukan financial asset register. | Fleet; TMS-LOG; BI |
| LOG-003 | Last Mile Delivery | Last Mile System | Dispatch dan bukti penghantaran akhir. | Dispatch; Routing; POD; Customer ETA | Pelaksanaan penghantaran; OMS sumber order. | OMS; TMS-LOG; Maps |
| LOG-004 | Freight Forwarding | Freight Management | Operasi forwarding dan dokumentasi kargo. | Shipments; Customs Docs; Rates; Milestones | Bukan customs authority atau carrier system. | TMS-LOG; DMS; Finance |
| LOG-005 | Passenger Booking & Dispatch | Passenger Transport System | Tempahan dan dispatch penumpang. | Reservations; Seats; Routes; Dispatch; Tickets | Bukan freight TMS. | Booking; Payment; Fleet |
| EVT-001 | Event Management | Event Operating System | Perancangan, vendor, venue dan hari acara. | Event Plan; Budget; Vendors; Run Sheet; Crew; Attendees | Event domain; marketing automation hanya promosi. | CRM; PMS-PRJ; Procurement; Payment |
| EVT-002 | Venue Management | Venue System | Ketersediaan dan operasi venue. | Spaces; Calendars; Packages; Site Ops | Bukan hotel room PMS atau property rent ledger. | Event; Booking; Finance |
| EVT-003 | Ticketing & Registration | Ticketing System | Pendaftaran dan tiket acara. | Ticket Types; QR; Check-in; Refund; Capacity | Bukan event project plan. | Event; Payment; CRM |
| EVT-004 | Rental Management | Rental System | Tempahan dan lifecycle aset sewaan. | Availability; Contracts; Dispatch; Returns; Damage | Bukan asset accounting; mengurus transaksi sewaan. | OMS; Asset; Payment; DMS |
| EVT-005 | Appointment Booking | Booking System | Slot, availability dan tempahan perkhidmatan. | Calendar; Staff Availability; Deposits; Reschedule | Tidak menjadi clinical record atau project management. | CRM; Payment; WFM |
| EVT-006 | Field Service Management | FSM | Kerja lapangan, dispatch dan service reports. | Work Orders; Technician Routing; Parts; Sign-off | Field execution; CMMS untuk penyelenggaraan aset dalaman. | CRM; Inventory; Maps; Billing |
| EVT-007 | Property Management - Real Estate | Property Management System | Portfolio hartanah, sewaan dan penyewa. | Units; Tenants; Leases; Rent; Maintenance Requests | Bukan Hotel PMS atau Project PMS. | Accounting; CRM; Maintenance |
| EVT-008 | Learning & Student Information | SIS | Pentadbiran institusi pendidikan. | Admissions; Student Records; Timetables; Grades | SIS rekod pelajar; LMS menyampaikan pembelajaran. | LMS-LRN; Finance; HRMS |
| EVT-009 | Learning Management - Education | LMS Education | Pengurusan kandungan dan pembelajaran pendidikan. | Courses; Lessons; Assessments; Progress; Certificates | Bukan Loyalty Management atau HR employee LMS. | SIS; Content; Payment |
| EVT-010 | Library Management | Library System | Katalog, pinjaman dan koleksi perpustakaan. | Catalog; Circulation; Reservations; Acquisitions | Bukan DMS enterprise. | SIS; Payment; Identity |
| EVT-011 | Professional Services Automation | PSA | Operasi firma perkhidmatan profesional. | Engagements; Time; Resource; Billing; Margin | Projek dan resource profesional; bukan ERP umum. | CRM; PMS-PRJ; Finance; HRMS |
| EVT-012 | Membership Management | Membership System | Keahlian organisasi/kelab dan renewal. | Member Registry; Dues; Benefits; Events | Keahlian pelanggan bukan user identity OAM. | CRM; Billing; Event |
| CORE-001 | OAM | Organization & Access Management | Organisasi, membership, roles, permissions, data scope dan seat. | Tenant; Company; Branch; Department; Team; Membership; RBAC/ABAC; Approval | Shared platform core; bukan HRIS atau CRM. | All modules; Subscription; Audit |
| CORE-002 | Subscription & Entitlement | Subscription Management | Pelan, billing entitlement dan kuota. | Plans; Subscriptions; Add-ons; Seats; Limits; Grace Period | Menentukan akses produk; authorization individu kekal OAM. | OAM; Billing; AI Gateway |
| CORE-003 | Notification Management | Notification System | Penghantaran notifikasi transaksi dan peringatan. | Templates; Channels; Preferences; Delivery; Retry | Bukan CRM campaign engine. | All modules; Email; SMS; WhatsApp |
| CORE-004 | Workflow & Approval | Approval Management | Kelulusan transaksi dan tindakan berisiko. | Rules; Delegation; Sequential; Parallel; Escalation | BPM mengatur proses luas; approval service menguatkuasa keputusan kelulusan. | OAM; Finance; Procurement; HRMS |
| CORE-005 | AI Gateway | AI Model Gateway | Routing model, token meter dan kawalan AI. | Providers; Routing; Quotas; Wallet; Cost; Logs | Tidak menyimpan business truth atau melangkaui agent permission. | AI Agents; Billing; OAM |
| CORE-006 | AI Agent Management | Agent Orchestration | Lifecycle agent, tools, memory dan policy. | Agent Registry; Tools; Plans; Evaluation; Execution; Audit | Agent ialah service identity, bukan user berprivilege tinggi. | OAM; AI Gateway; BPM; All modules |
| CORE-007 | Knowledge Management | Knowledge Management System | Pengetahuan dan SOP organisasi. | Knowledge Base; Search; Versioning; Review; Access | Dokumen asal mungkin di DMS; knowledge index menghormati ACL. | DMS; LMS; AI Agents |
| CORE-008 | Master Data & Reference Data | Reference Data Service | Kod, taxonomy dan data rujukan standard. | Code Sets; Units; Currencies; Countries; Industry Codes | Bukan master record pelanggan/produk yang domain-specific. | MDM; ERP; PIM |
| CORE-009 | Audit & Activity Log | Audit Management Service | Rekod tindakan pengguna, sistem dan agent. | Actor; Action; Target; Timestamp; Correlation; Retention | Tidak sama dengan Audit Management engagement. | OAM; SIEM; All modules |
| CORE-010 | Connector Registry | Connector Management | Pendaftaran, versi dan lifecycle connector. | Registry; Credentials Ref; Scopes; Health; Version | Tidak menjalankan transformasi migrasi penuh. | iPaaS; Migration Hub; API |
| CORE-011 | Data Quality Management | Data Quality System | Profiling, rules dan pemantauan kualiti data. | Rules; Profiling; Duplicates; Exceptions; Scorecards | Tidak menjadi MDM atau migration orchestrator. | MDM; Migration Hub; DWH |
| CORE-012 | Customer Identity & Consent | CIAM/Consent | Identiti pelanggan luaran dan persetujuan. | Customer Login; Consent; Preferences; Identity Link | Berbeza daripada workforce IAM/OAM. | CDP; CRM; Commerce |
| CORE-013 | Search & Discovery | Enterprise Search | Carian merentas data yang dibenarkan. | Indexing; Facets; Relevance; ACL Filtering | Search index bukan sumber rekod rasmi. | DMS; CRM; Knowledge; All modules |
| CORE-014 | Localization & Tax Engine | Localization Service | Bahasa, timezone, currency dan rules negara. | Locales; Currency; Tax Profiles; Formats | Tax calculation domain-specific; localization menyediakan config. | All modules; Tax |
| CORE-015 | File & Object Storage | File Storage Service | Fail, attachment dan lifecycle storage. | Upload; Version; Malware Scan; Retention; Signed URL | Bukan DMS content governance atau DAM taxonomy. | DMS; DAM; All modules |

## 5. Audit pertindihan dan keputusan canonical

| Konflik/overlap | Punca | Keputusan canonical |
|---|---|---|
| LMS | Loyalty Management System vs Learning Management System | Pisahkan kod/domain: `GRO-007 LOYALTY` dan `HR-005 LMS-LRN` / `EVT-009 LMS Education`. Jangan gunakan singkatan LMS tanpa domain. |
| PMS | Project Management vs Hotel PMS vs Property Management | Pisahkan `BOS-008 PMS-PRJ`, `HOS-004 Hotel PMS`, `EVT-007 Property Management`. Nama paparan wajib nyatakan domain. |
| TMS | Transportation Management vs Logistics Transportation Management | Pisahkan `SUP-006 TMS-LOG` untuk freight/logistics dan `LOG-001 Transport Operations` untuk operasi penumpang/transport fleet. Fleet kekal `SUP-009`. |
| BMS | Business Management System vs Building Management System | `BOS-004 BMS` ialah pengurusan bisnes; Building Management dijadikan `BMS-BLD` capability di Facilities/Building domain, bukan kod BMS generik. |
| CRM vs CDP | Kedua-duanya mempunyai data pelanggan | CRM mengurus akaun, hubungan, aktiviti dan pipeline; CDP menyatukan event/profile/identity daripada banyak sumber. CDP tidak menggantikan CRM. |
| ERP vs FMS vs Accounting | Semua menyentuh kewangan | ERP ialah orchestration/payung proses; Accounting/GL ialah rekod kewangan rasmi; FMS ialah pengurusan/analisis kewangan. |
| IMS vs WMS | Kedua-duanya mengurus stok | IMS/Inventory ledger ialah kuantiti dan stock movement truth; WMS mengurus lokasi, picking, packing dan warehouse execution. |
| BPM vs BPA vs Workflow/Approval | Semua melibatkan proses | BPM memodel dan mengurus proses hujung ke hujung; BPA automasi tasks; Approval service menguatkuasa keputusan kelulusan. |
| Asset Management vs EAM vs CMMS | Aset dan maintenance bertindih | Asset register menyimpan identiti aset; EAM mengurus lifecycle enterprise; CMMS melaksanakan maintenance work orders. |
| CMS vs DAM vs DMS | Semua menyimpan kandungan/fail | CMS menerbitkan halaman; DAM mengurus aset media; DMS mengurus dokumen perniagaan, versi dan retention. |
| HRMS vs HRIS vs HCM | Skop HR bertindih | HRIS ialah employee master; HRMS ialah proses HR operasi; HCM ialah payung strategik talent/workforce. |
| POS vs OMS vs Payment | Semua menyentuh jualan | POS menangkap transaksi; OMS mengurus order lifecycle; Payment mengurus payment intent/provider/status/refund. |
| Hotel PMS vs Property PMS | Kedua-duanya bernama Property Management | Hotel PMS mengurus bilik/guest folio; property real-estate mengurus unit, tenancy, lease dan rent. |
| QMS vs EHS vs EMS | Compliance/quality overlap | QMS ialah kualiti; EHS ialah keselamatan pekerja/persekitaran kerja; EMS ialah pengurusan impak alam sekitar. |
| OAM vs IAM vs HRIS | Semua mengurus pengguna/pekerja | IAM mengesahkan identiti; OAM mengurus organisasi/membership/authorization; HRIS mengurus rekod pekerja. |
| BI vs DWH vs MDM | Semua data-centric | BI menyampaikan analitik; DWH menyimpan data analitik; MDM mengurus golden/master data dan stewardship. |
| Event Management vs Event Marketing | Nama hampir sama | Event Management mengurus operasi acara; Event Marketing mengurus promosi, nurture dan attribution. |

## 6. Domain boundaries dan system-of-record

| Domain data | System of record dicadangkan | Sistem pengguna data |
|---|---|---|
| Identiti login | IAM/Identity | OAM dan semua aplikasi |
| Tenant, organisasi, membership, role | OAM | Semua modul |
| Employee master | HRIS/HRMS mengikut tenant setup | Payroll, WFM, LMS |
| Customer account & sales activities | CRM | CDP, Helpdesk, OMS |
| Unified customer events/profile | CDP | MAP, BI, personalization |
| Order lifecycle | OMS | POS, E-commerce, Logistics, Finance |
| Stock ledger & balances | IMS atau ERP/WMS yang dipilih secara eksplisit | POS, OMS, WMS, Procurement |
| Warehouse execution | WMS | OMS, TMS, IMS |
| General ledger | GL/Accounting | FMS, FP&A, BI |
| Payment status | Payment orchestration + provider confirmation | AR, Billing, POS, OMS |
| Product content | PIM | POS, E-commerce, CMS |
| Manufacturing execution | MES | ERP, MRP, QMS, BI |
| Clinical record | EMR/EHR | HIS, LIS, RIS, Pharmacy |
| Contract lifecycle | CLM | CRM, Procurement, Finance |
| Document originals | DMS / DAM mengikut jenis aset | CLM, HRMS, CMS, QMS |
| Audit history | Append-only audit service | OAM, SIEM, domain modules |
| Connector credentials | Secrets vault | Connector runtime |
| Migration job history | B-Migration Hub | Admin, tenant operators, audit |

## 7. Hubungan sistem utama — contoh aliran transaksi

### 7.1 POS → OMS → Inventory → Finance → AI CFO
1. POS menangkap jualan dan menghantar order/transaction event.
2. OMS merekod order canonical dan status fulfillment.
3. IMS merekod stock movement dengan idempotency key.
4. Payment service mengesahkan status daripada provider; redirect browser bukan bukti muktamad.
5. Accounting/GL merekod jurnal melalui kontrak domain.
6. BI mengemas kini metrik; AI CFO menganalisis data yang telah disahkan.
7. Reconciliation membandingkan POS, OMS, stock ledger, provider settlement dan GL.

### 7.2 CRM → CPQ → Proposal/CLM → OMS → Billing/Payment
CRM mengurus lead dan opportunity; CPQ menghasilkan konfigurasi/harga; Proposal/CLM mengurus tawaran dan kontrak; OMS diwujudkan selepas penerimaan sah; Billing mengeluarkan invoice; Payment mengesahkan kutipan; Finance merekod dan reconcile.

### 7.3 Manufacturing
ERP/Planning → MRP → Procurement/IMS → MES → QMS → WMS → GL/Costing → BI. SCADA/PLC kekal OT systems dan tidak digantikan oleh MES atau AI BIJAN.

## 8. OAM — Organization & Access Management

OAM ialah shared core service yang mengawal tenant, organization group, company, branch, department, team, user membership, roles, permissions, data scopes, seats, entitlements dan approval.

### 8.1 Organization hierarchy
```text
Tenant
└── Organization Group
    ├── Company
    │   ├── Branch
    │   │   ├── Department
    │   │   │   └── Team
    │   │   └── Users/Memberships
    │   └── Users/Memberships
    └── Other Companies
```

- `tenant_id` ialah boundary keselamatan SaaS; `organization_id` ialah business entity/group scope.
- Satu global user boleh mempunyai multiple memberships yang berasingan.
- RBAC + ABAC, default deny, least privilege, backend enforcement.
- Permission format: `module.resource.action`, contoh `finance.payments.release`, `crm.contacts.view`, `migration.jobs.execute`.
- Data scopes: own record, assigned, team, department, branch, selected branches, company, selected companies, organization group.
- Membership lifecycle: invited, pending, active, suspended, revoked, expired.
- AI agent ialah service identity dengan tenant/org, permission, tool allowlist, quota, execution policy dan audit sendiri.

### 8.2 Standard role templates
Business Owner; Organization Owner; Company Admin; Organization Admin; General Manager; Department Head; Manager; Supervisor; Team Leader; Finance Manager; HR Manager; Sales Manager; Marketing Manager; Operations Manager; Staff; Field Worker; Auditor; Viewer; Customer; Vendor; External Collaborator.

### 8.3 OAM acceptance tests
- Cross-tenant access denied; no privilege escalation; role/data scope verified.
- Multi-company, branch, department, team and multi-membership scenarios.
- Subscription expiry does not delete customer data.
- Seat billing follows accepted/active membership policy.
- High-risk actions require approval and audit.
- AI agent cannot exceed its assigned permissions.

## 9. B-Migration & Integration Hub

Shared capability merentasi Automation & Integration dan Core Platform. Tiga skop: data migration, system integration/coexistence, dan replacement/transformation.

### 9.1 Subsystems
B-Connector Hub; Connector Marketplace; Data Import/Export; Data Extraction; Data Mapping; Data Transformation; Data Cleansing; Data Validation; Data Migration; Data Synchronization; CDC; Scheduler; Monitor; Audit; AI Migration Assistant; Reconciliation; Conflict Manager.

### 9.2 Pipeline
Source discovery → extraction → profiling → AI mapping proposal → human mapping approval → transformation → validation → staging trial → approved production batches → reconciliation → cutover/parallel run → archive/retire legacy only after sign-off.

### 9.3 Sync policy
Set system of record per entity/field, sync direction, frequency, idempotency, retries/backoff, conflict resolution, cursor/CDC, dead-letter queue, monitoring and recovery. Avoid uncontrolled bidirectional loops.

### 9.4 Connector targets
ERP: SAP, Oracle, Dynamics, Odoo, NetSuite; databases: MySQL, PostgreSQL, SQL Server, Oracle, MariaDB, Db2, MongoDB, Snowflake, Redshift; accounting: QuickBooks, Xero, Sage, MYOB, AutoCount, SQL Account, Bukku, ABSS; CRM: Salesforce, HubSpot, Zoho, Pipedrive; HR: Workday, SuccessFactors, BambooHR, Zoho People, ADP; commerce: Shopify, WooCommerce, Magento, BigCommerce, Lazada, Shopee, TikTok Shop, EasyStore; files: CSV, XLSX, XML, JSON, SQL dumps. Ini sasaran connector, bukan pengesahan integrasi siap.

### 9.5 Security
Credentials disimpan dalam encrypted vault; least privilege; tenant isolation; TLS; rotation; PII masking; job isolation; scoped access; audit; rate limits. Jangan simpan secrets plaintext atau masukkan credential ke prompt/log.

## 10. API, event dan architecture contract

- API base path: `/api/v1`; versioned contracts dan OpenAPI.
- Domain events: `OrderCreated`, `OrderPaid`, `StockMoved`, `InvoiceIssued`, `PaymentReleased`, `ProductionOrderStarted`, `MigrationJobCompleted`, `ConnectorSyncFailed`, `MembershipRoleChanged`.
- Gunakan transactional outbox, idempotent consumers, correlation ID, schema version dan retry/dead-letter handling.
- Modul tidak mengakses jadual milik modul lain secara terus; gunakan service contract/API/event.
- Laravel modular monolith sebagai control plane; pecahkan microservices hanya apabila terdapat sebab operasi yang terbukti.

## 11. AI Intelligence & governance

AI CEO/Executive orchestration; AI CFO; AI CMO; AI COO; AI CSO; B-Boardroom; domain agents. Agent tool calls mesti memeriksa OAM, data scope, entitlement, risk policy, quota dan approval. Bezakan fakta, inferens dan ramalan; semua tindakan luaran mempunyai audit dan outcome verification.

## 12. Subscription & commercial packaging

Entitlement boleh mengawal modul, seats, usage quota, AI tokens, storage, API limits, connector tiers dan enterprise capabilities. Revenue streams: SaaS plans, add-on modules, seats, AI token packages, usage-based services, connector packages, migration assessment/execution, enterprise onboarding dan custom integration.

## 13. Implementation governance

Bagi setiap canonical system, rekodkan: Product Owner; Technical Owner; System ID; legacy crosswalk; category; capability class (Core Module/Submodule/Shared Service/Industry Extension/External Integration); function; submodules; explicit exclusions; data entities; system of record; API/events; OAM permissions; entitlement; agent policy; migration adapters; dependencies; risks; status; test evidence; release version.

### 13.1 Definition of Done
1. Domain boundary dan ownership didokumenkan.
2. Schema dan migrations diuji.
3. Tenant isolation dan OAM tests lulus.
4. API/event contracts diuji.
5. Idempotency, retries dan failure handling diuji.
6. Audit events dan monitoring tersedia.
7. Security review dan acceptance tests selesai.
8. Operational runbook, backup/recovery dan support ownership ditetapkan.

## 14. Open decisions / daftar tindakan

1. Import daftar asal lengkap nama sistem 1–210 untuk memuktamadkan crosswalk legacy.
2. Sahkan sama ada 210 asal ialah sistem, submodul, produk, atau gabungan capability; jangan anggap semua ialah bounded context.
3. Approve canonical IDs dan nama paparan.
4. Tetapkan domain owners dan system of record bagi setiap data entity.
5. Tentukan MVP sequence berdasarkan customer demand, dependencies, risk dan commercial value.
6. Tentukan compliance/legal review bagi healthcare, finance, payments, tax, HR dan data privacy.
7. Lengkapkan permission matrix dan entitlement map.
8. Sahkan connector access, vendor terms, API scopes dan redistribution rights.

## 15. Ringkasan keputusan

Registry baseline ini mengandungi **179 canonical systems/capabilities** dengan fungsi, submodul, sempadan dan integrasi ringkas; mengekalkan **210 legacy ID slots** dalam 15 kategori asal; merekod **17 isu pertindihan utama** dengan keputusan canonical; dan menggabungkan OAM serta B-Migration & Integration Hub sebagai shared platform services.

**Penting:** Ini ialah Master Inventory rasmi versi baseline gabungan, bukan dakwaan bahawa semua sistem telah dibangunkan atau bahawa semua nama legacy 1–210 telah dipulihkan. Apabila daftar asal penuh diterima, lakukan crosswalk satu-persatu tanpa mengubah legacy ID.
