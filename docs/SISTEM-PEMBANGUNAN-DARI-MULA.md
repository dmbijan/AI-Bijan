# AI BIJAN — SISTEM PEMBANGUNAN DARI MULA (Dokumen Induk Tunggal)

> **Satu dokumen. Bina semula AI BIJAN dari sifar hingga produksi.**
>
> **Versi:** 1.0 · **Tarikh:** 26 Oktober 2026
> **Sasaran:** Laravel **12.70.1** · PHP **8.2.12** · Filament **3.3.53** · PHPUnit **11.5** · SQLite (dev) / MySQL 8 (prod)
> **Blueprint:** `docs/AI-BIJAN-MASTER-BLUEPRINT.md` → **v9.6**, **103 bab / 27 PART**
> **Baseline metrik:** `docs/AUDIT-BASELINE.md`
> **Kaedah:** setiap angka dan perangkap dalam dokumen ini diterbitkan daripada kod sebenar
> (`app/`, `database/migrations/`, `routes/`, `tests/`, `config/`) — bukan hasrat atau pelan.

---

## ISI KANDUNGAN

| # | Bahagian | Isi |
|---|---|---|
| **1** | Ringkasan Sistem | Janji produk, skala sebenar, apa yang BUKAN |
| **2** | Keperluan & Pemasangan | Versi tepat, pakej, perangkap composer, setup dari kosong |
| **3** | 10 Prinsip Wajib | Peraturan yang setiap satunya mengatasi pepijat pengeluaran |
| **4** | Seni Bina 4 Lapisan | UI → API → Domain → Asas |
| **5** | Teras 1 — Skema Platform | 13 jadual platform + jadual sokongan + konvensyen |
| **6** | Teras 2 — Pengasingan Tenant | `TenantContext` + `BelongsToTenant` (kod penuh) |
| **7** | Teras 3 — RBAC & Entitlemen | `perm:` + `module:` + `ModuleGate::ALIAS` |
| **8** | Teras 4 — Middleware Chain | `ResolveTenant`, akses sokongan, idempotency |
| **9** | Teras 5 — Audit & Outbox | `AuditLogger`, kunci idempoten, peristiwa domain |
| **10** | Anatomi Modul | Templat provider/model/service/controller/route |
| **11** | Peta 58 Modul | Setiap modul, prefix, awalan jadual, bilangan laluan |
| **12** | 7 Corak Domain Berulang | Status, polimorfik, penomboran, wang, outbox |
| **13** | Lapisan AI | `GatewayService::call()` 8 langkah, PromptGuard, wallet |
| **14** | Otonomi & Kelulusan | Tahap 0–4, tindakan sentiasa perlu lulus |
| **15** | UI Filament | 7 peraturan wajib, kebenaran 2 lapis, CSP |
| **16** | Ujian | Trait, kategori wajib, perangkap PHPUnit 11 |
| **17** | Penjadual & Queue | Konteks tenant setiap iterasi |
| **18** | Keselamatan & PDPA | Senarai semak, biometrik 6 pagar |
| **19** | Pematuhan Malaysia | EPF/SOCSO/EIS/HRDF/PCB/e-Invois |
| **20** | Corak Jualan & Harga | Positioning, pesaing disahkan, Reseller |
| **21** | 12 Fasa Pembinaan | Urutan kerja 83–118 hari |
| **22** | Skrip & Alat | `skrip/*` + fungsi setiap satu |
| **23** | Senarai Semak Penerimaan | Struktur, keselamatan, AI, UI, operasi |
| **24** | Status Jujur | Apa siap, apa belum, skor 7 dimensi |
| **25** | Perangkap Mengikut Kategori | Migrasi, model, laluan, Filament, AI, ujian |
| **26** | Arahan Pengesahan | Angka patut, wildcard, health, audit |
| **27** | Glosari & Rujukan | Istilah + pautan dokumen lain |

---

# 1. RINGKASAN SISTEM

## 1.1 Apa Yang Dibina

**AI BIJAN** ialah **multi-tenant SaaS modular monolith** — satu aplikasi Laravel
yang menyediakan perniagaan lengkap untuk PKS Malaysia, dengan **satu lapisan AI**,
**satu pengasingan tenant**, dan **satu UI admin**.

| | |
|---|---|
| **Janji produk** | *Ganti 10+ alat perniagaan dengan satu sistem pada RM288–RM1,888/bulan* |
| **Pembeza** | AI yang **memahami aliran kerja** + **pematuhan Malaysia** (bukan harga) |
| **Bukan** | ERP generik · chatbot · himpunan modul berasingan · **ERPNext/Frappe** · microservices · Next.js/React |

## 1.2 Skala Sebenar (disahkan 26 Okt 2026)

| Metrik | Nilai | Cara sahkan |
|---|---|---|
| Modul dikodkan | **58** (+1 spesifikasi: `Reseller`) | `(Get-ChildItem app\Modules -Directory).Count` |
| Jadual pangkalan data | **346** | `php skrip/semak-dokumen.php` |
| Laluan API `api/v1` | **1,518** | `php artisan route:list --path=api/v1` |
| Prefix API | **76** | — |
| Semua laluan (API + web) | **1,795** | `php artisan route:list` |
| Ujian lulus | **963** (4 dilangkau, **0 gagal**) | `php artisan test` |
| Assertion | **5,034** | idem |
| Model | **292** | `php skrip/semak-dokumen.php` |
| Service | **166** | idem |
| Controller (modul) | **127** | idem |
| Resource Filament | **66** | `(Get-ChildItem app\Filament\Resources -Directory).Count` |
| Widget dashboard | **6** | — |
| Kebenaran | **212** | `skrip/sync-perm.php` → jadual `permissions` |
| Fail migration | **49** | idem |
| Kandungan blueprint | **103 bab / 27 PART** | `Select-String -Pattern "^## BAB "` |
| Advisori keselamatan | **0** | `composer audit` |

> ⚠️ **Angka berubah setiap kali kod berubah.** Jika dokumen menyebut satu angka dan
> `php skrip/semak-dokumen.php` menyebut angka lain — **kod betul, dokumen lapuk.**
> Skrip itu exit 1 jika ada percanggahan, jadi ia boleh dipakai dalam CI.

## 1.3 Bentuk Repositori

```
ai-bijan-saas/
├── app/
│   ├── Modules/            59 modul (domain + AI + studio)
│   ├── Models/             model teras (Tenant, User, TenantUser, …)
│   ├── Models/Concerns/    BelongsToTenant  ← trait teras
│   ├── Support/Tenancy/    TenantContext   ← konteks teras
│   ├── Http/Middleware/    ResolveTenant, RequirePermission, ResolveFilamentTenant, …
│   ├── Filament/           66 resource + Widgets + Pages + Concerns
│   ├── Services/           perkhidmatan teras (AuditLogger, TenantProvisioner, …)
│   └── Jobs/               pekerjaan queue
├── bootstrap/providers.php  senarai provider (WAJIB ikut urutan)
├── database/migrations/     49 fail → 346 jadual
├── routes/api.php           laluan teras + sebahagian modul
├── routes/console.php       penjadual
├── config/aibijan.php       sumber kebenaran kebenaran + peranan
├── skrip/                   18 skrip PHP/PowerShell (lihat §22)
├── tests/Feature/           ujian mengikut modul
└── docs/                    dokumen — mulakan dari README.md
```

---

# 2. KEPERLUAN & PEMASANGAN

## 2.1 Versi Tepat

| Komponen | Versi | Nota |
|---|---|---|
| **PHP** | **8.2.12** (minimum 8.2) | Jangan guna sintaks 8.3+ |
| **Laravel** | **12.70.1** | `php artisan --version` |
| Composer | 2.x | |
| **Filament** | **3.3.53** | Jangan cuba Filament 5 |
| PHPUnit | **11.5.x** | Anotasi `/** @test */` tidak sah — guna atribut |
| Node.js | 18+ | Hanya untuk build Vite |
| Pangkalan data | SQLite (dev) / MySQL 8 (prod) | `skrip/tukar_db.ps1` untuk tukar |
| Git | Di PATH | **Wajib** untuk modul `ADE` (worktree) |

⚠️ **Tiada ERPNext/Frappe.** AI BIJAN ialah aplikasi Laravel tulen.

## 2.2 Pakej Wajib

```json
"laravel/framework":       "^12.0",
"filament/filament":       "^3.3.53",
"barryvdh/laravel-dompdf": "^3.1",
"laravel/sanctum":         "*",
"phpunit/phpunit":         "^11.5",
"nunomaduro/collision":    "^8.9"
```

## 2.3 Lima Perangkap Pemasangan (setiap satu pernah menggagalkan `composer update`)

1. **`barryvdh/laravel-dompdf` mesti `^3.1`** — pin `3.0` menyekat `illuminate/support ^11`
   dan `composer update` sahaja **tidak** cukup; `composer.json` mesti diubah.
2. **`collision 8.9` perlukan `phpunit >= 11.5.50`** — naik taraf **kedua-duanya dalam
   satu arahan composer**, jika tidak keluar "conflict analysis result".
3. **Collision tiada v9** — jangan cuba `^9.0`.
4. **Filament CVE-2026-55409** — `<=3.3.52` terjejas, tampalan = **3.3.53**. Tidak perlu Filament 5.
5. **PHP 8.2 tidak benarkan `#[\PHPUnit\...\Test]`** (backslash hadapan = syntax error).
   Mesti `use PHPUnit\Framework\Attributes\Test;` kemudian `#[Test]`.

**Rantaian naik taraf yang berjaya (rujukan):**
Laravel 11.57 → 12.70.1 · PHPUnit 10.5.66 → 11.5.57 · Collision 8.5 → 8.9.5 ·
dompdf 3.0 → 3.1.2 · Filament 3.3.52 → 3.3.53 → **0 advisori**.

## 2.4 Setup Dari Kosong

```powershell
cd ai-bijan-saas

composer install
npm install

Copy-Item .env.example .env
php artisan key:generate

php artisan migrate --seed       # struktur + plans + katalog model AI
php artisan storage:link         # storan awam

php skrip/sync-perm.php          # ⚠️ WAJIB — tanpanya semua halaman 403
php skrip/cipta-tenant-ujian.php # pilihan: tenant dev untuk ujian manual

composer run dev                 # server + queue + vite serentak
# atau
php artisan serve --host=127.0.0.1 --port=8145
```

Buka `http://127.0.0.1:8145/admin`.

**Tenant ujian dev yang dihasilkan:** `ujian@bijan.test` / `Password12345`
(tenant "Syarikat Ujian", plan `enterprise`, 59 modul aktif, peranan `tenant_owner`).

## 2.5 ⚠️ Selepas `migrate:fresh` — Urutan Wajib

```powershell
php artisan migrate:fresh
php artisan db:seed --force        # 1. plans + katalog model AI
php skrip/cipta-tenant-ujian.php   # 2. pengguna + tenant + langganan
php skrip/sync-perm.php            # 3. 212 kebenaran → jadual permissions
```

Melangkau langkah 1 → tiada plan, tenant tidak boleh dicipta.
Melangkau langkah 3 → menu muncul tetapi setiap halaman **403** (bukan 500 — sukar didiagnosis).

---

# 3. SEPULUH PRINSIP WAJIB

Ini bukan cadangan. Setiap satu mengatasi kegagalan pengeluaran sebenar.

| # | Prinsip | Peraturan konkrit |
|---|---|---|
| **1** | **Tenant sentiasa eksplisit** | Setiap model perniagaan guna `BelongsToTenant`. Tiada rekod tanpa `tenant_id`. Tiada query tanpa skop. |
| **2** | **Kebenaran dua lapis** | RBAC (`perm:`) **DAN** entitlemen modul (`module:`). Satu lapis = kebocoran pendapatan. |
| **3** | **Semua AI melalui satu pintu** | Hanya `AIGateway\Services\GatewayService::call()`. Tiada modul memanggil provider terus. |
| **4** | **Manusia kawal tindakan berisiko** | AI boleh cadang; tidak boleh hantar wang, padam data, ubah otonomi sendiri. |
| **5** | **Spesifikasi ≠ pelaksanaan** | Jangan tandakan ✅ untuk ciri belum dikodkan. |
| **6** | **Deny by default** | Tiada kebenaran → tolak. Tiada langganan → tolak. Tiada baseline ujian → tolak. |
| **7** | **Wang sentiasa decimal** | `decimal(15,2)` + cast `decimal:2`. Tidak pernah `float`. |
| **8** | **Tindakan boleh diulang (idempoten)** | Webhook, sync, ingest pesanan guna kunci idempoten. |
| **9** | **Sahkan dalam pelayar sebenar** | Ujian unit lulus ≠ UI berfungsi. CSP, Livewire dan closure Filament hanya gagal dalam pelayar. |
| **10** | **Satu perangkap = satu catatan** | Setiap pepijat yang dibaiki mesti masuk §25 supaya tidak berulang. |

---

# 4. SENI BINA 4 LAPISAN

```
┌───────────────────────────────────────────────────────────────────────┐
│ 4. UI        Filament 3.3 · /admin · 66 resource · 6 widget · 1 halaman │
├───────────────────────────────────────────────────────────────────────┤
│ 3. API       routes/api.php + 59 provider · /api/v1/* · 1,518 laluan    │
│              auth:sanctum → tenant → perm:{ns}.{aksi} → module:{key}    │
├───────────────────────────────────────────────────────────────────────┤
│ 2. DOMAIN    59 modul · 166 service · 292 model · events + outbox       │
├───────────────────────────────────────────────────────────────────────┤
│ 1. ASAS      TenantContext · BelongsToTenant · AuditLogger · 346 jadual │
└───────────────────────────────────────────────────────────────────────┘
```

**Empat peraturan lapisan yang tidak boleh dilanggar:**

1. **UI tidak pernah menyentuh DB terus** — melalui API atau service.
2. **Modul tidak pernah memanggil provider AI** — melalui `GatewayService`.
3. **Modul tidak pernah memproses wang** — hanya `Invoicing`/`Finance`.
4. **Modul tidak pernah menulis ke jadual modul lain** — melalui *public contract*
   (service awam) atau *business event* (outbox).

---

# 5. TERAS 1 — SKEMA PLATFORM

