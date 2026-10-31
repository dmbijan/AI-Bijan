# AI BIJAN — MASTER RASMI (Dokumen Induk Gabungan)

**Versi:** 1.0  ·  **Tarikh:** 31 Oktober 2026  ·  **Status:** `AUTHORITATIVE` (indeks & status); kandungan terperinci kekal di dokumen sumber  
**Tagline:** *Every Business Deserves a Boardroom.*  
**Disediakan:** gabungan Master Inventory v1.0 + Master Blueprint v9.x + semakan kod sebenar repo `main` (v9.5) dan PR #2.

> Dokumen ini **menggabungkan** semua dokumen AI Bijan menjadi satu titik masuk. Ia tidak menyalin semula 10 MB kandungan lama; setiap bahagian menunjuk ke sumber yang betul, dan setiap sistem ditanda dengan **status sebenar dalam kod**, bukan dakwaan blueprint. Senarai penuh semua fail `.md` ada dalam [`INDEKS-DOKUMEN.md`](INDEKS-DOKUMEN.md).

**Kandungan:** 0 Sumber kebenaran · 1 Identiti · 2 Keputusan terbuka · 3 Registry 179 × status kod · 4 Modul luar registry · 5 Peta Blueprint · 6 Seni bina/OAM/Migrasi · 7 Roadmap MVP · 8 Kualiti · 9 Cara kemas kini · **10 Audit Odoo 50 app** · **11 Pelaksanaan Agentic AI** · **12 Standard UI/UX**

## 0. Hierarki sumber kebenaran

Jika dua sumber bercanggah, ikut susunan ini:

| # | Sumber | Fail | Peranan |
|---|---|---|---|
| 1 | **Kod & ujian** | `app/`, `tests/` | Apa yang benar-benar wujud. Status `VERIFIED READY` hanya selepas ujian penerimaan (Inventory §2). |
| 2 | **Master ini** | `docs/AI-BIJAN-MASTER.md` | Indeks rasmi, status, percanggahan & keputusan terbuka. |
| 3 | **Master Inventory v1.0** | [`AI-BIJAN-MASTER-INVENTORY-v1.0.md`](AI-BIJAN-MASTER-INVENTORY-v1.0.md) | Skop 179 sistem canonical, sempadan domain, system-of-record, OAM, Migration Hub, Definition of Done. |
| 4 | **Master Blueprint v9.x** | [`docs/AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md`](AI-BIJAN-MASTER-BLUEPRINT-v8.3%20(1).md) (alias [`AI-BIJAN-MASTER-BLUEPRINT.md`](AI-BIJAN-MASTER-BLUEPRINT.md)) | Spesifikasi produk terperinci: 25 PART, BAB 0–91. Mengandungi semakan v8.4–v9.3 (termasuk **v8.5 = BAB 82**). |
| 5 | Audit & kajian | `docs/AUDIT-*.md`, `docs/KAJIAN-*.md`, laporan `hasil/` pejabat | Bukti & analisis pada tarikh tertentu. |
| 6 | Arkib | `docs/archive/`, blueprint v2–v8.3, v4.x | Sejarah sahaja. **Jangan guna untuk keputusan baharu.** |

**Nota v8.5:** tiada fail "v8.5" berasingan. Kandungan v8.5 (BAB 82 — B-Executive AI) berada dalam fail blueprint induk di atas, yang namanya mengelirukan ("v8.3 (1)") tetapi isinya versi 9.3 (25/10) dan dilabel 9.6 dalam fail alias.

## 1. Identiti produk

- **Positioning (Blueprint BAB 0):** AI-Native Business OS for Emerging Markets — satu platform menggantikan 10+ alat.
- **Prinsip (Inventory §1):** satu platform & identiti; bounded context per domain; entitlement tentukan modul, OAM tentukan siapa buat apa; ejen AI guna identiti servis + kelulusan + audit.
- **Strategi menang (Blueprint BAB 81, riset pesaing):** integrasi + migrasi berperingkat + pematuhan Malaysia + AI yang bertindak dengan kelulusan — bukan perang harga.
- **Seni bina (Inventory §10):** Laravel modular monolith, API `/api/v1`, multi-tenant (`tenant_id`), domain events + outbox.

## 2. Percanggahan & keputusan terbuka (perlu owner)

