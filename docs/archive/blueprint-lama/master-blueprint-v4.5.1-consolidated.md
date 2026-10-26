# AI BIJAN SaaS — Dokumen Induk Lengkap

## Produk, Seni Bina, Industri & Pelan Pembangunan

**Versi:** 4.5.1  
**Tarikh Konsolidasi:** 2026-10-19  
**Nama Produk:** AI BIJAN SaaS  
**Tagline:** *Your AI Business Team, in the Room.*  
**Pemilik Konsep:** Bijan  
**Bahasa Utama:** Bahasa Melayu dan English  
**Model Produk:** Multi-tenant SaaS, modular, API-first, industry-aware  
**Pasaran Utama:** Malaysia  
**Admin Hierarchy:** Master → Super → White-label → Customer  
**Platform Features:** Brand portfolio, revenue sharing, white-label, subscription engine, AI reseller  

---

## Status Dokumen

> **Status:** Dokumen gabungan induk yang menyatukan SEMUA dokumen perancangan AI BIJAN.
> 
> **Sumber asal:**
> - AI BIJAN SaaS Master Blueprint v4.5.1 (Multi-Tier Admin & Platform Business)
> - Architecture v2.5 MASTER CONSOLIDATED (Seni bina teknikal, AI Gateway, Media Studio)
> - Architecture v2.4 (Shared Agent Memory & Inter-Agent Collaboration)
> - Architecture v2.3 (AI Provider & Token Reseller)
> - Architecture v2.2 (Universal SME Tools & Industry Packs)
> - Architecture v2.1 (Original Blueprint)
> - Ecosystem V4.5 (Struktur 7 Kumpulan & 18 B-Modules)
> - Customer Side Experience (Portal pelanggan & Personal AI)
> - Malaysia Industry Coverage V4.0 (Direktori industri & MSIC)
> - B-Personal AI Assistant V4.2 (Autonomous Business Engine)
> 
> **Prinsip utama:** Sistem berdiri sendiri (stand-alone), multi-tenant, berasaskan AI, sesuai untuk hosting cPanel.  
> **Keperluan wajib:** AI BIJAN dibangunkan sebagai sistem sendiri. **ERPNext bukan dependency**, bukan backend, bukan pangkalan data aplikasi, bukan UI, dan bukan enjin operasi yang diperlukan untuk menjalankan AI BIJAN. Frappe dan Frappe HR juga bukan dependency.
> 
> **Perhatian:** Dokumen ini ialah spesifikasi perancangan. Ia bukan bukti bahawa setiap fungsi telah dikodkan, diuji, diluluskan dari sudut pematuhan atau tersedia kepada pelanggan.

---

## Struktur Dokumen

| Bahagian | Kandungan |
|----------|-----------|
| **A** | Visi Produk & Seni Bina Platform |
| **B** | Shared Core (Auth, Tenant, RBAC, Subscription) |
| **C** | 18 B-Modules — Business Operations |
| **D** | Industry Blueprint Engine & Direktori Industri |
| **E** | B-Brain Hub — AI Agents & Autonomous Systems |
| **F** | Customer Portal & Personal AI Assistant |
| **G** | Platform Business & Multi-Tier Admin |
| **H** | Integrasi, API & Infrastruktur |
| **I** | Roadmap Pembangunan & Definition of Done |

---

# BAB A — VISI PRODUK & SENI BINA PLATFORM

## 1. Ringkasan Eksekutif

AI BIJAN ialah platform SaaS untuk membantu perusahaan, PKS, peniaga, penyedia perkhidmatan dan organisasi di Malaysia mengurus operasi melalui satu ruang kerja bersepadu.

Platform menggabungkan:

- **Operasi dalaman** — Virtual Office, HRMS, Projects, Assets, Sign & Docs
- **CRM & komunikasi pelanggan** — Omnichannel, Helpdesk, Loyalty
- **Jualan, POS, invoicing & payment** — POS, Marketplace, Site, Link, Invoicing
- **Inventory, procurement, warehouse & logistics** — Inventory, COGS, BOM/Manufacturing, Procurement, Logistics
- **Industry-specific workflows** — School Transport, Event & Rental, Construction, Automotive, Agriculture dll
- **Reporting, analytics & business intelligence** — Analytics dashboard dan reports
- **AI agents, automations & knowledge retrieval** — B-Brain Hub dengan agent registry dan tool execution
- **Industry Blueprint Engine** — Onboarding automatik mengikut jenis perniagaan
- **Subscription billing, usage metering & AI Token Wallet** — Pelan langganan, kuota penggunaan, wallet kredit AI

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

- Semua modul telah dibangunkan;
- Semua integrasi mempunyai akses API yang diluluskan;
- Semua kod MSIC telah dipetakan secara rasmi;
- Semua aliran kewangan mematuhi setiap keperluan yang terpakai;
- Semua AI agents memberikan jawapan tepat;
- Semua modul sesuai untuk aktiviti terkawal atau berlesen;
- Semua fungsi beroperasi dengan baik dalam production.

Setiap dakwaan mesti disokong oleh implementasi, ujian dan dokumentasi.

### 1.3 Pengguna Sasaran

- Usahawan solo dan freelancer
- PKS Malaysia
- Agensi digital dan penyedia perkhidmatan
- Syarikat sederhana yang memerlukan kawolan akses, audit dan integrasi

### 1.4 Model Komersial

- Langganan bulanan/tahunan mengikut tenant
- Had penggunaan AI, automasi, pengguna, saluran dan integrasi mengikut pelan
- Modul tambahan berbayar (add-on)
- Caj penggunaan bagi tindakan tertentu apabila sesuai
- White-label/partner plan sebagai fasa komersial kemudian

---

## 2. Prinsip Produk & Batasan

### 2.1 Prinsip Wajib

| # | Prinsip | Penerangan |
|---|---------|------------|
| 1 | **One Shared Core, Many Business Modules** | Semua industri berkonggi asas platform, tetapi tidak dipaksa menggunakan workflow yang sama |
| 2 | **Industry-aware, not industry-restricted** | Satu organisasi boleh menjalankan beberapa aktiviti perniagaan, cawangan dan jenama |
| 3 | **API-first** | Web, aplikasi mudah alih, integrasi dan ejen AI menggunakan kontrak API yang ditetapkan |
| 4 | **Secure by default** | Setiap permintaan mesti melalui pengesahan, skop tenant, permission dan entitlement |
| 5 | **AI with controlled autonomy** | AI boleh membantu membuat keputusan, tetapi tindakan berisiko memerlukan kelulusan manusia |
| 6 | **Source of truth** | Setiap jenis rekod rasmi mempunyai pemilik data yang jelas |
| 7 | **Configuration before custom code** | Perbezaan kecil industri diselesaikan melalui blueprint; perbezaan domain sebenar memerlukan modul khusus |
| 8 | **Evidence before production claims** | Modul tidak boleh dilabel production-ready hanya kerana halaman UI wujud |
| 9 | **Malaysia-first** | Keperluan Malaysia mesti disahkan dengan sumber rasmi yang berkaitan |
| 10 | **Phased delivery** | Lengkapkan aliran perniagaan sebenar sebelum mengembangkan bilangan modul |

### 2.2 Istilah Status

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

### 2.3 Stack Teknologi

| Komponen | Teknologi | Catatan |
|----------|-----------|---------|
| Backend | Laravel 11+, PHP 8.3+ | Modular monolith |
| Database | MySQL 8.x / MariaDB 10.6+ | Transactional data |
| Cache/Queue | Redis + Laravel Horizon | Background jobs |
| Realtime | Laravel Reverb | Chat, status, notifications |
| Frontend | Next.js 15 + React 19 + Tailwind CSS | User-facing screens |
| Admin Panel | Filament PHP | Platform HQ & tenant admin |
| Object Storage | Cloudflare R2 / AWS S3 | Files, images, documents |

---

## 3. Model Seni Bina Platform

### 3.1 Lapisan Platform

