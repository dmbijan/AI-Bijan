# AI BIJAN SaaS — Dokumen Induk Seni Bina & Produk

## Versi 4.5.1 Multi-Tier Admin & Platform Business (pembetulan editorial 18/10/2026)

**Tarikh:** 18 Oktober 2026  
**Nama produk:** AI BIJAN SaaS  
**Tagline:** *Your Team, in Virtual Office*  
**Pemilik konsep:** Bijan  
**Bahasa utama:** Bahasa Melayu dan English  
**Model produk:** Multi-tenant SaaS, modular, API-first, industry-aware  
**Pasaran utama:** Malaysia  
**Admin Hierarchy:** Master → Super → White-label → Customer  
**Platform Features:** Brand portfolio, revenue sharing, white-label, subscription engine, AI reseller  

---

## Status Dokumen

> **Status:** Dokumen gabungan induk yang menyatukan:
> - **AI BIJAN SaaS Master Blueprint v4.5** — Product, Architecture, Industry Coverage, Technical Specification & Multi-Tier Admin System
> - **Architecture v2.5 MASTER CONSOLIDATED** — Seni bina teknikal, AI Gateway, modular monolith, platform business, brand management
> 
> **Prinsip utama:** Sistem berdiri sendiri (stand-alone), multi-tenant, berasaskan AI, sesuai untuk hosting cPanel.  
> **Keperluan wajib:** AI BIJAN dibangunkan sebagai sistem sendiri. **ERPNext bukan dependency**, bukan backend, bukan pangkalan data aplikasi, bukan UI, dan bukan enjin operasi yang diperlukan untuk menjalankan AI BIJAN.
> 
> **Perhatian:** Dokumen ini ialah spesifikasi perancangan. Ia bukan bukti bahawa setiap fungsi telah dikodkan, diuji, diluluskan dari sudut pematuhan atau tersedia kepada pelanggan.

---

## Struktur Dokumen Gabungan

| Bahagian | Sumber | Kandungan |
|----------|--------|-----------|
| 0 | V4.1 | Tujuan, skop, prinsip, istilah status |
| 1 | V4.1+V3.0 | Ringkasan eksekutif, matlamat, perkara tidak tersedia automatik |
| 2 | V4.1 | Model seni bina platform (layers, modular monolith, data ownership) |
| 3 | V4.1+V2.4 | B-Auth & Security |
| 4 | V4.1 | Tenant, Organization, Branch & Membership |
| 4a | V4.5 | Platform Business & Brand Management |
| 4b | V4.5 | Multi-Tier Admin Hierarchy (Master/Super/WL/Customer) |
| 5 | V4.1 | Role, Permission & Entitlement |
| 6 | V4.1 | Subscription, Billing & Usage Metering |
| 6a | V4.5 | Subscription & Monetization Engine — Admin Panel |
| 7 | V4.1 | Notifications |
| 8 | V4.1 | Files & Document Storage |
| 9 | V4.1 | Audit Trail |
| 10 | V4.1 | API, Webhooks & Integrations |
| 11 | V4.1 | Localization & Malaysia Configuration |
| 12 | V4.1+V3.0 | Modul Perniagaan Umum (semua modul) |
| 13 | V4.0+V3.0 | Direktori Industri Malaysia & MSIC Reference |
| 14 | V4.1+V4.0 | Industry Blueprint Engine |
| 15-29 | V4.1 | B-Event & Rental (spesifikasi lengkap) |
| 30-37 | V4.1+V3.0 | B-Brain Hub & AI Token Wallet |
| **33a** | **V4.5** | **AI Reseller Platform — Admin HQ & Serveras.click Integration** |
| **33b** | **V4.5** | **Platform Business Setup & Domain Configuration** |
| 38-40 | V4.1 | Finance, Payments & Malaysia Integrations |
| 41-44 | V4.1 | Data, Reports & Governance |
| 45-47 | V4.1 | UI/UX & Product Experience |
| 48-49 | V4.1+V2.4 | QA, Testing & Release Readiness |
| 50-56 | V4.1+V3.0 | Roadmap Pembangunan Berfasa |
| 57-58 | V4.1 | Definition of Done |
| 59 | V4.1 | Open Decisions Register |
| 60 | V4.1 | Recommended Document Set |
| 61-62 | V4.1 | Versioning Policy & Changelog |
| 63-68 | V4.2 | B-Personal AI Assistant | 
| 69-84 | V4.2 | B-Business Understanding Engine | 
| 85-97 | V4.2 | B-Autonomous Business Growth Engine | 
| 98-102 | V4.2 | B-Autonomous Operations Orchestrator | 
| 103-106 | V4.2 | Autonomy, Safety & Control Plane | 
| 107-109 | V4.2 | AI Business Performance & Revenue Attribution | 
| 110-113 | V4.2 | Additional Data Model | 
| 114-116 | V4.2 | API and Tool Contracts | 
| 117-122 | V4.2 | Acceptance Tests for Personal AI & Autonomous Business | 
| 123 | V4.2 | Implementation Roadmap Addendum (Phase A-G) | 
| 124-125 | V4.2 | Final Product Principles & Vision Definition |
| 126-131 | V4.3 | Personal Brand & Social Media Control |
| 132-140 | V4.3 | Continuous AI Learning Loop |
| 141-143 | V4.3 | Additional Data Model (Brand & Learning) |
| 144-145 | V4.3 | API Contracts for Brand & Learning |
| 146-148 | V4.3 | Acceptance Tests for Brand & Learning |
| 149 | V4.3 | Implementation Roadmap (Phase B1-L3 + Integrated) |
| 152-172 | V4.5 | Business Access Management & Organizational Structure (Part XXXIII) |
| 173-177 | V4.5 | Platform Business, Brand Portfolio & Multi-Tier Admin System |

---

**Ringkasan Lengkap:** 33 Part (I–XXXIII), 178 Section (0–177)  
**Tarikh Konsolidasi:** 18 Oktober 2026  
**Fail Master:** `AI-BIJAN-MASTER-BLUEPRINT-v4.5.md`  
**Tambah Baru (v4.5):** Seksyen 4a (Platform Business & Brand), 4b (Multi-Tier Admin Hierarchy), 6a (Subscription Engine), 33b (Domain Configuration)  
**Admin Model:** 4-tier hierarchy — Master Admin → Super Admin → White-label Admin → Pelanggan Admin  
**Revenue Model:** SaaS subscription + AI token packages + white-label revenue sharing

---

# PART I — VISI PRODUK DAN SENI BINA

## 0. Tujuan, Skop dan Peraturan Dokumen

Dokumen ini menetapkan visi produk, seni bina, modul perniagaan, direktori industri, struktur data, workflow, keselamatan, AI, integrasi, pengujian dan pelaksanaan AI BIJAN.

Dokumen ini ialah spesifikasi perancangan. Ia bukan bukti bahawa setiap fungsi telah dikodkan, diuji, diluluskan dari sudut pematuhan atau tersedia kepada pelanggan.

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
| **Regulated Workflow Reviewed** | Aliran khusus telah disemak berdasarkan keperluan peraturan berkaitan |
| **Deprecated** | Tidak lagi disyorkan dan mempunyai pelan migrasi |

> Jangan menggunakan status ini tanpa bukti yang boleh diaudit.

---

## 1. Ringkasan Eksekutif

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

## 2. Model Seni Bina Platform

### 2.1 Lapisan Platform

```
AI BIJAN PLATFORM
│
├── Experience Layer
│   ├── Web Application
│   ├── Mobile Application
│   ├── Admin / Platform HQ
│   ├── Customer / Partner Portals
│   └── API Clients
│
├── Shared Core
│   ├── Authentication & Security
│   ├── Tenant / Organization / Branch
│   ├── Roles & Permissions
│   ├── Subscription & Entitlements
│   ├── Billing & Usage Metering
│   ├── Notifications
│   ├── Files & Documents
│   ├── Audit Trail
│   ├── API & Webhooks
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
│   ├── AI Gateway
│   ├── Model Catalog
│   ├── Agent Runtime
│   ├── Knowledge Retrieval
│   ├── Tool Registry
│   ├── Approval Policies
│   └── AI Usage Ledger / Token Wallet
│
├── Industry Blueprint Engine
│   ├── Industry Directory
│   ├── Module Recommendations
│   ├── Workflow Templates
│   ├── Custom Fields
│   ├── Permission Templates
│   └── Versioned Blueprint Releases
│
└── Infrastructure & Operations
    ├── Database
    ├── Queue / Background Jobs
    ├── Cache
    ├── File Storage
    ├── Observability
    ├── Backup & Restore
    └── Security / Incident Management
```

### 2.2 Seni Bina yang Disyorkan

Mulakan dengan **modular monolith** jika sesuai dengan saiz pasukan dan beban kerja. Pisahkan modul melalui kontrak domain dan API dalaman yang jelas.

Jangan memecahkan sistem kepada microservices hanya kerana bilangan modul bertambah. Microservices menambah kos deployment, observability, rangkaian, retry, data consistency dan operasi.

Pertimbangkan servis berasingan apabila terdapat bukti keperluan seperti:

- Beban kerja yang mempunyai corak skala sangat berbeza
- Keperluan keselamatan atau deployment berasingan
- Pasukan bebas yang mengurus domain berasingan
- Had prestasi yang tidak dapat diselesaikan secara munasabah dalam seni bina sedia ada

### 2.3 Peraturan Pemilikan Data

Setiap domain mempunyai sumber data rasmi atau **system of record**.

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
| Rekod klinikal | B-Clinic & Care, jika dibangunkan |

Modul lain tidak boleh mengubah jadual domain tersebut secara terus tanpa kontrak yang diluluskan.

---

# PART II — SHARED CORE

## 3. B-Auth & Security

### 3.1 Skop

- Pendaftaran dan log masuk
- Pengesahan e-mel atau nombor telefon
- Password reset
- Multi-factor authentication jika diperlukan
- Pengurusan sesi dan token
- Pengesahan API
- OAuth atau social login jika disokong
- Device/session management
- Account recovery
- Login history dan security events
- Service identity untuk integrasi dan AI agents

### 3.2 Keperluan Keselamatan

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

### 3.3 Acceptance Tests

- Pengguna tenant A tidak boleh membaca data tenant B
- Pengguna yang dibuang daripada organisasi kehilangan akses sewajarnya
- Token yang tamat tempoh ditolak
- Token yang telah dibatalkan tidak boleh digunakan semula
- Reset password tidak membocorkan kewujudan akaun secara berlebihan
- Endpoint sensitif menolak pengguna tanpa permission walaupun UI menyembunyikan butang
- Service identity mempunyai skop dan expiry yang ditetapkan

## 4. Tenant, Organization, Branch & Membership

### 4.1 Model

Satu pengguna global boleh menjadi ahli kepada beberapa organisasi. Satu organisasi boleh mempunyai beberapa cawangan, jenama, jabatan atau unit operasi.

Entiti teras:

- `users`, `tenants`, `organizations`, `organization_memberships`
- `branches`, `departments`, `brands`
- `tenant_settings`, `tenant_module_entitlements`
- `tenant_domains`, `tenant_invitations`

Nama jadual ialah cadangan, bukan migrasi yang telah dilaksanakan.

### 4.2 Peraturan

- Setiap rekod domain tenant-scoped mesti mempunyai skop tenant yang boleh disahkan
- Jangan mempercayai `tenant_id` yang dihantar oleh klien tanpa authorization
- Cawangan mesti berada di bawah organisasi yang sah
- Membership perlu mempunyai status dan tempoh akses
- Data cross-branch mesti dikawal mengikut role dan permission
- Perubahan struktur organisasi perlu mempunyai audit log

---

## 4a. Platform Business & Brand Management

### 4a.1 Konsep Utama

AI BIJAN bukan sekadar "platform kosong" — ia adalah **bisnes dengan identiti sendiri**. Platform owner (Bijan Holdings) perlu setup:
- Nama syarikat, SSM, tax ID, alamat berdaftar
- Brand portfolio (parent brand + sub-brands)
- Domain configuration (main domain, subdomain pattern, custom domain)
- Invoice settings (footer text, payment terms, late fee)
- Bank account untuk payment collection

Ini dibezakan daripada tenant customer yang hanya setup organization mereka sendiri.

### 4a.2 Platform Business Profile

| Field | Maksud | Contoh |
|-------|--------|--------|
| **Company Name** | Nama syarikat platform | Bijan Holdings Sdn Bhd |
| **SSM Number** | No pendaftaran ssm | 202XXX0XX-X |
| **Tax ID (EIN)** | Identification cukai | X-XXXXXXX-X |
| **Registered Address** | Alamat berdaftar rasmi | Lot XXX, Jalan XXX, XXX XX Kota Bharu, Kelantan |
| **Contact Email** | Email utama platform | admin@aibijan.com |
| **Contact Phone** | Nombor telefon | +60 XXX-XXXX XXXX |
| **Currency** | Mata wang default | MYR |
| **Timezone** | Timezone platform | Asia/Kuala_Lumpur |
| **Bank Account** | Info akaun bank untuk payment | Maybank 1234XXXXXXXX |
| **Invoice Footer** | Teks di bawah invois | Powered by AI BIJAN © 2026 |

### 4a.3 Brand Portfolio Structure

Hierarki brand yang boleh dikawal oleh Master Admin:

```
BIJAN HOLDINGS SDN BHD (Platform Owner)
│
├── AI BIJAN (Main SaaS Platform)
│   ├── aibijan.com
│   └── SaaS Subscription Plans
│       ├── Starter (RM49/bulan)
│       ├── Business (RM149/bulan)
│       ├── Professional (RM349/bulan)
│       └── Enterprise (RM799/bulan)
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

Setiap brand boleh ada:
- Nama, slug, description, icon, color
- Status (active/inactive/archived)
- Modul yang termasuk dalam brand
- Visibility kepada tenants

### 4a.4 Domain Configuration

| Domain Type | Pattern | Kegunaan |
|------------|---------|----------|
| **Main Domain** | `aibijan.com` | Platform landing page, auth, admin panel |
| **Subdomain Pattern** | `{tenant}.aibijan.com` | Default subdomain setiap tenant |
| **Custom Domain Rule** | `{custom-domain}` | Tenant boleh guna domain sendiri (white-label) |

**Peraturan:**
- SSL auto-provisioned untuk main domain dan subdomain pattern
- Custom domain memerlukan DNS verification (CNAME atau TXT record)
- Super Admin boleh enable/disable custom domain per tenant
- White-label admin boleh configure custom domain untuk tenant mereka

### 4a.5 Database Schema — Platform Business

```sql
-- Platform profile (data platform owner)
platform_profiles (
    id, company_name, ssm_number, tax_id, registered_address,
    contact_email, contact_phone, currency, timezone,
    logo_url, invoice_footer_text, terms_url, privacy_url,
    created_at, updated_at
)

-- Brand portfolio (parent + sub-brands)
brand_portfolio (
    id, parent_brand_id, name, slug, description, status,
    icon_url, color_hex, sort_order, created_at, updated_at
    -- parent_brand_id = NULL untuk top-level brand (AI BIJAN)
)

-- Brand-to-module mapping
brand_modules (
    id, brand_id, module_slug, is_visible_to_tenants, requires_approval
)

-- Domain configuration
platform_domains (
    id, domain_type, domain_value, ssl_enabled, redirect_to, status
    -- domain_type: 'main', 'subdomain_pattern', 'custom'
    -- domain_value: 'aibijan.com', '*.aibijan.com', 'clientbrand.com'
)

-- Tenant brand customization (white-label)
tenant_brand_customization (
    id, tenant_id, custom_logo_url, custom_primary_color,
    custom_font, custom_domain, white_label_enabled,
    verified_at, verified_by
)
```

---

## 4b. Multi-Tier Admin Hierarchy

### 4b.1 Konsep Sistem

AI BIJAN menggunakan **4 level admin** dengan权限 berbeza. Setiap level mempunyai scope, kuasa dan tanggungjawab yang tersendiri.

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
admin_roles (
    id, name, slug, description, level, created_at
    -- level: 'master', 'super', 'white_label', 'customer'
)

-- Admin user assignment (link user to role + tenant scope)
admin_role_assignments (
    id, user_id, role_id, tenant_id, branch_id, assigned_at, assigned_by
    -- tenant_id = NULL untuk master/super admin (global access)
    -- branch_id = NULL jika tidak branch-scoped
)

-- White-label configuration
white_label_configs (
    id, admin_id, parent_brand_id, custom_logo_url,
    custom_primary_color, custom_font, custom_domain,
    white_label_enabled, invoice_footer_text,
    revenue_share_percentage, status, created_at, updated_at
)

-- Tenant allocation (which white-label admin manages which tenants)
wl_tenant_allocations (
    id, wl_admin_id, tenant_id, allocated_at, notes
)

-- White-label pricing override (WL admin boleh adjust within limits?)
wl_pricing_overrides (
    id, wl_admin_id, tenant_id, plan_version_id,
    custom_price_monthly, custom_price_annual,
    valid_from, valid_until, approved_by, approved_at
)

-- Revenue sharing tracking
revenue_shares (
    id, tenant_subscription_id, wl_admin_id, amount_total,
    wl_revenue, platform_revenue, share_percentage,
    calculated_at, paid_at, payout_reference
)

-- White-label audit trail
wl_audit_logs (
    id, wl_admin_id, action, entity_type, entity_id,
    old_values JSON, new_values JSON, changed_at, ip_address
)
```

### 4b.4 White-label Admin Features

Apabila Super Admin create White-label Admin account, mereka boleh set:

| Setting | Kawalan Super Admin | Pilihan WL Admin |
|---------|-------------------|------------------|
| Enable white-label | ✅ Toggle on/off | ❌ Tidak boleh ubah |
| Custom logo | ✅ Upload/set | ✅ Upload/change |
| Primary color | ✅ Set hex code | ✅ Change within palette |
| Custom domain | ✅ Approve/reject | ✅ Request domain |
| Invoice footer | ✅ Set template | ✅ Edit text |
| Max discount allowed | ✅ Set % limit | ✅ Offer up to limit |
| Min price floor | ✅ Set RM minimum | ❌ Tidak boleh bawah |
| Can create custom packages | ✅ Yes/No | ❌ Hanya jika enabled |
| AI credit markup | ✅ Set max % | ✅ Mark up within limit |
| Revenue share % | ✅ Set (contoh: 70/30) | ❌ Tidak boleh ubah |
| Allocate tenants | ✅ Assign/remove | ✅ Add/manage theirs |
| Maximum tenant limit | ✅ Set cap (contoh: 50) | ❌ Tidak boleh exceed |

### 4b.5 Tenant Migration Rules

Jika White-label Admin leave platform atau di-revoke:

| Scenario | Action | Result |
|----------|--------|--------|
| **WL Admin voluntarily leave** | Transfer tenants first | Tenants moved ke Super Admin jika tiada transfer |
| **WL Admin revoked (violation)** | Immediate suspension | Tenants kekal active, akses WL Admin ditarik balik |
| **WL Admin payment failed** | Grace period → suspend | Tenants masuk read-only mode |
| **Tenant request migration** | Tenant boleh request move | Super Admin approve/reject, WL Admin notified |
| **Platform acquisition** | Bulk transfer to new owner | All tenants migrated with history |

**Peraturan:**
- Tenant sentiasa dapat service — tidak pernah down kerana WL Admin issue
- Data tenant tidak boleh diambil alih oleh WL Admin
- Audit trail penuh untuk semua migration actions
- Tenant notification wajib sebelum sebarang perubahan management

### 4b.6 Admin Panel UI — White-label Setup (Super Admin View)

```
┌─────────────────────────────────────────────────────────────┐
│  WHITE-LABEL MANAGEMENT (Super Admin View)                   │
├─────────────────────────────────────────────────────────────┤
│  [+ Create White-label Admin]                                │
├─────────────────────────────────────────────────────────────┤
│  Agency Name:         [Digital Solutions Sdn Bhd___]         │
│  Contact Email:       [admin@digitalsolutions.my]            │
│  Status:              [Active ▼]                             │
│                                                              │
│  🎨 White-label Configuration                               │
│  ☑ Enable white-label                                      │
│  Custom Logo:      [Upload logo.png__________]              │
│  Primary Color:    [#FF6600____]                            │
│  Custom Domain:    [digitalsolutions.my____________]        │
│  Invoice Footer:   [Powered by Digital Solutions_____]      │
│                                                              │
│  💰 Pricing Control (within Super Admin limits)             │
│  Max discount allowed: [15 _________ %]                     │
│  Min price floor:      RM[42 _________] (Starter plan)      │
│  Can create custom packages: ☐ Yes ☐ No                     │
│  AI credit markup max: [20 _________ %]                     │
│                                                              │
│  📊 Allocated Tenants: [12 / 50 maximum]                    │
│  [+ Allocate Existing Tenant]  [- Remove Tenant]            │
│                                                              │
│  💵 Revenue Share                                           │
│  WL Admin gets:           [70 _________ %]                  │
│  Platform keeps:          [30 _________ %]                  │
│  Payout schedule:         [Monthly ▼]                       │
│                                                              │
│  [Save Configuration]  [Preview Customer View]  [Deactivate]│
└─────────────────────────────────────────────────────────────┘
```

### 4b.7 Admin Panel UI — White-label Dashboard (WL Admin View)

```
┌─────────────────────────────────────────────────────────────┐
│  DIGITAL SOLUTIONS — Partner Dashboard                       │
├─────────────────────────────────────────────────────────────┤
│  👤 My Tenants: 12   💰 Revenue This Month: RM 4,230        │
│  📊 Total Revenue (Lifetime): RM 28,500                      │
│  ⏳ Pending Payout: RM 1,850                                 │
├─────────────────────────────────────────────────────────────┤
│  TENANT MANAGEMENT                                           │
│  ┌──────────┬──────────┬──────────┬──────────┬──────────┐   │
│  │ Tenant   │ Plan     │ Status   │ Seats    │ Actions  │   │
│  ├──────────┼──────────┼──────────┼──────────┼──────────┤   │
│  │ Cafe KL  │ Business │ Active   │ 8/10     │ [⚙][📧]│   │
│  │ ShopMY   │ Starter  │ Active   │ 2/3      │ [⚙][📧]│   │
│  │ AutoXY   │ Pro      │ Trial    │ 20/25    │ [⚙][📧]│   │
│  └──────────┴──────────┴──────────┴──────────┴──────────┘   │
│                                                              │
│  [+ Add New Tenant]  [Bulk Invite]  [Export List]           │
├─────────────────────────────────────────────────────────────┤
│  PRICING & DISCOUNTS                                         │
│  Current discount offered: 10% (max allowed: 15%)           │
│  [+ Create Custom Discount]  [View Pricing History]         │
├─────────────────────────────────────────────────────────────┤
│  SUPPORT TICKETS                                             │
│  Open: 3   Resolved this week: 8   Avg response: 2.5 hours  │
│  [+ New Ticket]  [View All Tickets]                         │
├─────────────────────────────────────────────────────────────┤
│  REVENUE & PAYOUTS                                           │
│  This month: RM 4,230 (my share: RM 2,961)                  │
│  Last payout: RM 3,100 on 2026-10-01                        │
│  [View Statements]  [Download Invoice]                      │
└─────────────────────────────────────────────────────────────┘
```

### 4b.8 Acceptance Tests — Admin Hierarchy

| # | Test Case | Expected Result |
|---|-----------|-----------------|
| 1 | Master Admin create Super Admin account | Super Admin dapat akses semua tenant |
| 2 | Super Admin create White-label Admin | WL Admin dapat allocate tenants |
| 3 | WL Admin login | Hanya nampak tenant yang dialokasikan |
| 4 | WL Admin cuba access tenant lain | Access denied (403 Forbidden) |
| 5 | WL Admin offer discount > max allowed | Rejected by system |
| 6 | WL Admin set price below min floor | Rejected by system |
| 7 | Tenant migrate from WL Admin A to B | Tenant data intact, history preserved |
| 8 | WL Admin revoked (violation) | Tenants remain active, WL Admin access removed |
| 9 | WL Admin leave platform | Tenants transferred to Super Admin or new WL Admin |
| 10 | Tenant upgrade plan via WL Admin | Prorated charge calculated correctly |
| 11 | Revenue share calculation | Correct split based on configured percentage |
| 12 | Custom domain verification | Domain verified before white-label activation |

### 4b.9 Security Rules for Multi-Tier Admin

| Peraturan | Penerangan |
|-----------|-------------|
| **Scope enforcement** | Setiap request check admin scope — WL Admin tidak boleh query tenant selain milik mereka |
| **Role escalation prevention** | WL Admin tidak boleh self-promote ke Super Admin atau modify own role |
| **Cross-tenant isolation** | Data tenant A tidak boleh diakses tenant B atau WL Admin lain |
| **Audit trail** | Semua admin actions direkod dengan user_id, timestamp, IP address |
| **MFA for sensitive actions** | Price changes, tenant deletion, bulk operations require MFA |
| **Session timeout** | Inactive session auto-logout after configurable period |
| **API key scoping** | Service API keys scoped to admin level — WL Admin API keys only access their tenants |

---

## 5. Role, Permission & Entitlement

Bezakan tiga konsep:

1. **Role:** kumpulan tanggungjawab pengguna
2. **Permission:** tindakan yang dibenarkan
3. **Entitlement:** fungsi yang tersedia berdasarkan pelan atau konfigurasi tenant

Contoh:

- Role `Finance Officer` mungkin mempunyai permission melihat invois
- Permission `invoice.refund.approve` diperlukan untuk meluluskan refund
- Entitlement `B-Finance` menentukan sama ada fungsi modul tersedia kepada tenant

Akses dibenarkan hanya apabila authentication, tenant scope, permission, entitlement dan syarat rekod dipenuhi.

### 5.1 Kawalan Tambahan

- Branch scope, Department scope, Assigned-record scope
- Ownership scope, Data classification, Approval limit
- Separation of duties, Time-limited vendor access

## 6. Subscription, Billing & Usage Metering

### 6.1 Skop

- Pelan langganan, module entitlements, billing cycle
- Trial dan grace period, upgrade/downgrade
- Seat limits, usage limits
- Invoice langganan, payment status
- Suspension dan reactivation, plan change history
- **AI Token packages** — pembelian kredit AI berasingan daripada SaaS subscription
- **Promosi, kupon dan harga khas** — discount management
- **Price versioning** — setiap tenant kekal dengan harga sah pada masa transaksi

### 6.2 Peraturan

- Bezakan langganan AI BIJAN daripada bil pelanggan tenant sendiri
- Bezakan kos infrastruktur, penggunaan AI dan harga jualan kepada pelanggan
- Jangan bergantung pada status UI untuk menentukan entitlement
- Perubahan pelan perlu mempunyai tarikh kuat kuasa dan audit
- Kegagalan payment tidak boleh menyebabkan data perniagaan dipadam
- Polisi grace period dan read-only mode perlu didokumenkan
- **Harga TIDAK boleh hardcode dalam frontend atau kod** — semua harga dari database
- **Tenant sedia ada kekalkan harga lama** sehingga pembaharuan atau upgrade

---

## 6a. Subscription & Monetization Engine — Admin Panel

### 6a.1 Konsep Utama

Untuk AI BIJAN, **semua harga langganan, pakej dan had penggunaan mesti boleh dikawal oleh Super Admin melalui Admin Panel Laravel**, tanpa perlu mengubah kod atau deploy semula sistem setiap kali harga berubah.

Ini adalah modul pusat bernama **Subscription & Monetization Engine** yang menjadi sumber tunggal untuk:
- Harga SaaS langganan
- Akses modul mengikut pakej
- AI Credits / token packages
- Add-ons (seat, cawangan, storan, API)
- Promosi, kupon dan diskaun
- Billing dan invoicing

Semua perubahan melalui backend dengan pengesahan kebenaran, transaksi database dan validasi.

### 6a.2 Super Admin — Pricing Management Features

#### Cipta & Edit Pakej

| Fungsi | Kawalan |
|--------|---------|
| **Pricing Plan Editor** | Cipta, edit, gandakan, aktifkan atau arkibkan pakej |
| **Nama pakej** | Bebas — contoh: "BIJAN Launch", "Professional Plus" |
| **Harga langganan (RM)** | Bulanan, tahunan, atau custom cycle |
| **Kitaran bil** | Bulanan, tahunan (dengan auto-renewal), kustom |
| **Tempoh percubaan** | 0 hari (tiada trial), 7 hari, 14 hari, 30 hari |
| **Modul yang termasuk** | Checklist modul untuk setiap pakej |
| **Had seat** | Bilangan user/seat termasuk dalam pakej |
| **Had cawangan** | Bilangan branch/organisasi unit |
| **Storan** | GB storage allocation |
| **API calls** | Rate limit per minit/jam |
| **AI Credits** | Kuota kredit AI setiap kitaran bil |
| **Add-on pricing** | Harga tambahan untuk extra seat, cawangan, storage |

#### Contoh Pakej SaaS AI Bijan

Berikut ialah simulasi konfigurasi yang boleh diubah oleh Super Admin:

| Pakej | Harga/Bulan | Seat | Cawangan | Storage | AI Credits | Modul Utama |
|-------|------------|------|----------|---------|------------|-------------|
| **Starter** | RM49 | 3 | 1 | 5 GB | 10 juta | Virtual Office, B-Site, B-Link, B-Omnichannel, B-Helpdesk (5 tiket) |
| **Business** | RM149 | 10 | 1 | 25 GB | 50 juta | Starter + POS, Inventory, Invoicing, Finance (limited), HRMS |
| **Professional** | RM349 | 25 | 5 | 100 GB | 200 juta | Business + Full POS, BOM, Procurement, Logistics, Marketing, Analytics |
| **Enterprise** | RM799 | Unlimited | Unlimited | 500 GB | *Unlimited* | Semua modul + Custom blueprint, API unlimited, Priority support |

**Annual pricing** (bayar tahunan = **2 bulan percuma**, iaitu bayar 10 bulan untuk 12 bulan; jimat ~16.7%):

| Pakej | Harga Tahunan | Jimat |
|-------|--------------|-------|
| Starter | RM490/tahun (RM40.83/bulan) | RM98 |
| Business | RM1,490/tahun (RM124.17/bulan) | RM298 |
| Professional | RM3,490/tahun (RM290.83/bulan) | RM698 |
| Enterprise | RM7,990/tahun (RM665.83/bulan) | RM1,598 |

#### AI Credits & Top-up Packages

Super Admin boleh ubah harga dan kuota AI credits secara berasingan:

| Pakej Token | Token (unit dalaman) | Harga | Sah |
|------------|------------|-------|-----|
| Starter | 80 juta token (80,000 unit) | RM80 | 30 hari |
| Basic | 140 juta token (140,000 unit) | RM110 | 30 hari |
| Plus | 200 juta token (200,000 unit) | RM160 | 30 hari |
| Pro | 1.1 bilion token (1,100,000 unit) | RM600 | 30 hari |
| Max | 2.25 bilion token (2,250,000 unit) | RM1,100 | 30 hari |

**Peraturan:**
- AI Credits berasingan daripada SaaS subscription
- Tenant boleh beli AI tokens tambahan bila-bila masa
- SaaS subscription yang aktif mungkin sudah include AI Credits tertentu
- Unused AI Credits expired selepas tempoh sah — tidak carry over

### 6a.3 Struktur Database Laravel

Semua pricing data disimpan dalam database, bukan hardcode:

```sql
-- Pakej utama
subscription_plans (
    id, name, slug, description, status, created_at, updated_at
)

-- Versi harga (price versioning)
plan_versions (
    id, plan_id, price_monthly, price_annual, effective_from, 
    effective_to, created_by, notes
)

-- Modul yang termasuk dalam setiap pakej
plan_modules (
    id, plan_version_id, module_slug, included, limit_value
)

-- Had penggunaan (seat, cawangan, storage, API)
plan_limits (
    id, plan_version_id, limit_type, limit_value, overage_price
    -- limit_type: 'seats', 'branches', 'storage_gb', 'api_calls_per_min'
)

-- Langganan tenant aktif
tenant_subscriptions (
    id, tenant_id, plan_version_id, status, start_date, end_date,
    next_billing_date, current_period_start, current_period_end,
    cancel_at_period_end, cancelled_at, cancellation_reason
)

-- Add-on items (extra seat, extra branch, dll)
subscription_items (
    id, tenant_subscription_id, item_type, item_name, quantity, unit_price
)

-- Pakej pembelian kredit AI
ai_credit_packages (
    id, name, token_units, price, validity_days, status
)

-- Ledger rekod kredit AI masuk dan keluar
ai_credit_ledger (
    id, tenant_id, transaction_type, reference_id, token_change,
    balance_before, balance_after, created_at
    -- transaction_type: 'purchase', 'usage', 'adjustment', 'refund', 'expiry'
)

-- Coupons & promotions
coupons (
    id, code, type, value, max_uses, used_count, applicable_plan_ids,
    valid_from, valid_until, status
    -- type: 'percentage', 'fixed_amount', 'free_trial_extension'
)

-- Invoices & payments
invoices (
    id, tenant_id, subscription_item_id, amount, currency, status,
    due_date, paid_at, payment_reference
)
payments (
    id, invoice_id, amount, method, provider_ref, status, recorded_at
)

-- Audit trail untuk semua perubahan pricing
pricing_audit_logs (
    id, admin_user_id, action, entity_type, entity_id,
    old_values JSON, new_values JSON, changed_at, ip_address
)
```