| # | Isu | Sumber A | Sumber B | Cadangan |
|---|---|---|---|---|
| 1 | **Harga langganan** | Blueprint: 5 pakej RM288–RM1,888, token termasuk | Kod Billing/Entitlement: harga berbeza dalam config | **DIPUTUSKAN 31/10/2026:** semua harga & pakej diurus di **Super Admin → Pakej & Harga** (PR #2); sistem hanya semai pakej contoh. Harga dalam blueprint dan config = contoh sahaja. |
| 2 | Bilangan sistem | Inventory: 179 canonical + 210 slot legacy | Kod: 60 modul (`app/Modules`) | Lihat §3: berapa canonical yang ada dalam kod, dan §4: modul kod di luar registry. |
| 3 | Dakwaan ujian | Commit baseline v9.5: "963 ujian lulus, 0 gagal" | Disahkan 31/10: suite **tidak boleh jalan** di `main` (26 migrasi bayangan). Selepas PR #2: 1,365 ujian, 3 gagal (ext-intl sandbox) | Guna nombor PR #2; CI GitHub kini dikunci (bil) — perlu dibuka. |
| 4 | Nama fail blueprint | `AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md` berisi v9.3 | Alias kata v9.6 | Namakan semula kepada `AI-BIJAN-MASTER-BLUEPRINT.md` (PR berasingan) dan kemas kini nombor versi. |
| 5 | 210 nama legacy | Nama individu 1–210 tidak dapat dipulihkan (kecuali MES #117) | – | Owner bekalkan daftar asal untuk crosswalk (Inventory §14 #1). |
| 6 | Alat ejen AI | – | 67 alat dalam `ToolExecutor` ialah stub dan tidak boleh dicapai: ejen tiada alat berfungsi (§11.2). Fasa 1.1 menambah alat sebenar | Wajib dibetulkan sebelum ejen dibuka kepada pelanggan. |
| 7 | Pemetaan §3 | Pemetaan canonical → modul dibuat oleh Dimas | – | Owner/Product Owner sahkan. |

## 3. Registry canonical × status kod sebenar

Sumber registry: Master Inventory v1.0 §4 (179 sistem). Status dikira daripada kod repo pada 31/10/2026 (baris PHP per modul, bilangan kaedah ujian yang menyebut modul). `IMPLEMENTED + diuji` = ≥20 kaedah ujian; ini **bukan** `VERIFIED READY` — itu memerlukan ujian penerimaan, keselamatan & integrasi (Inventory §13.1).

**Ringkasan:**

| Domain | Jumlah | Ada + diuji | Ada, ujian nipis | Sebahagian / rangka | Tiada dalam kod |
|---|---:|---:|---:|---:|---:|
| BOS — Business Operations | 15 | 8 | 2 | 0 | 5 |
| FIN — Kewangan | 18 | 8 | 0 | 3 | 7 |
| GRO — Jualan, Pemasaran & Pelanggan | 20 | 8 | 5 | 1 | 6 |
| HR — HR & Tenaga Kerja | 12 | 1 | 4 | 0 | 7 |
| SUP — Inventori & Rantaian Bekalan | 13 | 4 | 4 | 1 | 4 |
| TEC — Teknologi, Data & Integrasi | 18 | 2 | 3 | 0 | 13 |
| GRC — Tadbir Urus, Risiko & Pematuhan | 11 | 0 | 2 | 0 | 9 |
| MFG — Pembuatan | 10 | 0 | 2 | 1 | 7 |
| HLT — Kesihatan | 10 | 0 | 0 | 0 | 10 |
| HOS — Hospitaliti & F&B | 8 | 2 | 1 | 0 | 5 |
| RET — Runcit & E-dagang | 6 | 4 | 0 | 0 | 2 |
| CON — Pembinaan | 6 | 0 | 0 | 0 | 6 |
| LOG — Pengangkutan | 5 | 2 | 0 | 0 | 3 |
| EVT — Acara, Tempahan & Servis | 12 | 1 | 2 | 0 | 9 |
| CORE — Platform Teras | 15 | 5 | 3 | 1 | 6 |
| **Jumlah** | **179** | **45** | **28** | **7** | **99** |

`SEBAHAGIAN` / `RANGKA` = modul ada, tetapi capability khusus ini belum lengkap (disemak manual dalam kod; lihat lajur Nota).

### 3.1 BOS — Business Operations

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| BOS-001 | CRM | Customer Relationship Management | IMPLEMENTED + diuji | CRM (2,227 baris, 46 ujian) |  |
| BOS-002 | ERP | Enterprise Resource Planning | TIADA | – |  |
| BOS-003 | BPM | Business Process Management | IMPLEMENTED + diuji | WorkflowStudio (909 baris, 30 ujian) |  |
| BOS-004 | BMS | Business Management System | TIADA | – |  |
| BOS-005 | OMS | Order Management System | IMPLEMENTED + diuji | Orders (1,664 baris, 146 ujian) |  |
| BOS-006 | POS | Point of Sale | IMPLEMENTED + diuji | POS (5,193 baris, 130 ujian) | PR #2: checkout+offline, DuitNow, QR meja, potong stok |
| BOS-007 | OSM | Operations Management System | IMPLEMENTED + diuji | ServiceOS (3,148 baris, 63 ujian) |  |
| BOS-008 | PMS-PRJ | Project Management System | IMPLEMENTED (ujian nipis) | Projects (3,033 baris, 5 ujian) |  |
| BOS-009 | WFM | Workforce Management | TIADA | – |  |
| BOS-010 | EPM | Enterprise Performance Management | IMPLEMENTED + diuji | BusinessIntel (1,801 baris, 40 ujian) |  |
| BOS-011 | BPA | Business Process Automation | IMPLEMENTED + diuji | FlowAutomation (1,592 baris, 21 ujian) |  |
| BOS-012 | Task & Work Management | Work Management System | IMPLEMENTED + diuji | Projects (3,033 baris, 5 ujian); PersonalAssistant (2,320 baris, 31 ujian) |  |
| BOS-013 | Enterprise Content Portal | Corporate Portal | IMPLEMENTED (ujian nipis) | VirtualOffice (2,879 baris, 0 ujian) |  |
| BOS-014 | Business Continuity Management | BCM System | TIADA | – |  |
| BOS-015 | Strategy Execution Management | Strategy Management | TIADA | – |  |

### 3.2 FIN — Kewangan

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| FIN-001 | Accounting System | Accounting Management System | IMPLEMENTED + diuji | Finance (1,810 baris, 78 ujian) |  |
| FIN-002 | FMS | Financial Management System | IMPLEMENTED + diuji | Finance (1,810 baris, 78 ujian); BusinessIntel (1,801 baris, 40 ujian) |  |
| FIN-003 | AP | Accounts Payable | IMPLEMENTED + diuji | Finance (1,810 baris, 78 ujian) |  |
| FIN-004 | AR | Accounts Receivable | IMPLEMENTED + diuji | Finance (1,810 baris, 78 ujian); Invoicing (3,693 baris, 62 ujian) |  |
| FIN-005 | FP&A | Financial Planning & Analysis | TIADA | – |  |
| FIN-006 | Treasury Management | Treasury System | TIADA | – |  |
| FIN-007 | Billing System | Billing Management | IMPLEMENTED + diuji | Billing (2,233 baris, 40 ujian); Invoicing (3,693 baris, 62 ujian) |  |
| FIN-008 | Payment Management | Payment Orchestration | SEBAHAGIAN | Billing (2,233 baris, 40 ujian) | CHIP untuk langganan sahaja; bayaran pelanggan kedai belum |
| FIN-009 | Expense Management | Expense System | IMPLEMENTED + diuji | Expense (2,515 baris, 94 ujian) |  |
| FIN-010 | Tax Management | Tax System | TIADA | – |  |
| FIN-011 | General Ledger | GL | IMPLEMENTED + diuji | Finance (1,810 baris, 78 ujian) |  |
| FIN-012 | Fixed Asset Accounting | Fixed Asset System | IMPLEMENTED + diuji | Finance (1,810 baris, 78 ujian); Assets (2,484 baris, 5 ujian) |  |
| FIN-013 | Revenue Management | Revenue Recognition | TIADA | – |  |
| FIN-014 | Credit & Collections | Credit Management | SEBAHAGIAN | Invoicing (3,693 baris, 62 ujian); Reminder (1,206 baris, 33 ujian) |  |
| FIN-015 | Financial Consolidation | Consolidation System | TIADA | – |  |
| FIN-016 | E-Invoice Management | E-Invoicing | RANGKA | Invoicing (3,693 baris, 62 ujian) | `EInvoisService` rangka (ada TODO); belum disahkan dgn MyInvois |
| FIN-017 | Bank Reconciliation | Bank Reconciliation System | TIADA | – |  |
| FIN-018 | Cash Management | Cash Management System | TIADA | – |  |

### 3.3 GRO — Jualan, Pemasaran & Pelanggan

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| GRO-001 | SFA | Sales Force Automation | IMPLEMENTED + diuji | SalesCopilot (818 baris, 18 ujian); Prospecting (1,890 baris, 62 ujian) |  |
| GRO-002 | MAP | Marketing Automation Platform | IMPLEMENTED + diuji | Marketing (6,962 baris, 0 ujian); Blast (2,256 baris, 22 ujian) |  |
| GRO-003 | CDP | Customer Data Platform | TIADA | – |  |
| GRO-004 | DMP | Data Management Platform | TIADA | – |  |
| GRO-005 | CMS | Content Management System | IMPLEMENTED (ujian nipis) | Site (4,015 baris, 0 ujian) |  |
| GRO-006 | DAM | Digital Asset Management | IMPLEMENTED (ujian nipis) | MediaStudio (1,586 baris, 18 ujian) |  |
| GRO-007 | Loyalty Management | Loyalty Management System | IMPLEMENTED (ujian nipis) | Loyalty (2,963 baris, 1 ujian) |  |
| GRO-008 | PRM | Partner Relationship Management | SEBAHAGIAN | Billing (2,233 baris, 40 ujian) |  |
| GRO-009 | Affiliate Management | Affiliate System | IMPLEMENTED (ujian nipis) | Affiliate (3,052 baris, 0 ujian) |  |
| GRO-010 | CPQ | Configure Price Quote | IMPLEMENTED + diuji | Invoicing (3,693 baris, 62 ujian); CRM (2,227 baris, 46 ujian) |  |
| GRO-011 | Helpdesk | Customer Support System | IMPLEMENTED (ujian nipis) | Helpdesk (111 baris, 14 ujian) | Servis dalaman; laluan dalam routes/api.php |
| GRO-012 | Contact Centre | Contact Centre System | IMPLEMENTED + diuji | Omnichannel (2,050 baris, 35 ujian); LiveSupport (749 baris, 41 ujian) |  |
| GRO-013 | Customer Success Management | CSM | TIADA | – |  |
| GRO-014 | Social Media Management | SMM | IMPLEMENTED + diuji | ContentStudio (2,105 baris, 81 ujian) |  |
| GRO-015 | SEO & Metadata Management | SEO System | IMPLEMENTED + diuji | Seo (1,509 baris, 37 ujian) |  |
| GRO-016 | Reputation Management | Online Reputation System | TIADA | – |  |
| GRO-017 | Proposal Management | Proposal System | IMPLEMENTED + diuji | SignDocs (1,809 baris, 0 ujian); CRM (2,227 baris, 46 ujian) |  |
| GRO-018 | Customer Experience Management | CXM | TIADA | – |  |
| GRO-019 | Event Marketing Automation | Event Marketing | TIADA | – |  |
| GRO-020 | Conversational Commerce | Conversational Sales | IMPLEMENTED + diuji | Omnichannel (2,050 baris, 35 ujian) |  |

### 3.4 HR — HR & Tenaga Kerja

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| HR-001 | HRMS | Human Resource Management System | IMPLEMENTED (ujian nipis) | HRMS (4,394 baris, 17 ujian) |  |
| HR-002 | HRIS | Human Resource Information System | IMPLEMENTED (ujian nipis) | HRMS (4,394 baris, 17 ujian) |  |
| HR-003 | HCM | Human Capital Management | TIADA | – |  |
| HR-004 | ATS | Applicant Tracking System | TIADA | – |  |
| HR-005 | LMS-LRN | Learning Management System | TIADA | – |  |
| HR-006 | Payroll | Payroll Management | IMPLEMENTED + diuji | Payroll (2,474 baris, 40 ujian) |  |
| HR-007 | T&A | Time & Attendance | IMPLEMENTED (ujian nipis) | HRMS (4,394 baris, 17 ujian) |  |
| HR-008 | ESS | Employee Self-Service | IMPLEMENTED (ujian nipis) | HRMS (4,394 baris, 17 ujian) |  |
| HR-009 | Performance Management | Performance System | TIADA | – |  |
| HR-010 | Compensation Management | Compensation System | TIADA | – |  |
| HR-011 | Skills & Competency Management | Skills Management | TIADA | – |  |
| HR-012 | Employee Engagement | Engagement System | TIADA | – |  |

### 3.5 SUP — Inventori & Rantaian Bekalan

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| SUP-001 | IMS | Inventory Management System | IMPLEMENTED (ujian nipis) | Inventory (629 baris, 12 ujian) | Enjin stok ada; POS potong stok hanya dalam PR #2 |
| SUP-002 | WMS | Warehouse Management System | IMPLEMENTED (ujian nipis) | Logistics (2,050 baris, 0 ujian) |  |
| SUP-003 | SCM | Supply Chain Management | IMPLEMENTED + diuji | SCM (1,613 baris, 26 ujian) |  |
| SUP-004 | MRP | Material Requirements Planning | SEBAHAGIAN | Manufacturing (183 baris, 0 ujian) | BOM/kit ada; MRP penuh belum |
| SUP-005 | SRM | Supplier Relationship Management | IMPLEMENTED + diuji | SCM (1,613 baris, 26 ujian) |  |
| SUP-006 | TMS-LOG | Transportation Management System | IMPLEMENTED (ujian nipis) | Logistics (2,050 baris, 0 ujian) |  |
| SUP-007 | YMS | Yard Management System | TIADA | – |  |
| SUP-008 | Procurement | Procurement Management | IMPLEMENTED + diuji | SCM (1,613 baris, 26 ujian) |  |
| SUP-009 | Fleet Management | Fleet System | IMPLEMENTED + diuji | Fleet (1,086 baris, 26 ujian) |  |
| SUP-010 | Asset Management | Asset Management System | IMPLEMENTED (ujian nipis) | Assets (2,484 baris, 5 ujian) |  |
| SUP-011 | Demand Planning | Demand Planning System | TIADA | – |  |
| SUP-012 | Supplier Portal | Supplier Collaboration Portal | TIADA | – |  |
| SUP-013 | Product Information Management | PIM | TIADA | – |  |

### 3.6 TEC — Teknologi, Data & Integrasi

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| TEC-001 | BI | Business Intelligence | IMPLEMENTED + diuji | BusinessIntel (1,801 baris, 40 ujian); Analytics (2,601 baris, 0 ujian) |  |
| TEC-002 | DWH | Data Warehouse | TIADA | – |  |
| TEC-003 | MDM | Master Data Management | TIADA | – |  |
| TEC-004 | iPaaS | Integration Platform as a Service | IMPLEMENTED + diuji | FlowAutomation (1,592 baris, 21 ujian); McpStudio (1,534 baris, 41 ujian) |  |
| TEC-005 | API Management | API Management System | TIADA | – |  |
| TEC-006 | IAM | Identity & Access Management | IMPLEMENTED (ujian nipis) | app/Http (AuthController, 2FA/TOTP, Sanctum) |  |
| TEC-007 | SIEM | Security Information & Event Management | TIADA | – |  |
| TEC-008 | ITSM | IT Service Management | TIADA | – |  |
| TEC-009 | RPA | Robotic Process Automation | TIADA | – |  |
| TEC-010 | DMS | Document Management System | IMPLEMENTED (ujian nipis) | DocumentAI (1,700 baris, 0 ujian); SignDocs (1,809 baris, 0 ujian) |  |
| TEC-011 | ETL/ELT | Data Integration & Transformation | TIADA | – |  |
| TEC-012 | Data Governance | Data Governance System | TIADA | – |  |
| TEC-013 | Observability | Application Observability | IMPLEMENTED (ujian nipis) | HealthController (/health, /ready, /metrics) |  |
| TEC-014 | Secrets Management | Secrets Vault | TIADA | – |  |
| TEC-015 | Backup & Disaster Recovery | Backup/DR System | TIADA | – |  |
| TEC-016 | Configuration Management | CMDB & Configuration | TIADA | – |  |
| TEC-017 | DevSecOps | DevSecOps Platform | TIADA | – |  |
| TEC-018 | B-Migration Hub | Data Migration & Integration Hub | TIADA | – | Import asas sahaja; templat AutoCount/SQL/Bukku belum |

### 3.7 GRC — Tadbir Urus, Risiko & Pematuhan

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| GRC-001 | GRC | Governance Risk & Compliance | TIADA | – |  |
| GRC-002 | QMS | Quality Management System | TIADA | – |  |
| GRC-003 | EHS | Environment Health & Safety | TIADA | – |  |
| GRC-004 | EMS | Environmental Management System | TIADA | – |  |
| GRC-005 | CLM | Contract Lifecycle Management | IMPLEMENTED (ujian nipis) | SignDocs (1,809 baris, 0 ujian) |  |
| GRC-006 | ERM | Enterprise Risk Management | TIADA | – |  |
| GRC-007 | Audit Management | Audit System | IMPLEMENTED (ujian nipis) | app/Services/AuditLogger |  |
| GRC-008 | Policy Management | Policy System | TIADA | – |  |
| GRC-009 | Privacy Management | Privacy & Data Protection | TIADA | – |  |
| GRC-010 | Regulatory Compliance | Compliance Management | TIADA | – |  |
| GRC-011 | Fraud & AML Monitoring | Financial Crime Monitoring | TIADA | – |  |

### 3.8 MFG — Pembuatan

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| MFG-001 | MES | Manufacturing Execution System | RANGKA | Manufacturing (183 baris, 0 ujian) | Servis 183 baris sahaja |
| MFG-002 | CMMS | Computerized Maintenance Management System | IMPLEMENTED (ujian nipis) | Assets (2,484 baris, 5 ujian) |  |
| MFG-003 | EAM | Enterprise Asset Management | IMPLEMENTED (ujian nipis) | Assets (2,484 baris, 5 ujian) |  |
| MFG-004 | PLM | Product Lifecycle Management | TIADA | – |  |
| MFG-005 | CAD/CAM | Computer-Aided Design/Manufacturing | TIADA | – |  |
| MFG-006 | SCADA | Supervisory Control & Data Acquisition | TIADA | – |  |
| MFG-007 | PLC/Industrial Control | Programmable Logic Control | TIADA | – |  |
| MFG-008 | OEE Analytics | Overall Equipment Effectiveness | TIADA | – |  |
| MFG-009 | QMS Manufacturing | Manufacturing Quality | TIADA | – |  |
| MFG-010 | APS | Advanced Planning & Scheduling | TIADA | – |  |

### 3.9 HLT — Kesihatan

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| HLT-001 | HIS | Hospital Information System | TIADA | – |  |
| HLT-002 | EMR | Electronic Medical Records | TIADA | – |  |
| HLT-003 | EHR | Electronic Health Records | TIADA | – |  |
| HLT-004 | LIS | Laboratory Information System | TIADA | – |  |
| HLT-005 | RIS | Radiology Information System | TIADA | – |  |
| HLT-006 | PACS | Picture Archiving & Communication System | TIADA | – |  |
| HLT-007 | Pharmacy Management | Pharmacy System | TIADA | – |  |
| HLT-008 | Practice Management | Clinic Practice Management | TIADA | – |  |
| HLT-009 | Telehealth | Telemedicine Platform | TIADA | – |  |
| HLT-010 | RCM | Revenue Cycle Management | TIADA | – |  |

### 3.10 HOS — Hospitaliti & F&B

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| HOS-001 | Restaurant Management | Restaurant Operations System | IMPLEMENTED + diuji | POS (5,193 baris, 130 ujian) |  |
| HOS-002 | KDS | Kitchen Display System | IMPLEMENTED + diuji | POS (5,193 baris, 130 ujian) |  |
| HOS-003 | Recipe & Food Cost | Recipe Management | IMPLEMENTED (ujian nipis) | COGS (1,162 baris, 0 ujian); Manufacturing (183 baris, 0 ujian) |  |
| HOS-004 | Hotel PMS | Property Management System - Hotel | TIADA | – |  |
| HOS-005 | Channel Manager | Hotel Channel Management | TIADA | – |  |
| HOS-006 | Revenue Management Hotel | Hotel Revenue Management | TIADA | – |  |
| HOS-007 | Housekeeping Management | Housekeeping System | TIADA | – |  |
| HOS-008 | Food Safety & HACCP | Food Safety Management | TIADA | – |  |

### 3.11 RET — Runcit & E-dagang

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| RET-001 | E-commerce Platform | Commerce System | IMPLEMENTED + diuji | Site (4,015 baris, 0 ujian); Marketplace (3,345 baris, 46 ujian) |  |
| RET-002 | Marketplace Management | Marketplace Integration | IMPLEMENTED + diuji | Marketplace (3,345 baris, 46 ujian) |  |
| RET-003 | PIM | Product Information Management | TIADA | – |  |
| RET-004 | Retail Merchandising | Merchandising System | TIADA | – |  |
| RET-005 | Returns & Warranty | Returns Management | IMPLEMENTED + diuji | POS (5,193 baris, 130 ujian); Orders (1,664 baris, 146 ujian) |  |
| RET-006 | Subscription Commerce | Subscription Commerce | IMPLEMENTED + diuji | Billing (2,233 baris, 40 ujian) |  |

### 3.12 CON — Pembinaan

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| CON-001 | Construction Management | Construction Project System | TIADA | – |  |
| CON-002 | BIM | Building Information Modeling | TIADA | – |  |
| CON-003 | Estimating & BOQ | Construction Estimating | TIADA | – |  |
| CON-004 | Site Management | Construction Site Management | TIADA | – |  |
| CON-005 | Project Controls | Project Controls System | TIADA | – |  |
| CON-006 | Facilities Management | Facilities Management System | TIADA | – |  |

### 3.13 LOG — Pengangkutan

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| LOG-001 | Transport Management | Transport Operations System | IMPLEMENTED + diuji | Fleet (1,086 baris, 26 ujian) |  |
| LOG-002 | Fleet Telematics | Telematics System | TIADA | – |  |
| LOG-003 | Last Mile Delivery | Last Mile System | IMPLEMENTED + diuji | Cod (1,046 baris, 37 ujian); Logistics (2,050 baris, 0 ujian) |  |
| LOG-004 | Freight Forwarding | Freight Management | TIADA | – |  |
| LOG-005 | Passenger Booking & Dispatch | Passenger Transport System | TIADA | – |  |

### 3.14 EVT — Acara, Tempahan & Servis

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| EVT-001 | Event Management | Event Operating System | TIADA | – |  |
| EVT-002 | Venue Management | Venue System | TIADA | – |  |
| EVT-003 | Ticketing & Registration | Ticketing System | TIADA | – |  |
| EVT-004 | Rental Management | Rental System | TIADA | – |  |
| EVT-005 | Appointment Booking | Booking System | IMPLEMENTED (ujian nipis) | Booking (2,179 baris, 0 ujian) |  |
| EVT-006 | Field Service Management | FSM | IMPLEMENTED + diuji | ServiceOS (3,148 baris, 63 ujian) |  |
| EVT-007 | Property Management - Real Estate | Property Management System | TIADA | – |  |
| EVT-008 | Learning & Student Information | SIS | TIADA | – |  |
| EVT-009 | Learning Management - Education | LMS Education | TIADA | – |  |
| EVT-010 | Library Management | Library System | TIADA | – |  |
| EVT-011 | Professional Services Automation | PSA | TIADA | – |  |
| EVT-012 | Membership Management | Membership System | IMPLEMENTED (ujian nipis) | Loyalty (2,963 baris, 1 ujian) |  |

### 3.15 CORE — Platform Teras

| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |
|---|---|---|---|---|---|
| CORE-001 | OAM | Organization & Access Management | SEBAHAGIAN | app/Http/Middleware/ResolveTenant + ahli/peranan (members, invitations) | Ahli + peranan ada; hierarki cawangan/jabatan hampir tiada |
| CORE-002 | Subscription & Entitlement | Subscription Management | IMPLEMENTED + diuji | Entitlement (1,310 baris, 94 ujian); Billing (2,233 baris, 40 ujian) |  |
| CORE-003 | Notification Management | Notification System | IMPLEMENTED + diuji | Reminder (1,206 baris, 33 ujian); Blast (2,256 baris, 22 ujian) |  |
| CORE-004 | Workflow & Approval | Approval Management | IMPLEMENTED + diuji | WorkflowStudio (909 baris, 30 ujian) |  |
| CORE-005 | AI Gateway | AI Model Gateway | IMPLEMENTED + diuji | AIGateway (1,704 baris, 48 ujian) |  |
| CORE-006 | AI Agent Management | Agent Orchestration | IMPLEMENTED + diuji | AIAgents (2,215 baris, 0 ujian); AgentStudio (1,271 baris, 37 ujian); ADE (3,160 baris, 48 ujian); TeamAI (450 baris, 11 ujian) |  |
| CORE-007 | Knowledge Management | Knowledge Management System | IMPLEMENTED (ujian nipis) | BrainHub (2,304 baris, 0 ujian) |  |
| CORE-008 | Master Data & Reference Data | Reference Data Service | TIADA | – |  |
| CORE-009 | Audit & Activity Log | Audit Management Service | IMPLEMENTED (ujian nipis) | app/Services/AuditLogger |  |
| CORE-010 | Connector Registry | Connector Management | TIADA | – |  |
| CORE-011 | Data Quality Management | Data Quality System | TIADA | – |  |
| CORE-012 | Customer Identity & Consent | CIAM/Consent | TIADA | – |  |
| CORE-013 | Search & Discovery | Enterprise Search | TIADA | – |  |
| CORE-014 | Localization & Tax Engine | Localization Service | TIADA | – |  |
| CORE-015 | File & Object Storage | File Storage Service | IMPLEMENTED (ujian nipis) | Laravel Storage |  |

## 4. Modul kod di luar registry 179

Modul ini wujud dalam kod tetapi belum dipetakan ke mana-mana ID canonical. Cadangan: beri ID baharu (cth. `CORE-AI-*` untuk AI eksekutif) atau gabung sebagai capability.

| Modul | Baris PHP | Kaedah ujian | Laluan API (fail modul) |
|---|---:|---:|---:|
| Boardroom | 1,160 | 22 | 0 |
| Community | 612 | 42 | 8 |
| ExecutiveAI | 863 | 16 | 7 |
| Fundamental | 1,215 | 44 | 11 |
| IndustryPacks | 1,032 | 0 | 0 |
| Link | 1,159 | 0 | 0 |
| MediaEditor | 721 | 36 | 13 |
| ProofOfValue | 821 | 27 | 9 |
| Vision | 718 | 27 | 0 |

## 5. Peta Blueprint v9.x (PART) → domain

| PART | Tajuk | Domain / registry |
|---|---|---|
| PART I | VISI PRODUK DAN SENI BINA | Identiti, seni bina |
| PART II | SHARED CORE | CORE (OAM, entitlement, harga) |
| PART III | BUSINESS MODULES | BOS/FIN/GRO/HR/SUP modul perniagaan |
| PART IV | AI ENGINE & PERSONAL ASSISTANTS | CORE-005/006/007 AI |
| PART V | INTEGRATIONS & AUTOMATION | TEC-004/018 integrasi & migrasi |
| PART VI | IMPLEMENTATION & ROADMAP | Roadmap |
| PART VII | OPERATIONS, DEPLOYMENT & GOVERNANCE | TEC/GRC operasi & tadbir urus |
| PART VIII | CUSTOMIZATION, EXTENSIBILITY & FUTURE ROADMAP | Kebolehlanjutan |
| PART IX | COLLECTIVE INTELLIGENCE & PLATFORM EVOLUTION | CORE-007 kecerdasan kolektif |
| PART X | GLOBAL EXPANSION & MARKET DOMINANCE | Pengembangan pasaran |
| PART XI | COMMERCE & MARKETING ENHANCEMENTS | GRO/RET dagang & pemasaran |
| PART XII | KETAHANAN OPERASI, KESELAMATAN AI & PERTUMBUHAN | TEC-013/015, keselamatan AI |
| PART XIII | ORKESTRASI EJEN & TADBIR URUS KEPUTUSAN | CORE-006 orkestrasi ejen |
| PART XIV | LEARNING SIGNAL & EVOLUSI PRODUK | Pembelajaran produk |
| PART XV | DESIGN SYSTEM & INTERAKSI | Design system (UI) |
| PART XVI | E-MEL & PENGAMBILAN PEKERJA | CORE-003 e-mel, HR-004 pengambilan |
| PART XVII | KELEBIHAN KOMPETITIF & ENJIN PERTUMBUHAN | Strategi pesaing (BAB 81) |
| PART XVIII | PEMBANTU EKSEKUTIF MULTIMODAL & ENJIN JUALAN | B-Executive AI (BAB 82, v8.5) |
| PART XIX | MASTERPIECE: EVOLUSI MENJADI PLATFORM SAAS UNIVERSAL | Platform SaaS universal |
| PART XX | REVERSE ENGINEERING: 200 APPS + 50 PROJEK OSS | Kajian 200 app + 50 OSS |
| PART XXI | IMPLEMENTASI: 5 MODUL BAHARU DARIPADA AUDIT | Modul COD/Fleet/PoV/MCP/AgentStudio |
| PART XXII | EKOSISTEM OPERASI: 9 MODUL BAHARU + ADE | Orders, Seo, ContentStudio, ADE dll. |
| PART XXIII | INTEGRASI B-POS SYSTEM | BOS-006 POS (B-POS) |
| PART XXIV | AI FINANCE & EXPENSE INTELLIGENCE | FIN-009 Expense, AI Finance |
| PART XXV | DOKUMEN PELANGGAN & KECERDASAN VISUAL | Resit/penyata, Vision AI |

## 6. Seni bina, OAM, Migration Hub, tadbir urus AI

Kandungan rasmi kekal dalam Master Inventory (tidak disalin semula supaya tiada dua versi):

- Inventory §5 — Audit pertindihan (17 isu) & keputusan canonical
- Inventory §6 — Domain boundaries & system-of-record
- Inventory §7 — Aliran transaksi (POS→OMS→Inventori→Kewangan; CRM→CPQ→CLM→Billing; Pembuatan)
- Inventory §8 — OAM: hierarki tenant → syarikat → cawangan → jabatan → pasukan; RBAC+ABAC; peranan standard
- Inventory §9 — B-Migration & Integration Hub
- Inventory §10 — Kontrak API & event
- Inventory §11 — AI Intelligence & governance
- Inventory §12 — Pakej komersial
- Inventory §13 — Implementation governance & Definition of Done

Spesifikasi terperinci yang berkaitan dalam Blueprint: PART II (Shared Core), PART V (Integrasi & Migrasi), PART XIII (Orkestrasi Ejen).

## 7. Roadmap MVP (dipersetujui 31/10/2026)

Owner tiada pilihan khusus → ikut cadangan: **MVP-1 dulu**, vertical **F&B/runcit (kedai bunga owner)**, gateway **CHIP**. Polisi stok: jualan melebihi stok **dibenarkan + ditanda untuk semakan**.

| MVP | Sistem | Status 31/10 |
|---|---|---|
| **1. Kedai berniaga penuh** | BOS-006 POS, HOS-001/002, SUP-001 potong stok, FIN-016 e-Invois, FIN-008 CHIP/FPX, CORE-003 resit WhatsApp, CORE-001 skop cawangan | POS + offline + DuitNow + QR meja + potong stok: **siap (PR #2)**. e-Invois, CHIP pelanggan, WhatsApp, cawangan: belum |
| 2. Tukar tanpa sakit | TEC-018 B-Migration Hub, CORE-010 | Belum |
| 3. Kewangan | FIN-011 GL auto-jurnal, FIN-004 AR + ejen kutip, FIN-017 | Belum |
| 4. HR SME | HR-006 Payroll statutori MY, HR-007 | Payroll ada (ujian sederhana); perlu semakan profesional |
| Ditangguh | HLT, HOS-004..007, CON, EVT-008..010, CDP/DMP/MDM | PLANNED |

## 8. Kualiti & status repo (31/10/2026)

- Suite ujian (PR #2, SQLite, PHP 8.4): **1,372 ujian, 3 gagal** (FilamentPanelTest — ext-intl tiada dalam sandbox).
- `main` sebelum PR #2: suite tidak boleh jalan (26 migrasi bayangan), laluan modul POS/Booking/CRM/Payroll/DocumentAI/VirtualOffice tanpa auth+tenant.
- CI GitHub Actions: **tidak berjalan** — akaun dikunci kerana isu bil.
- Dokumen: 167 fail `.md` dalam repo; 24 kumpulan pendua tepat (≈4.4 MB) — lihat indeks.

## 9. Cara kemas kini dokumen ini

1. Sistem baharu → tambah dalam Master Inventory dengan ID canonical; jangan ubah ID lama.
2. Status → jana semula §3 dari kod (skrip penjana dalam PR ini), jangan taip tangan.
3. Keputusan owner → pindah dari §2 ke bahagian berkaitan dengan tarikh.
4. Dokumen lama → arkib; jangan sunting blueprint lama untuk keputusan baharu.

## 10. Audit Odoo: 50 app rasmi & reverse engineering

**Sumber (disemak 31/10/2026):**
- Senarai rasmi app: [odoo.com/page/all-apps](https://www.odoo.com/page/all-apps), 50 app dalam 9 kategori.
- Ciri terkini: [Odoo 19 Release Notes (Sept 2025)](https://www.odoo.com/odoo-19-release-notes) dan [Odoo 20 Release Notes (Sept 2026)](https://www.odoo.com/odoo-20-release-notes).
- Harga: [odoo.com/pricing](https://www.odoo.com/pricing). Standard € 19.90–24.90/pengguna/bulan; Custom € 35.90–44.90/pengguna/bulan (Studio, **Agentic AI**, multi-company, external API). Ciri AI memerlukan **kredit IAP** berasingan.

Lajur "AI Bijan sekarang" diambil daripada §3 (status kod). "Jadikan lebih power" ialah cadangan reverse engineering: ambil apa yang Odoo dah buktikan, kemudian tambah apa yang Odoo **tiada** (ejen AI yang bertindak dengan kelulusan, pematuhan Malaysia, WhatsApp-first, migrasi).

### 10.1 Pelajaran utama daripada Odoo

1. **Satu platform, banyak app, satu data.** Odoo menang kerana semua app berkongsi pelanggan, produk dan perakaunan. AI Bijan dah ikut model ni (modular monolith); kekalkan dan jangan pecah.
2. **"One App Free" sebagai pintu masuk.** Pelanggan mula dengan satu app, kemudian bertambah. AI Bijan boleh tiru melalui Super Admin (§2, keputusan harga): pakej satu modul percuma atau murah, contohnya POS sahaja.
3. **AI kini ciri asas, bukan tambahan.** Odoo 20 ada ejen yang cipta dan kemas kini rekod, MCP, *skills*, pemilihan model automatik dan suara. **Bezanya:** di Odoo, Agentic AI hanya dalam pakej Custom dan kredit dibayar berasingan. Di AI Bijan, ejen patut jadi teras dalam semua pakej, dengan dompet kredit yang telus (CORE-005).
4. **Lokalisasi menjual.** Odoo 20 tambah QRIS (Indonesia), FBR (Pakistan), SUNAT (Peru), GoFood/GrabFood. AI Bijan perlu lokalisasi Malaysia: MyInvois, DuitNow, FPX, EPF/SOCSO/EIS/PCB, SST dan GrabFood/Foodpanda.
5. **Studio (tanpa kod) mengunci pelanggan.** Pelanggan yang dah ubah medan dan aliran kerja susah nak pindah. AI Bijan: WorkflowStudio + AgentStudio, dan biar ejen AI yang bina perubahan tu daripada ayat biasa.
6. **Kelemahan Odoo yang jadi peluang AI Bijan:** pelaksanaan mahal (perlukan rakan/konsultan), UI padat untuk pengguna bukan teknikal, AI bergantung pada kredit IAP, lokalisasi MY lemah, dan WhatsApp sebagai saluran sampingan.

### 10.2 Jadual audit 50 app

| # | Kategori | App Odoo | Ciri utama Odoo (19/20) | AI Bijan sekarang (ID · modul · status) | Jurang | Jadikan lebih power (AI Bijan) | Keutamaan |
|---|---|---|---|---|---|---|---|
| 1 | Laman web | Website | Pembina laman; AI Website Assistant (edit blok, cipta halaman/borang, salin halaman dari URL), llms.txt, SEO berstruktur | GRO-005 · Site · diuji | AI assistant, salin halaman dari URL | Ejen "bina laman daripada perbualan WhatsApp/foto kedai"; templat industri MY | P2 |
| 2 | Laman web | eCommerce | Susun atur /shop, Click & Collect, COD semua kaedah, AI sunting produk, cross-sell, minta ulasan | RET-001 · Site+Marketplace · diuji | Click & Collect, cross-sell automatik | Satu stok dengan POS (PR #2), pesan WhatsApp → bayar DuitNow/FPX | P2 |
| 3 | Laman web | Blog | Reka bentuk semula, cadangan pos, jadual pos | GRO-005/014 · Site, ContentStudio · diuji | – | Ejen kandungan tulis + jadual + SEO serentak | P3 |
| 4 | Laman web | Forum | Q&A, FAQ | Community · diuji (luar registry) | Lencana/reputasi | Jawapan dicadang AI daripada pangkalan pengetahuan | P4 |
| 5 | Laman web | eLearning | Kursus, kuiz, portal pelanggan | EVT-009/HR-005 · tiada | Tiada | Latihan staf ringkas (video pendek + kuiz) untuk SOP kedai | P4 |
| 6 | Laman web | Live Chat | Chatbot, Messenger/Instagram DM, ejen AI livechat + jana lead | GRO-012 · Omnichannel+LiveSupport · diuji | Ejen AI jana lead | **WhatsApp-first** inbox + ejen AI jawab, tempah, buat sebut harga | **P1** |
| 7 | Jualan | CRM | Kebarangkalian menang (AI), imbas kad nama, agihan lead berputar, Dun & Bradstreet | BOS-001 · CRM · diuji | Skor AI, imbas kad | Ejen susulan lead automatik di WhatsApp dengan kelulusan | P2 |
| 8 | Jualan | Sales | Templat sebut harga, komisen, harga berkala, diskaun sekali guna | GRO-010 · Invoicing+CRM · diuji | Komisen jualan, harga berjadual | Sebut harga dari suara/WhatsApp → PDF → e-tandatangan | P2 |
| 9 | Jualan | Point of Sale | Kedai & restoran; GoFood/GrabFood, self-order QR, alergen, kursus, berbilang mata wang, pencetak per stesen | BOS-006/HOS-001/002 · POS · diuji (PR #2: offline, DuitNow, QR meja, potong stok) | GrabFood/Foodpanda, pencetak per stesen, alergen | Ejen kaunter: ringkasan tutup kedai di WhatsApp, cadang pesanan semula stok | **P1** |
| 10 | Jualan | Subscriptions | Invois berulang, pembaharuan, peraturan kesetiaan | RET-006 · Billing · diuji | Langganan untuk **pelanggan tenant** (bukan langganan AI Bijan) | Langganan pelanggan kedai (contohnya bunga mingguan) + bayaran auto | P3 |
| 11 | Jualan | Rental | Kontrak sewa, papan pemuka, harga ikut pricelist | EVT-004 · tiada | Tiada | Pakej industri sewa (kereta, peralatan acara) | P4 |
| 12 | Kewangan | Accounting | Rekonsiliasi bank pintas, penyata cukai, AI financial insights, ramalan baris bil, peraturan padanan | FIN-001/011 · Finance · diuji | Auto-jurnal dari POS/invois, rekon bank | Ejen CFO harian (§11) + jurnal automatik + rekon bank DuitNow/FPX | **P1** |
| 13 | Kewangan | Invoicing | Invois, bayaran, susulan | FIN-004/016 · Invoicing · diuji; **e-Invois RANGKA** | MyInvois sebenar | e-Invois LHDN automatik + ejen kutip hutang WhatsApp | **P1** |
| 14 | Kewangan | Expenses | Laporan gabungan, had per jawatan, imbas resit (IAP) | FIN-009 · Expense · diuji | Imbas resit AI | Snap resit di WhatsApp → tuntutan → kelulusan | P2 |
| 15 | Kewangan | Documents | Akses fail/folder, AI susun & namakan, cipta jurnal dari dokumen | TEC-010 · DocumentAI · ujian nipis | Susun automatik, dokumen → jurnal | Peti masuk dokumen: emel/WhatsApp → AI baca → rekod draf | P2 |
| 16 | Kewangan | Spreadsheets | Hamparan terbina, carta, penapis global | TEC-001 · BusinessIntel · diuji | Hamparan dalam app | Tanya data dalam BM ("jualan Disember ikut cawangan") → carta | P3 |
| 17 | Kewangan | Sign | Tandatangan dari aktiviti, templat, itsme® (EU) | GRC-005 · SignDocs · ujian nipis | Penandatangan dinamik | Tandatangan di WhatsApp + jejak audit | P3 |
| 18 | Kewangan | ESG | Jejak karbon GHG Protocol, faktor emisi (AI), metrik sosial | GRC-003/004 · tiada | Tiada | Tangguh: laporan ESG ringkas untuk vendor korporat (Bursa) | P4 |
| 19 | Rantaian bekalan | Inventory | Penilaian stok, peraturan pesanan semula + horizon, pakej dalam pakej, notifikasi WhatsApp | SUP-001 · Inventory · ujian nipis (+PR #2 potong stok POS) | Pesanan semula automatik, kiraan stok | **Ejen stok**: ramal habis → draf PO → minta kelulusan | **P1** |
| 20 | Rantaian bekalan | Manufacturing | Gantt MO, kos operasi, subkontrak, pengeluaran berterusan | MFG-001/SUP-004 · Manufacturing · RANGKA (BOM/kit ada) | MO penuh, kerja | Resipi/kit F&B + kos bahan (COGS) dulu, MES kemudian | P3 |
| 21 | Rantaian bekalan | PLM | Versi produk, ECO | MFG-004 · tiada | Tiada | Tangguh | P5 |
| 22 | Rantaian bekalan | Purchase | Incoterm, tarikh jangka, kadar kualiti vendor, perjanjian | SUP-008 · SCM · diuji | Kualiti vendor | PO dijana ejen stok; perbandingan harga vendor | P2 |
| 23 | Rantaian bekalan | Maintenance | Permintaan, Gantt, peringkat per pasukan | MFG-002/SUP-010 · Assets · ujian nipis | Penyelenggaraan berjadual | Peringatan servis aset/kenderaan di WhatsApp | P3 |
| 24 | Rantaian bekalan | Quality | Pemeriksaan, lokasi kegagalan | GRC-002 · tiada | Tiada | Senarai semak kebersihan/HACCP F&B (HOS-008) | P4 |
| 25 | HR | Employees | Rekod pekerja, kontrak berversi, kerja jauh | HR-001/002 · HRMS · ujian nipis | Versi kontrak | Onboarding staf ikut WhatsApp | P2 |
| 26 | HR | Recruitment | Skor padanan, talent pool, kempen | HR-004 · tiada | Tiada | Ejen saring calon + iklan kerja (Blueprint PART XVI) | P3 |
| 27 | HR | Time Off | Peruntukan & permohonan cuti | HR-001 · HRMS · ujian nipis | – | Mohon cuti di WhatsApp, lulus satu ketik | P2 |
| 28 | HR | Appraisals | Kempen, matlamat, maklum balas 360 | HR-009 · tiada | Tiada | Tangguh | P4 |
| 29 | HR | Payroll | Pay run berpandu, indeks gaji, simulasi net-ke-gross | HR-006 · Payroll · ujian nipis | Statutori MY disahkan | EPF/SOCSO/EIS/PCB + fail bank + slip WhatsApp (perlu semakan profesional) | P2 |
| 30 | HR | Referral | Rujukan pekerja | HR-004 · tiada | Tiada | Tangguh | P5 |
| 31 | HR | Fleet | Kenderaan, kos, pemandu auto dari Payroll | SUP-009 · Fleet · diuji | – | Kekal | P4 |
| 32 | Pemasaran | Marketing Automation | Aliran drag-and-drop, cetus dari event/webhook, AI bantu sediakan kempen | GRO-002 · Marketing+FlowAutomation · diuji | Pembina visual | "Terangkan kempen dalam BM" → ejen bina aliran → owner lulus | P2 |
| 33 | Pemasaran | Email Marketing | Blok bersyarat, senarai dinamik, templat | GRO-002 · Marketing+Blast · diuji | – | Utamakan WhatsApp; emel sebagai saluran kedua | P3 |
| 34 | Pemasaran | SMS Marketing | Reka, hantar, jejak SMS | GRO-002/CORE-003 · Blast · diuji | – | Kekal; WhatsApp didahulukan | P4 |
| 35 | Pemasaran | Social Marketing | Story FB/IG, jadual per platform | GRO-014 · ContentStudio · diuji | Story | Ejen kandungan: jana dari produk + jadual (gaya Repliz) | P2 |
| 36 | Pemasaran | Events | Tiket combo, jualan tiket | EVT-001/003 · tiada | Tiada | Pakej acara (tiket QR) kemudian | P4 |
| 37 | Pemasaran | Survey | Tinjauan, kuiz langsung | GRO-018 · tiada | Tiada | Maklum balas pelanggan lepas beli (WhatsApp 1–5 bintang) | P3 |
| 38 | Servis | Project | Templat projek & tugas, peranan, Gantt portal | BOS-008 · Projects · ujian nipis | Templat | Ejen pecah projek daripada brief | P3 |
| 39 | Servis | Timesheet | Jejak masa per tugas | BOS-008 · Projects · ujian nipis | – | Masa auto dari aktiviti | P4 |
| 40 | Servis | Field Service | Lokasi teknisyen langsung, sejarah peralatan pelanggan, laluan optimum, yuran perjalanan | EVT-006 · ServiceOS · diuji | Laluan & lokasi langsung | Jadual + laluan + laporan kerja + bayar di lokasi (DuitNow) | P3 |
| 41 | Servis | Helpdesk | AI kesan tiket serupa & cadang jawapan, peringatan tutup auto | GRO-011 · Helpdesk · ujian nipis | Cadangan AI | Ejen sokongan: jawab draf dari pangkalan pengetahuan, eskalasi | P2 |
| 42 | Servis | Planning | Auto-rancang syif, Gantt berbilang hari, hantar jadual ikut WhatsApp | BOS-009 · tiada (HRMS sebahagian) | Jadual syif | Jadual syif staf kedai + tukar syif di WhatsApp | P2 |
| 43 | Servis | Appointments | Kapasiti tempahan, produk aksesori, maklum balas | EVT-005 · Booking · ujian nipis | Kapasiti/aksesori | Tempah melalui WhatsApp + deposit FPX + peringatan | P2 |
| 44 | Produktiviti | Discuss | Sembang, transkrip panggilan, undian | VirtualOffice · ujian nipis | Transkrip | Pejabat Maya + ringkasan mesyuarat AI | P4 |
| 45 | Produktiviti | Approvals | Permohonan & kelulusan | CORE-004 · WorkflowStudio · diuji | – | **Peti kelulusan tunggal** untuk semua cadangan ejen AI (§11) | **P1** |
| 46 | Produktiviti | Internet of Things | Kotak IoT: pencetak, penimbang, laci | TNG Soundbox + WebUSB ESC/POS (POS) | Penimbang, paparan pelanggan | WebUSB/WebSerial tanpa kotak IoT (lebih murah) | P3 |
| 47 | Produktiviti | VoIP | Aliran panggilan, softphone, log panggilan | GRO-012 · tiada | Tiada | Tangguh; WhatsApp call log kemudian | P5 |
| 48 | Produktiviti | Knowledge | Artikel, templat, carian | CORE-007 · BrainHub · tiada ujian | Pangkalan pengetahuan | SOP kedai = sumber ejen (RAG) | P2 |
| 49 | Produktiviti | AI | Ejen bertindak (cipta/kemas kini rekod), MCP, skills, suara, kredit IAP | CORE-005/006 · AIGateway, AIAgents, AgentStudio, McpStudio, ExecutiveAI · **alat ejen = stub (lihat §11.2)** | Alat sebenar, kelulusan, eval | §11 Pelaksanaan Agentic AI | **P1** |
| 50 | Penyesuaian | Studio | Medan, borang, laporan, automasi tanpa kod | WorkflowStudio, AgentStudio · diuji | Medan/borang tersuai | "Studio AI": ubah borang/aliran dengan ayat BM, pratonton, kemudian lulus | P3 |

**Keutamaan:** P1 = MVP-1 (kedai berniaga penuh); P2 = dalam 6 bulan; P3 = kemudian; P4/P5 = tangguh, ikut permintaan pelanggan berbayar.

### 10.3 Di mana AI Bijan boleh menang terhadap Odoo

| Dimensi | Odoo | AI Bijan (sasaran) |
|---|---|---|
| Harga | Per pengguna (€ 19.90–44.90/pengguna/bulan) + kredit AI IAP | Harga rata per syarikat, pengguna tanpa had (Super Admin tetapkan) |
| Agentic AI | Hanya pakej Custom | Teras dalam semua pakej; dompet kredit telus |
| Saluran utama | Emel / portal | **WhatsApp-first** (resit, kelulusan, laporan, tempahan) |
| Pematuhan MY | Lokalisasi rakan kongsi | MyInvois, DuitNow, FPX, EPF/SOCSO/EIS/PCB, SST terbina |
| Pelaksanaan | Rakan/konsultan, minggu–bulan | Onboarding < 30 min oleh ejen penyediaan + B-Migration Hub |
| UI | Padat, untuk pengguna terlatih | Mobile-first, BM, satu tindakan utama per skrin (§12) |
| Migrasi | Import CSV | Templat AutoCount/SQL/Bukku/Shopee + jalan selari |

## 11. Pelaksanaan Agentic AI

**Matlamat:** ejen AI yang **benar-benar menjalankan kerja perniagaan** (bukan sekadar chatbot), selamat, boleh diaudit, dan sentiasa minta kelulusan untuk perkara berisiko. Sejajar dengan Master Inventory §11 (AI governance) dan Blueprint BAB 82 (tahap autonomi L0–L4).

### 11.1 Apa yang sudah ada dalam kod (disemak 31/10/2026)

| Lapisan | Modul / fail | Status |
|---|---|---|
| Gateway model AI | `AIGateway`: `GatewayService`, `ModelCatalogService`, `WalletService` (dompet kredit), `PiiRedactor`, `PromptGuard` | Ada, diuji |
| Orkestrasi ejen | `AIAgents`: `AgentOrchestrator`, `AgentService`, `AgentMemoryManager`, `AgentGovernance`, `ToolExecutor` | Ada; **alat stub** (lihat 11.2) |
| Pembina ejen | `AgentStudio`: `AgentBlueprintService` | Ada, diuji |
| MCP | `McpStudio`: `McpServerService` (initialize, tools/list, tools/call), `McpToolRegistry` | Ada, diuji |
| Autonomi | `ExecutiveAI`: `AutonomyService`, `ExecutivePreference.autonomy_level` | Ada, ujian sederhana |
| Aliran kerja & kelulusan | `WorkflowStudio`: `WorkflowEngine`; `POS/DiscountApprovalService` | Ada |
| Pengetahuan | `BrainHub`: insight, ramalan, cadangan | Ada, tiada ujian |
| Ejen pembangunan | `ADE`: koordinator, sandbox, semakan | Ada, diuji |
| Kredit & harga | `token_packages` + Super Admin (Pakej & Harga) | Ada (PR #2) |

### 11.2 Jurang kritikal (jujur)

**Semua 67 alat ejen dalam `AIAgents/Services/ToolExecutor.php` ialah stub** yang memulangkan keputusan tetap tanpa membuat apa-apa (contohnya `email.send` → `['email_sent' => true]`). Semakan lanjut (31/10/2026): stub ini **tidak boleh dicapai** kerana `toolExists()` membaca config `ai_agents.tools` yang tidak wujud, jadi setiap panggilan gagal dengan "Tool does not exist". Kesannya:
- Sebelum Fasa 1.1, ejen **tiada satu pun alat yang berfungsi**; mereka hanya boleh berbual.
- Jika config itu ditambah tanpa membetulkan pengendali, ejen akan terus mengaku "berjaya" untuk tindakan yang tidak berlaku. Ini lebih bahaya daripada gagal.

**Status Fasa 1.1 (cawangan `feat/agentic-asas`):** 6 alat sebenar (`AIAgents/Tools`) dengan kebenaran pengguna, Peti Kelulusan untuk risiko tinggi, pengesahan hasil dan audit; pengendali stub kini melontar `not_implemented`.

**Tindakan wajib sebelum mana-mana ejen dibuka kepada pelanggan:**
1. Alat stub mesti **gagal secara jelas** (`not_implemented`), bukan pulangkan "berjaya".
2. Setiap alat sebenar memanggil **servis domain sedia ada** (contohnya `SaleService`, `InvoiceService`), bukan menulis DB terus (peraturan repo: "AI agents use tools/API with limited permissions — no direct DB writes").
3. Setiap alat ada **ujian**, termasuk ujian bahawa ejen tak boleh melepasi kebenaran pengguna.

### 11.3 Seni bina sasaran

```text
Pengguna (WhatsApp / app / suara)
        │
        ▼
 Gateway AI (pilih model, PromptGuard, PiiRedactor, potong kredit dompet)
        │
        ▼
 Orkestrator ejen ──► Skill (arahan + senarai alat, versi)  ◄── AgentStudio / Studio AI
        │
        ▼
 Pendaftar Alat (satu sumber: dijana daripada servis domain + OpenAPI)
   - setiap alat: nama, skema input, kebenaran OAM (module.resource.action),
     tahap risiko, perlu kelulusan?, idempotency key, kos anggaran
        │
        ├── risiko RENDAH (baca, draf) ──► laksana terus
        └── risiko TINGGI (wang, harga, hantar keluar, padam, data pelanggan)
                 ──► Peti Kelulusan (CORE-004) ──► owner lulus di app/WhatsApp ──► laksana
        │
        ▼
 Audit + pengesahan hasil (semak rekod benar-benar berubah) + log kos
        │
        ▼
 MCP server (McpStudio) — alat yang sama boleh dipakai Claude/ChatGPT luar, ikut kebenaran yang sama
```

**Peraturan teras:**
- **Satu pendaftar alat** untuk orkestrator dalaman dan MCP luaran, jadi tiada dua set kebenaran.
- Ejen ialah **identiti servis** dengan tenant, kebenaran, kuota dan audit sendiri (Inventory §8). Ejen tak pernah lebih berkuasa daripada pengguna yang memanggilnya.
- **Pagar owner dikuatkuasa dalam kod**, bukan dalam prompt: keluar duit/ubah harga, padam data, hantar ke luar, dan sentuh data pelanggan **sentiasa** melalui Peti Kelulusan.
- **Pengesahan hasil:** selepas alat jalan, sistem membaca semula rekod untuk sahkan perubahan berlaku, sebelum ejen boleh lapor "siap".
- **Kredit telus:** setiap tindakan ejen menunjukkan anggaran kredit sebelum dijalankan; dompet tak boleh negatif.

### 11.4 Tahap autonomi (L0–L4, Blueprint BAB 82)

| Tahap | Ejen boleh | Contoh |
|---|---|---|
| L0 Nasihat | Jawab & terangkan sahaja | "Kenapa jualan turun minggu ni?" |
| L1 Draf | Sediakan draf; manusia hantar | Draf mesej kutip hutang |
| L2 Laksana risiko rendah | Tindakan dalaman boleh undur | Cipta tugas, tag pelanggan, jadual peringatan |
| L3 Laksana dengan kelulusan | Tindakan berisiko selepas owner lulus | Hantar PO ke vendor, hantar 200 mesej WhatsApp |
| L4 Autonomi berpagar | Laksana dalam had yang owner tetapkan | Pesan semula stok bawah RM500 dari vendor yang diluluskan |

Tahap ditetapkan **per ejen per syarikat** (`ExecutivePreference.autonomy_level`); lalai = L1.

### 11.5 Ejen pertama (ikut MVP)

| Ejen | Kerja | Alat utama | Tahap lalai | Metrik kejayaan |
|---|---|---|---|---|
| **Ejen Penyediaan** | Sediakan akaun dalam < 30 min: jenis bisnes, produk (dari foto/menu/Excel), kaunter, cukai | Import produk, cipta kaunter, tetapan cukai | L2 | Masa ke jualan pertama |
| **Ejen Kaunter** | Laporan tutup kedai, kesan jualan pelik, cadang promosi | Ringkasan POS, semakan stok | L1 | Laporan harian dibaca |
| **Ejen Stok** | Ramal stok habis, draf PO, semak "Semakan Stok" | Ramalan, cipta draf PO | L3 | Stok habis berkurang |
| **Ejen Kutip Hutang** | Senarai invois lewat, draf mesej sopan, hantar selepas lulus | AR, WhatsApp | L3 | Hari kutipan (DSO) turun |
| **Ejen e-Invois** | Semak invois sebelum hantar MyInvois, betulkan ralat biasa | Validasi, hantar LHDN | L3 | Kadar penolakan LHDN |
| **Ejen CFO Harian** | Ringkasan tunai, untung kasar, amaran perbelanjaan | GL, laporan | L0 | Digunakan setiap hari |
| **Ejen Migrasi** | Baca fail AutoCount/SQL/Bukku, cadang pemetaan, jalankan selepas lulus | Import, pemetaan, rekonsiliasi | L3 | Rekod dipindah tanpa ralat |
| **Ejen Pelanggan (WhatsApp)** | Jawab soalan, ambil tempahan/pesanan, serah ke manusia | Katalog, tempahan, sebut harga | L2 | Kadar jawapan < 1 min |

### 11.6 Langkah pelaksanaan

1. **Pendaftar alat sebenar + Peti Kelulusan:** tukar stub kepada `not_implemented`, wayarkan 10 alat pertama ke servis domain (POS, stok, invois, pelanggan, WhatsApp draf), dengan ujian kebenaran & kelulusan.
2. **Ejen Penyediaan + Ejen Kaunter** (risiko rendah, nilai tinggi untuk MVP-1).
3. **Ejen Stok + Ejen Kutip Hutang** (L3, melalui Peti Kelulusan di WhatsApp).
4. **MCP awam** guna pendaftar alat yang sama (pelanggan sambung Claude/ChatGPT sendiri).
5. **Studio AI**: ubah borang/aliran dengan ayat BM, pratonton, kemudian lulus.
6. **Eval berterusan:** set soalan ujian per ejen (ketepatan, tiada tindakan tanpa kelulusan, tiada kebocoran tenant), dijalankan dalam CI.

**Definition of Done ejen** (tambahan kepada Inventory §13.1): alat sebenar + ujian; pagar kelulusan diuji; pengesahan hasil; audit; anggaran kredit; eval lulus; boleh dimatikan per syarikat (*kill switch*).

## 12. Standard UI/UX: lebih mudah daripada Odoo

**Siapa pengguna kita:** pemilik kedai dan staf di Malaysia, kebanyakannya guna **telefon**, bukan pakar sistem. Odoo direka untuk pengguna terlatih yang dibantu konsultan. AI Bijan mesti boleh dipakai **tanpa latihan**.

### 12.1 Sepuluh prinsip

1. **Telefon dahulu.** Setiap skrin mesti boleh dipakai di skrin 390 px dengan satu tangan. Butang sekurang-kurangnya 44 px.
2. **Satu tindakan utama per skrin.** Butang utama jelas (contohnya "Bayar"); tindakan lain sekunder.
3. **BM santai, bukan bahasa sistem.** "Stok tak cukup", bukan "Insufficient quantity on hand". EN tersedia, tukar satu ketik.
4. **WhatsApp sebagai antara muka kedua.** Resit, laporan harian, kelulusan, peringatan dan tempahan boleh dibuat terus di WhatsApp tanpa buka app.
5. **Tanya, jangan cari.** Kotak "Tanya AI Bijan" di setiap skrin: "jualan hari ni?", "siapa belum bayar?". Jawapan beri angka + pautan ke rekod.
6. **AI cadang, manusia putus.** Cadangan AI dipaparkan sebagai kad dengan butang **Lulus / Ubah / Tolak**, berserta sebab ringkas dan kesan (RM, kredit).
7. **Tak pernah hilang kerja.** Offline-first untuk kaunter (dah dibuat, PR #2), draf autosimpan, undo untuk tindakan risiko rendah.
8. **Mula dengan contoh, bukan kosong.** Akaun baharu terus ada produk, kaunter dan laporan contoh mengikut jenis bisnes (Ejen Penyediaan); boleh dibuang satu klik.
9. **Ralat yang membantu.** Setiap mesej ralat beritahu apa berlaku dan apa nak buat ("Tiada gudang untuk kaunter ini → Tetapkan gudang").
10. **Laju.** Skrin utama < 2 s di 4G; tindakan kaunter < 300 ms; tiada spinner tanpa teks.

### 12.2 Perbandingan dengan Odoo

| Situasi | Odoo | AI Bijan |
|---|---|---|
| Mula guna | Pasang app, konfigurasi, latihan | Ejen Penyediaan tanya 5 soalan, sistem siap dengan data contoh |
| Cari maklumat | Menu → laporan → penapis | Tanya dalam BM |
| Kelulusan | Buka app Approvals | Ketik "Lulus" di WhatsApp |
| Kaunter tanpa internet | Bergantung pada versi/peranti | Jual terus, segerak kemudian tanpa pendua |
| Ubah borang | Studio (perlu faham model data) | Terangkan dalam ayat; pratonton; lulus |

### 12.3 Ukuran kejayaan UX

| Metrik | Sasaran |
|---|---|
| Masa daftar → jualan pertama | < 30 min |
| Tugas utama (jual, terima stok, hantar invois) | ≤ 3 ketik dari skrin utama |
| Pengguna aktif harian / pengguna berdaftar | > 60% |
| Cadangan AI yang diluluskan | > 50% (petanda cadangan berguna) |
| Tiket sokongan "macam mana nak…" per 100 syarikat/bulan | Menurun bulan ke bulan |

### 12.4 Langkah UI segera (sejajar MVP-1)

1. Skrin utama baharu: **4 kad sahaja** (Jualan hari ni, Duit belum kutip, Stok perlu tindakan, Cadangan AI untuk diluluskan).
2. **Peti Kelulusan** tunggal (app + WhatsApp) untuk semua cadangan ejen.
3. Kotak **Tanya AI Bijan** global (Ctrl+K / butang mikrofon di telefon).
4. Semak semua skrin sedia ada: telefon 390 px, tema cerah/gelap, BM, mesej ralat; gunakan proses `cek-tampilan` sebelum serah.