```
AI BIJAN PLATFORM
│
├── Experience Layer
│   ├── Web Application (Next.js 15)
│   ├── Mobile Application (Flutter)
│   ├── Admin / Platform HQ (Filament PHP)
│   ├── Customer / Partner Portals
│   └── API Clients
│
├── Shared Core
│   ├── Authentication & Security (B-Auth)
│   ├── Tenant / Organization / Branch
│   ├── Roles & Permissions (RBAC)
│   ├── Subscription & Entitlements
│   ├── Billing & Usage Metering
│   ├── Notifications
│   ├── Files & Documents
│   ├── Audit Trail
│   ├── API & Webhooks
│   └── Localization
│
├── Business Modules (18 B-Modules)
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
│   ├── AI Gateway (multi-provider)
│   ├── Model Catalog
│   ├── Agent Runtime
│   ├── Knowledge Retrieval
│   ├── Tool Registry
│   ├── Approval Policies
│   └── AI Usage Ledger / Token Wallet
│
├── Industry Blueprint Engine
│   ├── Industry Directory (MSIC-mapped)
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

### 3.2 Peraturan Pemilikan Data

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
| Invois dan quotation | B-Invoicing |
| Rekod kewangan | B-Finance |
| Pekerja dan cuti | B-HRMS |
| Projek dan milestone | B-Projects |
| Aset fizikal | B-Assets |
| Penghantaran | B-Logistics |
| Operasi acara | B-Event & Rental |

Modul lain tidak boleh mengubah jadual domain tersebut secara terus tanpa kontrak yang diluluskan.

### 3.3 Keputusan Seni Bina Utama

1. **Satu platform dan satu codebase backend** — bukan 18 aplikasi berasingan
2. **Modular monolith dahulu** — microservices hanya apabila keperluan operasi dan skala membuktikannya
3. **Setiap modul memiliki data dan kontraknya sendiri**
4. **Modul berkomunikasi melalui kontrak awam dan domain events**, bukan query terus ke jadual modul lain
5. **API-first dengan OpenAPI** sebagai kontrak antara backend, Next.js, Flutter, integrasi dan AI
6. **Keselamatan tenant, audit dan kawalan tindakan AI** dibina sejak awal
7. **Google Ads dan Meta Ads** dimasukkan sebagai integrasi rasmi dalam B-Marketing Ads Manager
8. **Infrastruktur mahal seperti ClickHouse dan Qdrant** diperkenalkan apabila kes penggunaan memerlukannya

---

# BAB B — SHARED CORE

## 4. B-Auth & Security

### 4.1 Skop

- Pendaftaran dan log masuk
- Pengesahan e-mel atau nombor telefon
- Password reset
- Multi-factor authentication (2FA)
- Pengurusan sesi dan token (Laravel Sanctum)
- Social login (jika diperlukan)
- SSO untuk enterprise tenant

### 4.2 Role-Based Access Control (RBAC)

Menggunakan `spatie/laravel-permission`:

- **Roles** — predefined dan custom roles
- **Permissions** — granular permissions per modul
- **Teams/Tenant isolation** — setiap role scoped kepada tenant
- **Permission inheritance** — hierarchical permissions

### 4.3 Keamanan Tambahan

- Input validation dan sanitization
- Rate limiting pada API endpoints
- CSRF protection
- XSS prevention
- SQL injection protection (Eloquent ORM)
- Encryption untuk sensitive data (tokens, credentials)
- Audit trail untuk semua auth events

---

## 5. Tenant, Organization & Branch

### 5.1 Model Data

- `tenants` — syarikat/pelanggan SaaS
- `organizations` — struktur organisasi dalam tenant
- `branches` — cawangan
- `memberships` — hubungan user-tenant-org
- `users` — global user identity

### 5.2 Pengasingan Data

- Setiap query auto-scoped kepada `tenant_id`
- Policy classes untuk enforce access control
- Database-level row-level security (via scopes)
- Cache keys prefixed dengan tenant identifier

---

## 6. Subscription, Billing & Usage Metering

### 6.1 Pelan Langganan

- Monthly dan annual billing cycles
- Feature toggles per plan tier
- Module entitlements (which modules aktif)
- User limits, storage limits, API rate limits
- Trial periods dan upgrade/downgrade flows

### 6.2 AI Token Wallet

Sistem kredit AI berasingan daripada langganan SaaS:

- Wallet per tenant
- Top-up bila-bila masa
- Usage tracking per AI call
- Low balance alerts
- Auto-suspend jika balance sifar (optional)
- Package pricing (token bundles)

### 6.3 Usage Metering

- Track module usage per tenant
- Overage billing jika melebihi had
- Real-time usage dashboard
- Export usage reports

---

## 7. Notifications

- Email notifications (SMTP/SES)
- Push notifications (FCM/APNs)
- In-app notifications
- Webhook notifications
- Template-based notification system
- Notification preferences per user

---

## 8. Files & Document Storage

- Cloudflare R2 / AWS S3 integration
- Signed URLs untuk secure access
- Document templates (PDF, Word, Excel)
- Electronic signature support
- Version control untuk documents
- Retention policies

---

## 9. Audit Trail

Semua perubahan kritikal direkodkan:

- Financial transactions
- Stock adjustments
- Payroll changes
- Access logs
- AI actions (tool calls, decisions)
- Document modifications
- User activity

---

## 10. API, Webhooks & Integrations

### 10.1 API Design

- RESTful API dengan versioning (`/api/v1/`, `/api/v2/`)
- OpenAPI/Swagger documentation
- API key management
- OAuth 2.0 untuk third-party integrations
- Request/response logging

### 10.2 Webhooks

- Configurable webhook endpoints
- Event-driven notifications
- Retry mechanism dengan exponential backoff
- Signature verification
- Delivery status tracking

### 10.3 Connector Model

Data model untuk integrations:

- `integrations` — registered connectors
- `integration_accounts` — connected accounts per tenant
- `integration_credentials` — encrypted tokens/secrets
- `integration_sync_runs` — sync job tracking
- `integration_webhook_endpoints` — webhook configuration
- `integration_events` — events received/sent
- `integration_errors` — error logging
- `integration_scopes` — permitted data scopes

Token OAuth disimpan terenkripsi, mempunyai proses refresh/revoke dan tidak dimasukkan ke log biasa.

---

# BAB C — 18 B-MODULES

## 11. Struktur 7 Kumpulan & 18 B-Modules

```
AI BIJAN SaaS
├── 1. Operasi Dalaman & Pasukan
│   ├── B-Virtual Office
│   ├── B-HRMS
│   ├── B-Sign & Docs
│   ├── B-Projects
│   └── B-Assets
│
├── 2. Jualan, Saluran & Pertumbuhan
│   ├── B-POS System
│   ├── B-Site
│   ├── B-Link
│   ├── B-Marketplace
│   └── B-Affiliate
│
├── 3. Pelanggan, Pemasaran & Sokongan
│   ├── B-Loyalty
│   ├── B-Omnichannel
│   ├── B-Booking
│   ├── B-Marketing
│   └── B-Helpdesk / B-Desk
│
├── 4. Stok, Pengeluaran & COGS
│   ├── B-COGS Engine
│   ├── B-Inventory & Warehouse
│   └── B-BOM & Manufacturing
│
├── 5. Kewangan, Pembelian & Logistik
│   ├── B-Finance
│   ├── B-Invoicing
│   ├── B-Procurement
│   └── B-Logistics
│
├── 6. Kebolehcapaian & Infrastruktur
│   ├── B-Connect / API Gateway
│   └── B-Auth & Security
│
└── 7. Dashboard & Intelligence
    ├── B-Analytics
    └── B-Brain Hub (AI Engine)