### 6a.4 Peraturan Sistem Pricing

| Fungsi | Peraturan |
|--------|-----------|
| **Ubah harga** | Harga baharu digunakan untuk langganan baharu atau pembaharuan. Tenant sedia ada kekal dengan harga lama sehingga renewal/upgrade. |
| **Ubah modul** | Akses modul dikawal oleh entitlement setiap tenant. Perubahan hanya affect tenant baru atau yang upgrade ke pakej baharu. |
| **Ubah AI Credits** | Kuota dan baki kredit direkodkan dalam `ai_credit_ledger`. Setiap adjustment perlu reason dan permission. |
| **Had penggunaan** | Tetapkan had seat, cawangan, API, storan dan penggunaan AI. Overage boleh kena caj tambahan (overage_price). |
| **Diskaun** | Sokong kupon, promosi dan harga khas pelanggan. Kupon boleh dikekang: applicable_plan_ids, max_uses, valid_until. |
| **Pelanggan sedia ada** | Kekalkan harga lama melalui price versioning. `tenant_subscriptions.plan_version_id` point ke snapshot harga pada masa subscribe. |
| **Audit** | Rekod siapa mengubah harga, bila, nilai lama dan nilai baharu dalam `pricing_audit_logs`. |
| **Pembayaran** | Pengiraan invois menggunakan harga dari `plan_versions` yang sah pada masa transaksi. |
| **Suspend tenant** | Jika payment gagal, tenant masuk grace period → read-only mode → suspend. Data tidak dipadam. |

### 6a.5 Admin Panel UI — Pricing Plan Editor

Simulasi antaramuka Super Admin:

```
┌─────────────────────────────────────────────────────────────┐
│  SUBSCRIPTION & PRICING MANAGEMENT                           │
├─────────────────────────────────────────────────────────────┤
│  [+ Create New Plan]   [Duplicate Selected]   [Archive]     │
├─────────────────────────────────────────────────────────────┤
│  Nama pakej:          [BIJAN Launch_____________]           │
│  Status:              [Active ▼]                            │
│                                                             │
│  Harga langganan:                                     │
│  ○ Bulanan   ○ Tahunan   ○ Custom                       │
│  Harga (RM):            [288__________]                   │
│  Kitiran bil:           [Bulanan ▼]                      │
│  Tempoh percubaan:      [14 _________] hari               │
│                                                             │
│  AI Credits setiap kitaran:                                │
│  [10,000,000 ___________] units                            │
│                                                             │
│  Had penggunaan:                                            │
│  Seat termasuk:            [3 _________]                    │
│  Cawangan termasuk:        [1 _________]                    │
│  Storage (GB):             [25 _________]                   │
│  API calls/min:            [100 _________]                  │
│                                                             │
│  Add-on pricing:                                            │
│  Extra seat:       RM[15______/seat/bulan]                 │
│  Extra branch:     RM[99______/branch/bulan]               │
│  Extra storage:    RM[10______/GB/bulan]                   │
│                                                             │
│  Modul yang termasuk:                                      │
│  ☑ Virtual Office    ☑ AI Assistant                        │
│  ☑ B-Site            ☑ B-Link                              │
│  ☑ B-Omnichannel     ☑ B-Helpdesk                          │
│  ☐ POS               ☐ Inventory                           │
│  ☐ Invoicing         ☐ Finance                             │
│  ☐ HRMS              ☐ Projects                            │
│  ☐ Marketing         ☐ Analytics                           │
│                                                             │
│  [Save Plan]  [Preview Customer View]  [Cancel]            │
└─────────────────────────────────────────────────────────────┘
```

### 6a.6 Customer-Facing Plan Preview

Apabila tenant melihat pakej, mereka hanya nampak ini:

```
┌─────────────────────────────────────────────────────────────┐
│  PILIH PELAN ANDA                                            │
├──────────────────┬──────────────────┬───────────────────────┤
│  STARTER         │  BUSINESS        │  PROFESSIONAL         │
│  ───────────     │  ───────────     │  ────────────         │
│  RM49/bulan      │  RM149/bulan     │  RM349/bulan          │
│                  │                  │                       │
│  ✓ Virtual Office│  ✓ Semua Starter │  ✓ Semua Business     │
│  ✓ B-Site        │  ✓ POS           │  ✓ Full Inventory     │
│  ✓ B-Link        │  ✓ Inventory     │  ✓ Manufacturing      │
│  ✓ Omnichannel   │  ✓ Invoicing     │  ✓ Procurement        │
│  ✓ Helpdesk (5)  │  ✓ Finance       │  ✓ Logistics          │
│                  │  ✓ HRMS (10)     │  ✓ Marketing          │
│                  │  ✓ Projects      │  ✓ Analytics          │
│                  │                  │  ✓ Priority Support   │
│                  │                  │                       │
│  3 seats         │  10 seats        │  25 seats             │
│  1 branch        │  1 branch        │  5 branches           │
│  10M AI Credits  │  50M AI Credits  │  200M AI Credits      │
│                  │                  │                       │
│  [Mulakan]       │  [Mulakan]       │  [Hubungi Sales]      │
└──────────────────┴──────────────────┴───────────────────────┘
```

### 6a.7 API Endpoints untuk Pricing

Backend menyediakan API untuk mendapatkan harga dan entitlement:

```
GET    /api/v1/plans                    # Senarai pakej aktif
GET    /api/v1/plans/{slug}             # Detail pakej
GET    /api/v1/tenant/{id}/entitlements # Apa tenant boleh akses
POST   /api/v1/subscribe                # Daftar langganan baru
POST   /api/v1/upgrade                  # Upgrade/downgrade plan
GET    /api/v1/tenant/{id}/subscription # Status langganan semasa
POST   /api/v1/buy-ai-credits           # Beli token AI
GET    /api/v1/ai-credit-balance        # Baki kredit tenant
POST   /api/v1/coupon/validate          # Validate kupon
GET    /api/v1/invoices/{id}            # Invoice details
```

**Peraturan API:**
- Semua endpoint require authentication
- Tenant hanya boleh baca data sendiri
- Super Admin boleh baca semua
- Harga dari `plan_versions`, bukan dari request payload
- Transaction scope checked sebelum setiap subscription change

### 6a.8 Acceptance Tests — Subscription Engine

| # | Test Case | Expected Result |
|---|-----------|-----------------|
| 1 | Super Admin ubah harga plan | Tenant yang sudah subscribe kekal harga lama |
| 2 | Tenant baru subscribe selepas harga naik | Dapat harga baharu |
| 3 | Tenant upgrade plan | Prorated charge, entitlement update segera |
| 4 | Tenant downgrade | Module access removed at next billing cycle |
| 5 | Payment gagal | Grace period → read-only → suspend (configurable) |
| 6 | Coupon code applied | Discount calculated correctly, max_uses decremented |
| 7 | AI Credits purchased | Ledger entry created, balance updated immediately |
| 8 | AI Credits expired | Expired entries logged, balance adjusted |
| 9 | Tenant delete account | Subscription cancelled, data retained for retention period |
| 10 | Price version rollback | Old versions preserved, new subscriptions use latest |
| 11 | Annual billing proration | Monthly equivalent calculated correctly |
| 12 | Multi-branch tenant | Each branch counted against limit, overage charged |

### 6a.9 Pricing Audit Trail

Setiap perubahan pricing direkod:

```json
{
  "admin_user_id": 1,
  "action": "update_price",
  "entity_type": "plan_version",
  "entity_id": 42,
  "old_values": {"price_monthly": 149, "price_annual": 1428},
  "new_values": {"price_monthly": 179, "price_annual": 1715},
  "changed_at": "2026-10-18T10:30:00Z",
  "ip_address": "203.0.113.42",
  "reason": "Annual cost increase due to infrastructure"
}
```

**Peraturan audit:**
- Tidak boleh delete atau edit pricing audit logs
- Access terhad kepada Super Admin dan platform finance team
- Log export tersedia untuk compliance review
- Alert jika price change > threshold (contoh: >20%)

---

## 7. Notifications

Saluran yang mungkin digunakan: in-app, e-mel, SMS, WhatsApp melalui penyedia yang disokong, push notification, webhook untuk sistem luaran.

Rekod notifikasi perlu mengandungi: recipient, tenant, template, channel, reference ID, status, retry count, provider message ID, timestamps (created/sent/delivered/failed).

Kegagalan notifikasi tidak boleh membatalkan transaksi perniagaan yang sudah berjaya.

## 8. Files & Document Storage

- Tenant-scoped file ownership, private-by-default storage
- Signed URL dengan expiry jika sesuai
- File size dan MIME validation, malware scanning jika tersedia
- Access logs, retention rules, versioning untuk dokumen penting
- Download permission, export dan deletion workflow

Jangan jadikan URL fail sebagai satu-satunya kawalan keselamatan.

## 9. Audit Trail

Audit event sekurang-kurangnya merekod: `tenant_id`, `actor_type`, `actor_id`, `action`, `resource_type`, `resource_id`, `timestamp`, `outcome`, `request_id`, `source`, metadata minimum.

Jangan menyimpan password, API key, token rahsia atau kandungan sensitif yang tidak diperlukan di dalam audit log.

Rekod audit penting perlu dilindungi daripada perubahan tanpa kebenaran.

## 10. API, Webhooks & Integrations

### 10.1 Standard API

API versioning, authentication & authorization, request validation, consistent error format, pagination, rate limiting, idempotency, request/correlation ID, API documentation, deprecation policy.

### 10.2 Webhook Standard

Setiap webhook perlu mempunyai: event type, event ID, timestamp, signature verification, retry policy dan replay protection, idempotent consumer, delivery status dan failure alert, reconciliation mechanism.

Jangan menganggap webhook berjaya hanya kerana permintaan dihantar.

## 11. Localization & Malaysia Configuration

Sokong konfigurasi: Bahasa Melayu dan English, timezone mengikut organisasi atau cawangan, mata wang MYR, format tarikh dan nombor, tax configuration yang boleh dikemas kini, alamat Malaysia, template dokumen tempatan, import/export data, tetapan retention dan privacy.

Semua kadar cukai, e-Invoice, lesen, permit dan keperluan undang-undang mesti disahkan berdasarkan sumber rasmi semasa. Jangan hardcode andaian peraturan ke seluruh sistem tanpa versioning.

---

# PART III — BUSINESS MODULE CATALOG

## 12. Modul Perniagaan Umum

Setiap modul perlu mempunyai: tujuan, persona, permission, entiti, workflow, API, event, report, integrasi, acceptance tests dan status pembangunan.

### 12.1 B-Virtual Office

- Workspace organisasi, announcement, shared calendar, internal directory
- Meeting notes, task reminders, knowledge links

#### 12.1.1 AI Agents di Virtual Office

Setiap modul dalam Virtual Office mempunyai ejen AI khusus yang membantu pekerja dengan autonomi terkawal:

| # | Agent Name | Modul | Fungsi Utama | Tool Permissions | Approval Gate |
|---|------------|-------|--------------|------------------|---------------|
| 1 | HR Assistant | B-HRMS | Draf surat pekerja, ringkasan kehadiran, cadangan jadual syif | `view:employees`, `view:attendance`, `view:leave`, `manage:templates` | Tidak boleh pecat/keluarkan pekerja tanpa kelulusan owner/admin |
| 2 | Document Assistant | B-Sign & Docs | Draf kontrak/surat/polisi, semakan versi | `manage:templates`, `create:documents`, `view:approvals` | Tidak boleh tandatangan atau terbitkan dokumen rasmi tanpa kelulusan |
| 3 | Project Coordinator | B-Projects | Pecahan tugasan, cadangan milestone, amaran risiko kelewatan | `view:projects`, `view:tasks`, `create:tasks`, `view:timesheets` | Tidak boleh ubah tarikh akhir kritikal tanpa kelulusan project owner |
| 4 | POS Operator | B-POS System | Cadangan diskaun, proses checkout, urus cash shift | `manage:pos-sales`, `manage:cash-shifts`, `view:customers`, `manage:discounts` | Tidak boleh refund/cancel melebihi had tanpa kelulusan manager |
| 5 | Inventory Planner | B-Inventory | Ramalan permintaan, cadangan pesanan belian, amaran stok rendah/luput | `view:stock`, `view:suppliers`, `create:purchase-orders`, `view:batches` | Tidak boleh beli melebihi bajet tanpa kelulusan finance/approver |
| 6 | COGS Analyst | COGS Engine | Kira kos tepat (FIFO/WAC), amaran margin rendah | `view:cogs`, `view:stock-layers`, `view:sales-margins`, `calculate:margins` | Tidak boleh ubah rekod kos sejarah atau audit log |
| 7 | Finance Advisor | B-Finance | Draf jurnal, ramalan aliran tunai, pengesanan anomali | `view:ledger`, `view:reports`, `create:journals`, `view:receivables-payables` | Tidak boleh post jurnal kewangan tanpa kelulusan finance_manager/owner |
| 8 | Sales & Marketing Scout | B-Site/B-Marketplace/B-Affiliate | Cadangan strategi pemasaran, pengoptimuman iklan | `view:analytics`, `view:campaigns`, `manage:ads`, `view:affiliates` | Tidak boleh spend budget iklan tanpa kelulusan owner/marketing manager |
| 9 | Customer Support Assistant | B-Loyalty/B-Omnichannel | Jawapan tiket sokongan, resolusi常见问题, cadangan reward | `view:conversations`, `view:members`, `view:points`, `send:responses` | Tidak boleh beri refund/pontos besar tanpa kelulusan |
| 10 | Asset Maintenance Reminder | B-Assets | Peringatan penyelenggaraan, cadangan jadwal servis | `view:assets`, `view:maintenance-schedules`, `create:maintenance-tasks` | Tidak boleh lupus atau transfer aset tanpa kelulusan asset manager |

**Multi-Agent Orchestration Example:**
Apabila seorang pelanggan membuat tempahan besar melalui B-POS:
1. **POS Operator** memproses checkout → 2. **Inventory Planner** mengesak stok tersedia → 3. **Finance Advisor** draf invois → 4. **Document Assistant** sediakan resit/dokumen → Semua tindakan direkod dalam audit trail Virtual Office.

### 12.2 B-HRMS

- Employee profiles, department dan reporting line
- Attendance, leave, shift, payroll integration jika tersedia dan disahkan
- Employee documents, onboarding dan offboarding, HR reports

### 12.3 B-Sign & Docs

- Document templates, versioning, approval workflow
- E-signature integration, document expiry reminders
- Access logs, contract repository

Kesahan e-signature perlu dinilai berdasarkan jenis dokumen, penyedia dan keperluan undang-undang yang berkenaan.

### 12.4 B-Projects

- Project, milestones, tasks, dependencies
- Assignments, time entries, budget, deliverables, risk register
- Project reporting

### 12.5 B-Assets

- Asset register, category, location, custodian
- Maintenance dan depreciation integration jika tersedia
- Check-in/check-out, asset audit dan disposal workflow

### 12.6 B-POS

- Product/service catalogue, cart, discounts
- Orders, payments, receipts, returns
- Refund requests, shift closing, cash reconciliation

### 12.7 B-Site

- Landing page, business profile, content blocks
- Contact forms, SEO metadata
- Domain configuration, analytics integration, publishing approval

### 12.8 B-Link

- Business profile links, QR codes
- Contact links, campaign tracking
- Link analytics, expiry, access control untuk pautan peribadi

### 12.9 B-Marketplace

- Seller profiles, product/service listings
- Search, order requests, seller verification
- Reviews, dispute workflow, commission rules, settlement reporting

### 12.10 B-Affiliate

- Partner registration, referral tracking
- Attribution windows, commission rules
- Approval, payout reconciliation, fraud detection
- Partner reporting

### 12.11 B-Loyalty

- Customer points, reward rules, redemption, expiry
- Reversal, tier management, fraud controls
- Loyalty ledger

### 12.12 B-Omnichannel

- Unified customer identity
- Conversation inbox, channel connection
- Conversation assignment, consent and communication preferences
- Delivery status, agent handoff, conversation history

### 12.13 B-Booking

- Service catalogue, availability, staff assignment
- Reservation, reschedule/cancellation, deposit, reminder
- No-show rules, booking calendar

Untuk tempahan sumber terhad, gunakan mekanisme concurrency-safe supaya dua pelanggan tidak boleh mengesahkan slot atau aset yang sama.

### 12.14 B-Marketing

- Campaigns, content calendar, audience segments
- Consent-aware messaging, campaign performance
- Landing page tracking, drafting assistance
- Approval before publishing
- Ad platform integration apabila akses dan kebenaran tersedia

### 12.15 B-Helpdesk / B-Desk

- Tickets, categories, priority, assignment, SLA
- Internal notes, customer replies, escalation
- Knowledge base, resolution reporting

### 12.16 B-COGS Engine

- Cost components: labour, material, packaging, delivery cost, overhead allocation
- Versioned cost calculations
- Estimated versus actual margin

### 12.17 B-Inventory & Warehouse

- Product variants, warehouses, locations
- Stock movements, reservations, transfers, stock count
- Lot/batch tracking jika diperlukan, serial tracking jika diperlukan
- Reorder alerts, inventory valuation integration

Jangan mengubah stok hanya dengan menggantikan angka kuantiti. Gunakan ledger pergerakan stok dan proses reconciliation.

### 12.18 B-BOM & Manufacturing

- Bill of materials, work orders, production stages
- Material reservation, work in progress
- Quality checks, scrap/waste, finished goods
- Actual versus standard cost

### 12.19 B-Finance

- Chart of accounts, journal entries, general ledger
- Accounts receivable/payable, reconciliation
- Financial reports, period close, audit trail

Sistem tidak boleh diiklankan sebagai pengganti perisian perakaunan yang mematuhi semua keperluan tanpa semakan profesional dan pengesahan yang berkaitan.

### 12.20 B-Invoicing

- Quotations, invoices, credit notes, debit notes jika diperlukan
- Payment allocation, due dates, reminders
- Document numbering, versioning, customer statements

### 12.21 B-Procurement

- Purchase requests, approval, vendor quotation
- Vendor comparison, purchase orders
- Goods received, invoice matching
- Supplier performance

### 12.22 B-Logistics

- Delivery jobs, dispatch, driver assignment
- Route planning integration, proof of delivery
- Failed delivery, return to sender
- Delivery cost, tracking status

### 12.23 B-Connect / API Gateway

- Connector registry, API credentials, secret rotation
- Webhook subscriptions, sync jobs
- Mapping rules, retry and dead-letter handling
- Connection health, integration logs

### 12.24 B-Analytics

- Operational dashboards, sales/finance/inventory/workforce/customer/event reports
- Export permissions, metric definitions
- Data freshness indicators

Setiap KPI mesti mempunyai definisi, sumber data, formula, timezone dan tarikh data terakhir dikemas kini.

### 12.25 B-Brain Hub

Spesifikasi terperinci terdapat dalam Part VI (Section 29–36 dan 33a).

---

# PART IV — INDUSTRY BLUEPRINTS

## 13. Prinsip Direktori Industri Malaysia

Direktori industri ialah peta produk untuk perancangan. Ia bukan senarai rasmi kod MSIC dan bukan bukti bahawa setiap subjenis telah mempunyai modul siap.

### 13.1 MSIC Reference

MSIC ialah klasifikasi aktiviti ekonomi. Ia bukan katalog ciri perisian.

Setiap mapping industri perlu menyimpan: `msic_code`, `msic_label`, `msic_version`, `source_reference`, `verified_at`, `verified_by`, `mapping_status`.

Jangan mereka kod MSIC. Semak kod sebenar melalui sumber rasmi DOSM yang berkenaan. Sokong primary dan secondary business activities.

### Sumber Rasmi MSIC

| Sumber | URL |
|--------|-----|
| DOSM — Kod MSIC 2025 | https://economiccensus.dosm.gov.my/portal-main/msic-2025 |
| OpenDOSM — MSIC data catalogue | https://open.dosm.gov.my/data-catalogue/msic |
| DOSM — Economic Census, All Sectors | https://www.dosm.gov.my/portal-main/release-content/economic-census--all-sectors |
| DOSM — MSME Performance | https://www.dosm.gov.my/portal-main/release-content/micro-small--medium-enterprises-msmes-performance-2025 |
| SME Corp Malaysia — Definisi PKS | https://smecorp.gov.my/index.php/my/component/content/article/87-definisi-pks-sme-info/371-sme-definition |

### 13.2 Direktori Industri

Industri yang perlu dipertimbangkan termasuk:

1. Transport, mobility dan fleet
2. School transport
3. Childcare dan education
4. Hospitality, hotel dan accommodation
5. Food & beverage
6. Retail dan wholesale
7. E-commerce
8. Salon, barber, spa dan personal care
9. Clinic dan care services
10. Construction
11. Property dan tenancy
12. Automotive
13. Agriculture
14. Event management dan equipment rental
15. Quality dan compliance
16. Subscription businesses
17. Professional services
18. Travel dan tour
19. Waste dan recycling
20. Security services
21. Community dan association
22. Pet services
23. Repair dan field services
24. Manufacturing
25. Logistics dan warehousing
26. Creative agency dan production
27. Training dan consultancy
28. Finance-related services (hanya dalam skop aktiviti yang dibenarkan)

### 13.3 Module Recommendation Map

| Industri | Modul Teras Potensi | Modul Khusus |
|----------|---------------------|--------------|
| Retail | POS, Inventory, Invoicing, Finance | Retail workflows |
| Wholesale | Inventory, Procurement, Logistics, Finance | Distribution |
| Salon/barber | Booking, POS, CRM, Loyalty | Service staff scheduling |
| F&B | POS, Inventory, COGS, HRMS | Recipe costing, kitchen workflow |
| School transport | Booking, Invoicing, CRM | B-School Transport |
| Fleet | Assets, Logistics, HRMS | B-Transport & Fleet |
| Event management | Projects, Booking, Procurement, Finance | B-Event & Rental |
| Construction | Projects, Procurement, Assets | B-Construction |
| Property | CRM, Documents, Finance | B-Property |
| Automotive | Booking, Inventory, POS | B-Automotive |
| Education | Booking, Invoicing, CRM | B-Childcare & Education |
| Hospitality | Booking, CRM, Finance | B-Hospitality |
| Clinic/care | Booking, Documents, Finance | B-Clinic & Care |
| Agriculture | Inventory, Assets, Logistics | B-Agriculture |
| Professional services | Projects, Invoicing, Documents | B-Professional Practice |
| Travel/tour | Booking, CRM, Finance | B-Travel & Tour |
| Repair/field service | Booking, Assets, Inventory | B-Repair & Field Service |
| Manufacturing | Inventory, Procurement, COGS | B-BOM & Manufacturing |

Mapping ini ialah cadangan awal. Modul sebenar perlu disahkan melalui kajian workflow pelanggan.

---

## 14. Industry Blueprint Engine

### 14.1 Tujuan

Blueprint Engine membolehkan pelanggan memilih jenis perniagaan dan menerima konfigurasi modul, roles, workflow, field, dashboard dan integrasi yang relevan.

### 14.2 Struktur Blueprint

Setiap blueprint perlu mempunyai:

- `blueprint_id`, `blueprint_version`, `industry_name`, `industry_aliases`
- `msic_mapping`, `required_modules`, `recommended_modules`, `optional_modules`
- `roles_template`, `permission_template`, `entities`, `custom_fields`
- `workflow_templates`, `notification_templates`, `sla_rules`
- `dashboard_presets`, `kpi_definitions`, `integration_requirements`
- `agent_profiles`, `acceptance_tests`, `known_limitations`
- `review_status`, `reviewed_at`

### 14.3 Onboarding Flow

1. Daftar akaun → Cipta organisasi
2. Pilih aktiviti perniagaan utama
3. Tambah aktiviti sekunder jika berkenaan
4. Pilih cawangan dan struktur organisasi
5. Jawab soalan operasi
6. Terima cadangan blueprint
7. Semak modul wajib, pilihan dan kos
8. Pilih modul untuk diaktifkan
9. Import data awal
10. Tetapkan pengguna dan permission
11. Uji workflow contoh
12. Sahkan konfigurasi dan mula menggunakan sistem

### 14.4 Versioning

- Blueprint baharu tidak boleh menukar tenant secara senyap
- Perubahan perlu menunjukkan perbezaan konfigurasi
- Migrasi perlu mempunyai dry run apabila sesuai
- Pengguna perlu tahu jika perubahan mengubah workflow, permission atau data
- Sediakan rollback atau recovery plan untuk perubahan yang berisiko

---

# PART V — B-EVENT & RENTAL (Spesifikasi Lengkap)

## 15. Kedudukan Produk

**B-Event & Rental** ialah modul khusus untuk mengurus syarikat event management, event production, venue, vendor, kru, equipment rental, bajet, pelanggan, pembayaran dan operasi hari acara.

Ia bukan sekadar sistem tempahan.

### 15.1 Subindustri

- Wedding planning, corporate events, conference dan seminar
- Exhibition dan trade show, concert dan live production
- Festival dan public events, private parties
- Birthday events, graduation events, community events
- Government/corporate functions
- Venue management
- Audio, lighting dan stage rental
- Furniture dan decor rental
- Photography/videography, catering coordination
- Event staffing

Setiap subindustri boleh menggunakan blueprint berlainan di atas domain event yang sama.

### 15.2 Role B-Event

Platform Admin, Company Owner, Organization Admin, Event Director, Event Manager, Sales/Client Manager, Finance Officer, Procurement Officer, Production Manager, Venue Manager, Crew Coordinator, Crew Member, Warehouse/Rental Officer, Vendor, Client, Guest/Attendee, Auditor atau reviewer yang diberi akses terhad.

Vendor dan pelanggan hanya boleh melihat data yang dibenarkan. Jangan memberikan akses seluruh organisasi kepada akaun vendor.

## 16. Event Lifecycle

```
Lead
  ↓
Qualification
  ↓
Client Brief
  ↓
Site/Venue Assessment
  ↓
Proposal & Budget
  ↓
Approval / Contract
  ↓
Deposit / Booking Confirmation
  ↓
Project Planning
  ↓
Vendor Procurement
  ↓
Crew & Equipment Allocation
  ↓
Guest / RSVP / Ticket Management
  ↓
Pre-event Readiness Review
  ↓
Event-day Operations
  ↓
Breakdown / Returns
  ↓
Cost Reconciliation
  ↓
Final Invoice / Settlement
  ↓
Post-event Report
```

Status sebenar boleh dikonfigurasi mengikut blueprint, tetapi transisi mesti mempunyai peraturan dan permission.

## 17. Event Data Model

### 17.1 Lead dan Client

- `event_lead`, `event_client`, `event_contact`, `event_brief`, `event_site_visit`, `event_venue_requirement`

### 17.2 Event Project

- `event_project`, `event_project_version`, `event_package`, `event_proposal`, `event_proposal_line`
- `event_contract`, `event_change_request`, `event_milestone`

### 17.3 Venue

- `event_venue`, `venue_resource`, `venue_availability`, `venue_hold`, `venue_booking`, `venue_booking_resource`

### 17.4 Budget dan Kewangan

- `event_budget`, `event_budget_line`, `event_cost_estimate`, `event_cost_commitment`, `event_cost_entry`
- `event_payment_milestone`, `event_invoice_reference`, `event_refund_request`, `event_settlement`

### 17.5 Timeline dan Operasi

- `event_timeline_item`, `event_run_sheet`, `event_task`, `event_task_dependency`
- `event_risk`, `event_incident`, `event_change_log`, `event_readiness_check`

### 17.6 Vendor dan Procurement

- `vendor_profile`, `vendor_service`, `vendor_availability`, `vendor_quote`, `vendor_comparison`
- `vendor_contract`, `purchase_request`, `purchase_order`, `goods_received`, `vendor_performance_record`

### 17.7 Crew

- `crew_member`, `crew_skill`, `crew_assignment`, `crew_attendance`, `crew_timesheet`, `crew_expense`

### 17.8 Rental dan Warehouse

- `rental_asset`, `rental_asset_component`, `rental_reservation`, `rental_dispatch`, `rental_return`
- `rental_return_inspection`, `rental_maintenance`, `rental_damage_report`, `rental_deposit`, `rental_stock_movement`

### 17.9 Guests dan Ticketing

- `guest`, `guest_group`, `guest_invitation`, `rsvp`, `ticket`, `ticket_scan`, `checkin_event`
- `guest_meal_preference`, `guest_access_requirement`

Data tetamu perlu dihadkan kepada keperluan operasi dan dikendalikan mengikut privacy policy yang berkaitan.

### 17.10 Dokumen dan Laporan

- `event_document`, `event_document_version`, `event_approval`, `event_media_reference`
- `post_event_report`, `event_feedback`, `event_kpi_snapshot`

Nama dan struktur akhir mesti diselaraskan dengan standard database platform.

## 18. Venue dan Resource Booking

### 18.1 Keperluan

- Calendar availability, resource capacity, setup/teardown buffer
- Temporary hold, hold expiry, confirmed reservation
- Reschedule, cancellation, conflict detection
- Multi-resource booking, maintenance blackout

### 18.2 Double Booking Prevention

Pemeriksaan availability sahaja tidak mencukupi jika dua permintaan berlaku serentak.

Gunakan transaksi database, locking atau mekanisme concurrency-safe yang sesuai dengan database dan model booking.

Syarat:

- Dua booking yang bertindih tidak boleh kedua-duanya disahkan jika resource eksklusif
- Expired hold tidak boleh menyekat slot selama-lamanya
- Reschedule mesti mengesahkan availability baharu
- Perubahan booking mesti merekod siapa, bila dan sebab
- Kegagalan transaksi tidak boleh meninggalkan resource dalam keadaan separuh ditempah

## 19. Proposal, Package dan Contract

- Package template, custom line items
- Venue, catering, production dan crew components
- Taxes/fees berdasarkan konfigurasi yang disahkan
- Discounts dengan permission
- Versioned proposals, approval before sending
- Client acceptance, contract attachment
- Change request, deposit schedule, cancellation terms
- E-signature integration jika disokong

Proposal yang telah diluluskan tidak boleh diubah tanpa versioning atau change workflow.

## 20. Event Budget dan Margin

Bezakan:

- **Estimated cost:** anggaran
- **Committed cost:** kos yang telah dipersetujui atau dikomit
- **Actual cost:** kos sebenar yang direkodkan
- **Client revenue:** pendapatan yang berkaitan
- **Gross margin:** berdasarkan definisi kewangan yang ditetapkan
- **Variance:** perbezaan antara anggaran, komitmen dan kos sebenar

Jangan menganggap quotation vendor sebagai actual cost. Jangan menganggap invoice dibayar hanya kerana ia dikeluarkan.

Setiap perubahan budget penting perlu merekod versi, sebab, pelulus dan masa.

## 21. Vendor Management

- Vendor onboarding, service categories, service area, availability
- Portfolio, quotation request, quote comparison, contract
- Purchase order, performance review, incident/complaint
- Payment status, document expiry

AI boleh mencadangkan vendor berdasarkan kriteria, tetapi keputusan akhir perlu mengambil kira ketersediaan, harga, kelayakan dan keperluan organisasi.

## 22. Crew Management

- Skill registry, availability, assignment, shift, call time
- Attendance, timesheet, overtime approval
- Meal/break information, emergency contact access jika diperlukan
- Crew briefing, incident reporting

Crew hanya boleh melihat tugasan dan maklumat yang diperlukan untuk peranan mereka.

## 23. Rental Inventory Lifecycle

```
Available
  ↓
Reserved
  ↓
Picked / Prepared
  ↓
Dispatched
  ↓
At Event
  ↓
Returned
  ↓
Inspection
  ├── Good → Available
  ├── Cleaning → Cleaning
  ├── Maintenance → Repair / Maintenance
  └── Damaged → Quarantine / Claim
```