> **Bina §5–§9 sepenuhnya sebelum menyentuh mana-mana modul.** Kesilapan di sini
> tidak boleh dibaiki kemudian tanpa menulis semula setiap modul.

## 5.1 Jadual Platform (13 wajib)

```php
// users (asas Laravel + sambungan)
is_platform_admin       boolean default false   // Super Admin
phone                   string(30) null
locale                  string(5) default 'ms'
two_factor_secret       text null
two_factor_confirmed_at timestamp null
last_login_at           timestamp null
is_active               boolean default true    // ← disemak SETIAP permintaan

// plans
id, code(string40 unique), name,
max_users(uint null), max_locations(uint null), storage_mb(bigint null),
ai_credits_monthly(bigint default 0),
modules(json null),            // senarai module_key
price_monthly(decimal 12,2 default 0), is_active(boolean default true), timestamps

// tenants
id, ulid(unique), name, slug(60 unique),
status(enum: trial|active|past_due|suspended|cancelled) default trial,
plan_id(FK plans, nullOnDelete),
timezone(40) default 'Asia/Kuala_Lumpur',
currency(char3) default 'MYR', locale(5) default 'ms',
trial_ends_at(timestamp null), timestamps

// tenant_users (keahlian)  ⚠️ TIADA kolum `role`
id, tenant_id(FK cascade), user_id(FK cascade),
status(enum: invited|active|disabled) default active,
invited_by(FK users null), invite_token_hash(char64 unique null),
invite_expires_at(timestamp null), joined_at(timestamp null), timestamps,
UNIQUE(tenant_id, user_id)

// permissions (GLOBAL — bukan per-tenant)
id, key(string80 unique), module(string40), description(string null)

// roles (per tenant)
id, tenant_id(FK cascade), key(string40), name,
is_system(boolean default false), timestamps, UNIQUE(tenant_id, key)

// pivot
permission_role:   PK(role_id, permission_id)
role_tenant_user:  PK(tenant_user_id, role_id)

// subscriptions
id, tenant_id(FK cascade), plan_id(FK plans),
status(enum: trial|active|past_due|suspended|cancelled),
starts_at, ends_at(timestamp null), timestamps

// tenant_modules (entitlemen aktif)
id, tenant_id(FK cascade), module(string40), enabled(boolean default true),
UNIQUE(tenant_id, module)

// settings (konfigurasi per tenant)
id, tenant_id(FK cascade), key(string80), value(json null), UNIQUE(tenant_id, key)
```

**Kenapa `tenants.ulid`?** `id` berangka untuk FK dalaman; `ulid` didedahkan kepada
klien (header `X-Tenant`) supaya ID dalaman tidak bocor dan tidak boleh diagak.

**`Tenant::isUsable()`** — `status IN (trial, active)`. Tenant `suspended`/`cancelled`
ditolak di middleware, bukan di controller.

**⚠️ `tenant_users` tiada kolum `role`** — peranan melalui pivot `role_tenant_user`.
Ini punca `MassAssignmentException` jika diandaikan. Guna `TenantProvisioner::create()`.

## 5.2 Jadual Sokongan Teras

| Jadual | Fungsi |
|---|---|
| `files` / `stored_files` | Muat naik, metadata, polisi akses |
| `api_keys` | Kunci API per tenant (disimpan **hashed**) |
| `audit_logs` | Jejak tindakan (siapa, apa, bila, before/after) |
| `approval_requests` | Pintu kelulusan — `type`, `payload`(json), `requested_by`, `decided_by`, `status`, `reason` |
| `idempotency_keys` | Halang pendua permintaan tulis |
| `support_access_grants` | Akses Super Admin (baca sahaja, luput, boleh dibatalkan) |
| `document_sequences` | Nombor dokumen berterusan per tenant/jenis/tahun |
| `documents` | Dokumen generik (invois, resit, kontrak) |
| `health_checks`, `observability_logs` | Observability |
| `plan_limits` | Had per plan (seats, token AI, storan) |

> ⚠️ Jadual kelulusan sebenar bernama **`approval_requests`** — bukan `approvals`.

## 5.3 Konvensyen Skema (WAJIB SERAGAM)

| Perkara | Peraturan |
|---|---|
| Kunci utama | `id()` bigint auto-increment |
| Tenant | `tenant_id` bigint + INDEX — **kecuali** jadual anak |
| Jadual anak | Tiada `tenant_id`; diwarisi daripada induk (49 jadual sedemikian) |
| Masa | `timestamps()` pada setiap jadual — termasuk yang model guna `HasFactory` |
| Soft delete | Hanya bila domain perlu (jika model guna, scope perlu `SoftDeletingScope`) |
| Wang | `decimal(12,2)` / `decimal(15,2)` — **jangan** float |
| Peratus | `decimal(5,2)` |
| JSON | `json()` untuk struktur fleksibel |
| Enum | `enum()` dengan senarai tertutup |
| Awalan jadual | **Wajib** awalan modul untuk elak pertindihan |

**Pertindihan sebenar yang pernah berlaku:**

| Konflik | Penyelesaian |
|---|---|
`workflow_definitions` (FlowAutomation) vs `workflows`/`workflow_runs` (WorkflowStudio) | WorkflowStudio guna `studio_*`
`documents` (SignDocs) vs DocumentAI | DocumentAI guna prefix laluan `document-ai/documents`
`projects` (Api\V1) vs `projects` (Projects module) | Modul guna prefix laluan `pm`
`inventory/warehouses` (Api\V1) vs Inventory module | Inventory guna `inventory/depots/*`
`affiliate_*` vs `reseller_*` | Jangan gabung — dua domain berbeza

---

# 6. TERAS 2 — PENGASINGAN TENANT

Ini komponen **paling kritikal** dalam sistem. Bina sekali, betul.

## 6.1 `TenantContext` — satu-satunya sumber kebenaran

```php
namespace App\Support\Tenancy;

class TenantContext
{
    private ?Tenant $tenant = null;
    private ?TenantUser $membership = null;
    private ?array $permissions = null;

    public function set(Tenant $tenant, ?TenantUser $membership = null, ?array $permissions = null): void
    {
        $this->tenant = $tenant;
        $this->membership = $membership;
        $this->permissions = $permissions;
    }

    /** Akses sokongan Super Admin: kebenaran baca terhad sahaja. */
    public function isSupportAccess(): bool
    {
        return $this->tenant !== null && $this->membership === null && $this->permissions !== null;
    }

    public function clear(): void
    {
        $this->tenant = $this->membership = null;
        $this->permissions = null;
    }

    public function has(): bool { return $this->tenant !== null; }

    public function tenant(): Tenant
    {
        return $this->tenant ?? throw new \RuntimeException('Tiada tenant aktif.');
    }

    public function id(): ?int { return $this->tenant?->id; }

    public function membership(): ?TenantUser { return $this->membership; }

    /** Kunci kebenaran pengguna dalam tenant ini. */
    public function permissions(): array
    {
        if ($this->permissions !== null) return $this->permissions;

        if (! $this->membership || $this->membership->status !== 'active') {
            return $this->permissions = [];
        }

        return $this->permissions = $this->membership->roles()
            ->with('permissions:id,key')->get()
            ->flatMap(fn ($r) => $r->permissions->pluck('key'))
            ->unique()->values()->all();
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}
```

**Peraturan:**
- `set()` dipanggil **hanya** oleh middleware selepas keahlian disahkan.
- `clear()` **wajib** dalam blok `finally` — jika tidak, konteks bocor ke permintaan
  berikutnya (kritikal dalam queue worker).
- `permissions()` pulang `[]` jika membership tiada atau `status !== 'active'`.
- **`TenantContext` tiada method `user()`** — guna `auth()->id()`.

## 6.2 Trait `BelongsToTenant` — 4 lapisan

```php
namespace App\Models\Concerns;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // 1. GLOBAL SCOPE — setiap query auto-tapis
        static::addGlobalScope('tenant', function (Builder $q) {
            $ctx = app(TenantContext::class);
            $q->where(
                $q->getModel()->getTable().'.tenant_id',
                $ctx->has() ? $ctx->id() : 0      // tiada tenant = 0 = tiada rekod
            );
        });

        // 2. CREATING — auto-fill + tolak ketidakpadanan
        static::creating(function ($m) {
            $ctx = app(TenantContext::class);
            if (! $ctx->has()) {
                throw new \RuntimeException('Rekod tenant tidak boleh dicipta tanpa tenant aktif.');
            }
            if ($m->tenant_id && (int) $m->tenant_id !== $ctx->id()) {
                throw new \RuntimeException('tenant_id tidak sepadan dengan tenant aktif.');
            }
            $m->tenant_id = $ctx->id();
        });

        // 3. UPDATING — halang pemindahan
        static::updating(function ($m) {
            if ($m->isDirty('tenant_id')) {
                throw new \RuntimeException('tenant_id tidak boleh diubah.');
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

**Kenapa `0` dan bukan `null`?** `where tenant_id = 0` sentiasa pulang kosong.
`where tenant_id = null` dalam SQL tidak padan apa-apa **melainkan** ditulis `IS NULL` —
mudah tersilap. `0` tidak boleh wujud sebagai ID sebenar.

**Lapisan ke-4** ialah konteks itu sendiri (§6.1). Empat lapisan = empat kali
peluang untuk menghalang kebocoran.

## 6.3 Cipta Tenant — `TenantProvisioner`

```php
// WAJIB: konteks ditetapkan SEBELUM cipta rekod BelongsToTenant
$tenant = TenantProvisioner::create([
    'name' => 'Syarikat Sdn Bhd',
    'plan' => 'growth',
    'owner' => ['name' => 'Ali', 'email' => 'ali@x.my', 'password' => '…'],
]);
// → tenant + subscription + peranan sistem + pemilik sebagai tenant_owner + tenant_modules
```

**Jangan** buat sendiri dengan `TenantUser::create(['role' => …])` — tiada kolum
`role` dan tiada `$fillable` untuk `role`/`tenant_id`.

## 6.4 Ujian Pengasingan Tenant (WAJIB setiap modul)

```php
public function test_tenant_lain_tidak_nampak_data(): void
{
    [$u1, $t1] = $this->daftar('a@x.my', 'Syarat A');
    [$u2, $t2] = $this->daftar('b@x.my', 'Syarat B');

    $this->dalamTenant($t1);
    $rekod = Model::create([/* … */]);

    // ⚠️ withoutGlobalScopes() WAJIB — global scope tapis ikut konteks SEMASA
    $this->dalamTenant($t2);
    $this->assertSame(0, Model::withoutGlobalScopes()
        ->where('id', $rekod->id)
        ->where('tenant_id', $t2->id)->count());
}
```

## 6.5 Lima Perangkap Pengasingan (semua pernah berlaku)

| Perangkap | Kesan | Pembetulan |
|---|---|---|
| `withoutGlobalScope('tenant')` dalam provisioning | Rekod dicipta dengan `tenant_id = 0` | `forceFill(['tenant_id' => $id])` |
| Join + global scope | `ambiguous column name: tenant_id` | `withoutGlobalScopes()` + qualify **semua** kolum + `whereNull('x.deleted_at')` |
| Relation di-cache | Data lama selepas `create()` | Kira terus dari DB (`Model::where(...)->count()`) |
| `dalamTenant($t)` tanpa membership | `permissions()` pulang kosong | `app(TenantContext::class)->set($t, $membership)` |
| Selepas HTTP request dalam ujian | Konteks dikosongkan | Cipta rekod **sebelum** `$this->sebagai()` |
| Queue worker | Konteks dari job sebelumnya | Tetapkan semula dalam **setiap** job dari DB |

---

# 7. TERAS 3 — RBAC & ENTITLEMEN

## 7.1 Format Kunci

```
{namespace}.{aksi}
```

**Aksi standard:** `view` · `manage` · `execute` · `approve` · `reports`
**Aksi domain:** `post` (jurnal) · `sign` (tandatangan) · `finance` (margin) · `sell` (POS)

## 7.2 `config/aibijan.php` — sumber kebenaran

```php
return [
    'permissions' => [
        // Teras platform
        'tenant.settings.view' => 'Lihat tetapan syarikat',
        'members.view' => 'Lihat ahli',
        'members.invite' => 'Jemput ahli',
        'roles.manage' => 'Urus peranan',
        'audit.view' => 'Lihat jejak audit',

        // Domain — satu set bagi setiap modul
        'orders.view'    => 'Lihat pesanan',
        'orders.manage'  => 'Cipta/edit pesanan',
        'orders.execute' => 'Tukar status & sync',

        'ade.view'    => 'Lihat skills, sandbox, coordination & review',
        'ade.manage'  => 'Bina/kemas kini skill, sandbox & review',
        'ade.execute' => 'Jalankan coordination, sub-ejen & semakan diff',
        'ade.approve' => 'Luluskan review kod & gabung perubahan',
    ],

    'roles' => [
        'tenant_owner'      => ['name' => 'Pemilik', 'permissions' => ['*']],
        'tenant_admin'      => ['name' => 'Pentadbir', 'permissions' => [/* eksplisit */]],
        'finance_manager'   => ['name' => 'Pengurus Kewangan', 'permissions' => [/* … */]],
        'sales_manager'     => ['name' => 'Pengurus Jualan', 'permissions' => [/* … */]],
        'hr_manager'        => ['name' => 'Pengurus HR', 'permissions' => [/* … */]],
        'inventory_manager' => ['name' => 'Pengurus Stok', 'permissions' => [/* … */]],
        'cashier'           => ['name' => 'Juruwang', 'permissions' => [/* … */]],
        'staff'             => ['name' => 'Staf', 'permissions' => [/* … */]],
        'auditor'           => ['name' => 'Juruaudit', 'permissions' => [/* … */]],
    ],
];
```

> ⚠️ **`'*'` bermaksud "semua yang WUJUD dalam jadual `permissions`"** — bukan
> "semua yang mungkin". Jika 6 namespace tidak didaftarkan dalam config, pemilik
> tetap dapat **403** walaupun dia `tenant_owner`. Ini pernah berlaku untuk
> `scm`, `vision`, `cod`, `media_studio`, `content_studio`, `workflow_studio`
> (117 → 208 kebenaran selepas dibaiki).

## 7.3 `skrip/sync-perm.php` — WAJIB

1. Baca `config('aibijan.permissions')`
2. `updateOrCreate` setiap kunci → jadual `permissions`
3. Bagi setiap tenant: cipta peranan sistem jika tiada
4. Beri **semua** kebenaran kepada `tenant_owner`

Jalankan **selepas `migrate`**, **selepas setiap tambah kebenaran**, dan **pada DB
produksi sebelum go-live**. Tanpanya: menu muncul, setiap halaman 403.

## 7.4 `ModuleGate` — gate langganan

```php
class ModuleGate
{
    // Namespace yang BUKAN modul domain — sentiasa dibenarkan
    public const TERAS = [
        'members','roles','audit','files','api_keys','approvals',
        'documents','biometric','ai_gateway','tenant','platform',
        'entitlement','fundamental',
    ];