```

---

## Kumpulan 1: Operasi Dalaman & Pasukan

### 11.1 B-Virtual Office

Ruang kerja syarikat dengan pasukan AI:

- Office Agents (AI agents dengan peranan spesifik)
- Office Tasks (tugasan dengan kanban board)
- Announcements & memos
- SOP repository
- Decision inbox (Peti Keputusan)
- Agent scheduling & monitoring

### 11.2 B-HRMS

Pengurusan sumber manusia lengkap:

- Employee database
- Attendance tracking (biometric, mobile, web)
- Leave management (types, approvals, balances)
- Payroll Malaysia (EPF, SOCSO, EIS, PCB)
- Shift management
- Onboarding/offboarding workflows
- Performance reviews
- Training & certifications

### 11.3 B-Sign & Docs

Dokumen dan tandatangan elektronik:

- Document templates (Malaysia formats)
- E-signature integration
- Document approval workflows
- Version control
- Digital stamp/seal
- Export: PDF, Word, Excel

### 11.4 B-Projects

Pengurusan projek:

- Project creation & planning
- Kanban board
- Gantt charts
- Milestone tracking
- Time tracking
- Resource allocation
- Document sharing
- Client collaboration portal

### 11.5 B-Assets

Pengurusan aset fizikal:

- Asset register
- Depreciation calculation
- Maintenance scheduling
- Asset assignment to employees
- Asset disposal
- Barcode/QR tagging

---

## Kumpulan 2: Jualan, Saluran & Pertumbuhan

### 11.6 B-POS System

Point of Sale untuk retail/F&B:

- Touch-screen interface
- Multiple payment methods (FPX, card, cash, e-wallet)
- QR Order (table ordering)
- Receipt printing & emailing
- Offline mode
- Multi-outlet support
- Integration dengan inventory & finance

### 11.7 B-Site

Website builder untuk perniagaan:

- Drag-and-drop builder
- Pre-built templates
- SEO optimization
- Landing page creator
- Sales funnel builder
- E-commerce capability
- Custom domain support

### 11.8 B-Link

Micro-site untuk pautan bio:

- Link-in-bio page
- Product catalog
- QR code generator
- Analytics on link clicks
- Social media integration

### 11.9 B-Marketplace

Storefront untuk jualan online:

- Product catalog
- Shopping cart
- Checkout flow
- Order management
- Shipping integration
- Payment gateway integration

### 11.10 B-Affiliate

Program afiliaat & dropship:

- Affiliate registration
- Commission tracking
- Dropship management
- Referral links
- Payout management
- Performance dashboard

---

## Kumpulan 3: Pelanggan, Pemasaran & Sokongan

### 11.11 B-Loyalty

Program kesetiaan pelanggan:

- Points earning & redemption
- Tier system (bronze, silver, gold, platinum)
- Coupon/voucher management
- Promotional campaigns
- Birthday rewards
- Partner network (cross-business loyalty)

### 11.12 B-Omnichannel

Centralized customer communication:

- Unified inbox (WhatsApp, Instagram, Facebook, email)
- Conversation threading
- Customer profile aggregation
- Automated responses (AI-powered)
- Escalation workflows
- SLA tracking

### 11.13 B-Booking

Appointment & service booking:

- Service catalog with slots
- Calendar view
- Deposit/payment collection
- Reminders (SMS, email, WhatsApp)
- Staff assignment
- Cancellation & rescheduling
- No-show management

### 11.14 B-Marketing

Marketing automation:

- Audience segmentation
- Campaign creation (email, SMS, WhatsApp)
- Content generation (AI-assisted)
- Campaign scheduling
- Performance analytics
- A/B testing
- Google Ads & Meta Ads integration
- ROI tracking

### 11.15 B-Helpdesk / B-Desk

Customer support ticketing:

- Ticket creation (多渠道)
- Priority & SLA management
- Knowledge base
- Automated routing
- Customer self-service portal
- Satisfaction surveys

---

## Kumpulan 4: Stok, Pengeluaran & COGS

### 11.16 B-COGS Engine

Cost of Goods Sold calculation:

- Automatic cost calculation per sale
- Margin analysis per SKU
- Material cost tracking
- Labor cost allocation
- Overhead distribution
- Profitability reports

### 11.17 B-Inventory & Warehouse

Stock management:

- Real-time stock levels
- Multi-location support
- Batch/expiry tracking
- Stock adjustments
- Stock transfers between locations
- Low stock alerts
- Inventory valuation (FIFO, weighted average)

### 11.18 B-BOM & Manufacturing

Bill of Materials & production:

- Recipe/BOM management
- Work orders
- Production costing
- Raw material requirements
- Yield tracking
- Quality control checkpoints

---

## Kumpulan 5: Kewangan, Pembelian & Logistik

### 11.19 B-Finance

Accounting core:

- Chart of accounts (Malaysia standard)
- Journal entries
- General ledger
- Trial balance
- Profit & Loss statement
- Balance sheet
- Cash flow statement
- Financial period locking
- Multi-currency support

### 11.20 B-Invoicing

Billing & invoicing:

- Quotation creation
- Invoice generation
- Credit notes
- Recurring invoices
- e-Invois LHDN integration (JSON format)
- Payment reminders
- Aging report

### 11.21 B-Procurement

Purchase management:

- Purchase requisitions
- RFQ (Request for Quotation)
- Purchase orders
- Goods received notes
- Supplier bill matching
- Supplier performance tracking

### 11.22 B-Logistics

Delivery & shipping:

- Delivery scheduling
- Courier integration
- Route optimization
- Tracking & updates
- Proof of delivery (photo, signature)
- Shipping cost calculation

---

## Kumpulan 6: Kebolehcapaian & Infrastruktur

### 11.23 B-Connect / API Gateway

Integration hub:

- Connector registry
- Webhook management
- Adapter pattern for providers
- Rate limiting per tenant
- API key management
- Usage analytics

### 11.24 B-Auth & Security

Security services:

- SSO (Single Sign-On)
- 2FA/MFA
- RBAC enforcement
- Tenant isolation
- Session management
- Security auditing

---

## Kumpulan 7: Dashboard & Intelligence

### 11.25 B-Analytics

Business intelligence:

- KPI dashboards
- Operational reports
- Custom report builder
- Data visualization (charts, graphs)
- Export capabilities
- Scheduled report delivery

### 11.26 B-Brain Hub

AI engine & automation:

- AI agent registry
- Tool execution framework
- Automation workflows
- Predictive analytics
- Natural language queries
- Knowledge retrieval (RAG)
- AI usage tracking & billing

---

# BAB D — INDUSTRY BLUEPRINT ENGINE

## 12. Tujuan Industry Blueprint Engine

Industry Blueprint Engine membolehkan AI BIJAN menyesuaikan sistem secara automatik mengikut jenis perniagaan pelanggan. Apabila pelanggan mendaftar, mereka memilih industri → sistem menyediakan:

- Modul yang disyorkan
- Workflow templates
- Custom fields
- Permission templates
- Dashboard presets
- Report templates

## 13. Direktori Industri Malaysia & MSIC Reference

Gunakan **Malaysia Standard Industrial Classification (MSIC)** DOSM sebagai taksonomi asas.

### 13.1 Kategori Industri Utama

| Kategori | Contoh Industri |
|----------|-----------------|
| **Pengangkutan & Mobiliti** | Van sekolah, bas ekspres, teksi, e-hailing, lori barang |
| **Runcit & Borong** | Kedai runcit, pasar raya, borong, kedai khas |
| **F&B** | Restoran, kafe, food court, catering, coffee shop |
| **Keselamatan & Penjagaan** | Taska, tadika, pusat jagaan kanak-kanak |
| **Hospitaliti** | Hotel, resort, homestay, guesthouse |
| **Klinik & Penjagaan Kesihatan** | Klinik swasta, klinik gigi, farmasi, pusat terapi |
| **Pembinaan & Hartanah** | Kontraktor, property developer, agency |
| **Automotif** | Bengkel, showroom, parts supplier, car rental |
| **Pertanian** | Ladang, pentanaman, penternakan, aquaculture |
| **Acara & Sewaan** | Event organizer, equipment rental, venue |
| **Perkhidmatan Khas** | Salon, gym, private tutor, cleaning service |
| **Perbankan & Kewangan** | Takaful, loan provider, financial advisor |

### 13.2 Contoh Khusus: Pengusaha Van Sekolah

| Fungsi Khusus | Modul BIJAN |
|---------------|-------------|
| Murid, penjaga, sekolah, laluan | **B-School Transport** |
| Jadual pickup/drop-off | B-Booking + B-Calendar |
| Langganan bulanan, status bayaran | B-Billing + B-Finance |
| Kehadiran/serahan murid | B-HRMS (attendance adapted) |
| Senarai kecemasan | B-Virtual Office |
| Notifikasi kelewatan | B-Omnichannel (WhatsApp) |
| Kenderaan, pemandu, dokumen | B-Assets + B-Fleet |
| Laporan keselamatan | B-Analytics |

---

# BAB E — B-BRAIN HUB: AI AGENTS & AUTONOMOUS SYSTEMS

## 14. Pasukan AI Maya — AI Team Dynamics

### 14.1 Peranan Ejen

| Ejen | Tanggungjawab | Contoh Tools |
|------|---------------|--------------|
| **AI CEO / Chief of Staff** | Menyelaraskan matlamat, delegasi, ringkasan eksekutif | `create_task`, `assign_agent`, `summarize_workspace` |
| **AI Executive Assistant** | Kalendar, e-mel, nota, susulan | `calendar.read`, `email.draft`, `task.create` |
| **AI CMO** | Strategi pemasaran, kandungan, kempen | `campaign.draft`, `ads.read`, `content.generate` |
| **AI CFO** | Aliran tunai, bajet, invois, margin | `finance.read`, `report.generate`, `invoice.draft` |
| **AI COO** | SOP, operasi, inventori, tugasan | `inventory.read`, `task.assign`, `operations.report` |
| **AI CSO** | Leads, pipeline jualan, follow-up | `crm.read`, `lead.update`, `sales.report` |
| **AI CHRO** | Kakitangan, jadual, cuti, onboarding | `hr.read`, `shift.draft`, `leave.review` |
| **AI Customer Support** | Soalan pelanggan, FAQ, pesanan | `knowledge.search`, `conversation.reply_draft` |
| **AI Ads Specialist** | Google Ads, Meta Ads, pengoptimuman | `ads.performance.read`, `campaign.draft` |
| **AI Researcher / Scout** | Trend, pesaing, sumber maklumat | `web.research`, `competitor.compare` |
| **AI Analyst** | Analisis KPI, ramalan, insight | `analytics.query`, `forecast.generate` |

\* Tindakan menerbitkan kempen, mengubah bajet atau membuat perubahan besar mesti melalui polisi kelulusan.

### 14.2 Konfigurasi Ejen

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

### 14.3 B-Boardroom — Multi-Agent Discussion

Aliran cadangan:

1. Pemilik mengemukakan persoalan atau keputusan
2. Chief of Staff menentukan ejen yang relevan
3. Ejen menerima konteks minimum yang diperlukan
4. Setiap ejen mengemukakan dapatan, andaian, bukti dan risiko
5. Orchestrator mengesan percanggahan dan meminta semakan jika perlu
6. AI CEO/Chief of Staff menyediakan ringkasan keputusan dan pilihan
7. Pemilik memilih tindakan; sistem merekod keputusan dan susulan

**Peraturan:** Boardroom tidak boleh menganggap persetujuan ejen sebagai bukti kebenaran. Dapatan perlu membezakan fakta, anggaran dan andaian.

---

## 15. B-Flow / B-Trigger — Enjin Automasi

### 15.1 Model Workflow

Workflow terdiri daripada:

- **Trigger:** domain event, webhook, jadual masa atau tindakan manual
- **Condition:** penapis, nilai, status, had dan cabang
- **Action:** panggilan API dalaman, connector atau tugas AI
- **Approval:** kelulusan manusia apabila diperlukan
- **Retry/error policy:** retry terhad, backoff, dead-letter queue
- **Audit:** input, output, masa, pemilik dan keputusan

### 15.2 Contoh: Pesanan Shopee Diterima

1. Connector mengesahkan webhook atau mengambil pesanan melalui API
2. B-Trigger mengesahkan tandatangan, timestamp dan idempotency key
3. B-Commerce mengemas kini pesanan melalui API dalaman
4. B-Inventory memproses perubahan stok melalui kontrak domain
5. B-Invoicing menyediakan dokumen jualan
6. B-Omnichannel menghantar mesej pelanggan jika dibenarkan
7. B-Analytics menerima event untuk pelaporan

Setiap langkah mesti selamat untuk diproses semula. Jangan menganggap penghantaran webhook hanya berlaku sekali.

### 15.3 Perlindungan Automasi

- Had tindakan per workflow dan per tenant
- Had belanja iklan dan had kewangan
- Dry-run / preview sebelum pengaktifan
- Approval gates
- Kill switch tenant dan global
- Retry yang tidak menghasilkan transaksi berganda
- Pemantauan kegagalan dan dead-letter queue

---

## 16. AI Gateway & Token Reseller — Serveras.click

### 16.1 Arkitektur — Reseller Model

**Prinsip utama:** Semua panggilan AI melalui backend AI BIJAN sahaja. Pelanggan TIDAK boleh akses serveras.click secara langsung. API key serveras.click disimpan selamat di server, tidak pernah sampai ke frontend atau aplikasi pelanggan.

```
┌─────────────────────────────────────────────────────┐
│              PELANGGAN AI BIJAN                      │
│  (Tenant, User, AI Agent)                            │
│  - Hanya guna nama model (contoh: "GPT-6 Astra")    │
│  - Lihat baki kredit mereka                          │
│  - TIDAK tahu serveras.click wujud                   │
│  - TIDAK dapat API key                               │
└──────────┬──────────────────────────────────────────┘
           │ POST /api/v1/ai/chat
           │ Model: "gpt-6-astra"
           │ Credit deduction from tenant balance
           ▼