### 23.1 Kawalan

- Track asset ID atau serial jika berkenaan
- Kit dan komponen, quantity availability, event allocation
- Dispatch checklist, return inspection, damage evidence
- Missing item workflow, maintenance schedule
- Deposit and claim workflow, stock ledger

Aset yang rosak, hilang atau sedang diselenggara tidak boleh ditawarkan sebagai available.

## 24. Run Sheet dan Event-Day Operations

Run sheet perlu menyokong:

- Masa mula/tamat, lokasi, task owner, vendor/crew
- Dependencies, equipment, instructions
- Escalation contact, status, revision history

Event-day dashboard boleh menunjukkan:

- Tasks overdue, vendor belum tiba, crew belum check-in
- Equipment issue, schedule deviation, unresolved incident
- Safety concern, critical contact

AI boleh membantu merumuskan perubahan jadual, tetapi tidak boleh membuat perubahan berisiko secara senyap.

## 25. Guest, RSVP dan QR Check-in

- Guest list import, duplicate detection, invitation status, RSVP
- Guest groups, ticket generation, QR scanning
- Manual lookup fallback, check-in timestamp
- Access category, attendance reports

QR scan perlu idempotent. Imbasan berulang tidak boleh menghasilkan check-in baharu yang menggandakan kehadiran. Jika repeat scan dibenarkan untuk kes tertentu, polisi mesti jelas dan diaudit.

## 26. B-Event AI Agents

### 26.1 Brief-to-Plan Assistant
Input: event brief, date, budget, guest count, venue constraints.
Output: suggested scope, preliminary timeline, task list, resource checklist, questions requiring clarification.
AI tidak boleh menganggap maklumat yang tiada sebagai fakta.

### 26.2 Budget Planner
- Cadangkan pecahan budget, tunjukkan anggaran dan assumptions
- Bandingkan dengan data sejarah yang sah
- Tandakan item yang belum mempunyai quotation
- Berikan variance explanation
AI tidak boleh memuktamadkan commitment atau pembayaran tanpa permission dan approval.

### 26.3 Vendor Match Assistant
- Cari vendor yang memenuhi kriteria
- Bandingkan harga, availability dan service fit
- Nyatakan sumber serta masa maklumat diambil
- Bezakan fakta disahkan daripada cadangan AI

### 26.4 Proposal & Document Assistant
- Draf proposal, draf client communication
- Sediakan checklist kontrak, ringkaskan perubahan versi
- Minta pengesahan manusia sebelum dokumen dihantar atau dimuktamadkan

### 26.5 Timeline & Risk Assistant
- Kenal pasti dependencies, kesan konflik jadual
- Cadangkan buffer, ringkaskan risiko
- Cadangkan contingency plan

### 26.6 Event-Day Copilot
- Cari run sheet, jawab soalan berdasarkan dokumen yang dibenarkan
- Rumuskan status, cadangkan escalation
- Rekod tindakan hanya melalui tools yang dibenarkan

### 26.7 Post-Event Analyst
- Bandingkan planned versus actual, rumuskan kos
- Analisis feedback, kenal pasti vendor issues
- Cadangkan penambahbaikan

## 27. B-Event Reports

- Sales pipeline, lead conversion, event profitability
- Budget variance, vendor performance, crew utilization
- Asset utilization, rental damage/loss, venue utilization
- RSVP conversion, attendance, payment collection
- Outstanding invoices, post-event satisfaction

Setiap report mesti mendokumenkan formula, filter, sumber data dan masa kemas kini.

## 28. B-Event Acceptance Tests

- Booking bertindih ditolak apabila resource eksklusif
- Expired hold dilepaskan dengan betul
- Reschedule menjalankan semakan konflik semula
- Proposal mempunyai version history
- Perubahan budget memerlukan permission yang sesuai
- Estimated, committed dan actual costs dibezakan
- Vendor hanya melihat maklumat yang dibenarkan
- Crew tidak boleh membaca rekod event yang tidak diberikan kepadanya
- Duplicate QR scan tidak menggandakan kehadiran
- Aset rosak atau maintenance tidak ditawarkan sebagai available
- Payment webhook duplicate tidak menggandakan bayaran
- Refund, payout dan perubahan kontrak mempunyai approval yang sesuai
- AI retrieval tidak membocorkan data tenant lain
- Report boleh dijejak kepada rekod sumber
- Kegagalan notifikasi tidak memadam transaksi
- Backup/restore mengekalkan integriti booking, budget dan inventory
- Audit log merekod tindakan sensitif
- Import data mengesan format salah dan duplicate
- Semua kegagalan kritikal mempunyai error handling yang jelas

---

# PART VI — B-BRAIN HUB & AI TOKEN WALLET

## 29. Tujuan B-Brain Hub

B-Brain Hub ialah lapisan AI pusat yang digunakan oleh modul perniagaan dan industry packs.

Ia bukan sekadar chat interface. Ia perlu menyediakan:

- AI provider gateway, model catalog, agent registry
- Tool registry, knowledge retrieval, prompt/version management
- Approval policy, usage metering, token wallet
- Cost accounting, rate limits, evaluation, audit, failure handling

## 30. AI Request Lifecycle

```
User / Workflow
    ↓
Authenticate
    ↓
Resolve Tenant & Permissions
    ↓
Check Module Entitlement
    ↓
Check Quota / Wallet
    ↓
Select Allowed Model
    ↓
Retrieve Authorized Context
    ↓
Run Model Through Server-side Gateway
    ↓
Validate Output / Tool Call
    ↓
Approval Gate if Required
    ↓
Execute Approved Tool
    ↓
Record Usage, Cost, Result and Audit
    ↓
Return Response
```

Setiap langkah perlu mempunyai error handling. Kegagalan satu langkah tidak boleh menyebabkan penggunaan atau tindakan direkod secara salah.

## 31. Model Catalog

Model registry perlu menyimpan:

- Provider, model ID, supported capabilities, input/output modality
- Context limits, pricing metadata, availability
- Allowed use cases, rate limits, timeout, fallback policy, review date

Harga dan kemampuan model berubah. Jangan menganggap model mempunyai harga, had atau ciri yang sama sepanjang masa.

## 32. Server-side AI Gateway

### 32.1 Secret Management

- Provider API key disimpan di server atau secret manager
- Jangan hantar provider key kepada browser, mobile app atau tenant
- Jangan masukkan key ke dalam source code, client bundle atau log
- Sokong key rotation
- Hadkan akses secret kepada servis yang memerlukannya
- Sediakan revocation dan incident response

### 32.2 Tenant Isolation

Setiap AI request perlu mempunyai tenant context yang disahkan.

- Retrieval hanya mencari sumber yang dibenarkan
- Cache mesti memisahkan tenant dan access scope
- Conversation history perlu mematuhi permission
- Tool execution mesti memeriksa authorization semula
- Output tidak boleh mendedahkan data tenant lain
- Jangan bergantung pada prompt sahaja untuk mengawal akses

## 33. AI Token Wallet

AI BIJAN boleh menggunakan provider key milik platform untuk menyediakan AI kepada tenant dan mengenakan caj mengikut penggunaan.

Ini memerlukan kawalan teknikal dan kewangan yang jelas.

### 33.1 Konsep Berasingan

Bezakan:

1. **Provider usage:** input/output tokens atau unit penggunaan provider
2. **Provider cost:** kos sebenar yang dikenakan oleh provider
3. **Internal usage units:** unit dalaman untuk metering
4. **Customer price:** harga jualan kepada pelanggan
5. **Margin:** perbezaan hasil dan kos mengikut kaedah pengiraan yang ditetapkan
6. **Wallet balance:** kredit yang boleh digunakan pelanggan
7. **Reserved balance:** kredit yang telah diperuntukkan kepada request tetapi belum diselesaikan

Jangan menyamakan token dengan ringgit tanpa kadar penukaran yang jelas.

### 33.2 Usage Ledger

Cadangan entiti:

- `ai_wallets`, `ai_wallet_transactions`, `ai_usage_reservations`
- `ai_usage_records`, `ai_provider_cost_records`
- `ai_model_pricing_versions`, `ai_usage_reconciliations`
- `ai_credit_adjustments`, `ai_refund_records`

Setiap rekod perlu mempunyai rujukan request dan tenant yang sesuai.

### 33.3 Reserve, Execute, Settle

1. Anggar had penggunaan untuk request
2. Reserve kredit secara atomik
3. Jalankan request
4. Terima usage metadata daripada provider jika tersedia
5. Kira caj menggunakan pricing version yang berkuat kuasa
6. Settle penggunaan sebenar
7. Lepaskan lebihan reservation
8. Rekod kegagalan atau adjustment dengan audit

Jika provider tidak memberikan usage metadata, tandakan pengiraan sebagai anggaran atau berdasarkan polisi yang didokumenkan.

### 33.4 Concurrency dan Overspending

- Dua request serentak tidak boleh membelanjakan kredit yang sama
- Reservation mempunyai expiry dan recovery process
- Retry tidak boleh menggandakan caj
- Provider timeout perlu mempunyai status yang boleh diselesaikan
- Unknown provider result perlu masuk reconciliation
- Refund/credit adjustment memerlukan reason dan permission

### 33.5 Pricing

Sokong: Pay-as-you-go, prepaid wallet, included usage, monthly allowance, tenant-specific limits, user-level limits, model-specific pricing, markup rules, promotional credits, manual adjustment dengan audit.

Harga sebenar mesti boleh dikemas kini melalui pricing versioning. Jangan hardcode kadar provider dalam frontend.

## 34. AI Agent Registry

Setiap agent perlu mempunyai:

- `agent_id`, `agent_version`, purpose, allowed modules
- Allowed tools, allowed data scope, allowed models
- Token budget, timeout, retry policy
- Approval policy, evaluation suite, owner, status
- Change history

Agent tidak boleh diberikan akses global hanya kerana ia digunakan merentas platform.

## 35. Tool Execution & Approval

Contoh tahap risiko:

| Tahap | Contoh | Polisi |
|-------|--------|--------|
| Rendah | Ringkasan dokumen yang dibenarkan | Boleh automatik |
| Sederhana | Draf e-mel atau task | Semakan mengikut tetapan |
| Tinggi | Hantar dokumen rasmi, ubah booking kritikal | Approval atau policy khusus |
| Kritikal | Refund besar, payout, perubahan kewangan berisiko | Human approval dan kawalan tambahan |

Senarai ini ialah contoh. Organisasi perlu menentukan threshold dan policy sebenar.

Sebelum tool dijalankan: validate schema, semak permission, semak tenant, semak entitlement, semak idempotency, semak approval, log request, jalankan tindakan, rekod outcome.

## 36. AI Security & Evaluation

Uji: prompt injection, data exfiltration, cross-tenant retrieval, unauthorized tool call, hallucinated facts, malicious document instructions, secret leakage, repeated tool calls, budget exhaustion, provider timeout, incorrect language or locale, sensitive data exposure.

AI tidak boleh dianggap tepat hanya kerana output kelihatan meyakinkan. Sediakan evaluation set untuk setiap use case kritikal.

## 33a. AI Reseller Platform — Admin HQ & Kawalan Penuh Bekalan AI

### 33a.1 Konsep Sistem AI Bijan v2.3

Saya bukan sekadar mahu mengintegrasikan AI ke dalam SaaS. Saya mahu menjadikan **AI Bijan sebagai platform pembekal dan penjual semula penggunaan AI** untuk kegunaan semua modul di AI Bijan, di mana saya mengawal:

- **Bekalan** — sambungan kepada AI provider (serveras.click dan lain-lain)
- **Model** — katalog model dengan nama paparan berasingan daripada ID dalaman
- **Harga** — markup, pakej, kadar per-model
- **Token** — metering, wallet, reservation, settlement
- **Pelanggan** — tenant yang menggunakan perkhidmatan AI
- **Keuntungan** — spread antara kos bekalian dan harga jualan

**Prinsip paling penting:** pelanggan menggunakan perkhidmatan melalui AI Bijan. API key pembekal dan pemetaan model dalaman **tidak dihantar ke aplikasi pelanggan**. Anda mengurus semua bekalan dan kadar dalaman melalui Admin HQ.

Semua endpoint, auth, error handling dan model dari provider di-rebrand sepenuhnya ke AI Bijan. Pelanggan tidak pernah tahu siapa provider sebenar.

### 33a.2 Admin HQ — Kawalan Penuh

Admin HQ ialah dashboard Super Admin untuk mengurus seluruh ekosistem AI:

| Fungsi | Kawalan |
|--------|---------|
| **API Supplier** | Daftar, aktifkan/nonaktifkan, rotate key, semak health status |
| **Model Catalog** | Tambah/edit/hapus model, tetapkan nama paparan, kadar input/output, context length, max output |
| **Harga & Pakej** | Tetapkan markup per-model, cipta pakej token, promosi, discount |
| **Token Metering** | Monitor penggunaan real-time, reconciliation, anomaly detection |
| **Pelanggan AI** | Lihat baki setiap tenant, history penggunaan, limit, suspend/resume |
| **Keuntungan** | Laporan spread per-model, per-tenant, per-pakej, revenue attribution |
| **Safety Controls** | Pause inference global, disable model tertentu, rate limit darurat |

### 33a.3 Gateway Dalaman AI Bijan — Selamat

```
┌─────────────────────────────────────────────────────┐
│              SUPER ADMIN HQ                          │
│  (Kawalan: model, harga, token, pelanggan, untung)   │
└──────────────────┬──────────────────────────────────┘
                   │ Config, Pricing, Monitoring
                   ▼
┌─────────────────────────────────────────────────────┐
│         AI BIJAN INTERNAL GATEWAY                    │
│                                                      │
│  • Secret Manager     (API keys disimpan server)     │
│  • Model Router       (alias → upstream ID)          │
│  • Quota Checker      (wallet balance check)         │
│  • Usage Meter        (token counting & logging)     │
│  • Rate Limiter       (per-tenant, global)           │
│  • Safety Switch      (pause, disable, emergency)    │
│  • Reconciliation     (provider vs customer ledger)  │
└──────────────────┬──────────────────────────────────┘
                   │ Authenticated Request
                   ▼
┌─────────────────────────────────────────────────────┐
│         EXTERNAL PROVIDER (serveras.click)           │
│         https://serveras.click/v1/*                  │
│         API key: asai_sk_...                         │
└─────────────────────────────────────────────────────┘
```

**Peraturan keselamatan gateway:**

1. Provider API key **HANYA** disimpan di server/secret manager
2. Jangan hantar provider key ke browser, mobile app atau tenant
3. Jangan masukkan key ke dalam source code, client bundle atau log
4. Sokong key rotation tanpa downtime
5. Hadkan akses secret kepada servis yang memerlukannya
6. Sediakan revocation dan incident response
7. Semua AI request melalui gateway — tiada direct access dari frontend

### 33a.4 Katalog Model — Customer-Facing Names

Setiap model mempunyai **dua identiti**:

| ID Upstream (Provider) | Nama Paparan Pelanggan | Context | Max Output | Input Rate | Output Rate |
|------------------------|----------------------|---------|------------|------------|-------------|
| `asai/gpt-6-astra` | gpt-6-astra | 1,050,000 | 16,384 | 10/token | 50/token |
| *(model lain...)* | *(nama ringkas)* | *(value)* | *(value)* | *(value)* | *(value)* |

**Peraturan katalog model:**

- Pelanggan **HANYA** nampak nama ringkas seperti `gpt-6-astra`
- Prefix `asai/` adalah dalaman sahaja — tidak pernah dihantar ke aplikasi pelanggan
- `GET /v1/models` dari provider **tidak pernah dedahkan** upstream names
- Setiap model boleh diaktifkan/nonaktifkan dari Admin HQ
- Model yang disabled return `503 model_disabled` kepada pelanggan
- Kadar boleh berubah — pricing versioning diperlukan
- Multiplier pembekal perlu disahkan supaya baki, caj dan keuntungan dikira betul

### 33a.5 Token & Bil System

#### Konsep Berasingan (Reinforced for Reseller Model)

| Konsep | Maksud |
|--------|--------|
| **Provider usage** | Input/output tokens yang dikenakan oleh serveras.click |
| **Provider cost** | Kos sebenar dalam USD/MYR yang dikenakan provider |
| **Internal units** | Unit dalaman AI Bijan untuk metering (1 unit = 1,000 tokens standard) |
| **Customer price** | Harga jualan kepada tenant (MYR) |
| **Spread/margin** | Perbezaan hasil dan kos — sumber pendapatan utama |
| **Wallet balance** | Kredit yang tersedia untuk tenant gunakan |
| **Reserved balance** | Kredit reserved untuk request ongoing (belum settle) |
| **Package validity** | Tempoh sah pakej (semua: 30 hari dari pembelian) |

#### Struktur Pakej Token AI Bijan

| Pakej | Token (unit dalaman) | Harga Jual | Kos Bekalan | Spread Kasar | Margin % |
|-------|------------|------------|--------------|--------------|----------|
| **Starter** | 80 juta token (80,000 unit) | RM80 | RM5 | RM75 | 93.8% |
| **Basic** | 140 juta token (140,000 unit) | RM110 | RM8 | RM102 | 92.7% |
| **Plus** | 200 juta token (200,000 unit) | RM160 | RM10 | RM150 | 93.8% |
| **Pro** | 1.1 bilion token (1,100,000 unit) | RM600 | RM50 | RM550 | 91.7% |
| **Max** | 2.25 bilion token (2,250,000 unit) | RM1,100 | RM100 | RM1,000 | 90.9% |

**Nota penting:**
- Semua pakej: **30 hari sah** dari tarikh pembelian
- Spread di atas ialah kasar — belum tolak kos operasi (infrastruktur, payment gateway, support)
- Kos bekalian berdasarkan anggaran dari serveras.click rates — perlu disahkan dengan provider actual pricing
- Markup berbeza mengikut pakej: pakej besar ada margin percentage lebih rendah tetapi absolute profit lebih tinggi
- Promosi/discount boleh reduce actual margin

#### Aliran Wallet Transaction

```
Tenant Beli Pakej
    ↓
Payment Gateway (ToyyibPay/Billplz/dll.)
    ↓
Webhook Verified ✓
    ↓
Credit Tokens ke Tenant Wallet
    ↓
Record: ai_wallet_transactions (type=purchase, amount, package_id)
    ↓
Notifikasi: "RM80 Starter — 80 juta token (80,000 unit) ditambah"
```

```
Tenant AI Request
    ↓
Check Wallet Balance ≥ Estimated Cost
    ↓
RESERVE credits (atomic lock)
    ↓
Gateway → serveras.click/v1/chat/completions
    ↓
Response + usage metadata
    ↓
SETTLE: deduct actual used from reserved
    ↓
Release excess back to wallet
    ↓
Record: ai_usage_records (actual tokens, cost, model)
    ↓
If insufficient after settle → alert tenant, freeze new requests
```

### 33a.6 Pelanggan AI Bijan — Pengalaman Rebranded

Pelanggan **tidak pernah lihat**:

- ❌ API key serveras.click
- ❌ Model IDs upstream (`asai/gpt-6-sol`, dll)
- ❌ Provider endpoints (`serveras.click`)
- ❌ Rates upstream (input/output per token)
- ❌ Provider dashboard

Pelanggan AI Bijan **hanya lihat**:

- ✅ Baki token AI Bijan (dalam dashboard tenant)
- ✅ Penggunaan harian/mingguan/bulanan
- ✅ Pakej aktif dan expiry date
- ✅ Model yang boleh digunakan (dengan nama AI Bijan)
- ✅ History request (tanpa provider details)
- ✅ Option beli pakej tambahan

### 33a.7 Provider Integration — serveras.click

Base URL: `https://serveras.click/v1`

#### Authentication

```
Authorization: Bearer asai_sk_...
```

- Keys shown once at creation, revealable later from dashboard
- Max 3 active keys per account, share one credit balance
- AI Bijan gateway menyimpan key ini — tenant tidak pernah lihat

#### Endpoints yang Digunakan

| Method | Path | Auth | Kegunaan di AI Bijan |
|--------|------|------|---------------------|
| `POST` | `/v1/chat/completions` | API key | Semua chat/completion requests dari tenant AI |
| `POST` | `/v1/responses` | API key | Codex CLI compatibility (untuk developer tenants) |
| `POST` | `/v1/messages` | API key | Anthropic Messages compatibility (Claude Code tenants) |
| `GET` | `/v1/models` | Public | Fetch model aliases & rates untuk sync katalog |

#### POST /v1/chat/completions — Request Mapping

AI Bijan menerima request dari tenant, map model alias ke upstream alias, kemudian forward:

```json
// Tenant kirim ke AI Bijan Gateway
{
  "model": "gpt-6-astra",
  "messages": [
    {"role": "system", "content": "Anda adalah pembantu perniagaan."},
    {"role": "user", "content": "Berapakah 2+2?"}
  ],
  "max_tokens": 100,
  "stream": false
}

// AI Bijan Gateway map dan forward ke serveras.click
{
  "model": "asai/gpt-6-astra",
  "messages": [
    {"role": "system", "content": "You are helpful."},
    {"role": "user", "content": "What is 2+2?"}
  ],
  "max_tokens": 100,
  "stream": false
}
```

#### GET /v1/models — Response Filtering

Provider returns all models. AI Bijan gateway **filter dan transform** sebelum tenant lihat:

```json
// Provider raw response (gateway internal)
{
  "data": [
    {"id": "asai/gpt-6-astra", "rates": {"input": "10", "output": "50"}},
    {"id": "asai/claude-haiku", "rates": {"input": "2.5", "output": "10"}},
    ...
  ]
}

// AI Bijan transformed response (tenant-facing)
{
  "data": [
    {
      "id": "gpt-6-astra",
      "name": "gpt-6-astra",
      "context_length": 1050000,
      "max_output": 16384,
      "pricing_tier": "premium",
      "available": true
    }
  ]
}
```

#### Error Handling — Standardisasi

Provider errors ditransform ke AI Bijan error format:

| StatusCode | Provider Error | AI Bijan Error | Maksud |
|------------|---------------|----------------|--------|
| 401 | `invalid_api_key` | `invalid_api_key` | API key tidak sah |
| 402 | `insufficient_credits` | `insufficient_credits` | Baki tenant tidak cukup |
| 403 | `key_expired` | `api_key_expired` | API key tenant tamat tempoh |
| 403 | `account_disabled` | `account_disabled` | Akaun tenant disuspend |
| 404 | `model_not_found` | `model_not_found` | Model alias tidak wujud |
| 429 | `rate_limit_exceeded` | `rate_limit_exceeded` | Tenant hit rate limit |
| 503 | `model_disabled` | `model_disabled` | Model dimatikan admin |
| 503 | `gateway_paused` | `inference_paused` | Emergency pause oleh admin |
| 503 | `upstream_unavailable` | `provider_unavailable` | Provider tidak reachable |
| 503 | `accounting_unavailable` | `billing_unavailable` | Billing system down |

**Peraturan critical:**
- Failures **sebelum** output delivery **tidak** dicaj kepada tenant
- Partial streamed output mungkin perlu reconciliation
- Semua error masuk audit log untuk troubleshooting

### 33a.8 Safety & Control Plane

Super Admin boleh:

| Tindakan | Kesannya | Keadaan Penggunaan |
|----------|----------|-------------------|
| **Pause Inference Global** | Semua AI request ditolak (503) | Provider outage, emergency maintenance |
| **Disable Model Tertentu** | Model itu return 503 `model_disabled` | Model bermasalah, kadar berubah drastik |
| **Set Rate Limit Tenant** | Tenant max N requests/min | Abuse prevention, fair usage |
| **Suspend Tenant AI Access** | Tenant tak boleh buat AI request | Payment default, ToS violation |
| **Manual Credit Adjustment** | Tambah/tolak token tenant wallet | Compensation, error correction, promo |
| **Enable Dry-Run Mode** | AI request simulate tanpa call provider | Testing new models, pricing validation |

### 33a.9 Reconciliation & Accounting

Setiap request AI perlu reconcile 3 sistem:

```
Provider Invoice (serveras.click)
         ↕ reconcile
AI Bijan Usage Ledger (internal metering)
         ↕ reconcile
Tenant Wallet Balance (customer billing)
```

**Reconciliation rules:**

1. Daily: compare provider usage logs vs AI Bijan usage records
2. Weekly: verify wallet transactions match package purchases
3. Monthly: full reconciliation — provider cost vs tenant revenue
4. Anomaly detection: flag unusual token consumption patterns
5. Dispute resolution: tenant can request breakdown of usage

### 33a.10 Acceptance Tests — AI Reseller Platform

| # | Test Case | Expected Result |
|---|-----------|-----------------|
| 1 | Tenant dengan baki 0 cuba AI request | Ditolak 402 `insufficient_credits` |
| 2 | Dua request serentak dari tenant sama | Reservation atomic, tidak double-spend |
| 3 | Provider timeout selepas stream start | Partial output reconciled, tenant hanya caj untuk completed tokens |
| 4 | Admin disable model | Tenant dapat 503 `model_disabled`, tidak dapat upstream error |
| 5 | Admin pause inference global | Semua request dapat 503 `inference_paused` |
| 6 | Tenant request dengan model yang sudah di-upgrade kadar | Pricing version yang betul digunakan |
| 7 | Package expiry semasa request ongoing | Request ongoing selesai, request baru ditolak |
| 8 | Manual credit adjustment oleh admin | Record di `ai_credit_adjustments` dengan reason & permission |
| 9 | Reconciliation daily run | No discrepancy > threshold, alert jika ada |
| 10 | Tenant API key leak → revoked | Old key rejected 401, new key issued |
| 11 | Provider rates change mid-month | New pricing version effective from specified date, old records preserved |
| 12 | Tenant dispute usage claim | Usage breakdown available, audit trail complete |

---

# PART VII — FINANCE, PAYMENTS & MALAYSIA INTEGRATIONS

## 39. Domain yang Perlu Dipisahkan

Jangan campurkan:

- Platform subscription billing
- AI Token Wallet
- Tenant invoicing
- Tenant finance ledger
- Payment provider transactions
- Customer deposits
- Vendor payments
- Refunds
- Marketplace settlements

Setiap domain mempunyai source of truth dan reconciliation rules tersendiri.

## 40. Payment Lifecycle

```
Invoice / Payment Request
    ↓
Payment Initiated
    ↓
Provider Checkout / Redirect
    ↓
Provider Callback or Webhook
    ↓
Signature / Status Verification
    ↓
Idempotency Check
    ↓
Finalize Payment
    ↓
Ledger / Allocation Reconciliation
    ↓
Update Business Status
    ↓
Notification
```

Jangan menganggap browser redirect sebagai bukti pembayaran berjaya. Status perlu disahkan dengan mekanisme provider yang sesuai.

### 40.1 Acceptance Tests

- Duplicate callback tidak menggandakan transaksi
- Invalid signature ditolak
- Callback yang lambat tidak membatalkan transaksi yang sah
- Refund memerlukan permission dan audit
- Invoice allocation sepadan dengan ledger
- Payment status yang bercanggah menghasilkan alert
- Kegagalan notification tidak membalikkan payment
- Provider timeout menghasilkan status yang boleh direconcile
- Amount dan currency dibandingkan dengan rekod server
- Setiap transaksi mempunyai reference ID yang boleh dijejak

## 41. Integrasi Malaysia

Integrasi yang boleh dinilai:

- Payment gateways, E-Invoice / MyInvois
- E-mel, WhatsApp Business melalui penyedia yang dibenarkan
- SMS, Calendar, Maps/geocoding
- Accounting exports, cloud storage
- AI providers, shipping/logistics providers
- Ad platforms

Sebelum pelaksanaan:

1. Sahkan dokumentasi rasmi terkini
2. Sahkan kelayakan akaun merchant
3. Sahkan sandbox
4. Sahkan API access dan approval
5. Sahkan rate limits
6. Sahkan callback/security requirements
7. Sahkan kos
8. Sahkan data retention
9. Sediakan test cases
10. Sediakan rollback dan reconciliation

Jangan mendakwa integrasi telah lengkap jika hanya UI atau konfigurasi asas tersedia.

---

# PART VIII — DATA, REPORTS & GOVERNANCE

## 42. Standard Data Model

Entiti domain lazimnya memerlukan:

- Primary ID, tenant reference, status
- Created/updated timestamps, actor references
- Version atau revision jika diperlukan
- External reference jika berintegrasi
- Audit event, soft-delete/retention policy apabila sesuai

Jangan menambah field yang sama ke semua jadual tanpa sebab domain yang jelas.

## 43. Data Ownership

Setiap domain perlu mendokumenkan:

- Owner module, creation rules, update rules, deletion rules
- Downstream consumers, API contract, event contract
- Retention, export, audit, recovery

## 44. Import dan Export

Sediakan: CSV/XLSX templates jika sesuai, field mapping, validation report, duplicate detection, dry run, error file, import batch ID, resume/retry, permission checks, export audit.

Import tidak boleh meninggalkan transaksi separuh siap tanpa status yang jelas.

## 45. Data Privacy dan Retention

- Kumpul data yang diperlukan sahaja
- Tentukan purpose dan access scope
- Sediakan retention policy
- Sediakan proses export/deletion mengikut kewajipan yang terpakai
- Hadkan akses staff
- Jangan masukkan data peribadi ke dalam AI context tanpa keperluan dan kebenaran
- Pastikan backup, log dan cache mematuhi polisi yang ditetapkan

Keperluan pematuhan perlu disemak mengikut jenis data, peranan organisasi, aktiviti dan peraturan semasa.

---

# PART IX — UI/UX & PRODUCT EXPERIENCE

## 46. Prinsip UI/UX

- Clean, modern dan konsisten
- Mobile-responsive
- Navigation berdasarkan role dan modul aktif
- Loading, empty, success, error dan partial failure states
- Accessible labels dan keyboard navigation
- Confirmation untuk tindakan berisiko
- Form validation yang jelas
- Tidak menyembunyikan status penting
- Tidak bergantung pada warna sahaja untuk menunjukkan status
- Bahasa dan format mengikut locale

## 47. Module Activation UX

- Paparkan modul aktif
- Paparkan modul yang memerlukan upgrade
- Bezakan fungsi unavailable daripada permission denied
- Berikan sebab apabila tindakan ditolak
- Jangan dedahkan data melalui preview atau notification yang tidak dibenarkan
- Berikan onboarding untuk setiap modul
- Paparkan had penggunaan AI dan kos dengan jelas

## 48. Platform Admin

Platform HQ perlu mempunyai kawalan bagi:

- Tenant management, subscription plans, module catalog
- Entitlement management, AI model catalog
- AI provider configuration, token pricing
- Usage reconciliation, industry blueprint versions
- Integration health, security events
- Audit review, support access, incident management

Support access ke data pelanggan mesti terhad, direkod dan mempunyai sebab yang sah.

---

# PART X — QA, TESTING & RELEASE READINESS

## 49. Test Layers

### 49.1 Unit Tests
Uji business rules, validators, pricing calculations, state transitions dan utility functions.

### 49.2 API Integration Tests
Uji request/response, authentication, permissions, validation, error handling dan data consistency.

### 49.3 End-to-end Tests
Uji aliran sebenar dari permulaan sehingga selesai.

### 49.4 Authorization Tests
Uji role, tenant, branch, ownership, assigned records dan entitlement.

### 49.5 Idempotency Tests
Uji duplicate requests, retries, callbacks dan webhook replays.

### 49.6 Failure Recovery
Uji timeout, queue failure, provider outage, database transaction rollback dan partial completion.

### 49.7 Performance
Tetapkan threshold berdasarkan sasaran pengguna, beban dan kos yang telah dipersetujui. Jangan menetapkan angka production tanpa benchmark.

### 49.8 Backup and Restore
Uji restore sebenar dan sahkan integriti data selepas pemulihan.

## 50. Production Readiness Checklist

### Security
- Tiada critical/high blocker yang belum diterima secara formal
- Tenant isolation tests lulus
- Permission checks dibuat di server
- Secrets tidak didedahkan
- Rate limiting dan abuse protection tersedia
- Audit trail untuk tindakan sensitif

### Product
- Workflow utama lengkap
- Empty/error/retry states wujud
- Dokumen dan help tersedia
- Permission matrix didokumenkan
- Data ownership jelas
- Known limitations dinyatakan

### Payments and Finance
- Idempotency lulus
- Reconciliation tersedia
- Refund permissions diuji
- Duplicate callback diuji
- Amount/currency validation diuji
- Unknown status boleh disiasat

