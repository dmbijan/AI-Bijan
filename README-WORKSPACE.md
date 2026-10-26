# AI BIJAN Workspace — Master Index

**Projek:** AI Bijan SaaS Platform  
**Tarikh Audit:** 2026-10-19  
**Status:** Active Development  
**Last Reorganized:** 2026-10-19 (Single consolidated structure)

---

## 📁 Struktur Workspace (SELEPAS PENYUSUNAN)

```
AI-Bijan/                          # ← SEMUA dalam SATU folder!
│
├── ai-bijan-saas/                 # ⭐ SISTEM UTAMA — Laravel 11+ SaaS
│   ├── app/, config/, database/, routes/
│   ├── composer.json, package.json
│   ├── vendor/, node_modules/
│   └── (70+ models, 50+ migrations, 43 tests)
│
├── bijan-virtual-office/          # 📋 Virtual Office — Design & Planning
│   ├── bijan-virtual-office-backend/
│   ├── bvo-dashboard.html
│   └── pelan-seni-bina-saas.md
│
├── bloyalty/                      # 💎 Loyalty Module — Laravel 12
│   ├── app/, routes/, config/
│   ├── PROJECT_PROGRESS.md
│   ├── PROJECT_TODO.md
│   └── (19 migrations, 13 models, complete loyalty system)
│
├── aibijan-frappe/                # 🔌 Frappe Integration Layer
│   └── aibijan/                   # Frappe app module
│
├── pos/                           # 🛒 POS System — Next.js
│   └── app/, components/, lib/
│
├── ruang-main/                    # 🏢 Ruang Dashboard — Next.js 15
│   ├── src/, app/, components/
│   └── server/
│
├── open-higgsfield/               # 🤖 Content Creator — Image/Video Generation
│   ├── src/, server/
│   └── docs/
│
├── erpnext-develop/               # 📚 ERPNext Source — Reference only
│   └── erpnext/                   # ERPNext app source
│
├── rujukan-ui/                    # 🎨 UI Reference — ERPNext v2.5 screens
│   └── pos/                       # POS Next.js reference UI
│
├── docs/                          # 📚 Architecture Documents (18 files)
│   ├── architecture-v2.1.md
│   ├── architecture-v2.5-master-consolidated.md
│   ├── architecture-v2.5-owner.md
│   ├── blueprint-v3.0.md
│   ├── blueprint-v4.3-complete.md
│   ├── blueprint-v4.5.1-corrected.md
│   ├── customer-side.md
│   ├── ekosistem-v3.0-owner.md
│   ├── industry-coverage-v4.0.md
│   ├── documents/                 # Supporting documents
│   ├── phase-0-1/                 # Phase 0-1 planning
│   └── operations/                # Operations reports
│
├── laporan/                       # 📊 Project Reports (18+ files)
│   ├── brainhub.md
│   ├── hr.md
│   ├── marketing.md
│   ├── omnichannel.md
│   ├── reka-v25.md
│   ├── studio.md
│   ├── siasatan-food-delivery.md
│   ├── 2026-10-14-pelan-ai-bijan.md
│   └── 2026-10-15-aibijan-*.md
│
├── references/                    # 📖 Third-party References
│   ├── ai-clipping-generator/     # AI content ref
│   ├── awesome-generative-ai-apps/ # Generative AI apps
│   ├── awesome-saas-directory/    # SaaS directory
│   ├── cal-diy/                   # Booking reference
│   ├── chatwoot/                  # Omnichannel reference
│   ├── librelinks/                # Link management ref
│   ├── linkstack/                 # Link management ref
│   ├── loyalpy-me/                # Loyalty program ref
│   ├── lynk/                      # Link in bio ref
│   ├── mautic/                    # Marketing automation ref
│   ├── open-source-saas/          # SaaS examples
│   ├── point-of-sales/            # POS reference
│   ├── rag-saas/                  # AI/RAG reference
│   ├── refref/                    # Reference tool
│   └── stampee/                   # Social media ref
│
├── sources/                       # 📦 Original Sources (Bijan-specific)
│
├── skrip/                         # 🔧 Scripts Collection
│   ├── python/                    # 19 Python scripts (Frappe tools)
│   └── debug/                     # 15 PHP debug/audit scripts
│
└── assets/                        # 📦 Static Assets
    ├── aibijan-template.html
    └── drive-download-20261019.zip
```

---

## 🚀 Quick Start

### 1. Setup Sistem Utama (ai-bijan-saas)
```bash
cd ai-bijan-saas
composer install
cp .env.example .env
# Edit .env dengan setting database anda
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### 2. Setup BLoyalty
```bash
cd bloyalty
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### 3. Setup Frontend (ruang-main)
```bash
cd ruang-main
npm install
npm run dev
```

---

## 📊 Modul Status

| Modul | Folder | Framework | Status |
|-------|--------|-----------|--------|
| **SaaS Platform** | `ai-bijan-saas/` | Laravel 11+ | 🟢 Active |
| **Virtual Office** | `BijanVirtualOffice/` | Design | 🟡 Planning |
| **Loyalty** | `BLoyalty/` | Laravel | 🟢 Active |
| **POS** | `pos/` | Next.js 15 | 🟡 In Progress |
| **Ruang Dashboard** | `ruang-main/` | Next.js 15 | 🟢 Active |
| **Frappe Integration** | `aibijan-frappe/` | Frappe | 🟡 In Progress |

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend API** | Laravel 11+, PHP 8.3+ |
| **Frontend** | Next.js 15, React 19, Tailwind CSS |
| **Database** | MySQL 8.x / MariaDB 10.6+ |
| **Queue/Cache** | Redis + Laravel Horizon |
| **Realtime** | Laravel Reverb |
| **Admin Panel** | Filament PHP |
| **Storage** | Cloudflare R2 / AWS S3 |

---

## 📝 Notes

- **Python scripts** (`skrip/python/`) — Untuk tool Frappe/ERPNext
- **Debug scripts** (`skrip/debug/`) — Audit trail & smoke testing
- **UI references** (`rujukan-ui-v25/`) — Legacy ERPNext v2.5 screens untuk rujukan reka bentuk
- **ERPNext source** (`erpnext-develop/`) — Source code untuk integrasi reference

---

## 🔗 Dokumen Penting

1. **[README.md](./ai-bijan-saas/README.md)** — Panduan sistem utama
2. **[PASANG.md](./ai-bijan-saas/PASANG.md)** — Panduan pemasangan cPanel
3. **[Master-Blueprint.md](./ai-bijan-saas/Master-Blueprint.md)** — Blueprint keseluruhan
4. **[AGENTS.md](./ai-bijan-saas/AGENTS.md)** — AI Agents configuration
5. **[CLAUDE.md](./ai-bijan-saas/CLAUDE.md)** — Claude AI configuration

---

*Last updated: 2026-10-19*