┌─────────────────────────────────────────────────────┐
│         BACKEND AI BIJAN (Laravel)                   │
│                                                      │
│  ┌──────────────────────────────────────────────┐   │
│  │  Model Catalog (dalaman)                     │   │
│  │  gpt-6-astra → asai/gpt-6-astra             │   │
│  │  claude-opus-4.8 → asai/claude-opus-4.8     │   │
│  │  Kimi K3 → asai/kimi-k3                     │   │
│  │  ... semua pemetaan ID dalaman               │   │
│  └──────────────────────────────────────────────┘   │
│                                                      │
│  ┌──────────────────────────────────────────────┐   │
│  │  AI Gateway                                  │   │
│  │  - Simpan API key serveras.click (encrypted) │   │
│  │  - Replace "asai/" prefix sebelum call       │   │
│  │  - Track usage per tenant                    │   │
│  │  - Deduct credits dari baki tenant           │   │
│  │  - Rate limiting & cost controls             │   │
│  └──────────────────────────────────────────────┘   │
└──────────┬──────────────────────────────────────────┘
           │ POST https://serveras.click/v1/chat/completions
           │ Authorization: Bearer asai_sk_... (admin key)
           │ Model: "asai/gpt-6-astra" (internal name)
           ▼
┌─────────────────────────────────────────────────────┐
│          SERVERAS.CLICK API GATEWAY                  │
│  Base URL: https://serveras.click/v1                 │
│  - Process request                                   │
│  - Deduct dari credit balance admin                  │
│  - Return response dengan usage tokens               │
└─────────────────────────────────────────────────────┘
```

**Peraturan wajib:**

| # | Peraturan | Keterangan |
|---|-----------|------------|
| 1 | **API key milik admin sahaja** | Pelanggan tidak masukkan API key sendiri |
| 2 | **Sembunyikan awalan `asai/`** | Nama model untuk pelanggan tanpa prefix dalaman |
| 3 | **Semua call melalui backend** | Tiada direct access ke serveras.click dari client |
| 4 | **Admin control penuh** | Admin pilih model aktif, harga, had penggunaan |
| 5 | **No provider leakage** | Jangan paparkan nama pembekal dalam log atau error messages |

### 16.2 Endpoint Serveras.click

Serveras.click menyediakan OpenAI-compatible API endpoints:

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| `POST` | `/v1/chat/completions` | API key | OpenAI Chat Completions (streaming & non-streaming) |
| `POST` | `/v1/responses` | API key | Responses compatibility for Codex CLI |
| `POST` | `/v1/messages` | API key | Anthropic Messages compatibility for Claude Code |
| `GET` | `/v1/models` | Public | List customer model aliases dan rates |

**Contoh panggilan dari backend AI BIJAN:**

```http
POST https://serveras.click/v1/chat/completions
Content-Type: application/json
Authorization: Bearer asai_sk_[admin-api-key]

