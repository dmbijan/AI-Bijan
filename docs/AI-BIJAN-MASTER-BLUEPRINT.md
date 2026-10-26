# AI BIJAN SaaS — Dokumen Induk Seni Bina & Produk Master Blueprint

**Versi:** 7  
**Tarikh:** 19 Oktober 2026  
**Nama produk:** AI BIJAN SaaS  
**Tagline:** *Every Business Deserves a Boardroom.*  
**Pemilik konsep:** Bijan Digital Marketing  
**Bahasa utama:** Bahasa Melayu dan English , Malay+english  
**Model produk:** Multi-tenant SaaS, modular, API-first, industry-aware  
**Pasaran utama:** Malaysia → SEA → Global South  
**Admin Hierarchy:** Master → Super Admin → White-label Admin → Customer Admin  
**Platform Features:** Brand portfolio, revenue sharing, white-label, subscription engine, AI reseller  
**AI Provider:** serveras.click (`https://serveras.click/v1`) — Reseller model  
**Commerce Strategy:** B-Site (Shopify replacement), B-Marketing (HubSpot replacement), Deep CRM  
**Positioning:** AI-Native Business OS for Emerging Markets — replaces 10+ tools at RM288-RM1,888/month  
**Competitive Score:** 7.7/10 vs market (SAP 7.0, Oracle 7.0, MSFT 7.0, Zoho 5.7, Odoo 5.3)  
**Unfair Advantages:** Malaysia-first compliance, Collective Intelligence, AI autonomy L0-L5, WhatsApp-first UX, Unlimited users flat rate, Bilingual BM/EN natively
**Gap Priority:** P0 = B-Payroll (MY Compliance), B-Finance Extended | P1 = B-SCM, Deep CRM SFA, B-Document AI

---

## Status Dokumen

> **Status:** Dokumen gabungan induk yang menyatukan SEMUA dokumentasi dari versi v2.1 hingga v4.5.1.  
> **Prinsip utama:** Sistem berdiri sendiri (stand-alone), multi-tenant, berasaskan AI, sesuai untuk hosting cPanel/VPS.  
> **Keperluan wajib:** AI BIJAN dibangunkan sebagai sistem sendiri. **ERPNext bukan dependency**, bukan backend, bukan pangkalan data aplikasi, bukan UI, dan bukan enjin operasi yang diperlukan untuk menjalankan AI BIJAN.  
> **Perhatian:** Dokumen ini ialah spesifikasi perancangan. Ia bukan bukti bahawa setiap fungsi telah dikodkan, diuji, diluluskan dari sudut pematuhan atau tersedia kepada pelanggan.

---

## Struktur Dokumen Gabungan

> **Nota:** Dokumen ini ialah gabungan induk dari versi v2.1 hingga v4.5.1. Beberapa bab dari versi asal (BAB 36-52) telah digabungkan atau dipinda dan kini diwakili oleh bahagian baru (BAB 53-60). Jadual di bawah mencerminkan struktur SEMASA dokumen ini.

| Bahagian | Sumber | Kandungan |
|----------|--------|-----------|
| 0 | V4.1+V3.0 | Tujuan, skop, prinsip, istilah status |
| 1 | V4.5.1+V3.0 | Ringkasan eksekutif, matlamat, perkara tidak tersedia automatik |
| 2 | V2.1+V2.5 | Model seni bina platform (7 lapisan, modular monolith) |
| 3 | V3.0+V4.5.1 | B-Auth & Security (RBAC, MFA, tenant isolation) |
| 4 | V4.5.1 | Tenant, Organization, Branch & Membership |
| 4a | V4.5.1 | Platform Business & Brand Portfolio |
| 4b | V4.5.1 | Multi-Tier Admin Hierarchy (4 level + permission matrix) |
| 5 | V3.0+V4.5.1 | Role, Permission & Entitlement Matrix |
| 6 | V4.5.1 | Subscription, Billing & Usage Metering |
| 6a | V4.5.1 | Subscription & Monetization Engine Admin Panel |
| 7-9 | V3.0 | Notifications, Files & Document Storage, Audit Trail |
| 10 | V2.1+V3.0 | API, Webhooks & Integrations |
| 11 | V3.0 | Localization & Malaysia Configuration |
| 12 | V3.0+V2.1 | 18 B-Modules Katalog (7 kumpulan + event contoh) |
| 13 | V3.0 | COGS Engine Formula + Inventory States + BOM |
| 14 | V3.0 | Finance, Invoicing & e-Invois LHDN |
| 15 | V2.2+V4.0 | Industry Packs (19 industri + MSIC mapping) |
| 16 | V2.1 | AI Agents (11 ejen dengan tools spesifik) |
| 17 | V4.3 | Personal AI Assistant (9 jenis + memory architecture) |
| 18 | V4.3 | Business Understanding Engine (10 tahap discovery) |
| 19 | V4.3 | Autonomous Growth Engine (20-step lifecycle) |
| 20 | V2.1 | B-Boardroom Workflow (7 langkah) |
| 21 | V2.4+V4.5.1 | AI Gateway — Serveras.click Reseller (20 models) |
| 22 | V4.5.1 | AI Token Wallet Pricing (4 tiers table) |
| 23 | V2.5 | AI Media Studio (image/video generation) |
| 24 | V2.5-owner | UI/UX Design System (mobile-first, glass morphism) |
| 25 | V2.1 | Domain Events & Transactional Outbox |
| 26 | V2.1+V3.0 | Database Design (20+ jadual utama) |
| 27 | V2.1 | Integrasi Workspace (B-Hub connectors + OAuth) |
| 28 | V2.1 | B-Flow / B-Trigger Automation |
| 29 | V2.1 | B-Scout, B-Intel, B-Compliance |
| 30 | V2.1 | Ads Manager (Google/Meta + guardrails) |
| 31 | V2.1 | Payment Gateways & Kurier |
| 32 | V2.5-master+V4.5.1 | Implementation Status & Roadmap (fasa 0-5) |
| 33 | customer-side.md | Customer Experience & Onboarding |
| 34 | V3.0 | QA, Testing & Release Readiness |
| 35 | V4.5.1 | Versioning Policy & Changelog |
| 50 | New | Global Expansion & Market Strategy (BAB 50) |
| 53 | New | Commerce & Marketing Enhancements (B-Site, B-Marketing deep CRM) |
| 54 | New | CRM & Sales Enablement (Contact DB, Deal Pipeline, Lead Scoring) |
| 55 | New | Service Desk & Quote Builder |
| 56 | New | Go-to-Market Strategy & Revenue Projections |
| 56A | New | AI Live Interaction: Voice, Video & Real-Time Communication |
| 56B | New | Mobile Applications: Android & iOS |
| 56C | New | CCTV Surveillance & AI Security (Real-time detection, PDPA compliance) |
| 57 | New | B-Payroll — Malaysia Statutory Compliance (EPF/SOCSO/EIS/PCB/EA) |
| 58 | New | B-SCM — Supply Chain Management (Supplier, Demand Planning, Advanced Inventory) |
| 59 | New | B-Document AI — OCR, Receipt Extraction, Contract Analysis |
| 60 | New | Competitive Analysis & Market Positioning Report |

---

# PART I — VISI PRODUK DAN SENI BINA

## BAB 0 — Tujuan, Skop dan Peraturan Dokumen

Dokumen ini menetapkan visi produk, seni bina, modul perniagaan, direktori industri, struktur data, workflow, keselamatan, AI, integrasi, pengujian dan pelaksanaan AI BIJAN.

### 0.1 Prinsip Utama

| # | Prinsip | Penerangan |
|---|---------|------------|
| 1 | **One Shared Core, Many Business Modules** | Semua industri berkongsi asas platform, tetapi tidak dipaksa menggunakan workflow yang sama |
| 2 | **Industry-aware, not industry-restricted** | Satu organisasi boleh menjalankan beberapa aktiviti perniagaan, cawangan dan jenama |
| 3 | **API-first** | Web, aplikasi mudah alih, integrasi dan ejen AI menggunakan kontrak API yang ditetapkan |
| 4 | **Secure by default** | Setiap permintaan mesti melalui pengesahan, skop tenant, permission dan entitlement |
| 5 | **AI with controlled autonomy** | AI boleh membantu membuat keputusan, tetapi tindakan berisiko memerlukan kelulusan manusia |
| 6 | **Source of truth** | Setiap jenis rekod rasmi mempunyai pemilik data yang jelas |
| 7 | **Configuration before custom code** | Perbezaan kecil industri diselesaikan melalui blueprint; perbezaan domain sebenar memerlukan modul khusus |
| 8 | **Evidence before production claims** | Modul tidak boleh dilabel production-ready hanya kerana halaman UI wujud |
| 9 | **Malaysia-first** | Keperluan Malaysia mesti disahkan dengan sumber rasmi yang berkaitan |
| 10 | **Phased delivery** | Lengkapkan aliran perniagaan sebenar sebelum mengembangkan bilangan modul |

### 0.2 Istilah Status

| Status | Maksud |
|--------|--------|
| **Planned** | Dicadangkan dalam roadmap |
| **Specified** | Keperluan dan kontrak didokumenkan |
| **In Development** | Sedang dibangunkan |
| **Internal QA** | Sedang diuji secara dalaman |
| **Pilot** | Digunakan oleh kumpulan pelanggan terhad |
| **Production Ready** | Lulus semua release gate yang ditetapkan |

> **⚠️ SYARAT PRODUCTION READY (A-16):**
>
> Status "Production Ready" HANYA boleh digunakan jika SEMUA syarat berikut dipenuhi:
> 
> | Gate | Bukti Yang Diperlukan |
> |------|----------------------|
> | Unit Tests | ≥ 80% code coverage, semua critical path covered |
> | Integration Tests | Semua API endpoint ada test suite |
> | Security Review | OWASP Top 10 scan lulus, penetration test (untuk financial modules) |
> | Performance | Response time < 500ms p95, throughput > 100 req/s per node |
> | Monitoring | Sentry alerts configured, Laravel Pulse dashboard live, uptime check |
> | Backup | Automated backup verified, restore tested |
> | Documentation | API docs (OpenAPI), user guide, runbook untuk ops team |
> | Rollback Plan | Blue-green atau canary deployment strategy tested |
> | Audit Log | Semua action logged dengan user_id, timestamp, IP address |
>
> **Tanpa bukti yang boleh diaudit, status mestilah "Specified" atau "In Development", BUKAN "Production Ready".**
| **Regulated Workflow Reviewed** | Aliran khusus telah disemak berdasarkan keperluan peraturan berkaitan |
| **Deprecated** | Tidak lagi disyorkan dan mempunyai pelan migrasi |

> Jangan menggunakan status ini tanpa bukti yang boleh diaudit.

---

## BAB 1 — Ringkasan Eksekutif

AI BIJAN ialah platform SaaS untuk membantu perusahaan, PKS, peniaga, penyedia perkhidmatan dan organisasi di Malaysia mengurus operasi melalui satu ruang kerja.

Platform menggabungkan:

- **Business operations** — Virtual Office, HRMS, Projects, Assets, Sign & Docs
- **CRM dan customer communication** — Omnichannel, Helpdesk, Loyalty
- **Sales, POS, invoicing dan payment workflows** — POS, Marketplace, Site, Link, Invoicing
- **Inventory, procurement, warehouse dan logistics** — Inventory, COGS, BOM/Manufacturing, Procurement, Logistics
- **Industry-specific workflows** — School Transport, Event & Rental, Construction, Automotive, Agriculture dll
- **Reporting, analytics dan business intelligence** — Analytics dashboard dan reports
- **AI agents, automations dan knowledge retrieval** — B-Brain Hub dengan agent registry dan tool execution
- **Industry Blueprint Engine** — Onboarding automatik mengikut jenis perniagaan
- **Subscription billing, usage metering dan AI Token Wallet** — Pelan langganan, kuota penggunaan, wallet kredit AI

Satu tenant boleh mengaktifkan modul berdasarkan keperluan dan pelan langganannya. Pelanggan tidak perlu menggunakan semua modul.

### 1.1 Matlamat Produk

- Mengurangkan kerja manual dan pertindihan sistem
- Menghubungkan operasi, pelanggan, dokumen dan kewangan
- Membolehkan pelanggan bermula dengan modul kecil dan berkembang
- Menyediakan ejen AI yang menggunakan data dengan kebenaran yang sesuai
- Memudahkan penyesuaian sistem mengikut industri
- Membolehkan pengendali platform menjual langganan dan penggunaan AI secara terkawal

### 1.2 Perkara yang Tidak Boleh Dianggap Tersedia Secara Automatik

Blueprint tidak membuktikan bahawa:

- semua modul telah dibangunkan;
- semua integrasi mempunyai akses API yang diluluskan;
- semua kod MSIC telah dipetakan secara rasmi;
- semua aliran kewangan mematuhi setiap keperluan yang terpakai;
- semua AI agents memberikan jawapan tepat;
- semua modul sesuai untuk aktiviti terkawal atau berlesen;
- semua fungsi beroperasi dengan baik dalam production.

Setiap dakwaan mesti disokong oleh implementasi, ujian dan dokumentasi.

---

## BAB 2 — Model Seni Bina Platform

### 2.1 Lapisan Platform

```
AI BIJAN PLATFORM
│
├── Experience Layer
│   ├── Web Application (Next.js 15 + React 19 + Tailwind CSS)
│   ├── Mobile Application (Flutter — jika diperlukan)
│   ├── Admin / Platform HQ (Filament PHP)
│   ├── Customer / Partner Portals
│   └── API Clients
│
├── Shared Core
│   ├── Authentication & Security (B-Auth)
│   ├── Tenant / Organization / Branch
│   ├── Roles & Permissions
│   ├── Subscription & Entitlements
│   ├── Billing & Usage Metering
│   ├── Notifications (B-Notify)
│   ├── Files & Documents
│   ├── Audit Trail
│   ├── API & Webhooks (B-Connect)
│   └── Localization
│
├── Business Modules
│   ├── CRM / Omnichannel
│   ├── Sales / POS / Booking
│   ├── Inventory / Procurement / Logistics
│   ├── Finance / Invoicing / COGS
│   ├── HRMS / Projects / Assets
│   └── Marketing / Analytics / Helpdesk
│
├── Industry Modules
│   ├── Event & Rental
│   ├── Retail / Wholesale
│   ├── Transport & Fleet
│   ├── Education / Childcare
│   ├── Hospitality
│   ├── Construction / Property
│   ├── Automotive / Repair
│   └── Other industry packs
│
├── B-Brain Hub
│   ├── AI Gateway (serveras.click)
│   ├── Model Catalog (20 models)
│   ├── Agent Runtime
│   ├── Knowledge Retrieval (RAG)
│   ├── Tool Registry
│   ├── Approval Policies
│   └── AI Usage Ledger / Token Wallet
│
├── Industry Blueprint Engine
│   ├── Industry Directory (MSIC mapping)
│   ├── Module Recommendations
│   ├── Workflow Templates
│   ├── Custom Fields
│   ├── Permission Templates
│   └── Versioned Blueprint Releases
│
└── Infrastructure & Operations
    ├── Database (MySQL 8.x / MariaDB 10.6+)
    ├── Queue / Background Jobs (Redis + Laravel Horizon)
    ├── Cache (Redis)
    ├── File Storage (Cloudflare R2 / AWS S3)
    ├── Observability (Sentry, Laravel Pulse)
    ├── Backup & Restore
    └── Security / Incident Management
```

### 2.2 Stack Teknologi

| Lapisan | Pilihan | Catatan |
|---------|---------|---------|
| Backend | Laravel 11+, PHP 8.3+ | Modular monolith, REST API |
| Admin HQ | Filament PHP | Platform operations dan admin merchant |
| Web | Next.js 15 + TypeScript + Tailwind CSS | Dashboard, B-Site, B-Link, portal |
| Mobile | Flutter | Merchant/pelanggan apps (jika diperlukan) |
| Database | MySQL 8.x / MariaDB 10.6+ | Source of truth transaksi |
| Cache/Queue | Redis + Laravel Horizon | Kerja latar belakang dan cache |
| Realtime | Laravel Reverb | Status, chat dan notifikasi langsung |
| Search | Meilisearch | Carian katalog dan pelanggan |
| Object Storage | Cloudflare R2 atau AWS S3 | Fail, gambar, dokumen |
| AI Gateway | serveras.click (`/v1`) | Provider-agnostic adapter layer |
| Vector Search | Qdrant (apabila perlu) | RAG dokumen tenant |
| Analytics | MySQL dahulu, ClickHouse kemudian | Laporan berat dan event analytics |
| Monitoring | Sentry, Laravel Pulse, uptime checks | Ralat, prestasi dan availability |
| API Contract | OpenAPI | Kontrak klien dan integrasi |

### 2.3 Peraturan Pemilikan Data

Setiap domain mempunyai sumber data rasmi atau **system of record**:

| Domain | Pemilik Rekod |
|--------|---------------|
| Identiti dan membership | B-Auth & Security |
| Organisasi dan cawangan | Tenant / Organization Core |
| Pelan platform | Platform Subscription & Billing |
| Penggunaan AI | B-Brain Hub Usage Ledger |
| Jualan kaunter | B-POS |
| Pergerakan stok | B-Inventory |
| Tempahan generik | B-Booking |
| Tempahan industri | Modul industri berkaitan |
| Invois dan quotation | B-Invoicing |
| Rekod kewangan | B-Finance |
| Pekerja dan cuti | B-HRMS |
| Projek dan milestone | B-Projects |
| Aset fizikal | B-Assets |
| Penghantaran | B-Logistics |
| Operasi acara | B-Event & Rental |

Modul lain tidak boleh mengubah jadual domain tersebut secara terus tanpa kontrak yang diluluskan.

---

## BAB 2 — Database Strategy & Deployment Tiers (Audit Fix A-11)

### 2.3a Database Decision Matrix

> **Keputusan:** MySQL/MariaDB sebagai **primary datastore** untuk transaksi perniagaan. PostgreSQL digunakan secara **eksplisit** untuk AI memory, vector search metadata, dan analytics workload. Ini bukan sisa daripada versi lama — ini adalah keputusan arkitek untuk menggunakan database yang paling sesuai untuk setiap workload.

| Workload | Database | Alasan |
|----------|----------|--------|
| Transaksi perniagaan (orders, invoices, inventory) | MySQL 8.x / MariaDB 10.6+ | ACID, mature, cPanel-compatible, known performance |
| User auth & tenant metadata | MySQL 8.x | Shared core, same transactional guarantees |
| AI conversation history | PostgreSQL 15+ | JSONB flexibility, better text search, extension support |
| Agent/Project memory (Redis + PG) | PostgreSQL 15+ | Complex queries on business rules, workflow templates |
| Vector embeddings metadata | Qdrant | Purpose-built vector similarity search |
| Aggregated analytics | MySQL → ClickHouse later | Heavy reporting, OLAP when scale demands |
| Cache & session | Redis | Low-latency access, TTL-based expiry |
| Queue & background jobs | Redis + Laravel Horizon | Proven reliability with Laravel ecosystem |

### 2.3b Deployment Tiers

> **Perhatian:** Dokumen ini merangkumi spesifikasi untuk semua tahap skala. Pilih tier yang sesuai dengan status semasa.

#### Tier 1 — Development / Demo (Minimum)

| Komponen | Spesifikasi | Kos/Bulan |
|----------|-------------|-----------|
| Web Server | Shared hosting cPanel atau VPS RM50-100/bulan | RM50-100 |
| Database | Single MySQL instance (shared) | Termasuk hosting |
| Cache | Redis pada sama VPS | Termasuk hosting |
| Storage | Local filesystem atau Cloudflare R2 free tier | RM0-5 |
| AI Gateway | serveras.click (pay-per-use) | Berubah mengikut usage |
| Monitoring | Sentry free tier | RM0 |
| **Jumlah Minimum** | | **RM50-105** |

**Batasan:** Satu tenant sahaja, tiada HA, manual backup, sesuai untuk dev/demo sahaja.

#### Tier 2 — Single VPS Production (SME Customer)

| Komponen | Spesifikasi | Kos/Bulan |
|----------|-------------|-----------|
| VPS | 4 vCPU, 8GB RAM, 160GB SSD (Contabo/Azure VM B4s) | RM150-300 |
| Database | MySQL dedicated instance | Termasuk VPS |
| Cache/Queue | Redis standalone | Termasuk VPS |
| Object Storage | Cloudflare R2 | RM5-20 |
| AI Gateway | serveras.click | Berubah |
| SSL | Let's Encrypt (free) | RM0 |
| Monitoring | Sentry + Laravel Pulse | RM0-50 |
| **Jumlah** | | **RM155-370** |

**Batasan:** Satu server, manual scaling, backup harian ke remote storage, suitable untuk 1-50 tenants.

#### Tier 3 — Multi-Node Production (Platform Scale)

| Komponen | Spesifikasi | Kos/Bulan |
|----------|-------------|-----------|
| App Servers | 2x 4 vCPU nodes behind load balancer | RM600-1200 |
| Database | Managed RDS (Multi-AZ) atau自建 master+replica | RM300-800 |
| Redis Cluster | Redis Enterprise atau Sentinel cluster | RM200-400 |
| Search | Meilisearch dedicated node | RM100-200 |
| Vector DB | Qdrant cloud/dedicated | RM100-300 |
| Object Storage | Cloudflare R2 / AWS S3 | RM50-200 |
| CDN | Cloudflare Pro | RM50 |
| Monitoring | Sentry paid + Grafana + PagerDuty | RM200-500 |
| Backup | Automated cross-region | RM100-200 |
| **Jumlah** | | **RM1700-4100** |

**Ciri-ciri:** Auto-scaling, zero-downtime deployment, multi-AZ, automated failover, suitable untuk 50+ tenants.

### 2.3c Migration Path

```
Dev/Demo (Tier 1) → Single VPS (Tier 2) → Multi-Node (Tier 3)
        ↓                      ↓                      ↓
   cPanel/VPS           Dedicated VPS          Load-balanced cluster
   Manual backup        Automated backup       Cross-region backup
   No HA                Basic HA               Full HA + auto-failover
   < 50 tenants         50-500 tenants         500+ tenants
```

> **Syarat migrasi dari Tier 2 ke Tier 3:** Lebih 50 tenants aktif, avg response time > 500ms, atau requirement untuk 99.9% uptime SLA.

### 2.3d Data Model Completeness Requirements (Audit Fix A-27)

> **⚠️ PERHATIAN — PRODUCTION READINESS:**
>
> Semua data model yang dinyatakan dalam dokumen ini adalah **LOGICAL DATA MODEL** sahaja.
> Sebelum migrasi ke pengeluaran, setiap jadual MESTI mempunyai DDL lengkap dengan:
>
> #### Mandatory DDL Elements
>
> | Elemen | Keperluan | Contoh |
> |--------|-----------|--------|
> | Primary Key | `id BIGINT PRIMARY KEY AUTO_INCREMENT` | Setiap jadual wajib ada PK |
> | Foreign Keys | Semua FK dengan `ON DELETE RESTRICT` atau `CASCADE` yang jelas | `tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE RESTRICT` |
> | Unique Constraints | `UNIQUE(slug)`, `UNIQUE(tenant_id, reference_number)` | Elakkan duplicate data |
> | Nullability | Semua NOT NULL dengan default value yang jelas | `status ENUM('draft','posted') NOT NULL DEFAULT 'draft'` |
> | Tenant Scope | Semua jadual multi-tenant perlu `tenant_id BIGINT NOT NULL` | Multi-tenancy enforcement |
> | Indexes | Composite indexes untuk `(tenant_id, created_at)`, `(status, priority)` | Query performance |
> | Soft Delete | `deleted_at TIMESTAMP NULL` untuk semua jadual perniagaan | Data recovery |
> | Retention Semantics | Column `retention_until DATE` untuk data tertakluk retention policy | Compliance |
> | Timestamps | `created_at`, `updated_at`, `deleted_at` (soft delete) | Audit trail |
> | Audit Fields | `created_by`, `updated_by` dengan tracking user ID | Accountability |
> | Partitioning | Large tables (>10M rows) perlu partitioned by `tenant_id` + `created_at` | Scale |
>
> #### Validation Checklist Sebelum Production
>
> ```
> [ ] Semua table ada PK, FK, unique constraints
> [ ] Semua column ada explicit nullability dan default
> [ ] Semua multi-tenant table ada tenant_id dengan check constraint
> [ ] Indexes defined untuk query pattern yang diketahui
> [ ] Soft delete implemented untuk business records
> [ ] Audit fields (created_by, updated_by) populated via triggers/application
> [ ] Retention columns aligned with BAB 9.2 retention policy
> [ ] Migration scripts tested on staging with production-like data volume
> [ ] Rollback script available for each migration
> [ ] Backup/restore procedure documented and tested
> ```
>
> #### Example: Complete DDL Template
>
> ```sql
> CREATE TABLE example_table (
>     id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
>     tenant_id BIGINT UNSIGNED NOT NULL,
>     slug VARCHAR(100) NOT NULL,
>     status ENUM('draft','active','archived') NOT NULL DEFAULT 'draft',
>     description TEXT NULL,
>     amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
>     currency CHAR(3) NOT NULL DEFAULT 'MYR',
>     retention_until DATE NULL,
>     deleted_at TIMESTAMP NULL,
>     created_by BIGINT UNSIGNED NULL,
>     updated_by BIGINT UNSIGNED NULL,
>     created_at TIMESTAMP NULL,
>     updated_at TIMESTAMP NULL,
>     
>     -- Constraints
>     CONSTRAINT fk_example_tenant FOREIGN KEY (tenant_id) 
>         REFERENCES tenants(id) ON DELETE RESTRICT,
>     CONSTRAINT uk_example_tenant_slug UNIQUE (tenant_id, slug),
>     CONSTRAINT chk_example_amount CHECK (amount >= 0),
>     
>     -- Indexes
>     INDEX idx_example_tenant_created (tenant_id, created_at),
>     INDEX idx_example_status (status),
>     INDEX idx_example_deleted (deleted_at) WHERE deleted_at IS NOT NULL
> ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
> ```

---

# PART II — SHARED CORE

## BAB 3 — B-Auth & Security

### 3.1 Skop

- Pendaftaran dan log masuk
- Pengesahan e-mel atau nombor telefon
- Password reset
- Multi-factor authentication (MFA/2FA) untuk admin dan platform
- Pengurusan sesi dan token
- Pengesahan API
- OAuth atau social login jika disokong
- Device/session management
- Account recovery
- Login history dan security events
- Service identity untuk integrasi dan AI agents

### 3.2 RBAC Roles

Roles yang digunakan:

| Role | Scope | Keterangan |
|------|-------|------------|
| `platform_super_admin` | Global | Operator platform (Bijan Holdings) |
| `tenant_owner` | Tenant | Pemilik akaun tenant |
| `tenant_admin` | Tenant | Pentadbir tenant |
| `finance_manager` | Tenant | Pengurus kewangan |
| `hr_manager` | Tenant | Pengurus HR |
| `inventory_manager` | Tenant | Pengurus inventori |
| `cashier` | Tenant/Branch | Kasir |
| `staff` | Tenant | Staf biasa |
| `auditor` | Tenant | Auditor |
| `customer` | Tenant | Pelanggan |
| `ai_agent` | Tenant | Ejen AI dengan permission terhad |
| `white_label_admin` | WL Scope | Admin reseller (manage their tenants only) |

### 3.3 Peraturan Keselamatan

- Jangan simpan password dalam bentuk plaintext
- Token rahsia tidak boleh dihantar ke browser atau aplikasi klien
- Gunakan expiry, revocation dan rotation yang sesuai
- Hadkan percubaan login dan reset password
- Semak permission pada server untuk setiap tindakan sensitif
- Elakkan IDOR/BOLA dengan memeriksa ownership dan tenant scope
- Gunakan CSRF protection apabila kaedah authentication memerlukannya
- Validate input di server
- Log peristiwa keselamatan tanpa merekod rahsia
- Sediakan proses revocation apabila pekerja atau vendor kehilangan akses

### 3.4 Tenant Isolation

- `tenant_id` pada setiap jadual yang menyimpan data tenant
- Global scopes sebagai kemudahan, bukan satu-satunya kawalan
- Policy checks pada setiap endpoint
- Composite indexes dan composite foreign keys untuk mengurangkan rujukan silang tenant
- Ujian negatif untuk IDOR dan kebocoran tenant
- Cache keys, storage paths, vector namespaces dan analytics filters mesti memasukkan tenant scope
- Queue job, scheduled task, file URL, search query dan AI tool mesti membawa tenant context eksplisit

### 3.5 Acceptance Tests

- Pengguna tenant A tidak boleh membaca data tenant B
- Pengguna yang dibuang daripada organisasi kehilangan akses sewajarnya
- Token yang tamat tempoh ditolak
- Token yang telah dibatalkan tidak boleh digunakan semula
- Reset password tidak membocorkan kewujudan akaun secara berlebihan
- Endpoint sensitif menolak pengguna tanpa permission walaupun UI menyembunyikan butang
- Service identity mempunyai skop dan expiry yang ditetapkan

---

## BAB 4 — Tenant, Organization, Branch & Membership

### 4.1 Model

Satu pengguna global boleh menjadi ahli kepada beberapa organisasi. Satu organisasi boleh mempunyai beberapa cawangan, jenama, jabatan atau unit operasi.

Entiti teras:

- `users`, `tenants`, `organizations`, `organization_memberships`
- `branches`, `departments`, `brands`
- `tenant_settings`, `tenant_module_entitlements`
- `tenant_domains`, `tenant_invitations`

### 4.2 Peraturan

- Setiap rekod domain tenant-scoped mesti mempunyai skop tenant yang boleh disahkan
- Jangan mempercayai `tenant_id` yang dihantar oleh klien tanpa authorization
- Cawangan mesti berada di bawah organisasi yang sah
- Membership perlu mempunyai status dan tempoh akses
- Data cross-branch mesti dikawal mengikut role dan permission
- Perubahan struktur organisasi perlu mempunyai audit log

---

## BAB 4a — Platform Business & Brand Portfolio

### 4a.1 Konsep Utama

AI BIJAN bukan sekadar "platform kosong" — ia adalah **bisnes dengan identiti sendiri**. Platform owner (Bijan Holdings) perlu setup:
- Nama syarikat, SSM, tax ID, alamat berdaftar
- Brand portfolio (parent brand + sub-brands)
- Domain configuration (main domain, subdomain pattern, custom domain)
- Invoice settings (footer text, payment terms, late fee)
- Bank account untuk payment collection

### 4a.2 Platform Business Profile

| Field | Maksud | Contoh |
|-------|--------|--------|
| **Company Name** | Nama syarikat platform | Bijan Holdings Sdn Bhd |
| **SSM Number** | No pendaftaran SSM | 202XXX0XX-X |
| **Tax ID (EIN)** | Identification cukai | X-XXXXXXX-X |
| **Registered Address** | Alamat berdaftar rasmi | Lot XXX, Jalan XXX, XXX XX Kota Bharu, Kelantan |
| **Contact Email** | Email utama platform | admin@aibijan.com |
| **Contact Phone** | Nombor telefon | +60 XXX-XXXX XXXX |
| **Currency** | Mata wang default | MYR |
| **Timezone** | Timezone platform | Asia/Kuala_Lumpur |
| **Bank Account** | Info akaun bank untuk payment | Maybank 1234XXXXXXXX |
| **Invoice Footer** | Teks di bawah invois | Powered by AI BIJAN © 2026 |

### 4a.2a Multi-Country & Multi-Currency Configuration

AI BIJAN direka sebagai platform global — bukan terhad kepada Malaysia sahaja. Super Admin boleh configure setiap negara dengan:

| Setting | Keterangan |
|---------|------------|
| **Country Code** | MY, SG, ID, PH, TH, VN, BD, PK, US, GB, dll |
| **Local Currency** | MYR, SGD, IDR, PHP, THB, VND, BDT, PKR, USD, GBP |
| **Exchange Rate** | Manual override atau auto-fetch dari API |
| **Price Multiplier** | Faktor penyesuaian mengikut kuasa beli negara |
| **Tax Rules** | SST (Malaysia 6%/8%), GST (Singapore 9%), VAT (EU), dll |
| **Payment Methods** | FPX, Card, GrabPay, GoPay, DANA, ShopeePay, M-Pesa, dll |
| **Language Default** | BM, EN, ID, TL |
| **Compliance** | e-Invois (MY), PDPA, GDPR, dll |

**Contoh Harga Berbeza Mengikut Negara:**

| Plan | Malaysia (MYR) | Singapore (SGD) | Indonesia (IDR) | Pakistan (PKR) |
|------|---------------|----------------|----------------|----------------|
| **Starter** | RM 288/month | S$ 85/month | Rp 350.000/bulan | Rs 6.000/bulan |
| **Professional** | RM 488/month | S$ 145/month | Rp 600.000/bulan | Rs 11.000/bulan |
| **Business** | RM 888/month | S$ 265/month | Rp 1.200.000/bulan | Rs 20.000/bulan |
| **Business Pro** | RM 1,288/month | S$ 385/month | Rp 1.700.000/bulan | Rs 28.000/bulan |
| **Enterprise** | RM 1,888/month | S$ 565/month | Rp 2.600.000/bulan | Rs 40.000/bulan |

> Semua harga di atas adalah CONTOH. Super Admin boleh ubah mana-mana harga, mana-mana mata wang, bila-bila masa.

### 4a.2b Configurable Pricing Engine (Master Admin)

Semua harga pakej dan token dikawal sepenuhnya dari **Master Admin Panel**. Tiada hardcoded price dalam kod.

> **Peraturan Kuasa Harga:**
> - **Master Admin**: Mempunyai kuasa KESELURUHAN — mencipta pelan global, menetapkan harga asas, mengubah kos wholesale, menetapkan kadar tukaran, mengaktifkan/disaktifkan pelan.
> - **Super Admin**: Boleh mencipta/mengedit pelan baru, menetapkan harga negara khusus, mengubah diskaun tempatan, tetapi TIDAK boleh mengubah struktur pelan global atau kos wholesale tanpa kebenaran Master Admin.
> - **White-label Admin**: Hanya boleh memilih pelan yang tersedia untuk tenant mereka; tidak boleh mengubah harga asas.
> - **Pelanggan Admin**: Hanya boleh upgrade/downgrade pelan sedia ada.
>
> **Nota:** Schema penuh untuk `country_pricing_plans`, `country_token_pricing`, dan `exchange_rates` terdapat di BAB 6 (bahagian Subscription & Billing). Semua rujukan pricing di sini menggunakan schema canonical tersebut.

### 4a.3 Brand Portfolio Structure

```
BIJAN HOLDINGS SDN BHD (Platform Owner)
│
├── AI BIJAN (Main SaaS Platform)
│   ├── aibijan.com
│   └── SaaS Subscription Plans
│       ├── Starter (RM288/bulan) — 80M tokens
│       ├── Professional (RM488/bulan) — 200M tokens
│       ├── Business (RM888/bulan) — 1B tokens
│       ├── Business Pro (RM1,288/bulan) — 2.25B tokens
│       └── Enterprise (RM1,888/bulan) — Unlimited*
│
├── BRAND PORTFOLIO (Sub-brands / Modules under AI BIJAN)
│   ├── B-Site (Website builder)
│   ├── B-Link (Social commerce)
│   ├── B-POS (Point of Sale)
│   ├── B-Omnichannel (Customer communication)
│   ├── B-Helpdesk (Support)
│   ├── B-Finance (Accounting)
│   ├── B-HRMS (Human resources)
│   ├── B-Inventory (Stock management)
│   ├── B-Projects (Project management)
│   └── B-Analytics (Business intelligence)
│
├── B-SOLUTIONS (Enterprise Services)
│   ├── solutions.aibijan.com
│   └── Custom implementation, consulting, training
│
└── B-LAB (R&D / Innovation)
    ├── lab.aibijan.com
    └── Experimental features, beta testing
```

Setiap brand boleh ada: nama, slug, description, icon, color, status (active/inactive/archived), modul yang termasuk, visibility kepada tenants.

### 4a.4 Domain Configuration

| Domain Type | Pattern | Kegunaan |
|------------|---------|----------|
| **Main Domain** | `aibijan.com` | Platform landing page, auth, admin panel |
| **Subdomain Pattern** | `{tenant}.aibijan.com` | Default subdomain setiap tenant |
| **Custom Domain Rule** | `{custom-domain}` | Tenant boleh guna domain sendiri (white-label) |

**Peraturan:** SSL auto-provisioned untuk main domain dan subdomain pattern. Custom domain memerlukan DNS verification (CNAME atau TXT record). Super Admin boleh enable/disable custom domain per tenant.

### 4a.5 Database Schema — Platform Business

```sql
-- Platform profile (data platform owner)
CREATE TABLE platform_profiles (
    id BIGINT PRIMARY KEY,
    company_name VARCHAR(255),
    ssm_number VARCHAR(50),
    tax_id VARCHAR(50),
    registered_address TEXT,
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    currency CHAR(3) DEFAULT 'MYR',
    timezone VARCHAR(50) DEFAULT 'Asia/Kuala_Lumpur',
    logo_url VARCHAR(500),
    invoice_footer_text TEXT,
    terms_url VARCHAR(500),
    privacy_url VARCHAR(500),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Brand portfolio (parent + sub-brands)
CREATE TABLE brand_portfolio (
    id BIGINT PRIMARY KEY,
    parent_brand_id BIGINT NULL REFERENCES brand_portfolio(id),
    name VARCHAR(255),
    slug VARCHAR(255),
    description TEXT,
    status ENUM('active','inactive','archived') DEFAULT 'active',
    icon_url VARCHAR(500),
    color_hex CHAR(7),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Brand-to-module mapping
CREATE TABLE brand_modules (
    id BIGINT PRIMARY KEY,
    brand_id BIGINT REFERENCES brand_portfolio(id),
    module_slug VARCHAR(100),
    is_visible_to_tenants BOOLEAN DEFAULT TRUE,
    requires_approval BOOLEAN DEFAULT FALSE
);

-- Domain configuration
CREATE TABLE platform_domains (
    id BIGINT PRIMARY KEY,
    domain_type ENUM('main','subdomain_pattern','custom'),
    domain_value VARCHAR(255),
    ssl_enabled BOOLEAN DEFAULT TRUE,
    redirect_to VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active'
);

-- Tenant brand customization (white-label)
CREATE TABLE tenant_brand_customization (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    custom_logo_url VARCHAR(500),
    custom_primary_color CHAR(7),
    custom_font VARCHAR(255),
    custom_domain VARCHAR(255),
    white_label_enabled BOOLEAN DEFAULT FALSE,
    verified_at TIMESTAMP NULL,
    verified_by BIGINT NULL
);
```

---

## BAB 4b — Multi-Tier Admin Hierarchy

### 4b.1 Konsep Sistem

AI BIJAN menggunakan **4 level admin** dengan permission berbeza. Setiap level mempunyai scope, kuasa dan tanggungjawab yang tersendiri.

```
MASTER ADMIN (Bijan Holdings)
│  ├─ Platform: AI BIJAN SaaS
│  ├─ Brand portfolio (AI BIJAN, B-Solutions, dll)
│  ├─ Pricing plans (semua harga dari sini)
│  ├─ Domain configuration (aibijan.com, *.aibijan.com)
│  └─ [Create Super Admin accounts]
│
├── SUPER ADMIN (AI BIJAN Operator)
│   ├─ Manage ALL tenants
│   ├─ Subscription & Monetization Engine
│   ├─ AI Gateway & Model catalog
│   ├─ White-label setup (enable/disable WL feature)
│   ├─ Create White-label Admin accounts
│   └─ Platform-wide analytics
│   │
│   ├── WHITE-LABEL ADMIN (Reseller/Agency)
│   │   ├─ Manage THEIR OWN tenants ONLY
│   │   ├─ Custom branding (logo, colors, domain)
│   │   ├─ Their own invoice footer & payment terms
│   │   ├─ Choose from available pricing plans (set by Super Admin)
│   │   ├─ Can add AI credits top-up packages
│   │   ├─ Their own support desk
│   │   ├─ Subdomain: agency.aibijan.com OR agency-custom.com
│   │   └─ Revenue share / billing to their end customers
│   │   │
│   │   │   └── PELANGGAN ADMIN (End Customer Tenant)
│   │   │       ├─ Organization & branches
│   │   │       ├─ Modules activated (based on plan)
│   │   │       ├─ Users, roles, permissions
│   │   │       └─ Business operations (POS, inventory, etc.)
│   │   │
│   │   └── PELANGGAN ADMIN (End Customer Tenant #2)
│   │       └─ ...same structure...
│   │
│   └── WHITE-LABEL ADMIN (Agency #2)
│       └─ ...same structure...
```

### 4b.2 Permission Matrix

| Fungsi | Master Admin | Super Admin | White-label Admin | Pelanggan Admin |
|--------|:------------:|:-----------:|:------------------:|:--------------:|
| Ubah harga platform | ✅ | ❌ | ❌ | ❌ |
| Cipta/edit pricing plans | ✅ | ✅ | ❌ | ❌ |
| Manage semua tenant | ✅ | ✅ | ❌ | ❌ |
| Manage tenant sendiri | ✅ | ✅ | ✅ (yang dialokasikan) | ❌ |
| White-label branding | ✅ | ✅ | ✅ (untuk diri mereka) | ❌ |
| Custom domain tenant | ✅ | ✅ | ✅ (untuk tenant mereka) | ✅ (tenant mereka) |
| AI Gateway config | ✅ | ✅ | ❌ | ❌ |
| Model catalog | ✅ | ✅ | ❌ | ❌ |
| Buy AI credits | ✅ | ✅ | ✅ (bulk purchase) | ✅ (top-up) |
| Tenant subscription | ✅ | ✅ | ✅ (subscribe untuk tenant mereka) | ✅ (upgrade/downgrade) |
| Module activation | ✅ | ✅ | ✅ (untuk tenant mereka) | ✅ (plan mereka allow) |
| Organization setup | ✅ | ✅ | ❌ | ✅ |
| Branch management | ✅ | ✅ | ❌ | ✅ |
| User management | ✅ | ✅ | ✅ (tenant mereka) | ✅ (org mereka) |
| Role & permission | ✅ | ✅ | ✅ (tenant mereka) | ✅ (org mereka) |
| View all analytics | ✅ | ✅ | ❌ | ❌ |
| View tenant analytics | ✅ | ✅ | ✅ (tenant mereka) | ✅ (org mereka) |
| Invoice to end customer | ✅ | ✅ | ✅ (mereka bill customer) | ❌ |
| Payment collection | ✅ | ✅ | ✅ (collect from their tenants) | ❌ |
| Support desk | ✅ | ✅ | ✅ (for their tenants) | ✅ (for their customers) |
| Export data | ✅ | ✅ | ✅ (tenant mereka) | ✅ (org mereka) |
| Delete account | ✅ | ✅ | ✅ (cancel subscription) | ✅ (request deletion) |

### 4b.3 Admin Role Database Schema

```sql
-- Admin role hierarchy definition
CREATE TABLE admin_roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(100),
    slug VARCHAR(100),
    description TEXT,
    level ENUM('master','super','white_label','customer'),
    created_at TIMESTAMP
);

-- Admin user assignment (link user to role + tenant scope)
CREATE TABLE admin_role_assignments (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    tenant_id BIGINT NULL,
    branch_id BIGINT NULL,
    assigned_at TIMESTAMP,
    assigned_by BIGINT
    -- tenant_id = NULL untuk master/super admin (global access)
);

-- White-label configuration
CREATE TABLE white_label_configs (
    id BIGINT PRIMARY KEY,
    admin_id BIGINT NOT NULL,
    parent_brand_id BIGINT NULL,
    custom_logo_url VARCHAR(500),
    custom_primary_color CHAR(7),
    custom_font VARCHAR(255),
    custom_domain VARCHAR(255),
    white_label_enabled BOOLEAN DEFAULT FALSE,
    invoice_footer_text TEXT,
    revenue_share_percentage DECIMAL(5,2),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Tenant allocation (which white-label admin manages which tenants)
CREATE TABLE wl_tenant_allocations (
    id BIGINT PRIMARY KEY,
    wl_admin_id BIGINT NOT NULL,
    tenant_id BIGINT NOT NULL,
    allocated_at TIMESTAMP,
    notes TEXT
);

-- Revenue sharing tracking
CREATE TABLE revenue_shares (
    id BIGINT PRIMARY KEY,
    tenant_subscription_id BIGINT,
    wl_admin_id BIGINT,
    amount_total DECIMAL(12,2),
    wl_revenue DECIMAL(12,2),
    platform_revenue DECIMAL(12,2),
    share_percentage DECIMAL(5,2),
    calculated_at TIMESTAMP,
    paid_at TIMESTAMP NULL,
    payout_reference VARCHAR(255)
);
```

---

## BAB 5 — Role, Permission & Entitlement

### 5.1 Granular Permission Model

Role bukan satu-satunya pemeriksaan; setiap tindakan juga perlu polisi objek, status dan skop organisasi.

Permission granular mengikut:
- **Modul** — apakah modul yang boleh diakses
- **Tindakan** — create, read, update, delete, approve, export
- **Lokasi** — branch mana yang boleh diakses
- **Rekod** — rekod mana yang boleh diubah (ownership-based)

### 5.2 Subscription Entitlement

Middleware dan domain services memeriksa status langganan, entitlement dan kuota.

Jadual: `plans`, `plan_features`, `subscriptions`, `tenant_modules`, `usage_records`, `billing_events`.

UI menyembunyikan modul yang tidak aktif, tetapi kawalan benar tetap di backend.

---

## BAB 6 — Subscription, Billing & Usage Metering

### 6.1 Pelan Langganan (Configurable dari Master Admin)

Semua harga pakej boleh diubah dari **Master Admin Panel** tanpa perlu ubah kod. Setiap negara boleh ada harga berbeza mengikut kuasa beli dan mata wang tempatan.

> **PENTING:** Subscription (platform access) dan AI Credits (model usage) adalah BERASAHAN.
> - Subscription = akses ke modul platform (B-Site, B-HRMS, B-Finance, dll)
> - AI Credits = penggunaan AI model API (GPT-6, Claude, Qwen, dll)
> - Tenant boleh beli AI credits secara add-on melebihi quota pelan mereka

> **⚠️ NOTA PENTING — MODEL CATALOG (A-24):**
>
> Nama model dalam dokumen ini adalah **TARGET/HYPOTHETICAL** dan mungkin sudah tidak relevan.
> Sistem pengeluaran MESTI mengimport senarai model aktif dari provider API pada masa build/deploy:
>
> ```sql
> -- Model Catalog Table (production requirement)
> CREATE TABLE ai_model_catalog (
>     id BIGINT PRIMARY KEY,
>     provider_slug VARCHAR(50) NOT NULL,  -- openai, anthropic, google, qwen, dll
>     provider_model_id VARCHAR(100) NOT NULL,  -- e.g., 'gpt-6-astra', 'claude-opus-4-8'
>     display_name VARCHAR(255),
>     tier ENUM('flash','standard','premium','ultra'),
>     capability TEXT,  -- text, image, audio, video, code
>     max_context INT,  -- max context window
>     input_rate DECIMAL(10,6),  -- per token cost
>     output_rate DECIMAL(10,6),  -- per token cost
>     is_active BOOLEAN DEFAULT TRUE,
>     version_snapshot VARCHAR(50),  -- API version yang di-test
>     availability_status ENUM('available','beta','deprecated','sunset'),
>     last_verified DATE,  -- tarikh terakhir verifikasi dari API
>     created_at TIMESTAMP,
>     updated_at TIMESTAMP,
>     UNIQUE(provider_slug, provider_model_id)
> );
> ```
>
> **Semua rujukan model dalam kod MESTI merujuk `ai_model_catalog`, bukan hardcoded string.**

#### Default Plan Structure (Malaysia - MYR)

> **⚠️ STATUS MODUL — BACA DULU:**
> 
> Modul dalam jadual di atas dibahagikan kepada dua kategori:
> 
> | Status | Maksud | Contoh |
> |--------|--------|--------|
> | ✅ **Available Now** | Modul sudah boleh diaktifkan dan digunakan | Core, POS, Basic CRM |
> | 🎯 **Planned / Target** | Modul dalam roadmap pembangunan, belum boleh diaktifkan | HRMS, Payroll, SCM, Manufacturing |
> 
> **Harga pelan adalah TARGET dan mungkin berubah** sebelum semua modul tersedia. Pelanggan yang mendaftar sekarang akan mendapat akses ke modul "Available Now", dan modul "Planned" akan ditawarkan pada harga yang sama apabila ia dilancarkan.

| Pakej | Harga/Bulan | AI Credits (Bulan) | Model Calls | Pengguna | Storage | Modul |
|-------|-------------|-------------------|-------------|----------|---------|-------|
| **Starter** | RM 288 | 80 juta tokens | ~80,000 calls | Unlimited | 10 GB | ✅ Core + POS + Basic CRM |
| **Professional** | RM 488 | 200 juta tokens | ~200,000 calls | Unlimited | 50 GB | ✅ Core + POS + 🎯 HRMS, Payroll, Marketing |
| **Business** | RM 888 | 1 bilion tokens | ~1,000,000 calls | Unlimited | 200 GB | ✅ Core + POS + 🎯 SCM, Manufacturing, Analytics |
| **Business Pro** | RM 1,288 | 2.25 bilion tokens | ~2,250,000 calls | Unlimited | 500 GB | ✅ Semua Business + 🎯 Priority Support, Custom Integrations |
| **Enterprise** | RM 1,888 | Unlimited* | Custom SLA | Unlimited | 1 TB+ | ✅ Semua modul + White-label + 🎯 Dedicated Support |

> *Enterprise: "Unlimited" merujuk kepada negotiated cap yang sangat tinggi. Actual API usage tetap tracked untuk monitoring.

#### Multi-Country Pricing Examples

| Plan | Malaysia (MYR) | Singapore (SGD) | Indonesia (IDR) | Pakistan (PKR) | Bangladesh (BDT) |
|------|---------------|----------------|----------------|----------------|------------------|
| **Starter** | RM 288/mth | S$ 85/mth | Rp 350.000/bulan | Rs 6.000/bulan | ৳ 3.200/bulan |
| **Professional** | RM 488/mth | S$ 145/mth | Rp 600.000/bulan | Rs 11.000/bulan | ৳ 5.800/bulan |
| **Business** | RM 888/mth | S$ 265/mth | Rp 1.200.000/bulan | Rs 20.000/bulan | ৳ 11.000/bulan |
| **Business Pro** | RM 1,288/mth | S$ 385/mth | Rp 1.700.000/bulan | Rs 28.000/bulan | ৳ 15.500/bulan |
| **Enterprise** | RM 1,888/mth | S$ 565/mth | Rp 2.600.000/bulan | Rs 40.000/bulan | ৳ 22.000/bulan |

> **Nota:** Semua harga di atas adalah CONTOH. Super Admin boleh:
> - Ubah mana-mana harga untuk mana-mana negara
> - Tambah atau buang plan baru
> - Set discount/promo codes
> - Configure annual billing dengan auto-discount
> - Enable/disable country-specific plans

#### Database Schema — Configurable Plans

```sql
-- Global plan definitions (plan structure, not prices)
CREATE TABLE plans (
    id BIGINT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Country-specific pricing for each plan
CREATE TABLE country_pricing_plans (
    id BIGINT PRIMARY KEY,
    plan_id BIGINT REFERENCES plans(id),
    country_code CHAR(2) NOT NULL,
    currency CHAR(3) NOT NULL,
    base_price DECIMAL(12,2) NOT NULL,
    discount_percent DECIMAL(5,2) DEFAULT 0.00,
    annual_discount_percent DECIMAL(5,2) DEFAULT 0.00,
    -- final_price = base_price after regular discount, then apply annual discount if applicable
    final_price DECIMAL(12,2) GENERATED ALWAYS AS (
        base_price * (1 - discount_percent / 100) * (1 - 
            CASE WHEN billing_cycle = 'annual' THEN annual_discount_percent ELSE 0 END / 100
        )
    ) STORED,
    billing_cycle ENUM('monthly','quarterly','annual') DEFAULT 'monthly',
    is_active BOOLEAN DEFAULT TRUE,
    effective_from DATE,
    effective_to DATE,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(plan_id, country_code, billing_cycle)
);

-- Plan features (module entitlements)
CREATE TABLE plan_features (
    id BIGINT PRIMARY KEY,
    plan_id BIGINT REFERENCES plans(id),
    module_slug VARCHAR(100) NOT NULL,
    token_limit BIGINT DEFAULT 0,
    user_limit INT DEFAULT -1,
    storage_gb INT DEFAULT 0,
    api_rate_limit INT DEFAULT 0,
    is_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP
);

-- Subscriptions
CREATE TABLE subscriptions (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    plan_id BIGINT REFERENCES plans(id),
    country_code CHAR(2) NOT NULL,
    currency CHAR(3) NOT NULL,
    monthly_price DECIMAL(12,2) NOT NULL,
    billing_cycle ENUM('monthly','quarterly','annual') DEFAULT 'monthly',
    status ENUM('trial','active','past_due','canceled','paused') DEFAULT 'trial',
    trial_ends_at TIMESTAMP NULL,
    current_period_start TIMESTAMP NOT NULL,
    current_period_end TIMESTAMP NOT NULL,
    canceled_at TIMESTAMP NULL,
    cancel_reason TEXT,
    payment_method JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 6.2 Token Packages (Configurable dari Master Admin)

Token packages boleh dibeli sebagai one-time atau recurring. Super Admin boleh ubah harga retail dan wholesale cost.

#### Default Token Pricing Tiers (Malaysia)

| Tokens | Kos Wholesale | Jual Retail (MYR) | Margin | Contoh Negara Lain |
|--------|--------------|-------------------|--------|-------------------|
| **80 Juta** | RM 5 | RM 79 | ~94% | SG: S$25, ID: Rp 130.000, PK: Rs 1.800 |
| **200 Juta** | RM 10 | RM 159 | ~94% | SG: S$49, ID: Rp 250.000, PK: Rs 4.000 |
| **1.1 Bilion** | RM 50 | RM 499 | ~90% | SG: S$149, ID: Rp 750.000, PK: Rs 10.000 |
| **2.25 Bilion** | RM 100 | RM 999 | ~90% | SG: S$299, ID: Rp 1.400.000, PK: Rs 20.000 |

> **Wholesale costs are HIDDEN from customers.** Only Super Admin can see and modify these.

#### Token Purchase Options

- **One-time purchase:** Top-up wallet balance (no expiry or 12-month expiry)
- **Recurring top-up:** Auto-recharge when balance < threshold
- **Bundled with plan:** Enterprise gets X million tokens included
- **Promo tokens:** Bonus tokens during launch campaigns
- **Referral tokens:** Reward for successful referrals

### 6.3 Exchange Rate Management

Super Admin boleh set exchange rate secara manual atau auto-fetch dari API.

```sql
-- Dynamic exchange rate management
CREATE TABLE exchange_rates (
    id BIGINT PRIMARY KEY,
    from_currency CHAR(3) NOT NULL,
    to_currency CHAR(3) NOT NULL,
    rate DECIMAL(15,8) NOT NULL,
    source ENUM('manual','api','auto') DEFAULT 'manual',
    last_updated TIMESTAMP,
    expires_at TIMESTAMP,
    UNIQUE(from_currency, to_currency)
);

-- Example: Base currency MYR (rate = how much 1 unit of from_currency is worth in to_currency)
-- Convention: from_currency -> to_currency, where to_currency = MYR (base)
-- 1 SGD -> MYR = 3.45000000 (1 Singapore Dollar = 3.45 Malaysian Ringgit)
-- 1 USD -> MYR = 4.47000000 (1 US Dollar = 4.47 Malaysian Ringgit)
-- 1 IDR -> MYR = 0.00057143 (1 Indonesian Rupiah = ~0.00057 MYR / RM1.75 per 1,000 IDR)
-- 1 SGD -> MYR = 3.45000000 (inverse of MYR->SGD would be 0.28985507)
```

**Exchange Rate Features:**
- Manual override: Super Admin boleh ubah bila-bila masa
- Auto-fetch: Integrasi dengan exchangerate-API, OpenExchangeRates, atau Bank Negara Malaysia
- Rate expiry: Set expiry date untuk提醒 update
- Historical rates: Simpan history untuk audit trail
- Rounding rules: Round prices ke nearest friendly number (RM 99, bukan RM 97.33)

### 6.4 Usage Metering

- Tracking penggunaan AI tokens per tenant, per agent
- Monitoring had pengguna, storage, API calls
- Alert apabila menghampiri had pelan (>80%, >95%, >100%)
- Overage policies: auto-top-up, throttle, atau block
- Billing adjustments untuk usage beyond plan limits
- Country-specific overage rates

### 6.5 Payment Gateway Integration

Multi-gateway support mengikut negara:

| Negara | Gateway | Payment Methods |
|--------|---------|----------------|
| **Malaysia** | ToyyibPay, Billpluz, Stripe | FPX, Card, E-Wallet, Installment |
| **Singapore** | Stripe, PayPal, GrabPay | Card, GrabPay, PayNow |
| **Indonesia** | Midtrans, Xendit | GoPay, DANA, ShopeePay, BCA Virtual Account |
| **Pakistan** | JazzCash, EasyPaisa, Stripe | JazzCash, EasyPaisa, Card |
| **Global** | Stripe, PayPal | Card, PayPal, Apple Pay, Google Pay |

### 6.6 Tax & Compliance

| Country | Tax Type | Rate | Configuration |
|---------|----------|------|---------------|
| **Malaysia** | SST | 6% / 8% | Auto-calculate based on service type |
| **Singapore** | GST | 9% | Auto-register if revenue > SGD 1M |
| **EU** | VAT | 19-25% | OSS scheme for digital services |
| **UK** | VAT | 20% | Digital services VAT |
| **US** | Sales Tax | Varies by state | Nexus-based calculation |
| **No Tax** | N/A | 0% | Countries without local tax framework |

---

## BAB 7 — Notifications System

- E-mel, WhatsApp, SMS, push notifications
- Template-based notifications
- Preference per user per channel
- Delivery tracking dan retry policy
- Consent management untuk marketing communications

---

## BAB 8 — Files & Document Storage

- Object storage (Cloudflare R2 / AWS S3)
- Tenant-isolated storage paths
- File metadata, versioning, access control
- Virus scanning pada upload
- Thumbnail generation untuk images
- Document templates dengan merge fields

---

## BAB 9 — Audit Trail

### 9.1 Core Requirements
- Append-only audit log
- Record actor, tenant, action, resource, timestamp, request ID, approver
- Financial changes, stock movements, payroll, access changes, AI actions
- Protected from normal modification
- Export capability for compliance

### 9.2 Data Retention & Purge Policy

> **⚠️ PERHATIAN:** Tempoh retention ditentukan oleh keperluan undang-undang Malaysia dan antarabangsa. Setiap kategori mempunyai dasar berbeza.

| Data Category | Retention Period | Legal Basis | Storage Tier | Purge Method |
|--------------|-----------------|-------------|-------------|-------------|
| Authentication events (login, logout, 2FA) | 3 years | Akta Komunikasi dan Multimedia 1998 (CMA) — log akses | Hot (MySQL) | Soft delete + archive |
| User activity logs | 2 years | PDPA 2010 — reasonable period | Warm (MySQL → Archive) | Batch delete by tenant_id |
| File access logs | 2 years | PDPA 2010 — reasonable period | Warm (MySQL + R2 metadata) | Batch delete |
| Financial transactions | 7 years | Akta Akuntansi dan Audit 2013 (AAA 2013), Section 149 | Cold (Archive table) | Immutable, legal hold |
| Invoice documents | 7 years | AAA 2013 + e-Invois LHDN (3 tahun selepas jana cukai) | Cold (R2 archive tier) | Immutable, legal hold |
| EPF/KWSP records | 7 years | Employees Provident Fund Act 1991 | Cold (Archive table) | Immutable, legal hold |
| SOCSO/PERKESO records | 7 years | Social Security Act 1969 | Cold (Archive table) | Immutable, legal hold |
| PCB tax records | 7 years | Income Tax Act 1967 | Cold (Archive table) | Immutable, legal hold |
| SST/e-Invois records | 7 years (e-Invois: 3 tahun selepas Cukai) | e-Invois LHDN guideline 2024-2027 | Cold (R2 archive tier) | Immutable, legal hold |
| AI conversation history | 2 years | PDPA 2010 — reasonable period | Hot/Warm (MySQL) | User-requested deletion |
| AI task execution logs | 3 years | Audit trail requirement | Warm (MySQL) | Batch purge |
| System error logs | 1 year | Operational necessity | Warm (Sentry + MySQL) | Auto-prune |
| Performance metrics | 90 days | Operational necessity — no legal requirement | Hot (Laravel Pulse → ClickHouse) | TTL cleanup |
| Session tokens | 30 days | Security best practice | Hot (Redis + MySQL) | Auto-expire |
| Temporary cache | As configured | No legal requirement | Hot (Redis) | TTL-based |
| Backup snapshots | 1 year rolling | Operational recovery need | Cold (R2 Glacier) | Auto-delete old snapshots |
| Employment records | 6 years | Akta Kerja 1955 — limitation period untuk tuntutan | Cold (Archive table) | Legal hold check before purge |
| Customer orders | 7 years | Sales tax / SST requirement | Cold (Archive table) | Immutable after retention end |

**Nota Penting:**
- "7 years" bermula dari **tarikh tamat hubungan perniagaan** (account closure, last transaction date)
- "Legal hold" = rekod tidak boleh dipadam jika tertakluk kepada siasatan atau litigasi
- Tenant admin boleh request early purge untuk data bukan-statutori (dengan approval Super Admin)

### 9.3 Purge Procedures

```
Purge Workflow:
1. Scheduled job runs daily (02:00 Asia/Kuala_Lumpur)
2. Query records exceeding retention period per category
3. Mark as 'pending_purge' in audit trail
4. Notify tenant admin 30 days before purge (if > 1000 records)
5. Execute batch delete in chunks of 10,000 rows
6. Verify record count post-purge
7. Log purge operation: {tenant_id, category, count, operator}
8. Generate monthly purge report for compliance
```

### 9.4 Data Subject Rights & Deletion Policy (PDPA 2010 / Akta 709)

> **⚠️ NOTA PENTING:** Istilah "Right to Be Forgotten" dan "Article 13" berasal dari GDPR, BUKAN dari Akta Perlindungan Data Peribadi 2010 (Act 709) Malaysia. Hak-hak di bawah berdasarkan teks semasa PDPA 2010 dan pindaan terkini. Nasihat undang-undang rasmi perlu diperoleh sebelum implementasi pengeluaran.

Bila user/meshaketanya memadam data peribadi mereka:

**Hak Pemadaman (Under PDPA 2010):**
- Pengguna boleh meminta pemadaman data peribadi yang diproses dengan consent
- Pemproses mesti mematuhi permintaan dalam tempoh yang munasabah
- Consent boleh ditarik balik pada bila-bila masa

**Prosedur Pemadaman:**
1. Delete/pseudonymize personal data dari `users`, `profiles`, `preferences`
2. Anonymize conversation history — remove user identity, keep aggregate analytics
3. Remove vector embeddings from Qdrant namespace (jika tidak diperlukan untuk tujuan lain)
4. Keep financial/legal records yang diwajibkan oleh undang-undang (e.g., SST 7 tahun, e-Invois retention)
5. Log deletion request: {user_id, requested_by, timestamp, scope, verified_by, legal_basis_for_retention}
6. Confirmation email within 30 days (bukan 24 jam — PDPA memberi tempoh munasabah)

**Pengecualian (Data yang TIDAK boleh dipadam atas sebab undang-undang):**
- Rekod kewangan & invois (Akta Akuntansi 2013, Kanun Tatausaha Kewangan)
- Rekod payroll & statutori (EPF, SOCSO, PCB requirements)
- Rekod e-Invois LHDN
- Rekod yang tertakluk kepada legal hold atau proses undang-undang

**Nota:** "Semua memori dipadam" bukan realiti teknikal atau undang-undang. Hash, anonymized aggregates, dan backup archives mungkin kekal mengikut polisi retention yang didokumenkan.

### 9.5 Audit Export Formats

- **CSV** — For accounting and manual review
- **JSON** — For programmatic access and API consumption
- **PDF** — For official submissions (LHDN, auditors)
- **SQL dump** — For migration and backup verification


---

## BAB 10 — API, Webhooks & Integrations

### 10.1 API Design

- REST API berversi: `/api/v1`
- OpenAPI dijana dan diuji dalam CI
- Resource, pagination, error envelope dan idempotency conventions konsisten
- Rate limit mengikut token, tenant, endpoint dan pelan
- API luar menggunakan OAuth2 scopes dan lifecycle client credentials yang jelas
- Webhooks bertandatangan, timestamped dan mempunyai perlindungan replay

### 10.2 Webhooks

- Signed webhooks dengan retry, idempotency dan log penghantaran
- Tenant-specific webhook management
- Controlled replay option
- Dead-letter queue untuk failed webhooks

### 10.3 Rate Limiting & Quotas

| Tier | Requests/Minute | Requests/Day | Burst Limit | Overquota Action |
|------|----------------|-------------|-------------|------------------|
| **Free Trial** | 30 rpm | 5,000 rd | 50 | Queue + notify |
| **Starter (RM288)** | 100 rpm | 20,000 rd | 150 | Throttle |
| **Professional (RM488)** | 300 rpm | 50,000 rd | 450 | Allow burst |
| **Business (RM888)** | 500 rpm | 100,000 rd | 750 | Allow burst |
| **Business Pro (RM1,288)** | 1,000 rpm | 200,000 rd | 1,500 | Priority queue |
| **Enterprise (RM1,888)** | 2,000 rpm | Unlimited | 3,000 | Priority queue |
| **Admin/Internal** | Unlimited | Unlimited | N/A | N/A |

**Rate Limit Headers:**
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 87
X-RateLimit-Reset: 1697635200
Retry-After: 30 ( bila overlimit )
```

**Quota Enforcement:**
- Token-based per tenant (shared across all users)
- Endpoint-specific limits untuk resource-intensive operations (AI calls, exports)
- Sliding window algorithm (bukan fixed window) untuk prevent burst attacks
- Grace period: 10% overlimit allowed selama max 5 minutes
- Alert pada 80%, 90%, 100% quota usage

### 10.4 API Versioning Policy

| Version Status | Lifecycle | Deprecation Notice | Support Timeline |
|---------------|-----------|-------------------|------------------|
| **Current (v2)** | Active development | N/A | Ongoing |
| **Previous (v1)** | Maintenance mode | 6 months notice | 12 months from deprecation |
| **Deprecated** | Read-only | Announced | End of support date |
| **Retired** | Removed | N/A | N/A |

**Version Migration Guide:**
- Setiap breaking change memerlukan new major/minor version
- Changelog published setiap release
- Migration assistant tool disediakan untuk major version upgrades
- Dual-mode support selama transition period (min 6 bulan)
- Sunset header pada deprecated endpoints:
  ```
  Sunset: Sat, 01 Jan 2028 00:00:00 GMT
  Deprecation: Tue, 01 Oct 2024 00:00:00 GMT
  Link: <https://api.aibijan.com/v2/docs>; rel="successor-version"
  ```

### 10.5 API Authentication Methods

| Method | Use Case | Security Level |
|--------|----------|---------------|
| **Bearer Token (JWT)** | User-facing apps, mobile | High (short-lived + refresh) |
| **API Key** | Server-to-server, webhooks | Medium (scoped by tenant) |
| **OAuth2 Client Credentials** | Third-party integrations | High (scoped, revocable) |
| **Webhook Signature** | Webhook verification | Medium (per-request HMAC) |
| **mTLS** | Internal service communication | Very High (certificate-based) |

### 10.6 Data Migration Framework

#### Import Sources Supported

| Source | Format | Tool | Validation |
|--------|--------|------|------------|
| **Excel / CSV** | .xlsx, .csv | Built-in importer | Column mapping, type check, duplicate detection |
| **Google Sheets** | Google API | Connector import | Auth required, real-time sync optional |
| **JSON API** | JSON response | API importer | Schema validation, field mapping |
| **Database dump** | SQL, Parquet | Migration script | Schema comparison, data transformation |
| **Legacy system** | Proprietary format | Custom adapter | Per-system mapping config |

#### Migration Process

```
Data Migration Workflow:
1. Upload source file / connect to source system
2. System auto-detects schema and suggests column mapping
3. User confirms/reviews mapping (visual drag-and-drop UI)
4. Preview sample rows with validation results
5. Run dry-run: detect duplicates, missing required fields, type mismatches
6. User approves migration plan
7. Execute migration in batches (1,000 records/batch)
8. Real-time progress dashboard
9. Post-migration validation report
10. Rollback option available within 24 hours
```

#### Migration Data Model

| Table | Purpose |
|-------|---------|
| `migration_jobs` | Track import/export jobs |
| `migration_mappings` | Source-to-target field mappings |
| `migration_batches` | Batch-level tracking and status |
| `migration_errors` | Per-record error details |
| `migration_rollback_logs` | Undo history for rollback capability |

### 10.7 SDK & Developer Resources

- **PHP SDK** (Laravel-first, Composer package)
- **JavaScript/TypeScript SDK** (npm package, browser + Node.js)
- **Python SDK** (pip package, for data science/analytics)
- **Postman Collection** (complete API reference with examples)
- **Code Samples** per module (copy-paste ready snippets)
- **Sandbox Environment** (`api-staging.aibijan.com`) for testing


---

## BAB 11 — Localization & Malaysia Configuration

- Format mata wang MYR
- Zon waktu `Asia/Kuala_Lumpur`
- Bahasa Melayu dan Inggeris
- Templat yang boleh dikonfigurasi untuk keperluan Malaysia
- SST (Sales and Service Tax) configuration
- e-Invois LHDN integration
- EPF/SOCSO/PCB payroll compliance

---

# PART III — BUSINESS MODULES

## BAB 12 — 18 B-Modules Katalog (7 Kumpulan)

### Kumpulan 1: Operasi Dalaman & Pasukan

| Modul | Pemilikan Domain | Event Contoh |
|-------|-----------------|--------------|
| **B-Virtual Office** | Agent, Task, Board, Schedule | `TaskCompleted` |
| **B-HRMS** | Employee, Shift, Leave, Payroll | `PayrollApproved` |
| **B-Payroll** | SalaryComponent, Payslip, StatutoryLedger, EPFRecord, SOCSORecord, PCBRecord, EAREcord | `PayslipGenerated`, `EPFSubmitted`, `EAFinalized` |
| **B-SCM** | Supplier, RFQ, PurchaseOrder, SerialTrack, BatchTrack, DemandPlan, ABCClass, VTFRReport | `PurchaseOrderApproved`, `StockReceived`, `DemandForecastUpdated` |
| **B-Sign & Docs** | Document, Template, Signature | `DocumentSigned` |
| **B-Projects** | Project, Milestone, Task | `ProjectCompleted` |
| **B-Assets** | Asset, Category, Maintenance | `AssetDisposed` |

### Kumpulan 2: Jualan, Saluran & Pertumbuhan

| Modul | Pemilikan Domain | Event Contoh |
|-------|-----------------|--------------|
| **B-POS** | Register, Sale, CashShift, Receipt | `SaleCompleted` |
| **B-Site** | Site, Page, Theme, Domain, Section, Post, Form, Analytics | `SitePublished`, `PageViewed` |
| **B-Link** | LinkPage, DigitalCard, Click | `LinkClicked` |
| **B-Marketplace** | Listing, Cart, Review | `OrderPlaced` |
| **B-Affiliate** | Affiliate, ReferralCode, Commission | `CommissionEarned` |

### Kumpulan 3: Pelanggan, Pemasaran & Sokongan

| Modul | Pemilikan Domain | Event Contoh |
|-------|-----------------|--------------|
| **B-Loyalty** | Member, PointsLedger, Reward, Tier | `PointsEarned`, `RewardRedeemed` |
| **B-Omnichannel** | Conversation, Channel, Message | `MessageReceived` |
| **B-Booking** | Service, Slot, Appointment, Provider | `AppointmentBooked` |
| **B-Marketing** | Campaign, Segment, Voucher, Contact, Deal, Sequence, Workflow, LeadScore | `CampaignSent`, `AdChangeApproved`, `LeadConverted` |
| **B-Helpdesk** | Ticket, Category, SLA | `TicketEscalated` |

### Kumpulan 4: Stok, Pengeluaran & COGS

| Modul | Pemilikan Domain | Event Contoh |
|-------|-----------------|--------------|
| **B-COGS Engine** | CostCalculation, MarginReport | `CostAdjusted` |
| **B-Inventory** | Product, SKU, StockMove, Warehouse | `StockLow`, `StockAdjusted` |
| **B-BOM & Manufacturing** | BOM, Recipe, WorkOrder | `ProductionCompleted` |

### Kumpulan 5: Kewangan, Pembelian & Logistik

| Modul | Pemilikan Domain | Event Contoh |
|-------|-----------------|--------------|
| **B-Finance** | Account, JournalEntry, BankTransaction | `JournalPosted` |
| **B-Invoicing** | Quote, Invoice, CreditNote, MyInvois | `InvoiceIssued`, `InvoicePaid` |
| **B-Procurement** | Supplier, PurchaseOrder, SupplierBill | `PurchaseOrderApproved` |
| **B-Logistics** | Warehouse, StockMove, Shipment | `ShipmentDelivered` |

### Kumpulan 6: Kebolehcapaian & Infrastruktur

| Modul | Pemilikan Domain | Event Contoh |
|-------|-----------------|--------------|
| **B-Connect / API Gateway** | APIEndpoint, Webhook, Integration | `WebhookDelivered` |
| **B-Auth & Security** | User, Role, Permission, Session | `UserLoggedIn`, `AccessDenied` |

### Kumpulan 7: Dashboard & Intelligence

| Modul | Pemilikan Domain | Event Contoh |
|-------|-----------------|--------------|
| **B-Analytics** | Metric, Report, Dashboard | `MetricRefreshed` |
| **B-Brain Hub** | Insight, Forecast, Recommendation | `InsightGenerated` |
| **B-Document AI** | Document, Extraction, ConfidenceScore, ReviewQueue | `DocumentProcessed`, `DataExtracted`, `ReviewCompleted` |

### Modul Teras Tambahan

- **Core Identity & Tenancy:** akaun, organisasi, membership dan akses
- **Core Commerce:** pelanggan, produk, varian, cart/order dan order items
- **B-Pay:** payment intent, payment, refund, settlement dan rekonsiliasi
- **B-Payroll:** struktur gaji, komponen statutori (EPF/SOCSO/EIS/PCB), payslip, EA Form
- **B-SCM:** supplier management, RFQ, demand planning, serial/batch tracking, ABC/VTFR analysis
- **B-Document AI:** OCR, invoice/receipt extraction, contract analysis, document processing
- **B-Notify:** e-mel, WhatsApp, SMS, push dan template
- **B-Flow / B-Trigger:** workflow runtime dan event triggers
- **B-Intel / B-Scout / B-Compliance:** local intelligence dan compliance knowledge

### Pemilikan Data yang Wajib

- Core Commerce memiliki `customers`, `products`, `product_variants`, `orders`, `order_items`
- B-Marketplace memiliki listing dan pengalaman marketplace, bukan salinan order utama
- B-Pay memiliki rekod pembayaran/refund; tidak menganggap order sebagai payment
- B-Finance memiliki akaun perakaunan dan jurnal
- B-Invoicing memiliki dokumen invois dan status e-Invois
- B-Logistics memiliki stok fizikal, pergerakan stok dan penghantaran
- B-Marketing memiliki kempen pemasaran serta konfigurasi integrasi ads

---

## BAB 13 — COGS Engine, Inventory States & BOM

### 13.1 B-COGS Engine

B-COGS Engine ialah servis pengiraan kos jualan dan margin yang menjadi sumber pengiraan kos bagi modul POS, marketplace, inventory, manufacturing dan finance.

#### Kaedah Kos

- FIFO atau Weighted Average Cost
- **LIFO tidak boleh diaktifkan sebagai kaedah pelaporan statutori secara lalai**
- Kesesuaian kaedah perlu disahkan dengan piawaian dan penasihat perakaunan

#### Formula Asas

- **Untung kasar** = Jualan bersih − COGS
- **Margin kasar (%)** = Untung kasar ÷ Jualan bersih × 100
- **Margin sumbangan** = Jualan bersih − kos berubah yang ditetapkan
- **Untung bersih** = hasil − semua perbelanjaan yang diiktiraf dalam tempoh

#### Peraturan

- Kos belian termasuk freight-in, insurans penghantaran dan caj berkaitan
- Bezakan COGS daripada margin sumbangan dan keuntungan bersih
- Payment gateway fee, komisen marketplace, penghantaran keluar, pemasaran, overhead dan cukai direkodkan dalam kategori yang betul
- Amaran apabila margin atau keuntungan kasar jatuh di bawah sasaran
- Rekod kos pada masa transaksi dan jejak perubahan
- Pengiraan semula terkawal dengan rekod versi dan sebab pelarasan

### 13.2 B-Inventory States

| State | Keterangan |
|-------|------------|
| **Available** | Stok tersedia untuk dijual/digunakan |
| **Reserved** | Stok ditempah oleh order belum fulfilled |
| **In Transit** | Stok dalam perjalanan dari supplier |
| **Damaged** | Stok rosak, perlu adjustment |
| **Quarantine** | Stok kuarantin, quality hold |
| **Safety Stock** | Stok keselamatan minimum |

### 13.3 B-BOM & Manufacturing

- BOM/recipe untuk produk siap, separa siap dan kit
- Kuantiti bahan, unit ukuran, kadar kehilangan/wastage dan versi resipi
- Work order, penggunaan bahan, hasil pengeluaran, reject dan scrap
- Kos bahan, buruh langsung dan overhead pengeluaran
- Auto-deduct bahan mentah hanya apabila transaksi penjualan/pengeluaran mencapai status yang ditetapkan
- Idempotency control supaya stok tidak ditolak dua kali
- Contoh: burger = 1 roti + 100g daging + 10g sos + 1 kotak pembungkus
- Sokongan unit conversion dengan ketepatan perpuluhan yang ditetapkan

---

## BAB 14 — Finance, Invoicing & e-Invois LHDN

### 14.1 B-Finance

- Carta akaun, jurnal bergu, general ledger dan tempoh kewangan
- Akaun belum terima/bayar, bank reconciliation dan rekod pembayaran
- Penyata untung rugi, kunci kira-kira dan aliran tunai
- Posting daripada POS, inventory, procurement, invoicing, payroll dan manufacturing
- Tutup tempoh, jurnal pelarasan dan kawalan transaksi selepas tutup
- Audit trail dan eksport laporan
- Semua posting perlu seimbang; transaksi kewangan tidak boleh dimasukkan langsung oleh AI tanpa validasi dan kelulusan

### 14.2 B-Invoicing

- Quotation, sales order, invois, credit note, debit note, resit dan bil berulang
- Nomor dokumen mengikut siri/aturan tenant
- Status: draf, diluluskan, dihantar, dibayar, lewat, dibatalkan dan dikreditkan
- Medan cukai dan e-Invois boleh dikonfigurasi mengikut spesifikasi semasa LHDN
- Integrasi e-Invois berdasarkan dokumentasi rasmi LHDN: validasi, submission, UUID/status, pembatalan dan pengurusan ralat
- Jangan mendakwa integrasi atau format telah diluluskan LHDN sehingga diuji dan disahkan

### 14.3 B-Procurement

- Senarai pembekal, item, harga, tempoh kredit dan dokumen pembekal
- Purchase requisition, RFQ, purchase order, goods received note dan supplier bill
- Aliran kelulusan berdasarkan jumlah dan peranan
- Perbandingan harga, sejarah harga dan analisis prestasi pembekal
- Pemisahan tugas: peminta, pelulus, penerima barang dan pembayar tidak semestinya individu yang sama

### 14.4 B-Logistics

- Penghantaran, kurier, tracking, consignment note dan status penghantaran
- Kos freight-in berbanding kos penghantaran keluar
- Pengagihan kos penghantaran kepada item mengikut polisi yang didokumenkan
- Integrasi kurier melalui API/adapter dan pengendalian kegagalan

---

## BAB 15 — Industry Packs (19 Industri + MSIC Mapping)

### 15.1 Prinsip Industry Packs

- Industri bukan sekadar label atau tema dashboard
- Setiap industri perlu mempunyai proses kerja, data, role, laporan dan ujian penerimaan sendiri
- Pelanggan hanya aktifkan modul yang diperlukan
- Satu organisasi boleh ada beberapa aktiviti perniagaan/cawangan/jenama
- Gunakan konfigurasi industri untuk adjust tanpa rebuild aplikasi

### 15.2 Senarai Industry Packs

| # | Industry Pack | Fungsi Khusus Utama | Modul BIJAN |
|---|--------------|---------------------|-------------|
| 1 | **School Transport** | Murid, penjaga, laluan, jadual sekolah, pickup/drop-off, kecemasan | B-School Transport + BILL + FIN + LOG + CRM |
| 2 | **Transport & Fleet** | Kenderaan, pemandu, fuel, mileage, permit, dispatch | B-Transport & Fleet + ASSET + HR + FIN |
| 3 | **Childcare & Education** | Kanak-kanak, kehadiran, yuran, jadual, kontak kecemasan | B-Childcare & Education + BILL + CRM |
| 4 | **Hospitality** | Room availability, booking, check-in/out, housekeeping | B-Hospitality + BOOK + BILL |
| 5 | **Clinic & Care** | Appointment, patient records, consent, clinical records | B-Clinic & Care + BOOK + BILL |
| 6 | **Construction** | Tender, BOQ, progress claim, site diary, subcontractor | B-Construction + PROJ + PROC + FIN |
| 7 | **Property** | Unit inventory, booking, tenancy, deposit, maintenance | B-Property + BILL + DOCS + FIN |
| 8 | **Automotive** | Job card, inspection, spare parts, warranty, service reminder | B-Automotive + INV + BOOK + BILL + FIN |
| 9 | **Agriculture** | Plot, tanaman, musim, input, hasil, kos | B-Agriculture + INV + COGS |
| 10 | **Event & Rental** | Event, package, deposit, milestone, equipment rental | B-Event & Rental + BOOK + BILL + PROJ |
| 11 | **Quality & Compliance** | Audit, evidence checklist, finding, report versions | Quality & Compliance + DOCS + PROJ |
| 12 | **Subscription Business** | Plan, renewal, churn, usage-based billing | B-Subscription Business + FIN + HELP |
| 13 | **Professional Practice** | Matter/file, conflict checks, time billing, trust account | B-Professional Practice + DOCS + PROJ + BILL |
| 14 | **Travel & Tour** | Tour package, passenger manifest, itinerary, supplier | B-Travel & Tour + BOOK + DOCS + BILL |
| 15 | **Waste & Recycling** | Collection schedule, route, bin, proof of service | B-Waste & Recycling + LOG + BILL |
| 16 | **Security Services** | Guard roster, site deployment, incident report, SLA | B-Security Services + HR + HELP |
| 17 | **Community & Association** | Ahli, yuran, mesyuarat, program, grant | B-Community & Association + FIN |
| 18 | **Pet Services** | Pet profile, grooming, vaccination, appointment | B-Pet Services + BOOK + BILL |
| 19 | **Repair & Field Service** | Job request, technician, spare parts, warranty | B-Repair & Field Service + INV + BOOK + BILL |

### 15.3 Keperluan Khusus: Van Sekolah

Sistem mesti menyokong:
- Rekod penjaga yang sah
- Kontak kecemasan
- Individu dibenarkan mengambil murid
- Status pickup/drop-off
- Audit perubahan
- Data kanak-kanak perlu akses terhad
- Tracking lokasi langsung hanya jika benar-benar diperlukan, mendapat kebenaran yang sesuai dan dilaksanakan dengan perlindungan data

### 15.4 MSIC Reference

Gunakan **Malaysia Standard Industrial Classification (MSIC)** DOSM sebagai taksonomi asas:
- Portal carian Kod MSIC 2025, DOSM: https://economiccensus.dosm.gov.my/portal-main/msic-2025
- OpenDOSM katalog MSIC: https://open.dosm.gov.my/data-catalogue/msic
- Simpan kod MSIC, label, versi klasifikasi dan tarikh semakan dalam direktori industri

---

# PART IV — AI ENGINE & PERSONAL ASSISTANTS

## BAB 16 — AI Agents (11 Ejen dengan Tools Spesifik)

### 16.1 Peranan Ejen

| Ejen | Tanggungjawab | Contoh Tools |
|------|--------------|--------------|
| **AI CEO / Chief of Staff** | Menyelaraskan matlamat, delegasi dan ringkasan eksekutif | `create_task`, `assign_agent`, `summarize_workspace` |
| **AI Executive Assistant** | Kalendar, e-mel, nota, susulan dan jadual | `calendar.read`, `email.draft`, `task.create` |
| **AI CMO** | Strategi pemasaran, kandungan, kempen dan analisis saluran | `campaign.draft`, `ads.read`, `content.generate` |
| **AI CFO** | Aliran tunai, bajet, invois, margin dan amaran risiko | `finance.read`, `report.generate`, `invoice.draft` |
| **AI COO** | SOP, operasi, inventori, tugasan dan kapasiti | `inventory.read`, `task.assign`, `operations.report` |
| **AI CSO** | Leads, pipeline jualan, follow-up dan affiliate | `crm.read`, `lead.update`, `sales.report` |
| **AI CHRO** | Kakitangan, jadual, cuti, onboarding dan HR | `hr.read`, `shift.draft`, `leave.review` |
| **AI Customer Support** | Soalan pelanggan, FAQ, pesanan dan eskalasi | `knowledge.search`, `conversation.reply_draft` |
| **AI Ads Specialist** | Google Ads, Meta Ads dan pengoptimuman kempen | `ads.performance.read`, `campaign.draft`, `campaign.publish*` |
| **AI Researcher / Scout** | Trend, pesaing dan sumber maklumat | `web.research`, `competitor.compare` |
| **AI Analyst** | Analisis KPI, ramalan dan insight | `analytics.query`, `forecast.generate` |

`*` Tindakan menerbitkan kempen, mengubah bajet atau membuat perubahan besar mesti melalui polisi kelulusan yang ditetapkan.

### 16.2 Konfigurasi Ejen

Setiap ejen mempunyai:
- ID dan versi konfigurasi
- Peranan, matlamat dan skop tugas
- Senarai tools yang dibenarkan
- Had kos, token, masa dan bilangan tindakan
- Polisi data yang boleh dibaca
- Memori tenant yang dibenarkan
- Polisi kelulusan
- Log input/output, tool calls, keputusan dan ralat
- Penilaian kualiti serta mekanisme untuk menghentikan atau mengulang tugas

### 16.3 AI Governance

- Least privilege
- Human approval untuk tindakan sensitif
- Tool allowlists dan parameter validation
- Had per-run dan per-tenant
- Audit input, output, tools, keputusan dan approver
- Data tenant tidak boleh dicampur
- AI mesti menyatakan ketidakpastian dan sumber bagi jawapan fakta
- Tindakan yang tidak boleh diundur memerlukan pengesahan khusus

---

## BAB 17 — Personal AI Assistant (9 Jenis)

### 17.1 Visi

B-Personal AI Assistant ialah pembantu AI peribadi untuk setiap pemilik bisnes, pengurus, pekerja dan pengguna yang dibenarkan. Bukan sekadar chatbot — ia bertindak sebagai ruang kerja pintar yang memahami konteks perniagaan.

Matlamat: pelanggan boleh berkata:
- "Fahami bisnes saya dan sediakan sistem yang sesuai."
- "Cari pelanggan baharu untuk servis saya."
- "Semak semua lead yang belum dihubungi."
- "Sediakan kempen pemasaran minggu ini."
- "Follow up quotation yang belum dijawab."
- "Semak invois yang masih belum dibayar."
- "Tunjukkan kenapa jualan bulan ini menurun."
- "Beritahu saya perkara yang memerlukan keputusan saya sahaja."

### 17.2 9 Jenis Personal AI Assistant

| # | Jenis | Untuk | Fungsi Utama |
|---|-------|-------|--------------|
| 1 | **Owner AI Assistant** | Pemilik/Pengarah | Ringkasan bisnes, cash-flow, lead, outstanding invoices, risiko, cadangan tindakan |
| 2 | **Executive AI Assistant** | Pengurusan Kanan | Executive briefing, KPI monitoring, meeting prep, decision summaries |
| 3 | **Sales AI Assistant** | Sales Team | Lead qualification, CRM updates, follow-up planning, quotation, pipeline monitoring |
| 4 | **Marketing AI Assistant** | Marketing | Campaign planning, audience segmentation, content generation, SEO, budget monitoring |
| 5 | **Operations AI Assistant** | Operations | Task coordination, workflow monitoring, inventory alerts, vendor coordination |
| 6 | **Finance AI Assistant** | Finance | Invoice summaries, cash-flow forecasts, budget variance, reconciliation exceptions |
| 7 | **HR AI Assistant** | HR | Onboarding checklists, shift planning, leave workflow, policy Q&A |
| 8 | **Customer Service AI Assistant** | Support | FAQ answers, ticket classification, draft replies, escalation |
| 9 | **Industry-Specific Assistant** | Setiap Industri | Event Planner, Retail Ops, F&B Ops, Fleet Ops, Salon Booking, Construction, Property, Professional Services |

### 17.3 Memory Architecture

| Jenis Memori | Contoh | Akses |
|-------------|--------|-------|
| Personal preferences | Bahasa pilihan, format laporan | Pengguna berkenaan |
| Work context | Tugasan semasa, projek | Mengikut permission |
| Business profile | Produk, harga, sasaran syarikat | Pengguna tenant yang dibenarkan |
| Operational records | Orders, invoices, tasks | Modul sumber dan permission |
| Conversation history | Perbualan terdahulu | Mengikut access policy |
| Approved knowledge | SOP, dokumen rasmi, polisi | Mengikut classification |
| Derived insights | Trend jualan, risiko, cadangan | Mengikut data source access |

### 17.4 Memory Rules

- Simpan hanya maklumat yang mempunyai tujuan jelas
- Sediakan fungsi review, correction dan deletion mengikut polisi
- Jangan jadikan andaian AI sebagai fakta yang disahkan
- Rekod sumber dan masa untuk business facts yang penting
- Hormati permission asal apabila mencari semula maklumat
- Jangan gunakan data tenant lain untuk menjawab soalan pelanggan
- Jangan dedahkan data peribadi kepada pengguna yang tidak dibenarkan
- Bezakan fakta, inferens, ramalan dan cadangan
- Data rasmi mesti dibaca daripada source of truth, bukan semata-mata memori AI

### 17.5 Personal AI Task Execution

Setiap tindakan AI perlu mempunyai: `task_id`, `tenant_id`, `requested_by`, `agent_id`, `objective`, `input_reference`, `allowed_tools`, `risk_level`, `approval_requirement`, `budget_limit`, `deadline`, `status`, `result_reference`, `error_details`, `created_at`, `completed_at`

Status: `Draft → Planned → Awaiting Approval → Queued → Running → Completed / Partially Completed / Failed / Cancelled`

### 17.6 Proactive Alerts

AI boleh memberikan amaran proaktif:
- Lead penting tidak dihubungi selepas tempoh ditetapkan
- Quotation belum mendapat jawapan
- Invoice hampir atau telah melepasi tarikh matang
- Stok dijangka tidak mencukupi
- Campaign melebihi bajet
- Penggunaan AI hampir mencapai had
- Integrasi payment mengalami masalah

Setiap alert menunjukkan: apa yang berlaku, data/asas, tahap kepastian, kesan mungkin, tindakan dicadangkan, sama ada boleh dibuat automatik, cara meluluskan/menolak/membetulkan.

---

## BAB 17.7 — AI Memory Persistence & Model Independence

### 17.7.1 Visi

Sistem memory AI BIJAN direka supaya **knowledge dan logic tenant kekal sama** walaupun model AI ditukar. Memory adalah **persistent, tenant-isolated, dan model-agnostic**. Model AI hanya *membaca context* dan *menulis output* — tidak menyimpan state sendiri.

> **Prinsip Utama:** Memory (persistent) ≠ AI Model (swappable). Tukar model tanpa lose data.

### 17.7.2 Architecture Layer

```
┌─────────────────────────────────────────────────┐
│         TENANT A — AI MEMORY LAYER              │
│  (Storage PERSISTENT, tidak bergantung pada     │
│   model AI yang digunakan)                       │
├─────────────────────────────────────────────────┤
│                                                  │
│  1️⃣  Business Profile (MySQL)                   │
│      ├── Products, services, pricing            │
│      ├── Customers, suppliers                    │
│      ├── Org chart, roles                        │
│      └── Industry-specific data                  │
│      ✅ Simpan di MySQL — model mana pun baca    │
│                                                  │
│  2️⃣  Conversation History (MySQL)                │
│      ├── Semua chat logs                         │
│      ├── Task executions                         │
│      └── Approval workflows                      │
│      ✅ Simpan di DB — context preserved          │
│                                                  │
│  3️⃣  Vector Store — Qdrant (Embeddings)         │
│      ├── Dokumen company disembed                 │
│      ├── SOP, policies disembed                   │
│      ├── Past insights disembed                   │
│      └── Per-tenant namespace                     │
│      ✅ Embeddings independent dari model AI      │
│                                                  │
│  4️⃣  System Prompts (Config Table)              │
│      ├── Template prompt untuk setiap role       │
│      ├── Business rules encoded                  │
│      ├── Approval gates                          │
│      └── Custom instructions per tenant          │
│      ✅ Prompt template — boleh tukar model       │
│                                                  │
│  5️⃣  Knowledge Base (Cloudflare R2/S3)          │
│      ├── Uploaded documents                      │
│      ├── Generated reports                       │
│      ├── Contracts, agreements                   │
│      └── Media assets                            │
│      ✅ File storage — model tak touch            │
│                                                  │
│  6️⃣  Derived Insights (MySQL)                   │
│      ├── Trend analysis results                  │
│      ├── Risk assessments                        │
│      ├── Recommendations                         │
│      └── Forecast data                           │
│      ✅ Results disimpan — boleh regenerate       │
└─────────────────────────────────────────────────┘
                    ↕️ API Call
┌─────────────────────────────────────────────────┐
│         AI GATEWAY — serveras.click             │
│  (Model boleh ditukar tanpa affect memory)       │
├─────────────────────────────────────────────────┤
│                                                  │
│  Model Pilihan:                                  │
│  ├── GPT-6 Astra → load prompts + context        │
│  ├── Claude Opus 4.8 → load prompts + context    │
│  ├── Qwen 3.8 Max → load prompts + context       │
│  └── Kimi K3 → load prompts + context            │
│                                                  │
│  ✅ Semua memory/profile/context sama            │
│  ✅ Hanya "otak" AI berubah, "ingatan" tetap    │
└─────────────────────────────────────────────────┘
```

### 17.7.3 Data Models — MySQL Tables

#### `ai_business_profiles`
Profil bisnes tenant yang dimodel AI baca sebagai context asas.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT FK | Tenant scope |
| business_name | VARCHAR(255) | Nama syarikat |
| industry_code | VARCHAR(20) | MSIC code |
| business_model | ENUM('retail','wholesale','service','subscription','hybrid') | Jenis bisnes |
| products_json | JSON | Senarai produk/service |
| customers_summary_json | JSON | Ringkasan customer base |
| org_structure_json | JSON | Struktur organisasi |
| custom_instructions | TEXT | Arahan khusus tenant |
| language_pref | VARCHAR(10) | Bahasa default (ms, en, zh) |
| tone_pref | VARCHAR(50) | Gaya komunikasi (formal, casual, friendly) |
| report_format | VARCHAR(50) | Format laporan preferred |
| preferred_model | VARCHAR(100) | Model AI pilihan semasa |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `ai_conversations`
Semua perbualan antara pengguna dan AI assistant.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT FK | Tenant scope |
| user_id | BIGINT FK | Pengguna yang chat |
| assistant_type | VARCHAR(50) | owner/sales/finance/hr/dll |
| model_used | VARCHAR(100) | FK ke ai_model_catalog.provider_model_id |
| messages_json | JSON | Array of {role, content, timestamp} |
| context_snapshot_id | BIGINT | Reference ke context used |
| token_usage | JSON | {prompt_tokens, completion_tokens, total} |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `ai_tasks`
Tugasan yang AI execute (extended dari BAB 17.5).

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT FK | Tenant scope |
| user_id | BIGINT FK | Yang request |
| agent_id | VARCHAR(100) | Agent yang handle |
| objective | TEXT | Objective tugasan |
| input_reference | VARCHAR(255) | Reference ke data source |
| allowed_tools | JSON | Tools yang dibenarkan |
| risk_level | ENUM('low','medium','high','critical') | Tahap risiko |
| approval_required | BOOLEAN | Perlu human approval? |
| budget_limit | DECIMAL(15,2) | Had bajet jika ada |
| deadline | DATETIME | Tarikh akhir |
| status | ENUM('Draft','Planned','Awaiting Approval','Queued','Running','Completed','Partially Completed','Failed','Cancelled') | Status execution |
| result_reference | VARCHAR(255) | Reference ke hasil |
| error_details | TEXT | Error jika gagal |
| created_at | TIMESTAMP | |
| completed_at | DATETIME | |

#### `ai_memory_items`
Memory items yang boleh di-query oleh AI context builder.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT FK | Tenant scope |
| user_id | BIGINT NULL | Owner memory (NULL = shared) |
| memory_type | ENUM('preference','context','insight','knowledge','history') | Jenis memori |
| category | VARCHAR(100) | Kategori (sales, finance, hr, dll) |
| content_text | TEXT | Kandungan utama |
| content_json | JSON | Structured data jika perlu |
| embedding_id | VARCHAR(255) | Reference ke Qdrant vector |
| source | VARCHAR(100) | Sumber (user_input, ai_derived, imported) |
| confidence_score | DECIMAL(3,2) | Confidence 0.00-1.00 |
| is_verified | BOOLEAN | Sudah disahkan manusia? |
| expires_at | DATETIME NULL | Expired jika temporary |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |
| deleted_at | DATETIME NULL | Soft delete |

#### `ai_prompt_templates`
Template system prompt yang configurable per tenant.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT NULL | NULL = platform default |
| assistant_type | VARCHAR(50) | Role template |
| version | INT | Version control |
| system_prompt | TEXT | Full system prompt |
| variables_json | JSON | Placeholder variables {{business_name}} |
| is_active | BOOLEAN | Active template |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `ai_insights`
Derived insights dari AI analysis.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT FK | Tenant scope |
| insight_type | ENUM('trend','risk','opportunity','anomaly','recommendation') | Jenis insight |
| category | VARCHAR(100) | Kategori |
| title | VARCHAR(255) | Title insight |
| description | TEXT | Detailed explanation |
| data_source | VARCHAR(255) | Modul sumber data |
| confidence_score | DECIMAL(3,2) | Confidence 0.00-1.00 |
| impact_level | ENUM('low','medium','high','critical') | Impact assessment |
| action_suggested | TEXT | Cadangan tindakan |
| is_acknowledged | BOOLEAN | User dah acknowledge? |
| is_resolved | BOOLEAN | Dah resolve? |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### 17.7.4 Vector Store — Qdrant Schema

Qdrant digunakan untuk **semantic search** atas dokumen tenant. Setiap tenant ada **namespace** berasingan.

#### Collection: `{tenant_id}_documents`

| Field | Type | Description |
|-------|------|-------------|
| id | String | Unique document ID |
| text | Payload | Original text content |
| metadata | Payload | {doc_type, filename, uploaded_by, uploaded_at, classification} |
| embedding | Vector | 1536-dim (text-embedding-3-large) atau 768-dim (text-embedding-3-small) |

**Document Types:**
- `sop` — Standard Operating Procedures
- `policy` — Company policies
- `contract` — Contracts & agreements
- `product_info` — Product descriptions
- `training_material` — Training docs
- `meeting_notes` — Notulen mesyuarat
- `custom` — Custom documents

#### Collection: `{tenant_id}_insights`

Untuk semantic search atas derived insights.

| Field | Type | Description |
|-------|------|-------------|
| id | String | Unique insight ID |
| text | Payload | Insight summary + details |
| metadata | Payload | {type, category, confidence, created_at} |
| embedding | Vector | Same embedding model as documents |

### 17.7.5 Context Builder Service

Sebelum hantar request ke AI gateway, **Context Builder** gabungkan semua relevant context:

```php
class ContextBuilderService
{
    public function buildContext(int $tenantId, string $assistantType, string $userQuery): array
    {
        // 1. Load business profile
        $profile = $this->loadBusinessProfile($tenantId);
        
        // 2. Load recent conversation history (last 50 messages)
        $history = $this->loadConversationHistory($tenantId, $assistantType, limit: 50);
        
        // 3. Semantic search for relevant documents
        $relevantDocs = $this->searchVectorStore($tenantId, $userQuery, topK: 5);
        
        // 4. Load active insights
        $insights = $this->loadActiveInsights($tenantId, limit: 10);
        
        // 5. Load current work context
        $workContext = $this->loadWorkContext($tenantId);
        
        // 6. Build system prompt from template
        $systemPrompt = $this->getSystemPrompt($tenantId, $assistantType);
        
        // 7. Assemble full context
        return [
            'system_prompt' => $this->renderPromptTemplate($systemPrompt, $profile),
            'conversation_history' => $history,
            'relevant_documents' => $relevantDocs,
            'active_insights' => $insights,
            'work_context' => $workContext,
            'user_query' => $userQuery,
        ];
    }
}
```

### 17.7.6 Model Switch Flow

Bila user tukar model AI, **memory tidak terjejas**:

```
1. User pilih model baru di UI
        ↓
2. Frontend call API: POST /api/ai/model-switch
        ↓
3. Backend validate:
   - Model tersedia dalam catalog
   - Tenant ada token balance
   - Model compatible dengan task type
        ↓
4. Update preference:
   UPDATE ai_business_profiles 
   SET preferred_model = 'claude-opus-4.8' 
   WHERE tenant_id = ?
        ↓
5. Next AI request:
   - Context Builder load SAME context (profile + history + docs)
   - Hantar ke NEW model (Claude Opus 4.8)
   - Output disimpan ke SAME memory tables
        ↓
6. Result:
   ✅ Context sama
   ✅ Memory sama
   ✅ Logic bisnes sama
   ❗ Hanya style/jawapan AI berubah (berbeza model)
```

### 17.7.7 Memory Lifecycle Management

| Operation | Method | Frequency |
|-----------|--------|-----------|
| **Auto-embed** | New document → auto embed ke Qdrant | Real-time |
| **Memory pruning** | Remove expired/temporary memories | Daily cron |
| **Insight refresh** | Regenerate insights from fresh data | Weekly cron |
| **Vector sync** | Sync MySQL memory ↔ Qdrant embeddings | Hourly |
| **Context cache** | Cache built context untuk repeat queries | On-demand |
| **Archive old chats** | Move old conversations ke cold storage | Monthly |

### 17.7.8 Security & Privacy Rules

- **Tenant Isolation:** Setiap tenant namespace berasingan di Qdrant, MySQL scoped dengan `tenant_id`
- **Data Never Shared:** Tenant A context tidak pernah masuk ke Tenant B request
- **Embedding Privacy:** Dokumen sensitif (finance, HR) tidak di-embed — hanya keyword indexed di MySQL
- **User Consent:** User boleh delete conversation history mereka
- **Audit Trail:** Semua AI actions logged dengan `task_id`, `model_used`, `token_usage`
- **GDPR/PDPA Compliance:** Data subject rights (access, rectification, erasure) perlu diimplementasi melalui workflow yang disahkan oleh pakar pematuhan data. Dakwaan "compliant" hanya boleh dibuat selepas semakan undang-undang rasmi. Right to be forgotten TIDAK bermaksud semua memori dipadam — rekod kewangan/statutori kekal mengikut keperluan undang-undang.

### 17.7.9 Implementation Priority

| Phase | Component | Status | Est. Timeline |
|-------|-----------|--------|---------------|
| Phase 1 | MySQL tables (`ai_business_profiles`, `ai_conversations`, `ai_memory_items`) | ❌ Belum | Week 1-2 |
| Phase 1 | Context Builder service | ❌ Belum | Week 2-3 |
| Phase 2 | Qdrant setup + embedding pipeline | ❌ Belum | Week 3-4 |
| Phase 2 | Vector search integration | ❌ Belum | Week 4-5 |
| Phase 3 | Prompt template engine | ❌ Belum | Week 5-6 |
| Phase 3 | Model switch endpoint + UI | ❌ Belum | Week 6-7 |
| Phase 4 | Memory lifecycle management | ❌ Belum | Week 7-8 |
| Phase 4 | Advanced features (insight refresh, archive) | ❌ Belum | Week 8-9 |

### 17.7.10 Integration dengan Existing Components

```
Context Builder reads from:
├── MySQL: ai_business_profiles → Business context
├── MySQL: ai_conversations → Conversation history
├── MySQL: ai_memory_items → Verified knowledge
├── MySQL: ai_insights → Active insights
├── Qdrant: {tenant}_documents → Semantic doc search
├── Cloudflare R2: Documents storage → File references
└── Laravel Pulse: Real-time usage metrics

Context Builder writes to:
├── MySQL: ai_conversations → Save new messages
├── MySQL: ai_tasks → Log task execution
├── MySQL: ai_insights → Store derived insights
├── Qdrant: {tenant}_documents → New embeddings
└── Redis: Context cache → Fast repeat queries

Output flows to:
├── AI Gateway (serveras.click) → Send context + query
├── Token Wallet → Deduct tokens used
├── Audit Trail → Log all AI actions
└── Filament Admin → Monitor AI usage dashboard
```

---

## BAB 18 — Business Understanding Engine (10 Tahap Discovery)

### 18.1 Objektif

B-Business Understanding Engine ialah proses onboarding mendalam untuk memahami cara bisnes pelanggan benar-benar beroperasi sebelum AI BIJAN membuat konfigurasi atau mencadangkan automasi. Sistem perlu membina **Business Operating Profile** yang boleh digunakan untuk menyediakan modul, data, workflow, KPI, AI assistants dan pelan automasi.

### 18.2 Discovery Lifecycle Flow

```
Business Registration
        ↓
Industry & Activity Discovery
        ↓
Business Model Discovery
        ↓
Products & Services Discovery
        ↓
Customer & Sales Discovery
        ↓
Operations Discovery
        ↓
People, Roles & Responsibilities
        ↓
Finance, Costs & Payments
        ↓
Tools, Data & Integrations
        ↓
Goals, Constraints & Risks
        ↓
Business Understanding Report
        ↓
Proposed System Blueprint
        ↓
Human Review & Confirmation
        ↓
Initial Setup & Data Import
        ↓
Workflow Simulation
        ↓
Go-Live Readiness
        ↓
Continuous Learning & Updates
```

### 18.3 10 Tahap Discovery

| Tahap | Kategori | Maklumat Yang Dikumpulkan |
|-------|----------|--------------------------|
| **1** | Company Identity | Nama syarikat, jenama, jenis entiti, negara/kawasan, aktiviti utama/sekunder, bilangan cawangan, saiz pasukan, bahasa operasi, mata wang, waktu operasi |
| **2** | Business Model | Apakah yang dijual, pelanggan utama, sumber hasil (jualan sekali/langganan/komisen/projek), cara penetapan harga, deposit, syarat pembatalan, kos terbesar, faktor had kapasiti |
| **3** | Products & Services | Nama, kategori, deskripsi, target customer, selling price, cost components, delivery method, fulfillment time, capacity, availability, add-ons, warranty, return/cancellation terms |
| **4** | Customer Understanding | B2B/B2C, customer segments, service areas, typical buyer, buying triggers, common objections, sales cycle, repeat purchase pattern, retention risks, preferred channels |
| **5** | Sales Process | 15-stage pipeline: lead source → capture → qualification → contact → discovery → proposal → negotiation → approval → contract → payment → delivery → invoice → collection → success → repeat |
| **6** | Operations | Work intake, scheduling, staff assignment, resource allocation, procurement, inventory, QC, delivery, confirmation, incident handling, returns, completion |
| **7** | Team & Organization | Owner, management, sales, marketing, operations, finance, customer service, procurement, fulfillment, external vendors, approval authority |
| **8** | Finance & Unit Economics | Revenue streams, direct/fixed/variable costs, staff costs, supplier costs, payment terms, deposits, receivables/payables, refunds, discounts, gross margin, break-even, cash-flow timing |
| **9** | Current Tools & Data | Existing CRM, accounting software, spreadsheets, POS, website, e-commerce, communication tools, payment providers, calendar systems, inventory systems, APIs |
| **10** | Goals, Constraints & Risks | Revenue/lead/conversion targets, AOV, repeat rate, delivery time, satisfaction, operating cost, staff capacity, budget, team capacity, technical limitations, legal constraints, risk tolerance |

### 18.4 Business Understanding Scorecard

| Dimensi | Soalan Penilaian |
|---------|-----------------|
| Business identity | Adakah entiti dan aktiviti disahkan? |
| Revenue model | Adakah sumber hasil difahami? |
| Product/service | Adakah katalog dan harga disahkan? |
| Customers | Adakah segmen dan saluran dikenal pasti? |
| Sales | Adakah pipeline dan conversion stages ditetapkan? |
| Operations | Adakah fulfilment workflow diketahui? |
| Team | Adakah role dan approval jelas? |
| Finance | Adakah kos, payment terms dan collection diketahui? |
| Data | Adakah source of truth dan integrasi dikenal pasti? |
| Automation | Adakah syarat dan batas automasi ditentukan? |
| Risk | Adakah risiko dan kawalan didokumenkan? |
| KPI | Adakah baseline dan sasaran tersedia? |

Status: `Unknown`, `Partially Understood`, `Customer Confirmed`, `System Verified`, `Needs Review`

### 18.5 Business Operating Profile Output

Selepas discovery, sistem menghasilkan profil berstruktur:
- Business identity, industry blueprint, revenue model
- Product/service catalogue, customer segments, sales pipeline
- Operations workflows, team and permission proposal
- Financial assumptions, KPI definitions, integration map
- Automation opportunities, AI assistant recommendations
- Data migration plan, risks and unknowns
- Required customer decisions, estimated implementation effort, proposed rollout sequence

### 18.6 Initial Setup Plan (16 Langkah)

1. Modules to activate → 2. Roles and permissions → 3. Organization and branch setup → 4. Products and services import → 5. Customer import → 6. Sales pipeline setup → 7. Workflow configuration → 8. Document templates → 9. Payment/invoicing configuration → 10. Notifications → 11. Integrations → 12. AI assistant setup → 13. Automation policies → 14. KPI dashboards → 15. Acceptance tests → 16. Training and go-live checklist

### 18.7 Approval Gate

Sebelum perubahan berimpak tinggi diaktifkan, pelanggan berkuasa perlu mengesahkan: harga, margin dan kos, kontrak, payment configuration, customer messaging, data migration, permission, financial rules, workflow kritikal.

---

## BAB 19 — Autonomous Growth Engine (20-Step Lifecycle)

### 19.1 Visi

B-Autonomous Business Growth Engine membantu pelanggan membina saluran pertumbuhan yang menyambungkan marketing, lead generation, sales, delivery, invoicing dan payment collection. Mengurangkan kerja manual dari mengenal pasti prospek hingga merekod pembayaran.

### 19.2 End-to-End Growth Lifecycle (20 Steps)

```
1.  Business Understanding
        ↓
2.  Define Ideal Customer Profile (ICP)
        ↓
3.  Define Offer & Value Proposition
        ↓
4.  Build Compliant Prospect Strategy
        ↓
5.  Find / Capture Leads
        ↓
6.  Validate & Deduplicate
        ↓
7.  Score & Qualify
        ↓
8.  Create CRM Record
        ↓
9.  Personalized Outreach
        ↓
10. Conversation & Discovery
        ↓
11. Proposal / Quotation
        ↓
12. Negotiation & Approval
        ↓
13. Contract / Order Confirmation
        ↓
14. Invoice / Deposit Request
        ↓
15. Payment Verification
        ↓
16. Delivery / Fulfilment
        ↓
17. Final Invoice / Collection
        ↓
18. Reconciliation
        ↓
19. Customer Success / Repeat Sales
        ↓
20. Performance Learning
```

Setiap peringkat mempunyai: syarat masuk, syarat keluar, pemilik, status, retry policy dan audit.

### 19.3 Ideal Customer Profile (ICP) Engine

ICP merangkumi: industry/activity, company size, service geography, business needs, buying triggers, budget range, procurement process, expected sales cycle, fit criteria, disqualifying criteria, data freshness requirements. ICP mesti diluluskan/disemak oleh pelanggan.

### 19.4 Lead Discovery & Sourcing Controls

Sumber sah: website inquiry forms, customer referrals, existing CRM, opt-in marketing lists, authorized business directories, public business websites (tertakluk syarat penggunaan), marketplace inquiries, partner integrations, event registrations dengan consent.

**Controls:** Record source/retrieval date, store evidence/source URL, deduplicate by business identifiers, respect opt-out/suppression lists, validate contact data before outreach, apply rate limits, avoid unauthorized scraping, do not invent contact details.

### 19.5 Lead Enrichment & Scoring

Lead attributes: company name, industry, location, website, contact channel, source, need/fit evidence, qualification status, potential value estimate, confidence level, last verified time, next action, assigned owner.

**Score factors:** ICP fit, need evidence, service area fit, budget/size fit, engagement, timing, buying authority evidence, data quality.

---

## BAB 20 — B-Boardroom Workflow

### 20.1 Aliran Cadangan 7 Langkah

1. Pemilik mengemukakan persoalan atau keputusan
2. Chief of Staff menentukan ejen yang relevan
3. Ejen menerima konteks minimum yang diperlukan
4. Setiap ejen mengemukakan dapatan, andaian, bukti dan risiko
5. Orchestrator mengesan percanggahan dan meminta semakan jika perlu
6. AI CEO/Chief of Staff menyediakan ringkasan keputusan dan pilihan
7. Pemilik memilih tindakan; sistem merekod keputusan dan susulan

**Peraturan:** Boardroom tidak boleh menganggap persetujuan ejen sebagai bukti kebenaran. Dapatan perlu membezakan fakta, anggaran dan andaian.

---

## BAB 21 — AI Gateway: Serveras.click Reseller Model & Model Catalog

### 21.1 Overview — Admin-Only API Key Security

AI BIJAN menggunakan **serveras.click** sebagai upstream AI provider melalui reseller model. **Penting: API keys TONTOH diberikan kepada pelanggan.** Semua kunci API dikawal sepenuhnya oleh Super Admin.

- **Endpoint:** `https://serveras.click/v1`
- **Auth:** `Authorization: Bearer asai_sk_...` (ADMIN ONLY — never exposed to customers)
- **Customer-facing:** Display names only (no internal provider IDs shown)
- **Model routing:** Internal model IDs mapped to customer-friendly display names
- **Billing:** Customers billed per token/credit consumed, not per API call

> **Keamanan:** Customer TIDA boleh access API keys serveras.click. Mereka hanya berinteraksi dengan AI melalui UI/platform AI BIJAN. Semua API calls dibuat dari backend server AI BIJAN menggunakan admin key yang tersembunyi.

### 21.2 Model Catalog — Internal ID to Display Name Mapping

Super Admin configure mapping antara internal model IDs (dari serveras.click) dan display names yang ditunjukkan kepada customer. Ini membolehkan:

1. **Rebranding:** Tukar nama model tanpa ubah kod
2. **Abstraction:** Tukar provider (dari serveras.click ke lain) tanpa affect customer UI
3. **Tiering:** Same model boleh ada different display name untuk different countries
4. **Marketing:** Nama yang lebih menarik daripada technical IDs

#### Model Catalog Configuration (Admin HQ Page)

| Internal ID (serveras.click) | Display Name (Customer-facing) | Category | Speed | Quality | Use Case | Cost/Tokens (USD) |
|------------------------------|-------------------------------|----------|-------|---------|----------|-------------------|
| `asai/gpt-6-astra` | GPT-6 Astra | General | Fast | Excellent | Reasoning, coding, analysis | $0.0001/M tokens |
| `asai/claude-opus-4-8` | Claude Opus 4.8 | Advanced | Medium | Best | Complex reasoning, research | $0.00015/M tokens |
| `asai/claude-opus-5` | Claude Opus 5 | Advanced | Medium | Best+ | Latest Claude, best reasoning | $0.00018/M tokens |
| `asai/claude-opus-5-5` | Claude Opus 5.5 | Advanced | Medium | Enhanced | Enhanced Claude variant | $0.0002/M tokens |
| `asai/kimi-k3` | Kimi K3 | Long Context | Slow | Very Good | Document analysis, RAG (200K context) | $0.00008/M tokens |
| `asai/qwen-3-8-max` | Qwen 3.8 Max | General | Fast | Very Good | Multilingual, balanced | $0.00007/M tokens |
| `asai/deepseek-v4-pro` | DeepSeek V4 Pro | Coding | Fast | Excellent | Code generation, debugging | $0.00009/M tokens |
| `asai/glm-5-3-flash` | GLM 5.3 Flash | Fast | Instant | Good | Quick responses, chat | $0.00004/M tokens |
| `asai/minimax-m3` | MiniMax M3 | General | Fast | Good | Balanced performance | $0.00005/M tokens |
| `asai/grok-4-7` | Grok 4.7 | Real-time | Fast | Very Good | News, real-time info | $0.00011/M tokens |
| `asai/muse-wonder-1-0` | Muse Wonder 1.0 | Creative | Medium | Good | Content generation, creative writing | $0.00006/M tokens |
| ...dan 9 model lain | ... | ... | ... | ... | ... | ... |

> **Super Admin boleh:** Tambah/buang model, tukar mapping, set default model per plan, configure cost per model, enable/disable model availability per country.

### 21.3 AI Gateway Features

- Satu pintu untuk provider LLM
- Model routing mengikut tugas, latency, kualiti dan kos
- Log penggunaan dan kos mengikut tenant/agent/model
- Budget caps dan rate limits
- Redaction/minimization PII sebelum menghantar prompt kepada provider
- Retry, timeout, fallback dan circuit breaker
- Prompt/template versioning
- Evaluations dan regression tests
- Provider adapters supaya model boleh ditukar tanpa mengubah modul domain
- **Auto model selection:** AI pilih model paling sesuai mengikut task type (coding → DeepSeek, creative → Muse, reasoning → GPT-6/Claude)
- **Cost optimization:** Untuk simple tasks, guna model murah (GLM Flash); untuk complex tasks, guna model premium (Claude Opus)

### 21.4 Agent Runtime

- Ejen tidak diberi akses SQL atau rahsia database
- Setiap tool memanggil endpoint/API yang disahkan
- Input schema, permission check, timeout dan audit
- Redaksi data sensitif sebelum dihantar kepada provider luar
- Tenant boleh menetapkan sama ada kandungan tertentu dibenarkan dihantar kepada AI luar
- Jika AI tidak tersedia, modul perniagaan teras masih boleh digunakan
- **Tenant TIDA boleh lihat API keys atau model internal IDs**

---

## BAB 22 — AI Credit Wallet & Token Pricing

### 22.1 Architecture — Separate Subscription vs AI Credits

Platform mempunyai DUA sistem billing yang berasingan:

```
┌─────────────────────────────────────────────────────┐
│              BILLING ARCHITECTURE                    │
├─────────────────────────────────────────────────────┤
│                                                      │
│  ┌──────────────────┐    ┌──────────────────────┐   │
│  │  SUBSCRIPTION     │    │  AI CREDITS WALLET   │   │
│  │  (Platform Access)│    │  (Model Usage)       │   │
│  ├──────────────────┤    ├──────────────────────┤   │
│  │ - Monthly/Annual │    │ - Pay-as-you-go     │   │
│  │ - Module access  │    │ - Token-based       │   │
│  │ - Fixed features │    │ - Per-model pricing │   │
│  │ - Included credits│   │ - Top-up available  │   │
│  └──────────────────┘    └──────────────────────┘   │
│                                                      │
│  Subscription → Platform modules (B-Site, HRMS...)  │
│  AI Credits → AI model usage (GPT-6, Claude...)     │
└─────────────────────────────────────────────────────┘
```

**Subscription** = Akses ke platform dan modul-modul perniagaan
**AI Credits** = Penggunaan AI model API (dibeli secara add-on jika kuota bulanan habis)

### 22.2 AI Credit Purchase Tiers (Configurable per Country)

Semua harga AI credits boleh diubah dari **Master Admin Panel** mengikut negara.

#### Default Token Pricing Tiers (Malaysia - MYR)

| AI Credit Package | Wholesale Cost (serveras.click) | Jual Retail (MYR) | Margin | Est. Model Calls |
|------------------|--------------------------------|-------------------|--------|------------------|
| **80 Juta Tokens** | RM 5 | RM 79 | ~94% | ~80,000 calls (avg 1K tokens/call) |
| **200 Juta Tokens** | RM 10 | RM 160 | ~94% | ~200,000 calls |
| **1.1 Bilion Tokens** | RM 50 | RM 600 | ~92% | ~1,100,000 calls |
| **2.25 Bilion Tokens** | RM 100 | RM 1,100 | ~91% | ~2,250,000 calls |

> **Wholesale costs are HIDDEN from customers.** Only Super Admin can see and modify these.
> Semua harga retail di atas adalah CONTOH. Super Admin boleh ubah bila-bila masa.

#### Multi-Country Token Pricing Examples

| Tier | Malaysia (MYR) | Singapore (SGD) | Indonesia (IDR) | Pakistan (PKR) |
|------|---------------|----------------|----------------|----------------|
| **80 Juta** | RM 79 | S$ 25 | Rp 130.000 | Rs 1.800 |
| **200 Juta** | RM 160 | S$ 49 | Rp 250.000 | Rs 4.000 |
| **1.1 Bilion** | RM 600 | S$ 180 | Rp 900.000 | Rs 12.000 |
| **2.25 Bilion** | RM 1,100 | S$ 340 | Rp 1.600.000 | Rs 22.000 |

### 22.3 Credit Expiry & Reset Policy

| Purchase Type | Expiry | Rollover | Keterangan |
|--------------|--------|----------|------------|
| **Bundled with plan** | End of billing cycle | ❌ No rollover | Tokens reset setiap bulan (contoh: Starter dapat 80M tokens setiap bulan) |
| **One-time top-up** | 30 days from purchase | ❌ Expire after 30 days | Bonus tokens bought separately expire after 30 hari |
| **Promo tokens** | 14 days | ❌ Short expiry | Bonus tokens from campaigns |
| **Referral tokens** | 30 days | ❌ | Reward tokens for referrals |
| **Enterprise custom** | Per contract | ✅ Negotiable | Per agreement terms |

**Reset Policy:**
- Tokens yang termasuk dalam subscription RESET setiap billing cycle
- Contoh: Starter plan dapat 80M tokens setiap bulan — jika habis di minggu pertama, tenant masih ada platform access tapi AI akan limited
- Top-up credits expire selepas 30 hari dari pembelian
- Tenant boleh enable auto-recharge: bila balance < 20%, auto-order package terpilih

### 22.4 Token Purchase Flow

```
Tenant checks AI credit balance → System shows current balance + included amount
        ↓
Balance low? System suggests top-up packages
        ↓
Tenant selects package (or Super Admin configures auto-recharge)
        ↓
System calculates final price with local tax
        ↓
Tenant pays via local payment gateway (FPX/GrabPay/etc.)
        ↓
Credits credited to wallet immediately
        ↓
Usage deducted in real-time based on model used:
  - GLM Flash (cheap): 1 token = 1 output token
  - GPT-6 Astra (mid): 1 token = 1 output token + input ratio
  - Claude Opus (premium): 1 token = 1 output token + higher input ratio
        ↓
Low balance alert at 80%, 50%, 20% remaining
        ↓
Auto-recharge trigger (if enabled) or manual top-up
```

### 22.5 Model Cost Tiering

> **⚠️ NOTA PENTING:** Sistem kredit menggunakan **per-token billing** dengan model tier multiplier, BUKAN "1 credit = X tokens" yang membingungkan.
>
> **Formula rasmi:**
> ```
> credits_charged = (input_tokens × input_rate) + (output_tokens × output_rate)
> 
> di mana:
>   input_rate  = base_rate × model_tier_multiplier
>   output_rate = base_rate × model_tier_multiplier
> 
> Contoh untuk Flash tier (1x): 1,000 input tokens + 200 output tokens = 1,200 credits
> Contoh untuk Premium tier (2x): 1,000 input tokens + 200 output tokens = 2,400 credits
> ```
>
> **Tambahan:**
> - Image generation: RM0.04-0.12 per image (berdasarkan resolution)
> - Audio transcription: 1 credit per 5 seconds of audio
> - Video processing: 1 credit per 2 seconds of video
> - Retry on failure: TIDAK dicaj semula (free retry within 5 minutes)
> - Cached responses: TIDAK dicaj (cache hit = free)
> - Rounding: Bulat ke atas ke nearest 100 credits

| Model Tier | Models | Multiplier | Base Rate (per token) | Best For |
|-----------|--------|------------|----------------------|----------|
| **Flash (1x)** | GLM 5.3 Flash, MiniMax M3 | 1× | RM0.0001 | Simple chat, quick answers |
| **Standard (1.5x)** | Qwen 3.8 Max, GPT-6 Astra, DeepSeek V4 Pro | 1.5× | RM0.00015 | Coding, analysis, multilingual |
| **Premium (2x)** | Claude Opus 4.8, Claude Opus 5 | 2× | RM0.0002 | Complex reasoning, research |
| **Ultra (2.5x)** | Claude Opus 5.5, Kimi K3 | 2.5× | RM0.00025 | Long context, advanced tasks |

> Tenant boleh pilih model berdasarkan budget. Simple queries → Flash model (lebih murah). Complex tasks → Premium model (lebih power).
>
> **Contoh pengiraan:**
> - Tenant guna Claude Opus (Premium 2x) untuk generate invoice
> - Input: 500 tokens (prompt + context) × RM0.0002 = 0.1 credits
> - Output: 200 tokens (invoice data) × RM0.0002 = 0.04 credits
> - Total: 0.14 credits dibulatkan ke 100 = **100 credits**

---

## BAB 23 — Memory Architecture & AI Media Studio

### 23.1 Three-Layer Memory System

AI BIJAN implements a hierarchical memory architecture that enables agents to learn and improve over time across three distinct scopes.

```
┌─────────────────────────────────────────────────────────┐
│           MEMORY ARCHITECTURE (3 Layers)                │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Layer 1: AGENT MEMORY                                  │
│  ├── Scope: Single AI agent                            │
│  ├── Lifetime: Agent lifecycle                         │
│  ├── Content: Task patterns, preferences, learned APIs │
│  └── Size: ~10KB per agent                             │
│                                                         │
│  Layer 2: PROJECT MEMORY                                │
│  ├── Scope: Tenant project/workspace                   │
│  ├── Lifetime: Project duration                        │
│  ├── Content: Business rules, workflows, templates     │
│  └── Size: ~100KB per project                          │
│                                                         │
│  Layer 3: ORGANIZATION MEMORY                           │
│  ├── Scope: Entire tenant organization                 │
│  ├── Lifetime: Permanent (anonymized)                  │
│  ├── Content: Industry patterns, best practices        │
│  └── Size: Aggregated across all tenants               │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

#### Layer 1: Agent Memory

Memory yang disimpan oleh individual AI agent untuk improve performance atas masa.

| Content | Example | Update Frequency |
|---------|---------|------------------|
| **Task Patterns** | "Tenant A selalu buat invoice setiap 1st of month" | Per interaction |
| **User Preferences** | "Tenant A prefer BM over EN for reports" | Per feedback |
| **API Learnings** | "Invoice creation needs 3 steps: customer → items → confirm" | Per successful task |
| **Error Recovery** | "Payment gateway X fails on weekends → suggest alternative" | Per failure/recovery |
| **Context Cache** | Recent invoices, customers, products for faster responses | Continuous |

**Storage:** Redis (fast access) + PostgreSQL (persistent)

#### Layer 2: Project Memory

Memory yang scoped kepada satu project/workspace tenant. Mengandungi knowledge spesifik organisasi.

| Content | Example | Source |
|---------|---------|--------|
| **Business Rules** | "Discount > 20% needs manager approval" | Tenant config + AI inference |
| **Workflow Templates** | "Purchase order flow: requisition → approval → PO → GRN" | Tenant customization |
| **Custom Field Definitions** | "Invoice has custom field: delivery_date" | Tenant config |
| **Document Templates** | Contract templates, letter heads, brand guidelines | Tenant uploads |
| **Historical Decisions** | "Switched supplier from A to B in Month 3" | Audit trail |
| **Agent Assignments** | "CFO agent handles finance, COO agent handles operations" | Tenant config |

**Storage:** PostgreSQL (tenant-scoped tables)

#### Layer 3: Organization Memory (Collective Intelligence)

Anonymized patterns dari SEMUA tenant dalam industri yang sama. Ini adalah **unfair advantage** AI BIJAN — platform makin pintar dari network effect.

| Content | Example | Anonymization |
|---------|---------|---------------|
| **Industry Benchmarks** | "F&B businesses average 65% food cost" | Aggregated, no tenant ID |
| **Best Practices** | "Retail businesses with loyalty see +23% retention" | Pattern only |
| **Seasonal Trends** | "School transport peaks in Month 1, 4, 7, 10" | Industry-level stats |
| **Pricing Insights** | "Car workshop avg service price: RM180 in KL" | Range only |
| **Cross-Tenant Patterns** | "80% of clinics use appointment reminder workflow" | Statistical only |

**Storage:** Vector store (Qdrant) + aggregated analytics tables
**Privacy:** Strict anonymization pipeline — NO tenant data leaks to other tenants
**Opt-in:** Tenants boleh choose untuk contribute ke collective intelligence untuk unlock insights

### 23.1 Consent Withdrawal & Deletion Policy

> **⚠️ PERHATIAN:** Apabila tenant menarik balik consent untuk collective intelligence, berikut adalah prosedur yang diikuti:

#### Prosedur Penarikan Consent

| Langkah | Tindakan | Tempoh |
|---------|----------|--------|
| 1 | Tenant klik "Withdraw from Collective Intelligence" di B-Hub settings | Immediate |
| 2 | System run **re-identification risk test** — pastikan anonymized data tidak boleh dipulangkan ke tenant asal | < 5 minit |
| 3 | Jika risk score > threshold (0.01): flag data untuk immediate deletion |
| 4 | Jika risk score ≤ threshold: keep aggregated statistics, remove tenant contribution record |
| 5 | Update **provenance tracking log**: {tenant_id, withdrawal_timestamp, action_taken, risk_score} |
| 6 | Notify tenant admin: "Your organization has been removed from collective intelligence. Aggregated statistics retained."

#### Jenis Data & Kesan Withdrawal

| Jenis Data | Personal | Pseudonymized | Anonymized | Statistical |
|-----------|----------|---------------|------------|-------------|
| **Boleh delete?** | ✅ Ya | ✅ Ya | ⚠️ Hanya jika n < 10 (k-anonimiti) | ❌ Tidak (agregat sahaja) |
| **Provenance tracked?** | ✅ Ya | ✅ Ya | ✅ Ya | ⚠️ Sebahagian |
| **Retention selepas withdrawal** | 30 days purge | 30 days purge | Indefinite (jika n ≥ 10) | Indefinite |

#### K-Anonimiti Requirement

> Semua aggregated statistics mesti memenuhi **k = 10** minimum — iaitu setiap statistik mestilah berasal daripada sekurang-kurangnya 10 tenant berbeza. Jika withdrawal menyebabkan n < 10, statistik tersebut perlu dihapuskan atau digabungkan dengan kategori lain.

### 23.2 Memory Persistence & Lifecycle

| Layer | Storage | TTL | Backup | Restore |
|-------|---------|-----|--------|---------|
| Agent Memory | Redis + PostgreSQL | Agent lifetime + 90 days | Daily | Point-in-time recovery |
| Project Memory | PostgreSQL | Project duration + 1 year | Daily | Full backup restore |
| Organization Memory | Qdrant + Analytics DB | Permanent (anonymized) | Weekly | Read-only rebuild |

### 23.3 Memory-Guided AI Actions

```
Agent receives task → Check Agent Memory for similar past tasks
        ↓
Past pattern found? Apply learned optimization
        ↓
Check Project Memory for business rules/constraints
        ↓
Apply tenant-specific rules (approval thresholds, templates)
        ↓
Check Organization Memory for industry benchmarks
        ↓
Suggest improvements based on cross-tenant insights
        ↓
Execute task → Record outcome → Update Agent Memory
        ↓
If novel pattern detected → Flag for Project Memory update
```

### 23.4 AI Media Studio

- **Image Generation** — Generate branded images, product photos, marketing visuals
- **Video Generation** — Create short promotional videos
- Reuses provider gateway, billing, memory, assets, audit dan tenant controls
- Content moderation pipeline
- Brand-safe content filtering
- Watak AI milik AI BIJAN sendiri (bukan copy dari sumber luar)

---

## BAB 24 — UI/UX Design System

### 24.1 Design Principles

- **Mobile-first design** — cerah & bersih
- **Background:** putih/kelabu lembut
- **Typography:** besar, mudah dibaca
- **Buttons:** gelap berbentuk pil (pill-shaped)
- **Cards:** "kaca" (glass morphism effect)
- **Accents:** coklat-amber & logo AI BIJAN
- Diilhamkan gaya samsai.co sahaja; tiada aset/disalin dari sumber luar

### 24.2 Priority Work Sequence

1. Command Center (main dashboard)
2. B-HRMS
3. B-Booking
4. Modul lain mengikut keutamaan

---

# PART V — INTEGRATIONS & AUTOMATION

## BAB 25 — Domain Events & Transactional Outbox

### 25.1 Convention

Nama event dalam bentuk lampau: `OrderPaid`, `InvoiceIssued`, `AppointmentBooked`. Event membawa ID, tenant ID, timestamp, schema version dan data minimum yang diperlukan.

### 25.2 Transactional Outbox Pattern

1. Transaksi perniagaan mengubah rekod domain dan memasukkan event ke `outbox_events` dalam transaksi database yang sama
2. Worker membaca event belum dihantar
3. Event diterbitkan kepada queue
4. Consumer merekod idempotency key dan memproses event sekali secara logik
5. Kegagalan di-retry dengan backoff; selepas had tertentu, masuk dead-letter queue
6. Operator boleh replay event dengan audit

### 25.3 Example: `OrderPaid` Flow

- B-Pay mengesahkan pembayaran dan menerbitkan event
- B-Finance memproses jurnal melalui kontraknya
- B-Loyalty menambah mata jika order memenuhi syarat
- B-Logistics mencipta penghantaran jika diperlukan
- B-Analytics menerima salinan event untuk metrik

Modul penerima tidak mengakses jadual pemilik event secara terus. Semua consumer mesti idempotent.

---

## BAB 26 — Database Design

### 26.1 Main Tables (20+)

| Table | Purpose |
|-------|---------|
| `tenants` | Merchant/organization entities |
| `users` | Global user identities |
| `tenant_user` | Tenant membership |
| `plans`, `plan_features` | Subscription plans |
| `subscriptions` | Active subscriptions |
| `tenant_modules` | Module entitlements |
| `customers` | Customer profiles (tenant-scoped) |
| `products`, `product_variants` | Product catalog |
| `orders`, `order_items` | Sales orders |
| `payments`, `refunds` | Payment records |
| `wallet_ledger` | AI token usage ledger |
| `outbox_events` | Transactional outbox |
| `activity_log` | Audit trail |
| `files` | File metadata |
| `integrations`, `integration_accounts` | Connector management |
| `approval_requests` | Human approval queue |
| `agent_runs`, `agent_tool_calls` | AI agent audit |
| `workflows`, `workflow_runs`, `workflow_steps` | Automation |
| `usage_records` | Usage metering |
| `ad_accounts`, `ad_campaigns` | Advertising |

### 26.2 Data Rules

- Gunakan ULID atau UUID yang konsisten; ID bukan kawalan akses
- Wang sebagai integer dalam unit minor (contoh: sen) bersama ISO currency code
- Masa disimpan dalam UTC; dipaparkan mengikut zon waktu tenant, lalai `Asia/Kuala_Lumpur`
- Indeks disusun mengikut pola query sebenar; jadual tenant lazimnya bermula dengan `tenant_id`
- Soft delete hanya jika semantik domain dan polisi retention membenarkannya
- Data kewangan/ledger bersifat append-only dengan reversal entries
- PII mempunyai polisi retention, export dan deletion yang terkawal
- Gunakan transaksi database untuk invariants yang mesti konsisten

### 26.3 Important Note on `customers`

"Rekod pelanggan dikongsi" bermaksud dikongsi dalam tenant yang sama. Jangan dedahkan satu profil pelanggan merentas merchant tanpa asas pemprosesan, persetujuan dan model identiti yang jelas. Global user identity dan tenant customer profile ialah konsep berbeza.

---

## BAB 27 — B-Hub Workspace Integrations

### 27.1 Integrations Planned

- Gmail dan Google Calendar
- WhatsApp Business Platform / Cloud API
- Facebook Pages, Instagram professional accounts dan Meta Marketing API
- Google Ads API
- TikTok dan TikTok Ads API (apabila akses tersedia)
- Shopee Open Platform (apabila kelulusan API membenarkan)
- Payment gateways dan bank feeds
- Telegram, e-mel, SMS, push notifications
- Google Maps
- LHDN MyInvois
- Kurier atau agregator logistik

**Nota:** Kemampuan API, kelulusan aplikasi, skop data, had kadar dan syarat komersial berbeza mengikut penyedia. Sistem mesti memaparkan integrasi sebagai `available`, `connected`, `needs_reauthorization`, `limited` atau `unavailable`.

### 27.2 Connector Data Model

- `integrations` — connector definitions
- `integration_accounts` — linked accounts per tenant
- `integration_credentials` — encrypted tokens (secrets never returned to client)
- `integration_sync_runs` — sync job tracking
- `integration_webhook_endpoints` — webhook configuration
- `integration_events` — events received/synced
- `integration_errors` — error logging
- `integration_scopes` — OAuth scope management

Token OAuth disimpan terenkripsi, mempunyai proses refresh/revoke dan tidak dimasukkan ke log biasa.

---

## BAB 28 — B-Flow / B-Trigger Automation

### 28.1 Workflow Model

Workflow terdiri daripada:
- **Trigger:** domain event, webhook, scheduled time atau tindakan manual
- **Condition:** penapis, nilai, status, had dan cabang
- **Action:** panggilan API dalaman, connector atau tugas AI
- **Approval:** kelulusan manusia apabila diperlukan
- **Retry/error policy:** retry terhad, backoff, dead-letter queue
- **Audit:** input, output, masa, pemilik dan keputusan

### 28.2 Example: Shopee Order Received

1. Connector mengesahkan webhook atau mengambil pesanan melalui API
2. B-Trigger mengesahkan tandatangan, timestamp dan idempotency key
3. B-Commerce mengemas kini pesanan melalui API dalaman
4. B-Inventory memproses perubahan stok melalui kontrak domain
5. B-Invoicing menyediakan dokumen jualan yang sesuai
6. B-Omnichannel menghantar mesej pelanggan jika dibenarkan
7. B-Analytics menerima event untuk pelaporan

Setiap langkah mesti selamat untuk diproses semula. Jangan menganggap penghantaran webhook hanya berlaku sekali.

### 28.3 Automation Protection

- Had tindakan per workflow dan per tenant
- Had belanja iklan dan had kewangan
- Dry-run / preview sebelum pengaktifan
- Approval gates
- Kill switch tenant dan global
- Retry yang tidak menghasilkan transaksi berganda
- Pemantauan kegagalan dan dead-letter queue

---

## BAB 29 — B-Scout, B-Intel & B-Compliance

### 29.1 B-Scout — Local Market Intelligence

- Trend carian dan kandungan yang tersedia melalui sumber sah
- Analisis topik mengikut kawasan atau demografi jika data platform menyokongnya
- Ringkasan sentimen dengan sampel dan had data dinyatakan
- Alert untuk lonjakan permintaan atau topik

### 29.2 B-Intel — Competitor Intelligence

- Perbandingan harga, promosi, katalog dan penawaran pesaing daripada sumber yang boleh diakses
- Pemantauan perubahan harga dengan timestamp dan URL sumber
- Analisis jurang produk/perkhidmatan
- Elakkan bypass kawalan akses, scraping yang melanggar syarat atau mengumpul data peribadi tanpa asas sah

### 29.3 B-Opportunity

- Gabungkan data jualan tenant, trend dan pemerhatian pesaing
- Paparkan tahap keyakinan, data yang hilang dan andaian
- Berikan tindakan yang boleh diuji, bukan janji bahawa jualan pasti meningkat

### 29.4 B-Compliance

- Rujukan LHDN MyInvois, PDPA, panduan halal dan peraturan berkaitan
- Setiap kandungan mempunyai sumber rasmi, tarikh disemak dan versi
- Bezakan maklumat umum daripada nasihat undang-undang/perakaunan
- Hantar isu kompleks kepada profesional berkelayakan
- Jadual kemas kini dan amaran apabila sumber rasmi berubah

---

## BAB 30 — Ads Manager (Google Ads & Meta Ads)

### 30.1 Google Ads Support

- Account dan campaign overview
- Search, Display, YouTube dan Performance Max (mengikut sokongan API)
- Bajet, status, impressions, clicks, CTR, CPC, conversions, CPA dan ROAS
- Keyword/search-term insights dan negative keyword workflow
- Draf campaign/ad group/ads, semakan polisi dan penerbitan melalui API
- Change history, budget alerts dan rollback plan

### 30.2 Meta Ads Support

- Ad account, campaign, ad set, ads dan insights
- Facebook dan Instagram placements
- Reach, impressions, clicks, CTR, CPC, CPM, leads, purchases, CPA dan ROAS
- Audience dan creative reporting
- Draf copy, variasi creative dan struktur kempen
- Penerbitan atau perubahan kempen hanya dengan skop yang sesuai dan kelulusan

### 30.3 Shared Ads Manager Features

- Satu dashboard merentas Google dan Meta
- Normalisasi metrik dengan definisi yang jelas
- Perbandingan spend, conversions, CPA dan ROAS
- Bajet harian/bulanan dan amaran
- UTM conventions dan conversion tracking status
- Creative library dan versi copy
- Cadangan AI dengan bukti, jangkaan kesan dan risiko
- Change log dan paparan perubahan sebelum/selepas

### 30.4 AI Ads Flow (8 Langkah)

1. Pemilik menetapkan objektif, kawasan, pelanggan sasaran, bajet dan had
2. AI CMO menyemak produk, laman pendaratan, penawaran dan data sejarah
3. AI Ads Specialist menyediakan strategi dan draf kempen
4. Validator menyemak bajet, tracking, polisi platform, destinasi dan parameter
5. Pemilik meluluskan penerbitan atau perubahan yang ditetapkan
6. Connector menghantar perubahan melalui API rasmi
7. Sistem memantau prestasi dan menghasilkan cadangan
8. Perubahan bajet, pause, publish atau tindakan berimpak tinggi tertakluk pada polisi kelulusan

### 30.5 Advertising Guardrails

- Had belanja keras per akaun, kempen dan tenant
- Had perubahan bajet automatik dalam tempoh tertentu
- Tiada penerbitan atau kenaikan bajet di luar mandat
- Approval untuk kempen baharu, perubahan sasaran penting dan perubahan bajet besar
- Rekord actor, approver, nilai sebelum/selepas dan API response
- Amaran jika conversion tracking rosak atau data tidak lengkap
- AI tidak menjamin kelulusan iklan atau hasil jualan
- Akaun iklan setiap merchant kekal terasing; token tidak dikongsi antara tenant

### 30.6 Additional Entities

- `ad_accounts`, `ad_campaigns`, `ad_creatives`, `ad_performance_snapshots`
- `ad_change_requests`, `ad_budget_policies`, `ad_conversion_sources`, `ad_attribution_events`

Data prestasi boleh disalin ke stor analitik; sumber transaksi dan konfigurasi sebenar kekal melalui connector dan API yang berautoriti.

---

## BAB 31 — Payment Gateways & Couriers

### 31.1 Payment Gateway Drivers

Driver yang dirancang: **Billplz, ToyyibPay, iPay88, Stripe** dan **DuitNow QR** melalui penyedia yang sesuai. Sokongan sebenar bergantung pada kontrak merchant, dokumentasi API, jenis transaksi dan kelayakan.

Jangan menyimpan nombor kad. Gunakan hosted checkout atau token gateway. Pembayaran, refund, settlement dan rekonsiliasi mesti idempotent serta boleh diaudit.

### 31.2 Courier Drivers

**J&T, Pos Laju, Lalamove** atau agregator seperti **EasyParcel**, tertakluk kepada API/akaun. Setiap driver menterjemah status penyedia kepada status dalaman standard.

### 31.3 Messaging Channels

WhatsApp Cloud API, e-mel, SMS, push, Telegram, Facebook dan Instagram mengikut API/permission. Patuhi consent, opt-out, template approval dan polisi platform.

---

# PART VI — IMPLEMENTATION & ROADMAP

## BAB 32 — Implementation Status & Roadmap

### 32.1 Current Implementation Status (19 Okt 2026, demo tempatan; belum di VPS)

| Concept | Status | Screen |
|---------|--------|--------|
| Platform teras (multi-tenant, RBAC, audit, 2FA) | ✅ Siap | `/admin` |
| Subscription & Monetization Engine | 🔨 Dalam pembinaan | `/admin/pricing` |
| Brand Portfolio Management | 🔨 Dalam pembinaan | `/admin/brands` |
| Brain Hub (fasa 1) | ✅ Siap | `/pejabat/otak` |
| AI Team CMO/CFO/COO/CSO + B-Boardroom | ⏳ Seterusnya | |
| B-Hub (Gmail, Calendar, TikTok, Shopee, bank) | ⏸️ Belum (perlu OAuth) | |
| B-Flow / B-Trigger | ⏸️ Belum | |
| B-Scout / B-Intel / B-Compliance | ⏸️ Belum | |
| B-Marketing (basic campaign + segment) | ✅ Siap (basic) | `/pemasaran` |
| B-Marketing Enhanced (sequences, lead scoring) | 📋 Planned Q1 | Month 1-3 |
| Ads Manager (Google/Meta) | ⏸️ Belum (perlu developer approval) | |
| B-POS | 🔨 Dalam pembinaan | |
| B-Loyalty, B-Link, B-Omnichannel | ✅ Siap | `/kesetiaan`, `/blink`, `/inbox` |
| B-HRMS (kehadiran & cuti siap; payroll belum) | 🔨 Separuh siap | `/hr`, `/hr/bos` |
| Finance, Invoicing, Logistics, Procurement | Sebahagian | `/app` |
| B-Site (website builder) | 📋 Planned Q1 | Month 1-3 |
| B-Marketplace | ⏸️ Belum | |
| B-Connect / API Gateway | ⏸️ Belum | |
| Deep CRM (contacts, deals, pipeline) | 📋 Planned Q1 | Month 1-3 |
| Email Sequences (drip campaigns) | 📋 Planned Q1 | Month 1-3 |
| Service Desk (ticketing) | 📋 Planned Q2 | Month 4-6 |
| Quote Builder (professional quotes) | 📋 Planned Q3 | Month 7-9 |
| AI Token Wallet & Gateway | 🔨 Dalam pembinaan | |

### 32.2 Development Roadmap (6 Phases)

#### Fasa 0 — Foundation
- Tetapkan domain boundaries, coding standards dan OpenAPI
- Repo, Docker, CI, environments, secrets dan monitoring
- Identity, tenant membership, permission policies dan audit
- Modul entitlement dan subscription skeleton
- **Exit criteria:** tenant A tidak boleh membaca atau mengubah data tenant B; pipeline CI lulus

#### Fasa 1 — AI Virtual Office MVP
- B-Hub asas, B-Task, B-Workspace, B-Memory
- AI Gateway, Chief of Staff dan ejen CMO/CFO/COO/CSO
- B-Boardroom, human approval dan agent audit
- **Exit criteria:** ejen boleh melengkapkan tugasan melalui API dengan permission checks dan audit lengkap

#### Fasa 2 — Automation
- B-Flow/B-Trigger, event outbox, workflow runs dan retry
- Integrasi e-mel/kalendar pertama
- Webhook signing dan idempotency
- **Exit criteria:** workflow boleh diproses semula tanpa menggandakan tindakan perniagaan

#### Fasa 3 — Marketing & Ads
- B-Marketing, Google Ads connector dan Meta Ads connector
- OAuth, account linking, read-only insights dahulu
- Ads draft builder, approval gates, budget policies dan change log
- Kemudian aktifkan create/update/publish selepas pengesahan API dan ujian keselamatan
- **Exit criteria:** tiada perubahan bajet atau penerbitan tanpa polisi/kelulusan yang sah; akaun setiap tenant terasing

#### Fasa 4 — Core Business Operations + Commerce Enhancement
- Commerce Core, POS, Site, Link, Marketplace
- **B-Site Phase 1:** Basic site builder, 5 themes, product sync, checkout (Month 1-3)
- **B-Site Phase 2:** Drag-and-drop editor, blog, SEO tools, custom domains (Month 4-6)
- **Deep CRM:** Contact DB, company view, deal pipeline, interaction timeline (Month 1-3)
- **Email Sequences:** Drip campaigns, templates, optimal send time (Month 1-3)
- **Lead Scoring:** Behavioral scoring, segmentation (Month 4-6)
- **Service Desk:** Ticket system, SLA, knowledge base (Month 4-6)
- **Quote Builder:** Professional quotes, e-signature, approval workflow (Month 7-9)
- Booking, Loyalty dan Omnichannel
- Invoicing, Finance dan Procurement
- HRMS, Sign & Docs dan Logistics
- **Exit criteria:** ownership data, event contracts, rekonsiliasi dan audit lengkap bagi aliran utama; B-Site generates revenue; CRM improves sales conversion

#### Fasa 5 — Intelligence
- B-Scout, Competitor Intel, B-Compliance
- Advanced analytics dan forecasting
- Full autonomous growth engine

---

## BAB 33 — Customer Experience & Onboarding

### 33.1 Customer Journey

1. **Discovery** — Pelanggan menemui AI BIJAN melalui website, referral atau white-label partner
2. **Registration** — Daftar akaun, verify email/phone
3. **Business Understanding** — 10-stage discovery process (BAB 18)
4. **Setup** — AI provides initial configuration based on Business Operating Profile
5. **Import** — Import existing data (customers, products, etc.)
6. **Training** — Go-live checklist dan training
7. **Go-Live** — Start using modules
8. **Growth** — Continuous learning and optimization

### 33.2 Portal Designs

| Portal | Users | Key Features |
|--------|-------|-------------|
| **Super Admin** | Platform operators | Tenant management, pricing, AI config, system health |
| **Tenant Owner** | Business owners | Organization setup, module selection, billing, AI config |
| **Manager** | Department managers | Team management, reports, approvals, KPIs |
| **Staff** | Employees | Tasks, attendance, leave requests, payslips |
| **Customer** | End customers | Orders, bookings, payments, support tickets |
| **Affiliate** | Partners | Referral links, commissions, performance |

---

## BAB 34 — QA, Testing & Release Readiness

### 34.1 Testing Strategy

- **Unit tests** untuk domain invariants
- **Feature/API tests** untuk endpoint dan policy
- **Architecture tests** untuk larangan dependency antara modul
- **Multi-tenant isolation tests** dengan sekurang-kurangnya dua tenant
- **Contract tests** untuk connector dan webhook
- **Idempotency tests** untuk payment, order dan event consumers
- **Queue retry/dead-letter tests**
- **AI tool permission tests** dan prompt-injection tests
- **Ads budget/approval tests** menggunakan mock/sandbox, bukan akaun live
- **Load tests** untuk endpoint utama dan workflow
- **Restore test backup** berkala
- **Accessibility dan mobile responsiveness tests** untuk UI

### 34.2 CI/CD Pipeline (GitHub Actions)

1. Format/lint
2. Unit dan feature tests (Pest)
3. PHPStan/static analysis
4. Architecture boundary tests
5. Tenant isolation/security tests
6. OpenAPI validation
7. Build artifacts/container
8. Deploy staging
9. Smoke tests dan migration checks
10. Production deploy dengan rollback plan

### 34.3 Release Gates

- Semua critical dan high severity bugs diselesaikan
- Tenant isolation tests lulus
- Security scan bersih
- Performance benchmarks within acceptable range
- Documentation updated
- Rollback plan tested

---

## BAB 35 — Versioning Policy & Changelog

### 35.1 Versioning Scheme

Format: `MAJOR.MINOR.PATCH` (contoh: `4.5.1`)

- **MAJOR:** Breaking changes, significant architecture shifts
- **MINOR:** New features, modules, capabilities
- **PATCH:** Bug fixes, documentation updates, editorial corrections

### 35.2 Document Evolution History

| Version | Date | Key Changes |
|---------|------|-------------|
| v2.1 | 17 Oct 2026 | Original blueprint — 11 AI agents, B-Boardroom, integrations, tech stack |
| v2.2 | — | Universal SME Tools & Industry Packs principle |
| v2.4 | — | AI Provider & Token Reseller model, Shared Agent Memory |
| v2.5 | — | AI Media Studio, Laravel SaaS Edition, Owner-specific design |
| v3.0 | 18 Oct 2026 | Ecosystem structure, 18 modules detailed scope, COGS engine |
| v4.0 | 18 Oct 2026 | Malaysia Industry Coverage, MSIC mapping, 19 industry packs |
| v4.2 | — | Personal AI Assistant, Business Understanding Engine, Autonomous Growth |
| v4.3 | — | Extended Personal AI, continuous learning loop |
| v4.5 | — | Multi-tier Admin, Platform Business, Brand Portfolio |
| v4.5.1 | 18 Oct 2026 | Editorial corrections, serveras.click integration details |
| **v4.5.1 Master** | **19 Oct 2026** | **This consolidated document — ALL versions combined** |

### 35.3 Recommended Document Set

Untuk development reference, gunakan dokumen berikut:
1. **AI-BIJAN-MASTER-BLUEPRINT.md** (dokumen ini) — Single source of truth
2. Architecture files — Detailed technical references
3. Industry coverage — Module mapping per industry
4. Customer-side — UX flows and portal designs

---

# PART VII — OPERATIONS, DEPLOYMENT & GOVERNANCE

## BAB 36 — Disaster Recovery & Business Continuity

### 36.1 RTO & RPO Targets

| System Component | RTO (Recovery Time) | RPO (Data Loss Limit) | Criticality |
|-----------------|--------------------|----------------------|-------------|
| **Platform API** | 15 minutes | 5 minutes | P0 - Critical |
| **Admin Panel** | 30 minutes | 5 minutes | P0 - Critical |
| **Customer Portal** | 30 minutes | 15 minutes | P0 - Critical |
| **Database** | 15 minutes | 5 minutes | P0 - Critical |
| **File Storage (R2)** | 1 hour | 1 hour | P1 - High |
| **Vector Store (Qdrant)** | 2 hours | 15 minutes | P1 - High |
| **Cache (Redis)** | 5 minutes | N/A (rebuildable) | P2 - Medium |
| **Queue (Horizon)** | 30 minutes | All messages preserved | P1 - High |
| **Search (Meilisearch)** | 1 hour | 15 minutes | P2 - Medium |
| **Email/SMS Service** | 4 hours | N/A (retry possible) | P2 - Medium |
| **AI Gateway** | 15 minutes | N/A (external) | P0 - Critical |

### 36.2 Backup Strategy

| Backup Type | Frequency | Retention | Storage Location | Encryption |
|------------|-----------|-----------|-----------------|------------|
| **Full Database** | Every 6 hours | 30 days | Primary + Secondary region | AES-256 at rest |
| **Incremental DB** | Every 15 minutes | 7 days | Primary + Secondary region | AES-256 at rest |
| **File Storage** | Daily | 90 days | Multi-region R2 buckets | Server-side encryption |
| **Vector Store** | Daily | 30 days | Synced to secondary Qdrant cluster | Network-level only |
| **Configuration** | Every change | Unlimited | Git repository (versioned) | Git signing |
| **Backup Snapshots** | Weekly | 1 year | Cold storage (R2 Glacier) | AES-256 + KMS |
| **Disaster Recovery Image** | Monthly | 3 months | DR region (different datacenter) | Full disk encryption |

### 36.3 Backup Verification

```
Backup Integrity Check (Daily 03:00 MYT):
1. Verify backup file checksums (SHA-256)
2. Test restore to isolated staging environment
3. Validate database consistency (pg_dump --check equivalent)
4. Spot-check random records for data integrity
5. Test file storage restoration (download sample files)
6. Verify vector store index rebuild capability
7. Generate backup health report → send to admin dashboard
8. Alert on any failure via SMS + email
```

### 36.4 Disaster Scenarios & Playbooks

#### Scenario A: Database Corruption
```
Trigger: Data corruption detected or failed migration
Response:
1. Switch to read-only mode immediately
2. Identify last known good backup point
3. Restore from incremental backup closest to failure time
4. Replay transaction logs from WAL/binlog
5. Run data consistency checks
6. Verify with sample queries
7. Switch back to read-write mode
8. Root cause analysis within 24 hours
Downtime: 15-30 minutes
```

#### Scenario B: Complete Server Failure
```
Trigger: Hardware failure, OS crash, network outage
Response:
1. Auto-detect via health check monitors (every 30 seconds)
2. DNS failover to secondary server (TTL: 30 seconds)
3. Load balancer removes unhealthy instance
4. New instance auto-provisioned from AMI/image
5. Database connection routed to replica (if primary down)
6. Rejoin worker nodes to queue
7. Verify all services healthy
8. Post-incident review
Downtime: 5-15 minutes
```

#### Scenario C: Ransomware / Security Breach
```
Trigger: Malicious activity detected, data exfiltration suspected
Response:
1. Immediate isolation: block affected tenant(s), revoke compromised tokens
2. Activate incident response team
3. Preserve forensic evidence (disk image, logs)
4. Restore clean systems from verified backup (pre-compromise)
5. Rotate ALL credentials (DB passwords, API keys, JWT secrets)
6. Patch vulnerability that enabled breach
7. Notify affected tenants within 72 hours (PDPA requirement)
8. Engage external security firm for full audit
9. Report to personal data protection authority if required
Downtime: 2-24 hours (depends on severity)
```

#### Scenario D: Regional Outage (Cloud Provider)
```
Trigger: AWS/Azure/GCP region-wide outage
Response:
1. Multi-region DNS routing redirects traffic to healthy region
2. Database fails over to read-replica in secondary region
3. Cache rebuilt from persistent store
4. Queue processing resumes on new region's workers
5. Monitor performance during transition
6. Communicate status to customers via status page
Downtime: 2-10 minutes (depends on DNS propagation)
```

### 36.5 Business Continuity Plan

| Event | Impact Level | Response Team | Communication |
|-------|-------------|---------------|---------------|
| Minor incident (< 1 hr downtime) | Low | On-call engineer | Status page update |
| Major incident (1-4 hrs) | High | Engineering lead + manager | Email to affected tenants |
| Catastrophic (> 4 hrs) | Critical | Full team + management | SMS, email, phone tree |
| Data breach | Critical | CISO + legal + management | PDPA notification within 72 hrs |

### 36.6 DR Testing Schedule

| Test Type | Frequency | Scope | Success Criteria |
|-----------|-----------|-------|-----------------|
| **Tabletop exercise** | Quarterly | Management review | All roles understand responsibilities |
| **Failover test** | Monthly | Database → secondary region | RTO met, data integrity verified |
| **Full DR drill** | Bi-annually | Complete system failover | All services operational in DR |
| **Restore test** | Weekly | Random backup restore | Data consistent, spot-check passes |
| **Security simulation** | Annually | Pen test + incident response | Vulnerabilities identified and fixed |

---

## BAB 37 — Performance Engineering & Scaling

### 37.1 Performance Benchmarks & SLA

| Metric | Target | Measurement | Alert Threshold |
|--------|--------|-------------|----------------|
| **API Response Time (p50)** | < 200ms | Laravel Pulse / Sentry | > 500ms sustained |
| **API Response Time (p95)** | < 500ms | Laravel Pulse / Sentry | > 1,500ms |
| **API Response Time (p99)** | < 1,000ms | Laravel Pulse / Sentry | > 3,000ms |
| **Page Load Time (First Content)** | < 1.5s | Web Vitals / Lighthouse | > 3s |
| **Time to Interactive** | < 3.5s | Web Vitals | > 6s |
| **Database Query Time (p95)** | < 100ms | Slow query log | > 500ms |
| **Queue Processing Latency** | < 30s | Horizon metrics | > 5 minutes |
| **Uptime** | 99.9% monthly | Uptime monitoring | < 99.5% |
| **Concurrent Users (per tenant)** | 50 standard | Session tracking | Warning at 80% |
| **AI Response Time** | < 5s (simple), < 30s (complex) | AI usage ledger | > 10s / > 60s |

### 37.2 Capacity Planning

| Resource | Current Capacity | Growth Factor | Upgrade Trigger | Max Scale |
|----------|-----------------|---------------|-----------------|-----------|
| **Application Servers** | 3 instances | +1 per 500 tenants | CPU > 70% avg | Auto-scale to 20 |
| **Database (MySQL)** | 32 GB RAM, 500 GB SSD | +16 GB per 1,000 tenants | Disk > 70%, CPU > 60% | RDS Multi-AZ, 128 GB max |
| **Redis Cache** | 16 GB | +8 GB per 1,000 tenants | Memory > 75% | Cluster mode, 64 GB |
| **Queue Workers** | 10 workers | +5 per 500 tenants | Queue depth > 1,000 | Horizontal scale |
| **Websocket (Reverb)** | 5,000 connections | +2,000 per 500 tenants | Connections > 80% | Auto-scale |
| **Object Storage (R2)** | 1 TB | Per-tenant ~2 GB | Cost threshold | Unlimited (S3) |
| **Vector Store (Qdrant)** | 500K vectors | Per-tenant ~10K vectors | Index size > 70% | Shard across nodes |
| **Bandwidth** | 1 Gbps | +500 Mbps per 1,000 tenants | Throughput > 70% | Auto-upgrade |

### 37.3 Scaling Strategies

```
Horizontal Scaling (Stateless Services):
├── Application servers behind load balancer
├── Auto-scaling based on CPU/memory/requests
├── Container orchestration (Docker Swarm / Kubernetes)
└── Zero-downtime deployments (rolling update)

Vertical Scaling (Stateful Services):
├── Database: Upgrade instance type when thresholds hit
├── Redis: Migrate to larger instance or cluster
└── Qdrant: Add read replicas for search offload

Database Scaling:
├── Read replicas: 1 primary + 2 read replicas
├── Connection pooling: PgBouncer / ProxySQL
├── Partitioning: Large tables by tenant_id (hash)
├── Archiving: Old data to cold storage quarterly
└── Query optimization: Index tuning, EXPLAIN analysis
```

### 37.4 Caching Strategy

| Layer | Technology | What's Cached | TTL | Invalidation |
|-------|-----------|--------------|-----|-------------|
| **Browser** | HTTP cache | Static assets, API responses | Varies | Cache-Control headers |
| **CDN** | Cloudflare | Static files, images, API cache | 1h - 24h | Purge on demand |
| **Laravel** | Redis | Session, config, query results | 5m - 24h | Key-based invalidation |
| **API** | Response caching | GET endpoints (non-personalized) | 1m - 15m | Route-based purge |
| **Database** | Query cache | Frequently executed SELECT | N/A | MySQL query cache disabled |
| **Vector Store** | Qdrant | Embedding cache | Persistent | Re-embed on document change |
| **Application** | Local memory | Tenant config, feature flags | 5m | Manual refresh |

### 37.5 Load Testing Protocol

```
Load Test Execution (Monthly):
1. Define test scenario (normal, peak, stress, spike)
2. Configure test environment (mirror of production)
3. Execute with increasing virtual users:
   - Normal: 100 concurrent users, 1 hour
   - Peak: 500 concurrent users, 30 minutes
   - Stress: Find breaking point (start 500, increase by 100)
   - Spike: Sudden jump from 50 to 1,000 users
4. Monitor: response times, error rates, resource usage
5. Identify bottlenecks: DB queries, API calls, memory leaks
6. Optimize and re-test until targets met
7. Document results and update capacity plan
```

### 37.6 Performance Monitoring Dashboard

Real-time metrics displayed in Filament Admin:
- Active users per tenant (real-time)
- API response time percentiles (p50, p95, p99)
- Error rate by endpoint (last 1h, 24h, 7d)
- Queue depth and processing speed
- Database connection pool utilization
- Cache hit ratio
- AI token consumption rate
- Storage usage per tenant
- Bandwidth consumption

---

## BAB 38 — Data Migration Framework

### 38.1 Import Sources Supported

| Source | Format | Tool | Validation |
|--------|--------|------|------------|
| **Excel / CSV** | .xlsx, .csv | Built-in importer | Column mapping, type check, duplicate detection |
| **Google Sheets** | Google API | Connector import | Auth required, real-time sync optional |
| **JSON API** | JSON response | API importer | Schema validation, field mapping |
| **Database dump** | SQL, Parquet | Migration script | Schema comparison, data transformation |
| **Legacy system** | Proprietary format | Custom adapter | Per-system mapping config |
| **Manual entry** | Web form | UI wizard | Field-by-field validation |

### 38.2 Migration Process

```
Data Migration Workflow:
1. Upload source file / connect to source system
2. System auto-detects schema and suggests column mapping
3. User confirms/reviews mapping (visual drag-and-drop UI)
4. Preview sample rows with validation results
5. Run dry-run: detect duplicates, missing required fields, type mismatches
6. User approves migration plan
7. Execute migration in batches (1,000 records/batch)
8. Real-time progress dashboard
9. Post-migration validation report
10. Rollback option available within 24 hours
```

### 38.3 Migration Data Model

| Table | Purpose |
|-------|---------|
| `migration_jobs` | Track import/export jobs |
| `migration_mappings` | Source-to-target field mappings |
| `migration_batches` | Batch-level tracking and status |
| `migration_errors` | Per-record error details |
| `migration_rollback_logs` | Undo history for rollback capability |

### 38.4 Pre-Migration Checklist

- [ ] Backup current tenant data (if migrating FROM existing system)
- [ ] Verify source data quality (no corrupted records)
- [ ] Map all custom fields and extensions
- [ ] Test migration on staging environment first
- [ ] Confirm data ownership and consent for transfer
- [ ] Prepare rollback plan with estimated recovery time
- [ ] Notify all users of planned downtime window

### 38.5 Post-Migration Validation

| Check | Method | Success Criteria |
|-------|--------|-----------------|
| Record count | Compare source vs target | ±0% variance |
| Data integrity | Sample random records | All fields match |
| Referential integrity | Foreign key checks | No orphaned records |
| Business logic | Run sample transactions | Expected outcomes |
| Permission mapping | Test user access levels | Correct permissions |
| File attachments | Download and verify | File size matches, no corruption |
| Date/time zones | Cross-check timestamps | All converted to Asia/Kuala_Lumpur |

### 38.6 Export Capabilities

| Export Type | Format | Scope | Use Case |
|------------|--------|-------|----------|
| **Full export** | JSON + SQL dump | Entire tenant | Backup, migration away |
| **Module export** | CSV, Excel | Single module | Reporting, analysis |
| **Financial export** | PDF, CSV, XML | Finance/Invoicing | Accounting software, tax filing |
| **Audit export** | JSON, PDF | Audit trail | Compliance, investigation |
| **AI training data** | JSONL | Conversations + outcomes | Model fine-tuning (opt-in) |

---

## BAB 39 — Security Operations & Pentesting

### 39.1 Security Architecture Layers

```
Layer 1: Network Security
├── DDoS protection (Cloudflare)
├── WAF (Web Application Firewall)
├── IP allowlisting for admin access
└── VPC isolation for internal services

Layer 2: Application Security
├── Input validation (server-side + client-side)
├── CSRF protection (tokens on all forms)
├── XSS prevention (Blade escaping, CSP headers)
├── SQL injection prevention (Eloquent ORM parameterized queries)
├── Rate limiting on all endpoints
└── Security headers (HSTS, X-Frame-Options, etc.)

Layer 3: Authentication & Authorization
├── 2FA/MFA enforcement for admin accounts
├── Password policy (min 12 chars, complexity, breach check)
├── Session management (secure cookies, rotation on login)
├── RBAC with least-privilege principle
└── API key scoping and rotation

Layer 4: Data Security
├── Encryption at rest (AES-256 for database, file storage)
├── Encryption in transit (TLS 1.3 everywhere)
├── Field-level encryption for sensitive data (SSN, bank accounts)
├── Secrets management (Laravel Vault / HashiCorp Vault)
└── Key rotation schedule (quarterly)

Layer 5: Tenant Isolation
├── Database scoped queries (tenant_id middleware)
├── Namespace isolation in Qdrant, Redis, R2
├── Cross-tenant query blocking (architecture tests)
└── Regular isolation penetration testing
```

### 39.2 Penetration Testing Schedule

| Test Type | Frequency | Scope | Performed By |
|-----------|-----------|-------|-------------|
| **Automated scan** | Weekly | All public endpoints | OWASP ZAP / Burp Suite CI |
| **Internal code review** | Monthly | New features, auth changes | Internal security team |
| **External pentest** | Quarterly | Full application | Certified third-party firm |
| **Social engineering** | Bi-annually | Phishing simulation | External firm |
| **Physical security audit** | Annually | Datacenter, office | Third-party auditor |
| **Red team exercise** | Annually | Full attack simulation | Elite external team |

### 39.3 Vulnerability Disclosure Program

```
Bug Bounty Workflow:
1. Researcher submits vulnerability via secure portal
2. Security team acknowledges within 24 hours
3. Severity assessment (Critical / High / Medium / Low)
4. Fix timeline:
   - Critical: 24 hours
   - High: 7 days
   - Medium: 30 days
   - Low: Next release cycle
5. Fix deployed and verified
6. Researcher notified and rewarded (if eligible)
7. Disclosure published (with researcher permission)
```

**Reward Structure:**
| Severity | Bounty Range | Example Finding |
|----------|-------------|-----------------|
| Critical | RM1,000 - RM10,000 | RCE, full tenant data exposure |
| High | RM500 - RM3,000 | SQL injection, auth bypass |
| Medium | RM200 - RM1,000 | XSS, IDOR, information disclosure |
| Low | RM50 - RM500 | CSRF on non-critical endpoint |

### 39.4 Security Incident Response

| Phase | Action | Timeline |
|-------|--------|----------|
| **Detection** | Automated alert or user report | Immediate |
| **Triage** | Assess severity, scope, impact | Within 1 hour |
| **Containment** | Isolate affected systems, revoke access | Within 2 hours |
| **Eradication** | Remove root cause, patch vulnerability | Within 24 hours |
| **Recovery** | Restore from clean backup, verify integrity | Within 48 hours |
| **Post-Incident** | Root cause analysis, lessons learned, update playbooks | Within 7 days |

### 39.5 Compliance Certifications Roadmap

| Certification | Target Date | Status | Key Requirements |
|--------------|-------------|--------|-----------------|
| **PDPA Malaysia** | Now (baseline) | In progress | Consent management, data subject rights, breach notification |
| **ISO 27001** | Year 2 | Planned | ISMS implementation, risk assessment, annual audits |
| **SOC 2 Type II** | Year 2 | Planned | Security, availability, confidentiality controls |
| **PCI DSS** | Year 3 (if card storage) | Future | Payment card data handling, network segmentation |
| **MyDigitalID** | Year 1 | Planned | National digital identity integration |

### 39.6 Security Configuration Checklist

Every new tenant setup must verify:
- [ ] TLS 1.3 enforced, weak ciphers disabled
- [ ] Security headers present on all responses
- [ ] 2FA available and encouraged for admin users
- [ ] Password policy enforced (min 12 chars, no common passwords)
- [ ] API rate limiting active
- [ ] CORS configured correctly (no wildcard *)
- [ ] Debug mode disabled in production
- [ ] Error messages don't leak stack traces or internal paths
- [ ] Database credentials rotated and stored in vault
- [ ] Backup encryption verified
- [ ] Tenant isolation tests pass
- [ ] Audit logging enabled for all actions

---

## BAB 40 — CI/CD & Release Management

### 40.1 Deployment Pipeline Architecture

```
Source Code (Git)
    ↓
[PRE-COMMIT HOOKS]
├── Lint (PHP-CS-Fixer, ESLint, Prettier)
├── Format check
└── Commit message validation (Conventional Commits)
    ↓
[PUSH TO BRANCH]
├── GitHub Actions workflow triggered
├── Branch protection rules enforced
└── Required reviews (min 2 approvers for main)
    ↓
[CI - Continuous Integration]
├── Step 1: Install dependencies (composer, npm)
├── Step 2: Unit tests (Pest, Jest)
├── Step 3: Feature/API tests (Pest)
├── Step 4: PHPStan static analysis (level 5+)
├── Step 5: Architecture boundary tests
├── Step 6: Tenant isolation tests (multi-tenant)
├── Step 7: OpenAPI contract validation
├── Step 8: Security scan (Dependabot, Snyk)
├── Step 9: Build frontend assets (Vite, Next.js)
├── Step 10: Docker image build and push
└── Step 11: Deploy to staging environment
    ↓
[STAGING VALIDATION]
├── Smoke tests (automated)
├── Migration backward compatibility check
├── Performance benchmark comparison
├── E2E tests (Playwright)
└── Manual QA sign-off (for major releases)
    ↓
[PRODUCTION DEPLOYMENT]
├── Blue-green deployment strategy
├── Health checks post-deploy
├── Gradual traffic shift (0% → 25% → 50% → 100%)
├── Real-time monitoring (Sentry, Laravel Pulse)
├── Rollback trigger (error rate > 1% or p95 > 2x baseline)
└── Post-deploy verification checklist
```

### 40.2 Environment Strategy

| Environment | Purpose | Data | Update Frequency | Access |
|------------|---------|------|-----------------|--------|
| **Local** | Developer machine | Seed data | Multiple times daily | Developers |
| **Feature branch** | Isolated development | Stubs | On merge | Feature branch team |
| **Staging** | Pre-production testing | Anonymized production copy | Every PR merge | QA, product, select devs |
| **Production** | Live customer traffic | Real data | Scheduled releases | DevOps, on-call engineers |
| **DR Region** | Disaster recovery | Synced from prod | Continuous replication | DevOps only |

### 40.3 Release Types & Cadence

| Type | Frequency | Scope | Approval Required | Example |
|------|-----------|-------|------------------|---------|
| **Hotfix** | As needed | Critical bug fix | Engineering lead | Security patch, data loss fix |
| **Patch** | Weekly | Minor bug fixes, docs | Tech lead | CSS fix, typo correction |
| **Minor** | Bi-weekly | New features, enhancements | Product owner + tech lead | New module, UI improvement |
| **Major** | Quarterly | Significant architecture changes | Executive approval | v4.0 → v5.0, new platform features |
| **Maintenance** | Monthly | Dependency updates, refactoring | Tech lead | Laravel version bump, library updates |

### 40.4 Blue-Green Deployment Procedure

```
Blue-Green Deployment Steps:
1. Deploy new version to Green environment (idle)
2. Run database migrations (backward compatible)
3. Run smoke tests on Green
4. Verify Green is healthy and serving requests
5. Switch load balancer: Blue (production) → Green (new)
6. Monitor error rates, response times, resource usage
7. If issues detected: immediate switch back to Blue (rollback)
8. If stable: keep Blue as warm standby (for quick rollback)
9. After 24 hours stable: decommission old Blue container
10. Document deployment: version, timestamp, deployer, metrics
```

### 40.5 Rollback Procedures

| Scenario | Rollback Method | Estimated Time | Data Safety |
|----------|----------------|---------------|-------------|
| **Deployment failure** | Switch back to previous blue/green | 2 minutes | No data loss |
| **Regression bug** | Revert code + redeploy | 10 minutes | Previous state preserved |
| **Database migration issue** | Rollback migration script | 5-30 minutes | Data preserved, schema reverted |
| **Performance regression** | Revert + optimize before retry | 10 minutes + fix time | No data impact |
| **Security vulnerability** | Emergency revert + patch | 5 minutes | Security improved |

### 40.6 Feature Flag System

| Flag Type | Purpose | Lifecycle | Example |
|-----------|---------|-----------|---------|
| **Experimental** | Test new features with subset of users | Days to weeks | `enable_ai_cmo_v2` |
| **Rollout** | Gradual feature rollout (percentage) | Weeks to months | `rollout_b_pos_50_percent` |
| **Toggle** | Quick enable/disable without deploy | Indefinite | `maintenance_mode` |
| **Opt-in** | Beta features for interested tenants | Until GA | `beta_marketplace` |
| **Kill switch** | Emergency disable for critical issues | Hours to days | `kill_switch_ai_gateway` |

**Feature Flag Management:**
- Flags stored in database (not code) for runtime control
- Admin dashboard to toggle flags per tenant or globally
- Automatic flag cleanup (remove unused flags every sprint)
- Flag analytics (usage stats, conversion impact)

### 40.7 Release Checklist

Pre-release verification:
- [ ] All CI pipeline stages pass
- [ ] Changelog updated with new features, fixes, breaking changes
- [ ] Migration scripts tested on staging with production-like data
- [ ] Rollback procedure documented and tested
- [ ] Documentation updated (API docs, user guides)
- [ ] Customer communication prepared (release notes, emails)
- [ ] Support team briefed on new features and known issues
- [ ] Monitoring alerts configured for new endpoints/features
- [ ] Performance benchmarks compared to previous release
- [ ] Security scan clean (no new vulnerabilities)
- [ ] Tenant isolation tests pass
- [ ] Feature flags set to correct initial state

---

## BAB 41 — Cost Optimization & FinOps

### 41.1 Infrastructure Cost Breakdown

| Component | Unit Cost | Monthly Estimate (100 tenants) | Monthly Estimate (1,000 tenants) | Scaling Factor |
|-----------|-----------|-------------------------------|---------------------------------|----------------|
| **Application Servers** | RM150/server/month | RM450 (3 servers) | RM1,500 (10 servers) | +RM150 per 100 tenants |
| **Database (RDS)** | RM500/db/month | RM500 | RM1,500 | +RM150 per 100 tenants |
| **Redis Cluster** | RM200/cache/month | RM200 | RM500 | +RM50 per 100 tenants |
| **Queue Workers** | RM50/worker/month | RM500 (10 workers) | RM2,000 (40 workers) | +RM200 per 100 tenants |
| **Websocket (Reverb)** | RM100/ws/month | RM100 | RM300 | +RM50 per 100 tenants |
| **Object Storage (R2)** | RM0.015/GB/month | RM150 (10,000 GB) | RM1,500 (100,000 GB) | Linear per GB |
| **Vector Store (Qdrant)** | RM300/node/month | RM300 | RM900 | +RM300 per 500 tenants |
| **Search (Meilisearch)** | RM200/search/month | RM200 | RM400 | +RM100 per 500 tenants |
| **CDN (Cloudflare)** | RM0.10/1K requests | RM100 | RM800 | Per request volume |
| **AI Gateway (serveras.click)** | Per-token billing | Variable | Variable | Pass-through to customer |
| **Monitoring (Sentry, Pulse)** | RM100/monitor/month | RM200 | RM500 | Per project |
| **Backup Storage** | RM0.012/GB/month | RM100 | RM500 | Linear per GB |
| **Total Est. Monthly** | | **~RM2,850** | **~RM9,750** | |

### 41.2 Cost Allocation Per Tenant

| Tenant Tier | Infra Cost/Month | Target Margin | Price Point | Profit/Month |
|------------|-----------------|--------------|-------------|-------------|
| Free Trial | RM50 | 0% (loss leader) | RM0 | -RM50 (CAC) |
| Starter (RM288) | RM30 | 89% | RM288 | RM258 |
| Professional (RM488) | RM50 | 90% | RM488 | RM438 |
| Business (RM888) | RM70 | 92% | RM888 | RM818 |
| Business Pro (RM1,288) | RM100 | 92% | RM1,288 | RM1,188 |
| Enterprise (RM1,888) | RM150 | 92% | RM1,888 | RM1,738 |
| **Weighted Average** | **RM65** | **~91%** | **RM888** | **RM823** |

### 41.3 Cost Optimization Strategies

| Strategy | Potential Savings | Implementation Effort | Priority |
|----------|------------------|----------------------|----------|
| **Auto-scaling** | 30-40% on compute | Medium | HIGH |
| **Reserved instances** | 20-30% on DB, servers | Low | HIGH |
| **Spot/preemptible instances** | 60-70% on workers | High (requires fault tolerance) | MEDIUM |
| **Query optimization** | 20% on DB cost | Medium | HIGH |
| **Cache warming** | 15% on API cost | Low | HIGH |
| **Storage tiering** | 40% on storage | Low | MEDIUM |
| **Batch processing** | 25% on queue cost | Medium | MEDIUM |
| **Right-sizing instances** | 15-25% overall | Low | HIGH |
| **Multi-tenant density** | Economies of scale | Ongoing | HIGH |
| **AI token optimization** | Reduce prompt size, use smaller models | Medium | MEDIUM |

### 41.4 FinOps Governance

```
Monthly Cost Review Process:
1. Automated cost report generated on 1st of each month
2. Compare actual vs budgeted costs (variance analysis)
3. Identify cost anomalies (> 10% deviation from forecast)
4. Review per-tenant profitability (cost to serve vs revenue)
5. Evaluate optimization opportunities (right-sizing, reserved instances)
6. Update cost projections for next quarter
7. Present to leadership: total spend, cost per tenant, margin trends
8. Approve budget adjustments if needed
```

### 41.5 Cost Alerting

| Alert | Threshold | Action |
|-------|-----------|--------|
| **Monthly budget exceeded** | > 90% of budget | Notify finance team |
| **Per-tenant cost spike** | > 50% above average | Investigate root cause |
| **Storage growth anomaly** | > 20% increase in 7 days | Check for data leak |
| **AI token overspend** | > 80% of tenant limit | Notify tenant admin |
| **Unused resources** | 0 utilization for 7 days | Schedule decommission |
| **Cost per tenant trend** | Increasing month-over-month | Review optimization strategies |

---

## BAB 42 — Customer Training & Success

### 42.1 Training Program Structure

| Module | Audience | Format | Duration | Completion Requirement |
|--------|----------|--------|----------|----------------------|
| **Getting Started** | All users | Video series + interactive tour | 30 minutes | Mandatory |
| **Role-Based Training** | Managers, Staff | Role-specific video modules | 1-2 hours | Mandatory for role |
| **AI Assistant Training** | All users | Hands-on workshop | 45 minutes | Recommended |
| **Advanced Features** | Power users | Live webinar / recorded | 1 hour per topic | Optional |
| **Admin Certification** | Platform admins | Self-study + exam | 4 hours | Required for admin role |
| **Industry-Specific** | Industry pack users | Domain expert sessions | 2 hours | Recommended |

### 42.2 Onboarding Checklist Template

```
Week 1: Foundation
├── [ ] Account setup and 2FA enabled
├── [ ] Business Understanding survey completed
├── [ ] Initial configuration AI-generated
├── [ ] Team members invited and onboarded
└── [ ] Getting Started training completed

Week 2: Data Import
├── [ ] Products/customers imported (or confirmed empty)
├── [ ] First module selected and configured
├── [ ] AI Assistant trained on business context
└── [ ] First AI task executed successfully

Week 3: Activation
├── [ ] First transaction processed (sale, invoice, booking)
├── [ ] Reports viewed and understood
├── [ ] Notifications configured
└── [ ] Success check-in call with customer success team

Week 4: Optimization
├── [ ] Advanced features explored
├── [ ] Additional modules evaluated
├── [ ] AI insights reviewed and acted upon
└── [ ] Quarterly business review scheduled
```

### 42.3 Customer Success Metrics

| Metric | Target | Measurement | Action if Below Target |
|--------|--------|-------------|----------------------|
| **Activation Rate** | > 70% within 14 days | Feature adoption tracking | Proactive outreach, additional training |
| **Weekly Active Users** | > 60% of licensed users | Login frequency tracking | Engagement campaigns, feature nudges |
| **Time to First Value** | < 48 hours | Registration to first transaction | Simplify onboarding flow |
| **Feature Adoption** | > 3 modules per tenant | Module usage analytics | Personalized recommendations |
| **Support Ticket Volume** | < 2 per user/month | Ticket count | Improve documentation, self-service |
| **Net Promoter Score (NPS)** | > 40 | Quarterly survey | Address pain points, improve experience |
| **Churn Rate** | < 3% monthly | Subscription cancellation | Win-back campaigns, value demonstration |

### 42.4 Knowledge Base & Self-Service

| Resource | Format | Access | Topics Covered |
|----------|--------|--------|---------------|
| **Help Center** | Web articles | All users | Setup, troubleshooting, FAQs |
| **Video Tutorials** | YouTube + embedded | All users | Step-by-step visual guides |
| **API Documentation** | Swagger/OpenAPI | Developers | Endpoints, examples, SDKs |
| **Community Forum** | Discourse / Discord | Premium+ users | Peer support, feature requests |
| **Status Page** | UptimeRobot / BetterUptime | Public | System uptime, incidents |
| **Release Notes** | Blog + in-app | All users | What's new, improvements |
| **Webinar Series** | Live + recorded | All users | Monthly deep-dive sessions |

---

# PART VIII — CUSTOMIZATION, EXTENSIBILITY & FUTURE ROADMAP

## BAB 43 — Module Customization & Extensibility Framework

### 43.1 Visi

Setiap B-Module dalam AI BIJAN direka supaya boleh di-customize oleh setiap tenant tanpa mengubah kod sumber platform. Customization berlaku pada **configuration layer**, bukan code layer. Ini membolehkan:

- Tenant A ada workflow invoice berbeza dari Tenant B
- Tenant C tambah custom fields pada produk tanpa affect Tenant D
- White-label admin tambah plugin khusus untuk industri mereka
- Platform tetap stable dan upgradeable walaupun 1,000 tenant customize berbeza

> **Prinsip Utama:** Configuration before customization. Default works for 80%, customization covers 20%.

### 43.2 Custom Fields Engine

Setiap modul (Finance, Inventory, HRMS, dll) boleh terima custom fields yang ditambahkan oleh tenant admin.

#### Data Model — `module_custom_fields`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT FK | Tenant scope |
| module_name | VARCHAR(100) | Modul target (finance, inventory, hrms, dll) |
| table_name | VARCHAR(100) | Table sasaran (invoices, products, employees, dll) |
| field_name | VARCHAR(100) | Nama slug field |
| field_label | VARCHAR(255) | Display label (multilingual supported) |
| field_type | ENUM('text','number','date','datetime','boolean','select','multiselect','textarea','file','richtext','currency','phone','email','url','json','reference') | Data type |
| options_json | JSON | Pilihan untuk select/multiselect [{"value":"a","label":"A"}] |
| validation_rules | JSON | {required:true, min:3, max:255, pattern:"^RM"} |
| default_value | TEXT | Nilai default |
| placeholder | VARCHAR(255) | Placeholder text |
| display_order | INT | Susunan dalam form |
| is_visible | BOOLEAN | Visible di UI? |
| is_editable | BOOLEAN | Boleh edit selepas create? |
| is_searchable | BOOLEAN | Muncul dalam search/filter? |
| permission_role_ids | JSON | Role IDs yang boleh akses |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### Example: Custom Fields untuk Invoices

```
Tenant: "Kedai Kuih Mak Haliah"
Module: Finance → Invoicing

Custom Field 1:
  - field_name: "delivery_date"
  - field_label: "Tarikh Penghantaran"
  - field_type: "date"
  - required: true
  - display_order: 5

Custom Field 2:
  - field_name: "payment_terms_note"
  - field_label: "Nota Terma Pembayaran"
  - field_type: "textarea"
  - placeholder: "Cth: Bayaran 30 hari dari tarikh invois"
  - display_order: 6

Custom Field 3:
  - field_name: "loyalty_points_earned"
  - field_label: "Mata Kesetiaan Diperoleh"
  - field_type: "number"
  - default_value: 0
  - is_visible: false (auto-calculated by loyalty module)
```

#### Runtime Behavior

```php
// When loading an invoice form:
$customFields = CustomFieldService::getForModule('invoices', $tenantId);
$formData = Invoice::find($id)->toArray();
$formData['custom'] = InvoiceCustomData::where('invoice_id', $id)->first()?->fields ?? [];

// Validation includes custom rules
$request->validate(array_merge($baseRules, $customFields->validationRules));

// Search includes custom fields
Invoice::search(['custom.delivery_date' => '>=', 'custom.payment_terms_note' => 'contains']);
```

### 43.3 Form Layout Customization

Tenant boleh susun semula layout form melalui drag-and-drop UI builder.

#### Data Model — `form_layouts`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT FK | Tenant scope |
| user_id | BIGINT NULL | NULL = default layout for all users |
| role_id | BIGINT NULL | Layout per role (overrides user-specific) |
| module_name | VARCHAR(100) | Modul target |
| form_name | VARCHAR(100) | Form target (create_invoice, edit_product, dll) |
| layout_json | JSON | Array of sections, rows, fields dengan configuration |
| is_active | BOOLEAN | Active layout? |
| version | INT | Version control |

#### Layout JSON Structure

```json
{
  "sections": [
    {
      "name": "customer_info",
      "title": "Maklumat Pelanggan",
      "collapsible": true,
      "collapsed": false,
      "fields": [
        {"field": "customer_id", "width": 8},
        {"field": "customer_name", "readonly": true, "width": 4}
      ]
    },
    {
      "name": "line_items",
      "title": "Perkara Invois",
      "repeater": true,
      "fields": [
        {"field": "product_id", "type": "autocomplete"},
        {"field": "description", "width": 6},
        {"field": "quantity", "width": 2},
        {"field": "unit_price", "width": 2},
        {"field": "discount_pct", "width": 2},
        {"field": "tax_amt", "width": 2}
      ]
    },
    {
      "name": "custom_fields_section",
      "title": "Maklumat Tambahan",
      "dynamic": true,
      "source": "module_custom_fields",
      "filter": {"table_name": "invoices", "is_visible": true}
    }
  ]
}
```

### 43.4 Workflow Customization

Setiap modul mempunyai workflow yang boleh diubah suai per tenant.

#### Data Model — `workflow_definitions`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT FK | Tenant scope |
| module_name | VARCHAR(100) | Modul target |
| workflow_name | VARCHAR(100) | Nama workflow (invoice_approval, product_publish, dll) |
| trigger_event | VARCHAR(100) | Event pemicu (InvoiceCreated, ProductUpdated, dll) |
| steps_json | JSON | Array of workflow steps dengan conditions |
| is_active | BOOLEAN | Active? |
| version | INT | Version control |
| created_by | BIGINT FK | User yang create |

#### Example: Invoice Approval Workflow

```json
{
  "workflow_name": "invoice_approval",
  "trigger_event": "InvoiceDrafted",
  "steps": [
    {
      "step": 1,
      "action": "check_amount",
      "condition": {
        "field": "total_amount",
        "operator": ">",
        "value_field": "approval_limit"
      },
      "then": "step_2",
      "else": "auto_approve"
    },
    {
      "step": 2,
      "action": "request_approval",
      "approver_role": "manager",
      "notification": "email + in_app",
      "timeout_hours": 48,
      "escalation": "step_3",
      "then": "step_4"
    },
    {
      "step": 3,
      "action": "escalate_to",
      "approver_role": "owner"
    },
    {
      "step": 4,
      "action": "finalize",
      "status_change": "Approved",
      "notify": ["creator", "finance_team"]
    }
  ]
}
```

### 43.5 Permission Customization

Setiap custom field dan workflow mengikut permission yang ditetapkan.

| Level | Control | Example |
|-------|---------|---------|
| **Module-level** | Enable/disable modul | Tenant disable B-Booking |
| **Feature-level** | Toggle features within module | Disable discount on invoices |
| **Field-level** | Show/hide/edit specific fields | Hide cost_price from sales staff |
| **Role-level** | Different views per role | Owner nampak semua, staff nampak terhad |
| **Record-level** | Row-level security | Sales A hanya nampak record sendiri |

### 43.6 Multi-Language Support

Semua custom labels dan UI elements menyokong multilingual.

| Language | Code | Default |
|----------|------|---------|
| Bahasa Melayu | `ms` | ✅ |
| English | `en` | ✅ |
| 中文 (Chinese) | `zh` | Optional |
| தமிழ் (Tamil) | `ta` | Optional |

```php
// Translation override per tenant
Translation::update([
    'tenant_id' => $tenantId,
    'key' => 'invoices.fields.delivery_date',
    'ms' => 'Tarikh Penghantaran (Khusus)',
    'en' => 'Delivery Date (Special)',
]);
```

---

## BAB 44 — Business Rule Engine (No-Code)

### 44.1 Visi

Tenants boleh define **business rules** tanpa perlu coding. Rules menggunakan bahasa natural yang difahami bisnes, bukan syntax programming.

> **Contoh:** "Jika order melebihi RM500, auto-approve. Jika tidak, require manager approval."

### 44.2 Rule Syntax

```yaml
rule: "free_shipping_for_premium"
when:
  - event: "OrderCreated"
  - condition: "order.total > 500"
  - condition: "customer.plan == 'premium'"
then:
  - action: "shipping.discount_pct = 100"
  - action: "shipping.method = 'express'"
  - notify: "admin@company.com"
  - log: "Free shipping applied to premium customer"
```

### 44.3 Rule Categories

| Category | Use Case | Example Rules |
|----------|----------|---------------|
| **Pricing** | Discount, markup, promo | "10% off untuk pelanggan lama", "Double price weekends" |
| **Approval** | Workflow gates | "Purchase order > RM1,000 perlu owner approve" |
| **Notification** | Alert triggers | "Email supplier bila stok < reorder level" |
| **Validation** | Data quality | "No negative quantities", "Required fields check" |
| **Automation** | Background tasks | "Auto-send invoice reminder 7 days before due" |
| **Inventory** | Stock management | "Auto-create PO when stock < safety level" |
| **HR** | Leave, attendance | "Overtime approved if team lead available" |
| **Finance** | Accounting rules | "Auto-categorize expenses by vendor" |

### 44.4 Rule Storage & Execution

#### Data Model — `business_rules`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT FK | Tenant scope |
| rule_name | VARCHAR(255) | Human-readable name |
| rule_yaml | TEXT | Full rule definition |
| category | ENUM(...) | Pricing, Approval, Notification, dll |
| priority | INT | Execution order (lower = higher priority) |
| is_active | BOOLEAN | Active rule? |
| effective_from | DATETIME | Start date |
| effective_to | DATETIME NULL | Expiry date |
| last_triggered_at | DATETIME NULL | Last execution time |
| trigger_count | INT | Total executions |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### Execution Flow

```
1. Domain event fired (e.g., OrderCreated)
        ↓
2. Rule Engine queries: SELECT * FROM business_rules 
   WHERE category IN ('pricing','automation') AND is_active=true
        ↓
3. Evaluate each rule's WHEN conditions against event data
        ↓
4. For matching rules, execute THEN actions sequentially
        ↓
5. Log execution: {rule_id, event_id, result, duration_ms}
        ↓
6. If action modifies data → wrap in transaction
        ↓
7. If action requires approval → create task
        ↓
8. Return result to calling module
```

### 44.5 Rule Conflict Detection

| Conflict Type | Detection Method | Resolution |
|--------------|------------------|------------|
| **Priority conflict** | Two rules fire on same event with different outcomes | Priority field determines order; alert on close priorities |
| **Logical conflict** | Rule A says X, Rule B says not-X | Static analysis detects contradictions |
| **Circular dependency** | Rule A triggers Rule B triggers Rule A | Max depth limit (10 levels), cycle detection |
| **Performance impact** | Too many rules firing per event | Rule count warning at >50 per event type |

---

## BAB 45 — Report Builder & Dashboard Customization

### 45.1 Custom Report Builder

Tenant boleh create laporan custom tanpa SQL knowledge.

#### Report Builder UI Features

| Feature | Description |
|---------|-------------|
| **Visual Query Builder** | Drag-and-drop tables, columns, filters |
| **SQL Editor** | Advanced users boleh tulis raw SQL |
| **Chart Types** | Bar, line, pie, area, gauge, heatmap, table |
| **Scheduling** | Auto-generate dan email laporan berkala |
| **Export Formats** | PDF, Excel, CSV, image, link share |
| **Filters** | Date range, category, status, custom fields |
| **Grouping** | Group by any column, subtotals, grand totals |
| **Drill-down** | Click chart → see underlying records |

#### Data Model — `custom_reports`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT FK | Tenant scope |
| user_id | BIGINT FK | Creator |
| report_name | VARCHAR(255) | Display name |
| description | TEXT | Purpose/description |
| query_config | JSON | Tables, joins, columns, filters, grouping |
| chart_config | JSON | Chart type, colors, axes, legends |
| schedule_config | JSON | Frequency, recipients, format |
| permissions_json | JSON | Who can view/edit/share |
| is_public | BOOLEAN | Shareable via link? |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### 45.2 Dashboard Widget System

Each tenant gets customizable dashboards per module.

#### Available Widget Types

| Widget | Data Source | Refresh Rate |
|--------|------------|-------------|
| **KPI Cards** | Aggregated metrics | Real-time |
| **Line Chart** | Trend over time (sales, revenue) | Every 5 min |
| **Bar Chart** | Comparison (products, categories) | Every 15 min |
| **Pie/Donut** | Distribution (status, source) | Every hour |
| **Table** | Detailed records (recent orders) | On demand |
| **List** | Quick view (pending tasks) | Real-time |
| **Map** | Geographic data (customers, deliveries) | Every hour |
| **Funnel** | Conversion stages (sales pipeline) | Every 15 min |
| **Progress** | Goal tracking (monthly target vs actual) | Real-time |
| **Alert** | Exception notifications | Real-time |

#### Dashboard Layout

```json
{
  "dashboard": "owner_command_center",
  "widgets": [
    {"type": "kpi_card", "title": "Jualan Hari Ini", "metric": "today_revenue", "position": [0,0]},
    {"type": "kpi_card", "title": "Invois Outstanding", "metric": "outstanding_amount", "position": [1,0]},
    {"type": "line_chart", "title": "Jualan 30 Hari", "chart": "daily_sales", "position": [0,1], "span": [2,1]},
    {"type": "list", "title": "Task Pending Approval", "query": "tasks.where(status='awaiting_approval')", "position": [0,2]},
    {"type": "alert", "title": "Low Stock Alerts", "query": "products.where(stock_qty < min_stock)", "position": [2,2]}
  ]
}
```

### 45.3 Scheduled Reports

| Schedule | Frequency | Typical Use Case |
|----------|-----------|-----------------|
| **Daily** | Setiap hari 8:00 MYT | Sales summary, cash flow |
| **Weekly** | Isnin 9:00 MYT | Weekly performance, inventory status |
| **Monthly** | 1st day 8:00 MYT | Financial statements, KPI review |
| **Quarterly** | First of quarter | Strategic review, board reporting |
| **Custom** | Cron expression | Special requirements |

---

## BAB 46 — Plugin Marketplace & Extension API

### 46.1 Plugin Architecture

Platform menyokong third-party plugins dan custom extensions melalui standardized API.

#### Plugin Lifecycle

```
Developer creates plugin
        ↓
Plugin packaged as .zip (manifest.json + code)
        ↓
Submitted to marketplace (or uploaded directly)
        ↓
Automated security scan (dependency check, vulnerability scan)
        ↓
Manual review (for marketplace listing)
        ↓
Published → Available for installation
        ↓
Tenant installs → Plugin activated in sandbox
        ↓
Tenant configures → Plugin integrated into workflow
        ↓
Updates pushed automatically (with changelog)
```

#### Plugin Manifest (`manifest.json`)

```json
{
  "id": "plugin-payment-stripe-my",
  "name": "Stripe Payment Gateway (Malaysia)",
  "version": "1.0.0",
  "author": "AI BIJAN Partners",
  "description": "Accept payments via Stripe for Malaysian businesses",
  "requires": {
    "ai_bijan_version": ">=4.5.0",
    "modules": ["finance", "invoicing"],
    "php_version": ">=8.2"
  },
  "permissions": [
    "finance.payments.create",
    "finance.payments.read",
    "settings.integration.edit"
  ],
  "hooks": [
    {"event": "InvoicePaid", "handler": "StripeWebhookHandler"},
    {"event": "PaymentFailed", "handler": "StripeErrorHandler"}
  ],
  "routes": ["/plugin/stripe/webhook"],
  "assets": ["/vendor/stripe/js", "/vendor/stripe/css"],
  "settings_schema": "schema.json",
  "is_verified": true,
  "rating": 4.8,
  "installations": 250
}
```

### 46.2 Plugin Hooks (Events)

Plugins boleh hook ke platform events.

| Hook Point | Timing | Example Plugin |
|-----------|--------|---------------|
| **BeforeSave** | Sebelum simpan ke database | Auto-validate, transform data |
| **AfterSave** | Selepas simpan berjaya | Send notification, update cache |
| **BeforeDelete** | Sebelum delete record | Check dependencies, soft-delete |
| **EventReceived** | Bila domain event fired | Process event, trigger actions |
| **ReportGenerated** | Laporan siap generate | Add watermark, convert format |
| **UserLogin** | Pengguna login | Update last_seen, send greeting |
| **ModuleActivated** | Modul baru diaktifkan | Setup defaults, import templates |
| **CustomAction** | Custom button clicked | Execute business logic |

### 46.3 Plugin Categories

| Category | Examples | Target Audience |
|----------|----------|----------------|
| **Payment Gateways** | Stripe, PayPal, Billplz, FPX, Touch 'n Go | Semua bisnes |
| **Shipping/Courier** | J&T, Pos Laju, DHL, Shopee Express | Retail, e-commerce |
| **Accounting** | QuickBooks sync, Xero integration | Enterprise, SME besar |
| **HR/Employment** | EPF calculator, SOCSO portal, MyCareersFuture | Syarikat dengan pekerja |
| **Marketing** | Mailchimp, SendGrid, WhatsApp Business API | Marketing teams |
| **Analytics** | Google Analytics, Hotjar, Mixpanel | Growth-focused businesses |
| **Communication** | Telegram bot, SMS gateway, email marketing | Customer-facing |
| **Industry-Specific** | Salon booking add-on, Clinic management, Fleet tracker | Niche industries |
| **AI/ML** | Sentiment analysis, demand forecasting, chatbot | Advanced users |
| **Security** | IP allowlisting, SSO, audit enhancement | Security-conscious |

### 46.4 Extension API (SDK)

#### PHP Plugin SDK (Laravel Package)

```php
namespace Plugins\StripePayment;

use AiBijan\Plugin\BasePlugin;
use AiBijan\Events\InvoicePaid;

class StripePlugin extends BasePlugin
{
    protected string $pluginId = 'plugin-payment-stripe-my';
    protected array $requiredPermissions = ['finance.payments.*'];
    
    public function register(): void
    {
        // Register routes
        $this->routes(function () {
            Route::post('/webhook/stripe', [WebhookController::class, 'handle']);
        });
        
        // Register hooks
        $this->listen(InvoicePaid::class, PaymentProcessor::class);
        
        // Register settings
        $this->settingsSchema('config/settings.json');
    }
    
    public function boot(): void
    {
        // Initialize Stripe SDK
        Stripe::setApiKey($this->getSetting('secret_key'));
    }
    
    // Hook handler
    public function handleInvoicePaid(InvoicePaid $event): void
    {
        // Process refund if payment was via Stripe
        if ($event->invoice->payment_method === 'stripe') {
            $refund = \Stripe\Refund::create([
                'charge' => $event->invoice->stripe_charge_id,
                'amount' => $event->invoice->refund_amount * 100,
            ]);
            $event->invoice->update(['stripe_refund_id' => $refund->id]);
        }
    }
}
```

#### JavaScript Plugin SDK (Frontend)

```javascript
// Frontend plugin for custom dashboard widget
import { registerWidget, api } from '@aibijan/plugin-sdk';

registerWidget({
  id: 'widget-sales-trend-my',
  name: 'Sales Trend (Enhanced)',
  category: 'analytics',
  props: {
    width: 'full',
    height: 'medium',
  },
  async loadData(props) {
    const response = await api.get('/plugins/my-analytics/sales-trend', {
      params: { period: props.period || '30d' },
    });
    return response.data;
  },
  render(data, props) {
    return `<div class="chart-container">
      ${renderChart(data, { type: 'line', colors: ['#FF6B6B', '#4ECDC4'] })}
    </div>`;
  },
});
```

### 46.5 Marketplace Governance

| Aspect | Policy |
|--------|--------|
| **Submission** | Open to all developers (self-service) |
| **Verification** | Automated scan + manual review for featured listing |
| **Security** | Dependency scanning, permission audit, sandbox testing |
| **Quality** | Rating system (1-5 stars), install count, reviews |
| **Updates** | Plugin authors push updates; tenants choose when to update |
| **Deprecation** | 6-month notice before removing incompatible plugins |
| **Revenue Share** | Free plugins: 0%; Paid plugins: Platform takes 20% commission |
| **Support** | Plugin author responsible for support; platform provides documentation |

---

## BAB 47 — Future AI Improvements Roadmap

### 47.1 Current AI Capabilities (v4.5)

| Capability | Status | Description |
|-----------|--------|-------------|
| **Multi-model Gateway** | ✅ Live | 20+ models via serveras.click |
| **Personal AI Assistants** | ✅ Live | 9 types (Owner, Sales, Finance, dll) |
| **Memory Persistence** | ✅ Designed | BAB 17.7 — profile, conversation, vector store |
| **Context Builder** | ✅ Designed | Load profile + history + docs per request |
| **Business Understanding** | ✅ Designed | 10-stage discovery onboarding |
| **Proactive Alerts** | ✅ Designed | AI monitors and alerts on issues |
| **Media Studio** | ✅ Planned | Image/video generation |
| **Token Wallet** | 🔨 Building | Billing separate from subscription |

### 47.2 Near-Term AI Enhancements (v5.0 — v5.5)

#### Phase 1: AI Autonomy Expansion (Months 1-6)

| Feature | Description | Priority |
|---------|-------------|----------|
| **Self-Correction Loop** | AI learns from user corrections — jika user edit AI-generated invoice, AI belajar pattern | HIGH |
| **Cross-Module Reasoning** | AI fikirkan across modules — contoh: low stock → suggest reorder → estimate cost → check cash flow | HIGH |
| **Natural Language Queries** | "Berapa jualan bulan lepas yang belum bayar?" → auto-generate SQL/query → show results | HIGH |
| **AI Meeting Assistant** | Join Zoom/Teams meetings, take notes, assign tasks, update CRM | MEDIUM |
| **Document Intelligence** | Upload invoice/receipt → auto-extract data → match with existing records | MEDIUM |

#### Phase 2: Predictive AI (Months 6-12)

| Feature | Description | Priority |
|---------|-------------|----------|
| **Demand Forecasting** | Predict sales based on historical data, seasonality, trends | HIGH |
| **Cash Flow Prediction** | Forecast 30/60/90 days ahead — identify shortfalls early | HIGH |
| **Churn Prediction** | Identify customers likely to stop buying → proactive retention | MEDIUM |
| **Dynamic Pricing** | Suggest optimal pricing based on demand, competition, costs | MEDIUM |
| **Anomaly Detection** | Flag unusual transactions, patterns, or behavior automatically | HIGH |

#### Phase 3: Generative AI (Months 12-18)

| Feature | Description | Priority |
|---------|-------------|----------|
| **Content Generation** | Auto-create product descriptions, marketing copy, social media posts | HIGH |
| **Email Drafting** | AI drafts customer emails (follow-up, negotiation, apology) | HIGH |
| **Report Generation** | Natural language → full reports with charts and insights | MEDIUM |
| **Code Generation** | AI helps build custom plugins/extensions via chat interface | LOW |
| **Voice Interface** | Speak commands → AI executes (hands-free operation) | LOW |

### 47.3 Medium-Term AI Evolution (v6.0 — v7.0)

| Feature | Timeline | Description |
|---------|----------|-------------|
| **Multi-Agent Collaboration** | v6.0 | AI agents talk to each other — CFO agent talks to COO agent to optimize operations |
| **Industry-Specific Models** | v6.0 | Fine-tuned models per industry (clinic, transport, retail, F&B) |
| **Real-Time Translation** | v6.5 | Chat in BM → AI responds in EN/ZH → real-time for multilingual teams |
| **Computer Vision** | v6.5 | Scan products via camera, verify documents, read receipts |
| **RPA Integration** | v7.0 | Robotic process automation — AI fills forms on legacy systems |
| **Predictive Maintenance** | v7.0 | IoT sensor data → predict equipment failure before it happens |

### 47.4 Long-Term AI Vision (v8.0+)

| Vision | Description | Feasibility |
|--------|-------------|-------------|
| **Autonomous Business Operation** | AI runs entire business with human oversight — handles ordering, pricing, hiring suggestions, investment advice | 3-5 years |
| **Swarm Intelligence** | Anonymized cross-tenant insights — "Your margin is below industry average for your sector" | 2-4 years |
| **Emotional AI** | Detect customer sentiment from text/voice → adjust response strategy | 2-3 years |
| **Quantum Optimization** | Solve complex logistics, scheduling, pricing problems beyond classical computing | 5+ years |
| **AGI Integration** | If AGI achieved, integrate safely with human-in-the-loop governance | 10+ years |

### 47.5 AI Ethics & Governance Framework

| Principle | Implementation | Enforcement |
|-----------|---------------|-------------|
| **Transparency** | AI always identifies itself as AI, not human | UI badge, disclosure in communications |
| **Human Oversight** | Risky actions require human approval | Approval gates in BAB 17.5 |
| **Bias Mitigation** | Regular audits of AI decisions for fairness | Quarterly bias assessment |
| **Data Privacy** | Tenant data never used without consent | PDPA compliance, opt-in training |
| **Explainability** | AI explains reasoning behind recommendations | "Why this suggestion?" button |
| **Accountability** | Clear audit trail of all AI actions | BAB 9 Audit Trail |
| **Safety** | AI cannot perform destructive actions alone | Tool permission restrictions |
| **Alignment** | AI goals aligned with tenant business objectives | Business Understanding Engine |

### 47.6 Model Selection Strategy

As AI models evolve rapidly, platform must adapt:

| Consideration | Strategy |
|--------------|----------|
| **Model Performance** | Benchmark quarterly, switch when better model available |
| **Cost Optimization** | Use smaller models for simple tasks, larger for complex reasoning |
| **Latency Requirements** | Fast models for real-time chat, slower for batch processing |
| **Language Support** | Choose best model per language (BM, EN, ZH, TA) |
| **Data Sovereignty** | Keep certain models within Malaysia/regional datacenters |
| **Vendor Diversity** | Never depend on single AI provider — abstraction layer essential |
| **Open Source Fallback** | Maintain capability to use self-hosted open-source models |
| **Fine-Tuning Pipeline** | Collect anonymized improvement data for future model training |

### 47.7 AI Development Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| **AI Task Success Rate** | > 85% | Tasks completed without human intervention |
| **User Acceptance Rate** | > 70% | AI suggestions accepted vs rejected |
| **Time Saved per User** | > 2 hours/day | Estimated from usage analytics |
| **AI Error Rate** | < 5% | Incorrect outputs requiring correction |
| **Token Efficiency** | Decreasing trend | Tokens per successful task over time |
| **Customer Satisfaction (AI)** | > 4.0/5.0 | Post-interaction surveys |
| **Model Switch Frequency** | Low (stable) | How often tenants change models |
| **Custom Prompt Adoption** | > 40% | Tenants who customize system prompts |

---

# PART IX — COLLECTIVE INTELLIGENCE & PLATFORM EVOLUTION

## BAB 48 — Master Blueprint Summary & Quick Reference

### 48.1 Complete System Catalog

| # | System | Status | Priority | Key Bab |
|---|--------|--------|----------|---------|
| 0 | Platform Foundation | ✅ Siap | P0 | BAB 2-11 |
| 1 | B-Auth & Security | ✅ Siap | P0 | BAB 3 |
| 2 | Multi-Tenant Core | ✅ Siap | P0 | BAB 4 |
| 3 | RBAC & Permissions | ✅ Siap | P0 | BAB 5 |
| 4 | Subscription & Billing | 🔨 Building | P0 | BAB 6 |
| 5 | Notifications | ✅ Siap | P1 | BAB 7 |
| 6 | File Storage | ✅ Siap | P1 | BAB 8 |
| 7 | Audit Trail | ✅ Siap | P0 | BAB 9 |
| 8 | API & Integrations | ✅ Siap | P0 | BAB 10 |
| 9 | Localization | ✅ Siap | P1 | BAB 11 |
| 10 | B-Virtual Office | ⏳ Next | P1 | BAB 12 |
| 11 | B-HRMS | 🔨 Partial | P1 | BAB 12 |
| 12 | B-Sign & Docs | ⏸️ Later | P2 | BAB 12 |
| 13 | B-Projects | ⏸️ Later | P2 | BAB 12 |
| 14 | B-Assets | ⏸️ Later | P2 | BAB 12 |
| 15 | B-POS | 🔨 Building | P1 | BAB 12 |
| 16 | B-Site | 📋 Planned Q1 | P0 | BAB 12, BAB 53 |
| 17 | B-Link | ✅ Siap | P2 | BAB 12 |
| 18 | B-Marketplace | ⏸️ Later | P2 | BAB 12 |
| 19 | B-Affiliate | ⏸️ Later | P2 | BAB 12 |
| 20 | B-Loyalty | ✅ Siap | P2 | BAB 12 |
| 21 | B-Omnichannel | ✅ Siap | P2 | BAB 12 |
| 22 | B-Booking | ⏸️ Later | P1 | BAB 12 |
| 23 | B-Marketing Enhanced | 📋 Planned Q1 | P0 | BAB 12, BAB 53 |
| 24 | B-Helpdesk / Service Desk | 📋 Planned Q2 | P1 | BAB 12, BAB 55 |
| 25 | COGS Engine | ⏸️ Later | P1 | BAB 13 |
| 26 | Inventory States | ⏸️ Later | P1 | BAB 13 |
| 27 | BOM & Manufacturing | ⏸️ Later | P3 | BAB 13 |
| 28 | Finance & Accounting | ⏸️ Later | P0 | BAB 14 |
| 29 | Invoicing & e-Invois | ⏸️ Later | P0 | BAB 14 |
| 30 | Industry Packs | ✅ Designed | P1 | BAB 15 |
| 31 | AI Agents (11 types) | ⏳ Next | P0 | BAB 16 |
| 32 | Personal AI (9 types) | ⏳ Next | P0 | BAB 17 |
| 33 | AI Memory & Independence | ✅ Designed | P0 | BAB 17.7 |
| 34 | Business Understanding | ✅ Designed | P0 | BAB 18 |
| 35 | Autonomous Growth | ✅ Designed | P1 | BAB 19 |
| 36 | B-Boardroom | ⏳ Next | P1 | BAB 20 |
| 37 | AI Gateway (serveras.click) | 🔨 Building | P0 | BAB 21 |
| 38 | AI Token Wallet | 🔨 Building | P0 | BAB 22 |
| 39 | AI Media Studio | ✅ Planned | P2 | BAB 23 |
| 40 | UI/UX Design System | ✅ Defined | P0 | BAB 24 |
| 41 | Domain Events & Outbox | ✅ Designed | P0 | BAB 25 |
| 42 | Database Design | ✅ Designed | P0 | BAB 26 |
| 43 | B-Hub Integrations | ⏸️ Later | P1 | BAB 27 |
| 44 | B-Flow Automation | ⏸️ Later | P1 | BAB 28 |
| 45 | B-Scout/Intel/Compliance | ⏸️ Later | P2 | BAB 29 |
| 46 | Ads Manager | ⏸️ Later | P2 | BAB 30 |
| 47 | Payment Gateways | ✅ Designed | P1 | BAB 31 |
| 48 | Custom Fields Engine | ❌ New | P1 | BAB 43 |
| 49 | Workflow Customization | ❌ New | P1 | BAB 43 |
| 50 | Form Layout Builder | ❌ New | P2 | BAB 43 |
| 51 | Business Rule Engine | ❌ New | P1 | BAB 44 |
| 52 | Custom Report Builder | ❌ New | P2 | BAB 45 |
| 53 | Dashboard Widgets | ❌ New | P2 | BAB 45 |
| 54 | Plugin Marketplace | ❌ New | P2 | BAB 46 |
| 55 | Extension API/SDK | ❌ New | P2 | BAB 46 |
| 56 | Disaster Recovery | ❌ New | P0 | BAB 36 |
| 57 | Performance Engineering | ❌ New | P1 | BAB 37 |
| 58 | Data Migration | ❌ New | P1 | BAB 38 |
| 59 | Security Operations | ❌ New | P0 | BAB 39 |
| 60 | CI/CD Pipeline | ❌ New | P1 | BAB 40 |
| 61 | Cost Optimization | ❌ New | P2 | BAB 41 |
| 62 | Customer Training | ❌ New | P1 | BAB 42 |
| 63 | AI Future Roadmap | ❌ New | Ongoing | BAB 47 |
| 64 | Collective Intelligence | ✅ Designed | P1 | BAB 49 |
| 65 | Global Expansion Strategy | ✅ Designed | P0 | BAB 50 |
| 66 | AI Leadership & Proprietary Models | ✅ Designed | P0 | BAB 51 |
| 67 | Ecosystem & Platform Business | ✅ Designed | P1 | BAB 52 |
| 68 | Commerce & Marketing Enhancements | 📋 Planned Q1 | P0 | BAB 53 |
| 69 | CRM & Sales Enablement | 📋 Planned Q1 | P0 | BAB 54 |
| 70 | Service Desk & Quote Builder | 📋 Planned Q2 | P1 | BAB 55 |
| 71 | Go-to-Market Strategy | 📋 Planned | P0 | BAB 56 |
| 72 | AI Live Interaction | ✅ Designed | P1 | BAB 56A |
| 73 | Mobile Applications | ✅ Designed | P1 | BAB 56B |
| 74 | CCTV Surveillance & AI Security | ✅ Designed | P1 | BAB 56C |

### 48.2 Technology Stack Summary

| Layer | Technology | Purpose |
|-------|-----------|---------|
| Backend | Laravel 11+, PHP 8.3+ | Modular monolith, REST API |
| Admin | Filament PHP | HQ, Super Admin, White-label panels |
| Frontend | Next.js 15 + React 19 + Tailwind CSS | All user-facing screens |
| Mobile | Flutter | Merchant/customer apps |
| Database | MySQL 8.x / MariaDB 10.6+ | Transactional data |
| Cache/Queue | Redis + Laravel Horizon | Background jobs, caching |
| Realtime | Laravel Reverb | Chat, notifications, status |
| Search | Meilisearch | Product/customer search |
| Object Storage | Cloudflare R2 / AWS S3 | Files, images, documents |
| Vector Store | Qdrant | AI memory, semantic search |
| AI Gateway | serveras.click | Multi-model AI access |
| Monitoring | Sentry + Laravel Pulse | Error tracking, performance |
| CDN | Cloudflare | Global delivery, DDoS protection |
| Email | SMTP / Transactional API | Notifications, invoices |
| Analytics | MySQL → ClickHouse | Reporting, business intelligence |

### 48.3 Implementation Priority Matrix

| Priority | Systems | Timeline | Impact |
|----------|---------|----------|--------|
| **P0 — Critical** | Platform, Auth, DB, API, Audit, AI Gateway, Finance, Invoicing, AI Memory | Months 1-6 | Platform unusable without these |
| **P1 — High** | HRMS, POS, Loyalty, Omnichannel, Marketing, Booking, Industry Packs, DR, Security, CI/CD | Months 3-12 | Core business operations |
| **P2 — Medium** | Projects, Assets, Site, Marketplace, Affiliate, Helpdesk, Custom Fields, Reports, Plugins | Months 6-18 | Competitive differentiation |
| **P3 — Lower** | Manufacturing, Advanced Analytics, Voice AI, Computer Vision, RPA | Months 12-36 | Future capabilities |

### 48.4 Key Success Factors

1. **Tenant isolation is non-negotiable** — no cross-tenant data leakage
2. **AI has limited autonomy** — human approval for risky actions
3. **Configuration before customization** — keep platform maintainable
4. **Modular activation** — tenants only pay for what they use
5. **AI memory persists independently** — model swaps don't lose knowledge
6. **Malaysia-first compliance** — e-Invois, SST, EPF/SOCSO, PDPA
7. **White-label flexibility** — resellers can brand everything as their own
8. **Extensible architecture** — plugins, custom fields, rule engine
9. **Observability first** — monitor everything, alert proactively
10. **Continuous learning** — AI improves from tenant interactions (opt-in)
11. **Collective intelligence** — every tenant makes the platform smarter for all
12. **Multilingual by default** — native BM, EN, CN, TA, ID, AR support
13. **Voice-first interface** — natural language commands for hands-free operation
14. **Proprietary Business LLM** — fine-tuned on real business data, not generic
15. **Platform ecosystem** — marketplace + SDK + certification = infinite scale
16. **Product-led growth** — build so good users become evangelists
17. **Emerging markets first** — 5B people underserved by Western tools

### 48.5 Document Statistics

| Metric | Value |
|--------|-------|
| **Total Chapters (BABS)** | 63 (BAB 0–60 plus 56A, 56B, 56C) |
| **Total Parts** | 10 (Part I–X) |
| **Total Lines** | ~8,620+ |
| **Total Tables** | 175+ |
| **Total Data Models** | 100+ |
| **Total AI Agents** | 20 (11 domain + 9 personal) + 6 specialized agent types |
| **Total B-Modules** | 18 |
| **Total Industry Packs** | 19 |
| **Total AI Models** | 20 (via serveras.click) + 1 proprietary Business LLM |
| **Implementation Phases** | 6 (Fasa 0–5 + Commerce Enhancement) |
| **Customization Layers** | 6 (fields, forms, workflows, rules, reports, plugins) |
| **Future AI Stages** | 5 (current → near → medium → long term → ecosystem) |
| **Global Expansion Markets** | 6 priority markets |
| **Revenue Streams** | 8 (subscription, transaction, marketplace, advertising, data, insurance, remittance, hardware commission) |
| **Languages Supported** | 8+ native languages |
| **Commerce Enhancements** | 5 (B-Site, B-Marketing Enhanced, Deep CRM, Service Desk, Quote Builder) |
| **Target Revenue Growth** | 16x (RM208K → RM3.5M/month with 66K tenants) |
| **New Features (v7)** | AI Live Interaction (56A), Native Mobile Apps (56B), CCTV Surveillance & AI Security (56C) |

---
## BAB 49 — Collective Intelligence & Platform Learning

### 49.1 Visi

AI BIJAN bukan sekadar platform SaaS — ia adalah **organisma yang belajar**. Setiap kali tenant customize sistem mereka (tambah custom field, ubah workflow, define business rule), platform mendapat **pelajaran berharga** yang boleh improve pengalaman SEMUA tenant dalam industri yang sama.

> **Prinsip Utama:** Satu tenant = satu data. Seribu tenant = seribu pelajaran. Platform makin pintar dari SEMUA interaksi, bukan hanya satu tenant.

#### The Network Effect Loop

```
Tenant A customize → Anonymize pattern → Detect trend → 
Approve as standard → Push to similar tenants → 
More adoption → Better AI suggestions → More tenants happy → 
More tenants join → More customizations → Better learning
```

Ini **virtuous cycle** yang menjadikan AI BIJAN semakin competitive dari masa ke masa — competitor tak boleh copy kerana mereka tak ada network of tenants untuk learn from.

### 49.2 Anonymization Pipeline

Sebelum mana-mana customization data dihantar ke collective intelligence engine, ia melalui **multi-layer anonymization**.

#### Layer 1: Identity Stripping

| Data Removed | Method | Example |
|-------------|--------|---------|
| Tenant ID | Hash + salt | `tenant_12345` → `t_x7k9m2p` |
| Tenant name | Delete | "Kedai Kuih Mak Haliah" → removed |
| Domain/brand | Delete | "makhaliah.com" → removed |
| Contact info | Delete | Email, phone, address → removed |
| User IDs | Re-map | `user_789` → `u_random` |
| Custom values | Strip | Actual invoice amounts, customer names → removed |

#### Layer 2: Pattern Extraction

Yang DIKEKAL selepas anonymization:

| Metadata Kept | Type | Purpose |
|---------------|------|---------|
| Module name | String | `finance`, `inventory`, `hrms` |
| Table name | String | `invoices`, `products`, `employees` |
| Field name (slug) | String | `delivery_date`, `batch_number` |
| Field label (translated) | String | "Tarikh Penghantaran", "Nombor Batch" |
| Field type | Enum | `date`, `text`, `select`, `number` |
| Validation rules | JSON | `{required:true, pattern:"^RM"}` |
| Industry code | String | MSIC code atau industry pack |
| Creation timestamp | DateTime | When was this field added? |
| Modification count | Integer | How many times edited? |

Yang TIDAK DIKEKAL:

| Data Excluded | Reason |
|---------------|--------|
| Custom field VALUES | Contains actual business data |
| Tenant subscription tier | Irrelevant to pattern learning |
| Tenant location (exact) | Only state-level if needed |
| Revenue/billing data | Privacy violation |
| Customer/employee PII | PDPA breach |
| Invoice/order amounts | Business-sensitive |
| Communication content | Confidentiality |

#### Layer 3: Aggregation Threshold

Pattern hanya muncul jika memenuhi **minimum threshold**:

| Threshold | Value | Rationale |
|-----------|-------|-----------|
| Minimum tenants | 10 | Prevent single-tenant identification |
| Minimum adoption rate | 5% of industry | Only meaningful patterns |
| Time window | 90 days | Recent trends only |
| Stability period | 30 days | Not fleeting fads |
| Cross-validation | 2+ modules | Pattern confirmed across contexts |

```sql
-- Example: Query for trending custom fields in food_beverage industry
SELECT 
    module_name,
    field_name,
    field_type,
    COUNT(DISTINCT tenant_hash) AS adoption_count,
    AVG(modification_count) AS avg_edits,
    MAX(created_at) AS last_seen
FROM tenant_customization_analytics
WHERE industry_code = 'food_beverage'
  AND created_at >= NOW() - INTERVAL 90 DAY
GROUP BY module_name, field_name, field_type
HAVING adoption_count >= 10
ORDER BY adoption_count DESC;
```

### 49.3 Pattern Recognition Engine

#### Detection Algorithms

| Algorithm | Purpose | Output |
|-----------|---------|--------|
| **Frequency analysis** | Most commonly added fields per module/industry | Top-N field recommendations |
| **Co-occurrence detection** | Fields that are often added together | Grouped field sets (e.g., delivery_date + tracking_code) |
| **Trend acceleration** | Fields growing in adoption rapidly | Rising stars (new must-have features) |
| **Seasonal patterns** | Fields added cyclically | Seasonal recommendations (e.g., promo_fields before Hari Raya) |
| **Industry divergence** | Fields unique to one industry vs common across all | Industry-specific vs universal patterns |
| **Abandonment tracking** | Fields added then deleted quickly | False positives (features that don't deliver value) |

#### Pattern Categories

| Category | Description | Example |
|----------|-------------|---------|
| **Universal** | Used by >50% of ALL tenants | "notes" field on invoices |
| **Industry-standard** | Used by >30% in specific industry | "delivery_date" in food_beverage |
| **Emerging** | Rapidly growing (<90 days) | "tiktok_shop_order_id" (+200% growth) |
| **Niche** | Used by <10% but high satisfaction | "halal_cert_number" in cosmetics |
| **Declining** | Added then removed frequently | "voucher_code" (native feature replaced it) |

#### Pattern Confidence Score

Each detected pattern gets a confidence score (0-100):

```
confidence = (adoption_weight × 40) + (stability_weight × 25) + 
             (satisfaction_weight × 20) + (cross_module_weight × 15)

Where:
- adoption_weight: min(adoption_rate / 0.3, 1.0) × 100
- stability_weight: 100 if active > 30 days, 50 if 7-30 days, 0 if < 7 days
- satisfaction_weight: based on deletion rate (low deletion = high satisfaction)
- cross_module_weight: bonus if pattern appears in 2+ related modules
```

### 49.4 Master Admin Dashboard — Review & Approve

#### Dashboard Views

| View | Content | Actions |
|------|---------|---------|
| **Trending Patterns** | Top 20 rising customizations across all industries | Approve, reject, request more data |
| **Industry Breakdown** | Patterns filtered by industry code | Drill into specific industry |
| **New Signals** | Emerging patterns below threshold but accelerating | Monitor, set alert |
| **Approved Standards** | All approved industry-standard patterns | Edit, unpublish, archive |
| **Adoption Tracker** | Track how approved standards spread to new tenants | Analyze effectiveness |
| **Quality Report** | Patterns with high deletion rate (poor quality) | Investigate root cause |

#### Approval Workflow

```
Pattern Detected (confidence ≥ 70)
        ↓
Auto-queued for review
        ↓
Master Admin receives notification
        ↓
Admin reviews:
  - Adoption rate (is it real or anomaly?)
  - Industry fit (makes sense for this sector?)
  - Quality signal (are people deleting these fields?)
  - Conflict check (doesn't break existing native features)
        ↓
Decision:
  ├─ ✅ APPROVE → Mark as "Industry Standard"
  ├─ ⏳ MONITOR → Wait for more data (lower confidence)
  ├─ ❌ REJECT → Log reason (false positive, privacy concern)
  └─ 🔧 MODIFY → Adjust thresholds before approval
        ↓
Approved patterns pushed to recommendation engine
```

#### Approval Criteria Checklist

| Check | Requirement | Weight |
|-------|-------------|--------|
| **Minimum adoption** | ≥ 10 tenants OR ≥ 5% of industry | Mandatory |
| **Stability** | Active for ≥ 30 days without deletion | High |
| **Confidence score** | ≥ 70 points | Mandatory |
| **No conflict** | Doesn't duplicate native feature | High |
| **Privacy clean** | No PII or sensitive data in metadata | Mandatory |
| **Cross-validation** | Confirmed by 2+ signals (e.g., co-occurring fields) | Medium |

### 49.5 Industry Standard Templates — Push to Tenants

#### Auto-Suggestion on Onboarding

Bila tenant baru register dengan industry pack tertentu, AI auto-suggest standardized custom fields:

```
📋 Recommended Setup for Kedai Makanan (Food & Beverage)

Based on 47 kedai makanan yang lain menggunakan AI BIJAN:

✅ Tarikh Penghantaran (date) — pada Invoicing
   Used by 67% of food tenants for delivery tracking
   
✅ Nota Pengemasan (textarea) — pada Orders  
   Used by 52% for packing instructions
   
✅ Kod Tracking (text) — pada Products
   Used by 43% for batch/lot tracking
   
✅ Terma Pemulangan (select) — pada Checkout
   Options: "7 hari", "Tiada pemulangan", "Pertimbangan个案"
   Used by 38% for return policy

✅ Nombor Halal (text) — pada Products
   Format: "2000000000000XXX"
   Used by 31% for compliance tracking

[Accept All] [Customize Selection] [Skip for Now]
```

#### In-App Nudges

Tenants yang belum adopt standard patterns dapat notifikasi berkala:

```
💡 Trending di Industri Anda

"45 kedai makanan telah menambah 'Tarikh Penghantaran' pada invois 
sejak bulan lepas. Ini membantu tracking penghantaran dengan lebih efisien."

[Try This Feature] [Not Now] [Tell Me More]
```

#### Monthly Industry Digest

Email/report kepada setiap tenant tentang trend dalam industri mereka:

```
📊 Laporan Trend Bulan Ini — Food & Beverage

Top 5 Customizations Bulan Lepas:
1. 🚀 "Delivery_window" — +12 new tenants (emerging!)
2. 📈 "Delivery_date" — still #1 at 67% adoption
3. 🆕 "Allergen_info" — new pattern, 8 tenants so far
4. ➖ "Voucher_min_spend" — declining (native feature now available)
5. ➖ "Manual_discount" — declining (replaced by B-Marketing)

Your Status: You're using 3/5 recommended fields.
Missing: Delivery_window, Allergen_info

→ Set up missing fields in 2 clicks
```

### 49.6 AI Training Data Collection

#### Improving Business Understanding Engine

Customization patterns menjadi **training data** untuk AI Business Understanding Engine:

```
Training Dataset Construction:

Input: Anonymized customization patterns from 1,000 tenants
Output: Improved AI recommendations for new tenants

Example Learning:
├── Pattern: "delivery_date" + "tracking_code" + "delivery_status"
│   ├── Co-occurrence: 78% (often added together)
│   ├── Industry: food_beverage (92% of occurrences)
│   ├── Timing: usually added within 7 days of each other
│   └── AI learns: Food businesses NEED delivery tracking trio
│
├── Pattern: "loyalty_points" + "points_balance" + "points_history"
│   ├── Co-occurrence: 65%
│   ├── Industry: retail_general (58%), food_beverage (34%)
│   └── AI learns: Retail businesses want customer loyalty tracking
│
└── Pattern: "warranty_expiry" + "warranty_terms" + "service_history"
    ├── Co-occurrence: 71%
    ├── Industry: electronics (82%), automotive (45%)
    └── AI learns: Technical products need warranty/service management
```

#### Fine-Tuning Pipeline

```
Data Collection (Ongoing):
├── Custom field patterns (anonymized)
├── Workflow definitions (anonymized)
├── Business rules (anonymized)
├── Form layout configurations (anonymized)
└── Report templates (anonymized)

Data Processing (Weekly):
├── Aggregate by industry, module, company_size
├── Calculate confidence scores
├── Detect new patterns and trends
└── Generate training dataset

Model Update (Monthly):
├── Retrain Business Understanding model
├── Validate improved accuracy on holdout set
├── A/B test new model against old
└── Deploy if improvement > 5%

Feedback Loop (Continuous):
├── Track suggestion acceptance rate
├── Track post-adoption retention (do they keep suggested fields?)
├── Measure time-to-value reduction
└── Feed results back into training data
```

#### AI Model Performance Metrics

| Metric | Before Collective Learning | Target After |
|--------|---------------------------|-------------|
| **Suggestion accuracy** | ~60% (based on industry template only) | 85%+ (learned from real tenant behavior) |
| **Time to first value** | 2-3 days (manual setup) | < 6 hours (auto-configured) |
| **Feature adoption rate** | 40% within first month | 70%+ within first week |
| **Onboarding completion** | 50% finish setup | 80%+ finish setup |
| **Tenant satisfaction (setup)** | 3.5/5 | 4.5/5 |

### 49.7 Product Roadmap Intelligence

Master admin boleh gunakan aggregation data untuk buat **data-driven product decisions**:

#### Feature Request Signal Strength

| Signal | Weak | Medium | Strong | Killer |
|--------|------|--------|--------|--------|
| **Custom adoption** | < 20 tenants | 20-50 tenants | 50-200 tenants | > 200 tenants |
| **Adoption rate** | < 5% | 5-15% | 15-30% | > 30% |
| **Willingness to pay** | Free trial users | Starter plan | Professional | Enterprise |
| **Support tickets** | < 10/month | 10-30/month | 30-100/month | > 100/month |
| **Churn correlation** | None | Low | Medium | High (people leave without it) |

#### Decision Matrix: Build Native Feature?

| Factor | Weight | Score (1-10) | Weighted |
|--------|--------|-------------|----------|
| Demand strength | 30% | 9 | 2.7 |
| Strategic fit | 25% | 8 | 2.0 |
| Implementation cost | 20% | 6 (medium) | 1.2 |
| Competitive gap | 15% | 7 | 1.05 |
| Revenue impact | 10% | 8 | 0.8 |
| **Total** | | | **7.75 / 10** |

**Decision:** 7.75 > 7.0 threshold → **BUILD IT** 🚀

#### Product Roadmap Output

```
Q1 2027 Feature Prioritization (based on collective intelligence):

Priority 1: Delivery Management Module
  - Signal: 200+ tenants added delivery-related fields
  - Industry coverage: food_beverage, retail, pharmacy
  - Churn risk: HIGH (delivery businesses can't operate well)
  - Revenue potential: Can upsell to Professional tier
  
Priority 2: Batch/Lot Tracking
  - Signal: 150 tenants added batch_number, expiry_date
  - Industry coverage: food_beverage, pharmaceutical, cosmetics
  - Compliance driver: Halal certification needs traceability
  - Revenue potential: Enterprise tier feature
  
Priority 3: Service Duration Presets
  - Signal: 120 tenants added service_duration with presets
  - Industry coverage: salon, clinic, automotive repair
  - Competitive gap: No native booking duration templates
  - Revenue potential: Industry pack upsell
```

### 49.8 Privacy & Consent Framework

#### PDPA Compliance Architecture

| Requirement | Implementation | Verification |
|-------------|---------------|--------------|
| **Lawful basis** | Consent + legitimate interest | Legal documentation |
| **Informed consent** | Clear explanation during onboarding | Opt-in checkbox |
| **Purpose limitation** | Only for platform improvement | Data processing agreement |
| **Data minimization** | Only metadata, never raw data | Automated filtering |
| **Accuracy** | Aggregation prevents individual identification | Statistical validation |
| **Retention** | Patterns kept 2 years max | Auto-purge schedule |
| **Security** | Encryption, access control | Annual audit |
| **Accountability** | Documented processing activities | Compliance officer |

#### Consent Flow

```
During Tenant Onboarding:

┌─────────────────────────────────────────────┐
│  🤝 Help Us Improve AI BIJAN for Everyone   │
│                                             │
│  Would you like to help improve the platform│
│  for other businesses in your industry?     │
│                                             │
│  When you enable this:                      │
│  ✓ Your CUSTOMIZATION PATTERNS will be      │
│    shared anonymously (field types, names)  │
│  ✓ Your INDUSTRY TRENDS will contribute to  │
│    collective learning                      │
│  ✓ YOUR BUSINESS DATA stays PRIVATE         │
│    (amounts, customers, employees never     │
│    shared)                                  │
│                                             │
│  Benefits:                                  │
│  ✓ Get EARLY ACCESS to new features         │
│    tested by other tenants                  │
│  ✓ Receive INDUSTRY INSIGHTS about trends   │
│    in your sector                           │
│  ✓ Contribute to BETTER AI RECOMMENDATIONS  │
│    for your business                        │
│                                             │
│  [ Enable Collective Learning ] ← Default: OFF │
│  [ Skip for Now ]                           │
│                                             │
│  Learn more about our privacy practices     │
└─────────────────────────────────────────────┘
```

#### Tenant Rights

| Right | Mechanism |
|-------|-----------|
| **Opt-in** | Toggle in settings during onboarding |
| **Opt-out anytime** | Settings → Privacy → "Stop sharing patterns" |
| **Data access** | Download what patterns exist from their tenant hash |
| **Data deletion** | Remove all historical pattern data (keep aggregated counts) |
| **Transparency** | Dashboard showing what patterns contributed |
| **Portability** | Export pattern contribution history |

#### Aggregation Safeguards

| Safeguard | Implementation |
|-----------|---------------|
| **k-anonymity** | Minimum k=10 (at least 10 tenants per pattern) |
| **differential privacy** | Add statistical noise to counts (< 5% error margin) |
| **suppression** | Hide patterns from small industries (< 5 tenants) |
| **generalization** | Broaden category if too specific (e.g., "kedai kuih" → "food_beverage") |
| **time decay** | Older contributions weighted less (recent data more valuable) |

### 49.9 Data Model — Collective Intelligence Tables

#### `tenant_customization_analytics`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_hash | VARCHAR(64) | Anonymized tenant identifier (SHA-256 with salt) |
| industry_code | VARCHAR(50) | MSIC code or industry pack slug |
| module_name | VARCHAR(100) | Modul target (finance, inventory, hrms) |
| table_name | VARCHAR(100) | Table sasaran (invoices, products, employees) |
| field_name | VARCHAR(100) | Slug field name |
| field_label_hash | VARCHAR(64) | Hash of translated label (for grouping similar labels) |
| field_type | ENUM(...) | Data type |
| validation_hash | VARCHAR(64) | Hash of validation rules (pattern matching) |
| adoption_count | INT | Number of tenants using this pattern |
| modification_count | INT | Total edits across tenants |
| deletion_flag | BOOLEAN | Was this field later deleted? |
| first_seen_at | DATETIME | When was this pattern first detected? |
| last_seen_at | DATETIME | Last time observed |
| confidence_score | DECIMAL(3,1) | 0.0-100.0 pattern confidence |
| is_approved | BOOLEAN | Approved as industry standard? |
| approved_at | DATETIME NULL | When approved by admin |
| approved_by | BIGINT FK NULL | Which admin approved |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

**Indexes:**
- `INDEX(industry_code, module_name, adoption_count DESC)` — Trend queries
- `INDEX(field_name, adoption_count DESC)` — Cross-industry patterns
- `INDEX(is_approved, first_seen_at)` — Approved standards feed
- `UNIQUE(tenant_hash, module_name, table_name, field_name)` — Deduplication

#### `industry_standard_templates`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| industry_code | VARCHAR(50) | Target industry |
| module_name | VARCHAR(100) | Target module |
| template_name | VARCHAR(255) | Human-readable name |
| fields_json | JSON | Array of recommended fields with config |
| adoption_target | INT | Target adoption count for "standard" status |
| current_adoption_pct | DECIMAL(5,2) | Current percentage of tenants using |
| is_active | BOOLEAN | Available for suggestion? |
| version | INT | Template version |
| created_from_pattern_id | BIGINT FK NULL | Source analytics record |
| effective_from | DATETIME | When template becomes active |
| expires_at | DATETIME NULL | When template expires (seasonal) |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `pattern_recommendations`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| tenant_id | BIGINT FK | Who received the recommendation |
| pattern_id | BIGINT FK | Which pattern recommended |
| recommendation_type | ENUM('onboarding','nudge','digest') | Context |
| channels_json | JSON | Where shown (in-app, email, SMS) |
| accepted | BOOLEAN | Did tenant accept? |
| accepted_at | DATETIME NULL | When accepted |
| dismissed | BOOLEAN | Did tenant dismiss? |
| dismissed_at | DATETIME NULL | When dismissed |
| dismissal_reason | ENUM('not_interested','already_have','will_later','other') | Why dismissed |
| clicked | BOOLEAN | Did tenant click through? |
| viewed_at | DATETIME NULL | When recommendation was shown |
| expires_at | DATETIME | When recommendation expires |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

**Indexes:**
- `INDEX(tenant_id, recommendation_type, accepted)` — Track acceptance rates
- `INDEX(pattern_id, accepted)` — Pattern effectiveness

#### `collective_learning_metrics`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| metric_key | VARCHAR(100) | What's being measured |
| metric_value | DECIMAL(10,4) | Numeric value |
| period_start | DATE | Measurement period start |
| period_end | DATE | Measurement period end |
| segment | VARCHAR(50) | Industry/size segment |
| baseline_value | DECIMAL(10,4) | Previous period value |
| improvement_pct | DECIMAL(5,2) | Percentage improvement |
| source | ENUM('survey','analytics','a_b_test','manual') | Data source |
| verified_by | BIGINT FK NULL | Who verified the metric |
| notes | TEXT | Context or caveats |
| created_at | TIMESTAMP | |

#### `roadmap_signals`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | Auto increment |
| signal_type | ENUM('custom_field_trend','support_ticket','churn_correlation','feature_request') | Signal origin |
| title | VARCHAR(255) | Brief description |
| description | TEXT | Detailed justification |
| demand_score | INT | 1-10 demand strength |
| strategic_fit | INT | 1-10 alignment with vision |
| implementation_effort | ENUM('small','medium','large','huge') | Estimated effort |
| revenue_impact | ENUM('low','medium','high','transformative') | Expected revenue effect |
| priority_score | DECIMAL(5,2) | Calculated priority (weighted sum) |
| roadmap_quarter | VARCHAR(7) | Target quarter (e.g., "2027-Q1") |
| status | ENUM('signal','validated','planned','in_progress','shipped') | Lifecycle |
| data_points | INT | Number of supporting data points |
| sources_json | JSON | References to supporting data |
| decided_by | BIGINT FK NULL | Who made final decision |
| decided_at | DATETIME NULL | When decided |
| decision_rationale | TEXT | Why this priority |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### 49.10 Governance & Ethics

#### Collective Intelligence Ethics Board

| Principle | Rule | Enforcement |
|-----------|------|-------------|
| **Consent first** | Never share without explicit opt-in | Code-level gate |
| **Never identify** | k-anonymity ≥ 10 always enforced | Automated check |
| **Transparent** | Tenant can see what they contributed | Dashboard visibility |
| **Reversible** | Opt-out removes all historical data | Automated purge |
| **Beneficial** | Contributor gets early access to improvements | Feature gating |
| **Auditable** | All data flows logged and reviewable | Audit trail |
| **Proportional** | Data collection matches benefit provided | Quarterly review |

#### Anti-Manipulation Safeguards

| Threat | Mitigation |
|--------|-----------|
| **Bot tenants inflating patterns** | Real payment verification required |
| **Competitor submitting fake patterns** | Cross-validation across multiple signals |
| **Vendor lock-in via patterns** | All patterns exportable, no proprietary formats |
| **Bias toward large tenants** | Weight by tenant size equally, not absolute count |
| **Echo chamber** | Actively seek patterns from diverse industries |
| **Over-automation** | Human approval required before pushing to tenants |

### 49.11 Future Evolution — Platform as Living Organism

#### Stage 1: Pattern Collection (Current)
- Collect anonymized customization patterns
- Manual review and approval
- Basic recommendations on onboarding

#### Stage 2: Automated Learning (Year 1-2)
- ML model auto-detects patterns with 90%+ accuracy
- Auto-approve low-risk patterns (confidence > 90)
- Real-time nudges based on tenant behavior

#### Stage 3: Predictive Platform (Year 2-3)
- Predict what tenants WILL need before they ask
- Proactively suggest entire workflows, not just fields
- Auto-generate industry-specific templates from patterns

#### Stage 4: Autonomous Optimization (Year 3-5)
- Platform self-tunes configuration per industry
- AI-to-AI communication between tenants' assistants
- Cross-tenant collaboration (opt-in, anonymized)
- Self-healing: detect failed customizations and rollback automatically

#### Stage 5: Ecosystem Intelligence (Year 5+)
- Multi-platform data (with consent) — supply chain insights
- Predictive market forecasting from aggregated business data
- Open-source pattern library for entire SaaS industry
- AI-generated industry reports from collective data

---

# PART X — GLOBAL EXPANSION & MARKET DOMINANCE

## BAB 50 — Global Expansion & Market Strategy

### 50.1 Vision — From Malaysia to World

```
Year 1: Dominate Malaysia (beachhead market)
        ↓
Year 2-3: Southeast Asia + Nigeria + India (emerging markets)
        ↓
Year 4-6: Africa, Middle East, Latin America (global south)
        ↓
Year 7+: Developed markets (compete on AI capability, not price)
        ↓
Year 10+: Every business on earth has an AI Boardroom
```

**Why emerging markets first?**
- Western tools (SAP, Oracle, Microsoft) too expensive, too complex
- 5 BILLION people in emerging markets underserved by enterprise software
- Multilingual needs ignored by competitors
- Mobile-first populations skip desktop ERP entirely
- Payment methods differ (FPX, DuitNow, GoPay, M-Pesa, UPI)

### 50.2 Phase 1 — Dominate Malaysia (Year 1-2)

| Goal | Metric | Strategy | Timeline |
|------|--------|----------|----------|
| **Market penetration** | 500K SMEs (30% of market) | Government partnership, banks, telcos | Months 1-18 |
| **Brand awareness** | #1 recognized among SMEs | PR, success stories, referrals | Months 1-24 |
| **Revenue** | RM50M ARR | Freemium → paid conversion | Months 6-24 |
| **Team** | 100 people | Engineering, sales, support, CS | Months 1-12 |
| **Partnerships** | 3 bank white-labels, 2 telcos | Revenue share model | Months 3-12 |

#### Malaysian Go-to-Market Strategy

| Channel | Approach | Expected CAC | Conversion |
|---------|----------|-------------|------------|
| **Government** | Ministry of SME endorsement, grant requirement | Near zero | High (mandated) |
| **Bank partnerships** | Maybank, CIMB, Public Bank bundle with business accounts | Low (bank absorbs) | Medium |
| **Telco bundles** | TM, Maxis, CelcomDigi bundle with business broadband | Low (telco absorbs) | Medium |
| **Freemium viral** | Free tier → word-of-mouth | Zero (organic) | 5% to paid |
| **Digital agency resellers** | 50 agencies resell as their own product | Medium (rev share) | 15% close rate |
| **Accounting firms** | Recommend to 10K+ client SMEs | Medium (rev share) | 20% close rate |

### 50.3 Phase 2 — Southeast Asia + Emerging Markets (Year 2-4)

| Market | Entry Strategy | Localization | Target Year 4 |
|--------|---------------|-------------|---------------|
| **Indonesia** | Partner Gojek/OVO, local team Jakarta | Bahasa Indonesia, BCA/Midtrans payments | 200K tenants |
| **Nigeria** | Partner Flutterwave/Paystack, Lagos office | English + Hausa, local compliance | 150K tenants |
| **India** | Partner Razorpay/Zoho, Bangalore dev hub | Hindi + Tamil + English, GST compliance | 300K tenants |
| **Brazil** | Partner Mercado Pago, São Paulo office | Portuguese, NF-e compliance | 200K tenants |
| **Vietnam** | Partner MoMo, Ho Chi Minh team | Vietnamese, e-invoice mandate | 100K tenants |
| **Philippines** | Partner GCash, Manila office | Filipino + English, BIR compliance | 100K tenants |

#### Localization Framework

| Layer | What's Localized | Example |
|-------|-----------------|---------|
| **Language** | UI, AI communication, support | Bahasa Indonesia vs Malay (different words) |
| **Payments** | Local gateways, bank transfers | GoPay, OVO, M-Pesa, UPI, Pix |
| **Compliance** | Tax, invoicing, labor law | Indonesia e-Invoice, India GST, Brazil NF-e |
| **Calendar** | Holidays, fiscal year | Hari Raya, Imlek, Chinese New Year, Diwali |
| **Currency** | Display, formatting, rounding | IDR (no decimals), KES shilling |
| **Business norms** | Greeting style, formality level | Formal "Anda" vs informal "kamu" |

### 50.4 Phase 3 — Global South Dominance (Year 4-7)

| Region | Strategy | Total Addressable Market |
|--------|----------|-------------------------|
| **Africa** (continent) | French-speaking (Senegal, Ivory Coast), Portuguese (Angola), Swahili (East Africa) | 500M people, 80M SMEs |
| **Middle East** | Arabic-first, Islamic finance, Zakat compliance | 450M people, 30M SMEs |
| **Latin America** | Spanish + Portuguese, local payment methods | 650M people, 50M SMEs |
| **Central Asia** | Russian + local languages, CIS compliance | 150M people, 10M SMEs |
| **Bangladesh/Pakistan** | Urdu/Bengali, mobile money | 400M people, 20M SMEs |

### 50.5 Phase 4 — Developed Markets (Year 7+)

| Market | Positioning | Competitive Angle |
|--------|------------|-------------------|
| **USA** | "The most advanced AI-native business OS" | AI capability > feature parity |
| **Europe** | Privacy-first, GDPR-by-design, multilingual | Data sovereignty + AI |
| **Australia/NZ** | Same as UK/EU but local tax/compliance | Timezone alignment |
| **Japan/Korea** | Ultra-reliable, Japanese/Korean native AI | Quality + localization |

### 50.6 White-Label at Scale

```
Bank X → Rebrand as "BankX Business OS"
         BankX sells to 50K corporate clients
         AI BIJAN gets RM10/tenant/month revenue share
         
Telco Y → Bundle with business broadband
          Telco handles sales, AI BIJAN handles product
          100K tenants via telco channel
          
Govt Z → National SME digitalization program
          Mandatory for grant recipients
          200K instant tenants
```

### 50.7 Funding Strategy

| Round | Timing | Amount | Use Case | Valuation Target |
|-------|--------|--------|----------|-----------------|
| **Pre-Seed** | Month 0-6 | RM5M | Team, MVP, initial tenants | RM50M |
| **Seed** | Month 12-18 | RM30M | Product, GTM, 100K tenants | RM300M |
| **Series A** | Month 24-30 | RM100M | Scale to SEA, 500K tenants | RM1B (unicorn) |
| **Series B** | Month 36-42 | RM300M | Global expansion, 2M tenants | RM5B |
| **Series C** | Year 5 | RM500M | IPO preparation, 5M tenants | RM15B |

---

## BAB 51 — AI Leadership & Proprietary Models

### 51.1 Vision — The Most Advanced Business AI on Earth

```
Current state (reactive AI):
User asks → AI responds

AI BIJAN v6 (proactive AI):
AI monitors → Detects opportunity → Suggests action → Executes (with approval)

AI BIJAN v8 (autonomous AI):
AI predicts → Prepares action → Auto-executes safe actions → Reports results
Human only reviews exceptions and strategic decisions
```

### 51.2 Proprietary Business LLM

Don't just use GPT/Claude — **train your own model**:

#### Training Data Sources

| Source | Volume | Purpose |
|--------|--------|---------|
| **Anonymized conversations** | 1M+ interactions | Business understanding, response quality |
| **Accounting entries** | 100K+ records | Financial reasoning, compliance |
| **HR scenarios** | 50K+ cases | Employment law, EPF/SOCSO logic |
| **Supply chain workflows** | 10K+ examples | Inventory optimization, procurement |
| **Multilingual corpus** | BM, EN, CN, TA, ID, AR | Native multilingual understanding |
| **Industry templates** | 19 packs × 50 fields each | Domain-specific knowledge |
| **Customer feedback** | 100K+ ratings | Response improvement loop |

#### Model Architecture

```
Base Model: Fine-tuned Llama 3.1 / Mistral Large (open-source)
        ↓
Domain Adaptation Layers:
├── Business Understanding Layer (finance, HR, operations)
├── Compliance Layer (Malaysia law → global law)
├── Multilingual Layer (7+ languages, cultural context)
└── Action Planning Layer (what to do, not just what to say)
        ↓
Safety Guardrails:
├── Tenant isolation enforcement
├── Permission checks before any action
├── PII detection and redaction
└── Toxic/hallucination filtering
        ↓
Output: Business-ready responses + structured actions
```

#### Competitive Advantage

| Capability | GPT-4/Claude | AI BIJAN Proprietary Model |
|-----------|-------------|---------------------------|
| General knowledge | Excellent | Good (fine-tuned for business) |
| Malaysian business context | Fair (generic training) | **Excellent** (domain-specific) |
| Multilingual (BM, TA, AR) | Machine translation | **Native understanding** |
| Action execution | Limited (chat-only) | **Full autonomous execution** |
| Compliance knowledge | Generic | **Deep local compliance** |
| Cost per inference | $0.01-$0.03 | **$0.001-$0.005** (self-hosted) |
| Data privacy | Cloud-based, vendor stores data | **Fully contained, tenant-scoped** |

### 51.3 Multilingual-by-Default (Not Translation)

| Language | Code | Native Support | Cultural Context |
|----------|------|---------------|-----------------|
| Bahasa Melayu | `ms` | ✅ Full | Malaysian business culture, formal/informal register |
| English | `en` | ✅ Full | International business, technical terms |
| 中文 (Chinese - Malaysian) | `zh-my` | ✅ Full | Malaysian Chinese business context, simplified |
| தமிழ் (Tamil - Malaysian) | `ta-my` | ✅ Full | Malaysian Tamil community business practices |
| Bahasa Indonesia | `id` | ✅ Full | Different vocabulary from Malay, local compliance |
| العربية (Arabic) | `ar` | ✅ Full | Islamic finance, Muslim business etiquette |
| Swahili | `sw` | ✅ Full | East African trade practices |
| Hindi | `hi` | ✅ Full | South Asian business context |

#### Dynamic Language Switching

```
Customer speaks BM → AI responds in BM
Employee replies in EN → AI translates internally, responds in EN
Owner views dashboard → AI shows ALL THREE languages side-by-side
```

This is **not machine translation** — it's **native multilingual AI** that understands code-switching (campur bahasa).

### 51.4 Voice-First Interface

Many PKS prefer talking over typing:

#### Voice Command Examples

```
Owner: "Bot, berapa sales hari ni?"
AI: "Hari ni ada 23 orders, RM4,567. Naik 12% dari semalam. 
     Top product: Nasi Lemak Special (8 orders)."

Owner: "Hantar invoice ke KL Enterprise"
AI: "Ada 3 pending. Yang RM2,300 atau RM890?"

Owner: "Yang besar la"
AI: "Done. Invoice #INV-2024-089 (RM2,300) dihantar via email 
     dan WhatsApp. Reminder set 7 hari sebelum due."

Owner: "Check stok nasi lemak"
AI: "Stok beras: 50kg. Santan: 10 liter. Cili: 5kg. 
     Cukup untuk 3 hari jika production berterusan."
```

#### Voice UI Features

| Feature | Description | Priority |
|---------|-------------|----------|
| **Wake word** | "Hey Bijan" or custom name | HIGH |
| **Continuous listening** | Natural conversation flow | HIGH |
| **Accent recognition** | Malaysian English, BM, dialects | HIGH |
| **Multi-speaker** | Recognize different voices, apply permissions | MEDIUM |
| **Offline mode** | Lightweight model on device for basic commands | MEDIUM |
| **Text fallback** | Show transcript alongside voice | LOW |

### 51.5 Autonomous Business Agents

#### Current State (Reactive)

```
User: "Create invoice for KL Enterprise, RM1000, due in 30 days"
AI: "Here's the invoice. [Shows draft]"
User: "OK, send it"
AI: "Sent."
```

#### Future State (Proactive)

```
🤖 AI CFO detected patterns:
"Encik, saya nampak:
1. Customer XYZ order 3x lebih banyak bulan ni (+200%)
2. Supplier A bagi diskaun 15% jika order RM10k+
3. Cash flow cukup untuk order sekarang

Saya sudah prepare purchase order ke Supplier A.
Nilai: RM8,500 (diskaun dijangka RM1,500)
Delivery: 3 hari

[Approve Order] [Modify Amount] [Not Now]"
```

#### Agent Autonomy Levels

| Level | Name | What It Can Do | Human Approval Needed |
|-------|------|---------------|----------------------|
| **L0** | Information Only | Answer questions, show data | N/A |
| **L1** | Draft Only | Create drafts, never send/save | Always |
| **L2** | Execute Safe Actions | Send emails, create invoices under threshold | No |
| **L3** | Execute with Notification | Any action, notify owner after | After the fact |
| **L4** | Full Autonomy | All actions within budget/rules | Pre-approved rules only |
| **L5** | Strategic Decisions | Hire, fire, invest, acquire | Always required |

**Default:** L2 for most agents, L1 for financial actions, L0 for personal AI.

### 51.6 Multi-Agent Collaboration

```
┌─────────────────────────────────────────────┐
│           Chief of Staff (Coordinator)      │
│    Routes requests, resolves conflicts      │
├──────────┬──────────┬──────────┬────────────┤
│  CFO AI  │  COO AI  │  CMO AI  │  CSO AI   │
│  Money   │  Ops     │  Growth  │  Data     │
├──────────┼──────────┼──────────┼────────────┤
│  HR AI   │  Sales AI│  Supply  │  Finance  │
│  People  │  Revenue │  Chain   │  Books    │
└──────────┴──────────┴──────────┴────────────┘
```

#### Example: Multi-Agent Workflow

```
Event: Large order received (RM50,000)

Chief of Staff routes to:
├── Sales AI: Verify customer credit history ✓
├── COO AI: Check production capacity ✓
├── Supply Chain AI: Verify material availability ⚠️ (shortage)
├── CFO AI: Assess cash flow impact ✓
└── CSO AI: Update forecast, notify investors (if public)

Result: 
"Order confirmed. Material shortage detected — 
 Supplier can deliver in 5 days. Production starts Day 6. 
 Delivery to customer: Day 15. All within SLA."
```

### 51.7 Edge AI for Low-Bandwidth Areas

```
Cloud AI (full power, latest models)
        ↓ syncs weekly
Lightweight model → Edge device (phone/tablet/raspberry pi)
        ↓
Tenant with poor internet still gets AI suggestions
        ↓
Actions queued locally, synced when connection restored
```

**Use cases:**
- Rural Malaysia (Sabah/Sarawak villages)
- East African farms
- Southeast Asian islands
- Any area with intermittent connectivity

---

## BAB 52 — Ecosystem & Platform Business

### 52.1 Vision — Platform, Not Product

```
Product mindset: We build features, customers use them
Platform mindset: We build infrastructure, others build on top

Result: Infinite features, infinite industries, infinite scale
```

### 52.2 Developer Marketplace

| Metric | Shopify | AI BIJAN Target |
|--------|---------|-----------------|
| **Apps/Plugins** | 8,000+ | 500 by Year 2, 5,000 by Year 5 |
| **Revenue share** | 15-30% | **20%** (generous to attract devs) |
| **Dev tools** | Good | **Best-in-class** (OpenAPI, SDKs, sandbox) |
| **Monetization** | App listings, subscriptions | Full marketplace with reviews, ratings |

#### Marketplace Categories

| Category | Examples | Target Developers |
|----------|----------|------------------|
| **Payment Gateways** | Local wallets, bank APIs | Fintech companies |
| **Shipping/Courier** | Regional logistics providers | Logistics startups |
| **Industry Plugins** | Salon management, clinic EMR | Vertical SaaS builders |
| **Integrations** | QuickBooks, Xero, SAP | Integration platforms |
| **AI Tools** | Sentiment analysis, forecasting | AI/ML startups |
| **Templates** | Industry-specific dashboards | Consultants, agencies |
| **Hardware** | POS terminals, scanners, printers | Hardware manufacturers |
| **Services** | Accounting, legal, consulting | Professional services |

### 52.3 Open API + SDK Strategy

| Component | Technology | Availability |
|-----------|-----------|-------------|
| **REST API** | OpenAPI 3.0 spec | Public (authenticated) |
| **GraphQL** | GraphQL schema | Public (beta) |
| **Webhooks** | Event-driven notifications | Public |
| **TypeScript SDK** | npm package | Public |
| **PHP SDK** | Composer package | Public |
| **Python SDK** | pip package | Public |
| **Flutter SDK** | pub package | Public (mobile apps) |
| **CLI Tool** | Node.js command-line | Public |

#### API Tiers

| Tier | Rate Limit | Features | Price |
|------|-----------|----------|-------|
| **Free** | 100 req/min | Read-only, limited endpoints | Free |
| **Developer** | 1,000 req/min | Full read/write, webhooks | Free (verified dev) |
| **Production** | 10,000 req/min | Priority support, SLA | Included in plan |
| **Enterprise** | Unlimited | Dedicated infra, custom limits | Custom pricing |

### 52.4 Certification Program

```
AI BIJAN Certified Partner Network:

├── Implementation Partners
│   ├── Consultancies who deploy and configure AI BIJAN
│   ├── Revenue share on referred customers (10% recurring)
│   └── Listed in partner directory with specialties
│
├── Plugin Developers
│   ├── Build and sell apps on marketplace
│   ├── Get 80% revenue share (20% platform fee)
│   └── Early access to beta features
│
├── Training Providers
│   ├── Certified trainers deliver AI BIJ Academy courses
│   ├── Revenue share on premium certifications
│   └── Co-branded training materials
│
└── Technology Integrators
    ├── POS hardware, payment devices, IoT sensors
    ├── Pre-certified compatibility
    └── Bundled sales opportunities
```

### 52.5 Transaction Revenue Model

| Revenue Stream | Mechanism | Margin | Scale Potential |
|---------------|-----------|--------|----------------|
| **Payment processing** | 1-2% on transactions through platform | 70-80% | Very high volume |
| **Supply chain matching** | Connect suppliers ↔ buyers, take 0.5% | 90%+ | Network effect |
| **Invoice factoring** | Partner with bank, get financing for tenants | 40-60% split | High demand |
| **Insurance marketplace** | Business insurance, commission per policy | 70-85% | Recurring |
| **Native advertising** | Suppliers advertise to relevant tenants | 80%+ | Scalable |
| **Data insights** | Anonymized industry reports sold to enterprises | 90%+ | Subscription |
| **Cross-border remittance** | Multi-currency tenants, FX spread | 60-75% | Growing |

### 52.6 Community Building

#### AI BIJAN Academy

| Program | Audience | Format | Certification |
|---------|----------|--------|--------------|
| **Digital Usahawan** | Solo entrepreneurs | Free online course | Digital badge |
| **AI Business Leader** | Managers/owners | Premium certification | Professional cert |
| **Partner Certification** | Consultants/developers | Technical training | Partner tier |
| **University Program** | Students | Curriculum integration | Academic credit |

#### Annual Conference — AI BIJAN Summit

```
AI BIJAN Summit 2027 — Kuala Lumpur Convention Centre
├── Keynotes from successful Malaysian entrepreneurs
├── Live AI demos (no slides, real-time)
├── Developer track (SDK, marketplace, workshops)
├── Industry vertical sessions (retail, F&B, healthcare, etc.)
├── Partner expo (50+ exhibitors)
├── Awards: "AI-Powered Business of the Year"
└── Networking: Dinner, after-party, investor meetups

Target: 2,000 attendees Year 1 → 10,000 Year 3 → 50,000 Year 5
```

#### Success Stories Engine

```
Monthly process:
1. Identify top 10 success stories (revenue growth, efficiency gains)
2. Produce professional case studies (video + written + infographic)
3. Distribute via PR, social media, press releases, sales deck
4. Feature on homepage, onboarding flow, investor materials

Example headline:
"Kedai Runcun Kecil Jadi Eksport — Naik 300% Guna AI BIJAN"
"From Mom-and-Pop Shop to Export Revenue: How AI BIJAN Transformed..."
```

### 52.7 Technology Moat — Uncopyable Advantages

| Moat | Description | Competitor Difficulty to Copy |
|------|-------------|------------------------------|
| **Proprietary Business LLM** | Fine-tuned on 1M+ business conversations | Years of data collection |
| **Multilingual Corpus** | Native BM, EN, CN, TA, ID, AR understanding | Language expertise needed |
| **Industry Pack Library** | 19 packs × 50+ fields each, continuously growing | Domain expertise + time |
| **Collective Intelligence** | Anonymized patterns from 1M+ tenants | Need critical mass of tenants |
| **White-label Network** | Banks, telcos, govt distribution channels | Relationship building |
| **Developer Ecosystem** | Marketplace + SDK + certification program | Network effects |
| **Compliance Database** | Malaysia law → global law, continuously updated | Legal expertise + maintenance |
| **Voice Interface** | Multilingual voice commands, accent recognition | Speech tech + domain adaptation |

### 52.8 Three-Year Battle Plan

```
YEAR 1 — PROVE IT (Malaysia Dominance)
├── Q1: Launch freemium, onboard 10K free tenants
├── Q2: Government partnership (SME ministry)
├── Q3: Bank partnership (Maybank/CIMB white-label)
├── Q4: 100K tenants, RM5M ARR, launch AI BIJAN Academy
└── WIN: Become #1 recognized SME platform in Malaysia

YEAR 2 — SCALE IT (Southeast Asia)
├── Q1: Launch Indonesia, Nigeria pilots
├── Q2: Proprietary Business LLM released
├── Q3: Developer marketplace opens
├── Q4: 500K tenants, RM50M ARR, Series B funding
└── WIN: #1 SME platform in 3 emerging markets

YEAR 3 — DOMINATE IT (Global Emerging Markets)
├── Q1: Launch India, Brazil
├── Q2: Transaction revenue > subscription revenue
├── Q3: AI BIJAN Summit (5K attendees)
├── Q4: 2M tenants, RM200M ARR, IPO preparation
└── WIN: Category creator — "AI-Native Business OS"
```

### 52.9 The One Thing That Matters Most

> **Build something so good that users become evangelists.**

Not marketing. Not ads. **PRODUCT-LED GROWTH.**

When every tenant tells 3 friends: *"You NEED to try this"* — that's when you become #1 globally.

**AI BIJAN's secret weapon:** Nobody has built a COMPLETE business OS with AI at the core for emerging markets. You're not competing against SAP or Oracle — you're creating a category that didn't exist.

**That's your blue ocean.** 🌊

---


> **Dokumen ini dicipta pada 19 Oktober 2026.**  


## PART XI — COMMERCE & MARKETING ENHANCEMENTS

## BAB 53 — B-Site: Full Website Builder (Shopify Store Replacement)

### 53.1 Vision — Why B-Site?

```
Current situation for Malaysian SMEs who want e-commerce:
├── Shopify: RM299+/bulan, US-focused, no MY compliance
├── WooCommerce: Free software but need hosting + maintenance + security
├── Shopee/Lazada: Marketplace commission 4-8%, no brand ownership
└── Result: Many businesses DON'T have proper website → lost revenue

AI BIJAN Solution:
├── B-Site: Built-in website builder, ONE platform
├── One product → Site + Marketplace + POS (single inventory)
├── FPX/DuitNow native (no extra gateway setup)
├── e-Invois ready (LHDN compliance built-in)
├── AI-generated content in BM/EN/CN
└── Price: Included in RM288-RM1,888 subscription

**Key Advantage vs Shopify:** Shopify starts at RM299/bulan untuk basic features — AI BIJAN Starter (RM288/bulan) dah include B-Site + POS + CRM + Finance + AI Agents.
```

**Positioning:** "Bina laman web bisnes anda dalam 5 minit — tanpa coding."

### 53.2 Feature Matrix — B-Site vs Shopify

| Feature | B-Site Target | Shopify Basic | Gap Strategy |
|---------|--------------|---------------|--------------|
| **Drag-and-drop builder** | ✅ Month 4-6 | ✅ Native | Build after basic builder |
| **Themes** | 20+ free themes Year 1 | 7 free + paid | Start with 5 industry-specific |
| **Product catalog sync** | ✅ Month 1-3 | ✅ Native | Single source of truth |
| **Checkout flow** | ✅ Month 1-3 | ✅ Native | FPX/DuitNow first, Stripe later |
| **Payment gateways** | Billplz, ToyyibPay, FPX, Stripe | Stripe, PayPal, Shopify Payments | Local payment first |
| **SEO tools** | AI meta tags BM/EN/CN | Basic SEO | AI multilingual advantage |
| **Blog/CMS** | ✅ Month 4-6 | ✅ Native | AI-written posts |
| **Custom domains** | ✅ Month 1-3 | ✅ Paid app | Free .xyz, RM5/year .com |
| **SSL certificate** | ✅ Included | ✅ Included | Automatic Let's Encrypt |
| **Hosting included** | ✅ Cloudflare R2 + CDN | ✅ Included | Same model |
| **Multi-language site** | ✅ Native BM/EN/CN/Tamil | ❌ Paid app ($20/mo) | KEY DIFFERENTIATOR |
| **Analytics dashboard** | ✅ AI insights | Basic stats | Predictive analytics |
| **Theme marketplace** | ✅ Year 2 | ✅ 8,000+ apps | Community-driven |
| **API for developers** | ✅ OpenAPI 3.0 | ✅ GraphQL | SDK for PHP/TS/Python |

### 53.3 Data Model — B-Site

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `sites` | Multi-tenant sites (one or more per tenant) | `tenant_id`, `name`, `domain`, `theme_id`, `status` |
| `site_pages` | Individual pages (home, about, products, blog) | `site_id`, `slug`, `template`, `seo_title`, `seo_description` |
| `site_themes` | Theme definitions (layout, colors, fonts) | `name`, `preview_image`, `settings_schema`, `sections_schema` |
| `site_sections` | Reusable sections (hero, product grid, testimonial) | `page_id`, `type`, `config_json`, `position` |
| `site_settings` | SEO, analytics, domain config | `site_id`, `google_analytics_id`, `meta_tags` |
| `site_posts` | Blog posts with categories/tags | `site_id`, `title`, `content`, `status`, `published_at` |
| `site_media` | Images, videos, documents | `site_id`, `url`, `mime_type`, `alt_text` |
| `site_forms` | Contact forms, lead capture | `site_id`, `fields_json`, `notification_email` |
| `site_analytics` | Page views, bounce rate, conversion | `site_id`, `page_slug`, `views`, `unique_visitors`, `date` |
| `site_products` | Product display configuration | `product_id`, `site_id`, `display_order`, `featured` |

### 53.4 AI BIJAN Advantage Over Shopify

```
Shopify Flow:
1. User picks theme → manually customize
2. User adds products one by one
3. User writes SEO meta tags manually
4. User creates blog posts manually
5. User sets up payments manually

AI BIJAN Flow:
1. User selects industry → AI generates COMPLETE website
   - Hero section with relevant imagery
   - Product grid auto-populated from inventory
   - About page with company info
   - Contact form with business details
   
2. User imports products (CSV/API) → AI optimizes listings
   - Auto-generates product descriptions in BM/EN
   - Suggests pricing based on market analysis
   - Creates product images with AI Media Studio
   
3. AI auto-generates SEO meta tags in BM/EN/CN
   - Title tag optimized for search intent
   - Description with keywords in all languages
   - Open Graph tags for social sharing
   
4. AI writes blog posts based on products/industry trends
   - Weekly automated posts (user approves)
   - SEO-optimized with internal linking
   - Shared to social media automatically
   
5. AI A/B tests landing pages automatically
   - Tests headlines, CTAs, layouts
   - Picks winner based on conversion rate
   - Continuously optimizes
   
6. AI optimizes checkout conversion in real-time
   - Reduces friction points
   - Suggests trust badges
   - Recommends payment methods by region
```

### 53.5 Implementation Timeline

| Phase | Features | Duration | Revenue Impact |
|-------|----------|----------|----------------|
| **Phase 1** | Basic site builder, 5 themes, product sync, checkout, custom domains | Month 1-3 | HIGH — immediate revenue |
| **Phase 2** | Drag-and-drop editor, blog, SEO tools, multi-language, analytics | Month 4-6 | MEDIUM — professional-grade |
| **Phase 3** | AI-generated content, A/B testing, predictive optimization | Month 7-9 | HIGH — differentiation |
| **Phase 4** | Theme marketplace, API for developers, advanced integrations | Month 10-12 | SCALE — ecosystem effects |

### 53.6 Technical Architecture

```
B-Site Stack:
├── Frontend: Next.js 15 + React 19 (SSR for SEO)
├── Tailwind CSS for styling
├── Headless CMS architecture
├── Static generation for performance
├── Edge caching via Cloudflare
└── Dynamic features via API routes

Rendering Strategy:
├── Homepage: SSR (dynamic content)
├── Product pages: SSG + ISR (incremental static regeneration)
├── Blog posts: SSG (fastest)
├── Checkout: CSR (requires auth/payment)
└── Custom pages: Configurable per theme
```

---

## BAB 54 — Deep CRM & Sales Enablement

### 54.1 Vision — Complete Customer 360°

```
Current state:
├── customers table exists in Core Commerce
├── Basic profiles: name, email, phone, address
├── Purchase history linked
└── NO unified view across channels

Target state (Deep CRM):
├── Unified contact graph (merge same person from email, phone, WhatsApp, social)
├── Interaction timeline (every touchpoint logged)
├── Company/organization view (B2B: see all contacts at same company)
├── Deal pipeline (visual sales funnel with drag-and-drop)
├── Activity logging (calls, meetings, notes)
├── AI insights (churn prediction, cross-sell opportunities)
└── Integration with B-Marketing sequences
```

**Why this matters:** Every business needs to track relationships. Without CRM, sales are chaotic and missed opportunities compound.

### 54.2 Feature Matrix — Deep CRM vs HubSpot CRM

| Feature | Deep CRM Target | HubSpot Free | AI BIJAN Twist |
|---------|----------------|--------------|----------------|
| **Contact database** | ✅ 360° view | ✅ Contacts | Merge from all channels automatically |
| **Company view** | ✅ B2B organizations | ✅ Companies | Link to purchase history |
| **Deal pipeline** | ✅ Visual funnel | ✅ Deals | AI predicts win probability |
| **Interaction timeline** | ✅ All touchpoints | ✅ Timeline | Includes WhatsApp, calls, purchases |
| **Email sequences** | ✅ Drip campaigns | ✅ Workflows | AI writes copy in BM/EN |
| **Lead scoring** | ✅ Behavioral | ✅ Paid only | FREE — core feature |
| **Task management** | ✅ Follow-ups | ✅ Tasks | AI suggests next actions |
| **Document storage** | ✅ Attach files | ❌ Limited | Proposals, contracts linked to deal |
| **Meeting scheduler** | ✅ Book links | ✅ Paid app | FREE — core feature |
| **Live chat** | ✅ Omnichannel inbox | ✅ Chat | Integrated with B-Omnichannel |
| **ROI dashboard** | ✅ Channel attribution | ✅ Reporting | AI recommends budget shifts |

### 54.3 Data Model — CRM

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `crm_contacts` | Unified contact (person-level) | `tenant_id`, `first_name`, `last_name`, `email`, `phone`, `source`, `status` |
| `crm_contact_properties` | Custom fields per contact | `contact_id`, `key`, `value`, `type` |
| `crm_companies` | B2B companies/organizations | `tenant_id`, `name`, `industry`, `website`, `size` |
| `crm_contact_company` | Many-to-many: contacts at companies | `contact_id`, `company_id`, `role`, `is_primary` |
| `crm_deals` | Sales opportunities | `tenant_id`, `title`, `contact_id`, `company_id`, `stage_id`, `value`, `probability`, `close_date` |
| `crm_deal_stages` | Pipeline stages configuration | `tenant_id`, `name`, `order`, `color` |
| `crm_activities` | Calls, meetings, emails, notes | `deal_id`, `type`, `summary`, `scheduled_at`, `completed_at` |
| `crm_tasks` | Follow-up items with due dates | `contact_id`, `title`, `description`, `due_at`, `status`, `assigned_to` |
| `crm_documents` | Attached files (proposals, contracts) | `deal_id`, `file_url`, `type`, `version` |
| `crm_communications` | Email, WhatsApp, call logs | `contact_id`, `channel`, `direction`, `content`, `timestamp` |
| `crm_lead_scores` | AI-calculated lead scores | `contact_id`, `score`, `factors_json`, `updated_at` |
| `crm_touchpoints` | Every interaction logged | `contact_id`, `channel`, `type`, `metadata`, `created_at` |

### 54.4 Deal Pipeline Example

```yaml
default_pipeline: "Sales Process"
stages:
  - order: 1
    name: "Prospect"
    color: "#6366F1"
    estimated_days: 7
    
  - order: 2
    name: "Qualified"
    color: "#8B5CF6"
    estimated_days: 14
    requirements:
      - "budget_discussed: true"
      - "need_confirmed: true"
      
  - order: 3
    name: "Proposal"
    color: "#EC4899"
    estimated_days: 10
    
  - order: 4
    name: "Negotiation"
    color: "#F59E0B"
    estimated_days: 7
    
  - order: 5
    name: "Closed Won"
    color: "#10B981"
    
  - order: 6
    name: "Closed Lost"
    color: "#EF4444"

AI predictions:
├── Win probability: calculated from stage, value, history
├── Recommended next action: based on stage duration
├── Similar deal comparison: "Deals like this close in 12 days avg"
└── Escalation alert: "Stuck in Proposal > 14 days"
```

### 54.5 Lead Scoring Engine

```
Lead Score = behavioral_score + demographic_score + engagement_score

Behavioral Score (0-40 points):
├── Visited pricing page: +10
├── Downloaded brochure: +15
├── Requested demo: +25
├── Opened email: +5
├── Clicked link in email: +8
└── Repeated visits (frequency): +5-15

Demographic Score (0-30 points):
├── Industry match (ideal customer profile): +10
├── Company size fit: +10
└── Decision maker title: +10

Engagement Score (0-30 points):
├── Email replies: +10
├── Meeting attended: +15
├── Proposal viewed: +10
└── Time since last contact: -5/day decay

Score Tiers:
├── Cold (< 30): Add to nurture sequence
├── Warm (30-60): Regular follow-up
├── Hot (60-80): Sales outreach priority
└── Ready (80+): Close immediately

AI Enhancement:
├── Learn from closed deals which scores led to wins
├── Adjust weights monthly based on conversion data
└── Predict optimal contact time per lead
```

### 54.6 Email Sequences — Drip Campaigns

```yaml
sequence: "welcome_nurture_7day"
trigger: "New contact added to segment 'new_prospect'"
steps:
  - day: 0
    action: "send_email"
    template: "welcome_introduction"
    language: "auto_detect" # BM, EN, CN based on contact profile
    send_time: "optimal_per_contact" # AI determined from open history
    
  - day: 1
    action: "check_behavior"
    conditions:
      - if: "email_opened"
        then: "tag_as_engaged"
      - if: "email_not_opened"
        then: "send_followup_email"
          subject: "Quick question for [Name]"
          
  - day: 3
    action: "check_behavior"
    conditions:
      - if: "visited_pricing_page"
        then: "add_to_deal_pipeline"
           stage: "qualified"
      - if: "clicked_product_link"
        then: "send_product_demo_video"
        
  - day: 5
    action: "ai_lead_score"
    update: "lead_score += 30 if engaged"
    
  - day: 7
    action: "conditional"
    conditions:
      - if: "lead_score > 70"
        then: "notify_sales_team"
      - if: "lead_score < 30"
        then: "add_to_general_newsletter"

AI Personalization:
├── Subject line A/B tested automatically
├── Send time optimized per recipient
├── Content adapted to industry/role
└── Follow-up suggestions based on engagement
```

### 54.7 Implementation Priority

| Feature | Priority | Timeline | Why First |
|---------|----------|----------|-----------|
| **Contact DB** | P0 | Month 1-3 | Foundation for everything |
| **Deal Pipeline** | P0 | Month 1-3 | Direct revenue impact |
| **Email Sequences** | P0 | Month 1-3 | Replaces Mailchimp immediately |
| **Lead Scoring** | P1 | Month 4-6 | Improve sales conversion |
| **Meeting Scheduler** | P1 | Month 4-6 | Professional feature |
| **AI Insights** | P1 | Month 7-9 | Differentiator vs HubSpot |

---

## BAB 55 — Service Desk & Quote Builder

### 55.1 Service Desk — Helpdesk + Customer Support

#### Current State
- B-Helpdesk entity defined but not built
- Basic ticket concept exists in some modules

#### What to Build

| Feature | Description | Priority |
|---------|-------------|----------|
| **Ticket system** | Create, assign, prioritize, resolve tickets | P0 |
| **Knowledge base** | Self-service FAQ/articles | P1 |
| **Customer portal** | Customers track their own tickets | P1 |
| **SLA tracking** | Response time, resolution time targets | P1 |
| **Automated routing** | Tickets to correct department/agent | P1 |
| **Canned responses** | Template replies for common issues | P2 |
| **Customer satisfaction** | Post-resolution survey (CSAT) | P2 |
| **AI assistant** | Auto-suggest solutions from knowledge base | P2 |

#### Data Model — Service Desk

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `tickets` | Support tickets | `tenant_id`, `subject`, `status`, `priority`, `assignee_id`, `customer_id` |
| `ticket_threads` | Conversation history | `ticket_id`, `message`, `sender_type`, `attachments` |
| `ticket_categories` | Department/category routing | `tenant_id`, `name`, `routing_rules` |
| `sla_policies` | Response/resolution time targets | `tenant_id`, `priority`, `response_hours`, `resolution_hours` |
| `sla_breaches` | SLA violation tracking | `ticket_id`, `policy_id`, `breach_type`, `detected_at` |
| `knowledge_base` | FAQ articles | `tenant_id`, `title`, `content`, `category`, `view_count` |
| `csat_surveys` | Post-resolution surveys | `ticket_id`, `rating`, `feedback`, `submitted_at` |

#### Ticket Workflow

```
Ticket Created (via email, portal, WhatsApp)
        ↓
Auto-categorize (AI suggests category/priority)
        ↓
Route to correct team/agent (based on skills, workload)
        ↓
Agent responds (canned templates + AI suggestions)
        ↓
Customer replies (thread continues)
        ↓
Resolved → CSAT survey sent automatically
        ↓
If unsatisfied → reopens → escalates to manager

SLA Monitoring:
├── Green: On track
├── Yellow: Approaching deadline (>50% time used)
└── Red: Breached → notify manager
```

### 55.2 Quote & Proposal Builder

#### Current State
- Quotes exist in B-Invoicing but basic
- No professional formatting or e-signature

#### What to Build

| Feature | Description | Priority |
|---------|-------------|----------|
| **Professional templates** | Branded quote templates | P0 |
| **Line items** | Products/services with quantities/prices | P0 |
| **Discounts & terms** | Volume discounts, payment terms | P0 |
| **E-signature** | Customer signs digitally | P1 |
| **Approval workflow** | Manager approves quotes above threshold | P1 |
| **Convert to invoice** | One-click after acceptance | P0 |
| **Version control** | Track quote revisions | P2 |
| **Validity period** | Quote expires after X days | P0 |

#### Data Model — Quotes

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `quotes` | Quote documents | `tenant_id`, `quote_number`, `contact_id`, `status`, `valid_until`, `total_amount` |
| `quote_lines` | Line items | `quote_id`, `product_id`, `description`, `quantity`, `unit_price`, `discount` |
| `quote_templates` | Template designs | `tenant_id`, `name`, `html_template`, `is_default` |
| `quote_signatures` | E-signature records | `quote_id`, `signer_name`, `signed_at`, `ip_address`, `signature_image` |
| `quote_versions` | Version history | `quote_id`, `version`, `changes_json`, `created_at` |

#### Quote Lifecycle

```
Draft (Agent creates quote from deal)
        ↓
Internal Approval (if amount > threshold)
        ↓
Sent to Customer (PDF + secure link)
        ↓
Customer Reviews (can request changes → new version)
        ↓
Customer Signs (e-signature captured)
        ↓
Accepted → Convert to Invoice (one-click)
        ↓
Or → Expired (after valid_until date)
        ↓
Or → Declined (reason logged for analytics)
```

---

## BAB 56 — Go-to-Market Strategy & Revenue Projections

### 56.1 Positioning Statement

> **"For Malaysian SMEs who currently juggle 10+ tools costing RM1,000+/month,
> AI BIJAN is the all-in-one AI-native business platform that replaces Shopify,
> HubSpot, Mailchimp, and accounting software — starting from just RM288/month."**

### 56.2 Messaging by Persona

| Persona | Pain Point | AI BIJAN Solution |
|---------|-----------|------------------|
| **Kedai Runcit** | "Saya guna Shopee + akaunsoft + whatsapp business" | Semua dalam satu: POS + site + finance + marketing |
| **Agency Digital** | "Client saya minta buat website + marketing + CRM" | B-Site + B-Marketing + CRM — deliver faster |
| **Wholesaler** | "Saya ada 50 kedai, susah track semua" | Central dashboard + inventory + finance |
| **Service Business** | "Salon/clinic perlu booking + reminder + loyalty" | B-Booking + B-Loyalty + B-Omnichannel |
| **Exporter** | "Export ke Indonesia/India, perlu multi-currency" | B-Marketplace + multi-currency + compliance |

### 56.3 Competitive Positioning

#### Before Enhancements (Current)

| Dimension | AI BIJAN | Shopify | HubSpot |
|-----------|----------|---------|---------|
| E-commerce | ⚠️ Partial | ⭐⭐⭐⭐⭐ | ❌ |
| Marketing | ⚠️ Basic | ⭐⭐ | ⭐⭐⭐⭐⭐ |
| CRM | ⚠️ Basic | ❌ | ⭐⭐⭐⭐ |
| Finance | ⚠️ Planned | ❌ | ❌ |
| HRMS | ⚠️ Partial | ❌ | ❌ |
| AI Agents | ⭐⭐⭐⭐⭐ | ❌ | ⭐⭐ |
| Price | RM288-1,888/mo | $25-149/mo | Free-$2,400/mo |

#### After Enhments (Target Year 2)

| Dimension | AI BIJAN | Shopify | HubSpot |
|-----------|----------|---------|---------|
| E-commerce | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ❌ |
| Marketing | ⭐⭐⭐⭐ | ⭐⭐ | ⭐⭐⭐⭐ |
| CRM | ⭐⭐⭐⭐ | ❌ | ⭐⭐⭐⭐ |
| Finance | ⭐⭐⭐⭐ | ❌ | ❌ |
| HRMS | ⭐⭐⭐ | ❌ | ❌ |
| AI Agents | ⭐⭐⭐⭐⭐ | ❌ | ⭐⭐ |
| All-in-One | ⭐⭐⭐⭐⭐ | ⭐⭐ | ⭐⭐ |
| Price | RM288-1,888/mo | $25-149/mo | Free-$2,400/mo |

**KEY INSIGHT:** After enhancements, AI BIJAN becomes the ONLY platform that competes across ALL dimensions at reasonable price.

### 56.4 Revenue Impact Analysis

> **⚠️ NOTA PENTING:** Semua angka di bawah adalah **PROYEKSI/SKENARIO**, bukan fakta atau jaminan. Angka ini berdasarkan andaian yang dinyatakan di bawah dan mungkin berbeza secara ketara daripada prestasi sebenar.
>
> **Andasan Asas (Base Case):**
> | Parameter | Nilai |
> |-----------|-------|
> | Free trial → Paid conversion rate | 8% (industry average SaaS Malaysia) |
> | Monthly churn rate | 3% (target after enhancements) |
> | ARPU (Average Revenue Per User) | RM980 (weighted across tiers) |
> | Customer Acquisition Cost (CAC) | RM350 (digital marketing + referrals) |
> | COGS (Cost of Goods Sold) | 35% of revenue (hosting, support, payment gateway fees) |
> | Time horizon | 24 bulan dari pelancaran |
>
> **Sensitivity Analysis:**
> | Scenario | Tenants (Year 2) | Monthly Revenue | Assumption Change |
> |----------|------------------|-----------------|-------------------|
> | **Pessimistic** | 15,000 | RM3.2M | Conversion 4%, Churn 6%, CAC RM500 |
> | **Base Case** | 71,000 | RM12.4M | Conversion 8%, Churn 3%, CAC RM350 |
> | **Optimistic** | 150,000 | RM30.0M | Conversion 12%, Churn 2%, CAC RM200 |

#### Projected Revenue Scenarios (Without Enhancements)

| Tier | Tenants | ARPU | Monthly Revenue |
|------|---------|------|----------------|
| Free Trial | 10,000 | RM0 | RM0 |
| Starter (RM288) | 1,000 | RM288 | RM288,000 |
| Professional (RM488) | 500 | RM488 | RM244,000 |
| Business (RM888) | 300 | RM888 | RM266,400 |
| Business Pro (RM1,288) | 150 | RM1,288 | RM193,200 |
| Enterprise (RM1,888) | 100 | RM1,888 | RM188,800 |
| **Total** | **12,050** | | **RM1,180,400/month** |

#### Projected Revenue Scenarios (With Enhancements — Base Case)

| Tier | Tenants | ARPU | Monthly Revenue |
|------|---------|------|----------------|
| Free Trial | 50,000 | RM0 | RM0 |
| Starter (RM288) | 10,000 | RM288 | RM2,880,000 |
| Professional (RM488) | 5,000 | RM488 | RM2,440,000 |
| Business (RM888) | 3,000 | RM888 | RM2,664,000 |
| Business Pro (RM1,288) | 2,000 | RM1,288 | RM2,576,000 |
| Enterprise (RM1,888) | 1,000 | RM1,888 | RM1,888,000 |
| **Total** | **71,000** | | **RM12,448,000/month** |

**Growth: RM1.2M → RM12.4M/month (10x increase)** — *base case projection*

Drivers:
1. **More conversions** from free trial (better product = higher value perception)
2. **Higher tier upgrades** (Business Pro & Enterprise tiers now include premium AI credits + priority support)
3. **Reduced churn** (more modules + memory architecture = harder to leave)
4. **New acquisition channel** (agencies reselling AI BIJAN as all-in-one solution)
5. **AI credit top-ups** (additional revenue from token purchases beyond included quotas)

Drivers:
1. **More conversions** from free trial (better product = higher value perception)
2. **Higher tier upgrades** (Business Pro & Enterprise tiers now include premium AI credits + priority support)
3. **Reduced churn** (more modules + memory architecture = harder to leave)
4. **New acquisition channel** (agencies reselling AI BIJAN as all-in-one solution)
5. **AI credit top-ups** (additional revenue from token purchases beyond included quotas)

### 56.5 Launch Campaign

```
Phase 1: Early Adopters (Month 1-2)
├── Offer: 6 months free for first 500 tenants
├── Requirement: Provide feedback + case study
├── Target: Existing AI BIJAN users + beta testers
└── Goal: Validate product-market fit

Phase 2: General Availability (Month 3-6)
├── Pricing: RM288 Starter, RM488 Professional, RM888 Business, RM1,288 Business Pro, RM1,888 Enterprise
├── Bundle: "All-in-One" package (Site + Marketing + CRM + Finance + AI Credits)
├── Comparison page: "AI BIJAN vs Shopify + HubSpot" cost breakdown
└── Goal: 5,000 tenants

Phase 3: Scale (Month 6-12)
├── Partner program: Agencies resell AI BIJAN
├── Marketplace: Third-party plugins/themes
├── Government partnership: SME digitalization grant
└── Goal: 50,000 tenants
```

### 56.6 The One Enhancement Rule

> **Build something so good that users become evangelists.**
> 
> Not marketing. Not ads. **PRODUCT-LED GROWTH.**
> 
> When every tenant tells 3 friends: *"You NEED to try this"* — that's when you become #1 globally.
> 
> **AI BIJAN's secret weapon:** Nobody has built a COMPLETE business OS with AI at the core for emerging markets. You're not competing against SAP or Oracle — you're creating a category that didn't exist.
> 
> **That's your blue ocean.** 🌊

---

> **BAB 53-56C dicipta pada 19 Oktober 2026.**  
> **Menutup siklus dokumentasi: Commerce (B-Site), CRM, Service Desk, Go-to-Market, AI Live Interaction, Mobile Apps, CCTV Surveillance.**  
> **Status: Perancangan strategik — panduan pelaksanaan Fasa 4-5.**

---

## BAB 56A — AI Live Interaction: Voice, Video & Real-Time Communication

### 56A.1 Visi — Mengapa AI Live Interaction?

```
Current situation for Malaysian SMEs:
├── WhatsApp: Semua orang guna, tapi AI tak boleh buat call/video
├── Zoom/Teams: Untuk meeting, tapi tiada AI integration
├── Chatbot: Text-only, tak sesuai untuk owner yang tak pandai type
├── Google Meet: Good video, tapi AI hanya transcribe sahaja
└── Result: SME owners yang busy/on-the-go TAK BISA guna AI dengan mudah

AI BIJAN Solution:
├── AI Voice Call: Tekan butang → bercakap macam WhatsApp voice note
├── AI Video Call: Video call dengan AI avatar (wajah + suara)
├── AI Camera Vision: Buka kamera → tunjuk barang → AI analyze
├── AI Meeting Room: AI join meeting sebagai peserta aktif
└── Semua dalam satu platform — RM288-RM1,888/bulan
```

**Positioning:** *"Bercakap dengan AI macam bercakap dengan staf terbaik anda — bila-bila masa, di mana-mana."*

**Target Users:**
- **Pakcik/Auntie kedai** yang tak pandai type → pakai voice call je
- **Owner bisnes on-the-go** → bercakap sambil drive/pandang jalan
- **Manager warehouse** → point kamera ke shelf, AI check stok
- **Sales team** → AI join customer call, transcribe & suggest responses

---

### 56A.2 Four Modes of AI Live Interaction

| Mode | Description | Use Case | Tech Stack |
|------|-------------|----------|------------|
| **Voice Chat** | Push-to-talk voice conversation | Quick questions while driving/working | WebRTC + Whisper + TTS |
| **Voice Call** | Continuous voice call (like phone) | Extended business discussions | SIP/WebRTC + VAD + LLM |
| **Video Call** | Video with AI avatar | Face-to-face business meetings | WebRTC + Avatar render + Vision |
| **Camera Vision** | Point camera at anything | Product lookup, document scan, market analysis | Camera API + GPT-4V + RAG |

#### 56A.2.1 Voice Chat (Push-to-Talk)

**User Experience:**
```
1. User tekan dan tahan butang mic (atau butang sentuh untuk toggle)
2. Bercakap seperti WhatsApp voice note
3. AI dengar → process → respond dengan suara
4. Lepaskan butang → AI mula think (loading animation)
5. AI respond dalam BM/EN/CN mengikut preference tenant

Features:
- Automatic language detection (BM/EN/CN mixed)
- Context awareness (AI tahu business tenant anda)
- Real-time transcription (subtitle muncul semasa bercakap)
- Save conversation history untuk reference
- Custom AI voice (clone owner's voice atau pilih preset)

Technical Flow:
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│  Mic     │───▶│  STT     │───▶│  LLM     │───▶│  TTS     │
│ Capture  │   │ (Whisper)│    │ (GPT-6)   │    │ (ElevenL)│
└──────────┘    └──────────┘    └──────────┘    └──────────┘
```

#### 56A.2.2 Voice Call (Continuous)

**User Experience:**
```
1. User tekan "Call AI" — video call interface muncul
2. AI avatar muncul (wajah + suara)
3. Bercakap continuous tanpa perlu tekan butang
4. AI interruptible (boleh interrupt AI tengah cakap)
5. Auto-save transcript + action items selepas call

Advanced Features:
- Voice Activity Detection (VAD) — auto detect bila user berhenti cakap
- Natural interrupt — user boleh interrupt AI tanpa masalah
- Noise suppression — background noise filter
- Echo cancellation — untuk speakerphone mode
- Multi-language switching — BM → EN → BM naturally
- Meeting mode — AI join Zoom/Meet sebagai peserta
```

#### 56A.2.3 Video Call (AI Avatar)

**User Experience:**
```
1. User pilih avatar AI (preset atau custom)
2. Start video call — avatar muncul dengan wajah + lip-sync
3. AI respond dengan facial expressions yang natural
4. Screen share capability — AI tunjuk data/chart sambil bercakap
5. Recording & transcript auto-save

Avatar Types:
- Professional: Business suit, formal background
- Friendly: Casual, warm smile, bright background
- Brand-custom: White-label dengan brand colors
- Custom: Upload photo/video untuk train custom avatar
```

#### 56A.2.4 Camera Vision (Point & Ask)

**User Experience:**
```
1. User buka kamera dalam app
2. Point ke: produk, dokumen, competitor shop, market
3. AI identify object + extract information
4. Show relevant data: price, stock, description, insights
5. Save capture untuk reference / training

Use Cases:
- Retail: Point at shelf → AI show stock levels, reorder suggestions
- Finance: Point at invoice → AI extract data, enter into system
- Market: Point at competitor → AI analyze pricing, suggest response
- Warehouse: Point at goods → AI verify delivery, log receipt
```

---

### 56A.3 Technical Architecture — Real-Time Pipeline

```
┌─────────────────────────────────────────────────────────────┐
│           AI LIVE INTERACTION (4 Modes)                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Mode 1: VOICE CHAT (Push-to-Talk)                          │
│  ├── Seperti WhatsApp voice note                           │
│  ├── Tekan butang → bercakap → lepas → AI jawab audio      │
│  ├── Latency: < 1 saat                                     │
│  └── Use: Quick questions, commands, notes                 │
│                                                             │
│  Mode 2: VOICE CALL (Continuous)                            │
│  ├── Seperti call WhatsApp/Telegram                        │
│  ├── Continuous conversation tanpa tekan butang            │
│  ├── Auto-detect speech end → AI respond natural           │
│  └── Use: Deep discussion, training, consultation          │
│                                                             │
│  Mode 3: VIDEO CALL (AI Avatar)                             │
│  ├── Video call dengan AI avatar (wajah + suara)           │
│  ├── AI ada visual presence — nampak macam staf              │
│  ├── Real-time lip-sync dari TTS output                    │
│  └── Use: Customer service, onboarding, presentations      │
│                                                             │
│  Mode 4: CAMERA VISION (Point & Ask)                        │
│  ├── Buka kamera → point ke objek → tanya AI               │
│  ├── AI analyze visual → respond audio/text                │
│  └── Use: Inventory check, product info, document scan     │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

### 56A.3 Technical Architecture — Real-Time Pipeline

```
┌──────────────────────────────────────────────────────────────┐
│              CLIENT (Mobile/Web Browser)                      │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────────┐   │
│  │  Camera   │  │   Mic    │  │  Speaker │  │  Display   │   │
│  │  (Video)  │  │  (Audio) │  │  (Audio) │  │  (Avatar)  │   │
│  └─────┬─────┘  └────┬─────┘  └────┬─────┘  └─────┬──────┘   │
│        │              │              │              │         │
│  ┌─────▼──────────────▼──────────────▼──────────────▼──────┐  │
│  │           WebRTC Stream (Real-time Media)                │  │
│  └─────────────────────────┬──────────────────────────────┘  │
└────────────────────────────┼────────────────────────────────┘
                             │ WebRTC (UDP)
                             │
┌────────────────────────────▼────────────────────────────────┐
│              AI BIJAN GATEWAY SERVER                         │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  STUN/TURN Server (Coturn) — NAT Traversal           │   │
│  │  WebRTC Signaling (Laravel Reverb/Soketi)            │   │
│  └──────────────────────────────────────────────────────┘   │
│                             │                                │
│  ┌─────────────────────────▼──────────────────────────────┐ │
│  │           AI PROCESSING PIPELINE                        │ │
│  │                                                         │ │
│  │  Audio In → [STT Engine] → Text                        │ │
│  │       ↓                                                   │ │
│  │  Text → [LLM API] → Response Text                       │ │
│  │       ↓                                                   │ │
│  │  Text → [TTS Engine] → Audio Out → Speaker              │ │
│  │                                                         │ │
│  │  Video In → [Vision Model] → Visual Understanding       │ │
│  │       ↓                                                   │ │
│  │  Visual Context → [LLM API] → Enhanced Response         │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────┐
│              AI MODEL SERVICES                               │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐            │
│  │  STT API   │  │  LLM API   │  │  TTS API   │            │
│  │ (Whisper)  │  │(GPT-6/Claude)│ (ElevenLabs)│            │
│  └────────────┘  └────────────┘  └────────────┘            │
│                                                              │
│  ┌────────────┐  ┌────────────┐                              │
│  │ Vision API │  │ Avatar     │                              │
│  │(GPT-4V)    │  │ Render     │                              │
│  │            │  │(Live2D/    │                              │
│  │            │  │  HeyGen)    │                              │
│  │            │  └────────────┘                              │
│  └────────────┘                                              │
└─────────────────────────────────────────────────────────────┘
```

#### Pipeline Details

| Step | Component | Technology | Latency Target | Cost/Min |
|------|-----------|------------|----------------|----------|
| **Audio Capture** | WebRTC Mic | browser API | < 50ms | Free |
| **Speech-to-Text** | Whisper Large-v3 | Open-source / serveras.click | < 800ms | $0.004/min |
| **Intent Recognition** | LLM (Qwen 3.8 Max) | serveras.click | < 500ms | $0.00007/token |
| **Response Generation** | LLM (GPT-6/Claude) | serveras.click | < 1,000ms | $0.0001/token |
| **Text-to-Speech** | ElevenLabs / Coqui | Self-hosted / cloud | < 600ms | $0.015/min |
| **Avatar Rendering** | Live2D / HeyGen | Cloud render | < 200ms/frame | $0.02/min |
| **Video Analysis** | GPT-4V / Claude Vision | serveras.click | < 1,500ms | $0.00015/token |

**Total Estimated Cost per Minute:**
- Voice-only chat: ~$0.025/min (RM0.11/min)
- Voice call (continuous): ~$0.035/min (RM0.15/min)
- Video call with avatar: ~$0.085/min (RM0.37/min)
- Camera vision (on-demand): ~$0.050/capture (RM0.22/capture)

---

### 56A.4 Feature Matrix by Tier

| Feature | Starter (RM288) | Professional (RM488) | Business (RM888) | Business Pro (RM1,288) | Enterprise (RM1,888) |
|---------|----------------|---------------------|------------------|----------------------|---------------------|
| **Voice Chat** (push-to-talk) | ✅ 30 min/month | ✅ 120 min/month | ✅ 300 min/month | ✅ 600 min/month | ✅ Unlimited |
| **Voice Call** (continuous) | ❌ | ✅ 30 min/month | ✅ 120 min/month | ✅ 300 min/month | ✅ Unlimited |
| **Video Call** (AI avatar) | ❌ | ❌ | ✅ 1 hour/month | ✅ 4 hours/month | ✅ 10 hours/month |
| **Camera Vision** (point & ask) | ❌ | ✅ 50 captures/month | ✅ 200 captures/month | ✅ 500 captures/month | ✅ Unlimited |
| **AI Meeting Room** (join calls) | ❌ | ❌ | ❌ | ✅ 5 hours/month | ✅ 20 hours/month |
| **Custom AI Voice** | ❌ | ❌ | ❌ | ✅ 1 voice clone | ✅ Up to 5 voices |
| **Custom AI Avatar** | ❌ | ❌ | ❌ | ❌ | ✅ White-label brand |
| **Multi-language Live** | BM/EN | BM/EN/CN | BM/EN/CN/TA | BM/EN/CN/TA+AR | All languages |
| **Meeting Transcription** | ❌ | ❌ | ✅ Auto-notes | ✅ Auto-actions | ✅ + full integration |
| **Call Recording & Replay** | ❌ | ✅ 7 days | ✅ 30 days | ✅ 90 days | ✅ Permanent |
| **AI Call Summary** | ❌ | ✅ Post-call summary | ✅ + action items | ✅ + CRM update | ✅ + full integration |
| **Screen Share + AI** | ❌ | ❌ | ❌ | ✅ Present + AI help | ✅ + co-pilot mode |

---

### 56A.5 Detailed Feature Specifications

#### 56A.5.1 Voice Chat (Push-to-Talk)

**User Experience:**
```
1. User buka AI BIJAN app → tap "AI Chat" button
2. Screen show AI avatar + big microphone button
3. User tekan butang mic → mula bercakap
   - "Berapa stock ayam hari ni?"
4. Lepas butang → AI process (0.5-1 saat)
5. AI jawab audio:
   - "Stok ayam hari ni ada 150kg. Ada 2 supplier tersedia — FGV dan Malaysia Poultry. Nak order?"
6. User boleh terus balas tanpa lepas butang (conversation mode)
```

**Technical Flow:**
```
Press & Hold Mic → Capture Audio Buffer
        ↓
Release → Send to STT Engine (Whisper)
        ↓
STT Output: "Berapa stock ayam hari ni?"
        ↓
Intent Detection: query_inventory(item="ayam")
        ↓
Query Database: SELECT * FROM inventory WHERE item='ayam' AND tenant_id=X
        ↓
LLM Generate Response: "Stok ayam hari ni ada 150kg..."
        ↓
TTS Convert: Audio waveform generation
        ↓
Play Audio to User Speaker
```

**Database Schema — Voice Chat Sessions:**

```sql
-- Voice chat sessions (push-to-talk conversations)
CREATE TABLE ai_voice_sessions (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    session_type ENUM('voice_chat','voice_call','video_call','meeting') NOT NULL,
    started_at TIMESTAMP NOT NULL,
    ended_at TIMESTAMP NULL,
    duration_seconds INT DEFAULT 0,
    status ENUM('active','completed','error','interrupted') DEFAULT 'active',
    model_used VARCHAR(100),
    stt_language CHAR(5) DEFAULT 'ms-MY',
    tts_voice_id BIGINT NULL,
    recording_url VARCHAR(500) NULL,
    transcript TEXT NULL,
    summary TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Voice chat messages (per turn in conversation)
CREATE TABLE ai_voice_messages (
    id BIGINT PRIMARY KEY,
    session_id BIGINT REFERENCES ai_voice_sessions(id),
    direction ENUM('user','ai') NOT NULL,
    transcript TEXT NOT NULL,
    intent VARCHAR(255) NULL,
    intent_params JSON NULL,
    action_taken VARCHAR(255) NULL,
    action_result JSON NULL,
    tokens_used INT DEFAULT 0,
    cost_usd DECIMAL(10,6) DEFAULT 0.000000,
    latency_ms INT NULL,
    recorded_at TIMESTAMP NOT NULL
);

-- AI voice profiles (custom voices per tenant)
CREATE TABLE ai_voice_profiles (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    voice_provider ENUM('elevenlabs','coqui','azure','aws') DEFAULT 'elevenlabs',
    voice_id VARCHAR(100) NOT NULL,
    language CHAR(5) DEFAULT 'ms-MY',
    accent VARCHAR(50) DEFAULT 'malaysian',
    gender ENUM('male','female','neutral') DEFAULT 'neutral',
    tone VARCHAR(50) DEFAULT 'professional',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP
);
```

#### 56A.5.2 Voice Call (Continuous Conversation)

**User Experience:**
```
1. User tekan "AI Call" → seperti buat call WhatsApp
2. AI answer dalam 2 saat — avatar muncul + audio live
3. Conversation continuous (tak perlu tekan butang)
   - User: "Saya nak tahu sales bulan ni macam mana?"
   - AI: "Sales Oktober sampai sekarang RM45,000. Naik 15% dari September. Top 3 produk..."
   - User: "Boleh compare dengan tahun lepas?"
   - AI: "Tahun lepas pada masa sama cuma RM38,000. Jadi growth 18.4%..."
4. User tekan "End Call" → AI bagi summary automatik
5. Summary disimpan ke CRM + email ke user
```

**Features:**
- **Auto VAD (Voice Activity Detection):** Detect bila user berhenti bercakap → AI respond automatically
- **Interrupt Handling:** User boleh interrupt AI tengah cakap → AI stop & dengar
- **Background Noise Suppression:** AI filter bunyi bising (traffic, kedai, construction)
- **Network Adaptation:** Low bandwidth → lower quality tapi still work
- **Call Transfer:** Kalau AI tak boleh handle → transfer ke human agent

**Use Cases:**
- Owner drive ke customer → call AI check inventory/sales
- Worker kat warehouse → hands-free call AI check order status
- Owner masak/tengok TV → casual chat with AI about business

#### 56A.5.3 Video Call (AI Avatar)

**User Experience:**
```
1. User pilih "Video Call dengan AI"
2. AI avatar muncul — wajah realistik + suara natural
3. Avatar ada expression (nod, smile, gesture) mengikut context
4. Video call seperti Zoom/WhatsApp call
5. Avatar boleh share screen (show charts, reports, products)
6. End call → AI generate meeting notes + action items
```

**Avatar Types:**
| Type | Description | Best For | Cost |
|------|-------------|----------|------|
| **2D Cartoon** | Animated character (low cost) | Casual chat, training | $0.02/min |
| **2D Realistic** | Human-like avatar (medium cost) | Customer service, sales | $0.05/min |
| **3D Photorealistic** | Near-human appearance (high cost) | Executive presentations | $0.10/min |
| **Brand Mascot** | Custom character per tenant (white-label) | Brand building | $0.08/min |

**Avatar Configuration (Admin Page):**
```sql
-- Tenant avatar configuration
CREATE TABLE ai_avatars (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('cartoon','realistic','photorealistic','mascot') DEFAULT 'realistic',
    model_file VARCHAR(500) NULL,
    base_image_url VARCHAR(500) NULL,
    expressions_json JSON NULL,
    default_pose VARCHAR(100) DEFAULT 'standing',
    background_type ENUM('solid','office','brand','custom') DEFAULT 'solid',
    background_url VARCHAR(500) NULL,
    voice_profile_id BIGINT REFERENCES ai_voice_profiles(id),
    personality_tone ENUM('professional','friendly','casual','formal') DEFAULT 'friendly',
    knowledge_base_slug VARCHAR(100) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP
);
```

#### 56A.5.4 Camera Vision (Point & Ask)

**User Experience:**
```
1. User buka kamera dalam AI BIJAN app
2. Point kamera ke:
   a) Produk di shelf → AI identify & show price, stock, description
   b) Dokumen/invois → AI extract data, enter into system
   c) Supplier → AI show rating, history, outstanding payments
   d) Staff → AI show profile, attendance, performance
3. User tanya audio: "Berapa harga ini?", "Siapa dia?", "Ada stock?"
4. AI jawab dengan visual context + database info
```

**Vision Use Cases:**

| Scenario | What User Points At | What AI Does | Data Source |
|----------|--------------------|--------------|-------------|
| **Inventory Check** | Product on shelf | Identify product, show stock level, suggest reorder | Inventory module |
| **Document Scan** | Invoice/receipt | Extract vendor, amount, date, tax | OCR + LLM parsing |
| **Customer Recognition** | Customer face | Show purchase history, preferences, loyalty points | CRM + Customer DB |
| **Staff ID** | Employee badge/card | Show profile, role, attendance, tasks | HRMS module |
| **Competitor Product** | Competitor's product | Compare features, pricing, suggest counter-strategy | Market intelligence |
| **Machine/Equipment** | Factory machine | Show maintenance schedule, fault diagnosis | Maintenance module |

**Database Schema — Camera Vision Captures:**

```sql
-- Camera vision captures (point & ask sessions)
CREATE TABLE ai_vision_captures (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    captured_at TIMESTAMP NOT NULL,
    image_url VARCHAR(500) NULL,
    video_clip_url VARCHAR(500) NULL,
    prompt_text TEXT NULL,
    vision_model VARCHAR(100) DEFAULT 'gpt-4v',
    vision_output TEXT NULL,
    extracted_data JSON NULL,
    action_triggered VARCHAR(255) NULL,
    confidence_score DECIMAL(5,4) NULL,
    tokens_used INT DEFAULT 0,
    cost_usd DECIMAL(10,6) DEFAULT 0.000000
);

-- Object recognition index (learned from captures)
CREATE TABLE ai_vision_objects (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    object_type ENUM('product','document','person','place','machine','other') NOT NULL,
    object_name VARCHAR(255) NOT NULL,
    image_embedding VECTOR(1536) NULL,  -- For similarity search
    metadata JSON NULL,
    first_seen_at TIMESTAMP NOT NULL,
    last_seen_at TIMESTAMP NOT NULL,
    capture_count INT DEFAULT 0
);
```

#### 56A.5.5 AI Meeting Room

**User Experience:**
```
1. User ada meeting dengan customer/supplier/team
2. AI join meeting sebagai participant (like Zoom bot)
3. AI roles during meeting:
   - 🎙️ Transcribe semua perbualan (real-time captions)
   - 📝 Take notes & highlight key decisions
   - 💡 Suggest responses/actions (private chat to user)
   - ⏰ Remind about deadlines, follow-ups
   - 📊 Pull up relevant data when discussed
4. Meeting selesai → AI auto-generate:
   - Full transcript
   - Action items with assignees & deadlines
   - Summary email to all participants
   - CRM updates (if customer meeting)
   - Task creation (if internal meeting)
```

**AI Meeting Features:**

| Feature | Description | Priority |
|---------|-------------|----------|
| **Live Transcription** | Real-time BM/EN transcription with speaker identification | P0 |
| **Sentiment Analysis** | Detect mood/tone of participants → alert user | P1 |
| **Action Item Extraction** | Auto-extract "you do this", "I'll send that" → create tasks | P0 |
| **Decision Logging** | Record all decisions made during meeting | P0 |
| **Data On-Demand** | When topic mentioned, AI pull relevant data to show user | P1 |
| **Follow-up Generator** | Auto-draft follow-up emails based on meeting content | P1 |
| **Multilingual Support** | Mix BM/EN/ZH → AI translate & transcribe accurately | P2 |
| **Meeting Scoring** | Rate meeting effectiveness (action items, decisions, attendance) | P2 |

**Database Schema — Meeting Intelligence:**

```sql
-- AI meeting recordings & transcripts
CREATE TABLE ai_meetings (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    meeting_type ENUM('customer','supplier','internal','training','other') NOT NULL,
    external_participants JSON NULL,  -- Names, emails of non-tenant participants
    scheduled_start TIMESTAMP,
    actual_start TIMESTAMP,
    actual_end TIMESTAMP,
    duration_minutes INT,
    recording_url VARCHAR(500) NULL,
    transcript_url VARCHAR(500) NULL,
    summary TEXT NULL,
    sentiment_overall DECIMAL(5,4) NULL,  -- 0=negative, 1=positive
    decisions_made TEXT NULL,
    action_items_count INT DEFAULT 0,
    status ENUM('in_progress','transcribing','completed','archived') DEFAULT 'in_progress',
    created_at TIMESTAMP
);

-- Meeting action items
CREATE TABLE ai_meeting_actions (
    id BIGINT PRIMARY KEY,
    meeting_id BIGINT REFERENCES ai_meetings(id),
    assigned_to_user_id BIGINT NULL,
    assigned_to_external_email VARCHAR(255) NULL,
    description TEXT NOT NULL,
    priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
    due_date DATE NULL,
    status ENUM('open','in_progress','completed','overdue') DEFAULT 'open',
    source_quote VARCHAR(500) NULL,  -- Exact quote from meeting
    created_at TIMESTAMP
);

-- Meeting decisions log
CREATE TABLE ai_meeting_decisions (
    id BIGINT PRIMARY KEY,
    meeting_id BIGINT REFERENCES ai_meetings(id),
    decision TEXT NOT NULL,
    rationale TEXT NULL,
    decided_by VARCHAR(255) NULL,
    effective_from DATE NULL,
    created_at TIMESTAMP
);
```

---

### 56A.6 Implementation Roadmap

```
Phase 1: Foundation (Months 1-3)
├── Week 1-4: Voice Chat (Push-to-Talk) MVP
│   ├── Integrate Whisper STT (open-source, self-hosted)
│   ├── Connect to existing LLM pipeline
│   ├── Implement basic TTS (Coqui, free)
│   └── Build mobile UI: big mic button + avatar display
│
├── Week 5-8: Voice Call (Continuous)
│   ├── Implement VAD (Voice Activity Detection)
│   ├── Add continuous conversation mode
│   ├── Background noise suppression
│   └── Call recording & storage
│
├── Week 9-12: Basic Video Call
│   ├── WebRTC integration (Coturn STUN/TURN)
│   ├── Basic 2D avatar (static image + lip-sync)
│   ├── Screen share capability
│   └── Mobile app video support
│
Phase 2: Enhancement (Months 4-6)
├── Advanced Avatar (Week 13-16)
│   ├── Dynamic 2D avatar with expressions (Live2D)
│   ├── Multiple avatar options per tenant
│   ├── Custom brand mascot upload
│   └── Background customization
│
├── Camera Vision (Week 17-20)
│   ├── Integrate GPT-4V / Claude Vision API
│   ├── Object recognition for inventory
│   ├── Document OCR + data extraction
│   └── Customer recognition (opt-in, privacy-compliant)
│
├── AI Meeting Room (Week 21-24)
│   ├── Zoom/Google Meet integration (bot join)
│   ├── Live transcription with speaker ID
│   ├── Action item extraction
│   └── Post-meeting summary automation
│
Phase 3: Scale & Polish (Months 7-12)
├── Multi-language Live (Month 7-8)
│   ├── Real-time translation during calls
│   ├── Mixed language detection (BM/EN/CN/TA)
│   ├── Accent adaptation
│   └── Cultural context awareness
│
├── Voice Cloning (Month 9-10)
│   ├── Tenant can record 5-min sample → custom voice
│   ├── Brand-consistent voice for customer-facing use
│   ├── Multiple voice profiles per tenant
│   └── Voice authentication for security
│
├── Analytics & Insights (Month 11-12)
│   ├── Call analytics dashboard
│   ├── Customer sentiment trends
│   ├── Common questions heatmap
│   ├── AI performance metrics
│   └── ROI calculation (time saved, deals closed)
│
Phase 4: Advanced (Year 2)
├── Emotional AI
│   ├── Detect emotion from voice tone
│   ├── Adjust AI response style accordingly
│   ├── Escalate to human if frustrated detected
│
├── Predictive Assistance
│   ├── AI anticipate what user will ask
│   ├── Pre-load relevant data before question
│   ├── Proactive suggestions during conversation
│
└── AR Integration
   ├── AR glasses support (Meta Ray-Ban, Apple Vision)
   ├── Hands-free AI assistant in field
   ├── Visual overlay with AI insights
```

---

### 56A.7 Infrastructure Requirements

```
┌─────────────────────────────────────────────────────────────┐
│              INFRASTRUCTURE FOR AI LIVE INTERACTION           │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Compute:                                                   │
│  ├── 4x Application Servers (WebRTC signaling + Laravel)   │
│  ├── 2x STT Workers (Whisper large-v3, GPU required)       │
│  ├── 2x TTS Workers (ElevenLabs or self-hosted Coqui)      │
│  └── 1x Avatar Render Server (Live2D/HeyGen integration)   │
│                                                             │
│  Storage:                                                   │
│  ├── R2 Storage: Call recordings (30-day retention)        │
│  ├── R2 Storage: Video clips (camera vision captures)      │
│  └── PostgreSQL: Transcripts, summaries, action items      │
│                                                             │
│  Network:                                                   │
│  ├── Coturn STUN/TURN servers (3 instances)                │
│  ├── Cloudflare CDN for media distribution                 │
│  └── WebSocket servers (Reverb) for signaling              │
│                                                             │
│  AI Services:                                               │
│  ├── Whisper (self-hosted or serveras.click)               │
│  ├── LLM API (existing serveras.click pipeline)            │
│  ├── ElevenLabs / Coqui TTS                                │
│  ├── GPT-4V / Claude Vision (for camera)                   │
│  └── Live2D / HeyGen (for avatar rendering)                │
│                                                             │
│  Estimated Monthly Cost:                                    │
│  ├── Compute: RM 2,500/month                               │
│  ├── Storage: RM 500/month                                 │
│  ├── Network (WebRTC + CDN): RM 1,000/month                │
│  ├── AI Services: Variable (per-minute billing)            │
│  └── Total Base: ~RM 4,000/month + variable AI costs       │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

### 56A.8 Privacy, Security & Compliance

```
┌─────────────────────────────────────────────────────────────┐
│              PRIVACY & SECURITY FRAMEWORK                     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Consent & Disclosure:                                      │
│  ├── Always show "You are talking to AI" indicator          │
│  ├── Never disguise AI as human                             │
│  ├── Record all calls with explicit consent banner          │
│  └── Allow users to opt-out of call recording               │
│                                                             │
│  Data Protection:                                           │
│  ├── All recordings encrypted at rest (AES-256)             │
│  ├── Transient audio in memory only (never persist unless   │
│  │  -recorded by user)                                       │
│  ├── Automatic deletion after retention period              │
│  └── Data handling designed to support PDPA compliance      │
│  │   (final compliance claim requires legal review)         │
│                                                             │
│  Access Control:                                            │
│  ├── Only tenant admin can enable/disable AI features       │
│  ├── Users can see their own call history                   │
│  ├── Admin can view team calls (with notification)          │
│  └── External participants NOT stored without consent       │
│                                                             │
│  Camera Privacy:                                            │
│  ├── Camera only active when user explicitly opens it       │
│  ├── No background/recording camera access                  │
│  ├── Visual data processed in real-time, not stored         │
│  │  (unless user saves capture)                              │
│  └── Face recognition OPT-IN ONLY with explicit consent     │
│                                                             │
│  Meeting Privacy:                                           │
│  ├── All participants notified "AI is transcribing"         │
│  ├── External participants must consent to recording        │
│  ├── Transcript accessible only to meeting organizer        │
│  └── No AI listening outside scheduled meetings             │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

### 56A.9 Monetization Strategy

```
┌─────────────────────────────────────────────────────────────┐
│              MONETIZATION MODEL                               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Included in Subscription (no extra charge):                │
│  ├── Voice Chat: 30-600 min/month (tier-dependent)         │
│  ├── Voice Call: 0-300 min/month (Professional+)           │
│  ├── Video Call: 0-10 hours/month (Business+)              │
│  └── Camera Vision: 0-500 captures/month (Professional+)   │
│                                                             │
│  Additional Revenue Streams:                                │
│  ├── AI Credit Top-up: Extra minutes beyond included quota │
│  │   Price: RM0.15/minute (voice), RM0.50/minute (video)   │
│  ├── Custom Voice Clone: RM99 one-time setup               │
│  ├── Custom Avatar Design: RM299 one-time design           │
│  ├── Meeting Room Add-on: RM49/month per seat              │
│  └── Enterprise Unlimited: Negotiable flat rate            │
│                                                             │
│  ROI Justification for Customers:                           │
│  ├── 1 hour AI customer service = save 1 staff member      │
│  │   Cost: RM1,500/month staff vs RM888 AI subscription    │
│  ├── 5 hours AI training/month = 20 hours saved            │
│  │   Equivalent to 5 hours productivity gain               │
│  └── Camera vision inventory check = 80% faster            │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

### 56A.10 Competitive Positioning

| Feature | AI BIJAN | Shopify | HubSpot | Zoho | Odoo |
|---------|----------|---------|---------|------|------|
| **AI Voice Chat** | ✅ Native | ❌ | ❌ | ❌ | ❌ |
| **AI Video Call** | ✅ Avatar | ❌ | ❌ | ❌ | ❌ |
| **AI Camera Vision** | ✅ Point & Ask | ❌ | ❌ | ❌ | ❌ |
| **AI Meeting Room** | ✅ Join & Assist | ❌ | Basic bot | ❌ | ❌ |
| **WhatsApp Integration** | ✅ Native | ❌ | Paid app | ✅ | ✅ |
| **Malay Language AI** | ✅ Native | ❌ | Limited | Limited | ❌ |
| **All-in-One Platform** | ✅ Yes | ❌ | Partial | Partial | Partial |

**KEY INSIGHT:** AI BIJAN adalah SAHAJA platform yang combine AI voice/video/camera/meeting dengan full business suite. Competitors ada chatbot atau meeting bot, tapi tak ada yang offer complete live interaction experience — apalagi dengan support Bahasa Melayu native.

---

### 56A.11 Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Weekly Active Users (Voice)** | > 40% of tenants | Users who use voice chat weekly |
| **Average Session Duration** | > 5 minutes | Per voice/chat session |
| **User Satisfaction (AI Voice)** | > 4.2/5.0 | Post-call survey |
| **Time Saved per User** | > 3 hours/week | Estimated from usage analytics |
| **AI Task Completion Rate** | > 80% | Tasks completed without human handoff |
| **Voice-to-Text Accuracy** | > 92% (BM/EN) | WER (Word Error Rate) measurement |
| **Call Drop Rate** | < 2% | Failed connections / total attempts |
| **Avatar Naturalness Score** | > 3.8/5.0 | User perception study |
| **Meeting Adoption Rate** | > 30% of Business+ tenants | Weekly meeting room usage |
| **Camera Vision Usage** | > 50 captures/active user/month | Active power users |

---

### 56A.12 Risk Mitigation

| Risk | Impact | Mitigation Strategy |
|------|--------|-------------------|
| **High infrastructure cost** | Profit margin pressure | Start with voice-only, add video later; self-host STT/TTS |
| **Poor voice quality in rural areas** | User frustration | Fallback to text; optimize for low bandwidth |
| **Privacy concerns** | Legal/compliance risk | Clear consent flows; PDPA compliance; data minimization |
| **AI hallucination during calls** | Wrong business advice | Confidence thresholds; escalate to human when uncertain |
| **User habit resistance** | Low adoption | Simple UI; WhatsApp-like interaction; training videos |
| **Competitor copycat** | Loss of differentiation | First-mover advantage; patent key innovations; build network effects |
| **Regulatory changes** | Operational disruption | Monitor telecom/AI regulations; flexible architecture |

---

> **BAB 56A dicipta pada 19 Oktober 2026.**  
> **Menambah dimensi baru: AI Live Interaction (Voice, Video, Camera, Meeting).**  
> **Status: Perancangan strategik — panduan pelaksanaan Fasa 1-4.**  
> **Unfair Advantage: Hanya AI BIJAN combine full business OS + native AI voice/video/camera untuk emerging markets.**

---

## BAB 56B — Mobile Applications: Android & iOS

### 56B.1 Visi — Mengapa Mobile Apps?

```
Current situation for Malaysian SMEs:
├── Web-only platform → Tak boleh guna on-the-go
├── Owner bisnes selalu mobile → kat pasar, kat customer, dalam kereta
├── Staff warehouse/field → Tak ada laptop, cuma phone
├── WhatsApp → Semua orang check phone 100x sehari
└── Result: Platform tanpa mobile app = tak accessible bila paling needed

AI BIJAN Solution:
├── Native mobile apps (Android + iOS) — Flutter single codebase
├── TWO app experiences dalam satu app (role-based):
│   ├── Tenant Admin/Pemilik App → Manage bisnes dari phone
│   └── Staff App → Buat task harian, scan, approve dari phone
├── Offline mode → Still work tanpa internet, sync bila online
├── Push notifications → Real-time alerts (order, payment, reminder)
└── All in one download — Google Play + Apple App Store
```

**Positioning:** *"Manage your entire business from your pocket — anywhere, anytime, even offline."*

---

### 56B.2 User Hierarchy & Access Model

```
┌─────────────────────────────────────────────────────────────┐
│              USER ACCESS HIERARCHY                           │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  MASTER ADMIN (Platform Owner)                              │
│  ├── Access: Web Admin Panel (Filament PHP)                 │
│  ├── Scope: ALL tenants across platform                     │
│  └── Mobile: NOT applicable (web-only admin)                │
│                                                             │
│  SUPER ADMIN (Per-Tenant Administrator)                     │
│  ├── Access: Web Dashboard + Mobile App (Admin Mode)        │
│  ├── Scope: Entire tenant organization                      │
│  └── Role: Tenant owner / CEO / Director                    │
│                                                             │
│  TENANT ADMIN (Department Manager)                          │
│  ├── Access: Web Dashboard + Mobile App (Admin Mode)        │
│  ├── Scope: Specific department/team                        │
│  └── Role: Manager, Supervisor, Department Head             │
│                                                             │
│  APP & WEB USER — PEMILIK (Tenant Owner/Business Owner)     │
│  ├── Access: Mobile App (Owner Mode) + Web Dashboard        │
│  ├── Scope: Full business visibility                        │
│  └── Capabilities: Approve, view reports, manage staff      │
│                                                             │
│  APP & WEB USER — STAFF (Employee/Worker)                   │
│  ├── Access: Mobile App (Staff Mode) + Web (limited)        │
│  ├── Scope: Assigned tasks, own records                     │
│  └── Capabilities: Execute tasks, scan, report, request     │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

#### Access Matrix by User Type

| Feature | Pemilik (Owner) | Staff (Employee) |
|---------|----------------|------------------|
| **Mobile App Access** | ✅ Full access | ✅ Task-specific access |
| **Web Dashboard** | ✅ Full access | ⚠️ Limited (task-focused) |
| **View Reports** | ✅ All reports | ❌ Own performance only |
| **Approve Transactions** | ✅ Yes | ❌ Cannot approve |
| **Manage Staff** | ✅ Add/remove/assign | ❌ No |
| **Execute Tasks** | ✅ Yes (optional) | ✅ Primary function |
| **AI Chat/Call** | ✅ Premium features | ✅ Basic features |
| **Camera Scan** | ✅ Full access | ✅ Work-related only |
| **Push Notifications** | ✅ All alerts | ✅ Task alerts only |
| **Offline Mode** | ✅ Full offline | ✅ Task offline |

---

### 56B.3 App Architecture — Single App, Dual Modes

```
┌─────────────────────────────────────────────────────────────┐
│           AI BIJAN MOBILE APP (Flutter)                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ONE App Download → Auto-detect User Role → Show Correct UI │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              APP LAUNCH FLOW                         │   │
│  │                                                       │   │
│  │  User opens app → Login with credentials             │   │
│  │       ↓                                               │   │
│  │  System checks: user.role + user.tenant_id           │   │
│  │       ↓                                               │   │
│  │  IF role = 'pemilik'/'admin'/'super_admin'           │   │
│  │      → Load OWNER MODE UI                            │   │
│  │  IF role = 'staff'/'employee'                        │   │
│  │      → Load STAFF MODE UI                            │   │
│  │       ↓                                               │   │
│  │  Home screen shows role-appropriate dashboard        │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌──────────────────┬──────────────────┐                    │
│  │   OWNER MODE     │    STAFF MODE    │                    │
│  │                  │                  │                    │
│  │ 📊 Dashboard     │ ✅ My Tasks      │                    │
│  │ 💰 Sales         │ 📦 Receive       │                    │
│  │ 👥 Staff Mgmt    │ 🏷️  Scan        │                    │
│  │ 📈 Reports       │ 📝 Report        │                    │
│  │ 💬 AI Chat/Call  │ 🔔 Notify Me     │                    │
│  │ ⚙️ Settings      │ 👤 My Profile    │                    │
│  │                  │                  │                    │
│  │ "Manage everything"│ "Do my job"    │                    │
│  └──────────────────┴──────────────────┘                    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

#### Technical Architecture

```dart
// Flutter App Structure (simplified)
lib/
├── main.dart                          # App entry point
├── config/
│   ├── app_config.dart                # App settings, API endpoints
│   ├── theme.dart                     # Material 3 theme
│   └── constants.dart                 # App-wide constants
├── auth/
│   ├── login_screen.dart              # Login with email/password
│   ├── session_manager.dart           # JWT token management
│   └── biometric_auth.dart            # Fingerprint/Face ID
├── models/
│   ├── user_model.dart                # User (role, tenant, permissions)
│   ├── tenant_model.dart              # Tenant info
│   ├── order_model.dart               # Order data
│   ├── product_model.dart             # Product catalog
│   └── task_model.dart                # Staff tasks
├── services/
│   ├── api_service.dart               # REST API client (Dio)
│   ├── realtime_service.dart          # WebSocket (Laravel Reverb)
│   ├── storage_service.dart           # Hive/SQLite local DB
│   ├── notification_service.dart      # Firebase Cloud Messaging
│   ├── camera_service.dart            # Camera X plugin
│   ├── barcode_service.dart           # Barcode/QR scanner
│   ├── ai_service.dart                # AI chat/call integration
│   └── sync_service.dart              # Offline sync engine
├── providers/
│   ├── auth_provider.dart             # State management (Riverpod)
│   ├── dashboard_provider.dart        # Dashboard data
│   ├── task_provider.dart             # Staff tasks
│   └── ai_provider.dart               # AI interaction state
├── screens/
│   ├── home/
│   │   ├── owner_dashboard.dart       # Owner home screen
│   │   └── staff_dashboard.dart       # Staff home screen
│   ├── sales/
│   │   ├── pos_screen.dart            # Mobile POS
│   │   ├── orders_list.dart           # Orders view
│   │   └── order_detail.dart          # Order details
│   ├── inventory/
│   │   ├── stock_view.dart            # Stock levels
│   │   ├── scan_screen.dart           # Barcode/QR scan
│   │   └── stock_take.dart            # Stock taking
│   ├── finance/
│   │   ├── invoices_list.dart         # Invoices
│   │   ├── payments.dart              # Payment recording
│   │   └── reports.dart               # Financial reports
│   ├── hrms/
│   │   ├── attendance.dart            # Clock in/out
│   │   ├── leave_request.dart         # Leave application
│   │   └── staff_list.dart            # Team directory
│   ├── ai/
│   │   ├── ai_chat.dart               # Voice/text chat
│   │   ├── ai_call.dart               # Voice/video call
│   │   └── ai_vision.dart             # Camera vision
│   └── settings/
│       ├── profile_settings.dart      # User settings
│       ├── tenant_settings.dart       # Tenant config (owner only)
│       └── about_screen.dart          # App info, version
├── widgets/
│   ├── common/
│   │   ├── custom_app_bar.dart        # Reusable app bar
│   │   ├── data_card.dart             # Data display card
│   │   └── empty_state.dart           # Empty list state
│   ├── owner/
│   │   ├── sales_chart.dart           # Sales visualization
│   │   ├── kpi_cards.dart             # KPI summary cards
│   │   └── staff_overview.dart        # Team status
│   └── staff/
│       ├── task_card.dart             # Task assignment card
│       ├── quick_action.dart          # Quick action buttons
│       └── scan_result.dart           # Scan result display
└── utils/
    ├── validators.dart                # Form validation
    ├── formatters.dart                # Date/currency formatting
    └── helpers.dart                   # Utility functions
```

---

### 56B.4 Owner Mode — Feature Matrix

| Module | Features | Priority |
|--------|----------|----------|
| **Dashboard** | Real-time sales, revenue, orders today, low stock alerts, pending approvals | P0 |
| **Sales/POS** | View orders, process refund, issue credit note, mobile POS (if no hardware) | P0 |
| **Inventory** | Check stock levels, approve purchase orders, view stock movement | P0 |
| **Finance** | View invoices, record payments, approve expenses, profit/loss summary | P0 |
| **HRMS** | View attendance, approve leave requests, view payroll summary | P1 |
| **Customers** | Customer directory, purchase history, loyalty points, notes | P0 |
| **Reports** | Daily sales, monthly revenue, top products, customer analytics, export PDF | P0 |
| **AI Assistant** | Voice/text chat, voice call, camera vision, meeting transcription | P1 |
| **Settings** | Tenant config, staff management, subscription, billing, integrations | P1 |
| **Notifications** | Push alerts for orders, payments, low stock, reminders, AI suggestions | P0 |

#### Owner Dashboard Screens

```
┌─────────────────────────────────────────────┐
│  AI BIJAN                    🔔  👤 Owner   │
├─────────────────────────────────────────────┤
│                                             │
│  📊 TODAY'S OVERVIEW                        │
│  ┌─────────┬─────────┬─────────┐           │
│  │ Revenue │ Orders  │ Customers│           │
│  │ RM2,450 │   28    │   15    │           │
│  │ +12% ↑  │  -3% ↓  │ +8% ↑   │           │
│  └─────────┴─────────┴─────────┘           │
│                                             │
│  ⚡ QUICK ACTIONS                           │
│  [💰 Record Payment] [📦 New Order]        │
│  [📋 Approve PO]     [👤 Add Staff]        │
│                                             │
│  📈 SALES TREND (Last 7 days)               │
│  │▌   ▌    ▌▌  ▌▌▌ ▌▌▌ ▌                  │
│  │▌▌▌ ▌▌ ▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌▌  │
│  │ Mon Tue Wed Thu Fri Sat Sun             │
│                                             │
│  ⚠️ ALERTS (3)                              │
│  • Low stock: Ayam Broiler (5kg left)      │
│  • Pending approval: PO #1234 (RM2,500)    │
│  • Invoice overdue: ABC Sdn Bhd (RM800)    │
│                                             │
│  💬 AI SUGGESTION                           │
│  "Sales predicted to drop tomorrow (Sunday  │
│   is usually slow). Consider running a     │
│   promo?" [YES, TELL ME MORE]              │
│                                             │
│  ─────────────────────────────────────────  │
│  🏠        📊        📦        💰        ⚙️  │
│  Home    Sales    Inventory  Finance  More  │
└─────────────────────────────────────────────┘
```

---

### 56B.5 Staff Mode — Feature Matrix

| Module | Features | Priority |
|--------|----------|----------|
| **My Tasks** | Today's assignments, checklist, deadline tracking | P0 |
| **POS/Order** | Take orders, process payments, print/send receipts | P0 |
| **Scan** | Barcode scan, QR scan, receive goods, stock count | P0 |
| **Attendance** | Clock in/out, break timer, leave request | P0 |
| **Customer** | Look up customer, check loyalty points, take new customer info | P1 |
| **Inventory** | Check stock, request transfer, report damaged items | P1 |
| **AI Assistant** | Voice/text chat for help, camera scan for product lookup | P2 |
| **Messages** | Internal messaging with supervisor/owner | P1 |
| **Profile** | Personal info, schedule, performance stats | P1 |
| **Notifications** | Task alerts, approval updates, team messages | P0 |

#### Staff Dashboard Screens

```
┌─────────────────────────────────────────────┐
│  AI BIJAN Staff              🔔  👤 Ahmad   │
├─────────────────────────────────────────────┤
│                                             │
│  ☀️ GOOD MORNING, AHMAD!                    │
│  📅 Tuesday, 19 October 2026                │
│  🕐 Shift: 9:00 AM - 6:00 PM                │
│  ✅ Clock-in: 8:55 AM (on time)             │
│                                             │
│  ✅ MY TASKS (5 today)                      │
│  ┌──────────────────────────────────────┐   │
│  │ ☐ Receive delivery from FGV          │   │
│  │    Due: 11:00 AM  🔴 URGENT          │   │
│  ├──────────────────────────────────────┤   │
│  │ ☐ Count stock: Chicken products      │   │
│  │    Due: 2:00 PM  🟡 NORMAL           │   │
│  ├──────────────────────────────────────┤   │
│  │ ☐ Prepare order #1230 for pickup     │   │
│  │    Due: 12:30 PM  🟡 NORMAL          │   │
│  ├──────────────────────────────────────┤   │
│  │ ☐ Clean & sanitize workstation       │   │
│  │    Due: 5:30 PM  🟢 LOW              │   │
│  └──────────────────────────────────────┘   │
│                                             │
│  ⚡ QUICK ACTIONS                           │
│  [📦 Take Order] [📷 Scan Item]             │
│  [✅ Clock Out]  [📝 Report Issue]          │
│                                             │
│  📊 TODAY'S STATS                           │
│  • Orders processed: 12                     │
│  • Items scanned: 87                        │
│  • Customer interactions: 8                 │
│                                             │
│  ─────────────────────────────────────────  │
│  🏠        ✅        📷        📋        👤  │
│  Home    Tasks     Scan     Tasks   Profile │
└─────────────────────────────────────────────┘
```

---

### 56B.6 Core Features — Both Modes

#### 56B.6.1 Authentication & Security

| Feature | Description |
|---------|-------------|
| **Login** | Email/phone + password, or OTP via WhatsApp/SMS |
| **Biometric** | Fingerprint (Android) / Face ID (iOS) for quick login |
| **Session** | JWT token, auto-refresh, 30-min idle timeout |
| **Device Binding** | Account linked to registered device(s) |
| **Remote Wipe** | Admin can revoke access remotely |
| **PIN Lock** | Optional app-level PIN for extra security |
| **Failed Attempts** | Lock after 5 failed attempts, require password reset |

#### 56B.6.2 Push Notifications (Firebase Cloud Messaging)

| Event | Owner Gets | Staff Gets |
|-------|-----------|-----------|
| **New Order** | ✅ Alert | ✅ Alert (if assigned) |
| **Payment Received** | ✅ Alert | ❌ |
| **Low Stock Alert** | ✅ Alert | ❌ |
| **Invoice Overdue** | ✅ Alert | ❌ |
| **Staff Check-in** | ✅ Alert | ❌ |
| **Task Assigned** | ❌ | ✅ Alert |
| **Approval Required** | ✅ Alert | ❌ |
| **System Maintenance** | ✅ Alert | ✅ Alert |
| **AI Suggestion** | ✅ Alert | ❌ |
| **Team Message** | ✅ Alert | ✅ Alert |

**Notification Channels:**
- Firebase Cloud Messaging (FCM) — primary
- WhatsApp Business API — critical alerts (payment, high-value orders)
- SMS — fallback if FCM fails
- In-app badge counter — persistent unread count

#### 56B.6.3 Offline Mode

```
┌─────────────────────────────────────────────────────────────┐
│              OFFLINE ARCHITECTURE                            │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  When Internet Available:                                   │
│  ├── App syncs with server (real-time via WebSocket)       │
│  ├── Local database updated with latest data               │
│  └── Cache frequently accessed data                        │
│                                                             │
│  When Internet Lost:                                        │
│  ├── App switches to offline mode automatically             │
│  ├── Local database becomes TEMPORARY operational store     │
│  │   (NOT final source of truth — server remains authoritative) │
│  ├── User can still perform core tasks                     │
│  └── Changes queued for sync when back online              │
│                                                             │
│  What Works Offline:                                        │
│  ├── View cached data (products, customers, orders)        │
│  ├── Create new orders (sync when online)                   │
│  ├── Process POS transactions (receipt stored locally)     │
│  ├── Scan barcodes/QR codes                                │
│  ├── Take photos (for document scan, damage report)        │
│  ├── Attendance clock-in/out                               │
│  └── Draft messages/notes                                  │
│                                                             │
│  What Requires Internet:                                    │
│  ├── Login/authentication                                    │
│  ├── AI chat/call features                                 │
│  ├── Real-time notifications                               │
│  ├── Viewing live reports                                  │
│  └── Syncing with other devices                            │
│                                                             │
│  Sync Engine:                                               │
│  ├── Automatic sync when connection restored               │
│  ├── Conflict resolution policy per domain:                │
│  │   • Financial data (payments, invoices): SERVER WINS    │
│  │   • Inventory counts: SERVER WINS (prevent oversell)    │
│  │   • Orders created offline: LAST WRITE WINS + audit log │
│  │   • Cached reference data (products, customers): LOCAL  │
│  ├── Idempotency keys on all sync operations               │
│  ├── Pending/sync-failed queue with manual retry           │
│  ├── Manual sync button: pull-to-refresh                   │
│  └── Sync queue: shows pending operations                  │
│                                                             │
│  ⚠️ IMPORTANT:                                                │
│  Local database is ONLY a temporary cache for allowed       │
│  offline operations. Server is ALWAYS the authoritative     │
│  source of record. No data leaves the system as "final"     │
│  until successfully synced and acknowledged by server.      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Local Database Schema (SQLite/Hive):**

```sql
-- Local cache tables (mirrored from server)
CREATE TABLE local_orders (
    id TEXT PRIMARY KEY,           -- UUID from server
    tenant_id INTEGER,
    order_number TEXT,
    customer_name TEXT,
    total_amount REAL,
    status TEXT,
    created_at TEXT,
    synced_with_server BOOLEAN DEFAULT 0,
    last_modified TEXT
);

CREATE TABLE local_products (
    id TEXT PRIMARY KEY,
    name TEXT,
    sku TEXT,
    current_stock REAL,
    unit_price REAL,
    image_url TEXT,
    synced_with_server BOOLEAN DEFAULT 0,
    last_synced_at TEXT
);

CREATE TABLE local_pending_sync (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    table_name TEXT NOT NULL,       -- Which table this record belongs to
    record_id TEXT NOT NULL,        -- Record ID from local table
    operation TEXT NOT NULL,        -- INSERT, UPDATE, DELETE
    payload_json TEXT NOT NULL,     -- Full record data as JSON
    priority INTEGER DEFAULT 0,     -- Higher = sync first
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    retry_count INTEGER DEFAULT 0,
    last_error TEXT
);
```

---

### 56B.7 AI Features in Mobile App

#### 56B.7.1 AI Chat (In-App)

| Feature | Owner | Staff |
|---------|-------|-------|
| Text chat with AI | ✅ | ✅ |
| Voice chat (push-to-talk) | ✅ | ✅ |
| AI suggest actions | ✅ | ✅ (task-focused) |
| AI generate reports | ✅ | ❌ |
| AI answer business questions | ✅ | ✅ (work-related) |

#### 56B.7.2 AI Voice Call (From App)

| Feature | Owner | Staff |
|---------|-------|-------|
| Voice call with AI | ✅ Included quota | ❌ |
| Camera vision | ✅ Included quota | ✅ Limited |
| AI meeting transcription | ✅ (Business+) | ❌ |

#### 56B.7.3 Camera Vision (Point & Ask)

**Owner Use Cases:**
- Point at competitor's shop → AI analyze & suggest pricing
- Point at financial report → AI explain trends
- Point at market → AI estimate foot traffic

**Staff Use Cases:**
- Point at product → AI show price, description, stock
- Point at receipt/invoice → AI extract data, enter into system
- Point at damaged item → AI log damage report with photo

---

### 56B.8 Development Roadmap

```
Phase 1: Foundation (Months 1-3)
├── Month 1: Project Setup
│   ├── Initialize Flutter project
│   ├── Configure CI/CD (GitHub Actions)
│   ├── Set up Firebase (FCM, Crashlytics)
│   ├── Design design system (Material 3)
│   └── Implement authentication flow
│
├── Month 2: Core Features
│   ├── Owner Dashboard (basic)
│   ├── Staff Dashboard (basic)
│   ├── Push notifications
│   ├── Offline mode skeleton
│   └── API integration (REST)
│
├── Month 3: Sales & Inventory
│   ├── Mobile POS
│   ├── Order management
│   ├── Product catalog view
│   ├── Barcode/QR scanning
│   └── Basic offline support
│
Phase 2: Enhancement (Months 4-6)
├── Month 4: Finance & HRMS
│   ├── Invoice viewing & payment recording
│   ├── Attendance clock-in/out
│   ├── Leave request workflow
│   ├── Financial reports (summary)
│   └── Approval workflows
│
├── Month 5: AI Integration
│   ├── In-app AI chat
│   ├── Voice chat (push-to-talk)
│   ├── Camera vision (point & ask)
│   ├── AI suggestions on dashboard
│   └── Smart notifications
│
├── Month 6: Polish & Testing
│   ├── Advanced offline mode
│   ├── Biometric authentication
│   ├── Performance optimization
│   ├── Accessibility (WCAG)
│   └── Beta testing with 50 tenants
│
Phase 3: Launch (Months 7-9)
├── Month 7: Beta Release
│   ├── Google Play Beta (internal track)
│   ├── Apple TestFlight
│   ├── Collect feedback from beta users
│   ├── Fix critical bugs
│   └── Performance tuning
│
├── Month 8: Public Beta
│   ├── Google Play (closed beta)
│   ├── Apple App Store (beta review)
│   ├── Marketing materials
│   ├── Tutorial/onboarding flow
│   └── Analytics integration
│
├── Month 9: Official Launch
│   ├── Google Play (full release)
│   ├── Apple App Store (full release)
│   ├── Press release
│   ├── Social media campaign
│   └── Influencer partnerships
│
Phase 4: Growth (Months 10-12)
├── Voice & Video Call integration
├── AI Meeting Room (mobile participant)
├── AR features (product visualization)
├── Widget support (home screen widgets)
├── Wearable support (smartwatch notifications)
└── Multi-language UI expansion
```

---

### 56B.9 Technology Stack — Mobile

| Component | Technology | Reason |
|-----------|------------|--------|
| **Framework** | Flutter 3.x | Single codebase for Android + iOS, near-native performance |
| **Language** | Dart | Easy to learn, strong typing, good ecosystem |
| **State Management** | Riverpod | Modern, testable, scalable |
| **HTTP Client** | Dio | Interceptors, retries, timeouts, multipart |
| **WebSocket** | web_socket_channel | Laravel Reverb integration |
| **Local Storage** | Hive + SQLite | Hive for key-value (settings), SQLite for relational data |
| **Offline Sync** | Custom sync engine | Queue-based with conflict resolution |
| **Push Notifications** | Firebase Cloud Messaging | Cross-platform, reliable |
| **Barcode Scanner** | mobile_scanner | Fast, accurate, supports all formats |
| **Camera** | camera + image_picker | High-quality capture |
| **Biometric Auth** | local_auth | Fingerprint (Android), Face ID (iOS) |
| **Charts** | fl_chart | Beautiful, customizable charts |
| **Navigation** | go_router | Declarative routing, deep linking |
| **Dependency Injection** | get_it + injectable | Clean architecture |
| **Testing** | flutter_test + mockito | Unit, widget, integration tests |
| **CI/CD** | GitHub Actions + Fastlane | Automated build & deploy |
| **Crash Reporting** | Firebase Crashlytics | Real-time crash tracking |
| **Analytics** | Firebase Analytics + Mixpanel | User behavior tracking |

---

### 56B.10 App Store Strategy

#### Google Play Store (Android)

| Detail | Value |
|--------|-------|
| **Developer Account** | Bijan Digital Marketing Sdn Bhd |
| **Registration Fee** | $25 (one-time) |
| **Target SDK** | API 36 (Android 16) — semak versi terkini sebelum setiap submission |
| **Minimum SDK** | API 26 (Android 8.0) |
| **App Bundle** | AAB (Android App Bundle) |
| **Languages** | English, Bahasa Melayu, Chinese (Simplified) |
| **Category** | Business > Business Tools |
| **Content Rating** | Everyone |
| **Pricing** | Free (with in-app purchases for premium features) |

#### Apple App Store (iOS)

| Detail | Value |
|--------|-------|
| **Developer Account** | Bijan Digital Marketing Sdn Bhd |
| **Registration Fee** | $99/year |
| **Target iOS** | iOS 16+ |
| **Minimum iOS** | iOS 14+ |
| **Bundle ID** | com.bijandigital.aibijan |
| **Languages** | English, Bahasa Melayu, Chinese (Simplified) |
| **Category** | Business |
| **Age Rating** | 4+ |
| **Pricing** | Free |

#### App Store Optimization (ASO)

```
Google Play / Apple App Store Listing:

App Name: AI BIJAN — Bisnes Dalam Poket Anda
Short Description: POS, Inventory, CRM, AI Assistant — Semua dalam satu!
Full Description:

"AI BIJAN adalah semua-dalam-satu platform perniagaan direka khas untuk SME Malaysia.

✨ CIRI UTAMA:
• Point of Sale (POS) — Proses transaksi dengan pantas
• Pengurusan Inventori — Track stock secara real-time
• CRM & Pelanggan — Kenali pelanggan anda lebih baik
• Invois & Kewangan — Track income & expense
• AI Assistant — Bercakap dengan AI untuk nasihat bisnes
• Offline Mode — Masih boleh guna tanpa internet

👔 UNTUK PEMILIK BISNES:
• Dashboard real-time — Sales, revenue, alerts
• Laporan lengkap — Export ke PDF/Excel
• Manage staff — Assign tasks, track attendance
• AI voice call — Dapatkan insights dari phone

👨‍💼 UNTUK STAFF:
• My Tasks — Lihat tugas harian
• Scan items — Barcode & QR scanner
• Clock in/out — Attendance dari phone
• Quick order — Ambil order dengan cepat

🤖 AI POWERED:
• Chat dengan AI — Soalan bisnes, jawapan pantas
• Camera vision — Scan produk, dapatkan info
• Voice call — Bercakap macam dengan staf terbaik

📱 MUDAH DIGUNAKAN:
• Interface mesra pengguna — Sesuai untuk semua umur
• Bilingual BM/EN — Tukar bahasa dengan sekali klik
• Training video — Panduan langkah demi langkah

Mulakan sekarang — percuma untuk 14 hari!

Supported languages: Bahasa Melayu, English, 中文"

Keywords (Google Play):
pos malaysia, bisnes app, kedai app, inventori, invoice, accounting, ai assistant, sme tools, whatsapp business, retail app
```

---

### 56B.11 Cost Estimation — Mobile Development

| Item | Cost (RM) | Notes |
|------|-----------|-------|
| **Flutter Developer (Senior)** | 15,000/month x 9 months | RM 135,000 |
| **UI/UX Designer** | 8,000/month x 4 months | RM 32,000 |
| **QA Tester** | 5,000/month x 3 months | RM 15,000 |
| **Google Play Account** | $25 (~RM115) | One-time |
| **Apple Developer Account** | $99/year (~RM465) | Annual |
| **Firebase Pro** | ~RM100/month | FCM, Crashlytics, Analytics |
| **Domain & Hosting** | Included in platform | Same infrastructure |
| **App Store Assets** | ~RM2,000 | Screenshots, icons, promotional videos |
| **Contingency (20%)** | ~RM37,000 | Buffer for unexpected costs |
| **TOTAL ESTIMATED** | **~RM222,000** | For Phase 1-3 (9 months) |

**ROI Justification:**
- 500 tenants x RM288/month average = RM144,000/month
- Mobile app expected to increase conversion by 30%
- Expected to reduce churn by 15% (app stickiness)
- Payback period: ~2 months after launch

---

### 56B.12 Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| **App Download Rate** | > 70% of active tenants | Downloads / total tenants |
| **Daily Active Users** | > 40% of installed | DAU / installs |
| **Session Duration** | > 8 minutes avg | Firebase Analytics |
| **Crash-Free Sessions** | > 99.5% | Crashlytics |
| **App Store Rating** | > 4.5 stars | Google Play + Apple |
| **Offline Usage** | > 20% of transactions | Transactions without internet |
| **Feature Adoption** | > 60% use AI chat weekly | AI feature usage analytics |
| **Time to Complete Task** | < 30 seconds (POS order) | In-app timing metrics |
| **User Satisfaction** | > 4.3/5.0 | In-app survey |
| **Support Tickets (App)** | < 5% of total tickets | Zendesk/support dashboard |

---

### 56B.13 Risk Mitigation

| Risk | Impact | Mitigation Strategy |
|------|--------|-------------------|
| **Flutter performance issues** | Poor UX | Profile early, optimize rendering, native modules if needed |
| **iOS rejection** | Delayed launch | Follow Apple guidelines strictly, prepare appeal plan |
| **Low adoption by staff** | Wasted investment | Simple UI, training videos, incentivize usage |
| **Offline data conflicts** | Data inconsistency | Server-wins for financial data, clear sync indicators |
| **Battery drain** | User frustration | Optimize background sync, limit location/GPS |
| **Device compatibility** | Some users can't install | Set reasonable minimum requirements, offer web fallback |
| **Security breach** | Data leak | Encrypt local data, secure JWT, biometric auth |
| **App store competition** | Hard to stand out | Clear positioning, focus on Malay market, AI differentiation |

---

> **BAB 56B dicipta pada 19 Oktober 2026.**  
> **Menambah lapisan mobile: Android + iOS apps untuk Tenant Admin dan Staff.**  
> **Status: Perancangan strategik — panduan pelaksanaan Fasa 1-4.**  
> **Unfair Advantage: Satu app untuk semua — dual mode (Owner + Staff), offline capable, AI integrated.**

---

## BAB 56C — CCTV Surveillance & AI Security

### 56C.1 Visi — Mengapa AI-Powered CCTV?

```
Current situation for Malaysian SMEs:
├── CCTV: Semua kedai/pejabat ada kamera, tapi hanya rekod (pasif)
├── Human monitoring: Perlu orang tengok screen 24/7 → mahal, penat, mudah miss
├── Post-event only: Dapat tengok bila dah jadi masalah (curi, vandalisme)
├── No search: Nak cari "siapa masuk pukul 3am" → kena fast forward 3 jam
└── Result: CCTV ada, tapi tak proactive — wasted investment

AI BIJAN Solution:
├── Real-time AI detection: Anomaly, threat, safety violation = instant alert
├── Natural language search: "Show me customer pakai baju merah tadi"
├── Staff behavior monitoring: Productivity, safety compliance, unauthorized access
├── Integration with B-HRMS: Attendance via facial recognition, shift violation alerts
├── Integration with B-Security: Alarm system, door locks, notification to police
└── Price: Included in RM488+ subscription (hardware optional via partner)

**Key Advantage:** CCTV biasa = rekod masa lalu. AI BIJAN CCTV = prevent masa depan.
```

**Positioning:** *"AI security guard yang tak tidur, tak makan, dan sentiasa waspada."*

### 56C.2 Feature Matrix — Traditional vs AI CCTV

| Feature | Traditional CCTV | AI BIJAN CCTV | Gap Strategy |
|---------|-----------------|---------------|--------------|
| **Live view** | ✅ Manual check | ✅ Dashboard + mobile | Real-time streaming |
| **Recording** | ✅ DVR/NVR storage | ✅ Cloud R2 + local edge | Hybrid storage model |
| **Motion detection** | ✅ Basic zone-based | ✅ AI person/vehicle classification | Reduce false positives 90% |
| **Face recognition** | ❌ Manual lookup | ✅ Auto identify staff/customers | Liveness detection anti-spoof |
| **Anomaly detection** | ❌ None | ✅ Loitering, fall, fight, fire | ML models trained on MY data |
| **Natural language search** | ❌ Time-based only | ✅ "Show me van putih masuk pukul 2pm" | Video understanding AI |
| **Staff monitoring** | ❌ Supervision required | ✅ Productivity score, safety compliance | Privacy-first design |
| **Access control** | ❌ Separate system | ✅ Facial recognition entry | Integrate with B-Auth |
| **Alarm integration** | ❌ Standalone | ✅ Door lock, siren, police alert | IoT gateway integration |
| **Multi-site dashboard** | ❌ Per-location | ✅ Central HQ monitoring | Scale to 100+ cameras/site |
| **Compliance reporting** | ❌ Manual export | ✅ PDPA audit trail, incident reports | Automated documentation |
| **Privacy mode** | ❌ None | ✅ Blur faces, mask sensitive data | PDPA compliant by default |

### 56C.3 Data Model — CCTV & AI Security

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `cctv_cameras` | Camera registry per tenant/site | `tenant_id`, `site_id`, `name`, `rtsp_url`, `status`, `location_json` |
| `cctv_streams` | Live stream configuration | `camera_id`, `resolution`, `fps`, `codec`, `bitrate` |
| `cctv_recordings` | Cloud/local storage index | `camera_id`, `start_time`, `end_time`, `storage_type`, `file_path` |
| `cctv_events` | AI-detected events/alerts | `camera_id`, `event_type`, `confidence`, `timestamp`, `thumbnail_url` |
| `cctv_analytics` | Daily/weekly/monthly stats | `camera_id`, `date`, `person_count`, `vehicle_count`, `alert_count` |
| `cctv_faces` | Recognized face embeddings | `camera_id`, `face_embedding`, `person_id`, `last_seen` |
| `cctv_persons` | Known persons (staff, VIP) | `tenant_id`, `name`, `role`, `photo_url`, `access_level` |
| `cctv_incidents` | Security incidents log | `tenant_id`, `type`, `severity`, `camera_ids`, `video_clip_url`, `status` |
| `cctv_access_logs` | Entry/exit via facial recognition | `camera_id`, `person_id`, `access_granted`, `timestamp` |
| `cctv_integrations` | Third-party alarm/door lock | `tenant_id`, `provider`, `api_key`, `status` |

#### Event Types (AI Detection)

| Event Type | Description | Alert Level | Action |
|------------|-------------|-------------|--------|
| `person_detected` | Human detected in frame | Info | Log + count |
| `vehicle_detected` | Car/motorcycle detected | Info | Log + plate OCR |
| `loitering` | Person stays >5min in one spot | Warning | Alert supervisor |
| `fall_detected` | Person fell down | Critical | Emergency alert + call |
| `fight_detected` | Physical altercation | Critical | Police alert + video clip |
| `fire_smoke` | Fire/smoke visual detection | Critical | Fire dept alert + evacuation |
| `unauthorized_access` | Unknown person in restricted area | High | Lockdown + alert |
| `after_hours_entry` | Movement outside business hours | Warning | Verify if authorized |
| `perimeter_breach` | Fence/barrier crossed | Critical | Security patrol alert |
| `package_theft` | Package picked up unexpectedly | High | Alert warehouse staff |

### 56C.4 Technical Architecture

```
AI CCTV System Architecture:
├── Edge Layer (On-premise/NVR)
│   ├── RTSP stream ingestion
│   ├── Local AI inference (lightweight models)
│   ├── Motion detection pre-filter
│   └── Edge recording (buffer 5min before event)
│
├── Cloud Layer (AI BIJAN Platform)
│   ├── Stream relay via WebRTC
│   ├── Deep AI analysis pipeline
│   │   ├── Object detection (YOLOv8/v10)
│   │   ├── Face recognition (ArcFace/InsightFace)
│   │   ├── Action recognition (SlowFast/MViT)
│   │   └── Anomaly detection (autoencoder-based)
│   ├── Video storage (Cloudflare R2 / AWS S3)
│   ├── Event indexing (Meilisearch)
│   └── Real-time alerting (Laravel Reverb WebSocket)
│
├── AI Models (serveras.click Gateway)
│   ├── General detection (multi-class)
│   ├── Industry-specific models (retail, warehouse, office)
│   └── Custom fine-tuning per tenant (opt-in)
│
└── Integration Layer
    ├── B-HRMS: Attendance, shift violation
    ├── B-Security: Alarm, access control
    ├── B-Notify: WhatsApp, SMS, email alerts
    └── B-Flow: Automation triggers (lock doors, sound siren)
```

#### Hardware Compatibility

| Tier | Camera Type | Resolution | AI Capability | Cost Estimate |
|------|-------------|------------|---------------|---------------|
| **Basic** | Existing IP cameras | 1080p | Cloud AI processing | RM0 (use existing) |
| **Standard** | AI-enabled edge cameras | 4K | Edge + Cloud hybrid | RM800-1,500/camera |
| **Premium** | PTZ auto-tracking cameras | 4K+ | Full AI suite | RM2,000-3,500/camera |
| **Enterprise** | Thermal + visible spectrum | Multi-spectral | All features + night vision | RM5,000+/camera |

**Partner Model:** AI BIJAN tidak jual hardware — integrate dengan vendor lokal (Hikvision, Dahua, TP-Link) sebagai reseller/integrator.

### 56C.5 AI Use Cases by Business Type

#### Retail Shop (Kedai/Rumah Makan)

```
Use Cases:
├── Customer counting: Peak hours analysis → optimize staffing
├── Shelf monitoring: Out-of-stock detection → auto reorder alert
├── Cashier monitoring: Transaction verification, theft prevention
├── Queue detection: Long wait time → staff alert to help
├── After-hours intrusion: Alarm + police notification
└── Staff behavior: Uniform compliance, phone usage tracking
```

#### Warehouse/Factory

```
Use Cases:
├── Safety compliance: Hard hat, vest detection → instant warning
├── Unauthorized zone entry: Restricted area lockdown
├── Package theft: Loading dock monitoring
├── Forklift/pedestrian separation: Collision prevention
├── Fire/smoke early detection: Evacuation trigger
└── Production line monitoring: Bottleneck identification
```

#### Office/Co-working

```
Use Cases:
├── Facial recognition entry: Access control + attendance
├── Meeting room occupancy: Utilization analytics
├── After-hours access: Visitor management
├── Parking space monitoring: Available spots detection
└── Visitor tracking: Who visited, when, duration
```

#### Construction Site

```
Use Cases:
├── Perimeter breach: Intrusion detection on large area
├── Equipment theft: Heavy machinery movement alert
├── Safety violation: PPE compliance monitoring
├── Progress tracking: Daily site comparison (AI image diff)
└── Worker presence: Headcount verification
```

### 56C.6 Privacy & PDPA Compliance

```
Privacy-by-Design Principles:
├── Consent management: Sign-in at entrance acknowledges CCTV
├── Data minimization: Only store event clips, not 24/7 raw footage
├── Retention policy: Auto-delete after 30 days (configurable)
├── Access control: Role-based viewing (Manager > Supervisor > Staff)
├── Audit trail: Every view/download logged for accountability
├── Privacy mode: Auto-blur faces in shared videos (non-security context)
├── Data residency: All storage within Malaysia (data sovereignty)
└── Employee notification: Staff know when they're being monitored
```

#### PDPA Checklist

| Requirement | Implementation | Status |
|-------------|----------------|--------|
| **Notice and Consent** | Sign-in posters, employee handbook clause | ✅ Built-in |
| **Purpose Limitation** | CCTV only for security, not general surveillance | ✅ Configured |
| **Data Minimization** | Event-based recording, not continuous | ✅ Default |
| **Security Safeguards** | Encryption at rest + in transit, RBAC | ✅ Implemented |
| **Retention Policy** | Auto-delete after configurable period | ✅ Automated |
| **Access Logging** | Every access recorded, immutable audit trail | ✅ Mandatory |
| **Individual Rights** | Request deletion, opt-out where possible | ✅ Process defined |

### 56C.7 Integration with Other Modules

```
CCTV × B-HRMS:
├── Automatic attendance via facial recognition at entry
├── Shift violation alert: Staff in wrong area during shift
├── Overtime detection: Staff present beyond scheduled hours
└── Break compliance: Staff in restricted area during break time

CCTV × B-Security (Future Module):
├── Integrated alarm system (siren, strobe, notification)
├── Door lock control via AI decision (manager approval required)
├── Police/emergency services direct alert (premium tier)
└── Incident response workflow: Detect → Assess → Respond → Document

CCTV × B-Notify:
├── Real-time push notification on critical events
├── WhatsApp video clip sharing (encrypted, expiring)
├── SMS alert for high-severity incidents
└── Email daily summary report

CCTV × B-Flow:
├── Trigger automation: If motion detected → turn on lights
├── If fire detected → unlock emergency exits, notify all
├── If after-hours entry → verify against authorized list
└── If package theft → lock loading dock, alert security
```

### 56C.8 Mobile App Integration (BAB 56B)

```
Owner/Staff Mobile Features:
├── Live camera feed (select which camera to view)
├── Push notifications for critical events (with video thumbnail)
├── Review event clips (swipe left/right to navigate)
├── Accept/reject incident alerts (confirm or dismiss)
├── Remote actions: Lock/unlock doors, sound siren (manager only)
├── Search events: "Show me all alerts from yesterday"
└── Daily/weekly security report (push notification summary)

Offline Mode:
├── Cached last 1 hour of event thumbnails
├── Local event queue (sync when connection restored)
└── Offline live view via local network (on-premise)
```

### 56C.9 Pricing & Monetization

| Tier | Cameras Included | Storage | AI Features | Hardware Cost | Monthly Fee |
|------|-----------------|---------|-------------|---------------|-------------|
| **Starter (RM288)** | 1 camera | 7 days | Basic motion detection | Own hardware | Included |
| **Professional (RM488)** | 4 cameras | 14 days | Person/vehicle detection | Partner discount | Included |
| **Business (RM888)** | 8 cameras | 30 days | Full AI suite + face recognition | Partner discount 15% | Included |
| **Business Pro (RM1,288)** | 16 cameras | 60 days | All features + priority support | Partner discount 20% | Included |
| **Enterprise (RM1,888)** | Unlimited | 90 days | Custom models + dedicated support | Partner discount 25% | Included |
| **Add-on Camera** | Any tier | 30 days | Full AI | N/A | RM49/camera |

**Hardware Partnership Revenue:**
- AI BIJAN earn commission from camera vendors (10-15% margin)
- Installation service via certified partners (RM300-800 per site)
- Maintenance contract: RM50-100/camera/year (optional)

### 56C.10 Implementation Roadmap

| Phase | Features | Duration | Priority |
|-------|----------|----------|----------|
| **Phase 1** | RTSP ingestion, live view, basic motion detection, cloud recording | Month 6-9 | P1 |
| **Phase 2** | AI event detection (person, vehicle, loitering), mobile app integration | Month 9-12 | P1 |
| **Phase 3** | Face recognition, natural language search, HRMS integration | Month 12-18 | P2 |
| **Phase 4** | Advanced AI (fight, fall, fire detection), alarm integration | Month 18-24 | P2 |
| **Phase 5** | PTZ auto-tracking, thermal camera support, predictive analytics | Month 24-36 | P3 |

### 56C.11 Competitive Positioning

| Feature | AI BIJAN CCTV | Hikvision VisionStream | Milestone XProtect |
|---------|--------------|------------------------|-------------------|
| **All-in-One Platform** | ✅ Yes (POS + CCTV + Finance) | ❌ CCTV only | ❌ CCTV only |
| **AI Detection** | ✅ Cloud AI (always updating) | ⚠️ Edge AI (model dependent) | ⚠️ Plugin-based |
| **Natural Language Search** | ✅ "Show me red shirt guy" | ❌ Time/location based | ❌ Metadata search |
| **Mobile Experience** | ✅ Native app (BAB 56B) | ⚠️ Basic viewer | ❌ Web-only |
| **Malaysia Compliance** | ✅ PDPA, e-Invois ready | ❌ Global generic | ❌ Global generic |
| **Pricing** | ✅ Included in subscription | ⚠️ Expensive licenses | ⚠️ Expensive licenses |
| **Integration** | ✅ 18 B-Modules ecosystem | ❌ Limited APIs | ⚠️ Enterprise APIs |

### 56C.12 Key Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| **False Positive Rate** | < 5% | Events that are not real threats |
| **Detection Accuracy** | > 95% | Correct identification of actual threats |
| **Alert Response Time** | < 30 seconds | From detection to human review |
| **Storage Efficiency** | < 50GB/camera/month | Event-based compression |
| **Face Recognition Accuracy** | > 98% | Under good lighting conditions |
| **System Uptime** | > 99.9% | Availability of live view + recording |
| **PDPA Compliance Score** | 100% | Annual audit pass rate |

---

> **BAB 56C dicipta pada 19 Oktober 2026.**  
> **Menutup siklus dokumentasi: Commerce (B-Site), CRM, Service Desk, Go-to-Market, AI Live Interaction, Mobile Apps, CCTV Surveillance.**  
> **Status: Perancangan strategik — panduan pelaksanaan Fasa 4-5.**  
> **Unfair Advantage: Satu platform untuk semua aspek bisnes — dari POS hingga CCTV, dari Finance hingga AI Security.**

---

## BAB 57 — B-Payroll: Malaysia Statutory Compliance

### 57.1 Visi

B-Payroll ialah modul pengurusan gaji yang direka khas untuk pematuhan Malaysia. Ia mengira gaji bersih secara automatik berdasarkan kehadiran, cuti, struktur gaji, dan semua kewajiban statutori (EPF, SOCSO, EIS, PCB). Output termasuk payslip, fail bank, dan laporan pelepasan cukai.

**Matlamat:** Pemilik bisnes boleh generate gaji bulanan dalam 5 minit tanpa perlu pakar perakaunan.

### 57.2 Struktur Gaji Employee

| Komponen | Jenis | Keterangan |
|----------|-------|------------|
| **Gaji Asas** | Earnings | Gaji bulanan tetap |
| **Allowance Transport** | Earnings | Elaun pengangkutan (tetap atau per km) |
| **Allowance Meal** | Earnings | Elaun makan (tetap atau per hari hadir) |
| **Overtime Regular** | Earnings | Lebih masa hari biasa (1/3 x jam wage x bilangan jam lebih) |
| **Overtime Rest Day** | Earnings | Lebih masa hari rehat (2 x jam wage x bilangan jam lebih) |
| **Overtime Public Holiday** | Earnings | Lebih masa hari kelepasan am (3 x jam wage x bilangan jam lebih) |
| **Bonus/Commission** | Earnings | Bonus tahunan atau komisen jualan |
| **EPF Employee (11%)** | Deduction | Berpanjangan bagi gaji <= RM5,000 (9% bagi >= RM5,000) |
| **EPF Employer (13%)** | Company Cost | Berpanjangan bagi gaji <= RM5,000 |
| **SOCSO** | Deduction | Berpanjangan mengikut tier gaji (had tahunan) |
| **EIS (0.2%)** | Deduction | Insurans Pekerjaan, maksimum RM100/tahun |
| **PCB** | Deduction | Potongan Cukai Berjadual mengikut mPTG dan jadual LHDN |
| **HRDF Levy** | Company Cost | 0.5% daripada gaji kasar (had RM100/bulan) |
| **Loan/Covenant** | Deduction | Pembayaran pinjaman syarikat atau koperasi |
| **Pekerjaan Berganda** | Deduction | Pengiraan PCB khas untuk pekerja dengan >1 pekerjaan |

### 57.3 Statutory Calculation Rules

> **⚠️ AMARAN PENTING:** Formula di bawah adalah untuk rujukan perancangan sahaja.
> Jangan gunakan sebagai logik pengeluaran sehingga disahkan oleh pakar payroll Malaysia dan sumber rasmi berikut:
> - KWSP: [https://www.kwsp.gov.my](https://www.kwsp.gov.my/en/employer/responsibilities/mandatory-contribution)
> - PERKESO: [https://www.perkeso.gov.my](https://www.perkeso.gov.my/en/uncategorised/778-contributions.html)
> - LHDN PCB: Jadual terkini perlu dimuat turun secara automatik
>
> Sistem pengeluaran MESTI menggunakan jadual kadar bertarikh yang boleh versioning, bukan formula hardcode.

#### EPF (Employees Provident Fund / KWSP)

```
Sumber rasmi: https://www.kwsp.gov.my/en/employer/responsibilities/mandatory-contribution

Kadar caruman (bagi warganegara & PR bawah 60 tahun):
- Gaji <= RM5,000:
  * Pekerja: 11%
  * Majikan: 13%
- Gaji > RM5,000:
  * Pekerja: 11% (pada amaun sehingga RM5,000 sahaja)
  * Majikan: 12% (pada amaun melebihi RM5,000)

Kategori kategori mengikut umur & status:
- Kategori A: Warganegara/PR < 60 tahun -> 11%/13% (gaji <= RM5,000)
- Kategori B: Warganegara/PR < 60 tahun -> 11%/12% (gaji > RM5,000)
- Pekerja asing: Tidak wajib menyertai KWSP (kecuali perjanjian khusus)

Nota:
- Kadar bagi pekerja >= 60 tahun berbeza (berperingkat menurun)
- Amaun caruman dibundarkan mengikut jadual KWSP
- Tiada "kategori baru lulus" dalam peraturan rasmi — klasifikasi berdasarkan umur & status kewarganegaraan
```

#### SOCSO / PERKESO (Employment Social Insurance System)

```
Sumber rasmi: https://www.perkeso.gov.my/en/uncategorised/778-contributions.html

Kadar EIS (Employment Insurance System):
- Jumlah: 0.4% daripada gaji kasar (siling andaian RM6,000/bulan)
- Pekerja: 0.2%
- Majikan: 0.2%
- Pengecualian: Pekerja dengan gaji < RM2,000 (sebelum 1 Jan 2024), pencen, kontrak tertentu

Had maksimum: Caruman tidak dikira melebihi gaji siling semasa (semak setiap tahun)
```

#### EIS (Employment Insurance System) — Nota Tambahan

```
- Kedua-dua pekerja DAN majikan membayar 0.2% = jumlah 0.4%
- Siling gaji andaian: RM6,000/bulan (semak versi terkini PERKESO)
- Pekerja bergaji rendah (< RM2,000 sebelum 1 Jan 2024) mungkin dikecualikan
- Majikan WAJIB daftar dan bayar — bukan pilihan
```

#### OT (Lebih Masa) — Akta Kerja 1955 (Revised 2022)

```
Berdasarkan Akta Kerja 1955 (yang telah disemak 2022):

Hari biasa: 1.5x kadar jam normal (bukan 1/3 tambahan)
Hari rehat: 2x kadar jam normal
Hari kelepasan am: 3x kadar jam normal

Formula:
- Kadar jam = Gaji bulanan / 26 hari / 8 jam (atau seperti yang ditetapkan dalam kontrak)
- OT hari biasa = Kadar jam x 1.5 x bilangan jam OT
- OT hari rehat = Kadar jam x 2 x bilangan jam OT
- OT hari kelepasan am = Kadar jam x 3 x bilangan jam OT
```

#### PCB (Potongan Cukai Berjadual)

```
Faktor pengiraan:
- mPTG (Maklumat Pemegang Pencarum Tanggungan): status kahwin, bilangan anak
- IMT (Istilah Maklumat Tambahan): kontrak, kerani, executive, manager
- Jadual PCB LHDN (berubah setiap tahun — MUAT TURUN VERSI TERKINI)
- Pelepasan individu (RM9,000/tahun lalai — semak setiap tahun)
- Pekerja berganda: gunakan jadual khusus

Formula ringkas:
- Cari pendapatan tahunan dijangka = Gaji Bulanan x 12
- Tolak pelepasan individu
- Cari kadar cukai dalam jadual LHDN
- Bahagi kembali kepada bulanan

NOTA: Jadual PCB berubah setiap tahun. Sistem mesti muat turun versi terkini dari LHDN.
```

#### HRDF (Human Resource Development Fund / HRD Corp)

```
- Kadar: 1% daripada jumlah gaji bulanan (bukan 0.5%)
- Had: Tiada had RM100/bulan dalam peraturan semasa
- Majikan bertanggungjawab penuh (tidak ditolak dari pekerja)
- Pengecualian: Syarikat dengan kurang 10 pekerja (sebelum bayaran yuran pendaftaran)
```

> **Semua kadar statutori perlu disemak semula sebelum sebarang pengiraan pengeluaran.**

### 57.4 Output & Pelaporan

| Output | Format | Tujuan |
|--------|--------|--------|
| **Payslip** | PDF + Portal Self-Service | Setiap employee terima salinan gaji |
| **EPF Form DW10A** | XML/CSV | Penghantaran electronic ke EPF |
| **SOCSO Monthly Return** | XML | Penghantaran electronic ke PERKESO |
| **PCB Summary** | Excel/PDF | Rekod potongan untuk LHDN |
| **EA Form** | PDF (Form EA) | Laporan tahunan pendapatan pekerja |
| **EMR File** | CSV/XML | Electronic Monthly Return (PERKESO) |
| **Bank Transfer File** | MT101 / CSV | Transfer gaji terus ke akaun bank |
| **Journal Entry** | Auto-post ke B-Finance | Posting gaji ke akaun perakaunan |

### 57.5 Workflow Pengajian

```
Start Payroll Run
        ↓
1. Import Attendance (dari B-HRMS)
        ↓
2. Import Leave Records (cuti tanpa gaji tolak dari gaji)
        ↓
3. Calculate Overtime (jika ada)
        ↓
4. Calculate Earnings (gaji asas + allowance + bonus)
        ↓
5. Calculate Deductions (EPF, SOCSO, EIS, PCB)
        ↓
6. Calculate Company Costs (EPF employer, SOCSO employer, HRDF)
        ↓
7. Preview Payslips (review mode)
        ↓
8. Manager Approval (approval gate)
        ↓
9. Generate Final Payslips (PDF + email)
        ↓
10. Generate Bank Transfer File
        ↓
11. Generate Statutory Files (EPF, SOCSO, PCB)
        ↓
12. Post Journal Entry ke B-Finance
        ↓
13. Archive Payroll Run (lock period)
        ↓
End Payroll Run
```

### 57.6 Data Model

| Table | Purpose |
|-------|---------|
| payroll_structures | Gaji structure per employee (basic, allowances) |
| payroll_runs | Bulanan payroll run (status: draft, approved, paid) |
| payroll_run_items | Per-employee calculation dalam satu run |
| salary_components | Definisi komponen (earnings/deductions/company cost) |
| statutory_configurations | Kadar EPF/SOCSO/EIS/PCB per tenant |
| payslips | Final payslip data (generated) |
| epf_records | Rekod penghantaran EPF |
| socso_records | Rekod penghantaran SOCSO |
| pcb_records | Rekod potongan PCB |
| ea_forms | Annual EA form data |
| bank_transfer_files | Bank transfer file metadata |
| pf_records | Rekod penghantaran EPF |
| socso_records | Rekod penghantaran SOCSO |
| pcb_records | Rekod potongan PCB |
| a_forms | Annual EA form data |
| ank_transfer_files | Bank transfer file metadata |

### 57.7 Integrations

| Module | Integration Point |
|--------|------------------|
| **B-HRMS** | Attendance, leave records → net pay calculation |
| **B-Finance** | Auto journal entry untuk posting gaji |
| **B-Invoicing** | Payslip delivery via document system |
| **B-Notify** | Notification bila payslip ready |
| **Payment Gateway** | Optional: direct deposit via gateway |

### 57.8 Compliance Updates

Statutory rates berubah setiap tahun. Sistem mesti menyokong:
- Versioning untuk kadar statutori (tahun, versi)
- Bulk update dari admin panel
- Backward compatibility (historical payroll runs tidak rosak)
- Alert apabila kadar berubah dan perlu update

---

## BAB 58 — B-SCM: Supply Chain Management

### 58.1 Visi

B-SCM menguruskan keseluruhan rantaian bekalan: dari pemilihan pembekal, RFQ, pembelian, penerimaan stok, hingga pengedaran. Direka untuk perniagaan perdagangan dan pembuatan yang memerlukan kawalan inventori yang lebih canggih.

**Matlamat:** Kurangkan stock-out, optimize carrying costs, dan automasi procurement workflow.

### 58.2 Supplier Management

#### Supplier Directory

| Field | Keterangan |
|-------|------------|
| Nama syarikat, kontak person, telefon, e-mel | Identiti utama |
| Alamat, kawasan perkhidmatan | Logistics planning |
| Payment terms (Net 30/60/90), kredit limit | Financial terms |
| Product categories supplied | Catalog mapping |
| Lead time (hari purata dari PO to delivery) | Planning input |
| Rating (price, quality, delivery, service) | Performance tracking |
| Status (active, suspended, blacklisted) | Lifecycle management |

#### Supplier Scorecard

| Metric | Formula | Frequency |
|--------|---------|-----------|
| **Price Competitiveness** | Average quote vs market price | Per order |
| **On-Time Delivery** | % orders delivered on or before promised date | Monthly |
| **Quality Rate** | % orders passing QC inspection | Per shipment |
| **Response Time** | Average hours to respond to RFQ | Per inquiry |
| **Documentation Accuracy** | % invoices matching PO exactly | Per invoice |
| **Overall Score** | Weighted average of all metrics | Quarterly |

#### RFQ Workflow

`
1. Create RFQ (request for quotation)
   ├── Products/items requested
   ├── Quantity required
   ├── Target price (optional)
   └── Deadline for response

2. Send to Shortlisted Suppliers
   ├── Email notification
   ├── Supplier portal access (self-service)
   └── Track responses

3. Receive Quotes
   ├── Compare prices, terms, lead times
   ├── Auto-rank by total cost of ownership
   └── Flag anomalies (price > X% above previous)

4. Award Order
   ├── Select winning supplier(s)
   ├── Auto-generate Purchase Order
   └── Notify awarded supplier

5. Follow-up
   ├── Track PO status
   ├── Reminder if past due date
   └── Escalation if critical delay
`

### 58.3 Advanced Inventory Controls

#### Serial Number Tracking

| Use Case | Implementation |
|----------|---------------|
| Warranty management | Each unit has serial, purchase date, warranty expiry |
| Recall management | Identify all customers with affected serial numbers |
| Service history | Track repairs/replacements per serial number |
| Asset tracking | High-value items tracked individually |

#### Batch/Expiry Tracking

| Parameter | Configuration |
|-----------|--------------|
| Batch number format | Alphanumeric, auto-generated or manual |
| Manufacturing date | From supplier or internal production |
| Expiry date | Fixed duration from mfg date or fixed date |
| FEFO (First Expired First Out) | Auto-select batch with earliest expiry for picking |
| Near-expiry alerts | Alert when product within X days of expiry |
| Quarantine expired stock | Auto-move expired products to quarantine state |

#### Consignment Stock

`
Consignment Arrangement:
├── Supplier owns the stock
├── AI BIJAN tenant holds stock at their location
├── Ownership transfers when item is sold
├── Unsold items can be returned
└── Reporting: consignment inventory separate from owned stock
`

#### ABC Analysis

| Category | Criteria | Action |
|----------|----------|--------|
| **A Items** | Top 20% of SKUs contributing to 80% of value | Tight control, frequent review, low safety stock |
| **B Items** | Next 30% of SKUs contributing to 15% of value | Moderate control, regular review |
| **C Items** | Bottom 50% of SKUs contributing to 5% of value | Loose control, bulk ordering, higher safety stock |

#### VTFR Analysis

| Classification | Meaning | Strategy |
|---------------|---------|----------|
| **V - Fast Moving** | High turnover rate | Keep adequate stock, frequent reorder |
| **T - Slow Moving** | Low turnover but still selling | Order in smaller quantities, monitor |
| **F - Fair Moving** | Moderate turnover | Standard ordering policy |
| **R - Red/Obsolete** | No movement in X days | Promote, discount, return to vendor, or scrap |

### 58.4 Demand Planning

#### Historical Demand Analysis

`
Input Data:
├── Sales history (minimum 12 months recommended)
├── Seasonal patterns (monthly, quarterly, yearly)
├── Promotional events (discounts, campaigns)
└── Market trends (from B-Scout intelligence)

Output:
├── Forecast for next N periods (configurable)
├── Confidence intervals (low/medium/high)
├── Seasonal index per SKU
└── Trend detection (growing, stable, declining)
`

#### Safety Stock Calculator

`
Safety Stock = Z x σ x √(Lead Time)

Where:
├── Z = Service level factor (1.65 for 95%, 2.05 for 98%)
├── σ = Standard deviation of demand
└── Lead Time = Average supplier lead time in same units as σ

Reorder Point = (Average Daily Demand x Lead Time) + Safety Stock
`

#### Purchase Suggestion Engine

`
For each SKU:
1. Check current stock level
2. Compare against reorder point
3. If below reorder point:
   a. Calculate suggested order quantity
      EOQ = √((2 x Annual Demand x Order Cost) / Holding Cost)
   b. Check supplier minimum order quantity
   c. Check lead time vs projected stock-out date
   d. Factor in pending POs (already ordered, not received)
4. Generate purchase suggestion
5. Auto-create draft PO if enabled
6. Route for approval based on amount
`

### 58.5 Returns Management

#### Return to Vendor (RTV)

`
1. Initiate RTV
   ├── Reason: defective, wrong item, over-delivery, expired
   ├── Quantity to return
   └── Reference: original PO or delivery note

2. Supplier Approval (if required by agreement)

3. Physical Return
   ├── Update stock: move to quarantine/damaged state
   ├── Generate return shipping label
   └── Track return shipment

4. Credit/Refund
   ├── Supplier issues credit note
   ├── Match credit note to original PO
   └── Auto-adjust accounts payable
`

#### Customer Return / Credit Memo

`
1. Return Authorization (RMA)
   ├── Reason code
   ├── Condition assessment (resellable, repair, scrap)
   └── Restocking fee (if applicable)

2. Goods Receipt
   ├── Update stock based on condition
   └── Trigger inspection if needed

3. Credit/Refund
   ├── Credit memo issued
   ├── Refund processed (original payment method)
   └── Auto-link to original sale order
`

### 58.6 Data Model

| Table | Purpose |
|-------|---------|
| suppliers | Supplier directory and contact info |
| supplier_ratings | Performance scorecard data |
| 
fqs | Request for quotation documents |
| 
fq_responses | Supplier quotes per RFQ |
| purchase_orders | Approved purchase orders |
| po_items | Line items per PO |
| goods_received_notes | GRN for incoming shipments |
| serial_numbers | Individual unit tracking |
| atch_inventory | Batch/expiry managed stock |
| consignment_stock | Consignment arrangement tracking |
| demand_forecasts | Predicted demand per SKU |
| bc_classifications | ABC analysis results |
| tfr_reports | VTFR classification snapshots |
| 
eturn_authorizations | RMA documents |
| 
eturn_to_vendor | RTV transactions |

### 58.7 Integrations

| Module | Integration Point |
|--------|------------------|
| **B-Inventory** | Stock levels, stock movements, warehouse operations |
| **B-Finance** | PO approval triggers journal estimates; GRN updates asset values |
| **B-Invoicing** | Supplier bill matching against PO and GRN (3-way match) |
| **B-Flow** | Automated RFQ emails, PO approvals, low-stock alerts |
| **B-Notify** | Notifications for PO status changes, delivery delays |

---

## BAB 59 — B-Document AI: Intelligent Document Processing

### 59.1 Visi

B-Document AI menggunakan OCR dan model AI untuk memahami dokumen yang dimasukkan oleh pengguna — invois, resit, kad perniagaan, kontrak, dan dokumen lain — dan mengekstrak data berstruktur secara automatik.

**Matlamat:** Kurangkan kerja input data manual sebanyak 80%. Pemilik bisnes hanya perlu "upload atau snap" — AI akan isi rekod.

### 59.2 Document Types & Extraction

#### Invoice/Receipt Extraction

| Source Field | Extracted To | Target Module |
|-------------|--------------|---------------|
| Vendor name | Supplier record (create if new) | B-Procurement |
| Invoice number | Invoice reference | B-Invoicing |
| Invoice date | Document date | B-Invoicing |
| Due date | Payment deadline | B-Invoicing |
| Tax amounts | SST breakdown (Malaysia only) | B-Finance |
| Line items | Product/service mapping | B-Inventory/B-Finance |
| Total amount | Amount due | B-Invoicing |
| Payment terms | Net X days | B-Finance |

**Workflow:**
`
1. User uploads invoice (image, PDF, email attachment)
2. OCR extracts raw text
3. AI identifies document type (invoice, receipt, bill)
4. AI extracts fields with confidence scores
5. For high-confidence fields (>90%): auto-populate
6. For low-confidence fields (<90%): flag for human review
7. Human reviews and corrects any fields
8. On approval: auto-create bill/supplier record
9. Audit trail: original image, extracted data, reviewer
`

#### Business Card Extraction

| Source Field | Extracted To | Target Module |
|-------------|--------------|---------------|
| Name | Contact first/last name | B-Marketing/CRM |
| Company | Organization name | B-Marketing/CRM |
| Job title | Role/title field | B-Marketing/CRM |
| Phone | Contact phone | B-Marketing/CRM |
| Email | Contact email | B-Marketing/CRM |
| Website | Company website | B-Marketing/CRM |
| Address | Company address | B-Marketing/CRM |

**Workflow:**
`
1. User snaps photo of business card or uploads image
2. OCR extracts text
3. AI maps fields using NLP (handles various card layouts)
4. Creates or updates contact record
5. Suggests duplicate merge if existing contact found
6. Sends welcome message via B-Omnichannel (if opted-in)
`

#### Contract Analysis

| Extracted Element | Purpose |
|------------------|---------|
| Party names | Identify contracting entities |
| Contract start/end dates | Timeline tracking, renewal alerts |
| Payment terms | Sync with invoicing schedule |
| Key obligations | Task creation for compliance |
| Penalty clauses | Risk alerts |
| Termination conditions | Legal compliance tracking |
| Auto-renewal clauses | Renewal reminder workflow |

### 59.3 Processing Pipeline

`
Document Ingestion
        ↓
Preprocessing (deskew, enhance contrast, crop)
        ↓
OCR Engine (Tesseract / Cloud Vision API / Azure OCR)
        ↓
Text Analysis (NLP for field identification)
        ↓
Field Extraction (ML models trained on document types)
        ↓
Confidence Scoring (per-field confidence 0-100%)
        ↓
Routing Decision:
├── All fields > 90% confidence → Auto-post
└── Any field < 90% → Queue for human review
        ↓
Human Review Interface (highlight uncertain fields)
        ↓
Approval → Auto-post to target module
        ↓
Feedback Loop (corrections retrain model)
`

### 59.4 Document Storage & Compliance

| Aspect | Implementation |
|--------|---------------|
| **Storage** | Cloudflare R2 (encrypted, versioned) |
| **Retention** | Configurable per document type (7 years for tax docs) |
| **Access Control** | RBAC — only authorized roles can view sensitive docs |
| **Audit Trail** | Who uploaded, who accessed, who edited, when |
| **Digital Signature** | Integration with MySignatures or equivalent |
| **Version Control** | Every modification creates new version |
| **Export** | Download as PDF, ZIP archive, or API access |

### 59.5 Data Model

| Table | Purpose |
|-------|---------|
| documents | Document metadata (type, source, upload date, status) |
| document_ocr_results | Raw OCR text output |
| document_fields | Extracted fields with values and confidence scores |
| document_review_queue | Documents awaiting human review |
| document_reviews | Review actions (approved, corrected, rejected) |
| document_links | Links to domain records (bill, contact, contract) |
| document_templates | Template definitions for new document types |
| extraction_models | ML model versions and performance metrics |

### 59.6 Integration Points

| Module | How B-Document AI Helps |
|--------|------------------------|
| **B-Invoicing** | Auto-create bills from supplier invoices |
| **B-Procurement** | Match delivery notes with POs |
| **B-HRMS** | Extract data from employment forms, IDs |
| **B-Finance** | Receipt validation for expense claims |
| **B-Compliance** | Store regulatory documents with retention policies |
| **B-Brain Hub** | Train better models on accumulated document data |

### 59.7 AI Model Training Loop

`
New Documents Uploaded
        ↓
Some Require Human Review
        ↓
Review Corrections Stored
        ↓
Weekly Batch: Retrain Models on Corrections
        ↓
New Model Version Tested on Validation Set
        ↓
If Accuracy Improved → Deploy to Production
        ↓
Monitor New Model Performance
        ↓
Rollback if Performance Degrades
`

---

## BAB 60 — Competitive Analysis & Market Positioning

### 60.1 Market Landscape Overview

AI BIJAN operates in the business software market where incumbents dominate specific segments but none offer the complete combination of features that AI BIJAN plans to deliver:

| Segment | Dominant Players | Weakness AI BIJAN Exploits |
|---------|-----------------|---------------------------|
| **Enterprise ERP** | SAP, Oracle, Microsoft | Too expensive, complex, slow for SMEs |
| **E-commerce** | Shopify, WooCommerce | Limited to online store only |
| **CRM/Marketing** | HubSpot, Salesforce | Operations/finance not included |
| **All-in-One SMB** | Zoho One, Odoo | Quality varies, not Malaysia-focused |
| **Accounting** | QuickBooks, Xero, AkaunSoft | Single-function, no operations |

### 60.2 Feature Parity Summary

> **⚠️ SUMBER DATA & TARIKH (A-22):**
>
> Penilaian feature parity berdasarkan sumber berikut — semua perlu disemak semula sebelum penggunaan komersial:
> 
> | Sistem | Sumber | Tarikh Akses | Plan Tier Dibandingkan |
> |--------|--------|--------------|----------------------|
> | QuickBooks Online Advanced | quickbooks.intuit.com/pricing | Oktober 2026 | Advanced ($200/mo US) |
> | Odoo Inventory | odoo.com/pricing | Oktober 2026 | Enterprise |
> | HubSpot Enterprise | hubspot.com/products | Oktober 2026 | Enterprise (custom pricing) |
> | Shopify Plus | shopify.com/plus/pricing | Oktober 2026 | Plus ($2,000/mo US) |
> | AkaunSoft HR | akaunsoft.com.my | Oktober 2026 | SME Package |
> | SAP S/4HANA | sap.com/products/s4-hana | Oktober 2026 | Enterprise (quote-based) |
> | Oracle NetSuite | oracle.com/netsuite | Oktober 2026 | Professional Edition |
> | Microsoft D365 | dynamics.microsoft.com | Oktober 2026 | Business Central |
> | Salesforce | salesforce.com/products | Oktober 2026 | Enterprise |
> | Zoho One | zoho.com/one/pricing | Oktober 2026 | One ($37/user/mo) |
> | Odoo Enterprise | odoo.com/pricing | Oktober 2026 | Enterprise |
> | Xero | xero.com/my/pricing | Oktober 2026 | Standard (RM250/mo MY) |
>
> **Nota:** Harga dalam USD perlu dinormalisasi ke MYR menggunakan exchange rate terkini. "Good parity" bermaksud AI BIJAN memenuhi ≥ 80% fungsi utama yang ditawarkan oleh alternatif.

| Capability Area | AI BIJAN Level | Best-in-Class Alternative | Gap Assessment |
|----------------|---------------|--------------------------|----------------|
| **Finance & Accounting** | Designed (BAB 14) | QuickBooks Online Advanced | Need project accounting, MFRS depth |
| **Inventory & Warehouse** | Designed (BAB 13) | Odoo Inventory | Need serial/batch tracking, ABC/VTFR |
| **CRM & Sales** | Basic (BAB 54 planned) | HubSpot Enterprise | Need full SFA, lead scoring, forecasting |
| **Marketing Automation** | Designed (BAB 53) | HubSpot Marketing | Good parity once B-Marketing enhanced |
| **E-commerce** | Planned (BAB 53) | Shopify Plus | Good parity for SME needs |
| **HR & Payroll** | Partial (BAB 12) | AkaunSoft HR | **CRITICAL GAP**: MY statutory compliance |
| **POS** | Building (BAB 12) | Shopify POS | Good parity for retail/F&B |
| **AI Agents** | Unique (BAB 16-17) | None (best: Copilot, Einstein) | **UNFAIR ADVANTAGE**: Autonomous + Memory |
| **Industry Packs** | Unique (BAB 15) | SAP/Oracle only at enterprise cost | **UNFAIR ADVANTAGE**: Affordable for SMEs |
| **Localization** | Malaysia-first (BAB 11) | Western systems = adapter layer | **UNFAIR ADVANTAGE**: Built-in, not bolted-on |

### 60.3 Competitive Score (Out of 10)

> **⚠️ METODOLOGI PENILAIAN — BACA DULU:**
>
> Skor di bawah adalah **TARGET/HYPOTHETICAL**, bukan fakta pasaran. Setiap skor berdasarkan:
> - Skala 1-10 (1 = tiada, 5 = sederhana, 10 = terbaik dalam kelas)
> - Tarikh penilaian: Oktober 2026
> - Sumber data: laman web rasmi, dokumentasi produk, ulasan awam (semua tanpa tarikh spesifik)
> - AI BIJAN skor berdasarkan reka bentuk dokumen ini, bukan prestasi sebenar
>
> **Berat Kategori** (jumlah = 100%):
> | Kategori | Berat | Alasan |
> |----------|-------|--------|
> | ERP | 25% | Fungsi teras perniagaan |
> | CRM | 15% | Pengurusan hubungan pelanggan |
> | Commerce | 15% | Keupayaan e-dagang |
> | HR/Payroll | 15% | Pengurusan pekerja & gaji |
> | AI | 15% | Keupayaan kecerdasan buatan |
> | Localization | 15% | Tempatan untuk Malaysia/ASEAN |
>
> **Formula:** `Weighted Total = Σ(Skor × Berat)` untuk setiap kategori.

| System | ERP | CRM | Commerce | HR/Payroll | AI | Localization | **Weighted Total** |
|--------|-----|-----|----------|-----------|-----|-------------|-------------------|
| SAP S/4HANA | 10 | 7 | 4 | 8 | 6 | 5 | **7.0** |
| Oracle NetSuite | 9 | 7 | 5 | 7 | 6 | 6 | **7.0** |
| Microsoft D365 | 8 | 8 | 5 | 7 | 8 | 6 | **7.0** |
| Salesforce | 3 | 10 | 4 | 4 | 8 | 5 | **5.7** |
| Zoho One | 6 | 6 | 5 | 6 | 5 | 5 | **5.7** |
| Odoo Enterprise | 6 | 6 | 6 | 5 | 4 | 4 | **5.3** |
| QuickBooks Adv | 5 | 1 | 2 | 5 | 3 | 6 | **3.7** |
| Xero | 4 | 1 | 2 | 5 | 2 | 6 | **3.3** |
| **AI BIJAN (v7 Planned)** | **7** | **7** | **7** | **6** | **10** | **10** | **7.7** |

> **AI BIJAN's v7 target score of 7.7 exceeds all competitors** when combining AI capability, breadth, and localization. This assumes all planned modules are delivered with sufficient quality.

### 60.4 Unfair Advantages

| Advantage | Why Competitors Can't Easily Copy |
|-----------|----------------------------------|
| **Malaysia-first statutory compliance** | Western systems treat Malaysia as an afterthought; local players lack AI and scale |
| **Collective Intelligence network effects** | Single-tenant or isolated systems cannot aggregate anonymized insights across tenants |
| **AI autonomy with governance (L0-L5)** | Competitors bolt on AI chat; AI BIJAN is born AI-native with approval frameworks |
| **WhatsApp-first UX** | Malaysian businesses live on WhatsApp; Western systems assume email/web |
| **Unlimited users at flat rate** | Per-user pricing kills adoption in SME teams with 10-50 staff |
| **Bilingual (BM/EN) natively** | English-first systems alienate non-English speaking owners and workers |
| **19 Industry Packs for Malaysian sectors** | School transport, childcare, waste collection, security — generic ERP doesn't understand these |
| **Affordable at RM288-1,888/month** | Enterprise systems start at RM5,000+/month; open-source requires technical expertise |

### 60.5 Critical Gaps to Close (Priority Order)

| Priority | Gap | Impact if Not Closed | Timeline |
|----------|-----|---------------------|----------|
| **P0** | B-Payroll (MY Compliance) | Cannot compete in primary market | Months 1-3 |
| **P0** | B-Finance Extended (project accounting, MFRS, consolidation) | Customers outgrow at ~RM5M revenue | Months 1-6 |
| **P1** | B-SCM (supplier mgmt, demand planning, advanced inventory) | Lose manufacturing/trading segment (40% of SMEs) | Months 4-9 |
| **P1** | Deep CRM SFA (lead scoring, forecasting, mobile offline) | Sales teams still need HubSpot/Salesforce | Months 4-9 |
| **P1** | B-Document AI (OCR, receipt extraction) | Miss productivity multiplier feature | Months 6-12 |
| **P2** | B-Mobile (Flutter native apps) | Limit adoption in mobile-first culture | Months 12-18 |
| **P2** | B-Hardware Bridge (printers, scanners, biometrics) | Physical businesses limited without hardware | Months 12-18 |
| **P2** | B-Benchmark (collective intelligence) | Miss ultimate moat feature | Months 12-18 |

### 60.6 Pricing Strategy (Configurable per Country from Master Admin)

Semua harga pakej dan token boleh diubah dari **Master Admin Panel** mengikut negara, mata wang, dan strategi pasaran.

#### Default Plan Structure (Malaysia - MYR)

| Plan | Price | Features Included | Target Segment |
|------|-------|------------------|---------------|
| **Starter** | RM 288/month | POS, Invoicing, Inventory, B-Site, Basic CRM, 80M AI tokens | Micro-business (1-3 staff) |
| **Professional** | RM 488/month | + HRMS, Payroll, Marketing Automation, 200M AI tokens | Small business (5-20 staff) |
| **Business** | RM 888/month | + SCM, Manufacturing, Advanced Analytics, 1B AI tokens | Growing SME (20-100 staff) |
| **Business Pro** | RM 1,288/month | + Priority Support, Custom Integrations, 2.25B AI tokens | Established SME (50-200 staff) |
| **Enterprise** | RM 1,888/month | + White-label, Unlimited AI tokens, Dedicated support | Multi-location groups, resellers |

#### Multi-Country Pricing Examples

| Plan | Malaysia (MYR) | Singapore (SGD) | Indonesia (IDR) | Pakistan (PKR) | Bangladesh (BDT) |
|------|---------------|----------------|----------------|----------------|------------------|
| **Starter** | RM 288/mth | S$ 85/mth | Rp 350.000/bulan | Rs 6.000/bulan | ৳ 3.200/bulan |
| **Professional** | RM 488/mth | S$ 145/mth | Rp 600.000/bulan | Rs 11.000/bulan | ৳ 5.800/bulan |
| **Business** | RM 888/mth | S$ 265/mth | Rp 1.200.000/bulan | Rs 20.000/bulan | ৳ 11.000/bulan |
| **Business Pro** | RM 1,288/mth | S$ 385/mth | Rp 1.700.000/bulan | Rs 28.000/bulan | ৳ 15.500/bulan |
| **Enterprise** | RM 1,888/mth | S$ 565/mth | Rp 2.600.000/bulan | Rs 40.000/bulan | ৳ 22.000/bulan |

> **Semua harga di atas adalah CONTOH.** Super Admin boleh:
> - Ubah mana-mana harga untuk mana-mana negara pada bila-bila masa
> - Tambah atau buang plan baru
> - Set discount/promo codes
> - Configure annual billing dengan auto-discount
> - Enable/disable country-specific plans
> - Set exchange rate manual atau auto-fetch

**All plans include:** Unlimited users, email/WhatsApp notifications, community support, standard integrations, mobile-responsive UI.

### 60.7 Go-to-Market Sequence (Global Expansion)

```
Phase 1 (Months 1-6): Foundation — Malaysia
├── Launch: POS, Inventory, Invoicing, Basic HR, B-Payroll
├── Target: 100 beta customers (free for feedback)
├── Focus: Retail, F&B, professional services
├── Pricing: MYR-based plans (RM288-RM1,888)
└── Goal: Validate product-market fit in home market

Phase 2 (Months 6-12): Growth — Malaysia + Regional Prep
├── Add: B-SCM, Deep CRM, B-Document AI
├── Launch: Top 5 industry packs
├── Target: 500 paying customers
├── Focus: Transport, healthcare, education, construction
├── Multi-currency engine ready (SGD, IDR, PKR)
└── Goal: Establish category leadership in Malaysia

Phase 3 (Months 12-18): Scale — SEA Expansion
├── Launch: Plugin marketplace, B-Mobile apps
├── Launch: B-Benchmark (collective intelligence)
├── Target: 2,000 paying customers
├── Expand: Indonesia, Singapore, Brunei, Philippines
├── Local payment gateways (Midtrans, Stripe SG, GCash)
├── Local compliance (GST SG, PDPA ID, e-Invois MY)
└── Goal: Regional presence across SEA

Phase 4 (Months 18-36): Global South Dominance
├── Enterprise features (consolidation, multi-entity)
├── Hardware ecosystem (printers, scanners, biometrics)
├── Financial services (lending based on business data)
├── Target: 10,000+ paying customers
├── Expand: Pakistan, Bangladesh, India, Nigeria, Kenya
├── Localize: Urdu, Bengali, Hindi, Swahili interfaces
├── Payment methods: JazzCash, EasyPaisa, M-Pesa, UPI
└── Focus: Ecosystem play, platform economy
```

### 60.8 Risk Matrix (Global)

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Scope creep — building too much too fast | High | Critical | Strict prioritization; ship MVP modules first |
| Zoho adds Malaysia payroll quickly | Medium | High | Move faster; Zoho adapts slowly to local needs |
| Odoo community builds better MY packages | High | Medium | Focus on AI differentiation; open-source is double-edged |
| Performance degradation with multi-tenancy | Medium | High | Early investment in tenant isolation, caching, indexing |
| e-Invois requirements change | High | Medium | Configurable mapping; rapid update capability |
| AI model reliability issues | Medium | Medium | Human-in-the-loop design; clear confidence scores |
| Cross-border payment failures | Medium | High | Multi-gateway strategy; fallback payment methods |
| Currency fluctuation impact | Medium | Medium | Monthly price reviews; buffer margins |
| Local compliance complexity | High | High | Hire local legal counsel per country; phased rollout |
| Data residency requirements | Low | High | Regional data centers (SG for SEA, EU for Europe) |
| Competitor price wars in emerging markets | Medium | Medium | Value-based pricing, not race-to-bottom |

---

> **BAB 56A-60 dicipta pada 19 Oktober 2026.**  
> **Menutup siklus dokumentasi lengkap: Commerce (B-Site), CRM, Service Desk, Go-to-Market, AI Live Interaction, Mobile Apps, CCTV Surveillance (56C), B-Payroll, B-SCM, B-Document AI, Market Positioning.**  
> **Status: Perancangan strategik — panduan pelaksanaan Fasa 0-4.**  
> **Competitive Score Target: 7.7/10 (melampaui SAP 7.0, Oracle 7.0, MSFT 7.0).**  
> **Platform: Global-ready, multi-currency, multi-country pricing configurable dari Master Admin.**
> **New Features: AI Voice/Video/Camera (56A), Native Mobile Apps Android+iOS (56B), CCTV Surveillance & AI Security (56C)**

---

## BAB 99 — Canonical Glossary (Audit Fix A-25)

> **⚠️ GLOSSARY RASMI:** Semua istilah dalam dokumen ini mesti merujuk kepada definisi berikut. Jika terdapat percanggahan antara istilah dalam bab berbeza, gunakan glossary ini sebagai sumber kebenaran.

### 99.1 Module Abbreviations

| Istilah Penuh | Slug | Display Name | Owner | Abbreviation |
|--------------|------|--------------|-------|-------------|
| Business Hub | b-hub | B-Hub | Platform Team | BHUB |
| Task Management | b-task | B-Task | Platform Team | BTASK |
| Workspace | b-workspace | B-Workspace | Platform Team | BWS |
| Memory System | b-memory | B-Memory | AI Team | BMEM |
| Brain Hub | b-brain | B-Brain Hub | AI Team | BRAIN |
| Site Builder | b-site | B-Site | Commerce Team | BSITE |
| Link/Linktree | b-link | B-Link | Commerce Team | BLINK |
| Point of Sale | b-pos | B-POS | Commerce Team | BPOS |
| Marketing Automation | b-marketing | B-Marketing | Growth Team | MKT |
| Customer Relationship | b-crm | B-CRM | Sales Team | CRM |
| Service Desk | b-service | B-Service Desk | Support Team | SDesk |
| Invoicing | b-invoicing | B-Invoicing | Finance Team | INV |
| Finance Core | b-finance | B-Finance | Finance Team | FIN |
| Supply Chain | b-scm | B-SCM | Operations Team | SCM |
| Manufacturing | b-mfg | B-Manufacturing | Operations Team | MFG |
| HR Management | b-hrms | B-HRMS | HR Team | HRMS |
| Payroll | b-payroll | B-Payroll | HR Team | PAY |
| Inventory | b-inventory | B-Inventory | Operations Team | INV |
| Logistics | b-logistics | B-Logistics | Operations Team | LOG |
| Compliance | b-compliance | B-Compliance | Legal Team | COMP |
| Scout/Intel | b-scout | B-Scout | Strategy Team | SCOUT |
| Document AI | b-doc-ai | B-Document AI | AI Team | DOCAI |
| Event & Rental | b-event | B-Event & Rental | Industry Teams | EVT |
| Asset Management | b-assets | B-Assets | Ops/Admin | ASSET |
| Booking | b-booking | B-Booking | Industry Teams | BOOK |
| Loyalty | b-loyalty | B-Loyalty | Growth Team | LOY |
| Omnichannel | b-omni | B-Omnichannel | Commerce Team | OMNI |
| Notify | b-notify | B-Notify | Platform Team | NOTIFY |

### 99.2 Event & API Namespaces

| Event Name | Namespace | Triggered By |
|-----------|-----------|-------------|
| Invoice Created | `invoice.created` | B-Invoicing |
| Payment Received | `payment.received` | B-Finance |
| Order Placed | `order.placed` | B-POS / B-Site |
| Stock Low | `inventory.low_stock` | B-Inventory |
| Task Completed | `task.completed` | B-Task |
| User Registered | `user.registered` | B-Auth |
| AI Task Executed | `ai.task.executed` | B-Brain Hub |
| Workflow Started | `workflow.started` | B-Flow |
| Subscription Renewed | `subscription.renewed` | Billing |
| Tenant Created | `tenant.created` | Super Admin |

### 99.3 Status Definitions

| Status | Definition | Evidence Required |
|--------|-----------|-------------------|
| Planned | Dicadangkan dalam roadmap | Ticket di project tracker |
| Specified | Keperluan dan kontrak didokumenkan | PRD + API spec (OpenAPI) |
| In Development | Sedang dibangunkan | Branch aktif, CI passing |
| Internal QA | Sedang diuji secara dalaman | Test report, coverage ≥ 80% |
| Pilot | Digunakan oleh kumpulan pelanggan terhad | Pilot agreement, feedback log |
| Production Ready | Lulus semua release gate | See BAB 0.2 gate checklist |
| Deprecated | Tidak lagi disyorkan | Migration guide + sunset date |

### 99.4 Technical Terms

| Term | Definition |
|------|-----------|
| Multi-tenancy | Satu codebase menyokong banyak tenant dengan data terasing |
| Tenant ID | Unique identifier untuk setiap organisasi/customer |
| AI Credit | Unit currency untuk billing penggunaan AI model API |
| Vector Embedding | Representasi numerik teks untuk similarity search |
| RAG (Retrieval-Augmented Generation) | Teknik AI yang combine retrieval dari database dengan generation |
| SLA (Service Level Agreement) | Komitmen prestasi antara provider dan customer |
| ARPU | Average Revenue Per User — purata pendapatan per tenant |
| CAC | Customer Acquisition Cost — kos untuk acquire satu customer baru |
| Churn Rate | Peratusan customer yang berhenti subscribe per bulan |
| MRR | Monthly Recurring Revenue — pendapatan berulang bulanan |
| ARR | Annual Recurring Revenue — pendapatan berulang tahunan |
| POS | Point of Sale — sistem jualan kaunter |
| SST | Sales and Service Tax — cukai jualan dan perkhidmatan Malaysia |
| e-Invois | E-Invoice requirement oleh LHDN Malaysia |
| MSIC | Malaysian Standard Industrial Classification — kod industri |
| PDPA | Personal Data Protection Act 2010 (Act 709) — undang-undang perlindungan data Malaysia |
| MFRS | Malaysian Financial Reporting Standard |
