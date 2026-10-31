# AI BIJAN — Inventori Induk Sistem Perniagaan v2.0
## THE BUSINESS GROWTH OPERATING SYSTEM — One platform. One business ecosystem.

Tarikh: 31/10/2026 · Disediakan: Dimas (T-13) · Asas: Master Inventory v1.0 (179 sistem) + registry status kod (PR #3) + senarai owner 31/10.

## 1. Ringkasan

- **Jumlah sistem berdaftar: 235** (179 dari v1.0 + 56 baharu dalam v2.0).
- Setiap sistem kini ada: lapisan (L1–L5), fungsi, submodul, sempadan sistem, integrasi utama, **status dalam kod AI Bijan** dan modul Laravel yang memegangnya.
- **Semua 85 sistem dalam senarai owner sudah dipetakan** (§3). Tiada yang tertinggal. Akronim bertindih (LMS×3, BMS×2, TMS×2, PMS×3) dipecahkan kepada ID berasingan.
- Prinsip: **satu platform, satu pangkalan data per tenant, satu identiti, satu data induk.** Sistem = modul dalam modular monolith Laravel, berhubung melalui service contract & event, bukan aplikasi berasingan.

| Lapisan | Bilangan |
|---|---|
| Layer 1 — AI Intelligence | 16 |
| Layer 2 — Business Systems | 112 |
| Layer 3 — Industry Solutions | 58 |
| Layer 4 — Automation & Integration | 22 |
| Layer 5 — Core Platform | 27 |

| Status kod | Bilangan |
|---|---|
| TIADA | 119 |
| IMPLEMENTED + diuji | 58 |
| IMPLEMENTED (ujian nipis) | 35 |
| SEBAHAGIAN | 21 |
| RANGKA | 2 |

**Makna status:** `IMPLEMENTED + diuji` = modul & ujian wujud · `IMPLEMENTED (ujian nipis)` = kod ada, ujian sedikit · `SEBAHAGIAN` / `RANGKA` = modul ada, capability belum lengkap · `TIADA` = belum dibina. Status sistem v1.0 diambil dari registry PR #3 (disemak manual dalam kod 31/10); status sistem baharu v2.0 dinilai dari nama & isi modul, **belum diaudit baris demi baris**.

## 2. Seni bina 5 lapisan

```
┌───────────────────────────────────────────────────────────────────────┐
│ L1 AI Intelligence   AI CEO · CFO · CMO · COO · CSO · CHRO · Boardroom │
│                      Copilot · Tool Registry · Peti Kelulusan · Eval   │
├───────────────────────────────────────────────────────────────────────┤
│ L2 Business Systems  CRM · ERP · HRMS · POS · OMS · WMS · SCM · FMS    │
│                      BI · BPM · GRC ...  (domain = pemilik data)       │
├───────────────────────────────────────────────────────────────────────┤
│ L3 Industry          Restoran · Runcit · Klinik · Pembinaan · Acara    │
│                      Hotel · Servis · Pembuatan · Logistik             │
│                      (pek konfigurasi + modul khusus atas L2)          │
├───────────────────────────────────────────────────────────────────────┤
│ L4 Automation        Workflow · Event Bus · API · MCP · Webhooks · RPA │
│                      WhatsApp · Email · Payment · Marketplace · LHDN   │
├───────────────────────────────────────────────────────────────────────┤
│ L5 Core Platform     Laravel · Multi-Tenant · OAM/IAM · Data Induk     │
│                      Security · Billing/Entitlement · AI Gateway       │
└───────────────────────────────────────────────────────────────────────┘
```

**Peraturan antara lapisan:**
1. L1 (AI) **membaca** dari L2/L3 dan **bertindak hanya melalui** Tool Registry (AI-009) → Peti Kelulusan (AI-010) untuk tindakan berisiko. AI tidak pernah menulis terus ke pangkalan data.
2. L2 ialah **pemilik rekod (system of record)**. Setiap entiti data ada satu pemilik sahaja (lihat §6).
3. L3 **bukan salinan** L2. Restoran = POS + KDS + Resepi + pek konfigurasi; Klinik = Booking + EMR + Billing. Industri menambah modul khusus, bukan pangkalan data baharu.
4. L4 menghubung modul sesama sendiri (event) dan dengan dunia luar (connector). Tiada modul membaca jadual modul lain secara terus.
5. L5 dikongsi oleh semua: satu `tenant_id`, satu pengguna, satu set kebenaran `modul.sumber.tindakan`, satu audit log.

## 3. Crosswalk senarai owner → ID canonical

Bukti semua sistem dalam senarai owner (31/10) ada tempat dalam inventori.

### 1. Pengurusan perniagaan & operasi

| Sistem (senarai owner) | ID canonical | Status kod |
|---|---|---|
| CRM | BOS-001 | IMPLEMENTED + diuji |
| ERP | BOS-002 | TIADA |
| BPM | BOS-003 | IMPLEMENTED + diuji |
| BMS (Business Management System) | BOS-004 | TIADA |
| OMS | BOS-005 | IMPLEMENTED + diuji |
| POS | BOS-006 | IMPLEMENTED + diuji |
| OSM | BOS-007 | IMPLEMENTED + diuji |
| PMS (Project) | BOS-008 | IMPLEMENTED (ujian nipis) |
| WFM | BOS-009 | TIADA |
| EPM | BOS-010 | IMPLEMENTED + diuji |

### 2. Kewangan & perakaunan

| Sistem (senarai owner) | ID canonical | Status kod |
|---|---|---|
| Accounting System | FIN-001 | IMPLEMENTED + diuji |
| FMS | FIN-002 | IMPLEMENTED + diuji |
| AP | FIN-003 | IMPLEMENTED + diuji |
| AR | FIN-004 | IMPLEMENTED + diuji |
| FP&A | FIN-005 | TIADA |
| Treasury Management | FIN-006 | TIADA |
| Billing System | FIN-007 | IMPLEMENTED + diuji |
| Payment Management | FIN-008 | IMPLEMENTED + diuji |
| Expense Management | FIN-009 | IMPLEMENTED + diuji |
| Tax Management | FIN-010 | TIADA |

### 3. Jualan, pemasaran & pelanggan

| Sistem (senarai owner) | ID canonical | Status kod |
|---|---|---|
| SFA | GRO-001 | IMPLEMENTED + diuji |
| MAP | GRO-002 | IMPLEMENTED + diuji |
| CDP | GRO-003 | TIADA |
| DMP | GRO-004 | TIADA |
| CMS | GRO-005 | IMPLEMENTED (ujian nipis) |
| DAM | GRO-006 | IMPLEMENTED (ujian nipis) |
| LMS (Loyalty) | GRO-007 | IMPLEMENTED (ujian nipis) |
| PRM | GRO-008 | SEBAHAGIAN |
| Affiliate Management | GRO-009 | IMPLEMENTED (ujian nipis) |
| CPQ | GRO-010 | IMPLEMENTED + diuji |
| Helpdesk | GRO-011 | IMPLEMENTED (ujian nipis) |
| Contact Centre | GRO-012 | IMPLEMENTED + diuji |

### 4. Pekerja & sumber manusia

| Sistem (senarai owner) | ID canonical | Status kod |
|---|---|---|
| HRMS | HR-001 | IMPLEMENTED (ujian nipis) |
| HRIS | HR-002 | IMPLEMENTED (ujian nipis) |
| HCM | HR-003 | TIADA |
| ATS | HR-004 | TIADA |
| LMS (Learning) | HR-005 | TIADA |
| Payroll | HR-006 | IMPLEMENTED + diuji |
| T&A | HR-007 | IMPLEMENTED (ujian nipis) |
| ESS | HR-008 | IMPLEMENTED (ujian nipis) |
| Performance Management | HR-009 | TIADA |

### 5. Stok, gudang & rantaian bekalan

| Sistem (senarai owner) | ID canonical | Status kod |
|---|---|---|
| IMS | SUP-001 | IMPLEMENTED (ujian nipis) |
| WMS | SUP-002 | IMPLEMENTED (ujian nipis) |
| SCM | SUP-003 | IMPLEMENTED + diuji |
| MRP | SUP-004 | SEBAHAGIAN |
| SRM | SUP-005 | IMPLEMENTED + diuji |
| TMS (Transportation) | SUP-006 | IMPLEMENTED (ujian nipis) |
| YMS | SUP-007 | TIADA |
| Procurement System | SUP-008 | IMPLEMENTED + diuji |
| Fleet Management | SUP-009 | IMPLEMENTED + diuji |
| Asset Management | SUP-010 | IMPLEMENTED (ujian nipis) |

### 6. Teknologi, data & keselamatan

| Sistem (senarai owner) | ID canonical | Status kod |
|---|---|---|
| BI | TEC-001 | IMPLEMENTED + diuji |
| DWH | TEC-002 | TIADA |
| MDM | TEC-003 | TIADA |
| iPaaS | TEC-004 | IMPLEMENTED + diuji |
| API Management | TEC-005 | TIADA |
| IAM | TEC-006 | IMPLEMENTED (ujian nipis) |
| SIEM | TEC-007 | TIADA |
| ITSM | TEC-008 | TIADA |
| RPA | TEC-009 | TIADA |
| DMS | TEC-010 | IMPLEMENTED (ujian nipis) |

### 7. Sistem korporat & pematuhan

| Sistem (senarai owner) | ID canonical | Status kod |
|---|---|---|
| GRC | GRC-001 | TIADA |
| QMS | GRC-002 | TIADA |
| EHS | GRC-003 | TIADA |
| EMS | GRC-004 | TIADA |
| CLM | GRC-005 | IMPLEMENTED (ujian nipis) |
| ERM | GRC-006 | TIADA |
| Audit Management | GRC-007 | IMPLEMENTED (ujian nipis) |
| Policy Management | GRC-008 | TIADA |

### 8. Sistem industri khusus

| Sistem (senarai owner) | ID canonical | Status kod |
|---|---|---|
| HIS | HLT-001 | TIADA |
| EMR | HLT-002 | TIADA |
| LIS | HLT-004 | TIADA |
| RIS | HLT-005 | TIADA |
| Resto Management | HOS-001 | IMPLEMENTED + diuji |
| Hotel PMS | HOS-004 | TIADA |
| BMS (Building Management System) | CON-007 (baharu) + CON-006 | TIADA |
| CMMS | MFG-002 | IMPLEMENTED (ujian nipis) |
| EAM | MFG-003 | IMPLEMENTED (ujian nipis) |
| MES | MFG-001 | RANGKA |
| CAD/CAM | MFG-005 | TIADA |
| LMS (Logistics) | LOG-001 | IMPLEMENTED + diuji |
| TMS (Transport) | LOG-001 / SUP-006 | IMPLEMENTED + diuji |
| Event Management System | EVT-001 | TIADA |
| Construction Management System | CON-001 | TIADA |
| Property Management System | EVT-007 | TIADA |

**Akronim bertindih — keputusan:**

| Akronim | Maksud | ID |
|---|---|---|
| LMS | Loyalty Management System | GRO-007 |
| LMS | Learning Management System | HR-005 (kod LMS-LRN) |
| LMS | Logistics Management System | LOG-001 |
| BMS | Business Management System | BOS-004 |
| BMS | Building Management System | CON-007 (kod BMS-BLD) |
| TMS | Transportation Mgmt (rantaian bekalan) | SUP-006 (kod TMS-LOG) |
| TMS | Transport Operations (syarikat pengangkutan) | LOG-001 |
| PMS | Project Management | BOS-008 (kod PMS-PRJ) |
| PMS | Hotel Property Management | HOS-004 |
| PMS | Property Management (hartanah) | EVT-007 |

Dalam UI & kod, gunakan kod unik (LMS-LRN, BMS-BLD, TMS-LOG, PMS-PRJ), jangan akronim bertindih.

## 4. Sistem baharu dalam v2.0

56 sistem ditambah untuk menutup jurang: lapisan AI (L1) belum didaftar sebagai sistem, lapisan automasi/connector (L4) belum lengkap, beberapa keperluan teras SaaS (L5), dan sistem perniagaan biasa di Malaysia yang tertinggal (cawangan, francais, zakat, komisen, kesetiausahaan, giliran, pesanan semula, COGS).

| ID | Nama | Lapisan | Sebab ditambah |
|---|---|---|---|
| AI-001 | AI CEO | L1 | Ringkasan kesihatan bisnes, keutamaan minggu ini, amaran risiko merentas semua domain. |
| AI-002 | AI CFO | L1 | Analisis aliran tunai, untung rugi, hutang, kesan setiap transaksi dan ramalan. |
| AI-003 | AI CMO | L1 | Prestasi kempen, cadangan kandungan, segmen pelanggan dan bajet iklan. |
| AI-004 | AI COO | L1 | Pantau operasi harian: stok kritikal, pesanan lewat, kekurangan staf, SLA. |
| AI-005 | AI CSO | L1 | Saluran jualan, ramalan, lead panas dan susulan. |
| AI-006 | AI CHRO | L1 | Kehadiran, cuti, kos gaji, giliran dan risiko pekerja berhenti. |
| AI-007 | AI Boardroom | L1 | Semua ejen C-suite berbincang satu keputusan dan bentang pilihan + risiko kepada pemilik. |
| AI-008 | AI Copilot (Tanya AI Bijan) | L1 | Soal jawab BM/EN atas data syarikat dari mana-mana skrin. |
| AI-009 | Agent Tool Registry | L1 | Senarai alat yang boleh dipanggil ejen, dengan kebenaran, risiko dan pengesahan. |
| AI-010 | Approval Inbox (Human-in-the-loop) | L1 | Tindakan ejen berisiko menunggu kelulusan, kemudian dilaksana & disahkan. |
| AI-011 | AI Forecasting | L1 | Ramalan jualan, permintaan stok, aliran tunai dan kehadiran. |
| AI-012 | Document AI | L1 | Baca resit, invois pembekal, borang dan tukar kepada draf rekod. |
| AI-013 | AI Evaluation & Guardrails | L1 | Ujian kualiti jawapan ejen, had kos, sekatan topik dan log keputusan. |
| AI-014 | Setup / Onboarding Agent | L1 | Tampal menu/senarai produk → kedai siap dikonfigurasi dan boleh terus jual. |
| AUT-001 | Workflow Builder | L4 | Bina automasi "bila X berlaku, buat Y" tanpa kod. |
| AUT-002 | Event Bus & Outbox | L4 | Penghantaran event antara modul dengan outbox, idempoten dan cuba semula. |
| AUT-003 | Webhooks Keluar | L4 | Hantar event kepada sistem pelanggan dengan HMAC dan perlindungan SSRF. |
| AUT-004 | WhatsApp Integration | L4 | Resit, pautan bayar, peringatan, chat jualan & laporan ejen melalui WhatsApp. |
| AUT-005 | Email Gateway | L4 | Hantar invois, resit, notifikasi dan kempen e-mel. |
| AUT-006 | SMS & Voice Gateway | L4 | OTP, peringatan dan panggilan keluar. |
| AUT-007 | Payment Gateway Connectors | L4 | Sambung CHIP, ToyyibPay, Billplz, Stripe, DuitNow QR mengikut kedai. |
| AUT-008 | Marketplace & E-commerce Connectors | L4 | Segerak produk, stok dan pesanan dari marketplace. |
| AUT-009 | Accounting Connectors | L4 | Import & eksport data perakaunan untuk migrasi atau segerak. |
| AUT-010 | Logistics Connectors | L4 | Tempah penghantaran, cetak AWB, jejak status. |
| AUT-011 | Government & Statutory Connectors | L4 | Penghantaran statutori: e-Invois, caruman, data syarikat. |
| AUT-012 | Food Delivery Connectors | L4 | Terima pesanan aplikasi penghantaran terus ke POS/dapur. |
| AUT-013 | Bank Feed Connectors | L4 | Import penyata bank (fail atau API) untuk padanan. |
| AUT-014 | MCP & Public API | L4 | Ejen AI luar (Claude, ChatGPT) guna alat AI Bijan dengan kebenaran tenant. |
| AUT-015 | App Marketplace | L4 | Pembangun luar terbitkan integrasi/ add-on untuk tenant. |
| AUT-016 | Scheduler & Jobs | L4 | Tugas berjadual (laporan harian, peringatan, segerak). |
| CORE-016 | Multi-Tenancy Engine | L5 | Pengasingan data setiap syarikat (tenant_id) di semua lapisan. |
| CORE-017 | Tenant Provisioning | L5 | Daftar syarikat, pakej, data permulaan dan tutup akaun. |
| CORE-018 | White-label & Reseller | L5 | Rakan jual AI Bijan dengan jenama & domain sendiri, perkongsian hasil. |
| CORE-019 | Usage Metering & AI Credits | L5 | Kira penggunaan AI, mesej, storan ikut pakej. |
| CORE-020 | Offline Sync & PWA | L5 | Aplikasi berfungsi tanpa internet dan segerak bila talian kembali. |
| CORE-021 | Print & Device Service | L5 | Pencetak resit, dapur, laci tunai, pengimbas, timbang. |
| CORE-022 | Feature Flags & Config | L5 | Hidup/matikan ciri ikut pakej, industri atau tenant. |
| CORE-023 | Encryption & Key Management | L5 | Penyulitan medan sensitif (kunci API, IC, gaji) dan putaran kunci. |
| BOS-016 | Multi-Branch / Outlet Management | L2 | Cawangan, outlet, gudang outlet, laporan & akses ikut cawangan. |
| BOS-017 | Franchise Management | L2 | Francaisor urus francaisi: royalti, standard, bekalan pusat. |
| BOS-018 | Company Secretarial | L2 | Rekod SSM, pengarah, pemegang saham, mesyuarat & resolusi. |
| BOS-019 | Visitor & Front Desk | L2 | Daftar pelawat, pas, dan bilik/meja. |
| BOS-020 | Queue Management | L2 | Nombor giliran kaunter, klinik, servis. |
| FIN-019 | Commission Management | L2 | Kira komisen staf/ejen jualan ikut peraturan. |
| FIN-020 | Intercompany & Multi-Entity | L2 | Transaksi antara syarikat dalam kumpulan. |
| FIN-021 | Zakat & Islamic Finance Compliance | L2 | Pengiraan zakat perniagaan & tanda produk/akaun patuh syariah. |
| GRO-021 | Gift Card & Voucher | L2 | Jual & tebus baucar dan kad hadiah dengan liabiliti. |
| GRO-022 | Link-in-bio & Mini Site | L2 | Laman pautan, katalog ringkas dan borang lead. |
| GRO-023 | Survey & Forms | L2 | Borang awam, tinjauan pelanggan/pekerja. |
| GRO-024 | Community Management | L2 | Komuniti pelanggan/ahli, forum dan acara. |
| HR-013 | Claims & Benefits Administration | L2 | Insurans, panel klinik, faedah pekerja. |
| HR-014 | Employee Onboarding/Offboarding | L2 | Senarai semak masuk/keluar, akses, aset, dokumen. |
| SUP-014 | Purchase Requisition & Reorder | L2 | Cadang & cipta PO bila stok di bawah paras. |
| SUP-015 | Landed Cost & Import | L2 | Kos penghantaran, duti, insurans masuk ke kos stok. |
| SUP-016 | Costing / COGS | L2 | Kos purata/FIFO, margin per produk & jualan. |
| CON-007 | BMS-BLD | L3 | Kawalan & pemantauan peralatan bangunan (HVAC, lampu, tenaga, akses). |

## 5. Daftar induk penuh (ikut lapisan)

Lajur: ID · Kod · Nama · Fungsi · Submodul · Sempadan sistem · Integrasi utama · Status kod · Modul AI Bijan. ★ = baharu v2.0.

### Layer 1 — AI Intelligence

| ID | Kod | Nama | Fungsi | Submodul | Sempadan | Integrasi | Status kod | Modul |
|---|---|---|---|---|---|---|---|---|
| AI-001 ★ | AI CEO | Ejen Ketua Pegawai Eksekutif | Ringkasan kesihatan bisnes, keutamaan minggu ini, amaran risiko merentas semua domain. | Daily Brief; Priority Board; Risk Alerts; Goal Tracking; Ask CEO | Hanya membaca data disahkan & mencadang; tindakan berisiko lalu Peti Kelulusan. | BI; EPM; AI CFO/CMO/COO/CSO; Boardroom | SEBAHAGIAN | ExecutiveAI |
| AI-002 ★ | AI CFO | Ejen Kewangan | Analisis aliran tunai, untung rugi, hutang, kesan setiap transaksi dan ramalan. | Cash Forecast; P&L Explain; AR Chase Suggest; Expense Anomaly; Tax Calendar | Tidak memposting jurnal atau melepaskan bayaran tanpa kelulusan manusia. | GL; AR; AP; Treasury; POS; Payroll; BI | SEBAHAGIAN | ExecutiveAI; Finance |
| AI-003 ★ | AI CMO | Ejen Pemasaran | Prestasi kempen, cadangan kandungan, segmen pelanggan dan bajet iklan. | Campaign Review; Content Ideas; Segment Builder; Ad Spend Advice; Brand Voice | Tidak menerbitkan atau membelanjakan bajet iklan tanpa kelulusan. | MAP; CDP; SMM; CMS; Loyalty; BI | SEBAHAGIAN | ExecutiveAI; ContentStudio; Marketing |
| AI-004 ★ | AI COO | Ejen Operasi | Pantau operasi harian: stok kritikal, pesanan lewat, kekurangan staf, SLA. | Ops Brief; Stock Alerts; Late Orders; Staffing Gaps; SLA Breach | Mencadang & mencipta tugasan; tidak mengubah stok/jadual secara terus. | IMS; OMS; WFM; Helpdesk; OSM; SCM | SEBAHAGIAN | ExecutiveAI |
| AI-005 ★ | AI CSO | Ejen Jualan (Chief Sales) | Saluran jualan, ramalan, lead panas dan susulan. | Pipeline Review; Lead Scoring; Follow-up Drafts; Quote Assist; Forecast | Draf mesej/sebut harga sahaja; penghantaran keluar perlu kelulusan. | CRM; SFA; CPQ; WhatsApp; OMS | SEBAHAGIAN | SalesCopilot; Prospecting |
| AI-006 ★ | AI CHRO | Ejen Sumber Manusia | Kehadiran, cuti, kos gaji, giliran dan risiko pekerja berhenti. | HR Brief; Leave Clash; Overtime Alert; Hiring Assist; Policy Q&A | Data pekerja sensitif: akses ikut peranan, tiada keputusan disiplin automatik. | HRMS; T&A; Payroll; WFM; ATS | TIADA | — |
| AI-007 ★ | AI Boardroom | Mesyuarat Lembaga AI | Semua ejen C-suite berbincang satu keputusan dan bentang pilihan + risiko kepada pemilik. | Agenda; Multi-agent Debate; Decision Memo; Vote Log; Follow-up Tasks | Keputusan muktamad kekal pada manusia; minit disimpan sebagai audit. | AI-001..006; Task; DMS | IMPLEMENTED (ujian nipis) | Boardroom |
| AI-008 ★ | AI Copilot (Tanya AI Bijan) | Pembantu Perbualan | Soal jawab BM/EN atas data syarikat dari mana-mana skrin. | Chat; Data Q&A; Explain Screen; Quick Actions | Jawapan mesti merujuk data sebenar; tidak mereka angka. | Semua modul melalui pendaftar alat; Knowledge | IMPLEMENTED + diuji | AIAgents; PersonalAssistant |
| AI-009 ★ | Agent Tool Registry | Pendaftar Alat Ejen | Senarai alat yang boleh dipanggil ejen, dengan kebenaran, risiko dan pengesahan. | Tool Schema; Permission Map; Risk Level; Dry-run; Verify Step | Satu pendaftar untuk ejen dalaman & MCP awam; tiada alat stub "berjaya". | OAM; Approval; Audit; MCP | IMPLEMENTED + diuji | AIAgents/Tools (Fasa 1.1) |
| AI-010 ★ | Approval Inbox (Human-in-the-loop) | Peti Kelulusan | Tindakan ejen berisiko menunggu kelulusan, kemudian dilaksana & disahkan. | Queue; Approve/Reject; Execute; Verify; Audit | Kelulusan ikut had RM/% peranan (OAM). | AI-009; CORE-004; OAM; Audit | IMPLEMENTED + diuji | AIAgents (/app/kelulusan) |
| AI-011 ★ | AI Forecasting | Ramalan AI | Ramalan jualan, permintaan stok, aliran tunai dan kehadiran. | Sales Forecast; Demand; Cash; Seasonality; Accuracy Tracking | Ramalan bukan transaksi; mesti tunjuk julat & ketepatan lalu. | POS; OMS; IMS; Treasury; BI | TIADA | — |
| AI-012 ★ | Document AI | Pemahaman Dokumen | Baca resit, invois pembekal, borang dan tukar kepada draf rekod. | OCR; Extraction; Classification; Draft Record; Confidence | Hasil = draf untuk disemak; tidak posting terus. | DMS; AP; Expense; Inventory | IMPLEMENTED (ujian nipis) | DocumentAI; Vision |
| AI-013 ★ | AI Evaluation & Guardrails | Penilaian & Pagar AI | Ujian kualiti jawapan ejen, had kos, sekatan topik dan log keputusan. | Eval Sets; Regression in CI; Cost Caps; Policy Filters; Incident Log | Bukan model AI; ia lapisan kawalan atas semua ejen. | AI Gateway; AI-009; Audit; DevSecOps | TIADA | — |
| AI-014 ★ | Setup / Onboarding Agent | Ejen Penyediaan | Tampal menu/senarai produk → kedai siap dikonfigurasi dan boleh terus jual. | Paste Menu; Product Create; Tax Setup; Printer; First Sale | Mencipta rekod draf yang disahkan pengguna. | POS; IMS; PIM; Migration Hub | IMPLEMENTED + diuji | AIAgents (/app/mula) |
| CORE-006 | AI Agent Management | Agent Orchestration | Lifecycle agent, tools, memory dan policy. | Agent Registry; Tools; Plans; Evaluation; Execution; Audit | Agent ialah service identity, bukan user berprivilege tinggi. | OAM; AI Gateway; BPM; All modules | IMPLEMENTED + diuji | AIAgents; AgentStudio; ADE; TeamAI |
| CORE-007 | Knowledge Management | Knowledge Management System | Pengetahuan dan SOP organisasi. | Knowledge Base; Search; Versioning; Review; Access | Dokumen asal mungkin di DMS; knowledge index menghormati ACL. | DMS; LMS; AI Agents | IMPLEMENTED (ujian nipis) | BrainHub |

### Layer 2 — Business Systems

| ID | Kod | Nama | Fungsi | Submodul | Sempadan | Integrasi | Status kod | Modul |
|---|---|---|---|---|---|---|---|---|
| BOS-001 | CRM | Customer Relationship Management | Pelanggan, akaun, interaksi dan peluang jualan. | Accounts; Contacts; Leads; Activities; Opportunities; Segments | Tidak menjadi ledger kewangan atau master inventory. | ERP; CPQ; Helpdesk; CDP; OMS; BI | IMPLEMENTED + diuji | CRM |
| BOS-002 | ERP | Enterprise Resource Planning | Penyelarasan transaksi dan sumber merentas fungsi syarikat. | General Ledger integration; Procurement; Inventory; Sales; Production; Projects | Bukan satu modul tanpa sempadan; ERP ialah payung integrasi domain. | Finance; HRMS; SCM; IMS; MES; BI | TIADA | – |
| BOS-003 | BPM | Business Process Management | Pemodelan, pelaksanaan dan pemantauan proses perniagaan. | Process Designer; Workflow; SLA; Escalation; Process Analytics | Mengurus aliran proses, bukan pemilik rekod domain transaksi. | OAM; ERP; CRM; HRMS; Approval; Automation | IMPLEMENTED + diuji | WorkflowStudio |
| BOS-004 | BMS | Business Management System | Paparan pengurusan menyeluruh dan konfigurasi operasi bisnes. | Business Profile; KPI; Goals; Policies; Management Dashboard | BMS ialah lapisan pengurusan; bukan pengganti ERP/CRM/HRMS. | ERP; BI; BPM; EPM; OAM | TIADA | – |
| BOS-005 | OMS | Order Management System | Kitar hayat pesanan merentas saluran. | Order Capture; Validation; Allocation; Fulfilment; Returns; Order Status | Pemilik lifecycle order; bukan pemilik stok atau ledger pembayaran. | POS; E-commerce; IMS; WMS; Finance; Logistics | IMPLEMENTED + diuji | Orders |
| BOS-006 | POS | Point of Sale | Transaksi jualan di kaunter atau titik jualan. | Checkout; Discounts; Taxes; Cash Drawer; Shift; Refund; Receipt | Channel transaksi; stok/ledger kekal pada domain pemilik masing-masing. | OMS; IMS; Payment; Finance; Loyalty | IMPLEMENTED + diuji | POS |
| BOS-007 | OSM | Operations Management System | Perancangan dan kawalan operasi harian. | Operations Board; Work Queue; SOP; Capacity; Exception Handling | Tidak menggantikan BPM atau task/project management. | BPM; WFM; PMS-PRJ; Inventory; BI | IMPLEMENTED + diuji | ServiceOS |
| BOS-008 | PMS-PRJ | Project Management System | Skop, jadual, kos dan deliverable projek. | Projects; Tasks; Milestones; Gantt; Risks; Timesheets; Budget | Mengurus projek, bukan portfolio hartanah atau hotel. | Finance; HRMS; Procurement; DMS; BI | IMPLEMENTED (ujian nipis) | Projects |
| BOS-009 | WFM | Workforce Management | Penjadualan tenaga kerja mengikut permintaan operasi. | Rostering; Shift; Skills; Availability; Labour Forecast; Attendance interface | Mengurus kapasiti dan jadual; HRMS kekal rekod pekerja. | HRMS; T&A; Payroll; POS; OSM | TIADA | – |
| BOS-010 | EPM | Enterprise Performance Management | Perancangan, sasaran dan prestasi organisasi. | Budgeting; Forecasting; Scorecards; Consolidation; Strategy Maps | Analisis prestasi dan perancangan; bukan transaksi GL. | Finance; BI; ERP; HRMS | IMPLEMENTED + diuji | BusinessIntel |
| BOS-011 | BPA | Business Process Automation | Automasi tugasan berasaskan trigger dan rules. | Triggers; Rules; Queues; Human Tasks; Retry; Monitoring | Bukan enjin AI reasoning atau pemilik rekod domain. | BPM; iPaaS; API; RPA; AI Agents | IMPLEMENTED + diuji | FlowAutomation |
| BOS-012 | Task & Work Management | Work Management System | Tugasan individu dan pasukan. | Tasks; Checklists; Assignment; Due Dates; Comments; Workload | Tidak mengurus projek kompleks sepenuhnya atau workflow transaksi. | PMS-PRJ; OSM; HRMS; Notifications | IMPLEMENTED + diuji | Projects; PersonalAssistant |
| BOS-013 | Enterprise Content Portal | Corporate Portal | Portal dalaman untuk maklumat dan perkhidmatan pekerja. | Announcements; Knowledge; Forms; Links; Service Catalogue | Portal pengalaman pengguna; bukan repository dokumen utama. | DMS; HRMS; ITSM; OAM | IMPLEMENTED (ujian nipis) | VirtualOffice |
| BOS-014 | Business Continuity Management | BCM System | Kesinambungan operasi dan pemulihan gangguan. | BIA; Continuity Plans; Recovery Exercises; Crisis Contacts | Mengurus kesiapsiagaan; bukan backup engine. | Risk; ITSM; Security; Audit | TIADA | – |
| BOS-015 | Strategy Execution Management | Strategy Management | Penterjemahan strategi kepada sasaran dan inisiatif. | Objectives; OKR; Initiatives; KPI; Reviews | Tidak menjadi sumber data KPI; membaca daripada domain lain. | EPM; BI; PMS-PRJ | TIADA | – |
| BOS-016 ★ | Multi-Branch / Outlet Management | Pengurusan Cawangan | Cawangan, outlet, gudang outlet, laporan & akses ikut cawangan. | Branches; Branch Stock; Branch P&L; Transfer; Branch Users | Cawangan ialah skop OAM, bukan tenant berasingan. | OAM; POS; IMS; GL | SEBAHAGIAN | Fundamental (org); Fasa 1.6 |
| BOS-017 ★ | Franchise Management | Pengurusan Francais | Francaisor urus francaisi: royalti, standard, bekalan pusat. | Franchisee Register; Royalty; Central Supply; Audit; Brand Standards | Francaisi = tenant/organisasi berasingan dengan perkongsian data terhad. | OAM; Billing; SCM; QMS | TIADA | — |
| BOS-018 ★ | Company Secretarial | Kesetiausahaan Syarikat | Rekod SSM, pengarah, pemegang saham, mesyuarat & resolusi. | Statutory Registers; Resolutions; Filing Calendar; Share Register | Penghantaran rasmi kekal oleh setiausaha berlesen. | DMS; GRC; SignDocs | TIADA | — |
| BOS-019 ★ | Visitor & Front Desk | Pengurusan Pelawat | Daftar pelawat, pas, dan bilik/meja. | Check-in; Badges; Host Notify; Room/Desk Booking | Data pelawat ikut PDPA. | Booking; VirtualOffice; Security | SEBAHAGIAN | VirtualOffice |
| BOS-020 ★ | Queue Management | Pengurusan Giliran | Nombor giliran kaunter, klinik, servis. | Ticket; Display; SMS/WA Call; Wait Analytics | Bukan sistem tempahan. | Booking; Clinic; WhatsApp | TIADA | — |
| FIN-001 | Accounting System | Accounting Management System | Perakaunan dan rekod jurnal kewangan. | Chart of Accounts; Journals; Trial Balance; Period Close; Statements | Subdomain accounting; FMS boleh menjadi payung UI bukan duplicate ledger. | AP; AR; GL; Tax; Billing; ERP | IMPLEMENTED + diuji | Finance |
| FIN-002 | FMS | Financial Management System | Kawalan kewangan, bajet, cash flow dan pelaporan. | Financial Dashboard; Budget; Cash Flow; Controls; Forecast | Payung pengurusan kewangan; ledger dan subledger kekal domain khusus. | Accounting; AP; AR; Treasury; EPM | IMPLEMENTED + diuji | Finance; BusinessIntel |
| FIN-003 | AP | Accounts Payable | Hutang pembekal dan pembayaran keluar. | Supplier Bills; Matching; Due Dates; Payment Proposal; Aging | Tidak mengurus procurement sourcing atau bank rails. | Procurement; Accounting; Payment; Treasury | IMPLEMENTED + diuji | Finance |
| FIN-004 | AR | Accounts Receivable | Hutang pelanggan dan kutipan. | Customer Invoices; Aging; Collections; Credit Notes; Dunning | Tidak mengesahkan settlement bank secara bersendirian. | Billing; CRM; Payment; Accounting | IMPLEMENTED + diuji | Finance; Invoicing |
| FIN-005 | FP&A | Financial Planning & Analysis | Perancangan, bajet dan analisis kewangan. | Budget; Forecast; Scenario; Variance; Management Reports | Bukan ledger transaksi atau tax filing engine. | FMS; EPM; BI; ERP | TIADA | – |
| FIN-006 | Treasury Management | Treasury System | Kecairan, bank position dan cash forecasting. | Cash Position; Bank Accounts; Cash Forecast; Bank Reconciliation | Tidak menyimpan credentials bank tanpa vault dan tidak menggantikan bank. | Accounting; Payment; Banking APIs | TIADA | – |
| FIN-007 | Billing System | Billing Management | Pengiraan caj dan penerbitan bil/invois. | Price Plans; Usage Rating; Invoice Generation; Credit Notes | Bil bukan bukti pembayaran diterima. | OMS; Subscription; AR; Payment | IMPLEMENTED + diuji | Billing; Invoicing |
| FIN-008 | Payment Management | Payment Orchestration | Inisiasi, status dan rekonsiliasi pembayaran. | Payment Intent; Provider Routing; Refund; Webhooks; Reconciliation | Tidak menyimpan dana atau menjadi bank/e-money issuer tanpa lesen. | POS; Billing; AR; Gateway; Treasury | IMPLEMENTED + diuji | Billing |
| FIN-009 | Expense Management | Expense System | Tuntutan dan kawalan perbelanjaan. | Claims; Receipts; Policy Checks; Approvals; Reimbursement | Bukan AP supplier invoice atau payroll. | HRMS; AP; Accounting; OAM | IMPLEMENTED + diuji | Expense |
| FIN-010 | Tax Management | Tax System | Pengiraan, pelaporan dan pematuhan cukai. | Tax Codes; SST/VAT; Withholding; Tax Returns; E-Invoice mapping | Peraturan cukai mesti dikonfigurasi mengikut bidang kuasa dan tarikh. | Accounting; Billing; ERP; E-invoice | TIADA | – |
| FIN-011 | General Ledger | GL | Buku besar dan akaun kawalan. | Journal Posting; Period; Trial Balance; Consolidation | Sumber rasmi baki perakaunan; modul lain tidak menulis terus tanpa kontrak. | AP; AR; Assets; Payroll; POS | IMPLEMENTED + diuji | Finance |
| FIN-012 | Fixed Asset Accounting | Fixed Asset System | Aset tetap dan susut nilai. | Register; Depreciation; Disposal; Revaluation; Asset Ledger | Bezakan daftar aset operasi daripada nilai buku perakaunan. | EAM; Accounting; Procurement | IMPLEMENTED + diuji | Finance; Assets |
| FIN-013 | Revenue Management | Revenue Recognition | Pengiktirafan hasil mengikut kontrak dan polisi. | Contracts; Performance Obligations; Deferred Revenue; Schedules | Tidak sama dengan invoicing atau payment collection. | CRM; CPQ; Billing; GL | TIADA | – |
| FIN-014 | Credit & Collections | Credit Management | Had kredit, risiko pelanggan dan kutipan. | Credit Limits; Dunning; Disputes; Promise-to-Pay | Keputusan kredit memerlukan polisi dan data yang sah. | AR; CRM; Risk; Payment | SEBAHAGIAN | Invoicing; Reminder |
| FIN-015 | Financial Consolidation | Consolidation System | Penyatuan kewangan kumpulan syarikat. | Intercompany; Eliminations; FX; Consolidated Statements | Tidak menggantikan GL setiap entiti. | GL; ERP; Treasury; EPM | TIADA | – |
| FIN-016 | E-Invoice Management | E-Invoicing | Penyediaan, penghantaran dan status invois elektronik. | Document Mapping; Validation; Submission; Rejection; Status | Adapter pematuhan; keperluan Malaysia berubah mengikut peraturan rasmi. | Billing; Tax; AR; API | RANGKA | Invoicing |
| FIN-017 | Bank Reconciliation | Bank Reconciliation System | Padanan transaksi bank dengan rekod dalaman. | Statement Import; Matching Rules; Exceptions; Reconciliation | Tidak menginisiasi pembayaran atau menyimpan wang. | Treasury; Payment; GL | TIADA | – |
| FIN-018 | Cash Management | Cash Management System | Tunai di kaunter, petty cash dan cash forecast ringkas. | Cashbook; Float; Cash Count; Variance | Tidak menggantikan treasury korporat. | POS; Accounting; Treasury | TIADA | – |
| FIN-019 ★ | Commission Management | Komisen Staf Jualan | Kira komisen staf/ejen jualan ikut peraturan. | Plans; Rules; Statements; Disputes; Payroll Handoff | Dibayar melalui Payroll/AP, bukan di sini. | POS; CRM; Payroll | SEBAHAGIAN | Affiliate |
| FIN-020 ★ | Intercompany & Multi-Entity | Antara Syarikat | Transaksi antara syarikat dalam kumpulan. | IC Invoices; Due To/From; Eliminations | Setiap entiti ada GL sendiri. | GL; FIN-015; OAM | TIADA | — |
| FIN-021 ★ | Zakat & Islamic Finance Compliance | Zakat & Patuh Syariah | Pengiraan zakat perniagaan & tanda produk/akaun patuh syariah. | Zakat Calc; Shariah Flags; Reports | Pengiraan perlu disahkan pihak berautoriti zakat negeri. | GL; Tax; BI | TIADA | — |
| GRC-001 | GRC | Governance Risk & Compliance | Tadbir urus, risiko dan pematuhan. | Controls; Risk Register; Obligations; Evidence; Reviews | Payung governance; modul khusus menyimpan transaksi sumber. | ERM; Audit; Policy; QMS | TIADA | – |
| GRC-002 | QMS | Quality Management System | Kawalan kualiti produk/proses. | Nonconformance; CAPA; Inspection; Documents; Supplier Quality | Quality domain; tidak menggantikan MES execution. | MES; Procurement; DMS; Audit | TIADA | – |
| GRC-003 | EHS | Environment Health & Safety | Kesihatan, keselamatan dan persekitaran kerja. | Incidents; Risk Assessments; Permits; Training; Inspections | Operasi EHS; EMS mengurus aspek alam sekitar lebih khusus. | HRMS; QMS; CMMS | TIADA | – |
| GRC-004 | EMS | Environmental Management System | Pengurusan impak alam sekitar. | Aspects; Emissions; Waste; Compliance; Objectives | Bukan EHS penuh atau energy grid control. | EHS; Facilities; BI | TIADA | – |
| GRC-005 | CLM | Contract Lifecycle Management | Kitar hayat kontrak. | Draft; Review; Clause Library; Approval; Signature; Renewal | Kontrak bukan invois atau rekod order. | CRM; Procurement; DMS; Finance | IMPLEMENTED (ujian nipis) | SignDocs |
| GRC-006 | ERM | Enterprise Risk Management | Risiko perusahaan dan mitigasi. | Risk Register; Appetite; Controls; KRIs; Treatment | Risiko merentas organisasi; GRC menyediakan umbrella controls. | GRC; BI; Audit | TIADA | – |
| GRC-007 | Audit Management | Audit System | Perancangan dan pelaksanaan audit. | Audit Plan; Workpapers; Findings; Actions; Evidence | Audit trail sistem bukan pengganti audit engagement. | GRC; DMS; QMS | IMPLEMENTED (ujian nipis) | app/Services/AuditLogger |
| GRC-008 | Policy Management | Policy System | Polisi, acknowledgement dan versioning. | Policy Library; Approvals; Attestation; Review Cycle | Tidak menggantikan workflow enforcement dalam aplikasi. | DMS; HRMS; GRC | TIADA | – |
| GRC-009 | Privacy Management | Privacy & Data Protection | Pengurusan hak subjek data dan privacy controls. | Data Inventory; Consent; DSAR; Retention; Breach Register | Keperluan undang-undang perlu disemak pakar; bukan jaminan compliance automatik. | IAM; CDP; DMS; Security | TIADA | – |
| GRC-010 | Regulatory Compliance | Compliance Management | Kewajipan kawal selia mengikut industri/negara. | Obligations; Evidence; Calendar; Regulatory Change | Rules perlu dikemas kini dan disahkan; tidak auto-certify. | GRC; Tax; Healthcare; Finance | TIADA | – |
| GRC-011 | Fraud & AML Monitoring | Financial Crime Monitoring | Pengesanan transaksi mencurigakan mengikut skop/lesen. | Rules; Alerts; Case Management; Screening | Tidak menjadikan platform entiti berlesen; peranan ditentukan undang-undang. | Payment; Finance; KYC provider | TIADA | – |
| GRO-001 | SFA | Sales Force Automation | Aktiviti dan produktiviti pasukan jualan. | Territories; Visits; Pipeline; Forecast; Sales Tasks | CRM mengurus rekod pelanggan; SFA mengoptimumkan aktiviti jualan. | CRM; CPQ; Maps; BI | IMPLEMENTED + diuji | SalesCopilot; Prospecting |
| GRO-002 | MAP | Marketing Automation Platform | Automasi kempen dan nurture. | Campaigns; Journeys; Segments; Triggers; Attribution | Tidak menggantikan CDP atau platform penerbitan sosial sepenuhnya. | CRM; CDP; Email; WhatsApp; Analytics | IMPLEMENTED + diuji | Marketing; Blast |
| GRO-003 | CDP | Customer Data Platform | Penyatuan profil pelanggan daripada pelbagai sumber. | Identity Resolution; Events; Segments; Consent; Profile | Bukan CRM; CDP menyatukan data, CRM mengurus hubungan dan aktiviti. | CRM; POS; E-commerce; Support; BI | TIADA | – |
| GRO-004 | DMP | Data Management Platform | Pengurusan audience/data pemasaran bersegmen. | Audience Taxonomy; Activation; Data Partnerships | Tidak menjadi master data pelanggan dalaman atau consent ledger. | CDP; MAP; Ad Platforms | TIADA | – |
| GRO-005 | CMS | Content Management System | Pengurusan halaman dan kandungan digital. | Pages; Posts; Templates; Versioning; Publishing | Tidak sama dengan DAM yang mengurus aset media asal. | SEO; DAM; Website; Omnichannel | IMPLEMENTED (ujian nipis) | Site |
| GRO-006 | DAM | Digital Asset Management | Penyimpanan, metadata dan lifecycle aset digital. | Asset Library; Metadata; Rights; Renditions; Approval | Bukan CMS; CMS menggunakan aset daripada DAM. | CMS; Creative Studio; Social Publishing | IMPLEMENTED (ujian nipis) | MediaStudio |
| GRO-007 | Loyalty Management | Loyalty Management System | Mata ganjaran, tier dan redemption pelanggan. | Points; Tiers; Rewards; Vouchers; Liability | Jangan kelirukan dengan Learning Management System. | POS; CRM; OMS; Payment | IMPLEMENTED (ujian nipis) | Loyalty |
| GRO-008 | PRM | Partner Relationship Management | Pengurusan rakan niaga dan channel partner. | Partner Portal; Deal Registration; MDF; Enablement | Bukan affiliate tracking pengguna; fokus partner B2B. | CRM; CPQ; Affiliate | SEBAHAGIAN | Billing |
| GRO-009 | Affiliate Management | Affiliate System | Pautan rujukan, atribusi dan komisen affiliate. | Tracking; Attribution; Commission; Payout Approval; Fraud | Komisen dikira di sini; payout disahkan Finance/Payment. | CRM; OMS; Payment; BI | IMPLEMENTED (ujian nipis) | Affiliate |
| GRO-010 | CPQ | Configure Price Quote | Konfigurasi produk, harga dan sebut harga. | Product Rules; Pricing; Discounts; Quote; Approval | Tidak menjadi kontrak atau order sehingga diterima dan ditukar. | CRM; ERP; Billing; CLM; OMS | IMPLEMENTED + diuji | Invoicing; CRM |
| GRO-011 | Helpdesk | Customer Support System | Tiket, SLA dan penyelesaian isu pelanggan. | Tickets; Queues; SLA; Knowledge; Escalation | Bukan contact centre telephony atau CRM account master. | CRM; Contact Centre; Knowledge | IMPLEMENTED (ujian nipis) | Helpdesk |
| GRO-012 | Contact Centre | Contact Centre System | Pengendalian panggilan dan interaksi omnichannel. | Voice; IVR; Routing; Recording; Agent Desktop | Bukan system of record untuk customer profile. | CRM; Helpdesk; WhatsApp; Analytics | IMPLEMENTED + diuji | Omnichannel; LiveSupport |
| GRO-013 | Customer Success Management | CSM | Onboarding dan kesihatan pelanggan selepas jualan. | Health Score; Adoption; Renewals; QBR; Churn Risk | Tidak menggantikan CRM sales pipeline. | CRM; Subscription; Support; BI | TIADA | – |
| GRO-014 | Social Media Management | SMM | Perancangan, penerbitan dan pemantauan media sosial. | Calendar; Publishing; Inbox; Comments; Analytics | API platform menentukan fungsi yang boleh dilaksanakan. | CMS; DAM; MAP; CRM | IMPLEMENTED + diuji | ContentStudio |
| GRO-015 | SEO & Metadata Management | SEO System | Metadata, indexability dan prestasi carian organik. | Meta; Schema; Sitemap; Redirects; Search Console | Tidak menjamin ranking dan tidak mengurus iklan berbayar. | CMS; Website; Analytics | IMPLEMENTED + diuji | Seo |
| GRO-016 | Reputation Management | Online Reputation System | Ulasan, maklum balas dan reputasi jenama. | Review Inbox; Response Approval; Sentiment; Alerts | Tidak boleh memalsukan review atau memanipulasi platform. | CRM; Helpdesk; Social | TIADA | – |
| GRO-017 | Proposal Management | Proposal System | Dokumen cadangan komersial. | Templates; Pricing; Versioning; Approval; E-sign handoff | CPQ mengira konfigurasi/harga; proposal membentangkan tawaran. | CRM; CPQ; CLM; DMS | IMPLEMENTED + diuji | SignDocs; CRM |
| GRO-018 | Customer Experience Management | CXM | Pengukuran pengalaman pelanggan merentas touchpoint. | Surveys; Journey; Feedback; NPS; Experience Analytics | Bukan pengganti CRM/CDP; mengukur pengalaman. | CDP; CRM; Support; BI | TIADA | – |
| GRO-019 | Event Marketing Automation | Event Marketing | Promosi dan komunikasi acara. | Registration Campaigns; Reminders; Segmentation; Attribution | Pengurusan acara sebenar kekal pada Event Management. | Event; CRM; MAP; Payment | TIADA | – |
| GRO-020 | Conversational Commerce | Conversational Sales | Jualan melalui WhatsApp/chat dan saluran perbualan. | Catalog; Cart; Agent Handoff; Payment Link; Order Capture | Mesej bukan rekod order sehingga disahkan OMS. | WhatsApp; CRM; OMS; Payment | IMPLEMENTED + diuji | Omnichannel |
| GRO-021 ★ | Gift Card & Voucher | Kad Hadiah & Baucar | Jual & tebus baucar dan kad hadiah dengan liabiliti. | Issue; Redeem; Balance; Expiry; Liability | Liabiliti direkod di GL. | POS; Loyalty; GL | IMPLEMENTED (ujian nipis) | POS (tender voucher); Loyalty |
| GRO-022 ★ | Link-in-bio & Mini Site | Laman Ringkas | Laman pautan, katalog ringkas dan borang lead. | Builder; Links; Catalog; Lead Form; Analytics | Bukan CMS penuh. | CMS; CRM; Site | IMPLEMENTED (ujian nipis) | Link; Site |
| GRO-023 ★ | Survey & Forms | Borang & Tinjauan | Borang awam, tinjauan pelanggan/pekerja. | Builder; Logic; Responses; Export | Data dikongsi ikut persetujuan. | CRM; CXM; HR | TIADA | — |
| GRO-024 ★ | Community Management | Pengurusan Komuniti | Komuniti pelanggan/ahli, forum dan acara. | Members; Posts; Groups; Moderation | Kandungan tertakluk moderasi. | Membership; CRM | IMPLEMENTED (ujian nipis) | Community |
| HR-001 | HRMS | Human Resource Management System | Pengurusan proses sumber manusia. | Employee Records; Leave; Benefits; HR Cases; Org Data | Payung HR; submodul khusus boleh berkongsi master employee. | Payroll; ATS; T&A; OAM; Finance | IMPLEMENTED (ujian nipis) | HRMS |
| HR-002 | HRIS | Human Resource Information System | Rekod induk maklumat pekerja. | Employee Profile; Employment; Position; Documents | HRIS ialah data pekerja; HRMS boleh menjadi payung fungsi HR. | Payroll; WFM; LMS-LRN; Access | IMPLEMENTED (ujian nipis) | HRMS |
| HR-003 | HCM | Human Capital Management | Strategi tenaga kerja dan modal insan. | Talent; Workforce Planning; Succession; Compensation | Payung strategik, bukan semestinya sistem transaksi berasingan. | HRMS; ATS; Performance; EPM | TIADA | – |
| HR-004 | ATS | Applicant Tracking System | Pengambilan calon pekerja. | Vacancies; Applications; Screening; Interviews; Offers | Lifecycle calon sehingga hire; selepas hire HRIS mengambil alih. | HRMS; Calendar; Email | TIADA | – |
| HR-005 | LMS-LRN | Learning Management System | Latihan, kursus dan kompetensi pekerja/pelajar. | Courses; Enrolment; Assessments; Certificates; Learning Paths | Bezakan kod daripada Loyalty Management System. | HRMS; Skills; Compliance | TIADA | – |
| HR-006 | Payroll | Payroll Management | Pengiraan gaji dan potongan. | Pay Runs; Allowances; Deductions; Statutory; Payslips | Payroll mengira; GL merekod jurnal; bank membayar. | HRMS; T&A; Tax; GL; Payment | IMPLEMENTED + diuji | Payroll |
| HR-007 | T&A | Time & Attendance | Masa kerja dan kehadiran. | Clock In/Out; Timesheets; Leave interface; Exceptions | Tidak menentukan polisi HR keseluruhan atau mengira payroll sepenuhnya. | WFM; Payroll; HRMS | IMPLEMENTED (ujian nipis) | HRMS |
| HR-008 | ESS | Employee Self-Service | Portal urusan kendiri pekerja. | Profile Updates; Leave Requests; Claims; Payslips | UI/portal pekerja; rekod rasmi berada dalam HRMS/Payroll. | HRMS; Expense; Payroll | IMPLEMENTED (ujian nipis) | HRMS |
| HR-009 | Performance Management | Performance System | Penilaian dan sasaran pekerja. | Goals; Reviews; 360 Feedback; PIP; Calibration | Bukan EPM korporat walaupun berkongsi KPI. | HRMS; HCM; LMS-LRN | TIADA | – |
| HR-010 | Compensation Management | Compensation System | Gaji asas, bonus dan ganjaran. | Salary Bands; Bonus; Incentives; Equity | Tidak memproses payroll payment. | HRMS; Payroll; Finance | TIADA | – |
| HR-011 | Skills & Competency Management | Skills Management | Peta kemahiran dan jurang kompetensi. | Skill Taxonomy; Assessments; Certifications; Matrix | Bukan LMS; LMS menyampaikan latihan. | HRMS; LMS-LRN; WFM | TIADA | – |
| HR-012 | Employee Engagement | Engagement System | Penglibatan, survey dan maklum balas pekerja. | Pulse Surveys; Recognition; Sentiment; Action Plans | Data survey sensitif; akses agregat dan privasi diperlukan. | HRMS; BI; Communications | TIADA | – |
| HR-013 ★ | Claims & Benefits Administration | Tuntutan & Faedah | Insurans, panel klinik, faedah pekerja. | Benefit Plans; Panel Clinic; Claims; Eligibility | Tuntutan perbelanjaan kekal di FIN-009. | HRMS; Payroll; Expense | TIADA | — |
| HR-014 ★ | Employee Onboarding/Offboarding | Masuk & Keluar Pekerja | Senarai semak masuk/keluar, akses, aset, dokumen. | Checklists; Access Provision; Asset Return; Exit Interview | Akses dicabut melalui IAM. | HRMS; IAM; Assets | TIADA | — |
| SUP-001 | IMS | Inventory Management System | Kuantiti, kos dan pergerakan stok. | Item Master; Stock Ledger; Transfers; Counts; Lots; Serials | Sumber kebenaran stok jika ditetapkan; WMS mengurus operasi gudang. | OMS; POS; WMS; Procurement; Finance | IMPLEMENTED (ujian nipis) | Inventory |
| SUP-002 | WMS | Warehouse Management System | Operasi gudang dan lokasi simpanan. | Receiving; Putaway; Picking; Packing; Cycle Count; Wave | WMS mengurus execution gudang; IMS/ledger menentukan stok kewangan. | IMS; OMS; TMS; ERP | IMPLEMENTED (ujian nipis) | Logistics |
| SUP-003 | SCM | Supply Chain Management | Perancangan dan penyelarasan rantaian bekalan. | Demand; Supply; Planning; Supplier; Network Visibility | Payung rantaian bekalan, bukan satu ledger transaksi. | MRP; SRM; Procurement; WMS; TMS | IMPLEMENTED + diuji | SCM |
| SUP-004 | MRP | Material Requirements Planning | Keperluan bahan untuk pengeluaran. | BOM Explosion; Netting; Planned Orders; Shortage | Merancang bahan; tidak menjalankan shop-floor execution. | MES; IMS; Procurement; ERP | SEBAHAGIAN | Manufacturing |
| SUP-005 | SRM | Supplier Relationship Management | Profil, prestasi dan hubungan pembekal. | Supplier Master; Scorecards; RFQ; Contracts; Risk | Hubungan/pemilihan pembekal; procurement melaksanakan pembelian. | Procurement; AP; SCM; CLM | IMPLEMENTED + diuji | SCM |
| SUP-006 | TMS-LOG | Transportation Management System | Perancangan dan pelaksanaan pengangkutan barang. | Load Planning; Carrier; Routing; Freight; Proof of Delivery | TMS logistik bukan pengurusan fleet aset dalaman. | WMS; OMS; Fleet; ERP | IMPLEMENTED (ujian nipis) | Logistics |
| SUP-007 | YMS | Yard Management System | Pergerakan lori dan trailer di kawasan yard. | Gate; Dock Scheduling; Yard Map; Check-in/out | Mengurus yard; bukan gudang atau pengangkutan keseluruhan. | WMS; TMS-LOG; RFID | TIADA | – |
| SUP-008 | Procurement | Procurement Management | Permintaan, tender, PO dan pembelian. | Requisition; RFQ; Bid; PO; Receiving; Supplier Award | Procurement tidak memiliki AP invoice payment ledger. | SRM; AP; IMS; CLM | IMPLEMENTED + diuji | SCM |
| SUP-009 | Fleet Management | Fleet System | Kenderaan, pemandu dan penyelenggaraan fleet. | Vehicles; Drivers; Fuel; Maintenance; Telematics | Fleet ialah aset operasi; TMS merancang penghantaran. | TMS-LOG; EAM; HRMS | IMPLEMENTED + diuji | Fleet |
| SUP-010 | Asset Management | Asset Management System | Daftar aset dan lifecycle aset organisasi. | Asset Register; Assignment; Warranty; Depreciation link | Bezakan aset fizikal operasi daripada fixed asset accounting. | EAM; Finance; Procurement | IMPLEMENTED (ujian nipis) | Assets |
| SUP-011 | Demand Planning | Demand Planning System | Ramalan permintaan dan replenishment. | Forecast; Seasonality; Promotions; Consensus Planning | Ramalan bukan pesanan pembelian atau stok sebenar. | OMS; POS; SCM; BI | TIADA | – |
| SUP-012 | Supplier Portal | Supplier Collaboration Portal | Kolaborasi pembekal dengan syarikat. | PO Acknowledgement; ASN; Invoice Submission; Quality Docs | Portal akses; rekod PO/AP kekal dalam domain pemilik. | Procurement; SRM; AP; WMS | TIADA | – |
| SUP-013 | Product Information Management | PIM | Maklumat produk merentas saluran. | Attributes; Variants; Taxonomy; Enrichment; Syndication | PIM kandungan produk; IMS kuantiti stok. | E-commerce; POS; CMS; IMS | TIADA | – |
| SUP-014 ★ | Purchase Requisition & Reorder | Pesanan Semula Automatik | Cadang & cipta PO bila stok di bawah paras. | Reorder Points; Suggestions; Draft PO; Approval | PO dihantar selepas kelulusan. | IMS; Procurement; AI-004 | TIADA | (Fasa 4.3) |
| SUP-015 ★ | Landed Cost & Import | Kos Pendaratan | Kos penghantaran, duti, insurans masuk ke kos stok. | Cost Allocation; Customs; Duties | Mempengaruhi COGS. | IMS; AP; COGS | TIADA | — |
| SUP-016 ★ | Costing / COGS | Kos Barang Dijual | Kos purata/FIFO, margin per produk & jualan. | Cost Methods; Margin; Recipe Cost; Variance | Penilaian stok di GL. | IMS; POS; GL | IMPLEMENTED + diuji | COGS |
| TEC-001 | BI | Business Intelligence | Analisis dan visualisasi prestasi. | Semantic Model; Dashboards; Reports; Alerts; Drilldown | BI membaca data; bukan pemilik transaksi. | DWH; ERP; CRM; Finance; all systems | IMPLEMENTED + diuji | BusinessIntel; Analytics |
| TEC-002 | DWH | Data Warehouse | Penyimpanan data analitik bersejarah. | Ingestion; Dimensional Models; ELT; Data Marts | Analitik, bukan operational database. | BI; ETL; Source Systems | TIADA | – |
| TEC-003 | MDM | Master Data Management | Governance master data merentas domain. | Golden Records; Matching; Stewardship; Hierarchies | Tidak bermaksud semua domain menyerahkan ownership; domain source tetap berkuasa. | ERP; CRM; PIM; CDP | TIADA | – |
| TEC-008 | ITSM | IT Service Management | Service desk dan lifecycle perkhidmatan IT. | Incidents; Problems; Changes; Requests; CMDB | IT service workflows; bukan customer helpdesk. | SIEM; CMDB; Asset; BPM | TIADA | – |
| TEC-010 | DMS | Document Management System | Dokumen, versi, metadata dan retention. | Repository; Versioning; OCR; Search; Retention; Access | Bukan DAM media asset library atau CMS publishing. | CLM; HRMS; Finance; QMS | IMPLEMENTED (ujian nipis) | DocumentAI; SignDocs |
| TEC-012 | Data Governance | Data Governance System | Polisi, pemilik, klasifikasi dan lineage data. | Catalog; Glossary; Owners; Classification; Lineage | Menetapkan governance; tidak mengambil alih database execution. | MDM; DWH; Security | TIADA | – |

### Layer 3 — Industry Solutions

| ID | Kod | Nama | Fungsi | Submodul | Sempadan | Integrasi | Status kod | Modul |
|---|---|---|---|---|---|---|---|---|
| CON-001 | Construction Management | Construction Project System | Pengurusan projek pembinaan. | Projects; Site Diary; Progress; Claims; Variations; Handover | Industry project execution; PMS umum boleh jadi shared core. | PMS-PRJ; Procurement; Finance; DMS | TIADA | – |
| CON-002 | BIM | Building Information Modeling | Model maklumat bangunan dan koordinasi reka bentuk. | Models; Clash Detection; Revisions; Quantities | Model digital; bukan schedule/cost system. | CAD; PLM; Construction | TIADA | – |
| CON-003 | Estimating & BOQ | Construction Estimating | Anggaran kos dan Bill of Quantities. | Takeoff; Rates; BOQ; Tender Estimate | Anggaran; procurement/finance rekod komitmen sebenar. | BIM; Procurement; Finance | TIADA | – |
| CON-004 | Site Management | Construction Site Management | Operasi harian tapak. | Site Diary; Labour; Equipment; Inspections; RFIs | Pelaksanaan tapak; bukan contract lifecycle. | Construction; EHS; WFM | TIADA | – |
| CON-005 | Project Controls | Project Controls System | Kos, masa dan kemajuan projek. | Baseline; Earned Value; Schedule; Cost Forecast | Kawalan projek; bukan general ledger. | PMS-PRJ; Finance; BI | TIADA | – |
| CON-006 | Facilities Management | Facilities Management System | Operasi dan penyelenggaraan bangunan. | Work Orders; Space; Utilities; Inspections | Bangunan operasi selepas siap; CMMS/EAM boleh shared. | BMS-BLD; CMMS; EAM | TIADA | – |
| CON-007 ★ | BMS-BLD | Building Management System | Kawalan & pemantauan peralatan bangunan (HVAC, lampu, tenaga, akses). | Sensors; HVAC; Energy Meters; Alarms; Access Control link | Integrasi baca/pantau; kawalan keselamatan fizikal kekal pada sistem BMS vendor. | CON-006; CMMS; EMS; IoT | TIADA | — |
| EVT-001 | Event Management | Event Operating System | Perancangan, vendor, venue dan hari acara. | Event Plan; Budget; Vendors; Run Sheet; Crew; Attendees | Event domain; marketing automation hanya promosi. | CRM; PMS-PRJ; Procurement; Payment | TIADA | – |
| EVT-002 | Venue Management | Venue System | Ketersediaan dan operasi venue. | Spaces; Calendars; Packages; Site Ops | Bukan hotel room PMS atau property rent ledger. | Event; Booking; Finance | TIADA | – |
| EVT-003 | Ticketing & Registration | Ticketing System | Pendaftaran dan tiket acara. | Ticket Types; QR; Check-in; Refund; Capacity | Bukan event project plan. | Event; Payment; CRM | TIADA | – |
| EVT-004 | Rental Management | Rental System | Tempahan dan lifecycle aset sewaan. | Availability; Contracts; Dispatch; Returns; Damage | Bukan asset accounting; mengurus transaksi sewaan. | OMS; Asset; Payment; DMS | TIADA | – |
| EVT-005 | Appointment Booking | Booking System | Slot, availability dan tempahan perkhidmatan. | Calendar; Staff Availability; Deposits; Reschedule | Tidak menjadi clinical record atau project management. | CRM; Payment; WFM | IMPLEMENTED (ujian nipis) | Booking |
| EVT-006 | Field Service Management | FSM | Kerja lapangan, dispatch dan service reports. | Work Orders; Technician Routing; Parts; Sign-off | Field execution; CMMS untuk penyelenggaraan aset dalaman. | CRM; Inventory; Maps; Billing | IMPLEMENTED + diuji | ServiceOS |
| EVT-007 | Property Management - Real Estate | Property Management System | Portfolio hartanah, sewaan dan penyewa. | Units; Tenants; Leases; Rent; Maintenance Requests | Bukan Hotel PMS atau Project PMS. | Accounting; CRM; Maintenance | TIADA | – |
| EVT-008 | Learning & Student Information | SIS | Pentadbiran institusi pendidikan. | Admissions; Student Records; Timetables; Grades | SIS rekod pelajar; LMS menyampaikan pembelajaran. | LMS-LRN; Finance; HRMS | TIADA | – |
| EVT-009 | Learning Management - Education | LMS Education | Pengurusan kandungan dan pembelajaran pendidikan. | Courses; Lessons; Assessments; Progress; Certificates | Bukan Loyalty Management atau HR employee LMS. | SIS; Content; Payment | TIADA | – |
| EVT-010 | Library Management | Library System | Katalog, pinjaman dan koleksi perpustakaan. | Catalog; Circulation; Reservations; Acquisitions | Bukan DMS enterprise. | SIS; Payment; Identity | TIADA | – |
| EVT-011 | Professional Services Automation | PSA | Operasi firma perkhidmatan profesional. | Engagements; Time; Resource; Billing; Margin | Projek dan resource profesional; bukan ERP umum. | CRM; PMS-PRJ; Finance; HRMS | TIADA | – |
| EVT-012 | Membership Management | Membership System | Keahlian organisasi/kelab dan renewal. | Member Registry; Dues; Benefits; Events | Keahlian pelanggan bukan user identity OAM. | CRM; Billing; Event | IMPLEMENTED (ujian nipis) | Loyalty |
| HLT-001 | HIS | Hospital Information System | Operasi hospital dan pentadbiran klinikal. | Patient Admin; Admissions; Billing; Departments; Bed Management | Hospital operations; EMR clinical record khusus. | EMR; LIS; RIS; Finance | TIADA | – |
| HLT-002 | EMR | Electronic Medical Records | Rekod klinikal pesakit. | Encounters; Notes; Diagnoses; Medication; Allergies | Rekod klinikal; akses ketat dan audit. | HIS; LIS; RIS; Pharmacy | TIADA | – |
| HLT-003 | EHR | Electronic Health Records | Rekod kesihatan longitudinal merentas penjagaan. | Longitudinal Record; Interoperability; Consent | Lebih luas daripada EMR satu fasiliti. | EMR; HIS; Labs; APIs | TIADA | – |
| HLT-004 | LIS | Laboratory Information System | Pengurusan ujian makmal. | Orders; Specimens; Results; QC; Lab Workflow | Keputusan makmal; bukan rekod klinikal penuh. | HIS; EMR | TIADA | – |
| HLT-005 | RIS | Radiology Information System | Aliran kerja radiologi. | Orders; Scheduling; Reporting; Modality Worklist | PACS menyimpan imej; RIS mengurus workflow. | HIS; EMR; PACS | TIADA | – |
| HLT-006 | PACS | Picture Archiving & Communication System | Penyimpanan dan paparan imej perubatan. | Imaging Archive; Viewer; DICOM Routing | Imej diagnostik; bukan RIS workflow. | RIS; EMR | TIADA | – |
| HLT-007 | Pharmacy Management | Pharmacy System | Preskripsi, dispensing dan inventori farmasi. | Medication Orders; Dispensing; Stock; Interactions | Bukan diagnosis atau rekod klinikal keseluruhan. | EMR; IMS; Payment | TIADA | – |
| HLT-008 | Practice Management | Clinic Practice Management | Operasi klinik dan appointment. | Appointments; Registration; Billing; Claims | Pentadbiran klinik; EMR menyimpan rekod klinikal. | EMR; CRM; Finance | TIADA | – |
| HLT-009 | Telehealth | Telemedicine Platform | Konsultasi jarak jauh. | Video Visit; Consent; Scheduling; Clinical Handoff | Komunikasi klinikal; rekod klinikal kekal EMR. | EMR; Appointment; Payment | TIADA | – |
| HLT-010 | RCM | Revenue Cycle Management | Kitar hasil fasiliti kesihatan. | Eligibility; Claims; Coding; Billing; Denials | Kewangan healthcare; bukan clinical record. | HIS; EMR; Finance | TIADA | – |
| HOS-001 | Restaurant Management | Restaurant Operations System | Operasi restoran hujung ke hujung. | Menu; Table; Kitchen; Recipe; Orders; Food Cost; Shift | Industry bundle; POS/IMS/OMS kekal domain teras. | POS; OMS; IMS; Procurement; Finance | IMPLEMENTED + diuji | POS |
| HOS-002 | KDS | Kitchen Display System | Paparan dan routing pesanan dapur. | Tickets; Stations; Prep Times; Bump Bar | Execution dapur; bukan order master. | POS; OMS; Restaurant | IMPLEMENTED + diuji | POS |
| HOS-003 | Recipe & Food Cost | Recipe Management | Resipi, BOM bahan dan kos menu. | Recipes; Yield; Portions; Allergens; Costing | Master recipe dan costing; stok sebenar IMS. | IMS; Procurement; POS | IMPLEMENTED (ujian nipis) | COGS; Manufacturing |
| HOS-004 | Hotel PMS | Property Management System - Hotel | Operasi penginapan hotel. | Reservations; Rooms; Check-in/out; Housekeeping; Folio | Hotel PMS bukan Project PMS atau Property Real Estate PMS. | Booking; Payment; Housekeeping; Finance | TIADA | – |
| HOS-005 | Channel Manager | Hotel Channel Management | Penyelarasan inventori bilik dan kadar saluran. | Rate Plans; Availability; OTA Sync; Restrictions | Tidak menjadi hotel reservation/folio system. | Hotel PMS; Booking; OTA | TIADA | – |
| HOS-006 | Revenue Management Hotel | Hotel Revenue Management | Harga dan hasil bilik hotel. | Demand Forecast; Rate Optimization; Yield | Cadangan harga; hotel PMS sumber tempahan sebenar. | Hotel PMS; BI; Channel Manager | TIADA | – |
| HOS-007 | Housekeeping Management | Housekeeping System | Jadual dan status pembersihan bilik. | Room Status; Tasks; Inspections; Linen | Operasi housekeeping; bukan room inventory master. | Hotel PMS; WFM | TIADA | – |
| HOS-008 | Food Safety & HACCP | Food Safety Management | Rekod keselamatan makanan. | Temperature Logs; Allergens; Hygiene; Traceability | Kawalan keselamatan makanan; bukan QMS korporat penuh. | QMS; Restaurant; IMS | TIADA | – |
| LOG-001 | Transport Management | Transport Operations System | Pengurusan operasi pengangkutan penumpang/barang mengikut domain. | Routes; Trips; Dispatch; Driver; Schedule | Bezakan daripada TMS-LOG freight planning dan Fleet. | Fleet; Booking; WFM; GPS | IMPLEMENTED + diuji | Fleet |
| LOG-002 | Fleet Telematics | Telematics System | Lokasi dan data kenderaan. | GPS; Sensors; Fuel; Driver Behaviour | Data telematik; bukan financial asset register. | Fleet; TMS-LOG; BI | TIADA | – |
| LOG-003 | Last Mile Delivery | Last Mile System | Dispatch dan bukti penghantaran akhir. | Dispatch; Routing; POD; Customer ETA | Pelaksanaan penghantaran; OMS sumber order. | OMS; TMS-LOG; Maps | IMPLEMENTED + diuji | Cod; Logistics |
| LOG-004 | Freight Forwarding | Freight Management | Operasi forwarding dan dokumentasi kargo. | Shipments; Customs Docs; Rates; Milestones | Bukan customs authority atau carrier system. | TMS-LOG; DMS; Finance | TIADA | – |
| LOG-005 | Passenger Booking & Dispatch | Passenger Transport System | Tempahan dan dispatch penumpang. | Reservations; Seats; Routes; Dispatch; Tickets | Bukan freight TMS. | Booking; Payment; Fleet | TIADA | – |
| MFG-001 | MES | Manufacturing Execution System | Pelaksanaan dan pemantauan pengeluaran di shop floor. | Production Orders; Work Orders; Scheduling; WIP; OEE; Batch/Lot; Quality; Downtime | MES execution; tidak menggantikan PLC atau machine safety controls. | ERP; MRP; WMS; QMS; CMMS; PLM; SCADA | RANGKA | Manufacturing |
| MFG-002 | CMMS | Computerized Maintenance Management System | Penyelenggaraan aset dan mesin. | Work Orders; PM Schedules; Spare Parts; Downtime; Technician | Penyelenggaraan; EAM mengurus lifecycle aset enterprise. | MES; EAM; IMS; IoT | IMPLEMENTED (ujian nipis) | Assets |
| MFG-003 | EAM | Enterprise Asset Management | Lifecycle aset fizikal perusahaan. | Asset Hierarchy; Lifecycle; Maintenance Strategy; Reliability | Lebih luas daripada CMMS; CMMS execution maintenance. | Finance; Procurement; CMMS; IoT | IMPLEMENTED (ujian nipis) | Assets |
| MFG-004 | PLM | Product Lifecycle Management | Data produk dari reka bentuk ke end-of-life. | CAD Links; BOM; Revisions; Engineering Change; Specs | Product definition; MES executes production. | CAD; ERP; MES; QMS | TIADA | – |
| MFG-005 | CAD/CAM | Computer-Aided Design/Manufacturing | Reka bentuk teknikal dan arahan pembuatan. | 3D/2D Design; Toolpaths; Drawings; NC Programs | Engineering authoring; bukan order/production execution. | PLM; MES; DMS | TIADA | – |
| MFG-006 | SCADA | Supervisory Control & Data Acquisition | Pemantauan dan kawalan proses industri. | Telemetry; Alarms; Historian; HMI | Sistem kawalan industri; jangan jalankan kawalan keselamatan melalui BOS. | PLC; MES; IoT; Historian | TIADA | – |
| MFG-007 | PLC/Industrial Control | Programmable Logic Control | Kawalan mesin dan proses masa nyata. | Logic; I/O; Interlocks; Safety Interfaces | Kekal pada sistem OT khusus; AI BIJAN hanya integrasi terhad dan selamat. | SCADA; MES | TIADA | – |
| MFG-008 | OEE Analytics | Overall Equipment Effectiveness | Keberkesanan mesin dan line pengeluaran. | Availability; Performance; Quality; Loss Analysis | Analitik; MES/SCADA sumber data operasi. | MES; SCADA; BI | TIADA | – |
| MFG-009 | QMS Manufacturing | Manufacturing Quality | Kualiti semasa proses pembuatan. | Inspection Plans; SPC; NCR; CAPA; Traceability | Boleh menjadi industry profile QMS umum, bukan duplicate product. | MES; PLM; ERP | TIADA | – |
| MFG-010 | APS | Advanced Planning & Scheduling | Jadual pengeluaran terhad kapasiti. | Finite Capacity; Sequencing; Constraints; What-if | Perancangan; MES melaksanakan work orders. | ERP; MRP; MES | TIADA | – |
| RET-001 | E-commerce Platform | Commerce System | Katalog, storefront dan checkout online. | Catalog; Cart; Checkout; Promotions; Customer Account | Channel jualan; OMS mengurus order keseluruhan. | PIM; OMS; Payment; IMS; CRM | IMPLEMENTED + diuji | Site; Marketplace |
| RET-002 | Marketplace Management | Marketplace Integration | Pengurusan jualan di marketplace. | Listings; Orders; Fees; Settlement; Sync | Connector/channel layer; marketplace kekal platform luar. | OMS; IMS; Finance | IMPLEMENTED + diuji | Marketplace |
| RET-003 | PIM | Product Information Management | Katalog dan atribut produk. | Attributes; Variants; Media; Taxonomy; Syndication | Maklumat produk bukan stok kuantiti. | E-commerce; POS; DAM; IMS | TIADA | – |
| RET-004 | Retail Merchandising | Merchandising System | Assortment, range dan product placement. | Assortment; Planograms; Pricing; Promotions | Perancangan merchandising; POS melaksanakan jualan. | PIM; POS; BI | TIADA | – |
| RET-005 | Returns & Warranty | Returns Management | Pemulangan, pertukaran dan jaminan. | RMA; Inspection; Refund Routing; Warranty | OMS order return; IMS stock disposition; Finance refund. | OMS; IMS; Payment; CRM | IMPLEMENTED + diuji | POS; Orders |
| RET-006 | Subscription Commerce | Subscription Commerce | Pesanan berulang produk/perkhidmatan. | Plans; Renewals; Dunning; Pause; Usage | Billing memegang caj; payment memproses transaksi. | Billing; Payment; OMS; CRM | IMPLEMENTED + diuji | Billing |

### Layer 4 — Automation & Integration

| ID | Kod | Nama | Fungsi | Submodul | Sempadan | Integrasi | Status kod | Modul |
|---|---|---|---|---|---|---|---|---|
| AUT-001 ★ | Workflow Builder | Pembina Aliran Kerja | Bina automasi "bila X berlaku, buat Y" tanpa kod. | Triggers; Conditions; Actions; Delays; Templates; Run History | Melaksana melalui API/event modul; tidak menulis terus ke jadual. | BPA; BPM; Event Bus; AI-009 | IMPLEMENTED (ujian nipis) | FlowAutomation; WorkflowStudio |
| AUT-002 ★ | Event Bus & Outbox | Bas Event Domain | Penghantaran event antara modul dengan outbox, idempoten dan cuba semula. | Outbox; Dispatcher; Consumers; Retry; Dead-letter; Replay | Infrastruktur; bukan pemilik data domain. | Semua modul; Webhooks; Workflow | SEBAHAGIAN | Laravel events/queue |
| AUT-003 ★ | Webhooks Keluar | Outbound Webhooks | Hantar event kepada sistem pelanggan dengan HMAC dan perlindungan SSRF. | Subscriptions; HMAC Signing; Retry; Delivery Log | Tidak menghantar data melebihi skop kebenaran langganan. | AUT-002; API Mgmt | IMPLEMENTED + diuji | Fundamental (§10) |
| AUT-004 ★ | WhatsApp Integration | WhatsApp Business | Resit, pautan bayar, peringatan, chat jualan & laporan ejen melalui WhatsApp. | wa.me Links; Cloud API Templates; Inbox; Opt-in; Delivery Status | Mesej templat perlu kelulusan Meta & persetujuan pelanggan. | POS; CRM; AR; Helpdesk; AI agents | SEBAHAGIAN | Omnichannel; Blast (wa.me siap, Cloud API belum) |
| AUT-005 ★ | Email Gateway | E-mel Transaksi & Pukal | Hantar invois, resit, notifikasi dan kempen e-mel. | SMTP/API Providers; Templates; Bounce; Unsubscribe; DKIM/SPF | Kempen ikut persetujuan (PDPA). | Billing; MAP; Notification | SEBAHAGIAN | Blast; Notification |
| AUT-006 ★ | SMS & Voice Gateway | SMS/Suara | OTP, peringatan dan panggilan keluar. | SMS Providers; OTP; Voice Bot; Delivery Log | Kos per mesej dikira ikut kuota langganan. | IAM; Contact Centre; Notification | TIADA | — |
| AUT-007 ★ | Payment Gateway Connectors | Penyambung Gerbang Bayaran | Sambung CHIP, ToyyibPay, Billplz, Stripe, DuitNow QR mengikut kedai. | Per-tenant Keys (encrypted); Payment Links; Callbacks; Refund; Settlement Import | Duit terus ke akaun kedai; AI Bijan tidak memegang dana pelanggan. | FIN-008; POS; Billing; AR | SEBAHAGIAN | Billing (CHIP siap; lain belum) |
| AUT-008 ★ | Marketplace & E-commerce Connectors | Shopee/Lazada/TikTok Shop/Woo | Segerak produk, stok dan pesanan dari marketplace. | Listing Sync; Stock Sync; Order Import; Fees; Returns | Stok muktamad kekal di IMS; marketplace hanya saluran. | RET-002; OMS; IMS; PIM | SEBAHAGIAN | Marketplace |
| AUT-009 ★ | Accounting Connectors | AutoCount/SQL Account/Bukku/Xero | Import & eksport data perakaunan untuk migrasi atau segerak. | Chart Mapping; Opening Balances; Invoice Sync; Reconciliation | Satu sumber kebenaran GL dipilih setiap tenant. | GL; Migration Hub | TIADA | — |
| AUT-010 ★ | Logistics Connectors | EasyParcel/J&T/Pos Laju/Lalamove | Tempah penghantaran, cetak AWB, jejak status. | Rate Check; Booking; AWB; Tracking; COD | Status penghantaran dari kurier; OMS pemilik pesanan. | OMS; TMS-LOG; Cod | SEBAHAGIAN | Logistics; Cod |
| AUT-011 ★ | Government & Statutory Connectors | LHDN MyInvois/KWSP/PERKESO/SSM | Penghantaran statutori: e-Invois, caruman, data syarikat. | MyInvois API; EPF/SOCSO/EIS files; PCB; SSM lookup | Keperluan berubah ikut peraturan rasmi; perlu semakan profesional. | FIN-016; Payroll; Tax | TIADA | — |
| AUT-012 ★ | Food Delivery Connectors | GrabFood/Foodpanda/ShopeeFood | Terima pesanan aplikasi penghantaran terus ke POS/dapur. | Menu Sync; Order Inbound; Status; Settlement | Bergantung API rakan; sesetengah perlu status rakan rasmi. | POS; KDS; OMS | TIADA | — |
| AUT-013 ★ | Bank Feed Connectors | Suapan Bank | Import penyata bank (fail atau API) untuk padanan. | CSV/MT940 Import; Open Banking API; Rules | Baca sahaja; tiada inisiasi bayaran. | FIN-017; Treasury | TIADA | — |
| AUT-014 ★ | MCP & Public API | MCP Server Awam | Ejen AI luar (Claude, ChatGPT) guna alat AI Bijan dengan kebenaran tenant. | MCP Server; OAuth Scopes; Tool Registry Reuse; Usage Log | Guna pendaftar alat yang sama (AI-009); tiada laluan pintas kebenaran. | AI-009; API Mgmt; IAM | SEBAHAGIAN | McpStudio |
| AUT-015 ★ | App Marketplace | Kedai Aplikasi Pihak Ketiga | Pembangun luar terbitkan integrasi/ add-on untuk tenant. | Listing; Install; Permissions; Billing Share; Review | Semakan keselamatan sebelum diterbitkan. | API Mgmt; Billing; OAM | TIADA | — |
| AUT-016 ★ | Scheduler & Jobs | Penjadual Tugas | Tugas berjadual (laporan harian, peringatan, segerak). | Cron; Queues; Retry; Monitoring | Infrastruktur. | Semua modul; Observability | IMPLEMENTED + diuji | Reminder; Laravel scheduler |
| CORE-010 | Connector Registry | Connector Management | Pendaftaran, versi dan lifecycle connector. | Registry; Credentials Ref; Scopes; Health; Version | Tidak menjalankan transformasi migrasi penuh. | iPaaS; Migration Hub; API | TIADA | – |
| TEC-004 | iPaaS | Integration Platform as a Service | Orkestrasi sambungan antara aplikasi. | Connectors; Mapping; Routing; Monitoring; Retry | Platform integrasi; bukan pemilik data domain. | API; Webhooks; Migration Hub | IMPLEMENTED + diuji | FlowAutomation; McpStudio |
| TEC-005 | API Management | API Management System | Pengurusan lifecycle dan akses API. | Gateway; Keys; OAuth; Rate Limits; Developer Portal | Mengawal akses API; tidak menggantikan domain services. | All modules; iPaaS | TIADA | – |
| TEC-009 | RPA | Robotic Process Automation | Automasi UI bagi sistem tanpa API memadai. | Bots; Schedules; Credentials; Run Logs; Exception Queue | Pilihan terakhir berbanding API; tidak boleh memintas kawalan akses. | BPM; iPaaS; ITSM | TIADA | – |
| TEC-011 | ETL/ELT | Data Integration & Transformation | Pemindahan dan transformasi data analitik/operasi. | Pipelines; Schedules; Quality Checks; Lineage | Bukan migration project governance sepenuhnya. | DWH; MDM; Migration Hub | TIADA | – |
| TEC-018 | B-Migration Hub | Data Migration & Integration Hub | Migrasi, mapping, validation, sync dan reconciliation. | Connectors; Extraction; Mapping; Trial; Batch; CDC; Conflict; Audit | Tidak menjamin semua vendor boleh diekstrak; bergantung API/lesen/akses. | ERP; CRM; HRMS; Accounting; Databases | TIADA | – |

### Layer 5 — Core Platform

| ID | Kod | Nama | Fungsi | Submodul | Sempadan | Integrasi | Status kod | Modul |
|---|---|---|---|---|---|---|---|---|
| CORE-001 | OAM | Organization & Access Management | Organisasi, membership, roles, permissions, data scope dan seat. | Tenant; Company; Branch; Department; Team; Membership; RBAC/ABAC; Approval | Shared platform core; bukan HRIS atau CRM. | All modules; Subscription; Audit | SEBAHAGIAN | app/Http/Middleware/ResolveTenant + ahli/peranan |
| CORE-002 | Subscription & Entitlement | Subscription Management | Pelan, billing entitlement dan kuota. | Plans; Subscriptions; Add-ons; Seats; Limits; Grace Period | Menentukan akses produk; authorization individu kekal OAM. | OAM; Billing; AI Gateway | IMPLEMENTED + diuji | Entitlement; Billing |
| CORE-003 | Notification Management | Notification System | Penghantaran notifikasi transaksi dan peringatan. | Templates; Channels; Preferences; Delivery; Retry | Bukan CRM campaign engine. | All modules; Email; SMS; WhatsApp | IMPLEMENTED + diuji | Reminder; Blast |
| CORE-004 | Workflow & Approval | Approval Management | Kelulusan transaksi dan tindakan berisiko. | Rules; Delegation; Sequential; Parallel; Escalation | BPM mengatur proses luas; approval service menguatkuasa keputusan kelulusan. | OAM; Finance; Procurement; HRMS | IMPLEMENTED + diuji | WorkflowStudio |
| CORE-005 | AI Gateway | AI Model Gateway | Routing model, token meter dan kawalan AI. | Providers; Routing; Quotas; Wallet; Cost; Logs | Tidak menyimpan business truth atau melangkaui agent permission. | AI Agents; Billing; OAM | IMPLEMENTED + diuji | AIGateway |
| CORE-008 | Master Data & Reference Data | Reference Data Service | Kod, taxonomy dan data rujukan standard. | Code Sets; Units; Currencies; Countries; Industry Codes | Bukan master record pelanggan/produk yang domain-specific. | MDM; ERP; PIM | TIADA | – |
| CORE-009 | Audit & Activity Log | Audit Management Service | Rekod tindakan pengguna, sistem dan agent. | Actor; Action; Target; Timestamp; Correlation; Retention | Tidak sama dengan Audit Management engagement. | OAM; SIEM; All modules | IMPLEMENTED (ujian nipis) | app/Services/AuditLogger |
| CORE-011 | Data Quality Management | Data Quality System | Profiling, rules dan pemantauan kualiti data. | Rules; Profiling; Duplicates; Exceptions; Scorecards | Tidak menjadi MDM atau migration orchestrator. | MDM; Migration Hub; DWH | TIADA | – |
| CORE-012 | Customer Identity & Consent | CIAM/Consent | Identiti pelanggan luaran dan persetujuan. | Customer Login; Consent; Preferences; Identity Link | Berbeza daripada workforce IAM/OAM. | CDP; CRM; Commerce | TIADA | – |
| CORE-013 | Search & Discovery | Enterprise Search | Carian merentas data yang dibenarkan. | Indexing; Facets; Relevance; ACL Filtering | Search index bukan sumber rekod rasmi. | DMS; CRM; Knowledge; All modules | TIADA | – |
| CORE-014 | Localization & Tax Engine | Localization Service | Bahasa, timezone, currency dan rules negara. | Locales; Currency; Tax Profiles; Formats | Tax calculation domain-specific; localization menyediakan config. | All modules; Tax | TIADA | – |
| CORE-015 | File & Object Storage | File Storage Service | Fail, attachment dan lifecycle storage. | Upload; Version; Malware Scan; Retention; Signed URL | Bukan DMS content governance atau DAM taxonomy. | DMS; DAM; All modules | IMPLEMENTED (ujian nipis) | Laravel Storage |
| CORE-016 ★ | Multi-Tenancy Engine | Enjin Berbilang Penyewa | Pengasingan data setiap syarikat (tenant_id) di semua lapisan. | Tenant Context; Scoped Queries; Tenant Middleware; Isolation Tests | Sempadan keselamatan utama; tiada pertanyaan tanpa skop. | Semua modul; OAM | IMPLEMENTED + diuji | Support/Tenancy |
| CORE-017 ★ | Tenant Provisioning | Penyediaan Tenant | Daftar syarikat, pakej, data permulaan dan tutup akaun. | Signup; Plan Assign; Seed Data; Suspend; Export & Delete | Tamat langganan tidak memadam data serta-merta. | CORE-002; Billing; OAM | IMPLEMENTED + diuji | Fundamental |
| CORE-018 ★ | White-label & Reseller | Jenama Putih & Penjual Semula | Rakan jual AI Bijan dengan jenama & domain sendiri, perkongsian hasil. | Master/Super/Reseller; Branding; Custom Domain; Revenue Share | Platform kekal pemilik kod & data pemprosesan. | Billing; CORE-002; PRM | IMPLEMENTED + diuji | Fundamental (§4a/§4b) |
| CORE-019 ★ | Usage Metering & AI Credits | Meter Penggunaan & Kredit AI | Kira penggunaan AI, mesej, storan ikut pakej. | Meters; Credits; Quota; Top-up; Overage | Kredit bukan wang tunai/e-money. | AI Gateway; Billing; CORE-002 | IMPLEMENTED + diuji | Entitlement; Billing |
| CORE-020 ★ | Offline Sync & PWA | Luar Talian & Aplikasi Mudah Alih | Aplikasi berfungsi tanpa internet dan segerak bila talian kembali. | PWA; Local Queue; Idempotent Sync; Conflict Rules | Transaksi luar talian mesti idempoten. | POS; T&A; FSM | IMPLEMENTED + diuji | POS (offline) |
| CORE-021 ★ | Print & Device Service | Pencetak & Peranti | Pencetak resit, dapur, laci tunai, pengimbas, timbang. | ESC/POS; WebUSB; Network Printers; Per-station Routing | Peranti dipadankan setiap cawangan. | POS; KDS; WMS | SEBAHAGIAN | POS (ESC/POS belum diuji perkakasan) |
| CORE-022 ★ | Feature Flags & Config | Bendera Ciri | Hidup/matikan ciri ikut pakej, industri atau tenant. | Flags; Plan Gates; Rollout; Industry Packs | Bukan pengganti kebenaran OAM. | CORE-002; IndustryPacks | IMPLEMENTED (ujian nipis) | Entitlement; IndustryPacks |
| CORE-023 ★ | Encryption & Key Management | Penyulitan Data | Penyulitan medan sensitif (kunci API, IC, gaji) dan putaran kunci. | Field Encryption; Key Rotation; Masking | Kunci tidak pernah dipaparkan semula. | TEC-014; Payroll; Payment | SEBAHAGIAN | Laravel encrypted casts |
| TEC-006 | IAM | Identity & Access Management | Identiti, authentication dan authorization. | SSO; MFA; Sessions; Policies; Provisioning | OAM mengurus konteks organisasi/membership; IAM ialah capability identity/security. | OAM; HRMS; all apps | IMPLEMENTED (ujian nipis) | app/Http |
| TEC-007 | SIEM | Security Information & Event Management | Pengumpulan dan korelasi log keselamatan. | Log Ingestion; Correlation; Detection; Incident Alerts | Bukan audit perniagaan atau endpoint protection. | IAM; SIEM agents; Audit; ITSM | TIADA | – |
| TEC-013 | Observability | Application Observability | Metrics, logs, traces dan health aplikasi. | APM; Distributed Tracing; Alerts; SLO | Operasi teknikal; bukan BI perniagaan. | All services; ITSM | IMPLEMENTED (ujian nipis) | HealthController |
| TEC-014 | Secrets Management | Secrets Vault | Penyimpanan credential dan secrets. | Vault; Rotation; Access Policy; Audit | Tidak menyimpan secret dalam plaintext atau tenant-shared scope. | Connectors; API; CI/CD | TIADA | – |
| TEC-015 | Backup & Disaster Recovery | Backup/DR System | Backup, restore dan pemulihan bencana. | Backup Jobs; Snapshots; Restore Tests; DR Plans | Bukan migration data atau version control. | Infrastructure; BCM; Security | TIADA | – |
| TEC-016 | Configuration Management | CMDB & Configuration | Inventori konfigurasi aset IT dan dependency. | CI Registry; Relationships; Change History | CMDB IT tidak sama dengan product/asset inventory komersial. | ITSM; Observability; Security | TIADA | – |
| TEC-017 | DevSecOps | DevSecOps Platform | Pipelines pembangunan dan keselamatan kod. | CI/CD; SAST; DAST; Dependency Scan; Release Gates | Toolchain engineering dalaman, bukan tenant business app. | Git; Security; Observability | TIADA | – |

## 6. Pemilik data (system of record)

Satu entiti = satu pemilik. Modul lain baca melalui API/service atau langgan event.

| Entiti data | Pemilik | Pengguna utama |
|---|---|---|
| Syarikat, cawangan, pengguna, peranan, kebenaran | CORE-001 OAM + CORE-016 | Semua |
| Pelanggan (profil hubungan) | BOS-001 CRM | POS, OMS, Helpdesk, Loyalty, AR |
| Profil pelanggan bersatu & persetujuan | GRO-003 CDP + CORE-012 | MAP, AI CMO |
| Produk (maklumat, varian, gambar) | SUP-013 PIM | POS, E-commerce, Marketplace |
| Kuantiti & pergerakan stok | SUP-001 IMS (stock ledger) | POS, OMS, WMS, MRP |
| Kos stok / COGS | SUP-016 Costing | GL, BI, AI CFO |
| Pesanan (semua saluran) | BOS-005 OMS | POS, Marketplace, Logistik, Billing |
| Jualan kaunter & syif | BOS-006 POS | OMS, IMS, Payment, GL |
| Invois pelanggan & baki hutang | FIN-004 AR (+FIN-007 Billing) | CRM, Kutip Hutang, GL |
| Bil pembekal | FIN-003 AP | Procurement, Treasury, GL |
| Status bayaran (disahkan gateway) | FIN-008 Payment | POS, AR, Billing, Treasury |
| Jurnal & baki akaun | FIN-011 GL | BI, FP&A, Tax, AI CFO |
| Pekerja | HR-002 HRIS | Payroll, T&A, WFM, IAM |
| Kehadiran | HR-007 T&A | Payroll, WFM |
| Gaji & caruman | HR-006 Payroll | GL, AUT-011 |
| Pembekal | SUP-005 SRM | Procurement, AP |
| Aset fizikal | SUP-010 Asset Mgmt | FIN-012, CMMS |
| Dokumen & versi | TEC-010 DMS | Semua |
| Kontrak | GRC-005 CLM | CRM, Procurement, Billing |
| Langganan & had pakej | CORE-002 + CORE-019 | Semua modul (gating) |
| Tindakan ejen AI & kelulusan | AI-010 + CORE-009 Audit | Pemilik, juruaudit |

## 7. Katalog event integrasi

Format: `Domain.Peristiwa` · dihantar melalui outbox (AUT-002) · idempoten (kunci = id sumber) · ada `tenant_id`, `correlation_id`, `schema_version`.

| Event | Penerbit | Pelanggan (consumer) | Kesan |
|---|---|---|---|
| `pos.SaleCompleted` | POS | OMS, IMS, GL, Loyalty, CDP, AI CFO | Pesanan direkod, stok dipotong, jurnal jualan, mata ganjaran |
| `pos.SaleRefunded` | POS | IMS, GL, Payment, Loyalty | Stok masuk semula, jurnal terbalik, refund gateway |
| `pos.ShiftClosed` | POS | Cash Mgmt, GL, AI COO | Varians tunai, laporan tutup kedai (WhatsApp) |
| `order.Created` | OMS | IMS (tempah stok), WMS, CRM | Stok ditempah, tugasan ambil |
| `order.Fulfilled` | OMS / WMS | Billing, Logistik, CRM, Helpdesk | Invois, AWB, notifikasi pelanggan |
| `payment.Succeeded` | Payment (callback bertandatangan) | POS/OMS/AR, Treasury, GL | Jualan/invois ditanda bayar, jurnal bank |
| `payment.Failed / Refunded` | Payment | POS/OMS/AR, CRM | Status dikemas kini, susulan |
| `stock.Moved` | IMS | COGS, BI, AI COO, Marketplace | Kos dikemas kini, stok marketplace segerak |
| `stock.BelowReorder` | IMS | SUP-014 Reorder, AI COO | Draf PO ke Peti Kelulusan |
| `invoice.Issued` | Billing / AR | GL, e-Invois (AUT-011), CRM, WhatsApp | Jurnal, hantar MyInvois, kirim pelanggan |
| `invoice.Overdue` | AR | Kutip Hutang (AI CFO), CRM | Draf peringatan WhatsApp (perlu kelulusan) |
| `einvoice.Validated / Rejected` | AUT-011 | AR, Tax, Audit | UUID LHDN disimpan / pembetulan |
| `purchase.OrderApproved` | Procurement | SRM, Supplier Portal, AP | PO dihantar, komitmen bajet |
| `goods.Received` | WMS / Procurement | IMS, AP (3-way match), QMS | Stok masuk, padanan bil |
| `bill.Approved` | AP | Treasury, Payment, GL | Cadangan bayaran |
| `employee.Hired / Terminated` | HRIS | IAM, Payroll, Assets, T&A | Akaun dibuka/ditutup, aset dipulangkan |
| `attendance.Recorded` | T&A | Payroll, WFM | Jam kerja & OT |
| `payroll.Finalised` | Payroll | GL, AUT-011 (KWSP/PERKESO/PCB), Payment, ESS | Jurnal gaji, fail statutori, slip |
| `lead.Captured` | CRM / MAP / Link | SFA, AI CSO, WhatsApp | Skor lead, susulan |
| `quote.Accepted` | CPQ | OMS, CLM, Billing | Pesanan & kontrak dicipta |
| `ticket.Created / SLABreached` | Helpdesk | CRM, AI COO, Notification | Eskalasi |
| `booking.Confirmed` | Booking | Payment (deposit), Calendar, WhatsApp, WFM | Deposit, peringatan, jadual staf |
| `production.OrderCompleted` | MES | IMS, COGS, QMS, GL | Barang siap masuk stok, kos WIP |
| `asset.MaintenanceDue` | CMMS / EAM | Task, Procurement, AI COO | Tugasan & pesanan alat ganti |
| `membership.RoleChanged` | OAM | IAM, Audit, AI agents | Kebenaran & skop ejen dikemas kini |
| `agent.ActionProposed / Approved / Executed` | AI-009/010 | Audit, Notification, modul sasaran | Jejak penuh tindakan AI |
| `subscription.Changed / Expired` | CORE-002 | Entitlement, semua modul | Ciri dihidup/dimatikan; data tidak dipadam |
| `migration.JobCompleted` | Migration Hub | MDM, BI, AI-014 | Laporan rekonsiliasi |

## 8. Aliran transaksi rujukan (contoh BOS sebenar)

### 8.1 Jualan kedai bunga (POS → semua)

1. Juruwang jual 1 jambak bunga RM120, pelanggan bayar guna pautan CHIP. → POS cipta jualan draf.
2. CHIP hantar callback bertandatangan → `payment.Succeeded` → POS tanda jualan selesai (**siap, Fasa 1.4**).
3. `pos.SaleCompleted` → IMS potong stok bunga & reben ikut resepi (**potong stok siap**; resepi = HOS-003).
4. OMS rekod pesanan; jika penghantaran, AUT-010 tempah kurier.
5. GL posting jurnal jualan + kos (**belum: Fasa 3.1 auto-jurnal**).
6. Loyalty tambah mata; CDP kemas kini profil.
7. AUT-011 masukkan jualan ke invois disatukan B2C bulanan MyInvois (**belum: Fasa 1.3**).
8. Malam: AI COO hantar laporan tutup kedai ke WhatsApp pemilik (**siap: Ejen Kaunter**); AI CFO tunjuk margin hari ini & amaran stok.

### 8.2 B2B: Lead → Kontrak → Bayaran

CRM lead → AI CSO skor & draf susulan → CPQ sebut harga → `quote.Accepted` → CLM kontrak + SignDocs → OMS → Billing invois → e-Invois → AR → `invoice.Overdue` → AI CFO draf peringatan (lulus) → `payment.Succeeded` → GL.

### 8.3 Gaji bulanan

T&A → Payroll (EPF/SOCSO/EIS/PCB) → semakan → `payroll.Finalised` → GL jurnal · fail KWSP/PERKESO · bayaran bank (manual/AP) · slip di ESS/WhatsApp.

## 9. Matriks integrasi teras (siapa bercakap dengan siapa)

Baris = modul penghantar; lajur = penerima. `E` = event, `R` = baca melalui API/service, `C` = panggil perintah (command), `—` = tiada hubungan terus.

| Dari \ Ke | POS | OMS | IMS | CRM | Payment | AR/Billing | GL | HR/Payroll | Procurement/AP | BI/AI |
|---|---|---|---|---|---|---|---|---|---|---|
| **POS** | — | E | E | R | C | — | E | R | — | E |
| **OMS** | R | — | E | R | R | E | — | — | — | E |
| **IMS** | R | R | — | — | — | — | E | — | E | E |
| **CRM** | R | R | — | — | — | R | — | — | — | E |
| **Payment** | E | E | — | — | — | E | E | — | E | E |
| **AR/Billing** | — | R | — | R | C | — | E | — | — | E |
| **GL** | — | — | — | — | — | — | — | — | — | E |
| **HR/Payroll** | R | — | — | — | C | — | E | — | — | E |
| **Procurement/AP** | — | — | E | — | C | — | E | — | — | E |
| **BI/AI** | R | R | R | R | R | R | R | R | R | — |

## 10. Keutamaan pembinaan (dipadankan dengan todo Fasa)

| Fasa | Fokus | Sistem utama (ID) | Status |
|---|---|---|---|
| 1 — Kedai berniaga penuh | Kedai bunga/F&B boleh jual, bayar, resit, e-Invois | BOS-006, HOS-001/002, SUP-001, FIN-008, AUT-007, AUT-004, AI-008/009/010/014, BOS-016, FIN-016 | Kebanyakan siap; baki: 1.3 MyInvois, 1.5b WhatsApp Cloud API, 1.6 cawangan, 1.8 uji di kedai |
| 2 — Pindah masuk mudah | Import data & migrasi dari sistem lama | TEC-018, AUT-009, AI-014, AUT-014 | Belum |
| 3 — Kewangan automatik | Auto-jurnal, kutip hutang, CFO harian, rekonsiliasi bank | FIN-011, FIN-004, AI-002, FIN-017, AUT-013 | Belum |
| 4 — Pekerja & stok | Payroll statutori MY, cuti/syif, PO automatik | HR-006, HR-007, BOS-009, AUT-011, SUP-014, AI-004/006 | Belum |
| 5 — Pertumbuhan | CRM AI, pemasaran, tempahan + deposit, delivery apps | GRO-001/002/003, AI-003/005, EVT-005, AUT-012 | Modul ada, AI & integrasi belum |
| 6 — Industri & korporat | Pek industri (klinik, hotel, pembinaan), GRC, data/BI lanjutan | L3 penuh, GRC-*, TEC-002/003, AI-011/013 | Kebanyakan TIADA; ikut permintaan pelanggan |

**Peraturan:** sistem `TIADA` di L3/GRC/TEC tidak dibina sehingga ada pelanggan membayar yang memerlukannya. Inventori ini memastikan **tiada yang terlepas pandang**, bukan bermaksud semua dibina sekarang.

## 11. Keputusan yang perlu owner sahkan

1. Terima 5 lapisan & 56 sistem baharu v2.0 sebagai baseline rasmi (ganti v1.0).
2. Kod unik untuk akronim bertindih (LMS-LRN, BMS-BLD, TMS-LOG, PMS-PRJ).
3. Zakat & patuh Syariah (FIN-021) dan Francais (BOS-017): masuk peta jalan atau simpan sebagai "kemudian".
4. Masih tertunggak dari v1.0: harga (3 versi bercanggah), padam 24 dokumen pendua.

## Lampiran: sumber

- `hasil/AI_BIJAN_Master_Inventory_v1.0.md` (fungsi, submodul, sempadan, integrasi 179 sistem v1)
- `docs/AI-BIJAN-MASTER.md` dalam PR #3 (status kod per sistem, disemak 31/10)
- `hasil/2026-10-31-todo-fasa-aibijan.md` (fasa & status terkini)
- Senarai sistem owner 31/10 (8 kategori, 85 sistem)
