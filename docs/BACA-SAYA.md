# AI BIJAN SaaS — Panduan Pakej & Pelaksanaan (v4.5, 18/10/2026)

## Isi
- `ai-bijan-saas/`: **sistem utama** (Laravel 11+, PHP 8.3+, MySQL/MariaDB). Tanpa ERPNext/Frappe.
  - Platform teras: multi-tenant SaaS, RBAC, audit trail, 2FA, Master/Super/White-label/Customer Admin hierarchy.
  - Subscription & Monetization Engine: pricing plans, AI Token Wallet, coupons, revenue sharing.
  - Brand Portfolio Management: parent brand + sub-brands, custom domain, white-label configuration.
  - Dokumen: 18 templat Malaysia, PDF/Word/ODT/Excel/ODS/CSV/HTML/JSON, 4 tema.
  - Modul sedia ada: Inventory & Warehouse, BOM & Manufacturing, Projects, Assets, Helpdesk.
  - `PASANG.md`: panduan pasang (cPanel). Ujian: `php artisan test` (43 ujian).
- `rujukan-ui-v25/pos/`: skrin Next.js gaya v2.5 (versi lama atas ERPNext) untuk **rujukan reka bentuk** UI baru.
- `docs/`: dokumen V3.0 owner, seni bina v2.5.
- `laporan/`: laporan Fasa 0/1, dokumen, modul operasi (+ contoh dokumen).

## Mula
```
cd ai-bijan-saas
composer install
cp .env.example .env      # isi DB_*, APP_URL
php artisan key:generate
php artisan migrate --seed
php artisan test
```

## Tidak dimasukkan (sengaja)
`.env` (kata laluan & kunci), `vendor/`, `node_modules/`, log. Jana semula dengan langkah di atas.

## Belum siap
Skrin (UI) untuk semua 18 B-Modules, HR/payroll, POS Laravel, Kewangan/lejar, Pembelian, e-Invois LHDN, Brain Hub, AI Gateway, Industry Blueprint Engine. Templat dokumen perlu semakan profesional.

## Arkitektur v4.5 — Ringkasan Perubahan Utama
1. **Laravel standalone** — Tiada dependency kepada ERPNext, Frappe atau Frappe HR.
2. **Multi-tenant SaaS** — Satu codebase, banyak tenant dengan pengasingan data penuh.
3. **4-tier Admin Hierarchy** — Master → Super → White-label → Customer.
4. **Platform Business** — Brand portfolio, revenue sharing, white-label reseller.
5. **18 B-Modules** — Virtual Office, HRMS, Projects, Assets, Sign & Docs, POS, Site, Link, Marketplace, Loyalty, Omnichannel, Booking, Marketing, Finance, Invoicing, Procurement, Logistics, Analytics, Brain Hub.
6. **AI Token Wallet** — Kredit AI berasingan daripada langganan SaaS.
7. **Industry Blueprint Engine** — Onboarding automatik mengikut jenis perniagaan Malaysia.