### AI
- Provider keys disimpan di server
- Usage attribution tepat
- Wallet reservation/settlement diuji
- Cross-tenant retrieval diuji
- Tool permissions diuji
- Approval gates diuji
- Model pricing versioning tersedia
- Timeout dan retries diuji
- AI evaluation dijalankan

### Operations
- Monitoring dan alerting tersedia
- Backup/restore diuji
- Migration telah diuji
- Rollback/disable plan wujud
- Runbook sokongan tersedia
- Incident response ditetapkan
- Kos operasi diketahui

---

# PART XI — ROADMAP PEMBANGUNAN

## 51. Fasa 0: Discovery & Catalog

- Pilih 3–5 industri pertama berdasarkan permintaan sebenar
- Sediakan industry directory
- Semak MSIC mapping melalui sumber rasmi
- Dokumentasikan persona dan workflow
- Tentukan MVP scope dan acceptance tests

**Gate:** scope dan workflow MVP diluluskan.

## 52. Fasa 1: Shared Core

- Authentication, tenant/organization/branch
- Roles/permissions, entitlements
- Audit trail, file storage, notifications
- API foundation, basic usage metering

**Gate:** tenant isolation dan authorization tests lulus.

## 53. Fasa 2: Satu Vertical Slice

Pilih satu aliran perniagaan lengkap. Contoh:

- Retail: product → order → stock → invoice/payment → report
- Salon: service → availability → booking → payment → loyalty/report
- Event: lead → proposal → booking → vendor/resource allocation → event delivery → reconciliation

**Gate:** pengguna sebenar boleh menyelesaikan aliran tanpa workaround kritikal.

## 54. Fasa 3: Finance & Integrations

- Invoicing, payment adapter, ledger/reconciliation
- Integration logs, retry/idempotency
- Export/reporting

**Gate:** payment lifecycle dan reconciliation tests lulus.

## 55. Fasa 4: B-Brain Hub

- Server-side AI gateway, model catalog
- Usage ledger, wallet reservation/settlement
- Tool registry, permission-aware retrieval
- Approval policies, AI evaluation

**Gate:** usage metering, security dan failure recovery lulus.

## 56. Fasa 5: Industry Blueprint Engine

- Industry selection, module recommendation
- Workflow templates, role templates
- Custom fields, dashboard presets
- Versioning, migration/review workflow

**Gate:** tenant boleh menggunakan blueprint tanpa menjejaskan tenant lain.

## 57. Fasa 6: Industry Expansion

- Tambah industri berdasarkan permintaan berbayar
- Reuse modul generik yang sesuai
- Bina modul khusus apabila domain memerlukannya
- Lengkapkan tests untuk setiap industry pack
- Sediakan dokumentasi deployment dan support

**Gate:** setiap industri mempunyai workflow demonstrable dan acceptance suite sendiri.

---

# PART XII — DEFINITION OF DONE

## 58. Definition of Done untuk Modul

Modul hanya dianggap selesai apabila:

1. Tujuan dan skop ditetapkan
2. Persona dan permission matrix tersedia
3. Data model dan ownership ditetapkan
4. Workflow hujung-ke-hujung tersedia
5. API dan error contract didokumenkan
6. Audit trail tersedia untuk tindakan relevan
7. Loading, empty, success dan failure states tersedia
8. Tenant isolation diuji
9. Authorization diuji di server
10. Idempotency diuji jika relevan
11. Integration/failure recovery diuji
12. Reporting mempunyai formula yang jelas
13. Import/export mempunyai validation
14. Dokumentasi pengguna tersedia
15. Dokumentasi support tersedia
16. Monitoring dan alerting tersedia
17. Backup/restore impact dinilai
18. Known limitations direkod
19. Security review diselesaikan
20. Status pembangunan dilabel dengan tepat

## 59. Definition of Done untuk Industry Blueprint

Setiap blueprint mesti mempunyai:

1. Persona dan peranan
2. Aktiviti perniagaan yang disokong
3. MSIC mapping yang disahkan atau status belum disahkan
4. Required/recommended/optional modules
5. Workflow utama
6. Entiti dan data khusus
7. Permission matrix
8. Documents dan notifications
9. KPI dan laporan
10. Integration contracts
11. Acceptance tests
12. Data import template
13. Onboarding guide
14. Known limitations
15. Review status dan version
16. Semakan keperluan lesen/peraturan yang relevan
17. Status sokongan yang jelas

Jangan label industri sebagai "fully supported" hanya kerana ia muncul dalam dropdown.

---

# PART XIII — OPEN DECISIONS REGISTER

## 60. Keputusan yang Perlu Dibuat Sebelum Pembangunan Besar

| ID | Keputusan | Pilihan/Pertimbangan | Status |
|----|-----------|----------------------|--------|
| D-001 | Industri MVP pertama | Berdasarkan pelanggan berbayar dan workflow yang boleh diselesaikan | Open |
| D-002 | Backend stack | Sahkan stack sebenar dan keupayaan pasukan | Open |
| D-003 | Frontend/mobile | Sahkan web/mobile target | Open |
| D-004 | Database | Sahkan consistency, transactions, backup dan deployment constraints | Open |
| D-005 | Tenant model | Shared database/schema atau model lain berdasarkan risk/scale | Open |
| D-006 | Module packaging | Core, add-ons, usage-based features | Open |
| D-007 | AI pricing | Prepaid, included quota, pay-as-you-go atau gabungan | Open |
| D-008 | Payment provider | Berdasarkan merchant access dan keperluan pelanggan | Open |
| D-009 | E-Invoice | Sahkan keperluan dan integrasi rasmi semasa | Open |
| D-010 | Data retention | Mengikut jenis data dan kewajipan yang terpakai | Open |
| D-011 | Human approval | Tentukan tindakan dan threshold berisiko | Open |
| D-012 | AI models | Pilih berdasarkan quality, latency, privacy dan kos | Open |
| D-013 | Backup/DRT | Tetapkan RPO/RTO berdasarkan keperluan operasi | Open |
| D-014 | Production gate | Tetapkan threshold ujian dan operasi yang boleh diukur | Open |
| D-015 | Support access | Polisi akses sementara, approval dan audit | Open |

Keputusan yang belum dipersetujui tidak boleh dianggap sebagai konfigurasi muktamad.

---

# PART XIV — RECOMMENDED DOCUMENT SET

## 61. Pecahan Dokumen untuk Penyelenggaraan

Blueprint induk ini perlu menjadi index utama. Untuk pelaksanaan, pecahkan spesifikasi terperinci kepada dokumen khusus:

1. `BIJAN_MASTER_BLUEPRINT_v4.1.md` — Dokumen ini
2. `BIJAN_PRODUCT_REQUIREMENTS.md`
3. `BIJAN_SHARED_CORE_SECURITY.md`
4. `BIJAN_MODULE_CATALOG.md`
5. `BIJAN_INDUSTRY_BLUEPRINTS.md`
6. `BIJAN_EVENT_RENTAL_SPEC.md`
7. `BIJAN_BRAIN_HUB_SPEC.md`
8. `BIJAN_AI_TOKEN_WALLET.md`
9. `BIJAN_FINANCE_INTEGRATIONS.md`
10. `BIJAN_DATA_MODEL.md`
11. `BIJAN_API_CONTRACTS.md`
12. `BIJAN_TEST_PLAN.md`
13. `BIJAN_RELEASE_READINESS.md`
14. `BIJAN_DECISIONS_REGISTER.md`
15. `BIJAN_CHANGELOG.md`

Dokumen pecahan tidak boleh bercanggah dengan blueprint induk. Perubahan penting perlu direkodkan dalam changelog.

---

# PART XV — CHANGE CONTROL

## 62. Versioning Policy

- **Major version:** perubahan besar kepada seni bina atau kontrak
- **Minor version:** modul atau capability baharu yang serasi
- **Patch version:** pembetulan dokumentasi atau penjelasan yang tidak mengubah kontrak utama

Setiap perubahan perlu menyatakan:

- Sebab, bahagian yang berubah
- Impak kepada modul, data, API, permission
- Migration requirement, acceptance tests
- Reviewer, tarikh berkuat kuasa

## 63. Changelog

### v4.5.1 — 18 Oktober 2026 (pembetulan editorial sahaja)

- Penomboran 128 sub-seksyen diselaraskan dengan seksyen induk (cth §154 mengandungi "153.1"/"151.2.1"; §134 mengandungi "33.1").
- 8 rujukan silang dibetulkan (cth "Section 163/164" → 166/167; "Section 30-37" → 29–36 & 33a).
- Ejaan penyedia pembayaran: "Billplakk" → "Billplz".
- Keputusan pemilik 18/10/2026: harga tahunan = **2 bulan percuma** (RM490 / RM1,490 / RM3,490 / RM7,990); tagline rasmi **"Your Team, in Virtual Office"**; pakej token dinyatakan dalam **token** (1 unit dalaman = 1,000 token, cth Starter 80 juta token = 80,000 unit).
- Rujukan undang-undang (§173) dan penyedia AI belum diubah — perlu semakan profesional/pengesahan penyedia.

### v4.1 — 18 Oktober 2026

Peluasan yang ditetapkan dalam spesifikasi:

- Shared Core dan security boundaries
- Penjelasan pemilikan data dan source of truth
- Blueprint Engine dan versioning
- B-Event & Rental dengan domain, workflow dan acceptance tests
- B-Brain Hub dan AI agent controls
- AI Token Wallet dengan reservation, settlement dan reconciliation
- Finance/payment lifecycle
- QA, release gates dan Definition of Done
- Open Decisions Register
- Cadangan struktur dokumen teknikal

### v4.2 — 18 Oktober 2026

Penambahan autonomi perniagaan dan personal AI:

- **B-Personal AI Assistant** (Sections 63-68): 9 jenis assistant (Owner, Executive, Sales, Marketing, Operations, Finance, HR, Customer Service, Industry-specific), memory architecture, task execution, proactive alerts
- **B-Business Understanding Engine** (Sections 69-84): 10-stage discovery lifecycle, Business Operating Profile, scorecard, approval gate, continuous learning
- **B-Autonomous Business Growth Engine** (Sections 85-97): End-to-end growth lifecycle, ICP engine, lead sourcing/enrichment/scoring, outreach orchestration (Level 0-3 autonomy), follow-up engine, sales conversation intelligence, proposal automation, deal closing, invoice & payment collection automation
- **B-Autonomous Operations Orchestrator** (Sections 98-102): Event-driven architecture, workflow builder, automation governance, human-in-the-loop
- **Autonomy, Safety & Control Plane** (Sections 103-106): Autonomy policy, risk classification, global kill switch, failure handling
- **AI Business Performance & Revenue Attribution** (Sections 107-109): KPI funnel (15 stages), revenue attribution rules, business outcome dashboard
- **Additional Data Model** (Sections 110-113): Business understanding entities, Personal AI entities, Growth engine entities, Automation entities
- **API and Tool Contracts** (Sections 114-116): Personal AI API, Business onboarding API, Growth automation API
- **Acceptance Tests** (Sections 117-122): Business understanding, Personal AI, Lead generation, Sales/closing, Payment/collection, Autonomous operations
- **Implementation Roadmap Addendum** (Section 124): Phase A-G build sequence
- **Final Product Principles** (Sections 124-125): 20 product rules, vision definition, principles flow

**Nota penting:**
- Payment verification TIDAK boleh berdasarkan screenshot, mesej pelanggan, atau browser redirect sahaja
- Autonomy levels (0-3) ditetapkan mengikut tenant, channel dan workflow
- Kill switch mesti berfungsi walaupun komponen AI mengalami masalah
- Semua automation mempunyai owner, version, audit trail dan compensation plan

### v4.3 — 18 Oktober 2026

Penambahan personal brand, sistem konten 5 level, dan continuous AI learning:

- **Personal Brand & Social Media Control** (Sections 126-131):
  - Setiap pengguna boleh bina personal brand unik dengan kawalan penuh
  - Perbezaan personal brand vs company brand jelas ditakrifkan
  - Social media connection melalui OAuth (bukan password storage)
  - Multi-account & team collaboration untuk multiple creators/sales
  - Dashboard personal brand dengan analytics dan engagement tracking

- **5-Level Content System** (Section 129):
  - **Level 0 — Raw Idea**: Bahan mentah (nota, idea, trend, customer feedback)
  - **Level 1 — Draft**: Konten siap produksi (copy, image, template)
  - **Level 2 — Review**: Semakan manusia dengan preview multi-platform
  - **Level 3 — Approved**: Diluluskan dengan immutable audit trail
  - **Level 4 — Published**: Terbit ke platform dengan metrics tracking
  - **Level 5 — Optimized**: Analisis prestasi → pembelajaran diekstrak
  - Permission matrix lengkap untuk setiap level
  - Workflow: Raw Idea → Draft → Review → Approved → Published → Optimized
  - AI capabilities mapped per level dengan autonomy yang berbeza
  - Brand safety filters, plagiarism check, cultural sensitivity (Malaysia context)

- **Continuous AI Learning Loop** (Sections 132-140):
  - 5 sumber pembelajaran: interaction logs, business outcomes, operational metrics, security data, user feedback
  - Processing pipeline: Collect → Clean → Analyze → Insight → Score → Recommend → Review → Implement → Validate → Update
  - 4 kategori cadangan kepada Super Admin: Model/Agent, Workflow/Automation, Product/Feature, Business Intelligence
  - Super Admin Dashboard (AI Learning Center): health overview, recommendation queue, insights, experiments, knowledge base
  - Guardrails: no auto model updates, rollback <15 minit, A/B testing required, impact assessment, audit trail, tenant isolation
  - 5 learning cycle frequencies: real-time, daily, weekly, monthly, quarterly
  - Self-evaluation framework: weekly, cross-agent monthly, quarterly architecture review
  - End-to-end example flow: pattern detection → recommendation → approval → A/B test → validated learning

- **Additional Data Model** (Sections 141-143): Personal brand entities, content system entities, learning system entities
- **API Contracts** (Sections 144-145): Personal Brand API, AI Learning API
- **Acceptance Tests** (Sections 146-148): Personal brand tests, 5-level content tests, continuous learning tests
- **Implementation Roadmap** (Section 150): Phase B1-B4 (Brand), Phase L1-L3 (Learning), Phase Integrated (Combined)
- **Updated Final Product Principles** (Sections 150-151): 10 additional rules (total 30), updated vision statement

**Ciri-ciri utama v4.3:**
- Personal brand empowerment: setiap pengguna ada kuasa penuh atas media sosial mereka
- 5-level content system: kualiti terjamin dari idea hingga optimization
- Continuous learning: AI BIJAN lebih baik setiap hari dari pembelajaran sebenar
- Super Admin control: semua perubahan AI melalui review dan approval
- Tenant-safe learning: data tidak bocor melalui insight atau pattern recognition

### v4.4 — 18 Oktober 2026

Penambahan AI Reseller Platform dan Admin HQ:

- **Seksyen 33a — AI Reseller Platform** (Sections 33a.1–33a.10):
  - Konsep sistem AI Bijan v2.3 sebagai AI reseller platform
  - Admin HQ dengan 7 fungsi kawalan (pricing, models, tokens, customers, revenue, white-label, analytics)
  - Gateway architecture 3-layer (Super Admin → AI Bijan Gateway → serveras.click)
  - Katalog model dual identity (upstream ID vs customer-facing name)
  - Token & Bil system dengan 8 konsep, 5 package pricing table, wallet transaction flows
  - Pelanggan rebranded — apa tenant CAN/CANNOT see
  - Provider integration — serveras.click endpoints, request mapping JSON, response filtering, 10 error codes
  - Safety & Control Plane — 6 Super Admin actions
  - Reconciliation 3-system flow + 5 rules
  - 12 acceptance tests untuk AI reseller operations

- **Renombak section numbering**: Sections 37–173 shifted +1 selepas insertion section 33a
- **Model naming convention**: `gpt-6-astra` untuk pelanggan, `asai/gpt-6-astra` internal sahaja

**Ciri-ciri utama v4.4:**
- AI reseller capability: Super Admin kontrol supply chain, models, pricing, tokens, customers, profits
- Provider abstraction: pelanggan tak pernah nampak API keys atau upstream model IDs
- Serveras.click integration lengkap dengan error handling standardization

### v4.5 — 18 Oktober 2026

Penambahan Platform Business, Brand Management, Multi-Tier Admin Hierarchy, dan Subscription Engine:

- **Seksyen 4a — Platform Business & Brand Management** (Sections 4a.1–4a.5):
  - Konsep platform owner (Bijan Holdings) berbeza daripada tenant customer
  - Platform Business Profile (SSM, tax ID, bank account, invoice settings)
  - Brand Portfolio Structure (AI BIJAN → B-Site, B-Link, dll + B-Solutions, B-Lab)
  - Domain Configuration (main domain, subdomain pattern, custom domain rules)
  - Database Schema (5 jadual: `platform_profiles`, `brand_portfolio`, `brand_modules`, `platform_domains`, `tenant_brand_customization`)

- **Seksyen 4b — Multi-Tier Admin Hierarchy** (Sections 4b.1–4b.9):
  - Hierarki 4 level admin: Master Admin → Super Admin → White-label Admin → Pelanggan Admin
  - Permission Matrix lengkap (23 fungsi × 4 admin levels)
  - Database Schema (7 jadual: `admin_roles`, `admin_role_assignments`, `white_label_configs`, `wl_tenant_allocations`, `wl_pricing_overrides`, `revenue_shares`, `wl_audit_logs`)
  - White-label Admin Features (Super Admin controls vs WL Admin choices)
  - Tenant Migration Rules (5 scenario: leave, revoke, payment failed, request migration, acquisition)
  - Admin Panel UI simulations (Super Admin WL Setup view + WL Admin Dashboard view)
  - 12 acceptance tests untuk admin hierarchy
  - 8 security rules untuk multi-tier admin

- **Seksyen 6a — Subscription & Monetization Engine** (Sections 6a.1–6a.9):
  - Konsep: semua harga dikawal melalui Admin Panel tanpa hardcode
  - Super Admin Pricing Management Features (Pricing Plan Editor, pakej SaaS, AI token packages)
  - Contoh pakej SaaS (4 tier: Starter RM49, Business RM149, Professional RM349, Enterprise RM799)
  - Annual pricing: 2 bulan percuma
  - AI Credits & Top-up Packages (5 tier: 80M–2.25B units)
  - Database Schema (12 jadual lengkap untuk subscription management)
  - Peraturan sistem pricing (price versioning, overage, grace period, audit trail)
  - Admin Panel UI simulation (Pricing Plan Editor form)
  - Customer-facing plan preview
  - 10 API endpoints untuk pricing & entitlement
  - 12 acceptance tests untuk subscription engine
  - Pricing audit trail dengan JSON example

- **Section numbering update**: Ringkasan naik ke 178 Section (0–177)

**Ciri-ciri utama v4.5:**
- Multi-tier reseller model: platform boleh scale melalui agency/reseller partnership
- White-label capability: partner boleh jual AI BIJAN dengan branding mereka sendiri
- Revenue sharing: auto-split antara platform dan white-label admin
- Subscription engine: semua pricing dinamik, tiada hardcode, price versioning untuk tenant protection
- Platform business setup: SSM, brand portfolio, domain management dalam satu modul

**Nota:** Changelog ini merekodkan peluasan spesifikasi, bukan pengesahan bahawa semua fungsi telah dibangunkan.

---

# FINAL PRODUCT RULE

**AI BIJAN perlu menyeluruh dalam peta produk, tetapi selektif dalam apa yang dibangunkan dan diaktifkan pada setiap release.**

Jangan cuba melancarkan semua industri serentak. Bina Shared Core yang selamat, pilih beberapa workflow yang mempunyai permintaan sebenar, lengkapkan setiap workflow dari mula hingga akhir, ukur kos dan kebolehpercayaan, kemudian kembangkan katalog industri.

Setiap modul mesti mempunyai data ownership, permission, workflow, error recovery, audit trail, tests dan status sokongan yang jelas.

**Prinsip terakhir: comprehensive product map, complete vertical slices, controlled AI autonomy, verified production readiness, empowered personal brands, and continuous intelligent evolution.**

---

# PART XVI — B-PERSONAL AI ASSISTANT

## 64. Visi Personal AI Assistant
B-Personal AI Assistant ialah pembantu AI peribadi untuk setiap pemilik bisnes, pengurus, pekerja dan pengguna yang dibenarkan.

Pembantu ini bukan sekadar chatbot yang menjawab soalan. Ia bertindak sebagai ruang kerja pintar yang memahami konteks perniagaan, membantu membuat keputusan, merancang tindakan, menjalankan workflow yang dibenarkan dan memantau hasil.

Matlamatnya adalah supaya pelanggan boleh berkata:

- "Fahami bisnes saya dan sediakan sistem yang sesuai."
- "Cari pelanggan baharu untuk servis saya."
- "Semak semua lead yang belum dihubungi."
- "Sediakan kempen pemasaran minggu ini."
- "Follow up quotation yang belum dijawab."
- "Semak invois yang masih belum dibayar."
- "Tunjukkan kenapa jualan bulan ini menurun."
- "Rancang operasi untuk mencapai sasaran jualan."
- "Uruskan kerja rutin yang boleh diautomasikan."
- "Beritahu saya perkara yang memerlukan keputusan saya sahaja."

AI perlu menyelaraskan permintaan dengan data sebenar, permission, modul aktif, bajet penggunaan AI, polisi organisasi dan hasil yang boleh diukur.

## 65. Jenis Personal AI Assistant

### 65.1 Owner AI Assistant
Untuk pemilik atau pengarah syarikat.

Fungsi: Ringkasan keadaan bisnes, sasaran jualan dan prestasi, cash-flow overview, lead dan peluang jualan, outstanding invoices, operasi yang lewat, risiko perniagaan, keperluan tenaga kerja dan aset, cadangan tindakan, kelulusan tindakan yang memerlukan pemilik.

### 65.2 Executive AI Assistant
Untuk pengurusan kanan.

Fungsi: Executive briefing, KPI monitoring, meeting preparation, decision summaries, department performance, risk and issue escalation, strategic planning support.

### 65.3 Sales AI Assistant
Fungsi: Lead qualification, customer research menggunakan sumber yang dibenarkan, CRM updates, follow-up planning, sales message drafting, quotation preparation, objection handling suggestions, pipeline monitoring, closing checklist, collection reminders.

### 65.4 Marketing AI Assistant
Fungsi: Campaign planning, audience segmentation, content generation, SEO planning, landing page drafts, marketing calendar, campaign measurement, lead attribution, budget monitoring, recommendations based on performance.

### 65.5 Operations AI Assistant
Fungsi: Task coordination, workflow monitoring, scheduling assistance, inventory alerts, vendor coordination, service delivery tracking, exception management, standard operating procedure assistance.

### 65.6 Finance AI Assistant
Fungsi: Invoice summaries, receivables monitoring, payment matching suggestions, expense categorization suggestions, cash-flow forecasts, budget variance analysis, reconciliation exceptions, finance report preparation.

**Perhatian:** AI tidak boleh memuktamadkan jurnal kewangan, meluluskan refund atau memindahkan wang tanpa permission dan approval yang sesuai.

### 65.7 HR AI Assistant
Fungsi: Onboarding checklists, training plans, shift planning assistance, leave workflow support, policy Q&A, skills and staffing summaries.

**Perhatian:** Keputusan pekerjaan berimpak tinggi tidak boleh dibuat berdasarkan cadangan AI semata-mata.

### 65.8 Customer Service AI Assistant
Fungsi: Answer approved FAQs, retrieve authorized customer/order status, draft replies, classify tickets, route cases, escalate complaints, summarize conversation history.

### 65.9 Industry-Specific Assistant
Setiap industry blueprint boleh mendaftarkan assistant khusus, contohnya:

- Event Planner AI
- Retail Operations AI
- F&B Operations AI
- Fleet Operations AI
- Salon Booking AI
- Construction Project AI
- Property Management AI
- Professional Services AI

Assistant khusus menggunakan domain knowledge, tool, workflow dan permission modul masing-masing.

## 66. Personal AI workspace
Setiap pengguna boleh mempunyai ruang AI dengan:

- Conversation history, saved tasks, reminders, personal preferences
- Work context, approved knowledge sources, connected modules
- Pending approvals, action history
- Usage and token balance, agent activity, automation subscriptions

Kandungan peribadi tidak secara automatik menjadi pengetahuan seluruh organisasi. Pengguna perlu memahami perbezaan antara personal context, shared company knowledge dan rekod rasmi.

## 67. Memory architecture
AI BIJAN perlu membezakan jenis memori.

| Jenis | Contoh | Akses |
|-------|--------|-------|
| Personal preferences | Bahasa pilihan, format laporan | Pengguna berkenaan |
| Work context | Tugasan semasa, projek yang ditugaskan | Mengikut permission |
| Business profile | Produk, harga, sasaran syarikat | Pengguna tenant yang dibenarkan |
| Operational records | Orders, invoices, tasks | Modul sumber dan permission |
| Conversation history | Perbualan terdahulu | Mengikut access policy |
| Approved knowledge | SOP, dokumen rasmi, polisi | Mengikut classification |
| Derived insights | Trend jualan, risiko, cadangan | Mengikut data source access |

### 67.1 Memory rules

- Simpan hanya maklumat yang mempunyai tujuan jelas.
- Sediakan fungsi review, correction dan deletion mengikut polisi.
- Jangan jadikan andaian AI sebagai fakta yang disahkan.
- Rekod sumber dan masa untuk business facts yang penting.
- Hormati permission asal apabila mencari semula maklumat.
- Jangan gunakan data tenant lain untuk menjawab soalan pelanggan.
- Jangan dedahkan data peribadi kepada pengguna yang tidak dibenarkan.
- Bezakan fakta, inferens, ramalan dan cadangan.
- Data rasmi mesti dibaca daripada source of truth, bukan semata-mata memori AI.

## 68. Personal AI task execution
Setiap tindakan AI perlu mempunyai:

`task_id`, `tenant_id`, `requested_by`, `agent_id`, `objective`, `input_reference`, `allowed_tools`, `risk_level`, `approval_requirement`, `budget_limit`, `deadline`, `status`, `result_reference`, `error_details`, `created_at`, `completed_at`

Status yang disyorkan: `Draft → Planned → Awaiting Approval → Queued → Running → Completed / Partially Completed / Failed / Cancelled`

AI tidak boleh mendakwa tugas telah selesai sebelum sistem sumber mengesahkan hasilnya.

## 69. Proactive Personal AI
AI boleh memberikan amaran dan cadangan secara proaktif apabila mempunyai data yang sah.

Contoh amaran:

- Lead penting tidak dihubungi selepas tempoh yang ditetapkan.
- Quotation belum mendapat jawapan.
- Invoice hampir atau telah melepasi tarikh matang.
- Stok dijangka tidak mencukupi.
- Event mempunyai task kritikal yang belum selesai.
- Campaign melebihi bajet.
- Penggunaan AI hampir mencapai had.
- Integrasi payment mengalami masalah.
- Sasaran jualan ketinggalan daripada trajectory.

Setiap alert perlu menunjukkan:

1. Apa yang berlaku.
2. Data atau rekod yang menjadi asas.
3. Tahap kepastian.
4. Kesan yang mungkin.
5. Tindakan yang dicadangkan.
6. Sama ada tindakan boleh dibuat automatik.
7. Cara pengguna meluluskan, menolak atau membetulkan cadangan.

---

# PART XVII — B-BUSINESS UNDERSTANDING ENGINE

## 70. Objektif
B-Business Understanding Engine ialah proses onboarding mendalam untuk memahami cara bisnes pelanggan benar-benar beroperasi sebelum AI BIJAN membuat konfigurasi atau mencadangkan automasi.

Matlamatnya bukan sekadar meminta pelanggan memilih industri. Sistem perlu membina **Business Operating Profile** yang boleh digunakan untuk menyediakan modul, data, workflow, KPI, AI assistants dan pelan automasi yang sesuai.

Sistem mesti mengesan maklumat yang belum diketahui dan bertanya soalan susulan apabila maklumat itu mempengaruhi konfigurasi atau risiko.

## 71. Business discovery lifecycle

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

## 72. Tahap 1 — Company identity
Kumpulkan maklumat yang diperlukan sahaja:

- Nama syarikat, nama jenama, jenis entiti perniagaan
- Negara dan kawasan operasi, aktiviti utama dan sekunder
- Bilangan cawangan, saiz pasukan, bahasa operasi
- Mata wang, waktu operasi, saluran pelanggan
- Peringkat perniagaan, model operasi, keperluan industri khusus

Maklumat pendaftaran atau pengenalan yang sensitif hanya boleh diminta apabila diperlukan untuk fungsi tertentu dan mempunyai kawalan akses yang sesuai.

## 73. Tahap 2 — Business model discovery
AI perlu mengenal pasti cara pelanggan menjana hasil.

Soalan yang boleh ditanya secara berperingkat:

- Apakah yang dijual? Siapakah pelanggan utama?
- Adakah hasil datang daripada jualan sekali, langganan, komisen, projek atau gabungan?
- Bagaimanakah harga ditetapkan? Adakah deposit diperlukan?
- Bagaimanakah pesanan diterima? Apakah syarat pembatalan?
- Apakah kos terbesar? Apakah faktor yang mengehadkan kapasiti?
- Bagaimanakah pelanggan mengetahui kewujudan syarikat?
- Apakah faktor utama yang membuatkan pelanggan membeli?

AI perlu menyesuaikan soalan mengikut jawapan terdahulu dan tidak meminta maklumat yang sudah disahkan tanpa sebab.

## 74. Tahap 3 — Products and services understanding
Bina katalog awal yang mengandungi:

- Product/service name, category, description, target customer
- Selling price, cost components, delivery method, fulfilment time
- Capacity, availability, optional add-ons
- Warranty/guarantee jika berkaitan, return/cancellation terms
- Required equipment or staff, tax configuration apabila relevan

AI boleh mencadangkan kategori dan struktur, tetapi pelanggan perlu mengesahkan harga, kos, janji perkhidmatan dan syarat jualan yang penting.

## 75. Tahap 4 — Customer understanding
Bina profil segmen pelanggan:

- B2B, B2C atau kedua-duanya; customer segments; service areas
- Typical buyer, buyer versus end user
- Buying triggers, common objections, typical sales cycle
- Repeat purchase pattern, retention risks
- Preferred communication channels, customer consent dan communication preferences

AI tidak boleh membuat andaian sensitif atau membuat profil diskriminasi. Segmentasi perlu berasaskan tujuan perniagaan yang sah dan data yang dibenarkan.

## 76. Tahap 5 — Sales process discovery
Petakan perjalanan jualan sebenar:

1. Lead source → 2. Lead capture → 3. Qualification → 4. First contact
5. Discovery → 6. Proposal/quotation → 7. Negotiation → 8. Approval
9. Contract/order → 10. Deposit or payment → 11. Delivery/fulfilment
12. Final invoice → 13. Payment collection → 14. Customer success
15. Repeat sales/referral

Bagi setiap peringkat, rekodkan: owner, required information, entry condition, exit condition, SLA, templates, automation eligibility, approval rules, common failure reasons, conversion metric.

## 77. Tahap 6 — Operations understanding
AI perlu memahami bagaimana produk atau perkhidmatan dihantar kepada pelanggan.

Aspek operasi: work intake, scheduling, staff assignment, resource allocation, procurement, inventory, quality control, delivery, customer confirmation, incident handling, returns/refunds, completion and handover.

Sistem perlu mengesan dependency yang penting. Contohnya, jualan tidak boleh dijanjikan jika kapasiti, stok, tenaga kerja atau vendor tidak tersedia.

## 78. Tahap 7 — Team and organization
Kumpulkan struktur tanggungjawab:

Owner, management, sales, marketing, operations, finance, customer service, procurement, fulfilment, external vendors, approval authority.

Jangan memberikan semua permission kepada pemilik akaun secara automatik untuk setiap pekerja. Sediakan role template dan minta pengesahan.

## 79. Tahap 8 — Finance and unit economics
Sistem perlu memahami:

Revenue streams, direct costs, fixed costs, variable costs, staff costs, supplier costs, payment terms, deposits, receivables, payables, refunds, discounts, gross margin, break-even assumptions, cash-flow timing.

AI perlu membezakan data sebenar daripada anggaran pelanggan. Jika kos tidak diketahui, sistem perlu menyatakan bahawa margin tidak dapat dikira dengan yakin dan meminta data tambahan.

