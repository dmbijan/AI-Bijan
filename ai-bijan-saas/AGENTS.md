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

# AI BIJAN SaaS — Master Blueprint v4.5 Context

## Product Overview
AI BIJAN is a multi-tenant SaaS platform for Malaysian businesses (SMEs, retail, F&B, services). Built with Laravel 11+, PHP 8.3+, MySQL/MariaDB.

**Tagline:** *Your AI Business Team, in the Room.*

## Key Architecture Decisions
1. **Laravel standalone** — No ERPNext/Frappe dependency
2. **Multi-tenant SaaS** — Single codebase, tenant_id scoped data isolation
3. **4-tier Admin Hierarchy** — Master → Super → White-label → Customer
4. **Platform Business** — Brand portfolio, revenue sharing, white-label reseller
5. **18 B-Modules** — Virtual Office, HRMS, Projects, Assets, Sign & Docs, POS, Site, Link, Marketplace, Loyalty, Omnichannel, Booking, Marketing, Finance, Invoicing, Procurement, Logistics, Analytics, Brain Hub
6. **AI Token Wallet** — Separate from SaaS subscription
7. **Industry Blueprint Engine** — Auto-onboarding by business type

## Tech Stack
| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11+, PHP 8.3+ |
| Database | MySQL 8.x / MariaDB 10.6+ |
| Cache/Queue | Redis + Laravel Horizon |
| Realtime | Laravel Reverb |
| Frontend | Next.js 15 + React 19 + Tailwind CSS |
| Admin Panel | Filament PHP |
| Storage | Cloudflare R2 / AWS S3 |

## Development Guidelines
- Follow Master-Blueprint.md for product specifications
- All pricing must be database-driven, never hardcoded
- Tenant data isolation via query scopes and authorization policies
- AI agents use tools/API with limited permissions — no direct DB writes
- Audit trail required for all financial, stock, access and AI actions
- Malaysia-first: MYR currency, Asia/Kuala_Lumpur timezone, BM/EN bilingual

## Module Structure
```
ai-bijan-saas/
├── app/Models/          # Eloquent models per module
├── app/Http/            # Controllers, middleware
├── app/Services/        # Business logic services
├── app/Jobs/            # Queue jobs
├── database/migrations/ # Schema migrations
├── database/seeders/    # Seed data
├── routes/api.php       # API routes
├── routes/web.php       # Web routes
└── tests/               # PHPUnit feature/unit tests
```
