<p align="center">
  <h1>AI BIJAN SaaS</h1>
  <p><em>Your AI Business Team, in the Room.</em></p>
  <p><strong>AI-Native Business Operating System</strong> untuk PKS Malaysia</p>
</p>

<p align="center">
  <a href="docs/SISTEM-AI-BIJAN.md"><strong>Dokumen Sistem</strong></a> ·
  <a href="docs/AUDIT-BASELINE.md"><strong>Baseline</strong></a> ·
  <a href="docs/AI-BIJAN-AUDIT-INDUK-LENGKAP.md"><strong>Audit</strong></a> ·
  <a href="docs/TODO.md"><strong>TODO</strong></a>
</p>

---

> ### ⚠️ Status: `DEVELOPMENT` — **belum production-ready**
>
> Repositori ini **mempunyai** implementasi kod yang meluas (60 modul, ~877 laluan
> API, ~1,119 kaedah ujian) tetapi **belum** disahkan untuk penggunaan komersial.
> Secara khusus:
>
> - ❌ **Tiada integrasi luar yang aktif** — Meta/WhatsApp, CHIP/FPX, LHDN e-Invois,
>   Google/Meta Ads dan penghantar kurier **belum** disambungkan (perlu akaun +
>   kelulusan provider).
> - ❌ **Belum disahkan pada MySQL** — dev berjalan pada SQLite.
> - ❌ **Audit keselamatan belum lengkap** — belum ada penetration test, load test,
>   atau drill backup/restore.
> - ❌ **Pematuhan belum disahkan** — PDPA, GST, e-Invois, EPF/SOCSO belum
>   disahkan oleh pihak berkuasa/pakar.
>
> Rujuk [`docs/AI-BIJAN-AUDIT-INDUK-LENGKAP.md`](docs/AI-BIJAN-AUDIT-INDUK-LENGKAP.md)
> §15–§17 untuk matriks keutamaan P0–P3 dan senarai semak pelepasan.

---

## Stack teknikal

| Lapisan | Teknologi |
|---|---|
| Backend | **Laravel 12.70.1**, **PHP 8.2.12** |
| Pangkalan data | SQLite (dev) · MySQL 8.x (prod) |
| Cache / Queue | Laravel database driver |
| Frontend | Blade + Vite (tiada SPA framework berasingan) |
| Admin panel | **Filament 3.3.53** — 65 resource, `/admin` |
| Auth | Laravel Sanctum + `spatie/laravel-permission` |
| AI | `AIGateway` — satu pintu masuk untuk semua model |

Team AI Workspace menyegerakkan aset AI pasukan daripada repository GitHub. Lihat
[`docs/TEAM-AI-WORKSPACE.md`](docs/TEAM-AI-WORKSPACE.md) untuk skop dan konfigurasi.

---

## Pasang & jalankan

**Prasyarat:** PHP **8.2+** (ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `dom`,
`gd`, `zip`), Composer 2, Node.js 20+.

```sh
# 1) Pasang dependencies
composer install
npm install --ignore-scripts

# 2) Sediakan persekitaran
cp .env.example .env
php artisan key:generate

# 3) Pangkalan data (SQLite lalai — portable, tiada pelayan diperlukan)
touch database/database.sqlite
php artisan migrate --seed

# 4) Bina frontend
npm run build

# 5) Jalankan
php artisan serve
```

Admin Filament: <http://localhost:8000/admin>

> **Pintasan:** `composer setup` menjalankan langkah 1–4 secara automatik.

### Tukar ke MySQL (untuk persediaan prod)

```sh
pwsh -File skrip/tukar_db.ps1 -Ke mysql
php artisan migrate:fresh --seed
php artisan test
```

Skrip `skrip/semak_mysql.php` telah mengaudit keserasian **58** fail migration
terhadap MySQL (0 SQL khusus SQLite, 0 indeks melebihi 3072 bait).

---

## Ujian & pengesahan

```sh
php artisan test                        # suite penuh
php artisan test --filter=Pengasingan    # ujian pengasingan tenant
composer audit                          # advisori keselamatan
php skrip/semak-dokumen.php             # bandingkan angka dokumentasi vs kod
php skrip/audit-tenant.php              # audit pengasingan tenant
php skrip/audit-ui.php                  # audit UI Filament
```

Skrip `semak-dokumen.php` turut memantau dua kelas pepijat sebenar:
`laluan_berganda` (`api/v1/finance/finance/*`) dan `laluan_wildcard` (`api/v1/{id}`).
Kedua-duanya mesti **0**.

---

## Struktur

```
ai-bijan-saas/
├── app/
│   ├── Modules/          # 60 modul mengikut subdomain (modular monolith)
│   ├── Filament/         # 65 resource admin + 6 widget
│   ├── Models/           # model platform & shared
│   ├── Http/             # middleware, controller teras
│   └── Services/         # servis teras
├── database/
│   ├── migrations/       # 58 fail
│   └── seeders/
├── routes/
│   ├── api.php           # laluan teras + auth
│   ├── marketing.php
│   └── web.php
├── skrip/                # skrip penyelenggaraan & audit
├── tests/
│   ├── Feature/          # 89 fail ujian (termasuk 39 ujian pengasingan tenant)
│   ├── Support/
│   └── Unit/
└── docs/                 # 141+ dokumen — mula dengan README.md
```

### Kontrak modul

Setiap modul mengikut corak yang sama:

```
app/Modules/<Modul>/
├── <Modul>ServiceProvider.php
├── config/               # config modul
├── Models/
├── Services/             # logik domain
├── Http/Controllers/
├── database/migrations/  # prefix jadual modul
└── routes/api.php        # didaftar oleh ServiceProvider sahaja
```

> ⚠️ **Jangan** daftar laluan modul dalam `routes/api.php` — itu menyebabkan
> prefix berganda. Modul mendaftarkan laluannya sendiri melalui ServiceProvider.

---

## Prinsip seni bina

1. **Laravel standalone** — tiada dependency wajib ERPNext/Frappe.
2. **Multi-tenant** — pengasingan data melalui `BelongsToTenant` (global scope +
   auto-fill `tenant_id` + halang ubah). `tenant_id` sahaja **bukan** bukti
   keselamatan — setiap laluan wajib `middleware('tenant')` + `perm:`.
3. **Hierarki 4 tahap** — Master → Super → White-label → Customer Admin.
4. **Satu AI Gateway** — semua panggilan model mesti melalui
   `AIGateway\Services\GatewayService`. Modul **dilarang** memanggil provider AI
   secara terus. `PromptGuard` berjalan sebagai langkah 0.
5. **Harga ber-basis pangkalan data** — jangan hardcode harga.
6. **Audit trail wajib** — untuk setiap tindakan kewangan, stok, akses dan AI.
7. **Malaysia-first** — MYR, `Asia/Kuala_Lumpur`, dwibahasa BM/EN.

---

## Modul (59)

| Kategori | Modul |
|---|---|
| **Teras platform** | `Entitlement`, `Fundamental`, `IndustryPacks` |
| **Jualan & saluran** | `CRM`, `Orders`, `POS`, `Omnichannel`, `Marketplace`, `Site`, `Booking`, `Cod`, `Logistics`, `Fleet` |
| **Katalog & stok** | `Inventory`, `Manufacturing`, `SCM`, `Loyalty`, `Affiliate` |
| **Kewangan** | `Invoicing`, `Finance`, `Expense`, `Payroll`, `COGS`, `Billing` |
| **Orang & operasi** | `HRMS`, `Projects`, `Assets`, `ServiceOS` |
| **Pemasaran** | `Marketing`, `Blast`, `ContentStudio`, `MediaStudio`, `MediaEditor`, `Seo`, `Link`, `SalesCopilot`, `Prospecting`, `ProofOfValue` |
| **AI & ejen** | `AIGateway`, `AIAgents`, `AgentStudio`, `Boardroom`, `PersonalAssistant`, `ExecutiveAI`, `BusinessIntel`, `BrainHub`, `McpStudio`, `FlowAutomation`, `WorkflowStudio`, `ADE` |
| **Lain-lain** | `Analytics`, `DocumentAI`, `SignDocs`, `Helpdesk`, `LiveSupport`, `Community`, `VirtualOffice` |

---

## Dokumentasi

| Fail | Kandungan |
|---|---|
| [`docs/README.md`](docs/README.md) | Indeks penuh dokumentasi |
| [`docs/AUDIT-BASELINE.md`](docs/AUDIT-BASELINE.md) | **Satu-satunya sumber kebenaran angka** |
| [`docs/SISTEM-AI-BIJAN.md`](docs/SISTEM-AI-BIJAN.md) | Dokumen sistem lengkap |
| [`docs/AI-BIJAN-AUDIT-INDUK-LENGKAP.md`](docs/AI-BIJAN-AUDIT-INDUK-LENGKAP.md) | Audit induk — jurang, risiko, pelan P0–P3 |
| [`docs/AI-BIJAN-MASTER-BLUEPRINT.md`](docs/AI-BIJAN-MASTER-BLUEPRINT.md) | Blueprint induk (v9.6) |
| [`docs/KATALOG-MODUL-AI-BIJAN.md`](docs/KATALOG-MODUL-AI-BIJAN.md) | Katalog terperinci setiap modul |
| [`docs/TODO.md`](docs/TODO.md) | Kerja belum siap (P0–P5) |
| [`docs/AGENTS.md`](docs/AGENTS.md) | Garis panduan untuk ejen kod |
| [`docs/archive/`](docs/archive/) | Arkib — **bukan** rujukan authoritative |

---

## Projek berkaitan

| Projek | Keterangan |
|---|---|
| [`../b-pos-system/`](../b-pos-system/) | **Produk berasingan** — sistem POS lengkap (RM88/tahun). 8/9 ciri unik telah dipindahkan ke modul `POS/`; lihat `docs/AI-BIJAN-AUDIT-INDUK-LENGKAP.md` § Integrasi B-POS. |

---

## Lesen

Hak cipta © Bijan Digital Marketing. Asas B-POS terhasil daripada
[aryadwiputra/point-of-sale](https://github.com/aryadwiputra/point-of-sale) (MIT) —
notis penuh dalam [`THIRD-PARTY-NOTICES.md`](THIRD-PARTY-NOTICES.md).

---

## Menyumbang

Projek ini milik **Bijan Digital Marketing**. Untuk isu keselamatan dalam AI BIJAN
(report vulnerability), gunakan[e-mel proprietari](mailto:security@ai-bijan.my) —
**jangan** buka isu GitHub awam.

Panduan ejen kod: [`docs/AGENTS.md`](docs/AGENTS.md) ·
skill: [`.github/skills/aibijan-coding/`](.github/skills/aibijan-coding/)