## 80. Tahap 9 — Current tools and data
Kenal pasti: existing CRM, accounting software, spreadsheets, POS, website, e-commerce platform, communication tools, payment providers, calendar systems, inventory systems, file repositories, existing APIs.

Bagi setiap integrasi, rekodkan: access status, data scope, owner, sync direction, sync frequency, recovery policy.

**Jangan meminta pelanggan menyerahkan kata laluan akaun luaran melalui chat.** Gunakan proses OAuth, credential vault atau kaedah sambungan selamat yang sesuai.

## 81. Tahap 10 — Goals, constraints and risks
Sistem perlu mendapatkan sasaran yang boleh diukur:

Revenue target, lead target, conversion target, average order value, repeat purchase rate, delivery time, customer satisfaction, operating cost, staff capacity, cash collection, error/rework rate.

Rekodkan juga: budget, team capacity, technical limitations, legal/industry constraints, required approvals, data sensitivity, risk tolerance, launch deadline.

## 82. Business Understanding Scorecard
Sediakan scorecard yang menerangkan kelengkapan maklumat, bukan memberi gambaran palsu bahawa AI memahami semuanya.

| Dimensi | Soalan Penilaian |
|---------|------------------|
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

Gunakan status: `Unknown`, `Partially Understood`, `Customer Confirmed`, `System Verified`, `Needs Review`.

Jangan gabungkan semua dimensi menjadi satu skor tanpa menjelaskan kaedah pengiraan.

## 83. Business Operating Profile
Selepas discovery, sistem menghasilkan profil berstruktur:

- Business identity, industry blueprint, revenue model
- Product/service catalogue, customer segments, sales pipeline
- Operations workflows, team and permission proposal
- Financial assumptions, KPI definitions, integration map
- Automation opportunities, AI assistant recommendations
- Data migration plan, risks and unknowns
- Required customer decisions, estimated implementation effort
- Proposed rollout sequence

Setiap item perlu mempunyai sumber, status pengesahan dan tarikh kemas kini apabila sesuai.

## 84. Initial setup plan
Daripada Business Operating Profile, AI menyediakan pelan setup:

1. Modules to activate
2. Roles and permissions
3. Organization and branch setup
4. Products and services import
5. Customer import
6. Sales pipeline setup
7. Workflow configuration
8. Document templates
9. Payment/invoicing configuration
10. Notifications
11. Integrations
12. AI assistant setup
13. Automation policies
14. KPI dashboards
15. Acceptance tests
16. Training and go-live checklist

### 84.1 Approval gate
AI boleh menyediakan cadangan dan draft konfigurasi. Sebelum perubahan yang berimpak tinggi diaktifkan, pelanggan yang berkuasa perlu mengesahkan: harga, margin dan kos, kontrak, payment configuration, customer messaging, data migration, permission, financial rules, workflow kritikal.

## 85. Continuous business understanding
Business Operating Profile bukan dokumen statik.

Sistem boleh mengemas kini pemahaman berdasarkan: perubahan katalog, jualan sebenar, customer conversion, operasi, inventory, kos, pembayaran, customer feedback, perubahan organisasi, arahan pengguna.

Namun, sistem mesti membezakan: customer-confirmed fact, imported record, system-verified fact, AI inference, forecast, proposed change. Perubahan penting mesti menunjukkan perbezaan sebelum/selepas dan membenarkan review.

---

# PART XVIII — B-AUTONOMOUS BUSINESS GROWTH ENGINE

## 86. Visi
B-Autonomous Business Growth Engine membantu pelanggan membina saluran pertumbuhan yang menyambungkan marketing, lead generation, sales, delivery, invoicing dan payment collection.

Objektifnya adalah mengurangkan kerja manual dari mengenal pasti prospek hingga merekod pembayaran. Ia tidak menjamin penjualan atau penerimaan wang. Ia mengurus proses, meningkatkan konsistensi dan mengukur hasil.

## 87. End-to-end business growth lifecycle

```
Business Understanding
        ↓
Define Ideal Customer Profile
        ↓
Define Offer & Value Proposition
        ↓
Build Compliant Prospect Strategy
        ↓
Find / Capture Leads
        ↓
Validate & Deduplicate
        ↓
Score & Qualify
        ↓
Create CRM Record
        ↓
Personalized Outreach
        ↓
Conversation & Discovery
        ↓
Proposal / Quotation
        ↓
Negotiation & Approval
        ↓
Contract / Order Confirmation
        ↓
Invoice / Deposit Request
        ↓
Payment Verification
        ↓
Delivery / Fulfilment
        ↓
Final Invoice / Collection
        ↓
Reconciliation
        ↓
Customer Success / Repeat Sales
        ↓
Performance Learning
```

Setiap peringkat mempunyai syarat masuk, syarat keluar, pemilik, status, retry policy dan audit.

## 88. Ideal Customer Profile Engine
Berdasarkan Business Operating Profile, AI membina Ideal Customer Profile (ICP).

ICP boleh merangkumi: industry/activity, company size, service geography, business needs, buying triggers, budget range jika diketahui secara sah, procurement process, expected sales cycle, fit criteria, disqualifying criteria, data freshness requirements.

ICP mesti diluluskan atau disemak oleh pelanggan. Ia tidak boleh bergantung kepada data sensitif yang tidak sesuai.

## 89. Lead discovery and sourcing
Sistem boleh mendapatkan prospek daripada sumber yang sah dan dibenarkan:

- Website inquiry forms, customer referrals, existing CRM
- Opt-in marketing lists, authorized business directories
- Public business websites (tertakluk kepada syarat penggunaan dan peraturan berkaitan)
- Marketplace inquiries, partner integrations
- Event registrations dengan consent yang sesuai
- Inbound calls/messages yang diintegrasikan

### 89.1 Lead sourcing controls

- Record source and retrieval date
- Store evidence/source URL where permitted
- Deduplicate by suitable business identifiers
- Respect opt-out and suppression lists
- Respect data-use restrictions
- Validate contact data before outreach
- Apply rate limits
- Avoid unauthorized scraping or account access
- Do not invent contact details
- Do not treat publicly visible data as automatic permission for every marketing use

Jika sistem tidak mempunyai akses kepada sumber, ia mesti menyatakan perkara itu dan meminta sambungan yang diperlukan.

## 90. Lead enrichment and scoring
Setiap lead boleh mempunyai: company name, industry, location/coverage, website, business contact channel, source, need/fit evidence, qualification status, potential value estimate, confidence level, last verified time, next action, assigned owner.

### 90.1 Lead score
Skor boleh mengambil kira: ICP fit, need evidence, service area fit, budget/size fit apabila diketahui, engagement, timing, buying authority evidence, data quality.

Paparkan faktor yang menyumbang kepada skor. Jangan menunjukkan skor sebagai fakta objektif jika ia hanya model anggaran.

## 91. Outreach orchestration
AI boleh menyediakan: e-mel pengenalan, follow-up e-mel, WhatsApp message drafts melalui integrasi yang dibenarkan, sales call briefs, LinkedIn/social drafts mengikut channel policy, proposal cover messages, appointment booking messages, customer reactivation campaigns.

### 91.1 Autonomy levels

| Level | Penerangan | Polisi |
|-------|------------|--------|
| Level 0 — Suggest only | AI membuat cadangan dan draft. Manusia menghantar. | Semua tindakan memerlukan human send |
| Level 1 — Approve before send | AI menyediakan mesej dan sasaran. Manusia meluluskan. | Approval required per message batch |
| Level 2 — Policy-based automation | AI boleh menghantar mesej rutin kepada penerima yang dibenarkan berdasarkan consent, channel rules, rate limits dan template yang diluluskan. | Template approved, rate limited, consent verified |
| Level 3 — Controlled sales automation | AI boleh mengurus susulan dan penjadualan dalam skop yang telah diluluskan. Diskaun, kontrak dan komitmen kewangan kekal di bawah approval rules. | Budget limits, approval gates for financial actions |

Autonomy level perlu ditetapkan bagi setiap tenant, channel dan workflow.

## 92. Follow-up engine
Follow-up engine mengurus: due date, channel, template, personalization, maximum attempt count, minimum interval, business hours, opt-out, suppression, reply detection, stop conditions, escalation, next best action.

Hentikan follow-up automatik apabila: pelanggan meminta untuk berhenti, pelanggan membalas dan memerlukan perbualan manusia, lead disqualify, kontrak telah ditandatangani, pelanggan telah membayar, had percubaan dicapai, consent tidak tersedia, terdapat aduan atau risiko.

Jangan menghantar mesej tanpa henti kerana tiada jawapan.

## 93. Sales conversation intelligence
AI boleh: summarize conversation, identify customer questions, detect objections, suggest replies, extract next steps, update CRM fields with review where required, prepare meeting notes, create follow-up tasks, highlight missing information, prepare proposal inputs.

AI mesti membezakan apa yang pelanggan benar-benar berkata daripada tafsiran model.

## 94. Proposal and quotation automation
AI boleh menyediakan draft berdasarkan: product/service catalogue, approved price list, approved discounts, customer requirements, capacity and availability, scope of work, delivery terms, taxes/fees yang telah disahkan, contract templates.

Sebelum dihantar, semak: harga, kuantiti, skop, availability, tax treatment, payment terms, expiry, exclusions, approval authority.

Jangan biarkan AI mencipta harga atau syarat kontrak yang tidak diluluskan.

## 95. Deal closing assistant
Deal Closing Assistant membantu menjejak proses sehingga keputusan pelanggan direkodkan.

Fungsi: deal readiness checklist, missing information detection, stakeholder mapping, objection summary, proposal expiry alerts, negotiation draft suggestions, approval workflow, contract preparation, signature status tracking, order creation, deposit/invoice generation, handover to fulfilment.

AI boleh mencadangkan tindakan closing tetapi tidak boleh memalsukan persetujuan pelanggan atau menandatangani kontrak bagi pihak pelanggan tanpa mekanisme sah dan kuasa yang jelas.

## 96. Invoice and payment collection automation
Selepas deal disahkan, sistem boleh:

1. Verify order/contract state
2. Generate invoice draft
3. Validate customer and amount
4. Apply numbering and terms
5. Request approval where required
6. Issue invoice through approved channel
7. Monitor due date
8. Send permitted reminders
9. Receive payment-provider events
10. Verify payment status server-side
11. Match payment to invoice
12. Update receivables
13. Reconcile transaction
14. Notify relevant parties
15. Stop reminders after confirmed settlement

### 96.1 Duit masuk — verification rules
AI BIJAN **tidak boleh** menandakan wang diterima berdasarkan:

- ❌ Screenshot resit semata-mata
- ❌ Mesej pelanggan
- ❌ Browser redirect
- ❌ E-mel tidak disahkan
- ❌ Kenyataan AI sendiri

✅ Gunakan payment provider confirmation, bank/accounting integration yang sah atau proses pengesahan kewangan yang diluluskan.

Jika transaksi belum dapat dipastikan, gunakan status seperti `Pending Verification` atau `Reconciliation Required`.

### 96.2 Payment states
Contoh status: `Draft → Issued → Awaiting Payment → Pending Verification → Partially Paid → Paid → Reconciliation Required → Reconciled`

Status `Failed`, `Cancelled`, `Refund Pending` dan `Refunded` perlu disokong mengikut payment workflow. Status sebenar hendaklah diselaraskan dengan model kewangan dan payment provider.

## 97. Customer success and repeat revenue
Selepas jualan: onboarding customer, delivery status, satisfaction follow-up, support ticket creation, renewal reminders, reorder reminders, cross-sell suggestions, referral requests, churn-risk signals, customer lifetime value reporting.

AI hanya boleh menghantar mesej mengikut consent, channel policy dan had komunikasi pelanggan.

## 98. Growth experiments
AI boleh mencadangkan eksperimen: landing page variations, offer variations, email subject lines, audience segments, pricing hypotheses, follow-up intervals, channel mix, content topics.

Setiap eksperimen perlu mempunyai: hypothesis, target segment, success metric, budget, duration, sample/measurement plan, approval, result, decision.

Jangan membenarkan AI mengubah semua harga atau campaign serentak tanpa kawalan, kerana hasilnya sukar dinilai dan risiko perbelanjaan meningkat.

---

# PART XIX — B-AUTONOMOUS OPERATIONS ORCHESTRATOR

## 99. Tujuan
Orchestrator menyelaraskan tindakan antara modul supaya satu peristiwa boleh mencetuskan workflow merentas sistem.

Contoh workflow automatik:

- Lead baharu → CRM record → qualification task
- Lead qualified → appointment → sales brief
- Deal approved → order → invoice
- Payment verified → receipt → fulfilment workflow
- Inventory low → procurement suggestion
- Event confirmed → project tasks → vendor requests → rental reservation
- Invoice overdue → collection workflow
- Customer complaint → ticket → owner assignment
- AI wallet low → usage warning → optional top-up flow

## 100. Event-driven architecture
Setiap domain boleh menerbitkan event yang telah didokumenkan, contohnya:

`lead.created`, `lead.qualified`, `proposal.approved`, `deal.won`, `order.confirmed`, `invoice.issued`, `payment.verified`, `payment.reconciled`, `inventory.threshold_reached`, `booking.confirmed`, `event.readiness_failed`, `support.ticket_created`

Event sebenar mesti mempunyai schema version, event ID, tenant context, timestamp dan resource reference.

### 100.1 Event processing rules

- Idempotent consumers
- Retry with backoff
- Dead-letter handling
- Duplicate detection
- Event ordering strategy where necessary
- Monitoring and alerting
- Replay policy
- Data privacy controls
- Audit trail

Jangan bergantung pada satu queue message sahaja untuk menjadi rekod rasmi transaksi kewangan.

## 101. Workflow automation builder
Pelanggan boleh membina workflow menggunakan: trigger, conditions, actions, delays, approvals, branching, retry, stop conditions, notifications, error paths.

Contoh workflow:

```
Trigger: Lead created
    ↓
Validate required fields
    ↓
Check duplicate
    ↓
If duplicate → merge/review
    ↓
If new → score lead
    ↓
If qualified → create sales task
    ↓
If outreach permission exists → prepare/send approved message
    ↓
Wait for response
    ↓
If reply → assign conversation
    ↓
If no reply → evaluate follow-up policy
    ↓
Update CRM and report outcome
```

## 102. Automation governance
Setiap automation perlu mempunyai: owner, tenant scope, version, trigger, allowed actions, budget/usage limit, rate limit, retry policy, approval policy, data access scope, kill switch, monitoring, test mode, rollback atau compensation plan where possible.

Perubahan workflow tidak boleh terus menjejaskan semua pelanggan tanpa versioning dan migration policy.

## 103. Human-in-the-loop
Tindakan yang lazimnya memerlukan approval bergantung pada polisi tenant:

- Menghantar kontrak rasmi
- Memberikan diskaun melebihi had
- Mengubah harga yang diluluskan
- Membuat refund, mengeluarkan payout, memindahkan wang
- Memadam rekod penting
- Mengubah permission admin
- Menghantar kempen berskala besar
- Mengaktifkan integrasi yang mengakses data sensitif

Approval perlu merecord approver, timestamp, decision, reason dan versi data yang diluluskan.

---

# PART XX — AUTONOMY, SAFETY & CONTROL PLANE

## 104. Autonomy policy
AI BIJAN perlu mempunyai satu lapisan polisi yang menentukan tindakan yang boleh dibuat secara automatik.

Policy boleh berdasarkan: tenant, user role, module, agent, tool, action type, financial amount, data sensitivity, channel, risk classification, business hours, usage quota, approval requirements.

## 105. Risk classification

| Risiko | Contoh | Kawalan Minimum |
|--------|--------|-----------------|
| Low | Summarize report yang dibenarkan | Permission dan audit |
| Moderate | Create task atau draft | Validation dan log |
| High | Send external communication atau ubah booking kritikal | Policy, rate limits, approval mengikut kes |
| Critical | Refund, payout, financial commitment | Strong authorization, approval, idempotency, audit |

Klasifikasi sebenar perlu dinilai mengikut impak tindakan, bukan nama tool sahaja.

## 106. Global kill switch
Platform mesti boleh menghentikan: semua AI actions, satu provider, satu agent, satu tool, satu tenant automation, satu communication channel, satu payment integration.

Kill switch perlu berfungsi walaupun komponen AI sedang mengalami masalah. Ia mesti diuji dan dipantau.

## 107. Failure handling
Jika workflow gagal:

- Jangan tandakan berjaya tanpa pengesahan
- Simpan status dan error yang boleh disiasat
- Elakkan duplicate external actions
- Retry hanya untuk tindakan yang selamat diulang
- Sediakan manual recovery
- Alert pemilik proses untuk kegagalan kritikal
- Reconcile dengan source of truth
- Rekod sama ada transaksi perlu tindakan manusia

---

# PART XXI — AI BUSINESS PERFORMANCE & REVENUE ATTRIBUTION

## 108. KPI funnel
Ukur keseluruhan funnel:

1. Prospects discovered → 2. Prospects validated → 3. Leads created
4. Qualified leads → 5. Contacted leads → 6. Replies
7. Meetings booked → 8. Proposals issued → 9. Deals won
10. Orders confirmed → 11. Invoices issued → 12. Payments verified
13. Payments reconciled → 14. Fulfilment completed → 15. Repeat purchases

## 109. Revenue attribution
Bezakan: revenue influenced by AI, revenue attributed to a campaign, revenue recorded in the source system, invoices issued, payments verified, payments reconciled, gross profit, net contribution after relevant costs.

Jangan menganggap semua jualan yang berlaku selepas AI menghantar mesej sebagai hasil yang disebabkan oleh AI. Attribution method perlu didokumenkan dan dipaparkan bersama report.

## 110. Business outcome dashboard
Dashboard owner boleh menunjukkan: new prospects, lead conversion, pipeline value, proposal win rate, average deal size, sales cycle duration, invoice outstanding, collection rate, confirmed receipts, revenue by channel, campaign cost, cost per qualified lead, AI usage cost, contribution margin estimate, customer retention, automation failure rate.

Setiap KPI perlu mempunyai definisi, formula, source of truth dan freshness indicator.

---

# PART XXII — ADDITIONAL DATA MODEL

## 111. Business Understanding entities
Cadangan entiti: `business_profiles`, `business_activities`, `business_discovery_sessions`, `business_discovery_answers`, `business_facts`, `business_fact_sources`, `business_assumptions`, `business_unknowns`, `business_goals`, `business_kpi_definitions`, `business_operating_profiles`, `business_blueprint_recommendations`, `business_setup_plans`, `business_setup_approvals`, `business_profile_versions`

Setiap fact atau assumption yang penting perlu mempunyai status pengesahan.

## 112. Personal AI entities
Cadangan entiti: `ai_assistants`, `ai_assistant_profiles`, `ai_conversations`, `ai_messages`, `ai_memory_items`, `ai_memory_sources`, `ai_tasks`, `ai_task_steps`, `ai_task_results`, `ai_agent_runs`, `ai_tool_calls`, `ai_action_approvals`, `ai_action_audit`, `ai_user_preferences`

Simpan maklumat minimum yang diperlukan dan gunakan retention policy yang jelas.

## 113. Growth engine entities
Cadangan entiti: `growth_campaigns`, `growth_audiences`, `growth_experiments`, `prospect_sources`, `prospects`, `prospect_enrichment_records`, `lead_scores`, `crm_leads`, `crm_activities`, `sales_opportunities`, `sales_stage_history`, `sales_followup_plans`, `sales_message_drafts`, `sales_outreach_events`, `sales_proposals`, `sales_approvals`, `deal_records`, `deal_handoffs`, `collection_workflows`, `revenue_attribution_records`

Jangan menyimpan data yang tidak mempunyai tujuan operasi yang sah.

## 114. Automation entities
Cadangan entiti: `automation_definitions`, `automation_versions`, `automation_triggers`, `automation_runs`, `automation_steps`, `automation_approvals`, `automation_failures`, `automation_retries`, `automation_dead_letters`, `automation_policy_bindings`

Struktur akhir mesti diselaraskan dengan event bus, queue dan database yang dipilih.

---

# PART XXIII — API AND TOOL CONTRACTS

## 115. Personal AI API capabilities
Contoh endpoint konseptual:

- `POST /v1/ai/conversations`
- `POST /v1/ai/conversations/{id}/messages`
- `GET /v1/ai/conversations/{id}`
- `POST /v1/ai/tasks`
- `GET /v1/ai/tasks/{id}`
- `POST /v1/ai/tasks/{id}/cancel`
- `GET /v1/ai/approvals`
- `POST /v1/ai/approvals/{id}/decision`
- `GET /v1/ai/usage`

Nama endpoint ini ialah cadangan. Ia perlu disesuaikan dengan routing dan versioning sebenar.

## 116. Business onboarding API capabilities
Contoh endpoint konseptual:

- `POST /v1/business-discovery/sessions`
- `POST /v1/business-discovery/sessions/{id}/answers`
- `GET /v1/business-discovery/sessions/{id}`
- `POST /v1/business-discovery/sessions/{id}/generate-profile`
- `GET /v1/business-operating-profile`
- `POST /v1/business-operating-profile/review`
- `POST /v1/business-setup-plans`
- `POST /v1/business-setup-plans/{id}/approve`
- `POST /v1/business-setup-plans/{id}/execute`

## 117. Growth automation API capabilities
Contoh endpoint konseptual:

- `POST /v1/growth/campaigns`
- `POST /v1/growth/prospects/import`
- `POST /v1/growth/leads/qualify`
- `GET /v1/growth/pipeline`
- `POST /v1/growth/outreach/drafts`
- `POST /v1/growth/outreach/{id}/approve`
- `POST /v1/growth/proposals`
- `POST /v1/growth/deals/{id}/close`
- `GET /v1/growth/collections`
- `GET /v1/growth/performance`

Semua endpoint perlu authentication, tenant scope, permission checks, validation, audit dan rate limits.

---

# PART XXIV — ACCEPTANCE TESTS FOR PERSONAL AI & AUTONOMOUS BUSINESS

## 118. Business understanding tests

- AI meminta maklumat yang diperlukan sebelum mencadangkan konfigurasi kritikal
- AI tidak menandakan andaian sebagai fakta disahkan
- Business profile mempunyai source dan status verification
- Pelanggan boleh membetulkan maklumat
- Perubahan penting mempunyai version history
- Cadangan blueprint menunjukkan sebab pemilihan modul
- Sistem boleh mengenal pasti maklumat yang masih belum diketahui
- Modul yang tidak disokong ditandakan dengan jelas
- Import data mempunyai validation report
- Setup tidak diaktifkan secara senyap untuk tindakan berisiko

## 119. Personal AI tests

- AI hanya membaca data yang dibenarkan
- Personal memory tidak bocor kepada pengguna lain
- AI menunjukkan sumber bagi fakta operasi penting
- AI membezakan fakta, inferens dan ramalan
- Task status sepadan dengan status sistem sebenar
- Pengguna boleh membatalkan task yang belum menyelesaikan tindakan luaran
- Tool call memeriksa permission semula
- AI token usage direkod
- Approval mempunyai actor dan timestamp
- Failure tidak dipaparkan sebagai success

## 120. Lead generation tests

- Lead source direkod
- Duplicate leads dikesan
- Contact data yang tidak sah ditandakan
- Lead scoring menunjukkan faktor utama
- Opt-out dihormati
- Suppression list dikuatkuasakan
- Sistem tidak menghantar mesej tanpa permission atau asas yang sesuai
- Rate limits berfungsi
- Reply detection menghentikan sequence yang tidak lagi sesuai
- Lead yang tidak qualified tidak terus menerima follow-up tanpa had

## 121. Sales and closing tests

- Harga datang daripada price list yang diluluskan
- Diskaun melebihi had memerlukan approval
- Proposal versioning berfungsi
- Contract acceptance mempunyai bukti yang sesuai
- AI tidak mencipta persetujuan pelanggan
- Deal status hanya berubah berdasarkan syarat yang sah
- Order tidak diwujudkan dua kali akibat retry
- Handover kepada operations membawa data yang diperlukan

## 122. Payment and collection tests

- Invoice amount disahkan di server
- Payment callback diverifikasi
- Duplicate callback tidak menggandakan penerimaan
- Browser redirect tidak dianggap bukti pembayaran
- Screenshot tidak secara automatik mengesahkan payment
- Partial payment dikendalikan
- Overpayment dan unmatched payment mempunyai workflow
- Collection reminder berhenti selepas settlement disahkan
- Refund memerlukan authorization
- Reconciliation mismatch menghasilkan alert
- Payment provider outage tidak menyebabkan data transaksi hilang

## 123. Autonomous operations tests

- Automation mempunyai owner dan version
- Kill switch menghentikan tindakan baharu
- Retry tidak menggandakan tindakan luaran
- Dead-letter failure boleh disiasat
- Approval policy diuji
- Tenant isolation diuji pada setiap tool
- Budget limits dikuatkuasakan
- Kegagalan integrasi menghasilkan status yang boleh dipulihkan
- Event-driven workflow tidak bergantung pada satu notification sebagai rekod rasmi
- Semua tindakan sensitif mempunyai audit trail

---

# PART XXV — IMPLEMENTATION ROADMAP ADDENDUM

## 124. Recommended build sequence

### Phase A — Business Understanding MVP
Bangunkan: discovery wizard, Business Operating Profile, unknowns and assumptions register, industry/module recommendation, setup plan, human review, profile versioning.

**Exit gate:** AI boleh menghasilkan profil yang boleh disemak dan pelanggan boleh membetulkannya sebelum setup.

### Phase B — Personal AI Foundation
Bangunkan: personal workspace, conversation history, authorized knowledge retrieval, task management, agent/tool registry, permission checks, usage metering, approval queue.

**Exit gate:** AI boleh membantu dengan tugasan berisiko rendah dan tidak boleh melangkaui permission.

### Phase C — CRM and Lead Pipeline
Bangunkan: prospect intake, CRM, deduplication, lead qualification, sales activities, pipeline dashboard, follow-up planning.

**Exit gate:** lead boleh dijejak dari sumber sehingga keputusan jualan.

### Phase D — Controlled Outreach
Bangunkan: approved templates, channel integrations, consent/suppression management, scheduling, rate limits, reply handling, campaign metrics.

**Exit gate:** mesej hanya dihantar melalui saluran yang disambungkan dan polisi yang diluluskan.

### Phase E — Deal to Payment
Bangunkan: proposal, approval, contract/order workflow, invoice, payment verification, collection reminders, reconciliation, fulfilment handoff.

**Exit gate:** deal, invoice dan pembayaran boleh dijejak tanpa status palsu atau duplicate transactions.

### Phase F — Autonomous Operations
Bangunkan: workflow builder, event-driven orchestration, cross-module automations, failure recovery, monitoring, global dan tenant kill switches, automation analytics.

**Exit gate:** workflow boleh dihentikan, dipulihkan dan diaudit.

### Phase G — Industry Expansion
Tambah industry assistants dan blueprint berdasarkan keperluan pelanggan sebenar.

**Exit gate:** setiap industry pack mempunyai workflow lengkap, permission matrix, data model, acceptance suite dan known limitations.

---

# PART XXVI — FINAL PRODUCT PRINCIPLES

## 125. Product rules

1. AI BIJAN perlu memahami bisnes sebelum mencadangkan setup yang kompleks.
2. Pemahaman bisnes mesti mempunyai sumber, status dan version history.
3. Personal AI perlu mempunyai permission yang sama atau lebih ketat daripada pengguna yang diwakilinya.
4. Autonomi ditetapkan mengikut risiko, bukan dihidupkan tanpa batas.
5. Lead generation mesti menggunakan sumber dan saluran yang dibenarkan.
6. Outreach mesti menghormati consent, opt-out, rate limits dan channel policies.
7. AI boleh membantu closing tetapi tidak boleh memalsukan persetujuan.
8. Invoice yang dikeluarkan bukan bukti wang diterima.
9. Payment hanya dianggap diterima selepas verification yang sah.
10. Payment diterima dan payment reconciled ialah status yang berbeza.
11. Kegagalan automation mesti kelihatan dan boleh dipulihkan.
12. AI usage, provider cost dan customer charges mesti boleh diaudit.
13. Setiap tindakan berisiko mempunyai approval rules yang jelas.
14. Semua tenant mesti terasing.
15. Semua modul mempunyai source of truth.
16. Setiap KPI mempunyai formula dan sumber.
17. AI tidak boleh menganggap output yang meyakinkan sebagai bukti kebenaran.
18. Pelanggan perlu boleh melihat dan membetulkan pemahaman AI.
19. Tiada modul boleh dilabel production-ready tanpa bukti ujian dan operasi.
20. Kejayaan AI BIJAN diukur melalui hasil yang disahkan, bukan bilangan automasi yang dijalankan.

## 126. Definisi visi akhir
AI BIJAN disasarkan menjadi platform operasi perniagaan yang boleh:

- memahami model bisnes pelanggan
- menyediakan konfigurasi awal yang sesuai
- menghubungkan operasi dan data
- membantu mencari prospek
- mengurus CRM dan follow-up
- menyediakan proposal dan membantu proses closing
- menyokong invoicing dan collection
- mengesahkan serta merekonsiliasi pembayaran melalui integrasi yang sah
- menyelaras operasi selepas jualan
- memantau prestasi
- mengenal pasti peluang penambahbaikan
- menjalankan tugasan rutin secara terkawal
- meminta manusia membuat keputusan apabila risiko atau ketidakpastian memerlukannya

**Prinsip produk:** Understand the business → Configure the system → Find opportunities → Convert leads → Deliver value → Verify payment → Learn from outcomes → Improve operations.

**Matlamat bukan automasi tanpa kawalan. Matlamatnya ialah perniagaan yang lebih autonomi, telus, boleh diaudit dan boleh dipercayai.**

---

# PART XXVII — PERSONAL BRAND & SOCIAL MEDIA CONTROL

## 127. Visi Personal Brand Module
AI BIJAN perlu membolehkan setiap pengguna (pemilik, pengurus, sales, creator) membina dan mengawal personal brand mereka secara berstruktur.

Setiap orang boleh mempunyai:

- Profil personal brand yang unik
- Konten yang diselaraskan dengan identiti peribadi
- Kawalan penuh ke atas saluran media sosial mereka
- Cadangan konten berasaskan AI yang boleh diluluskan atau diubah
- Analitik prestasi kandungan dan engagement
- Integration dengan platform media sosial yang dibenarkan

Perbezaan antara personal brand dan brand syarikat:

| Aspek | Personal Brand | Company Brand |
|-------|---------------|---------------|
| Identiti | Individu (nama, wajah, suara) | Organisasi (logo, warna, tone) |
| Kawalan | Pemilik profil | Organization Admin |
| Saluran | Akaun peribadi, LinkedIn, Instagram | Akaun syarikat, halaman rasmi |
| Konten | Pendapat peribadi, behind-the-scenes | Pengumuman rasmi, produk, servis |
| Approval | Auto-publish atau review sendiri | Review mengikut polisi organisasi |
| Analytics | Engagement peribadi | Analytics merentas semua channel |

## 128. Personal Brand Profile
Setiap personal brand profile mengandungi:

- Nama, jawatan, peranan dalam organisasi
- Bio/description, tagline
- Category/niche/specialization
- Visual identity (avatar, banner, color palette jika berkenaan)
- Voice/tone guidelines (formal, casual, profesional, santai)
- Link ke saluran media sosial
- Connected social accounts (dengan authorization yang sah)
- Content preferences (topics, frequency, language)
- Approval workflow settings
- Brand safety rules
- Posting schedule preferences
- Analytics dashboard access

### 128.1 Social Media Connection Policy

- Gunakan OAuth atau authorized integration untuk setiap platform
- Jangan minta password akaun media sosial pelanggan
- Token disimpan selamat di server, tidak dihantar ke client
- Sediakan token refresh dan revocation
- Record connection status, last sync, error state
- Support platforms: Facebook, Instagram, LinkedIn, Twitter/X, TikTok, YouTube, WhatsApp Business, Telegram, website/blog

## 129. 5-Level Content System

Sistem konten 5 level memberikan struktur kepada penghasilan, semakan, dan penerbitan konten. Setiap level mempunyai tujuan, permission, dan autonomy yang berbeza.

### Level 0 — Raw Idea / Bahan Mentah

Tahap paling asas. Input mentah dari pelbagai sumber:

- Nota pantas, voice note transkripsi, idea terhasil dari chat dengan AI
- Headline kasar, topic suggestion, keyword cluster
- Image/video raw assets dari library
- Customer question atau feedback yang boleh jadi konten
- Trend/news yang relevan dengan niche