{
  "model": "asai/gpt-6-astra",
  "messages": [
    {"role": "system", "content": "Anda adalah pembantu perniagaan."},
    {"role": "user", "content": "Analisis jualan bulan ini"}
  ],
  "max_tokens": 2048,
  "stream": false
}
```

**Response:**

```json
{
  "object": "chat.completion",
  "model": "asai/gpt-6-astra",
  "choices": [{
    "index": 0,
    "message": {"role": "assistant", "content": "..."},
    "finish_reason": "stop"
  }],
  "usage": {
    "prompt_tokens": 23,
    "completion_tokens": 8,
    "total_tokens": 31
  }
}
```

**Error codes yang perlu ditangani:**

| Code | Meaning | Action |
|------|---------|--------|
| 401 | `invalid_api_key` | Check API key configuration |
| 402 | `insufficient_credits` | Top-up admin credits |
| 403 | `key_expired` | Regenerate API key |
| 404 | `model_not_found` | Model disabled or alias wrong |
| 429 | `rate_limit_exceeded` | 20 RPM limit reached |
| 503 | `upstream_unavailable` | Fallback ke model lain |

### 16.3 Katalog Model — Dalaman vs Pelanggan

**Pemetaan model:** Sistem menyimpan ID dalaman dan nama paparan secara berasingan. Pelanggan hanya nampak nama paparan.

| ID Dalaman (Admin Only) | Nama Paparan (Pelanggan) | Context | Max Output | Relative Input Cost | Relative Output Cost |
|-------------------------|--------------------------|---------|------------|--------------------|--------------------|
| `asai/gpt-6-astra` | GPT-6 Astra | Text+Vision | 1,050,000 | 7.5x | 18.75x |
| `asai/gpt-6-sol` | GPT-6 Sol | Text+Vision | 1,050,000 | 10x | 25x |
| `asai/gpt-6.1-sol` | GPT-6.1 Sol | Text+Vision | 1,050,000 | 10x | 25x |
| `asai/gpt-5.6-sol` | GPT-5.6 Sol | Text+Vision | 1,050,000 | 10x | 25x |
| `asai/gpt-5.6-terra` | GPT-5.6 Terra | Text+Vision | 1,050,000 | 7.5x | 18.75x |
| `asai/gpt-5.6-luna` | GPT-5.6 Luna | Text+Vision | 1,050,000 | 5.63x | 14.08x |
| `asai/gpt-6-luna` | GPT-6 Luna | Text+Vision | 1,050,000 | 5.63x | 14.08x |
| `asai/claude-opus-4.8` | Claude Opus 4.8 | Text+Vision | 1,000,000 | 15x | 37.5x |
| `asai/claude-opus-5` | Claude Opus 5 | Text+Vision | 1,000,000 | 15x | 37.5x |
| `asai/claude-opus-5.5` | Claude Opus 5.5 | Text+Vision | 1,000,000 | 15x | 37.5x |
| `asai/claude-sonnet-5` | Claude Sonnet 5 | Text+Vision | 1,000,000 | 10.5x | 26.25x |
| `asai/claude-sonnet-5.5` | Claude Sonnet 5.5 | Text+Vision | 1,000,000 | 10.5x | 26.25x |
| `asai/claude-haiku-5.5` | Claude Haiku 5.5 | Text+Vision | 1,000,000 | 3x | 7.5x |
| `asai/kimi-k3` | Kimi K3 | Text+Vision+Video | 1,000,000 | 9x | 22.5x |
| `asai/qwen3.8-max` | Qwen 3.8 Max | Text+Vision+Video | 1,000,000 | 6x | 15x |
| `asai/deepseek-v4-pro` | DeepSeek V4 Pro | Text+Vision | 1,000,000 | 5x | 12.5x |
| `asai/glm-5.3-flash` | GLM 5.3 Flash | Text+Vision+Video | 1,000,000 | 2.8x | 7x |
| `asai/minimax-m3` | MiniMax M3 | Text+Vision+Video | 1,000,000 | 2.1x | 5.25x |
| `asai/grok-4.7` | Grok 4.7 | Text+Vision | 500,000 | 6x | 15x |
| `asai/muse-wonder-1.0` | Muse Wonder 1.0 | Text only | 1,048,576 | 10x | 25x |

> **Penting:** Nama paparan boleh diubah oleh admin. Senarai penuh semua model tersedia dalam panel admin. Admin boleh enable/disable model mengikut keperluan dan kos.

### 16.4 Sistem Kredit & Pakej Token

**Cara berfungsi:**

```
Admin beli tokens dari serveras.click
        ↓
Tokens masuk ke "Credit Pool" admin
        ↓
Pelanggan beli pakej → dapat "Customer Credits"
        ↓
Pelanggan guna AI → credits deducted based on model rates
        ↓
Backend track: actual token usage × model rate = credits used
        ↓
 bila credits hampir habis → alert → top-up
```

**Perbezaan penting:**

| Konsep | Penerangan |
|--------|------------|
| **Token serveras.click** | Penggunaan sebenar yang dilaporkan oleh API (prompt_tokens + completion_tokens) |
| **Kredit pelanggan** | Baki yang pelanggan beli — berbeza dari token sebenar kerana ada model rate multipliers |
| **Model rate multiplier** | Setiap model ada kadar berbeza (input 5x-15x, output 12.5x-37.5x) |
| **Harga pakej** | Harga yang admin tetapkan untuk pelanggan beli |

**Pengiraan contoh:**

```
Pelanggan guna "Claude Opus 4.8" (relative input: 15x, output: 37.5x)

Request: 500 prompt tokens, 200 completion tokens
Total tokens from serveras: 700

Credits used = (500 × 15) + (200 × 37.5)
             = 7,500 + 7,500
             = 15,000 credits

Berbanding jika guna "GLM 5.3 Flash" (relative input: 2.8x, output: 7x):
Credits used = (500 × 2.8) + (200 × 7)
             = 1,400 + 1,400
             = 2,800 credits

