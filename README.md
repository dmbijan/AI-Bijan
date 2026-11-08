<p align="center">
  <h1>AI BIJAN SaaS</h1>
  <p><em>Your AI Business Team, in the Room.</em></p>
  <p><strong>AI-Native Business Operating System</strong> untuk PKS Malaysia/SEA</p>
</p>

<p align="center">
  <a href="docs/NIRVANA-AI-BIJAN.md"><strong>🎯 Ringkasan</strong></a> ·`r`n  <a href="docs/BACA-SAYA.md"><strong>Mula Pantas</strong></a> ·
  <a href="docs/AUDIT-BASELINE.md"><strong>Baseline</strong></a> ·
  <a href="docs/README.md"><strong>Indeks Dokumentasi</strong></a> ·
  <a href="docs/TODO.md"><strong>TODO</strong></a> ·
  <a href="docs/BLUEPRINT-INDUK.md"><strong>Blueprint</strong></a>
</p>

---

## ⚠️ Status: `DEVELOPMENT` — belum production-ready

Repositori ini mempunyai implementasi kod yang meluas dan disahkan, tetapi
**belum** disahkan untuk penggunaan komersial:

- 🔌 **Tiada integrasi luar yang aktif** — Meta/WhatsApp, CHIP/FPX, LHDN e-Invois,
  Google/Meta Ads dan penghantar kurier belum disambungkan (perlu akaun +
  kelulusan provider).
- 🗄️ **Belum disahkan pada MySQL** — dev berjalan pada SQLite.
- 🔐 **Audit keselamatan belum lengkap** — belum ada penetration test, load test,
  atau drill backup/restore.
- 📋 **Pematuhan belum disahkan** — PDPA, GST, e-Invois, EPF/SOCSO belum
  disahkan oleh pihak berkuasa/pakar.

Rujuk [`docs/AI-BIJAN-AUDIT-INDUK-LENGKAP.md`](docs/AI-BIJAN-AUDIT-INDUK-LENGKAP.md)
untuk matriks keutamaan P0–P3 dan senarai semak pelepasan.

---

## 🚀 Pantas Pantas

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php skrip/sync-perm.php          # WAJIB — selaraskan kebenaran
php artisan serve --host=127.0.0.1 --port=8145
```

Buka `http://127.0.0.1:8145/admin`.

Panduan penuh: [`docs/getting-started.md`](docs/getting-started.md)

---

## 🏗️ Stack Teknikal

| Lapisan | Teknologi |
|---|---|
| Backend | **Laravel 12.70.1** · **PHP 8.2.12** |
| Pangkalan data | SQLite (dev) · MySQL 8.x (prod) |
| Admin panel | **Filament 3.3.53** — 66 resource di `/admin` |
| Auth | Laravel Sanctum + `spatie/laravel-permission` |
| Cache / Queue | Laravel database driver |
| Frontend | Blade + Vite |
| AI Gateway | Mod simulasi (`AI_GATEWAY_LIVE=false`) |

---

## 📦 Struktur Repositori

| Lokasi | Isi |
|---|---|
| `app/Modules/` | **75 modul** domain — setiap satu provider + config + routes sendiri |
| `app/Filament/` | **66 resource** UI admin |
| `app/Support/Tenancy/` | `TenantContext` — pusat pengasingan tenant |
| `routes/api.php` | Laluan API utama |
| `docs/` | **49 dokumen** aktif · `archive/` untuk versi lama |
| `skrip/` | **36 skrip** penyelenggaraan/diagnostik |
| `.github/skills/` | Skill pengekodan AI + 3 skill video |
| `tests/` | **118 fail ujian** termasuk 75 ujian pengasingan tenant |

> ⭐ **Angka rasmi** sentiasa di [`docs/AUDIT-BASELINE.md`](docs/AUDIT-BASELINE.md).
> Jana semula: `php skrip/jana-baseline.php --tulis`

---

## 🧩 75 Modul Mengikut Domain

