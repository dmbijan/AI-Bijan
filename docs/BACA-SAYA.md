# BACA-SAYA — Panduan Pantas AI BIJAN SaaS

> **Dikemas kini:** 26 Oktober 2026 · menggantikan versi v4.5 (18 Okt 2026) yang lapuk
> Indeks penuh: [`README.md`](README.md)

---

## Ini Apa?

**AI BIJAN SaaS** — *AI-Native Business OS* untuk PKS Malaysia/SEA.
Aplikasi **Laravel 12 tulen** (bukan ERPNext/Frappe), multi-tenant, modular, API-first.

---

## Fakta Teknikal (DISAHKAN 9 Nov 2026)

| Metrik | Nilai |
|---|---|
| PHP | **8.2.12** |
| Laravel | **12.70.1** |
| Pangkalan data (dev) | **SQLite** |
| Modul | **75** |
| Jadual | **440** |
| Laluan API (`api/v1`) | **1,629** |
| Ujian | **1,424 lulus** (23 dilangkau, 0 gagal) |
| Assertion | **6,121** |
| Resource Filament | **66** |
| Model · Service · Controller | **372 · 197 · 150** |
| Advisori keselamatan | **0** |

> ⚠️ **Angka ini tidak boleh diedit manual.** Sumber kebenaran:
> [`AUDIT-BASELINE.md`](AUDIT-BASELINE.md). Jana semula dengan
> `php skrip/jana-baseline.php --tulis`.

---

## Isi Repositori

| Lokasi | Isi |
|---|---|
| `app/Modules/` | **75 modul** domain (setiap satu provider + config + routes sendiri) |
| `app/Filament/` | **66 resource** UI admin di `/admin` |
| `app/Support/Tenancy/` | `TenantContext` — pusat pengasingan tenant |
| `database/migrations/` | Migration platform + migration dalam setiap modul |
| `routes/api.php` | Laluan API utama |
| `docs/` | **49 dokumen** aktif — mula dengan [`README.md`](README.md) |
| `docs/archive/` | Blueprint & spesifikasi lama (ARKIB — bukan rujukan) |
| `skrip/` | **36 skrip** penyelenggaraan/diagnostik |
| `.github/skills/` | Skill pengekodan AI + skill video (mograph/motion/editor) |
| `tests/` | **118 fail ujian** (termasuk 75 ujian pengasingan tenant) |

> 📊 **Angka rasmi:** [`AUDIT-BASELINE.md`](AUDIT-BASELINE.md). Jalankan
> `php skrip/jana-baseline.php --tulis` untuk jana semula.

---

## Mula Pantas

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php skrip/sync-perm.php          # WAJIB
php artisan serve --host=127.0.0.1 --port=8145
```

Buka `http://127.0.0.1:8145/admin`.

Panduan penuh: [`getting-started.md`](getting-started.md)

---

## Modul Mengikut Kategori

**Jualan & Pemasaran (8)** — CRM · Marketing · Blast · Prospecting · SalesCopilot · Loyalty · Affiliate · Link

**AI & Automasi (10)** — AIAgents · AIGateway · PersonalAssistant · Boardroom · FlowAutomation · ExecutiveAI · AgentStudio · McpStudio · WorkflowStudio · BusinessIntel

**Operasi & Logistik (10)** — Inventory · Manufacturing · SCM · Logistics · Fleet · Cod · Booking · POS · Orders · Reminder

**Kewangan & People (7)** — Finance · Invoicing · Payroll · COGS · HRMS · Projects · Assets

**Platform & Komunikasi (9)** — Site · Marketplace · Omnichannel · SignDocs · DocumentAI · Helpdesk · IndustryPacks · LiveSupport · Community

**Lain-lain** — Analytics · BrainHub · ProofOfValue · Vision · MediaStudio · ContentStudio · MediaEditor · Seo · ServiceOS · Expense · **ADE**

Katalog penuh: [`KATALOG-MODUL-AI-BIJAN.md`](KATALOG-MODUL-AI-BIJAN.md)

---

## Apa Yang SUDAH Siap

- ✅ **59 modul** backend dengan pengasingan tenant (`BelongsToTenant`)
- ✅ **UI admin Filament** — 66 resource CRUD, 6 widget, halaman Tetapan
- ✅ **AI Gateway** dengan wallet, `PromptGuard` (anti prompt-injection), redaksi PII
- ✅ **Pematuhan MY** — EPF/SOCSO/EIS/PCB/HRDF, e-Invois LHDN, biometrik + pagar PDPA
- ✅ **ADE** (BAB 92) — skills engine, git worktree isolation, sub-agent coordinator, review surface
- ✅ **0 advisori keselamatan** (Laravel 12.70.1)

## Apa Yang BELUM Siap (jujur)

- ⏸️ **B-Receipt & B-Vision** (BAB 90-91) — **spesifikasi sahaja**, tiada kod
- ⏸️ **ADE pelaksanaan kod sebenar** — callback sedia, sambungan enjin P2
- ⏸️ **Qdrant** — RAG guna hash-v1 (padanan perkataan, bukan semantik)
- ⏸️ **Connector Pack 20 sistem** (T-75), **Migration Wizard** (T-72)
- ⏸️ **SOC 2 / ISO 27001** — tiada
- ⏸️ **Integrasi bayaran** (FPX) — tiada
- ⏸️ **Telefoni sebenar** untuk LiveSupport — perlu kredensial
- ⏸️ **Renderer media** (FFmpeg/Sharp) — worker luar belum

---

## Amaran Penting

| ⚠️ | Perkara |
|---|---|
| **WhatsApp** | Jangan guna `whatsapp-web.js` — guna WhatsApp Business Cloud API |
| **Menu tersembunyi** | Bukan kawalan keselamatan. API dikawal `perm:` + `module:` |
| **`migrate:fresh`** | Memadam data dev — jalankan semula `db:seed` + `cipta-tenant-ujian.php` |
| **`rmdir app\Filament\Resources`** | Memadam resource manual — jalankan `pulih-pos.php` selepas `jana-resource.php` |
| **Produksi** | `composer audit` mesti 0, ujian staging, `sync-perm.php` pada DB produksi |
| **Angka dokumen** | Sentiasa `php skrip/semak-dokumen.php` sebelum mempercayai angka |

---

## Fail Tidak Dimasukkan (sengaja)

`.env` (kata laluan & kunci), `vendor/`, `node_modules/`, log, `database/database.sqlite`.
Jana semula dengan langkah di atas.
