<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

---

# AI BIJAN SaaS — Master Blueprint v9.6 Context

## Product Overview
AI BIJAN is a multi-tenant SaaS platform for Malaysian businesses (SMEs, retail, F&B, services). Built with **Laravel 12.70.1, PHP 8.2.12**, SQLite (dev) / MySQL 8 (prod).

**Tagline:** *Your AI Business Team, in the Room.*

> **Verified 2026-11-09:** lihat `docs/AUDIT-BASELINE.md` untuk angka rasmi terkini.
> **Baseline angka:** `docs/AUDIT-BASELINE.md` (satu-satunya sumber kebenaran — jangan salin angka secara manual).
> Jana semula: `php skrip/jana-baseline.php --tulis`. Full system doc: `docs/SISTEM-AI-BIJAN.md`.
> Status platform: **`DEVELOPMENT`** — belum production-ready (lihat `README.md`).

> ⚠️ **Jangan** nyatakan modul, jadual, laluan atau bilangan ujian sebagai fakta
> tanpa merujuk `docs/AUDIT-BASELINE.md`. Angka dalam dokumen lain ada yang lapuk.

## Key Architecture Decisions
1. **Laravel standalone** — No ERPNext/Frappe dependency
2. **Multi-tenant SaaS** — Single codebase, tenant_id scoped data isolation
3. **4-tier Admin Hierarchy** — Master → Super → White-label → Customer
4. **Platform Business** — Brand portfolio, revenue sharing, white-label reseller
5. **Domain Modules** — CRM, POS, Invoicing, Finance, Payroll, HRMS, Inventory, Manufacturing, SCM, Logistics, Orders, Booking, Site, Marketplace, Loyalty, Affiliate, Marketing, Blast, Prospecting, Analytics, BrainHub, plus **AI layer** (AIGateway, AIAgents, Boardroom, AgentStudio, McpStudio, BusinessIntel, ADE) and platform core (Entitlement, Fundamental, IndustryPacks) and **Billing** (reseller komisen berulang)
6. **Integrasi 2026** — 15 modul daripada sumber terbuka (OpenGenAI, VoiceStudio, OpenWhispr, OpenSEO, Workspace, WorkManagement, Scheduling, SharedInbox, ScreenRecorder, VideoStudio, POSBpos, OmniOrder, SocialBridge, ContentForge, WhatsAppSuite)
7. **AI Token Wallet** — Separate from SaaS subscription
8. **Industry Blueprint Engine** — Auto-onboarding by business type

## Tech Stack
| Layer | Technology |
|-------|-----------|
| Backend | **Laravel 12.70.1**, **PHP 8.2.12** |
| Database | SQLite (dev) / **MySQL 8.x** (prod) |
| Cache/Queue | Laravel cache + queue (database driver by default) |
| Frontend | **Blade + Vite** (no separate SPA framework) |
| Admin Panel | **Filament 3.3.53** (65 resources, `/admin`) |
| Storage | Local disk (dev) / S3-compatible (prod) |

## Development Guidelines
- Follow [`BLUEPRINT-INDUK.md`](BLUEPRINT-INDUK.md) for product specifications
- **Never copy metrics manually** — read `docs/AUDIT-BASELINE.md`. Regenerate with
  `php skrip/jana-baseline.php --tulis`
- All pricing must be database-driven, never hardcoded
- Tenant data isolation via query scopes and authorization policies
- AI agents use tools/API with limited permissions — no direct DB writes
- Audit trail required for all financial, stock, access and AI actions
- Malaysia-first: MYR currency, Asia/Kuala_Lumpur timezone, BM/EN bilingual

## Module Structure
```
ai-bijan/                        # akar repo
├── app/Modules/<Modul>/         # modul domain (provider + config + models + services + controllers + routes + migrations)
├── app/Models/                  # model platform (Tenant, User, Permission, ...)
├── app/Filament/Resources/      # 66 resource UI admin
├── app/Support/Tenancy/         # TenantContext — pusat pengasingan tenant
├── database/migrations/         # migrasi platform (+ migrasi dalam setiap modul)
├── database/seeders/            # seed data
├── routes/api.php               # laluan API utama
├── routes/web.php               # laluan web
├── docs/                        # dokumentasi (indeks: docs/README.md)
├── skrip/                       # skrip penyelenggaraan/diagnostik
├── .github/skills/              # skill AI (pengekodan + video)
└── tests/                       # PHPUnit feature/unit tests
```