| Domain | Modul |
|---|---|
| **Kewangan & Perbendaharaan** | Finance · Invoicing · Billing · Expense · COGS · Payroll · Cod · Fundamental |
| **Inventori & Logistik** | Inventory · SCM · Manufacturing · Logistics · Fleet · Assets |
| **Jualan & Komersial** | POS · Orders · Marketplace · Omnichannel · Booking · ServiceOS · Site · Loyalty · Affiliate · ADE |
| **CRM & Sumber Manusia** | HRMS · CRM · Prospecting · PersonalAssistant · VirtualOffice · Boardroom · SalesCopilot · LiveSupport · TeamAI · Vision · Projects |
| **Pemasaran & Komunikasi** | Marketing · ContentStudio · Blast · Link · Seo · Community · Reminder · McpStudio |
| **AI & Automasi** | AIGateway · AIAgents · ExecutiveAI · AgentStudio · BrainHub · FlowAutomation · WorkflowStudio · DocumentAI · ProofOfValue |
| **Dokumen, Media & Analitik** | Analytics · BusinessIntel · MediaStudio · SignDocs · MediaEditor |
| **Platform & Sokongan** | Entitlement · IndustryPacks · Helpdesk |
| **Integrasi 2026** | OpenGenAI · VoiceStudio · OpenWhispr · OpenSEO · Workspace · WorkManagement · Scheduling · SharedInbox · ScreenRecorder · VideoStudio · POSBpos · OmniOrder · SocialBridge · ContentForge · WhatsAppSuite |

Senarai penuh: [`docs/SENARAI-MODUL.md`](docs/SENARAI-MODUL.md)

---

## 🔌 Integrasi 2026

| Sumber | Modul | Rujukan |
|---|---|---|
| Higgsfield · ElevenLabs · Semrush · Notion · Wispr Flow · Asana · Calendly · Intercom · Loom | 9 modul | [`docs/INTEGRASI-SUMBER-TERBUKA.md`](docs/INTEGRASI-SUMBER-TERBUKA.md) |
| B-POS | `POSBpos` | [`docs/INTEGRASI-BPOS.md`](docs/INTEGRASI-BPOS.md) |
| Luxana · Post Bridge · SEO Writing · WasapBot | 4 modul | [`docs/INTEGRASI-4-LAMAN-WEB.md`](docs/INTEGRASI-4-LAMAN-WEB.md) |
| VOYNANN (rebrand) | `VideoStudio` | [`docs/INTEGRASI-VOYNANN.md`](docs/INTEGRASI-VOYNANN.md) |

---

## 🔐 Pengasingan Tenant

Setiap rekod-owned menggunakan trait `App\Models\Concerns\BelongsToTenant`:

- Skop global automatik — `find()`/`count()` tidak menembusi tenant lain
- `tenant_id` tidak boleh dipalsukan atau diubah selepas dicipta
- Setiap laluan API dilindungi `auth:sanctum` + `tenant` + `perm:<modul>.<tindakan>`

Liputan: **75/75 modul** mempunyai ujian pengasingan.

```bash
php skrip/audit-tenant.php            # semak liputan
php vendor/bin/phpunit --filter "TenantIsolationTest"
```

---

## 🛠️ Skrip Selenggaraan

| Skrip | Tujuan |
|---|---|
| `jana-baseline.php` | Jana semula `docs/AUDIT-BASELINE.md` (sumber kebenaran angka) |
| `audit-tenant.php` | Audit liputan ujian pengasingan tenant |
| `semak-kebenaran.php` | Audit laluan tanpa pagar `perm:` |
| `sync-perm.php` | Selaraskan jadual `permissions` dengan `config/aibijan.php` |
| `semak-dokumen.php` | Semak ketepatan dokumen |
| `semak-pautan-dokumen.php` | Semak pautan markdown rosak |
| `susun-repo.php` | Arkibkan blueprint duplikat |
| `bina-cache-laravel.php` | Bina semula `bootstrap/cache` |
| `fix-bom.php` | Buang BOM daripada fail PHP |

---

## 📚 Dokumentasi

Mula di [`docs/README.md`](docs/README.md) — indeks lengkap mengikut kategori.

---

## 📄 Lesen

Proprietary — lihat [`docs/THIRD-PARTY-NOTICES.md`](docs/THIRD-PARTY-NOTICES.md)
untuk notis komponen pihak ketiga.
