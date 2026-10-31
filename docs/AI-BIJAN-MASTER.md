# AI BIJAN — MASTER RASMI (Dokumen Induk Gabungan)

**Versi:** 1.0  ·  **Tarikh:** 31 Oktober 2026  ·  **Status:** `AUTHORITATIVE` (indeks & status); kandungan terperinci kekal di dokumen sumber  
**Tagline:** *Every Business Deserves a Boardroom.*  
**Disediakan:** gabungan Master Inventory v1.0 + Master Blueprint v9.x + semakan kod sebenar repo `main` (v9.5) dan PR #2.

> Dokumen ini **menggabungkan** semua dokumen AI Bijan menjadi satu titik masuk. Ia tidak menyalin semula 10 MB kandungan lama; setiap bahagian menunjuk ke sumber yang betul, dan setiap sistem ditanda dengan **status sebenar dalam kod**, bukan dakwaan blueprint. Senarai penuh semua fail `.md` ada dalam [`INDEKS-DOKUMEN.md`](INDEKS-DOKUMEN.md).

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
| 1 | **Harga langganan** | Blueprint BAB 2–3: Starter RM288 · Professional RM488 · Business RM888 · Business Pro RM1,288 · Enterprise RM1,888, token AI **termasuk** | Kod `Billing/config/billing.php` (27/10): Starter RM88 · Growth RM388 (bahagian platform RM288, reseller RM100) · Enterprise RM1,888, token AI **berbayar berasingan & wajib**. `Entitlement/config`: Growth RM288 · Enterprise RM1,888 | Owner tetapkan satu jadual harga; kemudian selaraskan Billing + Entitlement + blueprint. **Tiada harga diubah dalam dokumen ini.** |
| 2 | Bilangan sistem | Inventory: 179 canonical + 210 slot legacy | Kod: 60 modul (`app/Modules`) | Lihat §3: berapa canonical yang ada dalam kod, dan §4: modul kod di luar registry. |
| 3 | Dakwaan ujian | Commit baseline v9.5: "963 ujian lulus, 0 gagal" | Disahkan 31/10: suite **tidak boleh jalan** di `main` (26 migrasi bayangan). Selepas PR #2: 1,365 ujian, 3 gagal (ext-intl sandbox) | Guna nombor PR #2; CI GitHub kini dikunci (bil) — perlu dibuka. |
| 4 | Nama fail blueprint | `AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md` berisi v9.3 | Alias kata v9.6 | Namakan semula kepada `AI-BIJAN-MASTER-BLUEPRINT.md` (PR berasingan) dan kemas kini nombor versi. |
| 5 | 210 nama legacy | Nama individu 1–210 tidak dapat dipulihkan (kecuali MES #117) | – | Owner bekalkan daftar asal untuk crosswalk (Inventory §14 #1). |
| 6 | Pemetaan §3 | Pemetaan canonical → modul dibuat oleh Dimas | – | Owner/Product Owner sahkan. |

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