    // Alias: namespace permission → module_key dalam jadual plans
    public const ALIAS = [
        'content'      => 'content_studio',
        'workflow'     => 'workflow_studio',
        'media'        => 'media_studio',
        'media_editor' => 'media_editor',
        'mcp'          => 'mcp_studio',
        'personal_ai'  => 'personal_assistant',
        'ade'          => 'ade',

        // ⚠️ Namespace ≠ kunci modul — TANPA alias, menu tersembunyi / 403
        'finance'     => 'expense',          // Perbelanjaan
        'omnichannel' => 'orders',           // Pesanan
        'projects'    => 'service_os',       // Projek perkhidmatan
        'marketing'   => 'content_studio',   // Kandungan
        'brainhub'    => 'business_intel',   // Idea bisnes
    ];

    public static function ialahModul(string $namespace): bool;   // bukan TERAS
    public static function moduleKey(string $namespace): string;  // ALIAS atau asal
    public static function semak(array $perms, EntitlementService $s, int $tenantId): array;
}
```

**Kenapa alias perlu:** jadual `permissions` guna **namespace kebenaran**; jadual
`plans` guna **kunci modul**. Contoh sebenar: resource Filament "Perbelanjaan"
guna `expense.*` tetapi plan menyenaraikan `expense`; resource "Pesanan" guna
`omnichannel.*` tetapi plan menyenaraikan `orders`.

## 7.5 `EntitlementService` — perangkap skop tenant

```php
// ❌ SALAH — global scope tapis ikut konteks SEMASA, bukan $tenantId
TenantModule::where('tenant_id', $tenantId)->first();

// ✅ BETUL — baca
TenantModule::withoutGlobalScope('tenant')->where('tenant_id', $tenantId)->first();

// ✅ BETUL — cipta
TenantModule::withoutGlobalScope('tenant')
    ->create(['tenant_id' => $tenantId, 'module' => $m, 'enabled' => true]);
```

**Kenapa:** jika konteks kosong (provisioning, CLI, ujian), global scope menapis
`tenant_id = 0` → bacaan pulang kosong → rekod dicipta dengan `tenant_id = 0` →
tenant **tidak pernah** nampak modulnya.

Kaedah yang **mesti** guna `withoutGlobalScope('tenant')` + `forceFill`/array eksplisit:
`grantModul()` · `revokeModul()` · `bolehModul()` · `modulAktif()` · `syncModul()` · `aktifkanModul()`

---

# 8. TERAS 4 — MIDDLEWARE CHAIN

## 8.1 Urutan Wajib

```
Route::prefix('v1')
  → middleware('auth:sanctum')          // 1. Identiti
  → middleware('tenant')                // 2. Konteks tenant
  → middleware('perm:{ns}.{aksi}')       // 3. RBAC + entitlemen
  → controller
```

## 8.2 `ResolveTenant`

```php
public function handle(Request $request, Closure $next)
{
    // 1. Pengguna aktif?
    $user = $request->user();
    abort_unless($user && $user->is_active, 401);

    // 2. Pilih tenant — header ATAU sesi. BUKAN bukti kebenaran.
    $pilih = $request->header('X-Tenant')
        ?? ($request->hasSession() ? $request->session()->get('tenant_ulid') : null);

    // 3. Keahlian AKTIF sahaja
    $q = TenantUser::withoutGlobalScope('tenant')
        ->where('user_id', $user->id)->where('status', 'active');

    $m = $pilih
        ? (clone $q)->whereHas('tenant', fn ($t) => $t->where('ulid', $pilih))->first()
        : null;
    $m ??= $pilih ? null : $q->orderBy('id')->first();

    // 4. Akses sokongan (Super Admin tanpa keahlian)
    if (! $m && $pilih && $user->is_platform_admin) {
        return $this->aksesSokongan($request, $next, $user, $pilih);
    }

    abort_unless($m, 403, 'Anda bukan ahli syarikat ini.');

    // 5. Tenant boleh guna?
    $tenant = Tenant::find($m->tenant_id);
    abort_unless($tenant && $tenant->isUsable(), 403,
        'Akaun syarikat digantung atau dibatalkan.');

    // 6. Tetapkan konteks — WAJIB clear dalam finally
    $this->ctx()->set($tenant, $m);
    try {
        return $next($request);
    } finally {
        $this->ctx()->clear();
    }
}
```

**Akses sokongan (Super Admin) — 4 pagar:**

```php
// 1. 2FA disahkan
abort_unless($user->two_factor_confirmed_at
    && $request->session()->get('2fa_ok') === $user->id, 403);

// 2. Grant sah & belum luput
$grant = SupportAccessGrant::where('tenant_id', $tenant->id)
    ->where('admin_user_id', $user->id)
    ->whereNull('revoked_at')
    ->where('expires_at', '>', now())->latest()->first();
abort_unless($grant, 403);

// 3. BACA SAHAJA
abort_unless($request->isMethod('GET'), 403);

// 4. Kebenaran terhad + audit
const KEBENARAN_SOKONGAN = ['tenant.settings.view', 'members.view', 'audit.view'];
AuditLogger::log('support.access', $tenant, null, ['path' => …, 'grant' => $grant->id]);
```

## 8.3 `RequirePermission` — dua lapisan

```php
public function handle(Request $request, Closure $next, string ...$perms)
{
    // Lapisan 1 — RBAC
    foreach ($perms as $p) {
        abort_unless($this->ctx()->can($p), 403);
    }

    // Lapisan 2 — Entitlemen modul
    if ($perms !== [] && config('entitlement.enabled', true)) {
        $tenantId = (int) $this->ctx()->id();
        if ($tenantId > 0) {
            $hasil = ModuleGate::semak($perms, app(EntitlementService::class), $tenantId);
            if (! $hasil['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modul ini tidak termasuk dalam langganan syarikat anda.',
                    'meta'    => ['module' => $hasil['module'], 'upgrade' => '…'],
                ], 403);
            }
        }
    }

    return $next($request);
}
```

> **Middleware alias ialah `perm:` — bukan `permission:`.**

## 8.4 Middleware Lain

| Middleware | Fungsi |
|---|---|
| `PlatformAdmin` | Had akses ke laluan platform (bukan tenant) |
| `RequireModule` | Gate modul eksplisit: `module:seo,content` |
| `IdempotencyMiddleware` | Halang pendua POST/PUT guna header `Idempotency-Key` |
| `SecureHeaders` | CSP, HSTS, X-Frame-Options |
| `ResolveFilamentTenant` | Konteks tenant untuk panel Filament — **dalam kumpulan `web`** (§15.2) |

---

# 9. TERAS 5 — AUDIT & OUTBOX

## 9.1 `AuditLogger`

```php
app(AuditLogger::class)->log(
    string $action,      // 'tenant.created', 'support.access', 'expense.approved'
    $subject,            // model
    ?array $before,      // snapshot sebelum
    ?array $after        // snapshot selepas
);
```

**Bila WAJIB audit:** cipta/padam tenant · akses sokongan · kelulusan · perubahan
kebenaran · tindakan AI autonomi · eksport data. Setiap rekod mesti ada
`before`/`after` supaya boleh dipertahankan dalam audit luaran.

## 9.2 Idempotency

Permintaan tulis yang boleh diulang (webhook, sync, ingest pesanan):

```php
$key = $request->header('Idempotency-Key');
// Simpan hash tindakan + keputusan dalam `idempotency_keys`
// Jika wujud → pulang keputusan ASAL, JANGAN laksana semula
```

**Corak alternatif untuk ingest domain (outbox):**

```php
$key = md5($eventName . '-' . $aggregate . '-' . $id . '-' . json_encode($payload));
// ServiceOS / ADE guna: slug(event . aggregate . id . md5(payload))
```

## 9.3 Outbox Peristiwa Domain

```php
$table->string('event_name');
$table->json('payload');
$table->string('idempotency_key')->unique();
$table->enum('status', ['pending','dispatched','failed'])->default('pending');
$table->unsignedTinyInteger('attempts')->default(0);
```

**Kenapa outbox:** peristiwa tidak hilang jika queue mati. Idempoten = boleh
dihantar semula tanpa kesan berganda. Had keras (contoh ServiceOS): 100 peristiwa
per batch, 3 cubaan.

---

# 10. ANATOMI MODUL (TEMPLAT WAJIB)

```
app/Modules/<Nama>/
├── <Nama>ServiceProvider.php     # register + boot
├── config/<snake_case>.php       # konfigurasi modul
├── Models/                       # Eloquent (guna BelongsToTenant)
├── Services/                     # logik domain
├── Http/Controllers/             # HTTP sahaja, tiada logik
├── routes/api.php                # laluan
└── README.md                     # dokumentasi modul
```

Migration diletak **global** di `database/migrations/`, bukan dalam modul.
Provider didaftar di `bootstrap/providers.php`.

## 10.1 Provider

```php
namespace App\Modules\ADE;

class ADEServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/ade.php', 'ade');

        $this->app->singleton(AdeSkillService::class);
        $this->app->singleton(AdeSandboxService::class);
        $this->app->singleton(AdeCoordinatorService::class);
        $this->app->singleton(AdeReviewService::class);
        $this->app->singleton(AdeOrchestrator::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware(['api', 'auth:sanctum', 'tenant'])
                ->prefix('api/v1/ade')          // ← PREFIX WAJIB
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
```

> ⚠️ **Tanpa prefix**, modul mencipta wildcard `api/v1/{id}` yang **menangkap semua
> laluan lain**. Pernah menyebabkan `GET /api/v1/projects/{id}` → 404.
> Sahkan: `php artisan route:list --path=api/v1 | Select-String "v1/\{"` → **mesti kosong**.

## 10.2 Model

```php
namespace App\Modules\ADE\Models;

class AdeSkill extends Model
{
    use BelongsToTenant;                    // WAJIB (kecuali model global)

    protected $table = 'ade_skills';        // WAJIB jika nama ≠ plural kelas

    protected $fillable = [
        'tenant_id','key','name','category','description','system_prompt',
        'steps','triggers','tools','config','source','origin_project','license',
        'is_enabled','is_public','install_count','usage_count','success_rate',
    ];

    protected $casts = [
        'steps' => 'array', 'triggers' => 'array',
        'is_enabled' => 'boolean', 'usage_count' => 'integer', 'success_rate' => 'float',
    ];

    protected $attributes = [               // nilai lalai
        'category' => 'general', 'is_enabled' => true, 'is_public' => false,
    ];

    public function invocations(): HasMany
    {
        return $this->hasMany(AdeSkillInvocation::class, 'ade_skill_id');
    }
}
```

> ⚠️ **`$table` wajib** jika nama jadual ≠ `Str::plural(Str::snake(class))`.
> `Loyalty\Models\Reward` tanpa `$table` → Eloquent cari `rewards`, jadual sebenar
> `loyalty_rewards` → 500 "no such table".

> ⚠️ **`$fillable` mesti ada `tenant_id`** jika `create()` dihantar `tenant_id` eksplisit.

## 10.3 Service

```php
class AdeSkillService
{
    // Had keselamatan sebagai KONSTAN (bukan nombor ajaib)
    public const HAD_SKILL_PER_TENANT = 200;
    public const HAD_LANGKAH          = 20;
    public const HAD_PENCETUS         = 15;
    public const MIN_KEY_LENGTH       = 3;
    public const MAX_KEY_LENGTH       = 80;

    public function janaSkill(int $tenantId, array $data): AdeSkill
    {
        $this->guardBilang($tenantId);              // semak had dahulu
        $key = $this->normaliseKey($data['key']);
        $this->guardKey($key);                       // sahkan format

        if ($this->kunciWujud($tenantId, $key)) {
            throw new \InvalidArgumentException("Kunci '{$key}' sudah wujud.");
        }

        // Bina eksplisit, BUKAN mass-assign buta
        $skill = new AdeSkill();
        $skill->tenant_id = $tenantId;
        $skill->key       = $key;
        $skill->name      = $data['name'];
        $skill->steps     = $this->guardSteps($data['steps'] ?? []);
        $skill->triggers  = $this->guardTriggers($data['triggers'] ?? []);
        $skill->save();

        return $skill;
    }

    private function cari(int $tenantId, int $id): AdeSkill
    {
        // Cari DENGAN skop tenant — bukan find() sahaja
        $m = AdeSkill::where('tenant_id', $tenantId)->where('id', $id)->first();
        if ($m === null) throw new \RuntimeException('Tidak dijumpai.');
        return $m;
    }

    private function kunciWujud(int $tenantId, string $key): bool
    {
        // Semak global (NULL) DAN tenant — UNIQUE komposit tidak menangkap NULL
        return AdeSkill::withoutGlobalScopes()
            ->where('key', $key)
            ->where(fn ($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'))
            ->exists();
    }
}
```

**Corak service:** (1) sahkan input → (2) cari **dengan skop tenant** → (3) throw
exception untuk pelanggaran (bukan pulang `false`) → (4) tiada logik HTTP.

## 10.4 Controller

```php
namespace App\Modules\ADE\Http\Controllers;

use App\Http\Controllers\Controller;        // WAJIB (tiada base class dalam modul)

class AdeController extends Controller
{
    public function __construct(private AdeOrchestrator $ade) {}

    private function tenantId(): int
    {
        // BUKAN auth()->user()->tenant_id (atribut itu TIDAK wujud)
        return (int) app(\App\Support\Tenancy\TenantContext::class)->id();
    }

    public function senaraiSkill(Request $request): JsonResponse
    {
        $data = $this->ade->skills()->senarai($this->tenantId(), $request->query('kategori'));
        return response()->json(['data' => $data, 'jumlah' => count($data)]);
    }

    public function janaSkill(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key'   => ['required','string','min:3','max:80'],
            'name'  => ['required','string','max:150'],
            'steps' => ['nullable','array','max:20'],
        ]);

        $skill = $this->ade->skills()->janaSkill($this->tenantId(), $validated);
        return response()->json(['data' => $skill], 201);
    }
}
```

## 10.5 Laluan

```php
use Illuminate\Support\Facades\Route;
use App\Modules\ADE\Http\Controllers\AdeController;

Route::get('/summary', [AdeController::class, 'ringkasan'])->middleware('perm:ade.view');

// A1: Skills
Route::get('/skills',                  [AdeController::class, 'senaraiSkill'])->middleware('perm:ade.view');
Route::post('/skills',                 [AdeController::class, 'janaSkill'])   ->middleware('perm:ade.manage');
Route::post('/skills/install',         [AdeController::class, 'pasangSkill'])->middleware('perm:ade.manage');
Route::post('/skills/match',           [AdeController::class, 'padanSkill']) ->middleware('perm:ade.view');
Route::get('/skills/{id}/invocations', [AdeController::class, 'sejarahSkill'])->middleware('perm:ade.view');

// A2: Sandbox — ⚠️ Laluan STATIK mesti SEBELUM {id}
Route::get('/sandboxes/branches',       [AdeController::class, 'senaraiBranch'])->middleware('perm:ade.view');
Route::get('/sandboxes',                [AdeController::class, 'senaraiSandbox'])->middleware('perm:ade.view');
Route::post('/sandboxes',               [AdeController::class, 'ciptaSandbox'])  ->middleware('perm:ade.execute');
Route::post('/sandboxes/{id}/baseline', [AdeController::class, 'rekodBaseline'])->middleware('perm:ade.execute');
Route::delete('/sandboxes/{id}',        [AdeController::class, 'buangSandbox'])->middleware('perm:ade.manage');

// A4: Review
Route::post('/reviews/{id}/approve', [AdeController::class, 'sahkanReview'])->middleware('perm:ade.approve');
```

**Peraturan laluan:**
- Semua `use` di blok import **atas fail** — PHP tidak benarkan `use` dalam closure.
- Laluan statik (`/summary`, `/templates`, `/inbox/list`, `/branches`) **sebelum** `/{id}`.
- Jangan guna regex pukal (`-replace`) pada `routes/api.php` — pernah merosakkan 765 baris.

## 10.6 Urutan Boot

1. `bootstrap/providers.php` — senarai provider
2. Provider modul didaftar **sebelum** `routes/api.php`
3. → **Laluan provider sentiasa menang** atas `routes/api.php`

**Implikasi:** jika mahu `routes/api.php` menang, **buang** laluan duplikat dari provider.

---

# 11. PETA 58 MODUL

> Laluan = bilangan endpoint `api/v1/<prefix>`. Angka daripada router Laravel
> (`app('router')->getRoutes()`), bukan tekaan.
>
> **Reconcile:** 56 modul domain = **1,429** laluan · teras platform = **89** laluan ·
> **JUMLAH = 1,518**. Jumlah domain melebihi 1,429 kerana beberapa modul mendedahkan
> lebih daripada satu prefix (`Projects` → `pm` + `projects` + `timesheets`;
> `MediaStudio` → `media`; `ContentStudio` → `content` + `knowledge`).

## 11.1 Jualan & Pemasaran (8 modul · 398 laluan)

| Modul | Prefix | Awalan jadual | Laluan | Peranan |
|---|---|---|---|---|
| `Site` | `site` | `site_*` | 128 | Pembina laman (ganti Shopify) — halaman, tema, domain, katalog, AI copy |
| `Marketing` | `marketing` | `marketing_*` | 101 | Kempen, segmen, jujukan, baucar, atribusi 5 model |
| `CRM` | `crm` | `crm_*` | 46 | Pipeline — pelanggan, kenalan, lead, peluang, sebut harga → pesanan |
| `Loyalty` | `loyalty` | `loyalty_*` | 43 | Tahap, member, poin, tebus, ganjaran, ledger |
| `Affiliate` | `affiliate` | `affiliate_*` | 39 | Rujukan, kod, klik, komisen, payout (tenant-scoped) |
| `Blast` | `blast` | `blast_*` | 15 | Enjin omnichannel e-mel/SMS/WhatsApp — 4 pagar penghantaran |
| `Link` | `link` | `link_*` | 15 | Pautan pendek & atribusi |
| `Prospecting` | `prospecting` | `prospect_*` | 11 | Scraper hormat `robots.txt`, consent PDPA, penskoran |

## 11.2 AI & Automasi (15 modul · 227 laluan)

| Modul | Prefix | Awalan jadual | Laluan | Peranan |
|---|---|---|---|---|
| `ADE` | `ade` | `ade_*` | 24 | A1 skills · A2 worktree · A3 coordinator · A4 review |
| `ContentStudio` | `content` | `content_*` | 24 | Jana → jadual → terbit 9 platform, komen inbox, chat, moderasi |
| `BusinessIntel` | `business-intel` | `bi_*` | 20 | AI self-improve — skor 6 dimensi, kajian, idea feed, learning loop |
| `PersonalAssistant` | `personal-ai` | `ai_tasks`, `knowledge_*` | 19 | Memori, tugas, jadual, RAG vector store |
| `AIAgents` | `ai-agents` | `ai_agent*` | 18 | Multi-ejen — definisi, orkestrasi, memori, matriks |
| `BrainHub` | `brainhub` | `brainhub_*` | 18 | Insight, ramalan, cadangan, anomali |
| `FlowAutomation` | `flows` | `workflow_definitions`, `workflow_runs` | 18 | Pencetus → workflow → execute → retry → dead letter → kill switch |
| `Boardroom` | `boardroom` | `boardroom_*` | 14 | Mesyuarat eksekutif AI — majlis, hujah, keputusan berstruktur |
| `McpStudio` | `mcp` | `mcp_*` | 14 | Protokol MCP `2025-06-18` — Claude Desktop, Cursor, Windsurf |
| `AgentStudio` | `agent-studio` | `agent_*` | 13 | 16 templat ejen, persona, ikatan alat, 4 tahap otonomi |
| `MediaStudio` | `media` | `media_*` | 12 | Video, transcript, understanding, persona, consent gate |
| `AIGateway` | `ai-gateway` | `ai_*` | 12 | Wallet, routing, PromptGuard, PII redact, circuit breaker |
| `WorkflowStudio` | `workflows` | `studio_*` | 9 | Visual automation — 18 node, dry-run lalai, template marketplace |
| `ExecutiveAI` | `executive-ai` | `executive_*` | 7 | Otonomi 0–4, command centre, briefing, video blast |
| `SalesCopilot` | `sales-copilot` | (tiada) | 5 | Klasifikasi balasan, brief prospek, barisan susulan |

## 11.3 Operasi & Logistik (14 modul · 420 laluan)

| Modul | Prefix | Awalan jadual | Laluan | Peranan |
|---|---|---|---|---|
| `HRMS` | `hrms` | `hr_*`, `biometric_*` | 88 | Pekerja, jabatan, syif, cuti, kehadiran, biometrik + consent PDPA |
| `Marketplace` | `marketplace` | `marketplace_*` | 47 | Multi-vendor — produk, pesanan, cart, review, komisen |
| `Booking` | `booking` | `booking_*` | 46 | Slot, temujanji, pengingat, penyedia servis |
| `VirtualOffice` | `virtual-office` | `vo_*` | 42 | Ruang kerja maya |
| `ServiceOS` | `service-os` | `service_*` | 35 | Projek 8 status, sebut harga berversi, kontrak, kru, outbox idempoten |
| `Inventory` | `inventory` | `stock_*`, `items`, `warehouses` | 28 | FIFO/FEFO, lejar, pemindahan, pelarasan |
| `SCM` | `scm` | `scm_*` | 26 | Pembekal, pesanan beli, terimaan, KPI |
| `POS` | `pos` | `pos_*` | 24 | Daftar, jualan, item, bayaran, diskaun, pulangan, syif |
| `Logistics` | `logistics` | `logistics_*` | 22 | Penghantaran, kurier, gudang, tracking |
| `Reminder` | `reminders` | `reminder*` | 18 | 14 jenis, 5 saluran, eskalasi, kotak masuk AI |
| `Fleet` | `fleet` | `fleet_*` | 13 | Armada & GPS — lokasi, tingkah laku memandu, 9 amaran |
| `Orders` | `orders` | `unified_order*` | 12 | Pesanan unified — 14 status kanonikal, rizab stok berpusat |
| `Cod` | `cod` | `cod_*` | 11 | COD PKS MY — kelayakan, alamat MY, kutipan, reconciliation |
| `Manufacturing` | `manufacturing` | `boms`, `bom_lines`, `work_orders` | 8 | BOM, kos standard, work order, kit, variance |

## 11.4 Kewangan & People (7 modul · 202 laluan)

| Modul | Prefix | Awalan jadual | Laluan | Peranan |
|---|---|---|---|---|
| `Invoicing` | `invoicing` | `invoices`, `documents` | 46 | Sebut harga → invois → e-Invois LHDN, nota kredit, bil berulang |
| `Projects` | `pm` | `projects`, `tasks`, `timesheets` | 35 | Projek & tugas — dependency, timesheet, milestone |
| `Assets` | `assets` | `assets`, `asset_*` | 33 | Aset & susut nilai, penjagaan, pelupusan |
| `Expense` | `expenses` | `expense_*` | 26 | Perbelanjaan + resit OCR + tuntutan staff + post jurnal |
| `Finance` | `finance` | `chart_of_accounts`, `journal_*` | 26 | Akaun, jurnal, transaksi, saman bank, untung rugi, kunci akaun |
| `Payroll` | `payroll` | `payroll_*` | 21 | EPF 11%, EIS, SOCSO, HRDF, PCB, overtime, slip, eksport |
| `COGS` | `cogs` | `cogs_*` | 15 | Kos barang dijual — snapshot, margin, perbandingan |

## 11.5 Platform, Komunikasi & Analitik (12 modul · 182 laluan)

| Modul | Prefix | Awalan jadual | Laluan | Peranan |
|---|---|---|---|---|
| `Omnichannel` | `omnichannel` | `omnichannel_*` | 26 | Kedai + web + sosial, stok unified, pesanan hibrid |
| `SignDocs` | `sign-docs` | `sign_docs_*` | 25 | Tandatangan elektronik, audit trail, templat |
| `Analytics` | `analytics` | `analytics_*` | 25 | Metrik, dashboard, widget, laporan, export |
| `Seo` | `seo` | `seo_*` | 18 | SEO semua objek — 13 peraturan audit, skor 0–100, JSON-LD, sitemap |
| `Vision` | `vision` | `cctv_*` | 14 | CCTV, RTSP, peristiwa, amaran, retensi |
| `DocumentAI` | `document-ai` | (guna `documents`) | 13 | Ekstrak, klasifikasi, OCR, understanding, embedding |
| `MediaEditor` | `media-editor` | `media_projects`, `media_templates` | 13 | Canva/CapCut-class — 4 projek, 5 resolusi, 7 lapisan, render job |
| `Helpdesk` | `helpdesk` | `tickets`, `kb_articles` | 12 | Tiket, SLA, kategori, artikel, portal awam |
| `IndustryPacks` | `industry-packs` | `industry_profiles` | 10 | Retail, F&B, servis, pembuatan, kesihatan |
| `LiveSupport` | `live-support` | `live_*` | 9 | Customer service live AI + eSIM, 3 pencetus eskalasi |
| `ProofOfValue` | `proof-of-value` | `pov_*` | 9 | Metrik hasil per modul, baseline, sasaran, verifikasi |
| `Community` | `community` | `community_*` | 8 | Forum/academy/showcase/help, jawapan diterima, reputasi |

## 11.6 Teras Platform (bukan modul domain · 89 laluan)

| Prefix | Laluan | Fungsi |
|---|---|---|
| `fundamental` | 11 | Health, metrics, backup |
| `entitlement` | 9 | Langganan, plan, modul aktif |
| `admin` · `auth` · `documents` | 7 setiap satu | Pentadbiran, daftar/log masuk, dokumen |
| `knowledge` | 6 | Pengetahuan (RAG) |
| `webhooks` · `public` · `tasks` | 4 setiap satu | Webhook, awam, tugas |
| `tenant` · `members` · `roles` · `api-keys` | 3 setiap satu | Tetapan tenant, ahli, peranan, kunci API |
| `approvals` · `files` | 2 setiap satu | Kelulusan, fail |
| `audit` · `invitations` · `timesheets` | 1 setiap satu | Jejak audit, jemputan, timesheet |

**Modul #59 `Reseller`** — **spesifikasi sahaja, belum dikodkan** (§20.5).

---

# 12. TUJUH CORAK DOMAIN BERULANG

Bina sekali, guna semula merentas modul.

## 12.1 Status + Peta Aliran

```php
public const STATUS = ['draft','pending','confirmed','paid','processing',
    'packed','shipped','out_for_delivery','delivered','cancelled',
    'refunded','partially_refunded','on_hold','failed'];

public const ALIRAN = [
    'draft'      => ['pending', 'cancelled'],
    'pending'    => ['confirmed', 'cancelled', 'failed'],
    'confirmed'  => ['paid', 'cancelled'],
    'paid'       => ['processing', 'refunded'],
    'processing' => ['packed', 'on_hold'],
];

public function bolehPindah(string $ke): bool
{
    return in_array($ke, self::ALIRAN[$this->status] ?? [], true);
}
```

**Peraturan:** tolak perpindahan tidak sah **sebelum** simpan — jangan biar data rosak.
`ServiceOS` guna 8 status: `draft → quoted → confirmed → in_progress → delivered →
completed` (+`on_hold`/`cancelled`).

## 12.2 Pemetaan Status Platform

```php
public const PETA_STATUS = [
    'shopee' => ['READY_TO_SHIP' => 'confirmed', 'SHIPPED' => 'shipped'],
    'lazada' => ['pending' => 'pending', 'shipped' => 'shipped'],
    'tiktok' => ['AWAITING_SHIPMENT' => 'confirmed'],
    'pos'    => ['completed' => 'delivered'],
];
// Override per tenant melalui jadual `channel_bindings`
```

⚠️ Dalam gabungan data, masukkan status **kanonikal SESUDAH** `$data`.

## 12.3 Polimorfik — `subject_type` + `subject_id`

Digunakan `Reminder`, `Seo`, `AuditLog`, `ProofOfValue`, `SignDocs`:

```php
$table->string('subject_type');                  // FQCN model
$table->unsignedBigInteger('subject_id');
$table->string('subject_label')->nullable();     // untuk paparan

public function subject(): MorphTo { return $this->morphTo(); }
```

**Kenapa:** satu jadual peringatan boleh ikat ke invois, pesanan, kontrak, pelanggan —
tanpa jadual berasingan setiap satu.

## 12.4 Penjanaan Nombor Dokumen

```php
public function nomborSeterusnya(int $tenantId, string $jenis): string
{
    return DB::transaction(function () use ($tenantId, $jenis) {
        $seq = DocumentSequence::withoutGlobalScope('tenant')
            ->lockForUpdate()
            ->firstOrCreate(
                ['tenant_id' => $tenantId, 'jenis' => $jenis, 'tahun' => now()->year],
                ['counter' => 0]
            );
        $seq->increment('counter');
        return sprintf('%s-%s-%04d', strtoupper($jenis), now()->format('ym'), $seq->counter);
    });
}
```

## 12.5 Outbox Peristiwa (lihat §9.3)

## 12.6 Pengiraan Wang

```php
$table->decimal('total_amount', 15, 2);          // SENTIASA decimal
protected $casts = ['total_amount' => 'decimal:2'];

(string) $model->total_amount;                    // '1560'  ← BUKAN '1560.00'
number_format((float) $x, 2, '.', '');            // '1560.00' — guna ini dalam ujian
```

## 12.7 Kesan Pendua (Resit)

```php
$hash = hash('sha256', $merchant . '|' . $jumlah . '|' . $tarikh);   // resit
// offline POS / ServiceOS: idempotency_key = slug(event-aggregate-id-md5(payload))
```

---

# 13. LAPISAN AI — SATU PINTU

## 13.1 Mengapa Satu Pintu

Jika setiap modul memanggil provider terus: kunci API tersebar (bocor ke frontend) ·
tiada had kos (tenant boleh bakar kredit) · tiada pengesanan suntikan prompt ·
tiada audit penggunaan. **Satu pintu = satu tempat untuk semua kawalan.**

## 13.2 `GatewayService::call()` — 8 Langkah

```php
public function call(
    int $tenantId,
    array $messages,
    string $taskType = 'default',
    array $options = []
): array
```

| # | Langkah | Fail |
|---|---|---|
| **0** | **Imbas prompt injection** | `PromptGuard::imbasMesej()` |
| 1 | Feature flag — jika mati → respons simulasi | `config('ai_gateway.features.live_calls_enabled')` |
| 2 | Pilih model ikut `taskType` | `routeModel()` |
| 3 | Circuit breaker | `isCircuitOpen()` |
| 4 | Redaksi PII | `PiiRedactor::redactMessages()` |
| 5 | Had token | `estimateTokens()` |
| 6 | Semak wallet | `AiCreditWallet::canAfford()` |
| 7 | Panggil provider → sanitasi output | `callProvider()`, `PromptGuard::sanitasiOutput()` |
| 8 | Kira kredit + log penggunaan | `AiUsageEvent` |

> ⚠️ **LANGKAH 0 MESTI DI ATAS.** Jika dialihkan selepas feature flag, mod simulasi
> (`AI_GATEWAY_LIVE=false`) **tidak** disekat — pepijat keselamatan sebenar yang
> pernah berlaku. Jangan ulang.

## 13.3 Routing Model

```php
// config/ai_gateway.php
'model_routing' => [
    'default'   => 'gpt-4o-mini',
    'reasoning' => 'gpt-6-astra',      // CEO, CFO, COO, CISO, analyst, researcher
    'creative'  => 'claude-opus',      // CMO, ads specialist
    'quick'     => 'gpt-4o-mini',      // CHRO, support, exec assistant
],
```

## 13.4 Binding Provider

```php
// AIGatewayServiceProvider
$this->app->singleton(GatewayService::class, function ($app) {
    return new GatewayService(
        $app->make(PiiRedactor::class),
        $app->make(PromptGuard::class)      // ← 2 argumen WAJIB
    );
});
```

## 13.5 Mod Simulasi

`AI_GATEWAY_LIVE=false` (lalai) → respons deterministik tanpa panggilan rangkaian.
Ini membolehkan ujian tanpa kos dan tanpa kunci API. Ujian yang perlukan kunci
mesti **langkau sendiri** jika `AI_GATEWAY_KEY` tiada.

## 13.6 PromptGuard

```php
class PromptGuard
{
    public const CORAK = [           // 12 corak input
        '/ignore\s+(all\s+)?(previous|above)\s+(instructions?|prompts?)/i',
        '/you\s+are\s+now\s+(a|an)\s+/i',
        '/system\s*:\s*/i',
        // … 9 lagi
    ];

    public const CORAK_OUTPUT = [    // 4 corak output (bocor kunci)
        '/(sk|pk|api)[-_]?key\s*[:=]\s*\S+/i',
        '/Bearer\s+[A-Za-z0-9\-._~+\/]{20,}/',
        // …
    ];

    public function imbasInput(string $teks): array;      // {ditolak, corak, skor}
    public function imbasMesej(array $messages): array;
    public function imbasOutput(string $teks): array;
    public function bungkusSebagaiData(string $input): string;
    public function sanitasiOutput(string $teks): string;
}
```

**PII redaction** membuang nombor IC (MyKad), telefon, e-mel, nombor akaun bank
**sebelum** ke provider. **API key provider = ADMIN ONLY** — tidak pernah ke
frontend, tidak pernah dalam log.

`logUsage()` tidak boleh terima `$model = null` — guna `Log::warning` untuk blok awal.

## 13.7 Wallet & Kredit

```php
// ai_credit_wallets: tenant_id, balance, monthly_allowance, reset_at
// ai_usage_events:    tenant_id, user_id, model, input_tokens, output_tokens,
//                     credits_used, status, correlation_id, latency_ms

$wallet   = AiCreditWallet::forTenant($tenantId);
$anggaran = $model->calculateCredits($estimatedTokens, $maxOutput);

if (! $wallet->canAfford($anggaran)) {
    $this->logUsage(..., AiUsageEvent::STATUS_BLOCKED, 'Baki kredit tidak mencukupi.');
    return $this->failResponse('Baki kredit AI tidak mencukupi. Sila top-up.');
}
```

**Status penggunaan:** `success` · `failed` · `blocked` · `simulated`
**Scheduler:** `ai-wallet-reset` (bulanan) · `ai-wallet-alerts` (harian 08:00)

## 13.8 Lapan Muka AI

| Muka | Fungsi | Otonomi |
|---|---|---|
| `AIGateway` | Enjin asas — model, wallet, guard | — |
| `AIAgents` | 11 ejen peranan (CEO, CFO, COO, CISO…) | L0–L5 |
| `PersonalAssistant` | Memori + RAG per pengguna | — |
| `Boardroom` | 7 langkah mesyuarat → keputusan berstruktur | Cadangan |
| `ExecutiveAI` | Briefing, command centre, video blast | **0–4** |
| `AgentStudio` | 16 templat ejen | **0–3** |
| `McpStudio` | Alat MCP untuk Claude/Cursor | HITL |
| `ADE` | Ejen bina & semak kod | A1–A4 |

**RAG jujur:** `EmbeddingService` kini **hash-v1** (256 dim, signed hashing trick) —
padanan **perkataan**, bukan semantik. Tukar model melalui `AI_EMBEDDING_MODEL`;
setiap chunk menyimpan `embedding_model` supaya vektor tidak dicampur. Qdrant
belum disambung.

---

# 14. OTONOMI & KELULUSAN

## 14.1 Tahap Otonomi 0–4

| Tahap | Nama | Tingkah laku |
|---|---|---|
| **0** | Tanya | Ejen hanya bertanya, tiada tindakan |
| **1** | Cadang | Ejen cadang, manusia pilih |
| **2** | Draf | Ejen sediakan draf, manusia luluskan |
| **3** | Laksana | Ejen laksana, eskalasi jika perlu |
| **4** | Automasi | Ejen laksana sepenuhnya |

## 14.2 Tindakan SENTIASA Perlu Kelulusan (tidak kira tahap)

```php
public const SENTIASA_LULUS = [
    'send_payment',
    'delete_data',
    'change_autonomy',
    'change_tenant_settings',
    'send_video_blast',
    'send_financial_document',
];
```

## 14.3 Enam Pagar AI Wajib

| Pagar | Lokasi | Peraturan |
|---|---|---|
| **PromptGuard** | `GatewayService::call()` langkah 0 | 12 corak input, 4 output, bungkus sebagai data |
| **PiiRedactor** | langkah 4 | IC, telefon, e-mel tidak pernah ke provider |
| **Wallet** | langkah 6 | Tolak jika kredit habis |
| **Sentral kelulusan** | `AutonomyService` | 6 tindakan `SENTIASA_LULUS` |
| **ADE review** | `AdeReviewService::sahkan()` | Penemuan **kritikal** menyekat kelulusan |
| **Consent blast** | `ConsentService::bolehDihantar()` | consent + bukti + belum luput + saluran ada alamat |

## 14.4 Pagar Spesifik Domain

| Domain | Pagar |
|---|---|
| **Blast** | 4 pagar: consent → penindihan → had kadar → kelulusan kempen. Deliverability auto-pause jika bounce > 5% atau complaint > 0.1%. RFC 8058 `List-Unsubscribe` + one-click |
| **WhatsApp** | **Cloud API sahaja** (❌ `whatsapp-web.js` — risiko ban). Templat mesti diluluskan Meta. Tetingkap 24 jam dihormati |
| **Dokumen kewangan** | ❌ Tiada dokumen dihantar jika bayaran gagal/belum disahkan. PDF WhatsApp **bukan** e-Invois sah LHDN |
| **Expense** | 3 lapis: upload resit → draf sahaja · kelulusan manusia wajib · **pemohon tidak boleh lulus sendiri** (RuntimeException) · AI hanya cadang kategori |
| **ADE** | Kritikal ATAU ujian belum lulus → kelulusan **ditolak**. Sandbox tidak pernah jalankan shell automatik |
| **Vision** | RTSP dienkripsi, tidak dipulangkan API · webhook HMAC + replay protection · `face_match`/`identity` DITOLAK · **SSRF**: allowlist destinasi, bukan proxy terbuka |
| **Biometrik** | 6 pagar consent sebelum `enabled = true` (§19.3) |
| **Prospecting** | `RobotRules` (RFC 9309), `respect_robots` lalai true, UA `AI-Bijan-Prospecting/1.0`, tolak `/login` `/wp-admin` `/cart` |

---

# 15. UI FILAMENT

| Aspek | Nilai |
|---|---|
| Versi | Filament **3.3.53** |
| Panel | `/admin` · guard `web` |
| Resource | **66** (liputan 59 modul) |
| Widget | **6** — 4 KPI, trend 30 hari, donut saluran, 2 jadual |
| Halaman | Tetapan Syarikat `/admin/tetapan` |

## 15.1 Tujuh Peraturan Wajib (setiap satu pernah menyebabkan kegagalan)

**1. `getPages()` WAJIB** — folder `Pages/` + resource class **tidak cukup**.
Tanpa `getPages()`: `Route [filament.admin.resources.X.index] not defined`.

```php
public static function getPages(): array
{
    return [
        'index'  => Pages\ListX::route('/'),
        'create' => Pages\CreateX::route('/create'),
        'view'   => Pages\ViewX::route('/{record}'),
        'edit'   => Pages\EditX::route('/{record}/edit'),
    ];
}
```

**2. JANGAN `use Filament\Resources\Pages;`** — resource dalam `App\Filament\Resources\X\`
mesti resolve `Pages\ListX` **secara relatif**. Import eksplisit **memecahkan** rujukan.

**3. Closure kolum terima `$record`, BUKAN `$s`** — Filament suntik parameter
**mengikut nama**.

```php
IconColumn::make('x')->boolean(fn ($s) => $s->isOk()),        // ❌ 500 sunyi
IconColumn::make('x')->boolean(fn ($record) => $record->isOk()), // ✅
```
Parameter sah: `$record`, `$state`, `$column`, `$row`, `$livewire`.

**4. Kebenaran melalui trait `HasModulePermission`**

```php
use HasModulePermission;

abstract protected static function permissionNamespace(): string;   // MESTI diimplement
```

> ⚠️ **PHP 8.2: trait tidak boleh declare typed static property** yang resource
> override → guna `abstract protected static function permissionNamespace()`,
> bukan `protected static string $permission`.

**5. `$slug` wajib** — tanpanya URL jadi `/admin/expenses/expenses` (berganda).

**6. Widget: `protected static bool $isLazy = false;`** — `ChartWidget`/`TableWidget`
lalai lazy; jika AJAX gagal kekal `<div class="fi-loading-section">` kosong.

**7. JANGAN gabung `discoverWidgets()` dengan `widgets([])`** — `filament:cache-components`
menimpa senarai eksplisit dan sebahagian widget hilang **senyap**. Guna `widgets([...])` sahaja.

**Tambahan:** `getDescription()` JANGAN panggil `getData()` (cache basi → "Jumlah RM 0.00").
Jangan takrif `canCreate()` yang `return true;` — ia **memintas** trait.

## 15.2 Konteks Tenant untuk Panel

```php
// bootstrap/app.php
$middleware->web(append: [ResolveFilamentTenant::class]);
```

**Kenapa dalam `web`, bukan `authMiddleware` panel:** Livewire memanggil
`POST /livewire/update` **di luar** laluan panel. Tanpanya widget berjalan tanpa
konteks → KPI sentiasa "Tiada Syarikat Aktif" dan semua query pulang 0 rekod.

`ResolveFilamentTenant` mesti tetapkan konteks dari **keahlian pengguna** (bukan
input) dan `clear()` dalam `finally`.

## 15.3 CSP

```php
// config/security.php
'use_nonce' => false,     // lalai
```

> ⚠️ **`nonce` dan `'unsafe-inline'` SALING EKSKLUSIF.** Jika nonce hadir, pelayar
> **abaikan** `'unsafe-inline'` → semua skrip inline Livewire disekat → butang login
> mati. Bila `use_nonce=false`, **jangan keluarkan** nonce. Alpine.js perlukan
> `'unsafe-eval'`. Fon Filament (Bunny) perlukan `style-src` + `font-src`
> `https://fonts.bunny.net`.

## 15.4 Kebenaran Dua Lapis

| Lapisan | Mekanisme | Fail |
|---|---|---|
| **UI** | `HasModulePermission` — `canViewAny/canView/canCreate/canEdit/canDelete` + `shouldRegisterNavigation` | `app/Filament/Concerns/` |
| **API** | `perm:{ns}.{aksi}` + `module:{key}` | `routes/` |

`HasModulePermission` semak **RBAC + entitlemen modul** → menu tersembunyi jika
plan tiada modul (tiada 403 mengejut). `bolehUrus()` fallback ke `.view` jika
`.manage` tiada dalam DB (contoh `brainhub` hanya ada `brainhub.view`).

> ⚠️ **Menu tersembunyi BUKAN kawalan keselamatan.** API tetap dikawal `perm:` + `module:`.
> Kedua-dua lapisan mesti selari.

**8 resource contoh:** `perbelanjaan` (finance) · `pesanan` (omnichannel) ·
`pelanggan` (crm) · `invois` (invoicing) · `produk` (inventory) ·
`projek-perkhidmatan` (projects) · `kandungan` (marketing) · `idea-bisnes` (brainhub)

## 15.5 POS UI (3 resource)

| Resource | Laluan | Mod |
|---|---|---|
| `PosSaleResource` | `/admin/pos-jualan` | Baca sahaja (jualan melalui `SaleService`) |
| `PosRegisterResource` | `/admin/pos-kaunter` | CRUD penuh (konfigurasi) |
| `CashShiftResource` | `/admin/pos-syif` | Baca sahaja (syif melalui `CashShiftService`) |

⚠️ `pos_registers.code` + `created_by` NOT NULL → jana dalam `mutateFormDataBeforeCreate()`.

---

# 16. UJIAN

## 16.1 Trait Asas

```php
use RefreshDatabase, AksesTenant;

protected $seed = true;

protected function setUp(): void
{
    parent::setUp();
    $this->setUpAkses();
    [$this->u, $this->t] = $this->daftar('pemilik@x.my', 'Syarat X');
    $this->dalamTenant($this->t);
}
```

## 16.2 Kategori Ujian Wajib Setiap Modul

| Kategori | Minimum |
|---|---|
| Pengasingan tenant | 2 (baca + tulis) |
| Cipta gembira | 1 |
| Pengesahan input tidak sah | 2 |
| Had/kuota | 1 |
| Kebenaran ditolak | 1 |
| API endpoint | 2 |

## 16.3 Perangkap Ujian

| Perangkap | Pembetulan |
|---|---|
| `/** @test */` | Guna `#[Test]` (PHPUnit 11) |
| `#[\PHPUnit\...\Test]` | Syntax error PHP 8.2 — mesti `use` |
| `dalamTenant()` tanpa membership | `app(TenantContext::class)->set($t, $membership)` untuk ujian kebenaran |
| Rekod selepas HTTP request | Konteks dikosongkan — cipta **dahulu** |
| `withoutGlobalScopes()` | Wajib untuk ujian pengasingan |
| Decimal sebagai string | `number_format((float)$x, 2, '.', '')` |
| Widget lazy | `Livewire::test(...)->assertSee(...)`, bukan HTTP |
| `artisan test --filter` tidak dihormati | `php vendor/phpunit/phpunit/phpunit -c phpunit.xml --filter <nama>` |

## 16.4 Ujian API (Sanctum SPA + CSRF)

```
1. GET  /sanctum/csrf-cookie        (simpan cookie sesi)
2. POST /api/v1/auth/login          header X-XSRF-TOKEN=<cookie di-decode>, Referer=http://127.0.0.1:<port>
3. Endpoint tenant                  tambah header X-Tenant=<tenant_ulid>
   → ambil semula XSRF-TOKEN selepas login (token berputar)
PowerShell: guna -SkipHttpErrorCheck untuk baca body ralat.
```

## 16.5 Ujian Regresi Panel (contoh yang menangkap pepijat sebenar)

```
test_csp_tidak_campur_nonce_dengan_unsafe_inline
test_csp_membenarkan_fon_bunny
test_laluan_livewire_mempunyai_middleware_tenant
test_semua_resource_guna_namespace_permission_yang_wujud
test_setiap_resource_guna_namespace_yang_wujud_dalam_db
test_setiap_model_resource_mempunyai_jadual
test_dashboard_render_tanpa_ralat
test_kpi_tidak_tunjuk_tiada_syarikat_aktif
```

> **Pelajaran:** ujian unit lulus ≠ UI berfungsi. Sahkan dalam pelayar sebenar
> (buka halaman, semak status 200 + jadual render) untuk CSP, Livewire dan RPC AJAX.

---

# 17. PENJADUAL & QUEUE

```php
// routes/console.php
Schedule::call(function () {
    foreach (Tenant::all() as $tenant) {
        app(TenantContext::class)->set($tenant);          // WAJIB setiap iterasi
        try {
            app(SomeService::class)->jalankan($tenant->id);
        } finally {
            app(TenantContext::class)->clear();
        }
    }
})->dailyAt('04:00');
```

**Jadual lazim:**

| Jadual | Selang |
|---|---|
| `blast-dispatch` | 1 minit |
| `content-publish` | 5 minit |
| `reminder-dispatch` | 10 minit |
| `prospecting-rescore` | 10 minit |
| `orders-reservation-cleanup` | setiap jam |
| `seo-audit-daily` | 03:15 |
| `media-retention-purge` | 03:30 |
| `business-intel-daily` | 04:00 |
| `executive-briefing-daily` | 07:30 |
| `ai-wallet-alerts` | harian 08:00 |
| `helpdesk-sla` | 5 minit |

> ⚠️ **Queue worker mesti tetapkan konteks tenant dari DB, bukan dari payload job.**
> Jangan percaya `tenant_id` dalam payload — ia boleh dipalsukan. Peraturan sama
> untuk B-Vision worker (Python/FFmpeg berasingan): ambil konteks dari DB.

---

# 18. KESELAMATAN & PDPA

## 18.1 Senarai Semak Keselamatan

- [ ] Semua model perniagaan guna `BelongsToTenant`
- [ ] Semua laluan ada `perm:` **dan** `module:` gate
- [ ] Tiada `Auth::user()->tenant_id` — guna `TenantContext::id()`
- [ ] PromptGuard di langkah 0 `GatewayService::call()`
- [ ] API key provider tidak pernah ke frontend/log
- [ ] Kredensial kamera/RTSP dienkripsi (`Crypt::encryptString`), tidak dipulangkan API
- [ ] Webhook: HMAC + replay protection + idempotency
- [ ] Path traversal disekat (worktree ADE, muat naik fail)
- [ ] SSRF: allowlist destinasi, bukan proxy terbuka
- [ ] `composer audit` = 0 advisori
- [ ] Menu tersembunyi **bukan** kawalan keselamatan

## 18.2 Health & Observability

| Endpoint | Fungsi |
|---|---|
| `GET /health` | Liveness (tiada I/O) |
| `GET /ready` | DB/cache/storage/queue/migrasi → 503 jika gagal |
| `GET /metrics` | Metrik (throttle 30/min) |

Tiada Sentry/Pulse dipasang — health endpoint boleh dipantau UptimeRobot.

## 18.3 Pagar PDPA Biometrik — 6 syarat sebelum `enabled = true`

```php
alternative_method      // rfid|pin|qr|manual — WAJIB disediakan
dpo_name, dpo_email
privacy_notice
consent_version
retention_days          // 1–365
legal_review_date       // ≤ 12 bulan
```

Tarik consent → embedding **dipadam**, rekod kehadiran **kekal**.
`BiometricGateService` ialah **satu-satunya** laluan padanan wajah.

## 18.4 Prinsip Keselamatan Lain

- **Credentials:** `.env` tidak masuk repo; kunci AI serveras.click **ADMIN ONLY**.
- **Webhook:** HMAC wajib untuk `vision/ingest/{cameraId}` (di luar auth).
- **Facial recognition / identity:** DITOLAK melainkan melalui pagar 56C.6A.
- **Retensi:** polisi + notis kamera + retensi (PDPA) untuk Vision.

---

# 19. PEMATUHAN MALAYSIA

## 19.1 Kadar Berstatut

| Domain | Kadar/Peraturan | Sumber |
|---|---|---|
| EPF pekerja | **11%** | KWSP |
| EPF majikan | **13%** (≤RM5k) / **12%** (>RM5k) | KWSP |
| SOCSO | Berperingkat (maks ~RM1,200/thn) | PERKESO |
| EIS | **0.2%** (maks RM100/thn) | PERKESO |
| HRDF | **1%** (**tiada had**) | HRDF |
| PCB | Jadual bulanan | LHDN |
| Mileage | **RM0.60/km** | LHDN |
| e-Invois | Wajib berperingkat | LHDN |
| PDPA | Consent + DPO + retensi | Akta 709 |

> ⚠️ **Kadar berubah setiap tahun.** Nilai ini ialah **lalai sistem** — tenant mesti
> sahkan dengan penasihat cukai. Bandingkan dengan templat rasmi KWSP/PERKESO/LHDN
> terkini sebelum penyerahan.

## 19.2 Eksport Berstatut

- Format CSV + BOM UTF-8, baris JUMLAH, checksum SHA-256 (12 aksara) dalam nama fail
  + header `X-Checksum-Sha256`
- Hanya status `approved`/`finalized` boleh eksport
- Jenis: `epf` · `socso` · `eis` · `pcb` · `bank` · `semua`
- `PayrollRun` **tiada** medan `period` — guna `period_start` (format `Ym`)
- Pertimbangkan topeng IC (PDPA)

## 19.3 Ambang Lalai Expense (`config/expense.php`)

| Tetapan | Lalai |
|---|---|
| Kelulusan diperlukan | RM200 |
| Resit wajib | RM50 |
| Mileage | RM0.60/km |
| Per diem | RM50/hari |

⚠️ Semua nilai ini = **default sistem**; mesti disahkan tenant/penasihat cukai.

## 19.4 e-Invois LHDN — Status Jujur

Invois, nota kredit/debit dan validasi sudah dikodkan, tetapi **PDF melalui WhatsApp
bukan e-Invois sah LHDN** — hanya salinan dokumen. e-Invois kini **jadual stakes**
(StoreHub dan Bukku sudah ada), bukan pembeza.

---

# 20. CORAK JUALAN & HARGA

## 20.1 Positioning (jangan rosakkan)

✅ **"POS, CRM, HR, kewangan, AI dalam satu — dengan migrasi percuma"**

❌ **Jangan sebut:** "ganti SAP" · "59 modul siap" · "voice interface" ·
"collective intelligence" · "proprietary model" · "AI urus perniagaan anda"

| Dakwaan salah | Kenapa bahaya | Ganti dengan |
|---|---|---|
| "Ganti SAP" | Tidak benar; hilang kredibiliti | "Untuk PKS, bukan perusahaan" |
| "AI urus perniagaan anda" | Tidak boleh dibuktikan | "AI jimatkan X jam, tambah Y prospek" |
| "59 modul siap" | 66 resource = CRUD berfungsi, borang generik | "POS, CRM, HR, kewangan dalam satu" |
| "Voice interface" | Belum dikodkan | Jangan sebut sehingga siap |
| "Collective intelligence" | Belum dikodkan | Jangan sebut sehingga siap |

> **Peraturan emas:** tiada ciri AI boleh diiklankan tanpa **satu metrik boleh diukur**.

## 20.2 Harga Pesaing (disahkan dari laman rasmi 24 Okt 2026 — semak semula sebelum sebut)

| Produk | Harga | Nota |
|---|---|---|
| **Bukku** | RM0 Launch / RM35 Seed / RM65 Grow / RM95 Prime / RM135 Elite + 8% SST | Perakaunan sahaja; pengguna tanpa had; **e-Invois termasuk pada RM0** |
| **StoreHub** | RM122 Starter / RM235 Advanced / RM471 Pro **sebulan per perniagaan** | Termasuk 1 kaunter + 1 kedai; 20,000+ pelanggan; e-Invois ada |
| **Zoho One** | **RM148/pekerja/bulan** (semua pekerja) atau RM360/pengguna | 15 pekerja = RM2,220/bulan |
| **Odoo** | USD13.50 Standard / USD25.50 Custom per pengguna/bulan | Custom ada Agentic AI; **tidak termasuk** kos pelaksanaan RM15k–80k |
| **SiteGiant** | Harga tersembunyi (hubungi) | eCommerce multichannel |
| **AI BIJAN** | **RM288 flat, pengguna tanpa had** | 15 pekerja masih RM288 |

## 20.3 Perbandingan Kos Sebenar — PKS 15 Pekerja

| Sistem | Kos bulanan | Kos tahun 1 |
|---|---|---|
| **AI BIJAN** | **RM288** | RM3,456 |
| Bukku Elite | RM146 | RM1,749 |
| StoreHub Advanced | RM235 | RM2,820 |
| Odoo Custom | ~RM1,695 | ~RM20,340 + pelaksanaan |
| Zoho One (all-employee) | **RM2,220** | RM26,640 |
| MS Dynamics | RM8,100 | RM97,200 |
| SAP S/4HANA | RM45,000+ | RM540,000+ |

**Tafsiran jujur:** AI BIJAN **bukan** termurah (Bukku lebih murah kerana sempit).
AI BIJAN bersaing terus dengan StoreHub (RM235 vs RM288). Pembeza mesti
**integrasi + pematuhan HR + AI**, bukan harga.

## 20.4 Ancaman Gabungan

| Gabungan | Kos | Apa pelanggan dapat |
|---|---|---|
| Bukku Elite + StoreHub Starter | RM146 + RM122 = **RM268** | Perakaunan matang + POS matang + e-Invois |
| AI BIJAN | RM288 | Lebih banyak modul, UI masih generik |

**Cara menang:** dalam AI BIJAN, satu transaksi POS terus jadi invois dengan
e-Invois + rekod inventori + catatan jurnal — **tanpa salinan manual**. Dalam
gabungan, pemilik mesti eksport-import setiap hari.

## 20.5 Reseller — Komisen Berulang (SPESIFIKASI, belum dikodkan)

| Perkara | Nilai |
|---|---|
| Harga | **RM388/bulan** tetap |
| Komisen | **RM100/bulan** (25.8%) |
| Tempoh | **selagi pelanggan langgan — tiada had** |
| Jadi hak | **selepas 30 hari** (lindungi refund) |
| Jadual | `resellers`, `reseller_referrals`, `reseller_commissions`, `reseller_payouts`, `reseller_clicks` + 2 kolum pada `tenants` |
| Laluan | **14** (`/api/v1/reseller`) + awam `GET /r/{code}` |
| Ujian dirancang | **30** (komisen 10 · kematangan 6 · churn 5 · payout 5 · atribusi 4 · keselamatan 5) |
| Fasa | **14 hari** (R1–R6) |

**12 peraturan kritikal:** satu tenant satu reseller (UNIQUE) · satu komisen per
bulan (UNIQUE) · komisen atas **jumlah sebenar dibayar** · komisen ≤ 50% bayaran ·
30 hari sebelum jadi hak · churn → `reversed` · `approved` **tidak** dibatalkan ·
snapshot bank semasa bayar · tiada self-referral · cookie 90 hari · IP di-hash ·
tiada komisen jika menunggak.

**Model hasil (dibetulkan 27 Okt 2026):**

```
Langganan RM388/bulan           WAJIB
  → RM100 reseller (tetap)
  → RM288 platform
Pakej token AI RM55–1,100       WAJIB dipilih semasa langganan
  → 100% platform (reseller tidak dapat komisen dari token)
```

**⚠️ Kos token SUDAH DIKIRA** — margin 90.9% seragam pada semua 5 pakej
(kos pembekal RM5/RM8/RM10/RM50/RM100). Yang belum disahkan:

1. **Yuran gateway CHIP** (~1–3% daripada transaksi) — semak kontrak
2. **Dashboard kos AI per pelanggan** (`ai_usage_events`) — untuk ukur penggunaan sebenar

**Amaran harga token:** jika anda jual pakej token di bawah kos pembekal, anda
rugi pada setiap pelanggan yang menggunakan token penuh. Harga ini dilindungi
automatik oleh `BillingService::pakejRugi()` (ujian gagal jika pakej rugi).

**Risiko yang masih nyata:** harga pembekal naik · pelanggan guna melebihi
jangkaan · churn selepas komisen dibayar · liabiliti komisen masa depan.
Mitigasi: `RESELLER_COMMISSION_MONTHS=24` (jadual sudah sokong, tiada migrasi).

**Jangan gabung dengan `Affiliate`:** `Affiliate` = untuk **pelanggan tenant** jual
produk mereka (tenant-scoped). `Reseller` = untuk orang jual **SaaS AI BIJAN**
(platform-level). Jadual berbeza: `reseller_*` vs `affiliate_*`.

## 20.6 Skor Kompetitif (jujur, dinilai pada keupayaan hari ini)

| Dimensi | AI BIJAN | Zoho One | Odoo | StoreHub | Bukku |
|---|---|---|---|---|---|
| Kedalaman fungsi | 5 | 9 | 9 | 6 | 5 |
| Kematangan UI | **7** | 9 | 7 | 8 | 8 |
| Pematuhan Malaysia | **8** | 3 | 5 | 6 | 8 |
| Keupayaan AI | **7** | 3 | 4 | 2 | 1 |
| Keselamatan & isolasi | **9** | 7 | 6 | 5 | 6 |
| Kos efektif (PKS 15) | **9** | 3 | 5 | 8 | 10 |
| Kemudahan onboarding | 4 | 8 | 2 | 9 | 8 |

> **Sebelum T-71 (Filament):** UI 2/10, purata 6.3 — seri StoreHub, di belakang Bukku.
> **Selepas T-71:** UI 7/10. Jika UI mencapai 9 (UI domain khusus), purata mendahului semua.

---

# 21. DUA BELAS FASA PEMBINAAN

> **Jangan lompat fasa.** Setiap fasa bergantung pada yang sebelumnya.

| Fasa | Nama | Anggaran | Siap bila |
|---|---|---|---|
| **1** | **Asas** | 3–5 hari | `php artisan migrate` berjaya, `/admin` boleh dibuka |
| **2** | **Tenancy** ⭐ | 5–7 hari | Ujian pengasingan tenant lulus (2 tenant, 0 kebocoran) |
| **3** | **RBAC & Entitlemen** | 3–4 hari | Tenant tanpa langganan ditolak 403 |
| **4** | **Domain Teras** | 10–15 hari | Inventory · Manufacturing · Projects · Assets · Helpdesk · Invoicing · Finance · Payroll — setiap satu ada ujian pengasingan + cipta gembira |
| **5** | **AI Gateway** ⭐ | 5–7 hari | Prompt injection disekat walaupun `AI_GATEWAY_LIVE=false` |
| **6** | **Modul AI** | 7–10 hari | AIAgents · PersonalAssistant · Boardroom · FlowAutomation |
| **7** | **Jualan & Pemasaran** | 10–12 hari | CRM · Marketing · Loyalty · Affiliate · Link · Site |
| **8** | **Operasi Lanjutan** | 12–15 hari | POS · Booking · Marketplace · Omnichannel · Logistics · Orders · Reminder |
| **9** | **Studio AI** | 8–10 hari | AgentStudio · McpStudio · WorkflowStudio · ContentStudio · MediaStudio · MediaEditor |
| **10** | **Kecerdasan** | 6–8 hari | BusinessIntel · Seo · Analytics · BrainHub · ProofOfValue · ExecutiveAI · ADE |
| **11** | **UI Filament** | 10–15 hari | Setiap halaman 200, jadual render, tiada `BindingResolutionException` |
| **12** | **Pengukuhan** | 5–7 hari | `composer audit` = 0 · semua ujian lulus · `sync-perm.php` pada DB produksi · health endpoint · ujian staging |

**Jumlah: 83–118 hari kerja (4–6 bulan) untuk satu jurutera berpengalaman.**

### Fasa 1 — perincian
1. `composer create-project laravel/laravel`
2. Pasang Sanctum + Filament 3.3.53
3. Migration `users` + sambungan
4. `.env` (SQLite)

### Fasa 2 — perincian (paling kritikal)
1. Jadual `plans`, `tenants`, `tenant_users`, `permissions`, `roles`, pivot
2. `TenantContext` (§6.1)
3. Trait `BelongsToTenant` (§6.2)
4. `ResolveTenant` middleware (§8.2)
5. `TenantProvisioner`
6. `skrip/sync-perm.php`

### Fasa 5 — perincian
1. `ai_model_catalog`, `ai_credit_wallets`, `ai_usage_events`
2. `PromptGuard` (12 + 4 corak)
3. `PiiRedactor`
4. `GatewayService::call()` — 8 langkah, guard di **langkah 0**
5. Mod simulasi

### Fasa 11 — perincian
66 resource + 6 widget + halaman Tetapan. Resource dijana dengan
`php skrip/jana-resource.php`, kemudian **WAJIB** `php skrip/pulih-pos.php`.

---

# 22. SKRIP & ALAT (`skrip/`)

| Skrip | Fungsi | Bila jalankan |
|---|---|---|
| `sync-perm.php` | `config/aibijan.php` → jadual `permissions` (**212**) + beri semua kepada `tenant_owner` | Selepas migrate, selepas tambah kebenaran, **pada DB produksi sebelum go-live** |
| `cipta-tenant-ujian.php` | Pengguna + tenant ujian + plan enterprise (59 modul) | Dev sahaja, selepas `migrate:fresh` |
| `semak-dokumen.php` | Banding 9 metrik dokumen vs kod; exit 1 jika bercanggah | Selepas setiap perubahan struktur / CI |
| `jana-resource.php` | Jana resource Filament (definisi array di dalamnya) | Fasa 11 |
| `pulih-pos.php` | Pulihkan 3 resource POS manual | **WAJIB selepas `jana-resource.php`** |
| `naik-plan.php` | Naik taraf tenant (pengesahan visual) | Ujian manual |
| `semak-kebenaran.php`, `semak-perm-db.php`, `senarai-model.php` | Diagnostik kebenaran/model | Bila menu/halaman 403 |
| `audit-ui.php` | Audit halaman Filament | Selepas tambah resource |
| `semak_mysql.php` | Audit migration untuk MySQL | Sebelum tukar DB |
| `tukar_db.ps1` | Tukar `.env` SQLite ↔ MySQL + sandaran + ujian PDO | Bila tukar DB |
| `tetapan_ai.ps1` | Status/aktif `AI_GATEWAY_LIVE` (tidak terima kunci sebagai parameter) | Operasi AI |
| `borang-terperinci.php`, `betul-atribut.php`, `baiki_*.php` | Pembaikan sekali sahaja | Rujukan sejarah |

> ⚠️ **`rmdir /s app\Filament\Resources` MEMADAM resource manual.**
> Sentiasa jalankan `php skrip/pulih-pos.php` selepas `jana-resource.php`.
>
> ⚠️ **`migrate:fresh` MEMADAM data dev.** Selepas itu urutan §2.5 mesti dijalankan semula.

**Sandaran sebelum naik taraf besar:** `C:\ai-bijan\AI-Bijan\backups\pre-laravel12-2026\`

---

# 23. SENARAI SEMAK PENERIMAAN

## 23.1 Struktur
- [ ] 59 modul didaftar dalam `bootstrap/providers.php`
- [ ] Setiap modul ada prefix sendiri — tiada `api/v1/{id}` wildcard
- [ ] 346 jadual dicipta
- [ ] Semua model perniagaan guna `BelongsToTenant`
- [ ] Model dengan nama jadual ≠ plural ada `$table`
- [ ] `$fillable` ada `tenant_id` bila `create()` dihantar eksplisit

## 23.2 Keselamatan
- [ ] Ujian pengasingan tenant lulus untuk setiap modul
- [ ] `perm:` + `module:` pada setiap laluan domain
- [ ] PromptGuard disekat dalam mod simulasi **juga**
- [ ] Tiada `auth()->user()->tenant_id` dalam kod
- [ ] `composer audit` = 0
- [ ] CSP: nonce dan `unsafe-inline` tidak bercampur
- [ ] Path traversal + SSRF disekat

## 23.3 AI
- [ ] Semua panggilan AI melalui `GatewayService::call()`
- [ ] Wallet ditolak bila kredit habis
- [ ] Tindakan berisiko sentiasa perlu kelulusan
- [ ] Tiada kunci API dalam respons atau log

## 23.4 UI
- [ ] Setiap resource ada `getPages()` + 4 kelas `Pages/`
- [ ] Closure kolum guna `$record`
- [ ] Widget `$isLazy = false`
- [ ] `ResolveFilamentTenant` dalam kumpulan `web`
- [ ] Setiap halaman 200 **disahkan dalam pelayar sebenar**
- [ ] Setiap resource guna namespace yang **wujud dalam jadual `permissions`**
- [ ] Setiap model resource **mempunyai jadual**

## 23.5 Operasi
- [ ] Ujian penuh lulus, 0 gagal
- [ ] Queue worker tetapkan konteks dari DB
- [ ] Scheduler `clear()` dalam `finally`
- [ ] `/health` `/ready` `/metrics` berfungsi
- [ ] `sync-perm.php` dijalankan pada DB produksi
- [ ] `php skrip/semak-dokumen.php` → semua ✅

---

# 24. STATUS JUJUR

## 24.1 ✅ Siap & diuji

- 59 modul backend · 346 jadual · 1,518 endpoint · 963 ujian
- 66 resource Filament CRUD + 6 widget + halaman Tetapan
- Pengasingan tenant 4 lapisan pada 292 model
- AI Gateway + PromptGuard + PII redact + wallet + mod simulasi
- Pematuhan gaji MY + e-Invois LHDN + biometrik PDPA
- Transactional outbox (`ServiceOS`, `FlowAutomation`, `ADE`)
- **ADE A1–A4** — skills, worktree, coordinator, review
- 0 advisori keselamatan · Laravel 12.70.1

## 24.2 ⏸️ Belum siap (jujur)

| Perkara | Keadaan sebenar |
|---|---|
| **B-Receipt & B-Vision** (BAB 90-91) | **Spesifikasi sahaja — tiada kod, tiada jadual** |
| **Reseller** (BAB 93) | **Spesifikasi sahaja** — 5 jadual dirancang, belum dikodkan |
| **ADE pelaksanaan kod** | Callback sedia; sambungan enjin P2 — kini dry-run |
| **RAG** | Hash-v1 (padanan perkataan, **bukan** semantik); Qdrant belum |
| **B-Migration Wizard** (T-72) | Tiada — halangan skor migrasi 2/10 |
| **Connector Pack 20 sistem** (T-75) | Tiada |
| **SOC 2 / ISO 27001** | Tiada — USD30k–80k, 6–12 bulan |
| **Integrasi bayaran (FPX)** | Tiada — **tanpa ini, tiada transaksi sebenar** |
| **Telefoni LiveSupport** | Perlu kredensial Twilio/eSIM |
| **Renderer media** | Worker FFmpeg/Sharp luar belum |
| **UI domain-specific** | 66 resource = CRUD generik, bukan UI direka khusus |
| **Mod mudah alih / PWA** | Tiada |
| **WhatsApp UI** | Belum ada antara muka WhatsApp sebagai UI |

## 24.3 Skor 7 Dimensi

| Dimensi | Skor | Nota |
|---|---|---|
| Backend | **8/10** | Berfungsi, diuji, terasing |
| UI | **7/10** | Naik dari 2/10 (T-71); masih generik |
| AI | **7/10** | Guard kuat; pelaksanaan autonomi masih terbatas |
| Pematuhan MY | **8/10** | Kuat untuk MY; tiada per-negara lain |
| Migrasi masuk | **2/10** | Jurang terbesar |
| Ekosistem plugin | **1/10** | Jurang besar |
| Pematuhan enterprise | **3/10** | Tiada SOC 2 |

**Purata ≈ 5.1/10.** Pesaing: Zoho One 6.5, Dify 6.7, Retool 6.3, Odoo 6.0.

## 24.4 Tiga Syarat Jadi Platform Universal

1. **B-Migration Wizard** — import + profil + simulator + export
2. **UI 7 → 9** — UI domain khusus untuk POS, CRM, ServiceOS
3. **SOC 2 Type II + ISO 27001**

> **Nasihat:** jangan tambah modul. Ketiga-tiga di atas ialah perbezaan antara
> "produk bagus" dan "platform yang semua orang guna".

## 24.5 Apa Yang Sudah Sedia Tetapi Perlu Tindakan Anda

| Perkara | Tindakan |
|---|---|
| SMTP / Mailgun | Kredensial untuk blast e-mel sebenar |
| Twilio | Lesen WASTI MCMC untuk SMS/telefoni |
| Meta WhatsApp | Templat mesti diluluskan Meta |
| HeyGen / Synthesia | Kunci untuk video avatar |
| AI Gateway live | `AI_GATEWAY_LIVE=true` + kunci serveras.click (admin only) |
| MySQL produksi | `php skrip/tukar_db.ps1` + `sync-perm.php` |
| Staging | Ujian manual 66 halaman |

---

# 25. PERANGKAP MENGIKUT KATEGORI

Setiap baris ialah pepijat **sebenar** yang pernah berlaku dan dibaiki.

## 25.1 Migrasi

| Perangkap | Pembetulan |
|---|---|
| `UNIQUE(tenant_id, key)` dengan tenant NULL | SQLite/MySQL anggap NULL berbeza → **semak di PHP** (`kunciWujud()`) |
| Kolum `error_message` tiada | Tambah **SEBELUM** fillable model |
| `timestamps()` hilang pada jadual yang model guna `HasFactory` | Post jurnal gagal sepenuhnya — tambah `timestamps()` |
| Awalan jadual bertindih | Guna awalan modul unik (`studio_*`, `service_*`, `pos_*`, `ade_*`) |
| `tax_rate` bukan kolum | `unset($data['tax_rate'])` **sebelum** `create()` |
| `type`/`NOT NULL` SQLite | Nilai lalai melalui `$attributes` model |

## 25.2 Model

| Perangkap | Pembetulan |
|---|---|
| Cast `array` → `json_encode()` | Hantar array terus (double-encode) |
| `updateOrCreate` dengan kolum `date` | `whereDate()->first()` + create/update |
| `array_merge($defaults, $data)` dengan kunci bukan-kolum | `unset()` dahulu |
| Relation di-cache | Kira dari DB |
| `orderByDesc` atas relation yang ada `orderBy` | Guna `where()` bukan order |
| Nama jadual ≠ plural kelas | `protected $table = '…'` |
| `auth()->user()->tenant_id` | Atribut **tidak wujud** → `TenantContext::id()` |
| `TenantUser` tiada `$fillable` untuk `role`/`tenant_id` | `TenantProvisioner::create()` |
| Sifat vs method relasi | `$m->tool` (model) vs `$m->tool()` (relation) |

## 25.3 Laluan

| Perangkap | Pembetulan |
|---|---|
| Laluan statik selepas `{id}` | Statik **dahulu** |
| Tiada prefix | `->prefix('api/v1/x')` |
| Prefix berganda (`projects/projects`) | Guna `'/'` dalam fail laluan dalaman |
| Dua controller, satu jadual | Pilih satu, atau prefix berbeza (`pm`) |
| `use` dalam closure | Semua di blok import **atas fail** |
| Regex `-replace` pada `routes/api.php` | Guna alat edit — pernah merosakkan 765 baris |
| `Set-Content -NoNewline` pada `routes/api.php` | Semua newline hilang — **jangan guna** |

## 25.4 Filament

| Perangkap | Pembetulan |
|---|---|
| Tiada `getPages()` | Tambah |
| `use Filament\Resources\Pages` | Buang — resolve relatif |
| `fn ($s)` | `fn ($record)` — 500 sunyi |
| Widget lazy | `$isLazy = false` |
| `discoverWidgets()` + `widgets()` | `widgets()` sahaja |
| CSP nonce + `unsafe-inline` | `use_nonce = false`, jangan keluarkan nonce |
| `canCreate()` return true | Jangan takrif — memintas trait |
| `ResolveFilamentTenant` hanya di `authMiddleware` | Daftar dalam kumpulan `web` |
| `withoutGlobalScopes([SoftDeletingScope::class])` pada model tanpa `SoftDeletes` | Ralat `withoutTrashed()` undefined |
| Namespace permission tiada dalam config | Tambah + `sync-perm.php` |
| Resource model tanpa jadual | 500 "no such table" |
| `$slug` tiada | URL berganda |

## 25.5 AI

| Perangkap | Pembetulan |
|---|---|
| Guard selepas feature flag | Guard di **langkah 0** |
| `logUsage()` dengan `$model = null` | `Log::warning` untuk blok awal |
| Binding tanpa PromptGuard | 2 argumen wajib |
| `GatewayService::call()` named args | Guna posisional: `(tenantId, messages, taskType, options)` |
| `Blueprint::array()` | Tiada — guna `json()` |

## 25.6 Ujian

| Perangkap | Pembetulan |
|---|---|
| `/** @test */` | `#[Test]` |
| Rekod dicipta selepas `$this->sebagai()` | Cipta **sebelum** |
| `dalamTenant($t)` tanpa membership | `set($t, $membership)` |
| Decimal `'1560'` vs `'1560.00'` | `number_format((float)$x, 2, '.', '')` |
| `artisan test --filter` | Guna `vendor/phpunit/phpunit/phpunit -c phpunit.xml --filter <nama>` |

## 25.7 Kod / Alat Penulisan

| Perangkap | Pembetulan |
|---|---|
| Nama kaedah mengandungi ruang (`kemas kini`) | `kemasKini` — sahkan dengan `get_class_methods()` |
| CJK/ruang tersembunyi dalam fail panjang | `grep -P '[\x{4e00}-\x{9fff}]'` selepas tulis |
| `protected static ?string $model = App\Modules\X\Models\Y::class;` dalam penjana | PHP resolve relatif ke namespace semasa → guna nama pendek `Y::class` |
| `glob()` dengan `__DIR__.'/../'` | Guna `realpath()` + `basename(dirname(dirname($f)))` |
| `class_exists()` di luar bootstrap Laravel | Guna `file_exists()` |
| Penomboran bab blueprint berganda | `grep '^## BAB '` sebelum tambah bab baharu |

## 25.8 Perangkap B-POS (jangan tiru)

B-POS (`b-pos-system`) dinilai **8/10 POS**, **4/10 keselamatan**, **0/10 AI**.
Ia **rujukan pelaksanaan**, bukan modul baharu.

**❌ Jangan tiru:** tiada `tenant_id` (satu deployment = satu syarikat) · tiada
global scope tenancy · `legacySingleOutletBypass` lalai **TRUE** (tiada outlet =
semua user dapat semua gudang) · `whatsapp-web.js` (risiko ban) · `access_token`
uuid tanpa had masa · 4 jadual wilayah Indonesia (salah untuk Malaysia).

**✅ Patut ditiru** (dipindah satu per satu, T-76 → T-84): `client_uuid` +
`sync_fingerprint = hash(cart+waktu+jumlah)` untuk idempotency offline ·
`TransactionTender` sebagai entiti (bukan kolum `cash`/`card`/`ewallet` berasingan) ·
`AuditLog` snapshot `before`/`after` · batch allocation FEFO · dine-in + kitchen
display · modifier/add-on · service charge · multi-tender · cashier shift ·
offline PWA · TNG Soundbox · discount approval · thermal printer WebUSB.

**Lesen:** asas `aryadwiputra/point-of-sales` = **MIT** → wajib atribusi dalam
`THIRD-PARTY-NOTICES.md`.

## 25.9 Peraturan Lesen OSS

| Lesen | Boleh rebrand + komersial? | Syarat |
|---|---|---|
| **MIT / Apache-2.0 / BSD** | ✅ | Copyright asal + `THIRD-PARTY-NOTICES.md` |
| **AGPL / GPL / BSL / SSPL** | ❌ | Rujukan reka bentuk sahaja |
| **Sustainable Use (n8n)** | ❌ | Bukan OSS — rujukan reka bentuk sahaja |

---

# 26. ARAHAN PENGESAHAN

```powershell
# Struktur — patut 58
(Get-ChildItem app\Modules -Directory).Count

# Laluan API — patut 1,518
php artisan route:list --path=api/v1 | Measure-Object -Line

# Laluan wildcard — MESTI KOSONG
php artisan route:list --path=api/v1 | Select-String "v1/\{"

# Ujian — patut 963 lulus, 4 dilangkau, 0 gagal
php artisan test

# Resource Filament — patut 66
(Get-ChildItem app\Filament\Resources -Directory).Count

# Keselamatan — patut "no security vulnerability advisories found"
composer audit

# Angka dokumen vs kod — semua ✅, exit 0
php skrip/semak-dokumen.php

# Kesihatan
Invoke-WebRequest http://127.0.0.1:8145/health -SkipHttpErrorCheck
Invoke-WebRequest http://127.0.0.1:8145/ready  -SkipHttpErrorCheck
```

**Jika ada angka berbeza → dokumen lapuk, kod betul.** Kemas kini dokumen, bukan kod.

---

# 27. GLOSARI & RUJUKAN

## 27.1 Glosari

| Istilah | Maksud |
|---|---|
| **Tenant** | Satu organisasi pelanggan — data terasing sepenuhnya |
| **Keahlian** | Baris `tenant_users` — pengguna ∈ tenant |
| **Prefix** | Segmen pertama laluan API, cth `api/v1/orders` → `orders` |
| **Namespace** | Bahagian sebelum `.` dalam `perm:` |
| **Kunci modul** | Nilai dalam `plans.modules` — mungkin ≠ namespace (guna `ALIAS`) |
| **Entitlemen** | Modul yang dibenarkan oleh langganan |
| **Otonomi** | Tahap AI bertindak (0–4) |
| **Outbox** | Peristiwa disimpan dulu, dihantar kemudian (idempoten) |
| **Pagar** | Syarat wajib sebelum tindakan berisiko |
| **Worktree** | Salinan git terasing untuk satu subtugas ADE |
| **Deny by default** | Tiada kebenaran → tolak |
| **Teras** | Namespace platform — tidak dikawal entitlemen |
| **Baseline** | Keputusan ujian asas sebelum perubahan |
| **Resource** | Halaman CRUD Filament untuk satu model |

## 27.2 Dokumen Rujukan

| Dokumen | Isi |
|---|---|
| **`SISTEM-PEMBANGUNAN-DARI-MULA.md`** (dokumen ini) | **Satu dokumen lengkap: bina + operasi + jualan** |
| [`BLUEPRINT-SISTEM-LENGKAP.md`](BLUEPRINT-SISTEM-LENGKAP.md) | Reverse engineering 23 bab (kod penuh setiap corak) |
| [`SISTEM-AI-BIJAN.md`](SISTEM-AI-BIJAN.md) | Faham sistem dalam 9 seksyen |
| [`KATALOG-MODUL-AI-BIJAN.md`](KATALOG-MODUL-AI-BIJAN.md) | Katalog terperinci setiap modul |
| [`getting-started.md`](getting-started.md) | Setup dari sifar |
| [`README.md`](README.md) | Titik masuk dokumentasi mengikut peranan |
| [`MODUL-AI-INTEGRASI.md`](MODUL-AI-INTEGRASI.md) | Peta kebergantungan AI |
| [`ANALISIS-KOMPETITIF.md`](ANALISIS-KOMPETITIF.md) | Pesaing & harga disahkan |
| [`BUSINESS-GROWTH-ENGINE.md`](BUSINESS-GROWTH-ENGINE.md) | 9 kelebihan + status kod |
| [`CADANGAN-IMPAK-TINGGI.md`](CADANGAN-IMPAK-TINGGI.md) | 15 cadangan keutamaan |
| [`RESELLER-KOMISEN-BERULANG.md`](RESELLER-KOMISEN-BERULANG.md) | Spesifikasi komisen berulang + analisis margin |
| [`AUDIT-DOKUMEN.md`](AUDIT-DOKUMEN.md) | Laporan audit dokumentasi |
| [`TODO.md`](TODO.md) | T-01 → T-102 (siap & belum) |
| `../AI-BIJAN-MASTER-BLUEPRINT.md` | Blueprint induk v9.6 — **103 bab / 27 PART** |
| `../.github/skills/aibijan-coding/` | Skill + gotchas + peraturan keselamatan |
| `docs/archive/README.md` | ⚠️ Arkib — **66 fail bukan rujukan semasa** |

## 27.3 Susunan Bacaan yang Disyorkan

| Peranan | Susunan |
|---|---|
| **Developer baru** | §1 → §2 → §4 → §10 → §16 → dokumen ini §25 |
| **Nak bina dari sifar** | §3 → §5 → §6 → §7 → §8 → §9 → §21 → §23 |
| **Kerja AI** | §13 → §14 → `MODUL-AI-INTEGRASI.md` |
| **Kerja UI** | §15 → §25.4 → ujian regresi panel §16.5 |
| **Buat keputusan perniagaan** | §24 → §20 → `ANALISIS-KOMPETITIF.md` |

---

**Akhir dokumen.**

Setiap angka, corak dan perangkap di sini diterbitkan daripada kod sebenar AI BIJAN
pada 26 Oktober 2026. Jika kod berubah, dokumen ini mesti dikemas kini —
dan cara termudah mengesannya ialah `php skrip/semak-dokumen.php`.