**Ciri-ciri:**
- Tidak memerlukan approval untuk disimpan sebagai draft
- Tidak boleh dipublish atau dikongsi
- Boleh diedit oleh owner dan authorized contributors
- AI boleh generate multiple variations dari satu raw idea

### Level 1 — Draft / Draf Kandungan

Raw idea yang telah dikembangkan menjadi konten siap produksi:

- Full caption/post copy dengan hook, body, CTA
- Image/video edited atau template-based
- Carousel/slides structure
- Thread/tweet sequence
- Email newsletter draft
- Blog article draft

**Ciri-ciri:**
- Generated oleh AI berdasarkan template dan brand guidelines
- Perlu review sebelum ke Level 2
- Owner boleh edit, reject, atau approve ke Level 2
- Version history disimpan untuk setiap draft

### Level 2 — Review / Semakan

Konten yang siap untuk disemak oleh manusia:

- Final copy dengan metadata lengkap (hashtags, tags, mentions)
- Preview bagaimana konten akan kelihatan di setiap platform
- Scheduled publish time cadangan
- Target audience specification
- Performance prediction score (optional)

**Ciri-ciri:**
- Notification dihantar kepada reviewer/approver
- Reviewer boleh approve, request changes, atau reject
- Change requests direkod dengan reason dan timestamp
- Multiple reviewers boleh ditetapkan mengikut policy

### Level 3 — Approved / Diluluskan

Konten yang telah mendapat kelulusan:

- Status approved dengan approver name dan timestamp
- Scheduled atau ready-to-publish
- Platform-specific formatting applied
- Compliance check passed (jika berkenaan)
- Backup saved di content repository

**Ciri-ciri:**
- Boleh auto-publish mengikut schedule
- Boleh manual publish oleh authorized user
- Audit trail lengkap (draft → review → approve → publish)
- Immutable record selepas approval

### Level 4 — Published / Terbit

Konten yang telah diterbitkan ke platform:

- Live URL/reference ke konten published
- Publish timestamp dan platform confirmation
- Initial metrics (views, likes, comments, shares)
- Performance tracking enabled
- Archive link di content library

**Ciri-ciri:**
- Metrics tracked secara automatik melalui platform API
- Engagement monitoring aktif
- Negative feedback alert (hate comments, complaints)
- Edit/undo capability mengikut platform rules

### Level 5 — Optimized / Dioptimumkan

Konten yang telah dianalisis dan pembelajaran diekstrak:

- Performance analysis vs benchmark
- Engagement quality score (bukan sekadar quantity)
- Audience sentiment analysis
- Best performing elements identified (hook, CTA, format, timing)
- Learnings stored untuk improve future content generation
- A/B test results recorded
- ROI/contribution estimate (jika applicable)

**Ciri-ciri:**
- Data digunakan untuk train/improve AI content generation
- Insights shared dengan content team
- Pattern recognition untuk trend prediction
- Feedback loop ke AI model untuk better suggestions

#### 5-Level Content Workflow

```
Level 0: Raw Idea
    ↓ (AI develop / human expand)
Level 1: Draft
    ↓ (human review & edit)
Level 2: Review
    ↓ (approver approve/reject/changes)
Level 3: Approved
    ↓ (schedule trigger / manual publish)
Level 4: Published
    ↓ (analytics collection, 24-48h observation)
Level 5: Optimized
    ↓ (learnings stored → improve future Level 0→1)
```

#### 5-Level Permission Matrix

| Tindakan | Level 0 | Level 1 | Level 2 | Level 3 | Level 4 | Level 5 |
|----------|---------|---------|---------|---------|---------|---------|
| Create | ✅ Owner | ✅ AI/Owner | — | — | — | — |
| Edit | ✅ Owner, Contributors | ✅ Owner, Contributors | ❌ Lock | ❌ Lock | ❌ Lock | ❌ Read-only |
| Review | — | — | ✅ Approver | — | — | — |
| Approve | — | — | — | ✅ Approver | — | — |
| Publish | — | — | — | — | ✅ Scheduler | — |
| Analyze | — | — | — | — | ✅ Analytics | ✅ Analyst |
| Delete | ✅ Owner | ✅ Owner | ✅ Approver | ❌ Immutable | ❌ Immutable | ✅ Read-only |

## 130. Content Generation AI

AI boleh membantu di setiap tahap 5-level content system:

### AI Capabilities per Level

| Level | AI Capability | Autonomy Level |
|-------|--------------|----------------|
| 0 — Raw Idea | Topic generation, trend detection, news aggregation, customer insight extraction | Auto-generate, save as draft |
| 1 — Draft | Copy writing, image generation/editing, template filling, multi-platform adaptation | Generate drafts, human must review |
| 2 — Review | Preview rendering, compliance checking, readability scoring, SEO analysis | Assist review, cannot approve |
| 3 — Approved | Schedule optimization, best-time prediction, platform formatting | Auto-schedule if permitted |
| 4 — Published | Real-time monitoring, sentiment tracking, competitor comparison | Monitor only, alert on anomalies |
| 5 — Optimized | Performance analysis, pattern recognition, learning extraction, recommendation generation | Analyze and suggest improvements |

### Content Generation Rules

- AI generated content mesti dilabel sebagai "AI-assisted" atau "AI-generated" mengikut platform requirements
- Human approval diperlukan sebelum publish (minimum Level 2 review)
- Brand safety filters aktif pada semua AI output
- Plagiarism/check originality sebelum approval
- Tone/voice consistency check terhadap brand guidelines
- Fact-checking untuk claims yang spesifik (harga, statistik, claim produk)
- Cultural sensitivity check untuk Malaysia multi-ethnic context

## 131. Social Media Dashboard

Dashboard personal brand menampilkan:

- Upcoming scheduled posts (calendar view)
- Recent published content dengan metrics
- Engagement overview (likes, comments, shares, saves, clicks)
- Follower growth trend
- Top performing content (last 7/30/90 days)
- Content pipeline (count per level 0-5)
- Approval queue (pending items)
- Platform connection status
- AI suggestion queue
- Brand mention monitoring
- Competitor benchmark (jika data tersedia)

## 132. Multi-Account & Team Collaboration

Untuk organisasi dengan multiple creators/sales:

- Setiap anggota boleh ada personal brand profile sendiri
- Shared content library untuk reuse
- Cross-approval workflow (sales content approved by marketing)
- Content calendar merentas semua team members
- Role-based access (creator, reviewer, publisher, admin)
- Brand guidelines enforcement across all personal brands
- Conflict detection (two people posting similar content simultaneously)

---

# PART XXVIII — CONTINUOUS AI LEARNING LOOP

## 133. Visi Continuous Improvement
AI BIJAN perlu belajar dari setiap interaksi, outcome, dan feedback untuk menjadi lebih baik secara berterusan.

Sistem bukan statik — ia berkembang melalui:

- Pembelajaran dari successful vs failed workflows
- Analisis dari user behavior patterns
- Feedback explicit dan implicit daripada pengguna
- Performance metrics dari AI agents
- Industry benchmark comparisons
- Security incident learnings
- Automation failure root cause analysis

Hasil pembelajaran ini disampaikan kepada **Super Admin** sebagai cadangan penambahbaikan yang boleh dilaksanakan.

## 134. Learning Data Sources

Sistem mengumpul pembelajaran dari:

### 134.1 Interaction Logs
- AI conversation patterns (what works, what fails)
- Tool call success/failure rates
- Approval rejection reasons
- User corrections to AI output
- Prompt engineering effectiveness

### 134.2 Business Outcome Data
- Lead conversion rates by outreach strategy
- Sales cycle duration trends
- Payment collection efficiency
- Inventory turnover patterns
- Customer retention/churn signals
- Campaign performance attribution

### 134.3 Operational Metrics
- Workflow completion rates
- Automation failure patterns
- Manual intervention frequency
- Bottleneck identification
- Resource utilization efficiency
- SLA compliance rates

### 134.4 Security & Compliance
- Failed authentication patterns
- Permission violation attempts
- Data access anomaly detection
- Audit trail findings
- Compliance gap identification

### 134.5 User Feedback
- Explicit ratings (thumbs up/down, star ratings)
- Feature requests and complaints
- Usability friction points
- Training/support ticket themes
- NPS dan satisfaction scores

## 135. Learning Processing Pipeline

```
Raw Data Collection
        ↓
Data Cleaning & Anonymization
        ↓
Pattern Recognition & Analysis
        ↓
Insight Generation
        ↓
Confidence Scoring
        ↓
Impact Assessment
        ↓
Recommendation Formation
        ↓
Super Admin Review Queue
        ↓
Approval & Implementation
        ↓
Effectiveness Validation
        ↓
Knowledge Base Update
        ↓
Improved AI Behavior
```

## 136. Recommendation Types to Super Admin

Sistem akan menghantar cadangan kepada Super Admin dalam kategori:

### 136.1 Model & Agent Improvements

| Jenis Cadangan | Contoh | Priority |
|----------------|--------|----------|
| Prompt Optimization | "Prompt X menghasilkan 40% lebih accurate output untuk invoice queries" | Medium |
| Model Selection | "Model Y lebih sesuai untuk Bahasa Melayu legal documents berbanding Model Z" | High |
| Tool Enhancement | "Tool inventory-check perlu retry logic untuk timeout handling" | High |
| Agent Behavior | "Agent sales-outreach terlalu agresif, cadangkan reduce frequency by 30%" | Medium |
| Knowledge Gap | "Agent tidak dapat menjawab soalan tentang MSIC code untuk agriculture sector" | High |

### 136.2 Workflow & Automation Improvements

| Jenis Cadangan | Contoh | Priority |
|----------------|--------|----------|
| Workflow Optimization | "Lead qualification workflow boleh dipendek dari 5 ke 3 steps" | Medium |
| Failure Recovery | "Payment verification retry 3x sebelum fail, cadangkan increase ke 5x" | High |
| Bottleneck Removal | "Approval queue average wait 4 hours, cadangkan auto-approve untuk low-risk items" | Medium |
| Template Update | "Invoice template missing MyInvois required fields" | Critical |
| Integration Fix | "WhatsApp Business API rate limit exceeded, cadangkan queue batching" | High |

### 136.3 Product & Feature Recommendations

| Jenis Cadangan | Contoh | Basis |
|----------------|--------|-------|
| New Feature | "30% tenant meminta multi-currency support, cadangkan build feature" | Usage pattern |
| UI Improvement | "Users struggle to find approval queue, cadangkan prominent placement" | Friction analysis |
| Performance Issue | "Report loading >5s untuk dataset >10K records, cadangkan pagination" | Metric threshold |
| Security Enhancement | "Multiple IDOR attempts detected, cadangkan additional validation layer" | Security log |
| Localization | "20% users prefer Tamil interface, cadangkan language pack priority" | Demographic data |

### 136.4 Business Intelligence Insights

| Jenis Cadangan | Contoh | Insight Source |
|----------------|--------|----------------|
| Market Trend | "Increase in F&B tenant registrations, cadangkan F&B specific templates" | Onboarding data |
| Churn Risk | "Tenant A usage dropped 60% dalam 30 hari, cadangkan proactive outreach" | Usage analytics |
| Upsell Opportunity | "Tenant B menggunakan 8/10 modules dalam Enterprise plan, cadangkan upgrade path" | Adoption metrics |
| Pricing Adjustment | "AI token cost increased 25%, cadangkan pricing review" | Cost analysis |

## 137. Super Admin Dashboard — AI Learning Center

Dashboard khas untuk Super Admin menampilkan:

### 137.1 System Health Overview
- AI agent performance scores (last 7/30 days)
- Success/failure ratio trends
- Average response time trends
- Token usage cost efficiency
- Model quality metrics

### 137.2 Recommendation Queue
- Pending recommendations (count by priority)
- Recommended actions due (overdue review)
- Recently implemented changes
- A/B test results awaiting decision
- Rollback candidates (underperforming changes)

### 137.3 Learning Insights
- Top patterns detected (last period)
- Emerging issues requiring attention
- Successful improvement case studies
- Cross-tenant learnings (anonymized)
- Industry benchmark comparisons

### 137.4 Experiment Management
- Active A/B tests
- Test results summary
- Statistical significance indicators
- Recommendation based on test outcomes

### 137.5 Knowledge Base Status
- Updated knowledge entries
- Deprecated/obsolete entries flagged
- Missing knowledge gaps identified
- Contribution sources breakdown

## 138. Learning Governance & Safety

Pembelajaran automatik mesti mempunyai kawalan:

### 138.1 Guardrails

- **No automatic model updates without approval** — Semua perubahan model/perilaku perlu diluluskan Super Admin
- **Rollback capability** — Setiap perubahan boleh di-revert dalam <15 minit
- **A/B testing requirement** — Perubahan besar perlu di-test pada subset tenant dahulu
- **Impact assessment** — Setiap cadangan perlu menunjukkan expected impact dan risk level
- **Audit trail** — Semua pembelajaran dan perubahan direkod dengan traceability
- **Tenant isolation preserved** — Learning dari tenant A tidak bocor ke tenant B secara langsung
- **Privacy protected** — Raw data dinormalisasi/anonymized sebelum analysis

### 138.2 Learning Cycle Frequency

| Cycle | Frequency | Scope | Output |
|-------|-----------|-------|--------|
| Micro-learning | Real-time | Individual interactions | Immediate context improvement |
| Daily digest | Daily | Previous day patterns | Summary report to Super Admin |
| Weekly analysis | Weekly | Trend analysis | Weekly improvement recommendations |
| Monthly deep-dive | Monthly | Strategic insights | Monthly roadmap adjustment suggestions |
| Quarterly review | Quarterly | System-wide evaluation | Quarterly architecture review input |

### 138.3 Feedback Loop Quality Metrics

- **Response accuracy trend** — Adakah AI semakin tepat?
- **User correction frequency** — Adakah pengguna semakin jarang membetulkan AI?
- **Task completion rate** — Adakah AI-assisted tasks selesai lebih cepat?
- **Approval rejection rate** — Adakah AI-generated content semakin berkualiti?
- **Time-to-value** — Berapa lama sehingga AI memberikan nilai?
- **Self-correction rate** — Berapa kali AI membetulkan diri tanpa human intervention?

## 139. AI Self-Evaluation Framework

AI BIJAN perlu menjalankan self-evaluation secara berkala:

### 139.1 Weekly Self-Assessment

Setiap agent menilai dirinya:

- Accuracy vs ground truth samples
- Response time vs SLA targets
- Tool call success rate
- User satisfaction proxy metrics
- Edge cases encountered
- Knowledge gaps identified

### 139.2 Monthly Cross-Agent Review

Perbandingan merentas agents:

- Relative performance ranking
- Shared failure patterns
- Best practice extraction
- Resource utilization efficiency
- Cross-agent collaboration effectiveness

### 139.3 Quarterly Architecture Review Input

Output kepada Super Admin dan product team:

- System capability gaps
- Scalability concerns
- Security posture assessment
- Technology debt identification
- Competitive landscape observations
- Customer need evolution signals

## 140. Continuous Learning Data Model

Cadangan entiti untuk menyokong continuous learning:

- `ai_learning_events` — Setiap event pembelajaran
- `ai_pattern_records` — Pattern yang dikesan
- `ai_recommendations` — Cadangan kepada Super Admin
- `ai_recommendation_status` — Status review dan implementation
- `ai_experiment_results` — Hasil A/B tests dan experiments
- `ai_knowledge_base_entries` — Knowledge yang dikemas kini
- `ai_self_evaluations` — Self-assessment records
- `ai_model_performance_metrics` — Performance tracking
- `ai_feedback_collection` — Explicit dan implicit feedback
- `improvement_implementation_logs` — Log perubahan yang dilaksanakan

## 141. Example Learning Flow — End to End

Scenario: AI Sales Assistant terlalu kerap menghantar follow-up email

```
Day 1: AI Sales Assistant menghantar follow-up email ke 50 leads
Day 2: 15 leads mark as spam/unsubscribe
Day 3: System detects unusual unsubscribe pattern (30% rate, threshold is 10%)
Day 3: Pattern recognized: "follow-up frequency too high for cold leads"
Day 3: Confidence scored: 85% (based on 3-day data, consistent pattern)
Day 3: Impact assessed: High (reputation risk, potential deliverability issues)
Day 3: Recommendation formed: "Reduce cold lead follow-up from daily to every 3 days"
Day 4: Daily digest sent to Super Admin with recommendation
Day 4: Super Admin reviews recommendation, approves with modification (every 2 days instead)
Day 4: Change deployed to staging environment for A/B test
Day 7: A/B test shows: treatment group unsubscribe rate 8% vs control 28%
Day 7: Result validated: 71% reduction in unsubscribes, no negative impact on conversion
Day 7: Knowledge base updated: "Optimal cold lead follow-up interval = 2-3 days"
Day 7: AI Sales Assistant behavior improved: new follow-up schedule applied
Week 4: Metric confirms sustained improvement, pattern marked as "validated learning"
```

---

# PART XXIX — ADDITIONAL DATA MODEL FOR BRAND & LEARNING

## 142. Personal Brand entities
Cadangan entiti:

- `personal_brand_profiles`
- `brand_visual_assets`
- `brand_voice_guidelines`
- `social_media_accounts`
- `social_account_connections`
- `connection_tokens` (encrypted storage)
- `brand_mentions`
- `brand_analytics_snapshots`

## 143. Content System entities
Cadangan entiti:

- `content_items` (main table, linked to level)
- `content_levels` (level 0-5 state tracking)
- `content_versions` (version history)
- `content_approvals` (approval records)
- `content_schedules` (publish scheduling)
- `content_platform_previews` (how content looks per platform)
- `content_metrics` (engagement data)
- `content_learnings` (extracted insights from Level 5)
- `content_templates` (reusable templates)
- `content_library` (shared asset repository)

## 144. Learning System entities
Cadangan entiti:

- `ai_learning_events`
- `ai_patterns`
- `ai_recommendations`
- `ai_recommendation_reviews`
- `ai_experiments`
- `ai_experiment_variants`
- `ai_knowledge_base`
- `ai_self_evaluations`
- `ai_model_metrics`
- `ai_feedback_items`
- `improvement_logs`

---

# PART XXX — API CONTRACTS FOR BRAND & LEARNING

## 145. Personal Brand API capabilities
Contoh endpoint konseptual:

- `GET /v1/personal-brands`
- `POST /v1/personal-brands`
- `PUT /v1/personal-brands/{id}`
- `GET /v1/personal-brands/{id}/analytics`
- `POST /v1/social-accounts/connect`
- `POST /v1/social-accounts/{id}/disconnect`
- `GET /v1/social-accounts/{id}/status`
- `POST /v1/content/generate`
- `POST /v1/content/{id}/promote-level`
- `POST /v1/content/{id}/approve`
- `POST /v1/content/{id}/schedule`
- `GET /v1/content/pipeline` (count per level)
- `GET /v1/content/scheduled`
- `POST /v1/content/{id}/publish`

## 146. AI Learning API capabilities
Contoh endpoint konseptual:

- `GET /v1/admin/ai-learning/dashboard`
- `GET /v1/admin/ai-learning/recommendations`
- `POST /v1/admin/ai-learning/recommendations/{id}/review`
- `POST /v1/admin/ai-learning/recommendations/{id}/approve`
- `POST /v1/admin/ai-learning/recommendations/{id}/reject`
- `GET /v1/admin/ai-learning/patterns`
- `GET /v1/admin/ai-learning/experiments`
- `POST /v1/admin/ai-learning/experiments`
- `GET /v1/admin/ai-learning/knowledge-base`
- `POST /v1/admin/ai-learning/knowledge-base/update`
- `GET /v1/admin/ai-learning/self-evaluation`
- `GET /v1/admin/ai-learning/feedback`

Semua endpoint perlu authentication, tenant scope (untuk admin endpoints), permission checks, validation, audit dan rate limits.

---

# PART XXXI — ACCEPTANCE TESTS FOR BRAND & LEARNING

## 147. Personal Brand Tests

- Social account connection menggunakan OAuth, bukan password storage
- Token refresh berfungsi tanpa user intervention
- Token revocation menghapus akses dengan serta-merta
- Content preview menunjukkan tampilan sebenar di setiap platform
- Level promotion memerlukan permission yang sesuai
- Approved content tidak boleh diubah tanpa create new version
- Published content metrics tracked accurately
- AI-generated content dilabel dengan betul mengikut platform rules
- Brand safety filter menangkap inappropriate content
- Multi-creator collaboration tidak menyebabkan content conflict
- Scheduled content publish pada waktu yang betul
- Unsubscribe/opt-out requests diproses dengan segera

## 148. 5-Level Content Tests

- Level 0 (Raw Idea) tidak boleh dipublish secara langsung
- Level 1 (Draft) memerlukan review sebelum promote
- Level 2 (Review) lock content daripada editing semasa review
- Level 3 (Approved) immutable record selepas approval
- Level 4 (Published) metrics collection berfungsi
- Level 5 (Optimized) learning extraction dari actual performance
- Version history menyimpan semua perubahan
- Approval audit trail lengkap dengan approver dan timestamp
- Content yang rejected boleh direvisi dan submit semula
- Bulk operations (bulk approve, bulk schedule) mempunyai validation

## 149. Continuous Learning Tests

- Pattern recognition tidak menghasilkan false positive melebihi threshold
- Recommendation confidence scoring accurately reflected
- Super Admin approval diperlukan sebelum implementasi perubahan
- Rollback berfungsi dalam masa yang ditetapkan (<15 minit)
- A/B testing statistical significance correctly calculated
- Tenant data anonymized sebelum learning analysis
- No cross-tenant data leakage melalui learning insights
- Self-evaluation metrics accurately measured
- Knowledge base updates traceable kepada source event
- Feedback collection respects user privacy preferences
- Learning cycle schedules execute reliably
- Failed learning processing logged dan alerted

---

# PART XXXII — IMPLEMENTATION ROADMAP FOR BRAND & LEARNING

## 150. Build Sequence

### Phase B1 — Personal Brand Foundation
Bangunkan:
- Personal brand profile CRUD
- Basic brand guidelines (voice, visual)
- Content creation tools (Level 0-1)
- Simple scheduling
- Basic analytics (post publish)

**Exit gate:** User boleh create brand profile, generate draft content, dan schedule post.

### Phase B2 — Social Media Integration
Bangunkan:
- OAuth connection untuk 2-3 platform utama
- Token management dan refresh
- Content publishing via API
- Platform-specific formatting
- Basic metrics collection

**Exit gate:** User boleh connect social accounts, publish content, dan view basic metrics.

### Phase B3 — 5-Level Content System
Bangunkan:
- Full level 0-5 workflow
- Approval chain configuration
- Content versioning
- Platform preview rendering
- Content library dan templates

**Exit gate:** Full content lifecycle managed dengan approval workflow dan version control.

### Phase B4 — Advanced AI Content Generation
Bangunkan:
- AI-powered content generation per level
- Brand voice matching
- Multi-platform adaptation
- Brand safety filtering
- Tone/consistency checking

**Exit gate:** AI boleh generate quality drafts yang sesuai dengan brand guidelines.

### Phase L1 — Learning Foundation
Bangunkan:
- Interaction logging
- Basic pattern detection
- Super Admin recommendation queue
- Simple dashboard

**Exit gate:** System boleh collect data dan generate basic recommendations.

### Phase L2 — Intelligent Learning
Bangunkan:
- Advanced pattern recognition
- Confidence scoring
- A/B testing framework
- Knowledge base management
- Self-evaluation framework

**Exit gate:** System boleh identify valid patterns, propose evidence-based recommendations.

### Phase L3 — Autonomous Improvement
Bangunkan:
- Automated minor improvements (with approval)
- Cross-agent learning sharing
- Predictive recommendations
- Quarterly architecture review input

**Exit gate:** System continuously improves dengan minimal human intervention untuk routine optimizations.

### Phase Integrated — Brand + Learning Combined
- AI content generation learns from published content performance
- Level 5 learnings feed back into content generation
- Personal brand analytics inform business understanding
- Cross-module learning insights

**Exit gate:** Content performance directly improves AI suggestions, creating virtuous cycle.

---

# FINAL PRODUCT PRINCIPLES — UPDATED

## 151. Additional Product Rules (from v4.2+)

21. Setiap pengguna boleh membina dan mengawal personal brand mereka dengan penuh.
22. Kawalan media sosial melalui OAuth yang selamat, tiada password storage.
23. Sistem 5-level content memastikan kualiti dan compliance sebelum publish.
24. AI boleh generate content tetapi human approval diperlukan sebelum publish.
25. AI-generated content mesti dilabel mengikut platform requirements.
26. Setiap konten melalui learning loop: publish → measure → analyze → learn → improve.
27. Semua pembelajaran AI disampaikan kepada Super Admin sebagai cadangan, bukan arahan automatik.
28. Super Admin mempunyai kuasa penuh untuk approve, reject, atau modify setiap cadangan.
29. Rollback capability mesti tersedia untuk semua perubahan yang dihasilkan oleh AI learning.
30. Tenant data tidak boleh bocor melalui learning insights atau pattern recognition.

## 152. Updated Vision Statement

AI BIJAN disasarkan menjadi platform operasi perniagaan yang boleh:

- memahami model bisnes pelanggan
- menyediakan konfigurasi awal yang sesuai
- menghubungkan operasi dan data
- membantu mencari prospek
- mengurus CRM dan follow-up
- menyediakan proposal dan membantu proses closing
- menyokong invoicing dan collection
- mengesahkan serta merekonsiliasi pembayaran melalui integrasi yang sah
- menyelaras operasi selepas jualan
- memantau prestasi
- mengenal pasti peluang penambahbaikan
- menjalankan tugasan rutin secara terkawal
- meminta manusia membuat keputusan apabila risiko atau ketidakpastian memerlukannya
- **membolehkan setiap pengguna membina personal brand dan kawal media sosial mereka**
- **menghasilkan konten berkualiti melalui sistem 5-level dengan bantuan AI**
- **belajar dari setiap interaksi untuk menjadi lebih baik secara berterusan**
- **memberi cadangan bernilai kepada Super Admin untuk penambahbaikan sistem**

**Prinsip produk yang dikemas kini:**
Understand the business → Configure the system → Find opportunities → Convert leads → Deliver value → Verify payment → Learn from outcomes → Improve operations → Empower personal brands → Automate with control → Continuously evolve.

**Matlamat akhir:** Bukan sekadar alat operasi — AI BIJAN ialah rakan perniagaan yang memahami, membantu, tumbuh bersama, dan sentiasa lebih bijak dari sebelumnya. Dengan kuasa personal brand dan continuous learning, setiap pengguna bukan sahaja mengendalikan perniagaan mereka, tetapi juga membina pengaruh dan kepercayaan di pasaran.

**Prinsip terakhir: comprehensive product map, complete vertical slices, controlled AI autonomy, verified production readiness, empowered personal brands, and continuous intelligent evolution.**

---

# PART XXXIII — BUSINESS ACCESS MANAGEMENT & ORGANIZATIONAL STRUCTURE

## 153. Pengenalan dan Prinsip Asas

### 153.1 Visi

AI BIJAN mesti lengkap dari sudut pengurusan akses, struktur organisasi dan operasi untuk **setiap sektor perniagaan** — bukan sekadar ada modul bisnes, AI Assistant dan automasi.

Apabila pelanggan mendaftar perniagaan restoran, syarikat event management, kontraktor, klinik atau kedai runcit, AI BIJAN perlu memahami struktur perniagaan itu dan menyediakan akses yang sesuai untuk pemilik, pengurusan, admin, penyelia, staf, pekerja lapangan dan pelanggan mengikut keperluan sektor tersebut.

Setiap sektor boleh mempunyai struktur jawatan dan operasi berbeza, tetapi semuanya menggunakan **satu sistem pengurusan akses yang konsisten**.

### 153.2 Prinsip Utama

| # | Prinsip | Penerangan |
|---|---------|------------|
| 1 | **Role ≠ Permission** | Nama peranan tidak menentukan kuasa. Kuasa ditentukan melalui permission matrix, skop organisasi dan dasar kelulusan. |
| 2 | **Owner Supremacy** | Pemilik perniagaan sentiasa mempunyai kawalan tertinggi — termasuk hak menarik balik akses pada bila-bila masa. |
| 3 | **Need-to-Know by Default** | Setiap pengguna hanya mengakses data yang diperlukan untuk tugas mereka sahaja. |
| 4 | **Branch & Department Scope** | Pengguna hanya mengakses cawangan dan jabatan yang dibenarkan secara eksplisit. |
| 5 | **Approval Limits Vary** | Had kelulusan berbeza mengikut peranan, sektor dan konfigurasi tenant. |
| 6 | **AI Follows Human Permissions** | AI assistant setiap pengguna hanya boleh membaca dan melakukan tindakan yang dibenarkan kepada pengguna itu. |
| 7 | **Audit Everything** | Siapa melakukan apa, bila, pada data mana, dan sama ada diluluskan — direkod sepenuhnya. |
| 8 | **Join/Transfer/Exit Lifecycle** | Proses menambah staf, menukar jawatan, memindahkan cawangan, menggantung dan membuang akses dikawal sepenuhnya. |
| 9 | **Industry Blueprint Drives Access** | Business Understanding Engine + Blueprint Engine cadangkan peranan dan permission berdasarkan sektor perniagaan. |
| 10 | **Custom Roles Allowed** | Pemilik boleh mencipta jawatan dan set permission tersendiri melebihi peranan standard. |

### 153.3 Status Dokumen

> **Status:** Spesifikasi ini menetapkan keperluan wajib untuk pengurusan akses dan struktur organisasi. Ia belum bermaksud setiap fungsi telah dikodkan, diuji, atau dilancarkan. Blueprint terdahulu sudah menetapkan konsep asas role-based permissions dan tenant isolation, tetapi matriks lengkap setiap peranan dan kuasa akses belum ditakrifkan sepenuhnya.

---

## 154. Struktur Akses Asas — 7 Peranan Teras

### 154.1 Ringkasan Peranan

| # | Peranan | Kod | Gred | Skop Asas |
|---|---------|-----|------|-----------|
| 1 | Pemilik Perniagaan | `OWNER` | G10 | Seluruh organisasi — semua cawangan, jabatan, data kewangan, tetapan pemilikan |
| 2 | Pengurus Besar / Operasi | `MANAGER` | G8 | Jabatan atau cawangan yang dilantik — KPI, pasukan, kelulusan terhad |
| 3 | Pengurus Cawangan | `BRANCH_MANAGER` | G7 | Satu cawangan tertentu — operasi harian, staf cawangan |
| 4 | Admin Perniagaan | `ADMIN` | G6 | Konfigurasi modul, rekod operasi, pengguna (mengikut had kuasa) |
| 5 | Ketua Jabatan / Penyelia | `SUPERVISOR` | G5 | Pasukan sendiri — tugasan, kehadiran, kualiti kerja |
| 6 | Staf / Pekerja | `STAFF` | G3 | Tugasan sendiri, jadual, rekod pekerjaan |
| 7 | Pelanggan / Luar | `EXTERNAL` | G1 | Akses terhad — tempahan, pesanan, portal pelanggan |

### 154.2 Pemilik Perniagaan — Business Owner (`OWNER`)

#### 154.2.1 Definisi

Pemilik perniagaan mempunyai akses pemilikan dan kawalan tertinggi dalam organisasi miliknya. Ini adalah peranan dengan kuasa paling luas dalam sistem.

#### 154.2.2 Hak dan Kawalan

| Kategori | Fungsi | Permission Code | Keterangan |
|----------|--------|-----------------|------------|
| Prestasi | Lihat keseluruhan prestasi | `view:dashboard:all` | Revenue, jualan, profit, cash flow, KPI semua cawangan |
| Organisasi | Urus cawangan, jabatan, pengguna | `manage:organization`, `manage:members`, `manage:branches`, `manage:departments` | Tambah, buang, pindahkan cawangan/jabatan/pengguna |
| Pelantikan | Tetapkan admin dan pengurus | `assign:roles`, `revoke:access` | Melantik atau menarik balik peranan pengurusan |
| Kewangan | Keluluskan pembayaran, perbelanjaan | `approve:payments`, `approve:expenses`, `approve:contracts` | Semua kelulusan kewangan memerlukan OWNER jika melebihi had |
| Sistem | Modul, tetapan AI, langganan | `manage:modules`, `manage:tenant_settings`, `manage:subscription` | Aktifkan/nonaktif modul, ubah pelan, tetapan AI |
| Audit | Lihat audit log, tarik akses | `view:audit_log`, `revoke:any_access` | Semua aktiviti sistem boleh disemak oleh owner |
| Rahsia | Akses penuh data sensitif | `view:financial_records`, `view:bank_accounts`, `view:system_secrets` | Maklumat bank, API keys, tetapan sistem penuh |