→ Model mahal = lebih credits digunakan!
```

### 16.5 Pakej Token — Kos Admin vs Harga Jual

**Harga pembelian admin dari serveras.click:**

| Tokens | Kos Admin (RM) | Sah (hari) |
|--------|---------------|------------|
| 80,000,000 | RM 5.00 | 30 hari |
| 140,000,000 | RM 8.00 | 30 hari |
| 200,000,000 | RM 10.00 | 30 hari |
| 1,100,000,000 | RM 50.00 | 30 hari |
| 2,250,000,000 | RM 100.00 | 30 hari |

**Harga jualan kepada pelanggan AI BIJAN:**

| Tokens | Harga Jual (RM) | Untung Kasar | Margin |
|--------|-----------------|--------------|--------|
| 80 juta | RM 80.00 | RM 75.00 | 93.8% |
| 140 juta | RM 110.00 | RM 102.00 | 92.7% |
| 200 juta | RM 160.00 | RM 150.00 | 93.8% |
| 1.1 bilion | RM 600.00 | RM 550.00 | 91.7% |
| 2.25 bilion | RM 1,100.00 | RM 1,000.00 | 90.9% |

> **Nota:** Untung kasar di atas belum menolak kos pelayan, caj pembayaran (ToyyibPay/Billplz ~3%), cukai, sokongan pelanggan, atau perbezaan kos penggunaan model sebenar. Margin bersih mungkin lebih rendah.

**Cadangan pakej pelanggan (dengan tier):**

| Pakej | Tokens | Kos Admin | Harga Jual | Sasaran Pelanggan |
|-------|--------|-----------|------------|-------------------|
| **Starter** | 80 juta | RM 5 | RM 79/bulan | Usahawan solo, freelancer |
| **Business** | 200 juta | RM 10 | RM 159/bulan | PKS kecil (5-20 staff) |
| **Professional** | 1.1 bilion | RM 50 | RM 499/bulan | Syarikat sederhana (20-100 staff) |
| **Enterprise** | 2.25 bilion | RM 100 | RM 999/bulan | Syarikat besar (100+ staff) |
| **Top-up** | 80 juta | RM 5 | RM 80 sekali | Tambahan bila kuota hampir habis |

### 16.6 Panel Admin AI — Keperluan

Admin perlu boleh:

- [ ] Mengubah API base URL dan mengurus API key serveras.click dengan selamat (encrypted storage)
- [ ] Mengaktifkan atau menyahaktifkan model individu
- [ ] Menetapkan nama paparan, kategori, keupayaan (teks/imej/video) dan had penggunaan setiap model
- [ ] Menetapkan model rate multipliers (input/output/cache) untuk pengiraan kredit
- [ ] Mengurus harga pakej token, tempoh sah (30 hari), had penggunaan dan polisi lebihan
- [ ] Melihat penggunaan setiap pelanggan: model dipakai, credits terpakai, kos sebenar, untung
- [ ] Melihat kos pembekal vs hasil jualan vs margin keuntungan (dashboard financial)
- [ ] Menetapkan had harian/bulanan per tenant, had concurrent requests, perlindungan daripada penyalahgunaan
- [ ] Menyekat model automatik apabila serveras.click unavailable atau kos melebihi had
- [ ] Menyimpan audit log untuk perubahan harga, API key, model dan pelarasan kredit
- [ ] Auto-top-up admin credits bila balance rendah
- [ ] Export laporan penggunaan dan keuntungan (CSV/PDF)

### 16.7 Keputusan Penting — Tempoh Sah Token

**Soalan yang perlu diputuskan:**

Pakej serveras.click menyatakan tempoh 30 hari. Sistem AI BIJAN perlu menetapkan:

1. **Adakah baki yang tidak digunakan akan luput selepas 30 hari?**
   - Ya → perlu clear expiry date, alert 7 hari sebelum luput
   - Tidak → credits kekal selagi ada subscription aktif

2. **Adakah pembelian baru menambah baki atau memulakan tempoh baharu?**
   - Additive → baki lama + baru, tempoh sama
   - Fresh start → baki baru reset, tempoh 30 hari dari pembelian

3. **Apa jadi bila subscription tamat?**
   - Credits hangus terus?
   - Give 7 hari grace period?
   - Convert ke read-only mode?

**Cadangan:**
- Credits sah 30 hari dari tarikh pembelian
- Pembelian baru ADD ke baki sedia ada (tidak reset)
- Alert pada hari ke-25 ("Baki anda luput dalam 5 hari!")
- Bila subscription tamat → 7 hari grace period untuk guna baki
- Selepas grace period → credits hangus, akaun downgrade ke free tier

---

## 17. B-Scout, B-Intel & B-Compliance

### 17.1 B-Scout — Local Market Intelligence

- Trend carian dan kandungan melalui sumber sah
- Analisis topik mengikut kawasan atau demografi
- Ringkasan sentimen dengan sampel dan had data
- Alert untuk lonjakan permintaan atau topik

### 17.2 B-Intel — Competitor Intelligence

- Perbandingan harga, promosi, katalog pesaing
- Pemantauan perubahan harga dengan timestamp
- Analisis jurang produk/perkhidmatan
- Elakkan bypass kawalan akses atau scraping yang melanggar syarat

### 17.3 B-Opportunity

- Gabungkan data jualan tenant, trend dan pemerhatian pesaing
- Paparkan tahap keyakinan, data yang hilang dan andaian
- Berikan tindakan yang boleh diuji, bukan janji

### 17.4 B-Compliance

- Rujukan LHDN MyInvois, PDPA, panduan halal dan peraturan berkaitan
- Setiap kandungan mempunyai sumber rasmi, tarikh disemak dan versi
- Bezakan maklumat umum daripada nasihat undang-undang/perakaunan
- Jadual kemas kini dan amaran apabila sumber rasmi berubah

---

## 17. Shared Agent Memory & Collaboration

### 17.1 Memory Architecture

| Jenis Memori | Contoh | Akses |
|--------------|--------|-------|
| Personal preferences | Bahasa pilihan, format laporan | Pengguna berkenaan |
| Work context | Tugasan semasa, projek | Mengikut permission |
| Business profile | Produk, harga, sasaran syarikat | Pengguna tenant yang dibenarkan |
| Operational records | Orders, invoices, tasks | Modul sumber dan permission |
| Conversation history | Perbualan terdahulu | Mengikut access policy |
| Approved knowledge | SOP, dokumen rasmi, polisi | Mengikut classification |
| Derived insights | Trend jualan, risiko, cadangan | Mengikut data source access |

### 17.2 Memory Rules

- Simpan hanya maklumat yang mempunyai tujuan jelas
- Sediakan fungsi review, correction dan deletion mengikut polisi
- Personal memory ≠ organizational knowledge
- AI actions logged with traceability

### 17.3 Inter-Agent Collaboration

- Agent-to-agent messaging via domain events
- Shared knowledge bases (scoped by permission)
- Cross-agent task delegation
- Conflict detection and resolution
- Collaborative decision making (B-Boardroom)

---

## 18. B-Scout, B-Intel & B-Compliance

### 18.1 B-Scout — Local Market Intelligence

- Trend carian dan kandungan melalui sumber sah
- Analisis topik mengikut kawasan atau demografi
- Ringkasan sentimen dengan sampel dan had data
- Alert untuk lonjakan permintaan atau topik

### 18.2 B-Intel — Competitor Intelligence

- Perbandingan harga, promosi, katalog pesaing
- Pemantauan perubahan harga dengan timestamp
- Analisis jurang produk/perkhidmatan
- Elakkan bypass kawalan akses atau scraping yang melanggar syarat

### 18.3 B-Opportunity

- Gabungkan data jualan tenant, trend dan pemerhatian pesaing
- Paparkan tahap keyakinan, data yang hilang dan andaian
- Berikan tindakan yang boleh diuji, bukan janji

### 18.4 B-Compliance

- Rujukan LHDN MyInvois, PDPA, panduan halal dan peraturan berkaitan
- Setiap kandungan mempunyai sumber rasmi, tarikh disemak dan versi
- Bezakan maklumat umum daripada nasihat undang-undang/perakaunan
- Jadual kemas kini dan amaran apabila sumber rasmi berubah

---

## 19. AI Media Studio

Integrasi OpenHiggsfield-style image/video generation:

- Image generation (product photos, marketing materials)
- Video generation (promotional videos, tutorials)
- Content moderation and approval workflow
- Brand guidelines enforcement
- Usage billing through AI Token Wallet
- Asset management and versioning

---

# BAB F — CUSTOMER PORTAL & PERSONAL AI ASSISTANT

## 20. Customer Portal Structure

### 20.1 Business Dashboard

Paparan ringkasan prestasi perniagaan, jualan, prospek, pelanggan, tugasan AI, pendapatan, perbelanjaan dan notifikasi penting.

### 20.2 Menu Customer Portal (22 menu)

| Menu | Fungsi |
|------|--------|
| Home Dashboard | Prestasi keseluruhan bisnes |
| My AI Assistant | Chat, arahan, nasihat dan tugasan AI |
| My Business Profile | Profil, objektif, model bisnes dan maklumat operasi |
| Business Knowledge | Dokumen, SOP, FAQ, katalog dan pengetahuan AI |
| Customers & CRM | Rekod pelanggan dan prospek |
| Lead Generation | Pencarian dan pengurusan bakal pelanggan |
| Marketing Studio | Kandungan, kempen dan bahan pemasaran |
| Sales Pipeline | Peluang jualan, rundingan dan susulan |
| Products & Services | Produk, servis, harga, pakej dan maklumat stok |
| Orders & Bookings | Pesanan, tempahan dan status pelaksanaan |
| Quotations & Invoices | Sebut harga, invois dan rekod berkaitan |
| Payments & Finance | Pembayaran, pendapatan, perbelanjaan dan laporan |
| AI Agents | Ejen AI mengikut fungsi perniagaan |
| Automation Workflows | Pencetus, syarat, tindakan dan jadual automasi |
| Tasks & Projects | Tugasan, projek dan pemantauan kemajuan |
| Staff & Team | Staf, jabatan, cawangan dan pengagihan kerja |
| Reports & Analytics | Analisis prestasi, penukaran dan keuntungan |
| Integrations | WhatsApp, e-mel, laman web, pembayaran dan sistem luaran |
| AI Usage & Credits | Baki kredit, penggunaan token dan sejarah kos |
| Subscription & Billing | Pelan langganan, bil dan naik taraf |
| Notifications | Amaran, aktiviti AI dan perkara yang memerlukan tindakan |
| Settings & Security | Profil, akses, keselamatan dan pilihan sistem |

Menu akhir berubah mengikut pelan langganan, sektor perniagaan dan kebenaran pengguna.

---

## 21. Business Understanding — Setup Awal Pelanggan

AI BIJAN perlu memahami perniagaan pelanggan sebelum mula menjalankan automasi.

**Langkah 1 — Kenali Bisnes**  
Jenis industri, model perniagaan, lokasi, cawangan, saiz pasukan dan sistem yang digunakan.

**Langkah 2 — Produk dan Kewangan**  
Produk, servis, harga, kos, margin, kapasiti, bajet dan sasaran pendapatan.

**Langkah 3 — Pelanggan Sasaran**  
Profil pelanggan ideal, masalah mereka, kawasan pasaran dan saluran pemasaran.

**Langkah 4 — Cara Bisnes Beroperasi**  
SOP, proses jualan, pengurusan staf, tempahan, pembayaran dan sokongan pelanggan.

**Langkah 5 — AI membina Business Knowledge Profile**  
AI menyediakan rumusan kefahaman perniagaan, menyenaraikan maklumat yang belum lengkap dan meminta pengesahan pelanggan.

**Langkah 6 — Aktifkan Operasi**  
Pelanggan memilih matlamat, automasi, tahap autonomi dan had kuasa AI sebelum workflow diaktifkan.

---

## 22. Personal AI Assistant

Personal AI Assistant bukan sekadar ruang sembang. Ia menjadi pintu utama untuk pelanggan mengendalikan seluruh sistem.

**Contoh arahan pelanggan:**

- "Cari 100 prospek baharu yang sesuai dengan servis saya."
- "Analisis kenapa jualan bulan ini menurun."
- "Sediakan kempen untuk mendapatkan 30 pelanggan baharu."
- "Susuli prospek yang belum membalas sebut harga."
- "Semak tugasan staf yang masih belum selesai."
- "Buat laporan untung rugi berdasarkan transaksi yang telah direkodkan."
- "Cadangkan strategi untuk meningkatkan margin keuntungan."

### 22.1 Jenis Personal AI Assistant

| Jenis | Sasaran | Fungsi Utama |
|-------|---------|--------------|
| **Owner AI Assistant** | Pemilik/Pengarah | Ringkasan bisnes, sasaran, cash-flow, risiko, cadangan tindakan |
| **Executive AI Assistant** | Pengurusan Kanan | Executive briefing, KPI monitoring, meeting prep, decision summaries |
| **Sales AI Assistant** | Pasaran Jualan | Lead qualification, CRM updates, follow-up planning, quotation prep |
| **Marketing AI Assistant** | Pemasaran | Campaign planning, content generation, SEO, budget monitoring |
| **Operations AI Assistant** | Operasi | Task coordination, workflow monitoring, inventory alerts |
| **Finance AI Assistant** | Kewangan | Invoice summaries, cash-flow forecasts, budget variance |
| **HR AI Assistant** | Sumber Manusia | Onboarding, training plans, shift planning, policy Q&A |
| **Customer Service AI** | Sokongan Pelanggan | FAQ answers, ticket classification, conversation summary |
| **Industry-Specific AI** | Industri Khusus | Function-specific assistant per industry blueprint |

### 22.2 Autonomi & Kawalan Pelanggan

| Mod | Cara Operasi |
|-----|-------------|
| 🔒 Manual | AI memberi cadangan; pelanggan menjalankan tindakan |
| 🔍 Assisted | AI menyediakan tindakan untuk disemak |
| ✅ Approval-based | AI melaksanakan tindakan selepas kelulusan |
| 🚀 Autonomous | AI menjalankan workflow yang telah diluluskan |

Tindakan kewangan sensitif, perubahan hak akses dan tindakan berisiko tinggi tetap memerlukan kawalan khusus walaupun mod Autonomous diaktifkan.

---

## 23. Staff, Admin & Owner Portal

Setiap akaun perniagaan mempunyai pengurusan pasukan terbina dalam:

- **Owner Portal** — kawalan bisnes, strategi, kewangan, langganan, akses dan kelulusan
- **Business Admin Portal** — mengurus operasi dan pasukan berdasarkan kuasa yang diberikan
- **Staff Portal** — melihat tugasan, pelanggan dan fungsi yang dibenarkan mengikut peranan
- **AI Agent Workspace** — memaparkan tugasan automatik, status, penggunaan kredit, hasil dan isu

---

# BAB G — PLATFORM BUSINESS & MULTI-TIER ADMIN

## 24. Multi-Tier Admin Hierarchy

| Tier | Peranan | Kawalan |
|------|---------|---------|
| **Master Admin** | Bijan Holdings (platform owner) | Full platform access, all tenants, billing, provider management |
| **Super Admin** | Platform operator | Tenant management, platform configuration, support access |
| **White-label Admin** | Reseller/agency dengan branding sendiri | Their own tenants, custom branding, billing to sub-tenants |
| **Customer Admin** | Tenant pelanggan akhir | Their own organization, users, modules, settings |

### 24.1 Super Admin Capabilities

- Manage SaaS tenants, account status dan subscription packages
- Plan pricing, user limits, storage, AI usage dan API quotas
- Enable/disable modules per package
- AI Gateway configuration (serveras.click API key, model catalogue, pricing tiers)
- Token package management, credit pools dan auto-recharge thresholds
- Usage monitoring per tenant — model dipakai, credits terpakai, kos sebenar
- Rate limiting, cost caps dan perlindungan daripada penyalahgunaan
- System health, errors, queues, resource usage dan security logs
- System health, errors, queues, resource usage dan security logs
- Support tickets dan platform announcements
- Global settings, language, currency, timezone dan email configuration
- Administrator action audit dan controlled support access

**Peraturan:** Super Admin tidak boleh melihat kandungan perniagaan tenant secara automatik tanpa kebenaran yang sah. Akses sokongan luar biasa perlu bersebab, terhad masa, dilog dan boleh diaudit.

### 24.2 White-label Features

- Custom branding (logo, colors, domain)
- Their own billing to sub-tenants
- Isolated tenant management
- Revenue sharing with platform
- Custom module entitlements

---

## 25. Platform Business & Brand Management

### 25.1 Brand Portfolio

- Multiple brands under one platform ownership
- Brand-specific configurations
- Revenue tracking per brand
- White-label provisioning

### 25.2 Revenue Sharing

- Platform commission on subscriptions
- AI token reselling margins
- White-label revenue splits
- Automated payout calculations

### 25.3 AI Reseller Platform

- Serveras.click integration
- Token package distribution
- Usage reporting for resellers
- Automated billing reconciliation

---

# BAB H — INTEGRASI, API & INFRASTRUKTUR

## 26. Integrasi Yang Dirancang

### 26.1 Communication Channels

- Gmail dan Google Calendar
- WhatsApp Business Platform / Cloud API
- Facebook Pages, Instagram professional accounts dan Meta Marketing API
- Google Ads API
- TikTok dan TikTok Ads API (apabila akses tersedia)
- Telegram, e-mel, SMS, push notifications

### 26.2 E-Commerce & Marketplace

- Shopee Open Platform (apabila akaun dan kelulusan API membenarkan)
- Lazada API (jika diperlukan)
- Etsy/international marketplaces

### 26.3 Payments & Banking

- Payment gateways (ToyyibPay, Billplz, Stripe)
- FPX direct bank payments
- Bank feeds melalui penyedia yang dibenarkan
- E-wallets (Touch 'n Go, GrabPay, Boost)

### 26.4 Government & Compliance

- LHDN MyInvois (e-Invois JSON submission)
- SSM integration (company verification)
- JDH (Jabatan Kerja Malaysia) for EPF/SOCIO/PCB
- PDPA-compliant data handling

### 26.5 Logistics

- Kurier atau agregator logistik (J&T, Ninja Van, DHL)
- Route optimization
- Proof of delivery integration

### 26.6 Maps & Location

- Google Maps API
- Geocoding dan route planning

**Nota integrasi:** Kemampuan API, kelulusan aplikasi, skop data, had kadar dan syarat komersial berbeza mengikut penyedia. Sistem mesti memaparkan integrasi sebagai `available`, `connected`, `needs_reauthorization`, `limited` atau `unavailable`.

---

## 27. B-Marketing Ads Manager — Google Ads & Meta Ads

### 27.1 Google Ads

Sokongan dirancang, tertakluk kepada kelayakan akaun dan Google Ads API:

- Account dan campaign overview
- Search, Display, YouTube dan Performance Max
- Bajet, status, impressions, clicks, CTR, CPC, conversions
- Keyword/search-term insights
- Draf campaign/ad group/ads, semakan polisi dan penerbitan
- Change history, budget alerts dan rollback plan

### 27.2 Meta Ads

Sokongan dirancang, tertakluk kepada app review, permissions dan akses Marketing API:

- Ad account, campaign, ad set, ads dan insights
- Audience targeting recommendations
- Creative optimization suggestions
- Budget pacing dan spend alerts
- ROAS tracking dan attribution

---

## 28. Localization & Malaysia Configuration

### 28.1 Format Standards

- Mata wang: MYR (RM)
- Zon waktu: Asia/Kuala_Lumpur
- Bahasa: Bahasa Melayu dan Inggeris
- Tarikh: DD/MM/YYYY
- Nombor: European format (1,000.00)

### 28.2 Tax & Compliance

- SST (Sales & Service Tax)
- e-Invois LHDN (mandatory from 2024-2025 rollout)
- EPF, SOCSO, EIS, PCB untuk payroll
- PDPA data protection compliance
- Halal certification tracking (jika relevan)

---

## 29. Infrastructure & Operations

### 29.1 Deployment Options

- **cPanel hosting** (for individual tenants, limited features)
- **VPS/Cloud** (for platformHQ, full features)
- **Hybrid** (core on cloud, tenant containers on shared hosting)

### 29.2 Scalability Considerations

- Start with modular monolith
- Add Redis for caching and queues
- Implement horizontal scaling when needed
- Consider microservices only for high-load modules
- Database sharding by tenant for large-scale deployment

### 29.3 Monitoring & Observability

- Laravel Telescope (development)
- Sentry (error tracking)
- Prometheus + Grafana (metrics)
- ELK Stack (logging)
- Uptime monitoring (UptimeRobot, Pingdom)

### 29.4 Backup & Disaster Recovery

- Automated daily backups
- Point-in-time recovery
- Cross-region replication (critical data)
- Tested restore procedures
- RTO/RPO targets documented

---

# BAB I — ROADMAP & IMPLEMENTATION

## 30. Phased Development Roadmap

### FASA 1: Foundation (Sekarang — 2-3 minggu)

- [ ] Complete ai-bijan-saas foundation
- [ ] Run `composer install` & `php artisan migrate --seed`
- [ ] Run `php artisan test` — ensure 43 tests passing
- [ ] Complete Subscription Engine UI (Filament admin)
- [ ] Complete Brand Portfolio Management UI
- [ ] Finalize AI Token Wallet gateway integration
- [ ] Add missing modules: Procurement, Logistics basic models
- [ ] e-Invois LHDN integration research & planning

### FASA 2: Frontend Screens (4-6 minggu, selari dengan Fasa 1)

- [ ] Dashboard main layout (navigation, sidebar, header)
- [ ] Virtual Office screen (OfficeAgent, OfficeTask kanban)
- [ ] HRMS complete screen (attendance, leave, payroll)
- [ ] POS complete screen (cart, checkout, receipt)
- [ ] Inventory/Warehouse screen
- [ ] Projects & Helpdesk screen
- [ ] Assets management screen
- [ ] Finance/Invoicing screen
- [ ] Marketing campaign screen
- [ ] Analytics dashboard

**Tech Stack:** Next.js 15 + React 19 + Tailwind CSS + Filament-inspired design

### FASA 3: Integration (1 minggu)

- [ ] Merge BLoyalty models/routes into ai-bijan-saas
- [ ] Share User model (single auth across platforms)
- [ ] Convert standalone to Laravel module
- [ ] Update routes to `/loyalty`
- [ ] Test multi-tenant isolation with combined system

### FASA 4: Virtual Office Backend ⭐ PRIORITI TINGGI (6-8 minggu)

- [ ] Create `app/Modules/VirtualOffice/` structure
- [ ] Set up AI Gateway (OpenAI/Anthropic/Gemini routing)
- [ ] Build connector system (WhatsApp, Telegram, Google Drive, Shopee, TikTok)
- [ ] Implement agent runtime (Redis jobs via Horizon)
- [ ] Build Peti Keputusan (approval inbox)
- [ ] Agent scheduling system
- [ ] Credit/token billing per tenant
- [ ] Integrate Higgsfield API for image/video generation

### FASA 5: Ruang Dashboard Completion (3-4 minggu)

- [ ] Connect to ai-bijan-saas API
- [ ] Build admin panel views
- [ ] Implement real-time updates (Laravel Reverb)
- [ ] Add role-based access control UI

### FASA 6: POS Next.js Screen (3-4 minggu)

- [ ] Product catalog view
- [ ] Cart/checkout flow
- [ ] Payment processing (ToyyibPay/FPX)
- [ ] Receipt generation
- [ ] Offline mode (PWA service worker)
- [ ] Barcode scanning (html5-qrcode)

### FASA 7: Brain Hub Phase 2

- [ ] Connect remaining modules (Booking, Sign & Docs, Site, Marketplace)
- [ ] AI autonomous suggestions with human approval
- [ ] Advanced analytics and forecasting
- [ ] Natural language queries

### FASA 8: Industry Blueprints

- [ ] School Transport complete workflow
- [ ] Event & Rental complete workflow
- [ ] Construction project management
- [ ] Automotive workshop management
- [ ] Healthcare clinic management

---

## 31. Definition of Done

Sebuah modul/fungsi dianggap **Done** apabila:

- [ ] Semua unit tests lulus (coverage > 80%)
- [ ] Integration tests lulus
- [ ] Code review selesai
- [ ] Documentation lengkap
- [ ] Security scan bersih
- [ ] Performance benchmarks dicapai
- [ ] User acceptance testing (internal) lulus
- [ ] Deployed to staging environment
- [ ] Rollback procedure tested

---

## 32. Open Decisions Register

| # | Keputusan | Tarikh | Status |
|---|-----------|--------|--------|
| 1 | ERPNext BUKAN dependency | 2026-10-18 | Final |
| 2 | Modular monolith dahulu, microservices kemudian | 2026-10-17 | Final |
| 3 | Virtual Office dalam ai-bijan-saas (bukan app berasingan) | 2026-10-19 | Final |
| 4 | New Next.js app untuk frontend (bukan guna ruang-main) | 2026-10-19 | Final |
| 5 | Higgsfield = content creator (image/video generation) | 2026-10-19 | Final |
| 6 | Laravel 11+ sebagai backend framework | 2026-10-17 | Final |
| 7 | Next.js 15 + React 19 untuk frontend | 2026-10-17 | Final |
| 8 | Filament PHP untuk admin panel | 2026-10-17 | Final |
| 9 | MySQL/MariaDB sebagai database utama | 2026-10-17 | Final |
| 10 | Cloudflare R2 untuk object storage | 2026-10-17 | Final |

---

## 33. Versioning Policy & Changelog

### Versioning Scheme

`MAJOR.MINOR.PATCH`

- **MAJOR** — Breaking changes, architecture changes
- **MINOR** — New features, modules
- **PATCH** — Bug fixes, minor improvements

### Changelog

| Versi | Tarikh | Perubahan |
|-------|--------|-----------|
| 4.5.1 | 2026-10-19 | Konsolidasi semua dokumen docs/, rename ke kebab-case |
| 4.5.0 | 2026-10-18 | Multi-Tier Admin, Brand Portfolio, AI Reseller, Domain Config |
| 4.3.0 | 2026-10-17 | Personal Brand, Social Media, Continuous AI Learning Loop |
| 4.2.0 | 2026-10-16 | Personal AI Assistant, Business Understanding, Autonomous Engine |
| 4.1.0 | 2026-10-15 | Full module specifications, industry coverage |
| 4.0.0 | 2026-10-14 | Industry Blueprint Engine, MSIC mapping |
| 3.0.0 | 2026-10-13 | Ecosystem structure, 18 B-Modules defined |
| 2.5.0 | 2026-10-12 | AI Media Studio integration |
| 2.4.0 | 2026-10-11 | Shared Agent Memory, Inter-Agent Collaboration |
| 2.3.0 | 2026-10-10 | AI Provider Gateway, Token Reseller |
| 2.2.0 | 2026-10-09 | Universal SME Tools, Industry Packs |
| 2.1.0 | 2026-10-08 | Original architecture blueprint |

---

## Lampiran: Sumber Rasmi & Rujukan

### MSIC & DOSM
- Portal carian Kod MSIC 2025, DOSM: https://economiccensus.dosm.gov.my/portal-main/msic-2025
- OpenDOSM katalog MSIC: https://open.dosm.gov.my/data-catalogue/msic
- DOSM Economic Census: https://www.dosm.gov.my/portal-main/release-content/economic-census--all-sectors

### LHDN MyInvois
- Portal e-Invois: https://boardmyinvois.hasil.gov.my/

### PDPA 2010
- Portal PDP: https://www.pdp.gov.my/

### SSMBusiness
- Pendaftaran perniagaan: https://www.ssm-einfo.my/

---

**Dokumen ini disediakan oleh AI Assistant untuk pasukan pembangunan AI BIJAN.**  
**Terakhir dikemaskini:** 2026-10-19  
**Status:** Planning Specification — NOT proof of implementation