#### 154.2.3 Peraturan Khusus

- Owner sentiasa boleh melihat dan mengubah apa-apa data dalam organisasinya
- Owner tidak boleh "dihapuskan" daripada organisasi sendiri — hanya boleh dipindahkan ke peranan lain
- Apabila owner melantik admin, admin **tidak** mendapat akses penuh seperti owner
- Owner menerima notifikasi automatik untuk perubahan kritikal (modul ditambah/dibuang, admin dilantik, data dipadam besar-besaran)
- Owner boleh menetapkan had kelulusan untuk diri mereka sendiri (self-imposed limits) untuk governance

### 154.3 Pengurusan Kanan — Management (`MANAGER`, `BRANCH_MANAGER`)

#### 154.3.1 Definisi

Pengurusan kanan mengurus operasi mengikut kuasa yang diberikan pemilik. Mereka **bukan** pemilik dan tidak semestinya mempunyai akses kepada semua maklumat.

#### 154.3.2 Jenis Pengurusan

| Peranan | Kod | Skop | Contoh Jawatan |
|---------|-----|------|----------------|
| Pengurus Besar | `GENERAL_MANAGER` | Seluruh organisasi atau bahagian utama | General Manager, COO |
| Pengurus Operasi | `OPS_MANAGER` | Operasi harian | Operations Manager |
| Pengurus Cawangan | `BRANCH_MANAGER` | Satu cawangan | Store Manager, Branch Manager |
| Pengurus Jabatan | `DEPT_MANAGER` | Satu jabatan | Sales Manager, Marketing Manager |

#### 154.3.3 Hak dan Kawalan

| Kategori | Fungsi | Permission Code | Had / Syarat |
|----------|--------|-----------------|--------------|
| KPI | Pantau prestasi pasukan | `view:kpi:assigned`, `view:reports:team` | Hanya untuk pasukan/cawangan yang dilantik |
| Laporan | Akses laporan operasi | `view:reports:operations` | Tidak termasuk laporan kewangan penuh kecuali diberi kebenaran |
| Kelulusan | Luluskan tugasan/perbelanjaan | `approve:tasks`, `approve:expenses:limited` | Mengikut had yang ditetapkan owner |
| Staf | Lihat profil staf tersirat | `view:employees:assigned` | Tidak boleh pecat atau naikkan gaji tanpa owner approval |
| Tempahan | Urus tempahan/reservasi | `manage:bookings:assigned` | Hanya untuk cawangan/jabatan yang dilantik |

#### 154.3.4 Peraturan Khusus

- Manager **tidak** mendapat akses automatik kepada data kewangan sensitif (bank accounts, payroll penuh)
- Manager **tidak** boleh mengubah tetapan sistem atau pemilikan
- Manager **tidak** boleh melantik atau membuang pengguna lain tanpa owner approval
- Had kelulusan kewangan ditetapkan oleh owner (contoh: RM5,000 untuk branch manager, RM50,000 untuk general manager)
- Perubahan melebihi had memerlukan escalation kepada owner atau finance approver

### 154.4 Admin Perniagaan — Business Admin (`ADMIN`)

#### 154.4.1 Definisi

Admin mengurus konfigurasi dan rekod yang dibenarkan oleh pemilik. Admin **bukan** pemilik dan **bukan** pengurusan.

#### 154.4.2 Hak dan Kawalan

| Kategori | Fungsi | Permission Code | Had / Syarat |
|----------|--------|-----------------|--------------|
| Pengguna | Urus pengguna (terhad) | `manage:users:limited` | Boleh tambah/staf, tidak boleh buang owner/manager tanpa approval |
| Operasi | Produk/servis, tempahan, dokumen | `manage:products`, `manage:bookings`, `manage:documents` | Mengikut modul yang diaktifkan |
| Modul | Konfigurasi modul (terbatas) | `configure:modules:limited` | Tidak boleh aktifkan/nonaktifkan modul utama tanpa owner |
| Rekod | CRUD rekod operasi | `create:*`, `read:*`, `update:*`, `delete:*` | Mengikut permission yang dilantik |

#### 154.4.3 Had Admin

- Admin **tidak** automatik dapat akses kepada: bank, rahsia sistem, pemilikan, semua rekod kewangan
- Admin **tidak** boleh mengubah tetapan tenant atau subscription
- Admin **tidak** boleh melantik atau menaikkan peranan pengguna lain
- Admin **tidak** boleh mengeksport data seluruh organisasi tanpa approval
- Admin **tidak** boleh mengubah audit log atau menyembunyikan aktiviti

### 154.5 Ketua Jabatan / Penyelia — Supervisor (`SUPERVISOR`)

#### 154.5.1 Definisi

Supervisor mengawal pasukan, tugasan dan operasi harian. Mereka berada antara pengurusan dan staf.

#### 154.5.2 Hak dan Kawalan

| Kategori | Fungsi | Permission Code | Keterangan |
|----------|--------|-----------------|------------|
| Tugasan | Menugaskan kerja | `assign:tasks`, `view:team_tasks` | Untuk pasukan yang dipantau |
| Kehadiran | Semak kehadiran | `view:attendance:team`, `approve:leave:team` | Hanya untuk staf bawahannya |
| Kualiti | Semak kualiti kerja | `review:work:quality` | Menyemak output staf |
| Kelulusan | Pengesahan awal/kelulusan terhad | `approve:minor:actions` | Had kecil (contoh: diskaun ≤5%, refund ≤RM100) |

#### 154.5.3 Had Supervisor

- Supervisor **tidak** boleh mengubah struktur organisasi atau peranan
- Supervisor **tidak** boleh mengakses data kewangan sensitif
- Supervisor **tidak** boleh membuang atau menggantung staf tanpa manager approval
- Supervisor **tidak** boleh melihat gaji staf lain tanpa authorization khas

### 154.6 Staf / Pekerja — Staff (`STAFF`)

#### 154.6.1 Definisi

Staf mempunyai akses untuk menjalankan kerja sendiri sahaja.

#### 154.6.2 Hak dan Kawalan

| Kategori | Fungsi | Permission Code | Keterangan |
|----------|--------|-----------------|------------|
| Jadual | Lihat jadual sendiri | `view:schedule:self` | Jadual shift/tugasan peribadi |
| Tugasan | Lihat dan kemas kini tugasan | `view:tasks:self`, `update:tasks:self` | Hanya tugasan yang diberikan kepada mereka |
| SOP | Akses SOP dan maklumat kerja | `view:sop`, `view:knowledge_base` | Dokumen operasi yang relevan |
| Kehadiran | Catat kehadiran sendiri | `record:attendance:self` | Check-in/check-out sendiri |
| Laporan | Kemas kini kemajuan kerja | `create:reports:self`, `update:status:self` | Laporan untuk tugasan mereka |
| Pelanggan | Akses rekod pelanggan (jika perlu) | `view:customers:work-related` | Hanya data pelanggan berkaitan tugas |

#### 154.6.3 Had Staf

- Staf **tidak** boleh melihat gaji, data sulit atau rekod staf lain tanpa kebenaran
- Staf **tidak** boleh mengubah konfigurasi sistem atau modul
- Staf **tidak** boleh mengeksport data organisasi
- Staf **tidak** boleh meluluskan transaksi atau membuat keputusan pengurusan
- Staf luaran (freelancer, vendor) mempunyai skop lebih terhad lagi

### 154.7 Pelanggan / Pihak Luar — External (`EXTERNAL`)

#### 154.7.1 Definisi

Pelanggan, vendor, freelancer dan pihak luar mempunyai akses terhad kepada portal atau fungsi tertentu.

| Jenis | Kod | Akses |
|-------|-----|-------|
| Pelanggan | `CUSTOMER` | Portal tempahan, status pesanan, sejarah pembelian |
| Vendor | `VENDOR` | Portal pesanan belian, status pembayaran, katalog produk |
| Freelancer | `FREELANCER` | Tugasan tertentu, dokumen berkaitan projek |
| Auditor | `AUDITOR` | Baca-only akses kepada rekod tertentu (tempoh terhad) |
| Pelajar/Pemeriksa | `INSPECTOR` | Akses terhad mengikut keperluan pemeriksaan |

#### 154.7.2 Peraturan External

- External users **tidak** boleh mengakses data dalaman organisasi
- External users **tidak** boleh melihat struktur organisasi, staf atau gaji
- External users **tidak** boleh berkomunikasi dengan internal staff melalui sistem (kecuali channel yang ditetapkan)
- Akses external mestilah **time-limited** dan **revocable** oleh owner/admin
- External users mesti melalui authentication yang berasingan (tidak bercampur dengan internal users)

---

## 155. Matriks Peranan dan Kuasa Akses

### 155.1 Matriks Permission Asas

Tanda ✓ = dibenarkan, ✗ = tidak dibenarkan, △ = bergantung pada konfigurasi tenant.

| Permission | OWNER | MANAGER | BRANCH_MGR | ADMIN | SUPERVISOR | STAFF | EXTERNAL |
|------------|-------|---------|------------|-------|------------|-------|----------|
| `view:dashboard:all` | ✓ | △ | △ | ✗ | ✗ | ✗ | ✗ |
| `view:financial_records` | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| `view:bank_accounts` | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| `manage:organization` | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| `manage:members` | ✓ | ✗ | ✗ | △ | ✗ | ✗ | ✗ |
| `assign:roles` | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| `revoke:access` | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| `view:employees` | ✓ | △ | △ | △ | △ | ✗ | ✗ |
| `view:attendance` | ✓ | △ | △ | △ | ✓ | self | ✗ |
| `manage:products` | ✓ | △ | △ | ✓ | ✗ | ✗ | ✗ |
| `manage:bookings` | ✓ | △ | △ | ✓ | △ | ✗ | △ |
| `approve:payments` | ✓ | △ | △ | ✗ | △ | ✗ | ✗ |
| `approve:expenses` | ✓ | △ | △ | ✗ | △ | ✗ | ✗ |
| `create:tasks` | ✓ | ✓ | ✓ | △ | ✓ | ✗ | ✗ |
| `view:tasks:self` | ✓ | ✓ | ✓ | △ | △ | ✓ | △ |
| `update:tasks:self` | ✓ | ✓ | ✓ | △ | △ | ✓ | △ |
| `record:attendance:self` | ✓ | ✓ | ✓ | △ | △ | ✓ | ✗ |
| `view:sop` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| `configure:modules` | ✓ | ✗ | ✗ | △ | ✗ | ✗ | ✗ |
| `manage:subscription` | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| `view:audit_log` | ✓ | ✗ | ✗ | △ | ✗ | ✗ | ✗ |
| `export:data` | ✓ | △ | △ | △ | ✗ | ✗ | ✗ |
| `delete:records` | ✓ | ✗ | ✗ | △ | ✗ | ✗ | ✗ |

### 155.2 Matriks Kelulusan (Approval Limits)

Had kelulusan berbeza mengikut peranan dan sektor perniagaan. Owner menetapkan had asas; sektor boleh menambah had khusus.

| Tindakan | OWNER | MANAGER | BRANCH_MGR | ADMIN | SUPERVISOR | STAFF |
|----------|-------|---------|------------|-------|------------|-------|
| Diskaun produk | Tanpa had | ≤10% | ≤5% | ≤3% | ≤2% | ✗ |
| Refund pelanggan | Tanpa had | ≤RM5,000 | ≤RM1,000 | ≤RM200 | ≤RM100 | ✗ |
| Pembelian stok | Tanpa had | ≤RM50,000 | ≤RM10,000 | ≤RM2,000 | ✗ | ✗ |
| Upah/pecat staf | Tanpa had | ✗ | ✗ | ✗ | Cadangan sahaja | ✗ |
| Naikkan gaji | Tanpa had | ≤5% | ✗ | ✗ | ✗ | ✗ |
| Ubah tetapan sistem | Tanpa had | ✗ | ✗ | △ | ✗ | ✗ |
| Contract signing | Tanpa had | ≤RM100,000 | ≤RM25,000 | ✗ | ✗ | ✗ |

### 155.3 Data Classification dan Akses

| Klasifikasi | Deskripsi | OWNER | MANAGER | ADMIN | SUPERVISOR | STAFF |
|-------------|-----------|-------|---------|-------|------------|-------|
| Public | Maklumat awam | ✓ | ✓ | ✓ | ✓ | ✓ |
| Internal | Maklumat dalaman | ✓ | ✓ | ✓ | ✓ | △ |
| Confidential | Data sensitif | ✓ | ✗ | ✗ | ✗ | ✗ |
| Restricted | Data rahsia | ✓ | ✗ | ✗ | ✗ | ✗ |
| Financial | Rekod kewangan | ✓ | △ | ✗ | ✗ | ✗ |

△ = bergantung pada peranan spesifik dan konfigurasi tenant

---

## 156. Role & Permission Engine

### 156.1 Model Data

Sistem menggunakan RBAC (Role-Based Access Control) dengan fine-grained permissions:

```
User → OrganizationMembership → Role(s) → Permission(s) + Scope(s)
```

Entiti teras:

| Entiti | Keterangan |
|--------|-----------|
| `users` | Pengguna global (boleh jadi ahli beberapa organisasi) |
| `organizations` | Organisasi perniagaan (tenant-level) |
| `organization_memberships` | Hubungan user-org dengan peranan dan skop |
| `roles` | Definisi peranan (standard atau custom) |
| `permissions` | Tindakan individu yang dibenarkan |
| `role_permissions` | Junction table: peranan ↔ permission |
| `scopes` | Batasan ruang: branch, department, record |
| `approval_limits` | Had kelulusan per peranan |
| `data_classifications` | Klasifikasi data dan aksesnya |

### 156.2 Standard Roles (Built-in)

Peranan standard yang disediakan AI BIJAN:

| Role Code | Nama | Gred | Deskripsi |
|-----------|------|------|-----------|
| `OWNER` | Pemilik Perniagaan | G10 | Kawalan penuh organisasi |
| `GENERAL_MANAGER` | Pengurus Besar | G8 | Pengurusan seluruh organisasi |
| `OPERATIONS_MANAGER` | Pengurus Operasi | G8 | Pengurusan operasi harian |
| `BRANCH_MANAGER` | Pengurus Cawangan | G7 | Pengurusan satu cawangan |
| `SALES_MANAGER` | Pengurus Jualan | G7 | Pengurusan pasukan jualan |
| `MARKETING_MANAGER` | Pengurus Pemasaran | G7 | Pengurusan pemasaran dan brand |
| `FINANCE_MANAGER` | Pengurus Kewangan | G8 | Pengurusan kewangan dan akaun |
| `HR_MANAGER` | Pengurus HR | G7 | Pengurusan sumber manusia |
| `BUSINESS_ADMIN` | Admin Perniagaan | G6 | Konfigurasi dan rekod operasi |
| `FINANCE_OFFICER` | Pegawai Kewangan | G5 | Pengurusan kewangan harian |
| `ACCOUNTANT` | Akauntan | G5 | Buku kira dan pelaporan |
| `TREASURER` | Juruwang | G4 | Pengurusan tunai dan POS |
| `SUPERVISOR` | Penyelia | G5 | Pengawasan pasukan |
| `TEAM_LEAD` | Ketua Pasukan | G5 | Kepimpinan pasukan tanpa kuasa pengurusan penuh |
| `SENIOR_STAFF` | Staf Kanan | G4 | Staf berpengalaman dengan akses tambahan |
| `STAFF` | Staf / Pekerja | G3 | Akses asas untuk tugas |
| `INTERN` | Pelatih | G2 | Akses terhad untuk latihan |
| `VENDOR` | Vendor / Pembekal | G1 | Akses portal pembekal |
| `CUSTOMER` | Pelanggan | G1 | Akses portal pelanggan |
| `FREELANCER` | Freelancer | G1 | Akses projek tertentu |
| `AUDITOR` | Auditor | G1 | Baca-only tempoh terhad |

### 156.3 Custom Roles

Pemilik boleh mencipta peranan custom:

- Custom roles meniru struktur sebenar syarikat
- Setiap custom role mempunyai set permission tersendiri
- Custom roles masih tertakluk kepada owner supremacy dan audit trail
- Custom roles boleh diwarisi dari standard roles (inherit)
- Owner boleh menetapkan had kelulusan khusus untuk setiap custom role

### 156.4 Permission Granularity

Setiap permission terdiri daripada:

```
{resource}.{action}.{scope}
```

Contoh:

- `employee.view.self` — lihat profil sendiri
- `employee.view.team` — lihat profil pasukan
- `employee.create` — cipta rekod pekerja baru
- `employee.update` — ubah rekod pekerja
- `employee.delete` — padam rekod pekerja
- `employee.approve.leave` — luluskan cuti
- `payment.approve.expense` — luluskan perbelanjaan
- `payment.approve.refund` — luluskan refund
- `product.discount.apply` — gunakan diskaun
- `booking.manage.all` — urus semua tempahan
- `booking.manage.branch` — urus tempahan cawangan
- `report.export.full` — eksport laporan penuh
- `report.export.anonymized` — eksport laporan teranonimisasi
- `system.configure.modules` — konfigurasikan modul
- `system.view.audit_log` — lihat audit log
- `user.manage.invite` — jemput pengguna baru
- `user.manage.revoke` — cabut akses pengguna

### 156.5 Scope Enforcement

Selain permission, scope menentukan **di mana** dan **pada apa** pengguna boleh bertindak:

| Scope Type | Keterangan | Contoh |
|------------|-----------|--------|
| `branch` | Cawangan tertentu | `branch_id: 5` — hanya cawangan Bangsar |
| `department` | Jabatan tertentu | `department_id: 3` — hanya jabatan Jualan |
| `record` | Rekod tertentu | `record_ids: [101, 102]` — hanya pelanggan tertentu |
| `ownership` | Milik sendiri | `owner_id: current_user` — hanya data milik sendiri |
| `time` | Tempoh masa | `valid_from: 2026-01-01, valid_to: 2026-12-31` |
| `classification` | Klasifikasi data | `min_classification: confidential` |

Scope enforcement berlaku pada setiap lapisan:

1. **Query layer** — SQL automatically scoped
2. **Service layer** — Authorization middleware
3. **API layer** — Request validation
4. **UI layer** — Button/feature visibility (bukan security boundary)

---

## 157. Business Owner Access — Kawalan Pemilikan

### 157.1 Hak Mutlak Owner

Owner mempunyai hak mutlak terhadap organisasi mereka:

- Melihat dan mengubah semua data
- Menambah, mengubah, membuang semua pengguna
- Melantik dan mencabut peranan
- Mengubah tetapan organisasi dan sistem
- Mengaktifkan/nonaktifkan modul
- Mengubah pelan langganan
- Mengeksport semua data
- Melihat semua audit logs
- Menetapkan dan mengubah had kelulusan
- Menjual atau memindahkan organisasi (transfer ownership)

### 157.2 Ownership Transfer

Proses pemindahan pemilikan:

1. Owner sedia memohon transfer
2. Calon owner dipilih (mesti ada akaun aktif)
3. Kedua-dua belu meluluskan melalui confirmation
4. Audit log direkodkan
5. Semua permission dan settings dipindahkan
6. Notifikasi dihantar kepada semua admin/manager

### 157.3 Owner Self-Imposed Limits

Owner boleh memilih untuk hadkan kuasa sendiri untuk governance:

- Set had kelulusan maksimum untuk diri sendiri
- Require second-owner approval untuk tindakan kritikal (multi-owner setup)
- Enable mandatory cooling-off period untuk perubahan besar
- Delegate specific powers kepada manager dengan expiry date

### 157.4 Multi-Owner Setup (Optional)

Untuk organisasi besar, AI BIJAN menyokong multi-owner:

- Lebih satu pengguna boleh menjadi owner
- Semua owners mempunyai kuasa setara
- Tindakan kritikal memerlukan dual approval
- Owner boleh menetapkan primary owner untuk decisions akhir

---

## 158. Business Admin Access — Pengurusan Operasi

### 158.1 Admin Levels

| Level | Kod | Akses |
|-------|-----|-------|
| Senior Admin | `SENIOR_ADMIN` | Konfigurasi hampir penuh, tidak termasuk system settings |
| Business Admin | `BUSINESS_ADMIN` | Konfigurasi modul, rekod operasi, pengguna terhad |
| Junior Admin | `JUNIOR_ADMIN` | Hanya CRUD rekod, tiada konfigurasi |

### 158.2 Admin Boundaries

Admin **TIDAK BOLEH**:

- Mengubah tetapan tenant atau subscription
- Mengubah ownership atau transfer organization
- Menghapuskan organisasi atau data historikal penting
- Mengubah audit logs atau menyembunyikan aktiviti
- Melantik owner atau senior manager
- Mengeksport data seluruh organisasi tanpa approval
- Mengubah approval limits melebihi had yang ditetapkan
- Mengakses bank accounts atau system secrets

Admin **BOLEH** (dengan permission):

- Tambah/edit/buang produk dan servis
- Urus tempahan dan reservasi
- Konfigurasi template dokumen
- Urus pengguna (add staff, limited edit)
- Generate laporan operasi
- Configure workflow (within module limits)

### 158.3 Admin Action Approval Matrix

| Tindakan Admin | Perlu Approval? | Dari Siapa |
|----------------|-----------------|------------|
| Tambah produk | Tidak | - |
| Buang produk | Ya (jika ada jualan) | Owner / Manager |
| Tambah staf | Tidak | - |
| Buang staf | Ya | Manager / Owner |
| Ubah harga | Ya (jika melebihi standard) | Manager |
| Buat discount besar | Ya | Manager / Owner |
| Configure module | Ya | Owner |
| Export data | Ya | Owner |
| Delete records | Ya | Owner (jika historikal) |

---

## 159. Staff Management — Profil, Jawatan, Jabatan, Kehadiran

### 159.1 Employee Profile Structure

Setiap staf mempunyai profil lengkap:

```
Employee Profile:
├── Personal Information
│   ├── Name, IC/Passport, Contact
│   ├── Address, Emergency Contact
│   └── Photo (optional)
├── Employment Details
│   ├── Job Title / Position
│   ├── Department
│   ├── Branch
│   ├── Reporting Line (Manager)
│   ├── Employment Type (Full-time, Part-time, Contract, Freelance)
│   ├── Join Date
│   ├── Probation End Date
│   └── Contract Expiry (if applicable)
├── Compensation (Restricted Access)
│   ├── Base Salary
│   ├── Allowances
│   ├── EPF/SOCSO/PCB Numbers
│   └── Bank Account (for payroll)
├── Attendance & Leave
│   ├── Attendance Records
│   ├── Leave Balances
│   ├── Leave History
│   └── Shift Schedule
├── Documents
│   ├── Contracts
│   ├── Certificates
│   ├── Performance Reviews
│   └── Disciplinary Records
└── AI Assistant Configuration
    ├── Personal AI Agent Enabled
    ├── AI Token Allocation
    └── AI Permission Scope
```

### 159.2 Job Title and Position Hierarchy

Struktur jawatan mengikut organisasi:

```
Organization
├── Owner
├── General Manager
│   ├── Operations Manager
│   │   ├── Branch Manager (Bangsar)
│   │   │   ├── Supervisor
│   │   │   │   ├── Staff (Cashier)
│   │   │   │   └── Staff (Cook)
│   │   │   └── Staff (Cleaner)
│   │   └── Branch Manager (Petaling Jaya)
│   │       └── ...
│   ├── Sales Manager
│   │   ├── Team Lead
│   │   └── Sales Executive
│   └── Finance Manager
│       ├── Accountant
│       └── Treasurer
├── HR Manager
│   ├── HR Executive
│   └── Training Officer
└── IT/Admin
    └── System Admin
```

### 159.3 Attendance Management

| Fitur | Deskripsi | Akses |
|-------|-----------|-------|
| Check-in/out | Staf catat kehadiran | Staff (self only) |
| Attendance View | Lihat kehadiran pasukan | Supervisor, Manager |
| Leave Request | Ajukan cuti | Staff (self), Manager (approve) |
| Leave Approval | Luluskan cuti | Manager, Owner |
| Shift Assignment | Tetapkan jadual shift | Supervisor, Manager |
| Overtime Record | Rekod waktu tambahan | Staff (request), Manager (approve) |
| Absence Alert | Amaran ketidakhadiran | Manager, Owner |

### 159.4 Shift Management

Sistem sokong pelbagai jenis shift:

- Fixed shift (shift tetap)
- Rotating shift (shift berpusing)
- Flexible hours (waktu fleksibel)
- On-call (panggilan sahaja)
- Remote work (kerja dari rumah)

Shift schedule diterbitkan kepada staf melalui:

- In-app notification
- Email
- SMS (optional)
- Calendar integration

---

## 160. Branch & Department Access — Skop Organisasi

### 160.1 Branch Structure

```
Organization
├── HQ (Headquarters)
│   ├── All departments accessible
│   └── Full financial access
├── Branch A
│   ├── Limited to Branch A data
│   └── Branch Manager has full Branch A access
├── Branch B
│   └── Same as Branch A
└── Pop-up / Temporary
    └── Time-limited access
```

### 160.2 Department Structure

Setiap organisasi boleh mempunyai jabatan berikut (atau custom):

| Department | Fungsi | Typical Roles |
|------------|--------|---------------|
| Operations | Operasi harian | Ops Manager, Branch Manager, Supervisor, Staff |
| Sales | Jualan dan biz dev | Sales Manager, Team Lead, Sales Exec |
| Marketing | Pemasaran dan brand | Marketing Manager, Content Creator, Designer |
| Finance | Kewangan dan akaun | Finance Manager, Accountant, Treasurer |
| HR | Sumber manusia | HR Manager, HR Exec, Training Officer |
| IT/Tech | Teknologi | IT Manager, System Admin, Developer |
| Customer Service | Sokongan pelanggan | CS Manager, CS Executive |
| Procurement | Pembelian dan bekalan | Procurement Manager, Buyer |
| Warehouse | Gudang dan logistik | Warehouse Manager, Storekeeper, Packer |
| Legal | Undang-undang | Legal Advisor, Compliance Officer |

### 160.3 Cross-Branch Access Rules

| Scenario | Access Rule |
|----------|-------------|
| HQ Manager | Akses semua cawangan |
| Branch Manager | Hanya cawangan sendiri (kecuali didelegasikan) |
| Department Head (HQ) | Akses data jabatan di semua cawangan |
| Staff | Hanya cawangan dan jabatan sendiri |
| Shared Staff | Akses ke cawangan yang dilibatkan (multiple branches) |
| Consultant/Vendor | Akses terhad kepada project/data tertentu |

### 160.4 Department-Level Permissions

Setiap jabatan mempunyai permission set tersendiri:

```
Department: Sales
├── view:leads:all (Sales Manager)
├── view:leads:own (Sales Exec)
├── create:proposals
├── update:deals
├── view:commission
└── export:reports (Manager+)

Department: Finance
├── view:ledger:all
├── create:journals
├── approve:payments (Finance Manager+)
├── view:tax_reports
├── export:financials (Owner+Finance Manager)
└── manage:bank_accounts (Owner only)
```

---

## 161. Approval Workflow — Had Kelulusan Berbeza

### 161.1 Approval Engine Design

Approval workflow berdasarkan:

1. **Action type** — apa tindakan dibuat
2. **Amount/value** — berapa nilai transaksi
3. **Requester role** — siapa memohon
4. **Branch/Department** — dari mana
5. **Risk level** — tahap risiko
6. **Tenant policy** — dasar organisasi

### 161.2 Approval Matrix Template

| Action | Auto-Approve | Level 1 | Level 2 | Level 3 |
|--------|--------------|---------|---------|---------|
| Discount ≤ 2% | Supervisor | - | - | - |
| Discount 3-5% | - | Branch Manager | - | - |
| Discount 6-10% | - | - | General Manager | - |
| Discount > 10% | - | - | - | Owner |
| Refund ≤ RM100 | Supervisor | - | - | - |
| Refund RM100-1,000 | - | Branch Manager | - | - |
| Refund RM1,000-5,000 | - | - | General Manager | - |
| Refund > RM5,000 | - | - | - | Owner |
| Purchase ≤ RM2,000 | - | Branch Manager | - | - |
| Purchase RM2,000-10,000 | - | - | General Manager | - |
| Purchase > RM10,000 | - | - | - | Owner |
| Hire Staff | - | Dept Manager | - | Owner |
| Terminate Staff | - | - | - | Owner |
| Salary Change ≤ 5% | - | - | General Manager | Owner |
| Salary Change > 5% | - | - | - | Owner |
| Contract Signing ≤ RM25K | - | Branch Manager | - | - |
| Contract Signing RM25K-100K | - | - | General Manager | - |
| Contract Signing > RM100K | - | - | - | Owner |

### 161.3 Approval Escalation

Jika approver tidak tindak balas dalam tempoh tertentu:

1. **Reminder** dihantar pada jam ke-4
2. **Escalation** kepada atasan pada jam ke-24
3. **Auto-escalate** sehingga tingkat tertinggi pada jam ke-72
4. **Emergency override** oleh Owner (dengan audit note)

### 161.4 Delegation of Approval

Manager boleh delegate kuasa kelulusan:

- Delegasi boleh ditetapkan untuk tempoh tertentu
- Delegasi boleh ditarik balik pada bila-bila masa
- Delegasi direkod dalam audit log
- Delegasi tidak boleh melebihi power delegator
- Sub-delegation (delegate to delegate) tidak dibenarkan tanpa owner approval

---

## 162. AI Assistant Permissions — AI Mengikuti Permission Manusia

### 162.1 Principle: AI Permission Mirror

Setiap AI assistant **mesti** mengikuti permission pemiliknya:

```
User Permission = AI Assistant Permission
```

AI tidak boleh melakukan atau melihat lebih daripada yang pemiliknya dibenarkan.

### 162.2 AI Permission Mapping

| User Role | AI Capability | AI Limitation |
|-----------|---------------|---------------|
| OWNER | AI boleh analisis semua data | Tiada batasan |
| MANAGER | AI boleh analisis jabatan/cawangan | Tidak boleh akses data organisasi penuh |
| BRANCH_MGR | AI boleh analisis cawangan | Tidak boleh akses cawangan lain |
| ADMIN | AI boleh bantu konfigurasi | Tidak boleh ubah system settings |
| SUPERVISOR | AI boleh bantu pasukan | Tidak boleh akses data kewangan |
| STAFF | AI boleh bantu tugas sendiri | Tidak boleh akses data lain |
| EXTERNAL | AI boleh bantu tempahan/order | Tidak boleh akses data dalaman |

### 162.3 AI Action Guardrails

Setiap tindakan AI mesti melalui check:

1. **Permission Check** — Adakah user dibenarkan?
2. **Scope Check** — Adakah dalam scope user?
3. **Approval Check** — Adakah tindakan memerlukan approval?
4. **Audit Log** — Adakah tindakan direkodkan?
5. **Rate Limit** — Adakah melebihi limit?

Jika mana-mana check gagal, AI **tidak boleh** melaksanakan tindakan.

### 162.4 AI-Specific Permissions

Selain human permissions, AI mempunyai permission tersendiri:

| AI Permission | Deskripsi |
|---------------|-----------|
| `ai:read:data` | Baca data yang dibenarkan |
| `ai:analyze:data` | Analisis data yang dibenarkan |
| `ai:generate:report` | Jana laporan berdasarkan data |
| `ai:create:draft` | Cipta draf (surat, proposal, dll) |
| `ai:send:message` | Hantar mesej (jika user allow) |
| `ai:execute:action` | Execute action (jika approved) |
| `ai:schedule:task` | Jadwalkan tugasan |
| `ai:learn:pattern` | Belajar pattern (anonymized) |
| `ai:access:memory` | Akses personal memory (user's own) |
| `ai:use:tools` | Gunakan tools yang dibenarkan |

### 162.5 AI Autonomy Levels

| Level | Deskripsi | Contoh |
|-------|-----------|--------|
| L0 — Read Only | AI hanya baca dan buat cadangan | "Based on sales data, recommend..." |
| L1 — Draft Only | AI boleh cipta draf, manusia lulus | Draf invois, draf surat |
| L2 — Execute with Approval | AI execute selepas approval | Hantar invois, book appointment |
| L3 — Autonomous with Monitoring | AI execute sendiri, audit trail | Lead outreach, stock reorder |
| L4 — Fully Autonomous | AI decide dan execute (rare) | Hanya untuk owner-configured low-risk tasks |

Autonomy level ditetapkan per AI agent oleh owner.

---

## 163. Audit, Security, Join/Transfer/Exit Lifecycle

### 163.1 Comprehensive Audit Trail

Setiap aktiviti mesti direkodkan:

```
Audit Event:
├── event_id (unique)
├── timestamp (ISO 8601)
├── actor_type (user / ai_agent / system)
├── actor_id (user_id / agent_id)
├── actor_name
├── action (verb)
├── resource_type (model name)
├── resource_id (record id)
├── resource_scope (branch, department)
├── outcome (success / failed / blocked)
├── ip_address
├── user_agent
├── request_id
├── approval_chain (if applicable)
│   ├── requested_by
│   ├── approved_by
│   ├── approved_at
│   └── approval_notes
├── before_state (JSON, for updates/deletes)
├── after_state (JSON, for creates/updates)
└── metadata (custom fields)
```

### 163.2 Audit Log Access

| Role | Can View Audit Log? | Can Modify? |
|------|---------------------|-------------|
| OWNER | Yes, all | No |
| MANAGER | Assigned areas only | No |
| ADMIN | Operational events only | No |
| SUPERVISOR | Team events only | No |
| STAFF | Own actions only | No |
| AUDITOR | Read-only, time-limited | No |
| SYSTEM | Write only | No |

**Audit logs tidak boleh diubah atau dipadam oleh sesiapa pun.** Owner boleh archive (not delete).

### 163.3 Security Controls

| Control | Deskripsi |
|---------|-----------|
| Multi-Factor Authentication (MFA) | Wajib untuk OWNER, MANAGER, ADMIN |
| Session Management | Timeout, concurrent session limit |
| IP Whitelisting | Optional untuk akses admin |
| Device Management | Track registered devices, remote revoke |
| Password Policy | Minimum complexity, rotation, history |
| Account Lockout | After failed attempts |
| Data Encryption | At rest and in transit |
| API Key Rotation | Regular rotation, expiry dates |
| Breach Detection | Anomaly detection, alerting |

### 163.4 Join Process — Menambah Staf Baru

```
1. Owner/Admin sends invitation
   ├── via email
   └── via SMS/link

2. Accepts invitation
   ├── Creates account (if new)
   ├── Links to existing account
   └── Completes profile

3. Role assignment
   ├── Select from standard roles
   └── Or assign custom role

4. Scope configuration
   ├── Branch assignment
   ├── Department assignment
   └── Approval limits (if managerial)

5. Onboarding checklist
   ├── Accept terms and policies
   ├── Complete training (if required)
   ├── AI assistant setup
   └── First login orientation

6. Activation
   ├── Account activated
   ├── Notifications configured
   └── Audit log recorded
```

### 163.5 Transfer Process — Menukar Jawatan/Cawangan

```
1. Initiate transfer
   ├── Role change
   ├── Branch transfer
   ├── Department transfer
   └── Promotion/Demotion

2. Approval required
   ├── Current manager approval
   ├── Receiving manager approval
   └── Owner approval (if role change)

3. Access transition
   ├── Old permissions revoked
   ├── New permissions granted
   └── Grace period (if needed)

4. Handover
   ├── Task handover list
   ├── Document handover
   └── Knowledge transfer notes

5. Confirmation
   ├── User acknowledges transfer
   ├── Audit log updated
   └── Notifications sent
```

### 163.6 Exit Process — Membuang Akses

#### 163.6.1 Voluntary Resignation

```
1. Resignation submission
   ├── Notice period calculation
   └── Last working day

2. Exit interview (optional)
   ├── Feedback collection
   └── Reason documentation

3. Access revocation
   ├── Day of last working: reduce access gradually
   ├── Final day: revoke all access
   └── Post-exit: account deactivated

4. Asset return
   ├── Company device
   ├── Access cards
   ├── Documents
   └── Digital assets

5. Final settlement
   ├── Outstanding leave
   ├── Final salary
   └── Certificate issuance
```

#### 163.6.2 Termination

```
1. Termination decision
   ├── By owner or authorized manager
   └── With documented reason

2. Immediate actions
   ├── Access suspended immediately
   ├── Device secured
   └── Team notified (minimal info)

3. Investigation (if applicable)
   ├── Evidence preservation
   ├── Audit log review
   └── Legal involvement (if needed)

4. Final processing
   ├── Final settlement
   ├── Legal compliance
   └── Record retention
```

#### 163.6.3 Account States

| State | Deskripsi | Access Level |
|-------|-----------|--------------|
| `ACTIVE` | Normal operation | Full according to role |
| `SUSPENDED` | Temporarily disabled | No access |
| `RESIGNING` | Notice period | Gradual reduction |
| `TERMINATED` | Permanently ended | No access, data retained |
| `INVITED` | Invitation sent, not accepted | No access |
| `DEACTIVATED` | Cleaned up after retention period | No access, data archived |

### 163.7 Industry-Specific Access Examples

#### Restoran

| Role | Akses Khusus |
|------|--------------|
| Owner | Semua data, termasuk recipe secrets, supplier contracts |
| Branch Manager (Outlet) | POS reports, staff scheduling, inventory outlet |
| Head Chef | Menu management, recipe access, kitchen staff |
| Cashier | POS transactions, cash handling, receipts |
| Waiter | Table orders, customer preferences (order-related only) |
| Supplier | Order placement, delivery schedule, invoice viewing |

#### Klinik

| Role | Akses Khusus |
|------|--------------|
| Owner | Semua data, financial records, patient analytics |
| Medical Director | Patient records, medical protocols, doctor schedules |
| Doctor | Patient records assigned, prescription management |
| Nurse | Patient vitals, appointment scheduling, medication prep |
| Receptionist | Appointments, billing, patient check-in |
| Pharmacist | Prescription fulfillment, medication inventory |
| Insurance | Claim submissions, coverage verification |

#### Kontraktor

| Role | Akses Khusus |
|------|--------------|
| Owner | All projects, financials, contracts, client data |
| Project Manager | Project-specific: budget, timeline, team, materials |
| Site Supervisor | Daily operations, safety, material usage, worker attendance |
| Quantity Surveyor | Cost estimation, variation orders, payment certificates |
| Engineer | Technical drawings, specifications, method statements |
| Worker | Assigned tasks, safety briefings, attendance, timesheet |
| Client | Project progress, milestones, payment status |

#### Kedai Runcit

| Role | Akses Khusus |
|------|--------------|
| Owner | All stores, financials, supplier contracts, franchise data |
| Store Manager | Single store: sales, inventory, staff, deliveries |
| Assistant Manager | Support store manager, specific departments |
| Cashier | POS, receipts, cash handling, returns |
| Stock Clerk | Inventory receiving, shelving, stocktakes |
| Delivery Driver | Delivery routes, order status, proof of delivery |
| Franchisee | Their store data, royalty payments, brand guidelines |

---

## 164. Data Model for Organizational Structure

### 164.1 Core Tables

```sql
-- Users and authentication
users (id, global_id, email, phone, password_hash, mfa_secret, status, created_at)
user_profiles (user_id, first_name, last_name, photo_url, timezone, language)

-- Organizations and tenants
tenants (id, tenant_id, name, status, plan, created_at)
organizations (id, tenant_id, name, legal_name, registration_number, industry_code, created_at)

-- Organizational structure
branches (id, organization_id, code, name, address, status, parent_branch_id)
departments (id, organization_id, code, name, description, head_user_id)
job_titles (id, organization_id, code, title, grade, description)

-- Memberships and roles
organization_memberships (
    id, user_id, organization_id, branch_id, department_id,
    role_code, job_title_id, status, joined_at, ends_at,
    approval_limit_currency, approval_limit_amount,
    created_at, updated_at
)
role_permissions (role_code, permission_code, scope_config, created_at)
user_custom_roles (id, organization_id, code, name, description, permissions_json, created_by)

-- Approvals
approval_requests (
    id, tenant_id, requester_id, action_type, resource_type, resource_id,
    requested_by, requested_amount, requested_branch_id,
    status, created_at
)
approval_responses (
    id, approval_request_id, approver_id, response, notes,
    approved_at, ip_address
)

-- Attendance and leave
employee_records (
    id, user_id, organization_id, branch_id, department_id,
    job_title_id, employment_type, join_date, probation_end, contract_end,
    reporting_to_user_id, status, created_at
)
attendance_records (id, employee_id, check_in, check_out, date, method, location)
leave_balances (id, employee_id, leave_type, annual_entitlement, used, carried_over)
leave_requests (id, employee_id, leave_type, start_date, end_date, reason, status)
shift_schedules (id, employee_id, date, shift_code, start_time, end_time, status)

-- Audit
audit_events (
    id, tenant_id, actor_type, actor_id, actor_name,
    action, resource_type, resource_id, resource_scope,
    outcome, ip_address, user_agent, request_id,
    before_state, after_state, metadata, created_at
)

-- AI Assistant configuration
ai_agent_configs (
    id, user_id, tenant_id, agent_type, enabled, autonomy_level,
    token_allocation_monthly, permission_scope_json, created_at
)
```

### 164.2 Index Strategy

Critical indexes for performance:

```sql
-- Membership lookups
CREATE INDEX idx_org_memberships_user ON organization_memberships(user_id);
CREATE INDEX idx_org_memberships_org ON organization_memberships(organization_id);
CREATE INDEX idx_org_memberships_active ON organization_memberships(organization_id, status);

-- Branch/department scoping
CREATE INDEX idx_org_memberships_branch ON organization_memberships(branch_id);
CREATE INDEX idx_org_memberships_dept ON organization_memberships(department_id);

-- Audit efficiency
CREATE INDEX idx_audit_events_tenant ON audit_events(tenant_id, created_at);
CREATE INDEX idx_audit_events_actor ON audit_events(actor_id, created_at);
CREATE INDEX idx_audit_events_action ON audit_events(action, created_at);

-- Approval workflow
CREATE INDEX idx_approval_requests_status ON approval_requests(status, created_at);
CREATE INDEX idx_approval_responses_request ON approval_responses(approval_request_id);
```

---

## 165. API Contracts for Access Management

### 165.1 User and Membership APIs

```
POST   /v1/auth/register              -- Register new user
POST   /v1/auth/login                 -- Login with MFA support
POST   /v1/auth/logout                -- Logout
POST   /v1/auth/refresh               -- Refresh token
POST   /v1/auth/forgot-password       -- Request password reset
POST   /v1/auth/reset-password        -- Reset password
GET    /v1/auth/me                    -- Get current user profile

POST   /v1/organizations/{id}/invitations       -- Invite user to organization
GET    /v1/organizations/{id}/invitations       -- List invitations
POST   /v1/invitations/{id}/accept              -- Accept invitation
POST   /v1/invitations/{id}/reject              -- Reject invitation

GET    /v1/organizations/{id}/members           -- List organization members
POST   /v1/organizations/{id}/members           -- Add member
GET    /v1/organizations/{id}/members/{uid}     -- Get member details
PUT    /v1/organizations/{id}/members/{uid}     -- Update member role/scope
DELETE /v1/organizations/{id}/members/{uid}     -- Remove member
PATCH  /v1/organizations/{id}/members/{uid}/suspend   -- Suspend member
PATCH  /v1/organizations/{id}/members/{uid}/activate  -- Activate member
```

### 165.2 Role and Permission APIs

```
GET    /v1/roles                          -- List standard roles
GET    /v1/roles/{code}                   -- Get role details with permissions
POST   /v1/organizations/{id}/custom-roles      -- Create custom role
GET    /v1/organizations/{id}/custom-roles        -- List custom roles
PUT    /v1/organizations/{id}/custom-roles/{code} -- Update custom role
DELETE /v1/organizations/{id}/custom-roles/{code} -- Delete custom role
GET    /v1/permissions                    -- List all available permissions
GET    /v1/permissions/by-category        -- Group permissions by category
```

### 165.3 Approval APIs

```
POST   /v1/approvals                      -- Create approval request
GET    /v1/approvals                      -- List approval requests (filtered by role)
GET    /v1/approvals/{id}                 -- Get approval request details
POST   /v1/approvals/{id}/respond         -- Approve or reject
POST   /v1/approvals/{id}/delegate        -- Delegate to another user
GET    /v1/approvals/templates            -- List approval templates
GET    /v1/approvals/{id}/history         -- Get approval chain history
```

### 165.4 Staff Management APIs

```
GET    /v1/organizations/{id}/branches          -- List branches
POST   /v1/organizations/{id}/branches          -- Create branch
PUT    /v1/organizations/{id}/branches/{id}     -- Update branch
DELETE /v1/organizations/{id}/branches/{id}     -- Close branch

GET    /v1/organizations/{id}/departments       -- List departments
POST   /v1/organizations/{id}/departments       -- Create department
PUT    /v1/organizations/{id}/departments/{id}  -- Update department
DELETE /v1/organizations/{id}/departments/{id}  -- Delete department

GET    /v1/employees                            -- List employees (scoped)
GET    /v1/employees/{id}                       -- Get employee profile
PUT    /v1/employees/{id}                       -- Update employee profile
POST   /v1/employees/{id}/transfer              -- Transfer employee
POST   /v1/employees/{id}/suspend               -- Suspend employee
POST   /v1/employees/{id}/terminate             -- Terminate employee
GET    /v1/employees/{id}/attendance            -- Get attendance records
POST   /v1/employees/{id}/attendance/check-in   -- Check in
POST   /v1/employees/{id}/attendance/check-out  -- Check out
GET    /v1/employees/{id}/leave                 -- Get leave balances and history
POST   /v1/employees/{id}/leave/request         -- Request leave
GET    /v1/shifts                               -- Get shift schedules
POST   /v1/shifts                               -- Create shift schedule
```

### 165.5 Audit APIs

```
GET    /v1/audit/events                       -- List audit events (scoped)
GET    /v1/audit/events/{id}                  -- Get specific event
GET    /v1/audit/export                       -- Export audit data (permission required)
GET    /v1/audit/anomalies                    -- Get detected anomalies
GET    /v1/audit/compliance-reports           -- Generate compliance reports
```

### 165.6 AI Assistant Permission APIs

```
GET    /v1/ai/config                          -- Get AI configuration for current user
PUT    /v1/ai/config                          -- Update AI configuration
GET    /v1/ai/permissions                     -- Get AI permission scope
POST   /v1/ai/actions                         -- Execute AI action (permission checked)
GET    /v1/ai/audit                           -- Get AI action audit log
GET    /v1/ai/token-usage                     -- Get token usage statistics
```

---

## 166. Acceptance Tests for Access Management

### 166.1 Role and Permission Tests

- [ ] Owner can view and modify all data across all branches and departments
- [ ] Manager cannot access financial records beyond their approval limit
- [ ] Branch manager cannot access data from other branches
- [ ] Admin cannot change tenant settings or subscription
- [ ] Admin cannot invite or remove owner-level users
- [ ] Staff cannot view salary information of other staff
- [ ] External users cannot access internal organizational data
- [ ] Custom roles can be created with specific permission sets
- [ ] Permission changes take effect immediately (no cache delay)
- [ ] Revoked access is instantly invalidated (token blacklisting works)
- [ ] MFA is enforced for owner, manager, and admin roles
- [ ] Session timeout applies correctly based on role sensitivity
- [ ] Concurrent session limits are enforced
- [ ] IP whitelist restrictions apply to admin accounts
- [ ] Password policy enforcement works (complexity, rotation, history)

### 166.2 Approval Workflow Tests

- [ ] Discount within supervisor limit is auto-approved
- [ ] Discount exceeding supervisor limit escalates to manager
- [ ] Payment exceeding manager limit escalates to owner
- [ ] Approval delegation works with expiry date
- [ ] Delegated approvals are logged in audit trail
- [ ] Sub-delegation is prevented without owner approval
- [ ] Approval reminders are sent at configured intervals
- [ ] Escalation to higher level works after timeout
- [ ] Emergency override by owner is logged with reason
- [ ] Rejected approvals cannot be overridden by requester
- [ ] Approval limits are currency-aware (MYR vs foreign currency)
- [ ] Approval state persists across system restarts

### 166.3 Staff Lifecycle Tests

- [ ] Invitation link expires after configured time
- [ ] Accepted invitation creates proper organization membership
- [ ] Role assignment during onboarding sets correct permissions
- [ ] Branch/department assignment scopes data access correctly
- [ ] Transfer process revokes old permissions and grants new ones
- [ ] Handover checklist must be completed before transfer finalization
- [ ] Resignation initiates gradual access reduction
- [ ] Terminated account has immediate access suspension
- [ ] Asset return tracking is enforced before final settlement
- [ ] Exited user's data remains intact and auditable
- [ ] Reactivation of terminated user requires owner approval
- [ ] Bulk import of employees preserves permission boundaries

### 166.4 AI Permission Tests

- [ ] AI reads only data the user is permitted to see
- [ ] AI cannot execute actions requiring approval without approval
- [ ] AI autonomy level is respected (L0-L4)
- [ ] AI token usage is tracked per user
- [ ] AI personal memory does not leak between users
- [ ] AI cannot escalate its own permissions
- [ ] AI actions are logged in audit trail with actor_type=ai_agent
- [ ] Suspended user's AI assistant is immediately disabled
- [ ] AI permission scope updates propagate in real-time
- [ ] AI cannot access data classified above user's clearance level

### 166.5 Audit and Security Tests

- [ ] Every create/update/delete action generates audit event
- [ ] Audit events cannot be modified or deleted
- [ ] Audit export requires explicit permission
- [ ] Anomaly detection flags unusual access patterns
- [ ] Failed login attempts are logged and trigger lockout
- [ ] Brute force protection activates after threshold
- [ ] API key rotation invalidates old keys immediately
- [ ] Data encryption is applied at rest and in transit
- [ ] Cross-tenant data access is impossible
- [ ] CORS restrictions prevent unauthorized cross-origin requests
- [ ] Rate limiting prevents abuse of public endpoints
- [ ] SQL injection attempts are blocked
- [ ] XSS attacks are neutralized by input sanitization
- [ ] CSRF tokens are validated on state-changing requests

### 166.6 Industry-Specific Access Tests

**Restoran:**
- [ ] Head chef cannot access financial records
- [ ] Cashier cannot view recipe secrets
- [ ] Supplier cannot see staff salaries
- [ ] Owner sees all outlet data consolidated

**Klinik:**
- [ ] Doctor sees only assigned patient records
- [ ] Receptionist cannot view medical diagnoses
- [ ] Pharmacist cannot access patient financial data
- [ ] Medical director sees all clinical data but not franchise data

**Kontraktor:**
- [ ] Site supervisor cannot access company-wide finances
- [ ] Quantity surveyor sees only assigned project costs
- [ ] Worker sees only assigned tasks and safety briefings
- [ ] Client sees only their project progress, not other clients

**Kedai Runcit:**
- [ ] Store manager sees only their store data
- [ ] Cashier cannot access inventory master pricing
- [ ] Franchisee sees only their store, not competitor franchises
- [ ] Owner sees consolidated multi-store analytics

---

## 167. Integration with Business Understanding Engine

### 167.1 Industry Blueprint → Access Mapping

When Business Understanding Engine completes discovery:

```
Industry Discovery Output
├── Industry type (restoran, klinik, kontraktor, etc.)
├── Organization size (1-10, 11-50, 51-200, 200+)
├── Number of branches
├── Department structure
├── Regulatory requirements
└── Custom roles identified

↓

Access Blueprint Generation
├── Recommended roles and permissions
├── Approval limits by industry standards
├── Department access matrix
├── Compliance-specific restrictions
├── Custom role definitions
└── AI assistant configuration per role

↓

Owner Review and Confirmation
├── Visual org chart preview
├── Permission summary per role
├── Approval workflow visualization
├── Compliance checklist
└── One-click accept or manual adjustment
```

### 167.2 Example: Restaurant Blueprint

```
Input: Restaurant, 3 outlets, 25 staff, F&B industry

Generated Access Structure:
├── Owner (1) — Full access
├── Area Manager (1) — All outlets, no recipe secrets
├── Outlet Managers (3) — Each outlet full access
├── Head Chefs (3) — Kitchen management, recipes, kitchen staff
├── Sous Chefs (6) — Assisted kitchen management
├── Cashiers (9) — POS, receipts, cash handling
├── Wait Staff (6) — Orders, table management
└── Cleaner (7) — Schedule, assignments only

Approval Limits:
├── Cashier: Refund ≤ RM50
├── Wait Staff: Discount ≤ 5% (for complaint resolution)
├── Outlet Manager: Refund ≤ RM500, Discount ≤ 15%
├── Area Manager: Refund ≤ RM2,000, Discount ≤ 25%
└── Owner: Unlimited

Compliance Requirements:
├── Food handler certification tracking
├── Health inspection records
├── Halal certification status (if applicable)
└── Alcohol license tracking (if applicable)
```

### 167.3 Example: Clinic Blueprint

```
Input: Medical clinic, 1 branch, 15 staff, healthcare industry

Generated Access Structure:
├── Owner/Partner (2) — Full access
├── Medical Director (1) — All patient records, clinical protocols
├── Doctors (4) — Assigned patients, prescriptions
├── Nurses (4) — Patient vitals, appointment prep
├── Receptionists (2) — Appointments, billing, check-in
├── Pharmacist (1) — Prescriptions, medication inventory
└── Billing Officer (1) — Insurance claims, payments

Approval Limits:
├── Receptionist: Appointment reschedule only
├── Nurse: Medication dispensing (per prescription)
├── Doctor: Treatment plans, referrals, prescriptions
├── Medical Director: Procedure approvals, specialist referrals
└── Owner: Financial decisions, facility changes

Compliance Requirements:
├── HIPAA/Medical confidentiality enforcement
├── Prescription audit trail
├── Patient consent records
├── Medical waste disposal tracking
└── License expiry monitoring (doctors, clinic)
```

---

## 168. Implementation Roadmap for Access Management

### Phase A — Foundation (Weeks 1-4)

| Task | Description | Priority |
|------|-------------|----------|
| Database schema | Create all tables from Section 164 | High |
| Migration scripts | Version-controlled migrations | High |
| Basic RBAC | Role creation, permission assignment | High |
| Middleware | Authorization middleware for API | High |
| Owner supremacy | Hard-coded owner privileges | High |
| Basic audit | Log all auth events | High |

### Phase B — Structure (Weeks 5-8)

| Task | Description | Priority |
|------|-------------|----------|
| Branch/Department | CRUD and scoping | High |
| Organization memberships | Multi-tenant membership management | High |
| Approval engine | Basic approval workflow | High |
| Staff profiles | Employee record management | Medium |
| Attendance | Check-in/out, leave requests | Medium |
| Custom roles | Owner-defined roles | Medium |

### Phase C — Lifecycle (Weeks 9-12)

| Task | Description | Priority |
|------|-------------|----------|
| Join process | Invitation, onboarding flow | High |
| Transfer process | Role/branch transfer with handover | High |
| Exit process | Resignation, termination workflows | High |
| Account states | Active, suspended, resigned, terminated | High |
| Asset tracking | Digital asset return tracking | Medium |
| Bulk operations | CSV import/export for staff | Low |

### Phase D — AI Integration (Weeks 13-16)

| Task | Description | Priority |
|------|-------------|----------|
| AI permission mirror | AI follows user permissions | High |
| Autonomy levels | L0-L4 configuration per agent | High |
| AI audit logging | All AI actions logged | High |
| Token management | Monthly allocation tracking | Medium |
| AI config API | Manage AI settings per user | Medium |

### Phase E — Industry Blueprints (Weeks 17-20)

| Task | Description | Priority |
|------|-------------|----------|
| Restaurant blueprint | Pre-configured access structure | High |
| Clinic blueprint | Healthcare-specific access | High |
| Construction blueprint | Project-based access | Medium |
| Retail blueprint | Multi-store access model | Medium |
| Blueprint UI | Visual org chart builder | Medium |
| Blueprint wizard | Guided setup for new tenants | High |

### Phase F — Security Hardening (Weeks 21-24)

| Task | Description | Priority |
|------|-------------|----------|
| MFA enforcement | TOTP-based 2FA for sensitive roles | High |
| Session management | Timeout, concurrent limits, device tracking | High |
| IP whitelisting | Optional admin IP restriction | Medium |
| Password policy | Complexity, rotation, breach checking | High |
| Anomaly detection | Unusual access pattern alerts | Medium |
| Penetration testing | Third-party security audit | High |

### Phase G — Audit and Compliance (Weeks 25-28)

| Task | Description | Priority |
|------|-------------|----------|
| Comprehensive audit | Full audit trail implementation | High |
| Audit export | Secure data export with permission checks | High |
| Compliance reports | Industry-specific compliance dashboards | Medium |
| Retention policies | Automatic archival and cleanup | Medium |
| GDPR/MDPA compliance | Data privacy compliance features | High |
| Audit UI | Searchable, filterable audit log interface | High |

### Phase H — Testing and Launch (Weeks 29-32)

| Task | Description | Priority |
|------|-------------|----------|
| Acceptance tests | Run all tests from Section 166 | High |
| UAT with beta tenants | Real-world testing with pilot customers | High |
| Performance testing | Load testing for permission checks | High |
| Documentation | Admin guide, API docs, user guides | High |
| Training materials | Video tutorials, screenshots | Medium |
| Launch preparation | Production deployment checklist | High |

---

## 169. Product Rules — Access Management

| # | Rule | Enforcement |
|---|------|-------------|
| 1 | **Name ≠ Power** | Role name never determines access. Permission codes do. |
| 2 | **Owner Always Supreme** | Owner can override any permission, at any time. |
| 3 | **Need-to-Know Default** | New users get zero access until explicitly granted. |
| 4 | **Scope Is Mandatory** | Every permission check includes branch/department/record scope. |
| 5 | **Approval Escalation** | Unanswered approvals escalate automatically. |
| 6 | **AI Mirrors Human** | AI permission set is always a subset of user permission set. |
| 7 | **Audit Immutable** | Audit events cannot be modified or deleted by anyone. |
| 8 | **Lifecycle Required** | Join, transfer, exit processes are mandatory for all access changes. |
| 9 | **Industry Blueprint** | New tenants get role/access recommendations based on industry. |
| 10 | **Custom Allowed** | Owners can create custom roles beyond standard set. |
| 11 | **MFA for Sensitive** | MFA required for owner, manager, and admin roles. |
| 12 | **Session Limits** | Concurrent sessions and timeouts enforced by role sensitivity. |
| 13 | **Data Classification** | Access respects data classification levels. |
| 14 | **No Silent Changes** | All permission changes notify affected users and owner. |
| 15 | **Cross-Tenant Wall** | Zero data leakage between tenants, regardless of shared user. |

---

## 170. Open Decisions — Access Management

| # | Decision | Options | Recommendation | Rationale |
|---|----------|---------|----------------|-----------|
| 1 | Ownership transfer mechanism | Direct transfer, auction, board vote | Direct transfer with dual confirmation | Simplest for SME market |
| 2 | Multi-owner support | Yes, with dual approval | Yes | Larger organizations need it |
| 3 | Approval automation | Manual only, semi-auto, fully auto | Semi-auto with escalation | Balance speed and control |
| 4 | AI autonomy ceiling | Max L2, L3, or L4 | L3 with owner opt-in to L4 | Safety first, flexibility optional |
| 5 | External user authentication | Separate DB, SSO, or linked | Linked with separate scope | Easier UX, same identity platform |
| 6 | Audit retention period | 1 year, 3 years, 7 years, indefinite | 7 years minimum, indefinite for financial | Malaysian regulatory requirement |
| 7 | Password policy source | System default, tenant configurable, hybrid | Hybrid with system minimum | Consistency + flexibility |
| 8 | Custom role limits | Unlimited, capped at 20, capped at 50 | Uncapped but audit every 10 custom roles | Flexibility with governance |

---

## 171. Recommended Document Set for Access Management

For implementation teams, prepare:

1. **Database Schema Diagram** — ERD for all access-related tables
2. **Permission Matrix Spreadsheet** — Complete grid of role × permission
3. **Approval Workflow Diagrams** — Flowcharts for each approval type
4. **Org Chart Builder UI Mockup** — Visual design for owner setup
5. **Invitation Flow Wireframe** — Step-by-step onboarding design
6. **Transfer Process Screen Flow** — UI for role/branch transfers
7. **Exit Checklist Template** — Form for termination procedures
8. **Industry Blueprint Templates** — Pre-configured structures per sector
9. **API Specification Document** — Detailed endpoint documentation
10. **Security Architecture Document** — Threat model and controls
11. **Compliance Checklist** — MDPA, industry regulations mapping
12. **Test Case Repository** — Automated and manual test cases

---

## 172. Glossary — Access Management Terms

| Term | Definition |
|------|-----------|
| **Owner** | Pemilik perniagaan dengan kawalan tertinggi dalam organisasi |
| **Tenant** | Unit multi-tenancy dalam sistem AI BIJAN |
| **Organization** | Entiti perniagaan dalam sistem (syarikat, NGO, dll) |
| **Branch** | Cawangan atau lokasi operasi organisasi |
| **Department** | Jabatan atau unit fungsi dalam organisasi |
| **Role** | Kumpulan tanggungjawab dengan set permission tertentu |
| **Permission** | Tindakan individu yang dibenarkan pada sumber tertentu |
| **Entitlement** | Ketersediaan fungsi berdasarkan pelan atau konfigurasi |
| **Scope** | Batasan ruang (branch, department, record) pada permission |
| **Approval Limit** | Had kuasa kelulusan mengikut nilai atau jenis tindakan |
| **Audit Log** | Rekod tidak boleh ubah bagi semua aktiviti sistem |
| **MFA** | Multi-Factor Authentication — pengesahan identiti dua langkah |
| **RBAC** | Role-Based Access Control — model kawalan akses berasaskan peranan |
| **ABAC** | Attribute-Based Access Control — model berasaskan atribut (future) |
| **Join** | Proses menambah pengguna baru ke organisasi |
| **Transfer** | Proses menukar peranan, cawangan atau jabatan pengguna |
| **Exit** | Proses membuang atau menggantung akses pengguna |
| **Custom Role** | Peranan yang dicipta oleh pemilik melebihi peranan standard |
| **AI Permission Mirror** | Prinsip bahawa AI mengikuti permission pemiliknya |
| **Autonomy Level** | Tahap kebebasan AI bertindak tanpa kelulusan manusia (L0-L4) |

---

## 173. References and Standards

### 173.1 Malaysian Regulations

| Regulation | Relevance |
|------------|-----------|
| **Personal Data Protection Act 2010 (PDPA)** | Employee data, customer data handling |
| **Employment Act 1955** | Staff records, leave, attendance requirements |
| **Industrial Relations Act 1967** | Termination procedures, disciplinary records |
| **Occupational Safety and Health Act 1994** | Workplace safety records, incident reporting |
| **Companies Act 2016** | Organization registration, director records |
| **Sherlan Enactments (various states)** | Local licensing, permit requirements |
| **Halal Certification Requirements** | Food industry specific access controls |
| **Medical Development Act 1971** | Healthcare data access restrictions |

### 173.2 International Standards

| Standard | Relevance |
|----------|-----------|
| **ISO 27001** | Information security management |
| **ISO 27002** | Security controls implementation |
| **NIST SP 800-53** | Security and privacy controls |
| **SOC 2** | Service organization controls |
| **GDPR** | Data privacy (reference for PDPA alignment) |

---

**Akhir Part XXXIII — Business Access Management & Organizational Structure**

**Status:** Spesifikasi lengkap untuk pengurusan akses dan struktur organisasi. Komponen wajib untuk semua sektor perniagaan dalam AI BIJAN.

**Next Steps:**
1. Implement database schema (Phase A)
2. Build basic RBAC engine (Phase A)
3. Develop industry blueprint templates (Phase E)
4. Create visual org chart builder (Phase E)
5. Integrate with Business Understanding Engine (Section 167)
6. Run acceptance tests (Section 166)
7. Security audit and penetration testing (Phase F)
8. Production deployment (Phase H)

**Key Takeaway:** Setiap sektor perniagaan mempunyai struktur akses berbeza, tetapi semua menggunakan enjin keselamatan yang sama. Peranan yang sama tidak semestinya mempunyai kuasa yang sama dalam setiap syarikat. Kuasa ditentukan melalui permission, skop organisasi dan dasar kelulusan — bukan berdasarkan nama jawatan sahaja.
