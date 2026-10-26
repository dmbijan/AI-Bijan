# AI BIJAN — BLUEPRINT SISTEM LENGKAP (Reverse Engineering)

> **Tujuan:** Membolehkan sesiapa **membina semula AI BIJAN dari sifar** tanpa
> merujuk kod asal, tanpa contoh luaran, tanpa andaian.
>
> **Kaedah:** Setiap bahagian diterbitkan daripada kod sebenar — `app/`,
> `database/migrations/`, `routes/`, `config/` — bukan daripada hasrat atau pelan.
>
> **Disahkan:** 26 Oktober 2026 · Laravel 12.70.1 · PHP 8.2.12

---

## ISI KANDUNGAN

**BAHAGIAN I — ASAS**
1. Apa yang dibina (skop sebenar)
2. Keperluan teknikal
3. Falsafah seni bina (6 prinsip wajib)

**BAHAGIAN II — TERAS (bina ini dahulu)**
4. Lapisan data — skema teras
5. Pengasingan tenant (4 lapisan)
6. RBAC & kebenaran
7. Entitlemen modul (gate langganan)
8. Middleware chain
9. Audit & idempotency

**BAHAGIAN III — MODUL**
10. Anatomi modul (templat wajib)
11. Pendaftaran & laluan
12. Senarai 58 modul + jadual
13. Corak domain berulang

**BAHAGIAN IV — AI**
14. AI Gateway (satu pintu)
15. PromptGuard & PII
16. Wallet & kredit
17. Corak otonomi & kelulusan

**BAHAGIAN V — UI**
18. Filament — 7 peraturan wajib

**BAHAGIAN VI — OPERASI**
19. Ujian
20. Penjadual & queue
21. Keselamatan & pematuhan
22. Urutan pembinaan (12 fasa)
23. Senarai semak penerimaan

**LAMPIRAN**
A. Perangkap mengikut kategori
B. Arahan pengesahan
C. Glosari

---

# BAHAGIAN I — ASAS

## 1. Apa Yang Dibina

**AI BIJAN** ialah **multi-tenant SaaS modular monolith** — satu aplikasi Laravel
yang menyediakan perniagaan lengkap untuk PKS Malaysia.

### 1.1 Skop Sebenar (jangan lebih, jangan kurang)

| Dimensi | Nilai |
|---|---|
| Modul | **58** |
| Jadual | **346** |
| Laluan API | **1,518** |
| Prefix API | **76** |
| Ujian lulus | **962** |
| Model | **292** |
| Service | **166** |
| Controller | **127** |
| Resource UI | **66** |
| Kebenaran | **212** |
| Fail migration | **49** |

### 1.2 Apa Yang BUKAN

- ❌ **Bukan** microservices — satu aplikasi, satu pangkalan data
- ❌ **Bukan** ERP generik — domain Malaysia-first
- ❌ **Bukan** SPA berasingan — Blade + Filament, tiada Next.js/React
- ❌ **Bukan** ERPNext/Frappe — Laravel tulen
- ❌ **Bukan** platform chatbot — AI sebagai lapisan, bukan produk

### 1.3 Bentuk Sistem

```
Satu repositori
├── app/Modules/          58 modul (domain + AI)
├── app/Models/           42 model teras (tenant, user, stok, dokumen…)
├── app/Support/          Tenancy (TenantContext)
├── app/Http/             12 controller + 8 middleware
├── app/Filament/         66 resource + 6 widget + 1 page
├── database/migrations/  49 fail → 346 jadual
├── routes/api.php        laluan utama
├── config/aibijan.php    permissions + roles
└── tests/Feature/        44 fail ujian
```

---

## 2. Keperluan Teknikal

| Komponen | Versi | Nota |
|---|---|---|
| PHP | **8.2+** (diuji 8.2.12) | Jangan guna sintaks 8.3+ |
| Laravel | **12.x** (diuji 12.70.1) | |
| Pangkalan data | SQLite (dev) / MySQL 8 (prod) | |
| Filament | **3.3.x** (diuji 3.3.53) | Jangan cuba Filament 5 |
| PHPUnit | 11.x | Anotasi `/** @test */` **tidak** sah — guna atribut |
| Node | 18+ | Hanya untuk build Vite |
| Git | Di PATH | Diperlukan modul ADE (worktree) |

### 2.1 Pakej Wajib

```
laravel/framework        ^12.0
filament/filament        ^3.3.53
barryvdh/laravel-dompdf  ^3.1     ← JANGAN pin 3.0 (sekat illuminate ^11)
laravel/sanctum          (SPA/API auth)
phpunit/phpunit          ^11.5
nunomaduro/collision     ^8.9
```

### 2.2 Perangkap Pemasangan

1. **`barryvdh/laravel-dompdf` mesti `^3.1`** — versi 3.0 menyekat
   `illuminate/support ^11` dan menggagalkan resolver.
2. **`collision 8.9` perlukan `phpunit >= 11.5.50`** — naik taraf kedua-duanya
   dalam **satu** arahan composer, jika tidak "conflict analysis result".
3. **Collision tiada v9** — jangan cuba `^9.0`.
4. **Filament CVE**: guna `^3.3.53` (3.3.52 dan ke bawah terjejas).
5. **PHP 8.2 tidak benarkan `#[\PHPUnit\...\Test]`** — backslash hadapan ialah
   syntax error. Mesti `use PHPUnit\Framework\Attributes\Test;` + `#[Test]`.

---

## 3. Falsafah Seni Bina — 6 Prinsip Wajib

Ini bukan cadangan. Setiap satu mengatasi pepijat pengeluaran sebenar.

### Prinsip 1 — Tenant sentiasa eksplisit, tidak pernah tersirat

Tiada query tanpa skop tenant. Tiada rekod tanpa `tenant_id`.
Model perniagaan **mesti** guna trait `BelongsToTenant`.

### Prinsip 2 — Kebenaran dua lapis, bukan satu

RBAC (`perm:`) **DAN** entitlemen modul (`module:`).
Satu lapisan sahaja = lubang bisnes (tenant RM88 dapat akses semua modul).

### Prinsip 3 — Semua AI melalui satu pintu

Tiada modul memanggil provider AI secara terus. Semua melalui
`GatewayService::call()`. Ini satu-satunya tempat PromptGuard, PII redaction,
dan wallet berjalan.

### Prinsip 4 — Manusia mengawal tindakan berisiko

AI boleh mencadang. AI tidak boleh menghantar wang, memadam data, atau mengubah
tahap otonomi sendiri tanpa kelulusan manusia.

### Prinsip 5 — Spesifikasi ≠ pelaksanaan

Jangan tandakan ✅ untuk ciri belum dikodkan. Dokumen yang mendakwa lebih
daripada kod membawa kepada keputusan salah dengan penuh keyakinan.

### Prinsip 6 — Deny by default

Tiada kebenaran → tolak. Tiada langganan → tolak. Tiada baseline ujian → tolak.
Whitelist, bukan blacklist.

---

# BAHAGIAN II — TERAS

> **Bina Bahagian II sepenuhnya sebelum menyentuh mana-mana modul.**
> Kesilapan di sini tidak boleh dibaiki kemudian tanpa menulis semula setiap modul.

---

## 4. Lapisan Data — Skema Teras

### 4.1 Jadual Platform (WAJIB — 13 jadual)

#### `users` (Laravel asas + sambungan)

```php
// Sambungan pada jadual users:
is_platform_admin       boolean default false   // Super Admin
phone                   string(30) null
locale                  string(5) default 'ms'
two_factor_secret       text null
two_factor_confirmed_at timestamp null
last_login_at           timestamp null
is_active               boolean default true     // ← disemak setiap permintaan
```

#### `plans`

```php
id, code(string40 unique), name,
max_users(uint null), max_locations(uint null),
storage_mb(bigint null), ai_credits_monthly(bigint default 0),
modules(json null),         // senarai module_key
price_monthly(decimal 12,2 default 0),
is_active(boolean default true), timestamps
```

#### `tenants`

```php
id, ulid(unique), name, slug(60 unique),
status(enum: trial|active|past_due|suspended|cancelled) default trial,
plan_id(FK plans, nullOnDelete),
timezone(40) default 'Asia/Kuala_Lumpur',
currency(char3) default 'MYR',
locale(5) default 'ms',
trial_ends_at(timestamp null), timestamps
```

**Kenapa `ulid`?** ID berangka (`id`) digunakan untuk foreign key dalaman.
`ulid` didedahkan kepada klien (header `X-Tenant`) supaya ID dalaman tidak bocor.

**`isUsable()`** — semak `status IN (trial, active)`. Tenant
`suspended`/`cancelled` ditolak di middleware.

#### `tenant_users` — keahlian

```php
id, tenant_id(FK cascade), user_id(FK cascade),
status(enum: invited|active|disabled) default active,
invited_by(FK users null), invite_token_hash(char64 unique null),
invite_expires_at(timestamp null), joined_at(timestamp null), timestamps,
UNIQUE(tenant_id, user_id)
```

> ⚠️ **TIADA kolum `role`.** Peranan melalui pivot `role_tenant_user`.
> Ini kesilapan yang menyebabkan `MassAssignmentException` jika diandaikan.

#### `permissions` — global, bukan per-tenant

```php
id, key(string80 unique), module(string40), description(string null)
```

> `key` = `{namespace}.{aksi}` cth `orders.view`, `ade.approve`
> `module` = kunci modul untuk gate langganan

#### `roles` — per tenant

```php
id, tenant_id(FK cascade), key(string40), name,
is_system(boolean default false), timestamps,
UNIQUE(tenant_id, key)
```

#### Pivot

```php
permission_role:   role_id, permission_id          PK(role_id, permission_id)
role_tenant_user:  tenant_user_id, role_id         PK(tenant_user_id, role_id)
```

#### `subscriptions`

```php
id, tenant_id(FK cascade), plan_id(FK plans),
status(enum: trial|active|past_due|suspended|cancelled),
starts_at(timestamp), ends_at(timestamp null), timestamps
```

#### `tenant_modules` — entitlemen aktif

```php
id, tenant_id(FK cascade), module(string40),
enabled(boolean default true), timestamps,
UNIQUE(tenant_id, module)
```

#### `settings` — konfigurasi per tenant

```php
id, tenant_id(FK cascade), key(string80), value(json null), timestamps,
UNIQUE(tenant_id, key)
```

### 4.2 Jadual Sokongan Teras

| Jadual | Fungsi |
|---|---|
| `files` / `stored_files` | Muat naik, metadata, polisi akses |
| `api_keys` | Kunci API per tenant (disimpan hashed) |
| `audit_logs` | Jejak tindakan (siapa, apa, bila, before/after) |
| `approval_requests` | Pintu kelulusan (`type`, `payload`, `status`, `reason`) |
| `idempotency_keys` | Halang pendua permintaan tulis |
| `support_access_grants` | Akses sokongan Super Admin (baca sahaja, luput) |
| `document_sequences` | Penjanaan nombor dokumen berterusan |
| `documents` | Dokumen generik (invois, resit, kontrak) |
| `health_checks` / `observability_logs` | Observability |
| `plan_limits` | Had per plan (seats, AI tokens, storage) |

### 4.3 Konvensyen Skema (WAJIB SERAGAM)

| Perkara | Peraturan |
|---|---|
| **Kunci utama** | `id()` bigint auto-increment |
| **Tenant** | `tenant_id` bigint + INDEX, **kecuali** jadual anak |
| **Jadual anak** | Tiada `tenant_id` — diwarisi daripada induk |
| **Masa** | `timestamps()` pada setiap jadual |
| **Soft delete** | `softDeletes()` hanya bila domain perlu |
| **Wang** | `decimal(12,2)` atau `decimal(15,2)` — **jangan** float |
| **Peratus** | `decimal(5,2)` |
| **JSON** | `json()` untuk struktur fleksibel |
| **Enum** | `enum()` dengan senarai tertutup |
| **Awalan jadual** | Guna awalan modul untuk elak konflik |

> ⚠️ **Awalan wajib.** `workflow_definitions` (FlowAutomation) bertindih dengan
> `workflows`/`workflow_runs` (WorkflowStudio). Selesaikan dengan `studio_*`.

---

## 5. Pengasingan Tenant — 4 Lapisan

Ini komponen paling kritikal dalam sistem. Bina sekali, betul.

### 5.1 `TenantContext` — satu-satunya sumber kebenaran

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
        return $this->tenant ?? throw new RuntimeException('Tiada tenant aktif.');
    }

    public function id(): ?int { return $this->tenant?->id; }

    public function membership(): ?TenantUser { return $this->membership; }

    /** Kunci kebenaran pengguna dalam tenant ini. */
    public function permissions(): array
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }
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
- `clear()` **wajib** dalam blok `finally` — jika tidak, konteks bocor ke
  permintaan berikutnya (kritikal dalam queue worker).
- `permissions()` pulang `[]` jika membership tiada atau `status !== 'active'`.

### 5.2 Trait `BelongsToTenant` — 3 hook Eloquent

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
                $ctx->has() ? $ctx->id() : 0     // ← tiada tenant = 0 = tiada rekod
            );
        });

        // 2. CREATING — auto-fill + tolak ketidakpadanan
        static::creating(function ($m) {
            $ctx = app(TenantContext::class);
            if (! $ctx->has()) {
                throw new RuntimeException('Rekod tenant tidak boleh dicipta tanpa tenant aktif.');
            }
            if ($m->tenant_id && (int) $m->tenant_id !== $ctx->id()) {
                throw new RuntimeException('tenant_id tidak sepadan dengan tenant aktif.');
            }
            $m->tenant_id = $ctx->id();
        });

        // 3. UPDATING — halang pemindahan
        static::updating(function ($m) {
            if ($m->isDirty('tenant_id')) {
                throw new RuntimeException('tenant_id tidak boleh diubah.');
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
`where tenant_id = null` dalam SQL tidak padan apa-apa **melainkan** ditulis
`IS NULL` — mudah tersilap. `0` tidak boleh wujud sebagai ID sebenar.

### 5.3 Ujian Pengasingan Tenant (WAJIB untuk setiap modul)

```php
public function test_tenant_lain_tidak_nampak_data(): void
{
    [$u1, $t1] = $this->daftar('a@x.my', 'Syarat A');
    [$u2, $t2] = $this->daftar('b@x.my', 'Syarat B');

    $this->dalamTenant($t1);
    $rekod = Model::create([/* ... */]);

    // ⚠️ withoutGlobalScopes() WAJIB — global scope tapis ikut konteks SEMASA
    $this->dalamTenant($t2);
    $this->assertSame(0, Model::withoutGlobalScopes()
        ->where('id', $rekod->id)
        ->where('tenant_id', $t2->id)->count());
}
```

### 5.4 Perangkap Pengasingan (semua pernah berlaku)

| Perangkap | Kesan | Pembetulan |
|---|---|---|
| `withoutGlobalScope('tenant')` dalam provisioning | Rekod dicipta dengan `tenant_id = 0` | Guna `forceFill(['tenant_id' => $id])` |
| Join + global scope | `ambiguous column name: tenant_id` | `withoutGlobalScopes()` + qualify semua kolum |
| Relation di-cache | Data lama selepas `create()` | Kira terus dari DB |
| `dalamTenant($t)` tanpa membership | `permissions()` pulang kosong | `app(TenantContext::class)->set($t, $membership)` |
| Selepas HTTP request dalam ujian | Konteks dikosongkan | Cipta rekod **sebelum** `$this->sebagai()` |
| Queue worker | Konteks dari job sebelumnya | Tetapkan semula dalam setiap job |

---

## 6. RBAC & Kebenaran

### 6.1 Format Kunci

```
{namespace}.{aksi}
```

**Aksi standard:** `view` · `manage` · `execute` · `approve` · `reports`

**Aksi domain:** `post` (jurnal) · `sign` (tandatangan) · `finance` (margin) ·
`sell` (POS)

### 6.2 Konfigurasi (`config/aibijan.php`)

```php
return [
    'permissions' => [
        // Platform teras
        'tenant.settings.view'  => 'Lihat tetapan syarikat',
        'members.view'          => 'Lihat ahli',
        'members.invite'        => 'Jemput ahli',
        'roles.manage'          => 'Urus peranan',
        'audit.view'            => 'Lihat jejak audit',

        // Domain — satu set bagi setiap modul
        'orders.view'    => 'Lihat pesanan',
        'orders.manage'  => 'Cipta/edit pesanan',
        'orders.execute' => 'Tukar status & sync',

        'ade.view'     => 'Lihat skills, sandbox, coordination & review',
        'ade.manage'   => 'Bina/kemas kini skill, sandbox & review',
        'ade.execute'  => 'Jalankan coordination, sub-ejen & semakan diff',
        'ade.approve'  => 'Luluskan review kod & gabung perubahan',
    ],

    'roles' => [
        'tenant_owner'      => ['name' => 'Pemilik',  'permissions' => ['*']],
        'tenant_admin'      => ['name' => 'Pentadbir','permissions' => [/* eksplisit */]],
        'finance_manager'   => ['name' => 'Pengurus Kewangan', 'permissions' => [...]],
        'sales_manager'     => ['name' => 'Pengurus Jualan',   'permissions' => [...]],
        'hr_manager'        => ['name' => 'Pengurus HR',       'permissions' => [...]],
        'inventory_manager' => ['name' => 'Pengurus Stok',     'permissions' => [...]],
        'cashier'           => ['name' => 'Juruwang', 'permissions' => [...]],
        'staff'             => ['name' => 'Staf',     'permissions' => [...]],
        'auditor'           => ['name' => 'Juruaudit','permissions' => [...]],
    ],
];
```

> ⚠️ **`'*'` bermaksud "semua yang WUJUD dalam jadual `permissions`"** — bukan
> "semua yang mungkin". Jika namespace tidak didaftarkan, pemilik tetap 403.

### 6.3 Segerak ke Pangkalan Data

Skrip `skrip/sync-perm.php` mesti:
1. Baca `config('aibijan.permissions')`
2. `updateOrCreate` setiap kunci ke jadual `permissions`
3. Bagi setiap tenant: cipta peranan sistem jika tiada
4. Beri **semua** kebenaran kepada `tenant_owner`

**Ini WAJIB dijalankan** selepas `migrate` dan selepas setiap tambah kebenaran.
Tanpanya: menu muncul tetapi setiap halaman 403.

---

## 7. Entitlemen Modul — Gate Langganan

### 7.1 Masalah Yang Diselesaikan

Tanpa gate ini, tenant yang bayar RM88 mendapat akses semua modul.
Ini bukan pepijat teknikal — ia **kebocoran pendapatan**.

### 7.2 `ModuleGate`

```php
class ModuleGate
{
    // Namespace yang BUKAN modul domain — sentiasa dibenarkan
    public const TERAS = [
        'members', 'roles', 'audit', 'files', 'api_keys', 'approvals',
        'documents', 'biometric', 'ai_gateway', 'tenant', 'platform',
        'entitlement', 'fundamental',
    ];

    // Alias: namespace permission → module_key dalam jadual plans
    public const ALIAS = [
        'content'     => 'content_studio',
        'workflow'    => 'workflow_studio',
        'media'       => 'media_studio',
        'media_editor'=> 'media_editor',
        'mcp'         => 'mcp_studio',
        'personal_ai' => 'personal_assistant',
        'ade'         => 'ade',

        // ⚠️ Namespace ≠ kunci modul — TANPA alias, menu tersembunyi
        //    walaupun pemilik ada ['*']
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

### 7.3 Kenapa Alias Diperlukan

Jadual `permissions` menggunakan namespace **kebenaran**.
Jadual `plans` menggunakan kunci **modul**. Ia tidak semestinya sama.

Contoh sebenar: resource Filament "Perbelanjaan" guna `expense.*`, tetapi
resource "Pesanan" guna `omnichannel.*` manakala plan menyenaraikan `orders`.

### 7.4 `EntitlementService` — Perangkap Skop Tenant

```php
// ❌ SALAH — global scope tapis ikut konteks SEMASA, bukan $tenantId
TenantModule::where('tenant_id', $tenantId)->first();

// ✅ BETUL — baca
TenantModule::withoutGlobalScope('tenant')
    ->where('tenant_id', $tenantId)->first();

// ✅ BETUL — cipta
TenantModule::withoutGlobalScope('tenant')
    ->create(['tenant_id' => $tenantId, 'module' => $m, 'enabled' => true]);
```

**Kenapa:** jika konteks kosong (provisioning, CLI, ujian), global scope menapis
`tenant_id = 0` → bacaan pulang kosong → rekod dicipta dengan `tenant_id = 0`.

Kaedah yang **mesti** guna `withoutGlobalScope('tenant')`:
`grantModul()` · `revokeModul()` · `bolehModul()` · `modulAktif()` ·
`syncModul()` · `aktifkanModul()`

---

## 8. Middleware Chain

### 8.1 Urutan Wajib

```
Route::prefix('v1')
  → middleware('auth:sanctum')          // 1. Identiti
  → middleware('tenant')                // 2. Konteks tenant
  → middleware('perm:{modul}.{aksi}')   // 3. RBAC + entitlemen
  → controller
```

### 8.2 `ResolveTenant`

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
AuditLogger::log('support.access', $tenant, null, ['path' => ..., 'grant' => $grant->id]);
```

### 8.3 `RequirePermission` — Dua Lapisan

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
                    'meta'    => ['module' => $hasil['module'], 'upgrade' => '...'],
                ], 403);
            }
        }
    }

    return $next($request);
}
```

### 8.4 Middleware Lain

| Middleware | Fungsi |
|---|---|
| `PlatformAdmin` | Had akses ke laluan platform (bukan tenant) |
| `RequireModule` | Gate modul eksplisit: `module:seo,content` |
| `IdempotencyMiddleware` | Halang pendua POST/PUT guna `Idempotency-Key` |
| `SecureHeaders` | CSP, HSTS, X-Frame-Options |
| `ResolveFilamentTenant` | Konteks tenant untuk panel Filament (§18) |

---

## 9. Audit & Idempotency

### 9.1 `AuditLogger`

```php
app(AuditLogger::class)->log(
    string $action,      // 'tenant.created', 'support.access', 'expense.approved'
    $subject,            // model
    ?array $before,      // snapshot sebelum
    ?array $after        // snapshot selepas
);
```

**Bila WAJIB audit:** cipta/padam tenant · akses sokongan · kelulusan ·
perubahan kebenaran · tindakan AI autonomi · eksport data.

### 9.2 Idempotency

Untuk semua endpoint tulis yang boleh diulang (webhook, sync, ingest pesanan):

```php
$key = $request->header('Idempotency-Key');
// Simpan hash tindakan + keputusan dalam `idempotency_keys`
// Jika wujud → pulang keputusan asal, JANGAN laksana semula
```

**Corak alternatif** (ingest domain):

```php
$key = md5($eventName . '-' . $aggregate . '-' . $id . '-' . json_encode($payload));
```

---

# BAHAGIAN III — MODUL

## 10. Anatomi Modul (Templat Wajib)

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

### 10.1 Provider

```php
namespace App\Modules\ADE;

class ADEServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/ADE/config/ade.php',
            'ade'
        );

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

Daftar dalam `bootstrap/providers.php`.

> ⚠️ **PREFIX WAJIB.** Tanpa prefix, modul mencipta wildcard `api/v1/{id}` yang
> menangkap semua laluan lain. Sahkan:
> `php artisan route:list --path=api/v1 | Select-String "v1/\{"` → mesti kosong.

### 10.2 Model

```php
namespace App\Modules\ADE\Models;

class AdeSkill extends Model
{
    use BelongsToTenant;                    // ← WAJIB (kecuali model global)

    protected $table = 'ade_skills';        // ← WAJIB jika nama ≠ plural kelas

    protected $fillable = [
        'tenant_id', 'key', 'name', 'category', 'description',
        'system_prompt', 'steps', 'triggers', 'tools', 'config',
        'source', 'origin_project', 'license', 'is_enabled', 'is_public',
        'install_count', 'usage_count', 'success_rate',
    ];

    protected $casts = [
        'steps'      => 'array',
        'triggers'   => 'array',
        'is_enabled' => 'boolean',
        'usage_count'=> 'integer',
        'success_rate' => 'float',
    ];

    protected $attributes = [               // nilai lalai
        'category'   => 'general',
        'is_enabled' => true,
        'is_public'  => false,
    ];

    public function invocations(): HasMany
    {
        return $this->hasMany(AdeSkillInvocation::class, 'ade_skill_id');
    }
}
```

> ⚠️ **`$table` wajib** jika nama jadual ≠ `Str::plural(Str::snake(class))`.
> Tanpa ia: `Loyalty\Models\Reward` → Eloquent cari `rewards`, jadual sebenar
> `loyalty_rewards` → 500 "no such table".

### 10.3 Service

```php
class AdeSkillService
{
    // Had keselamatan sebagai konstan (bukan nombor ajaib)
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

        // Bina eksplisit, BUKAN mass-assign
        $skill = new AdeSkill();
        $skill->tenant_id     = $tenantId;
        $skill->key           = $key;
        $skill->name          = $data['name'];
        $skill->steps         = $this->guardSteps($data['steps'] ?? []);
        $skill->triggers      = $this->guardTriggers($data['triggers'] ?? []);
        $skill->save();

        return $skill;
    }

    private function cari(int $tenantId, int $id): AdeSkill
    {
        // Cari DENGAN skop tenant — bukan find() sahaja
        $m = AdeSkill::where('tenant_id', $tenantId)->where('id', $id)->first();
        if ($m === null) {
            throw new \RuntimeException('Tidak dijumpai.');
        }
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

**Corak service:**
1. **Sahkan input** (had, format, kewujudan)
2. **Cari dengan skop tenant** — bukan `find()` sahaja
3. **Throw exception** untuk pelanggaran — bukan pulang `false`
4. **Tiada logik HTTP** — service tidak tahu tentang `Request`/`Response`

### 10.4 Controller

```php
namespace App\Modules\ADE\Http\Controllers;

use App\Http\Controllers\Controller;        // ← WAJIB (tiada base class dalam modul)

class AdeController extends Controller
{
    public function __construct(private AdeOrchestrator $ade) {}

    private function tenantId(): int
    {
        // ← BUKAN auth()->user()->tenant_id (atribut itu tidak wujud)
        return (int) app(\App\Support\Tenancy\TenantContext::class)->id();
    }

    public function senaraiSkill(Request $request): JsonResponse
    {
        $data = $this->ade->skills()->senarai(
            $this->tenantId(),
            $request->query('kategori')
        );
        return response()->json(['data' => $data, 'jumlah' => count($data)]);
    }

    public function janaSkill(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key'  => ['required', 'string', 'min:3', 'max:80'],
            'name' => ['required', 'string', 'max:150'],
            'steps'=> ['nullable', 'array', 'max:20'],
        ]);

        $skill = $this->ade->skills()->janaSkill($this->tenantId(), $validated);

        return response()->json(['data' => $skill], 201);
    }
}
```

### 10.5 Laluan

```php
use Illuminate\Support\Facades\Route;
use App\Modules\ADE\Http\Controllers\AdeController;

// Gambaran keseluruhan
Route::get('/summary', [AdeController::class, 'ringkasan'])->middleware('perm:ade.view');

// A1: Skills
Route::get('/skills',                    [AdeController::class, 'senaraiSkill'])->middleware('perm:ade.view');
Route::post('/skills',                   [AdeController::class, 'janaSkill'])   ->middleware('perm:ade.manage');
Route::post('/skills/install',           [AdeController::class, 'pasangSkill'])->middleware('perm:ade.manage');
Route::post('/skills/match',             [AdeController::class, 'padanSkill']) ->middleware('perm:ade.view');
Route::get('/skills/{id}/invocations',   [AdeController::class, 'sejarahSkill'])->middleware('perm:ade.view');

// A2: Sandbox — ⚠️ Laluan STATIK mesti sebelum {id}
Route::get('/sandboxes/branches',        [AdeController::class, 'senaraiBranch'])->middleware('perm:ade.view');
Route::get('/sandboxes',                 [AdeController::class, 'senaraiSandbox'])->middleware('perm:ade.view');
Route::post('/sandboxes',                [AdeController::class, 'ciptaSandbox'])  ->middleware('perm:ade.execute');
Route::post('/sandboxes/{id}/baseline',  [AdeController::class, 'rekodBaseline'])->middleware('perm:ade.execute');
Route::delete('/sandboxes/{id}',         [AdeController::class, 'buangSandbox'])->middleware('perm:ade.manage');

// A4: Review
Route::post('/reviews/{id}/approve',     [AdeController::class, 'sahkanReview'])->middleware('perm:ade.approve');
```

> ⚠️ **Laluan statik MESTI sebelum `{id}`.** Jika tidak, `/branches` ditawan
> oleh `/{id}` dan `$id = "branches"`.

---

## 11. Pendaftaran & Laluan

### 11.1 Urutan Boot

1. `bootstrap/providers.php` — senarai provider
2. Provider modul **didaftar sebelum** `routes/api.php`
3. → **Laluan provider sentiasa menang**

**Implikasi:** jika mahu `routes/api.php` menang, **buang** laluan duplikat dari provider.

### 11.2 Perangkap Laluan

| Perangkap | Gejala | Pembetulan |
|---|---|---|
| Tiada prefix | Wildcard `api/v1/{id}` tangkap semua | Sentiasa `->prefix('api/v1/<modul>')` |
| Prefix berganda | `projects/projects` | Guna `'/'` dalam fail laluan dalaman |
| Dua controller, satu jadual | Satu sentiasa 404 | Pilih satu, atau beri prefix berbeza |
| `use` dalam closure | PHP syntax error | Semua `use` di blok import atas fail |
| Regex pada `routes/api.php` | Fail rosak | Guna edit tool, bukan `-replace` |

---

## 12. Senarai 58 Modul + Jadual

### 12.1 Jualan & Pemasaran (8 · 398 laluan)

| Modul | Prefix | Awalan jadual | Laluan |
|---|---|---|---|
| `Site` | `site` | `site_*` | 128 |
| `Marketing` | `marketing` | `marketing_*` | 101 |
| `CRM` | `crm` | `crm_*` | 46 |
| `Loyalty` | `loyalty` | `loyalty_*` | 43 |
| `Affiliate` | `affiliate` | `affiliate_*` | 39 |
| `Blast` | `blast` | `blast_*` | 15 |
| `Link` | `link` | `link_*` | 15 |
| `Prospecting` | `prospecting` | `prospect_*` | 11 |

### 12.2 AI & Automasi (15 · 227 laluan)

| Modul | Prefix | Awalan jadual | Laluan |
|---|---|---|---|
| `ADE` | `ade` | `ade_*` | 24 |
| `ContentStudio` | `content` | `content_*` | 24 |
| `BusinessIntel` | `business-intel` | `bi_*` | 20 |
| `PersonalAssistant` | `personal-ai` | `ai_tasks`, `knowledge_*` | 19 |
| `AIAgents` | `ai-agents` | `ai_agent*` | 18 |
| `BrainHub` | `brainhub` | `brainhub_*` | 18 |
| `FlowAutomation` | `flows` | `workflow_definitions`, `workflow_runs` | 18 |
| `Boardroom` | `boardroom` | `boardroom_*` | 14 |
| `McpStudio` | `mcp` | `mcp_*` | 14 |
| `AgentStudio` | `agent-studio` | `agent_*` | 13 |
| `MediaStudio` | `media` | `media_*` | 12 |
| `AIGateway` | `ai-gateway` | `ai_*` | 12 |
| `WorkflowStudio` | `workflows` | `studio_*` | 9 |
| `ExecutiveAI` | `executive-ai` | `executive_*` | 7 |
| `SalesCopilot` | `sales-copilot` | (tiada) | 5 |

### 12.3 Operasi & Logistik (14 · 420 laluan)

| Modul | Prefix | Awalan jadual | Laluan |
|---|---|---|---|
| `HRMS` | `hrms` | `hr_*`, `biometric_*` | 88 |
| `Marketplace` | `marketplace` | `marketplace_*` | 47 |
| `Booking` | `booking` | `booking_*` | 46 |
| `VirtualOffice` | `virtual-office` | `vo_*` | 42 |
| `ServiceOS` | `service-os` | `service_*` | 35 |
| `Inventory` | `inventory` | `stock_*`, `items`, `warehouses` | 28 |
| `SCM` | `scm` | `scm_*` | 26 |
| `POS` | `pos` | `pos_*` | 24 |
| `Logistics` | `logistics` | `logistics_*` | 22 |
| `Reminder` | `reminders` | `reminder*` | 18 |
| `Fleet` | `fleet` | `fleet_*` | 13 |
| `Orders` | `orders` | `unified_order*` | 12 |
| `Cod` | `cod` | `cod_*` | 11 |
| `Manufacturing` | `manufacturing` | `boms`, `bom_lines`, `work_orders` | 8 |

### 12.4 Kewangan & People (7 · 202 laluan)

| Modul | Prefix | Awalan jadual | Laluan |
|---|---|---|---|
| `Invoicing` | `invoicing` | `invoices`, `documents` | 46 |
| `Projects` | `pm` | `projects`, `tasks`, `timesheets` | 35 |
| `Assets` | `assets` | `assets`, `asset_*` | 33 |
| `Expense` | `expenses` | `expense_*` | 26 |
| `Finance` | `finance` | `chart_of_accounts`, `journal_*` | 26 |
| `Payroll` | `payroll` | `payroll_*` | 21 |
| `COGS` | `cogs` | `cogs_*` | 15 |

### 12.5 Platform, Komunikasi & Analitik (12 · 182 laluan)

| Modul | Prefix | Awalan jadual | Laluan |
|---|---|---|---|
| `Omnichannel` | `omnichannel` | `omnichannel_*` | 26 |
| `SignDocs` | `sign-docs` | `sign_docs_*` | 25 |
| `Analytics` | `analytics` | `analytics_*` | 25 |
| `Seo` | `seo` | `seo_*` | 18 |
| `Vision` | `vision` | `cctv_*` | 14 |
| `DocumentAI` | `document-ai` | (guna `documents`) | 13 |
| `MediaEditor` | `media-editor` | `media_projects`, `media_templates` | 13 |
| `Helpdesk` | `helpdesk` | `tickets`, `kb_articles` | 12 |
| `IndustryPacks` | `industry-packs` | `industry_profiles` | 10 |
| `LiveSupport` | `live-support` | `live_*` | 9 |
| `ProofOfValue` | `proof-of-value` | `pov_*` | 9 |
| `Community` | `community` | `community_*` | 8 |

### 12.6 Teras Platform (bukan modul domain · 89 laluan)

`Fundamental` (11) · `Entitlement` (9) · `admin` (7) · `auth` (7) · `documents` (7) ·
`knowledge` (6) · `webhooks` (4) · `public` (4) · `tasks` (4) · `tenant` (3) ·
`members` (3) · `roles` (3) · `api-keys` (3) · `approvals` (2) · `files` (2) ·
`audit` (1) · `invitations` (1) · `timesheets` (1)

> **Reconcile:** 1,429 (domain) + 89 (teras) = **1,518**.

---

## 13. Corak Domain Berulang

Bina sekali, guna semula merentas modul.

### 13.1 Status + Peta Aliran

```php
public const STATUS = ['draft','pending','confirmed','paid','processing',
    'packed','shipped','out_for_delivery','delivered','cancelled',
    'refunded','partially_refunded','on_hold','failed'];

// Perpindahan sah SAHAJA
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

### 13.2 Pemetaan Status Platform

```php
public const PETA_STATUS = [
    'shopee' => ['READY_TO_SHIP' => 'confirmed', 'SHIPPED' => 'shipped'],
    'lazada' => ['pending' => 'pending', 'shipped' => 'shipped'],
    'tiktok' => ['AWAITING_SHIPMENT' => 'confirmed'],
    'pos'    => ['completed' => 'delivered'],
];
// Override per tenant melalui jadual `channel_bindings`
```

### 13.3 Polimorfik — `subject_type` + `subject_id`

Digunakan oleh `Reminder`, `Seo`, `AuditLog`, `ProofOfValue`, `SignDocs`:

```php
$table->string('subject_type');                  // FQCN model
$table->unsignedBigInteger('subject_id');
$table->string('subject_label')->nullable();     // untuk paparan

public function subject(): MorphTo { return $this->morphTo(); }
```

**Kenapa:** satu jadual peringatan boleh ikat ke invois, pesanan, kontrak, pelanggan —
tanpa jadual berasingan setiap satu.

### 13.4 Penjanaan Nombor Dokumen

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
        return sprintf('%s-%s-%04d',
            strtoupper($jenis), now()->format('ym'), $seq->counter);
    });
}
```

### 13.5 Outbox Peristiwa Domain

```php
$table->string('event_name');
$table->json('payload');
$table->string('idempotency_key')->unique();
$table->enum('status', ['pending','dispatched','failed'])->default('pending');
$table->unsignedTinyInteger('attempts')->default(0);

// Kunci idempoten
$key = md5($eventName . '-' . $aggregate . '-' . $id . '-' . json_encode($payload));
```

**Kenapa outbox:** peristiwa tidak hilang jika queue mati. Idempoten = boleh
dihantar semula tanpa kesan berganda.

### 13.6 Pengiraan Wang

```php
// SENTIASA decimal, TIDAK PERNAH float
$table->decimal('total_amount', 15, 2);

// Model
protected $casts = ['total_amount' => 'decimal:2'];

// ⚠️ decimal cast pulang STRING
(string) $model->total_amount;          // '1560'  BUKAN '1560.00'
number_format((float) $x, 2, '.', '');  // '1560.00' — guna ini dalam ujian
```

### 13.7 Kesan Pendua (Resit)

```php
// Hash kandungan untuk kesan pendua
$hash = hash('sha256', $merchant . '|' . $jumlah . '|' . $tarikh);
```

---

# BAHAGIAN IV — AI

## 14. AI Gateway — Satu Pintu

### 14.1 Mengapa Satu Pintu

Jika setiap modul memanggil provider terus:
- Kunci API tersebar (bocor ke frontend)
- Tiada had kos (tenant boleh bakar kredit)
- Tiada pengesanan suntikan prompt
- Tiada audit penggunaan

**Satu pintu** = satu tempat untuk semua kawalan.

### 14.2 Aliran `call()` — 8 Langkah

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

> ⚠️ **LANGKAH 0 MESTI DI ATAS.** Jika dialihkan selepas feature flag, mod
> simulasi (`AI_GATEWAY_LIVE=false`) **tidak** disekat — pepijat keselamatan
> sebenar yang pernah berlaku dalam projek ini.

### 14.3 Routing Model

```php
// config/ai_gateway.php
'model_routing' => [
    'default'   => 'gpt-4o-mini',
    'reasoning' => 'gpt-6-astra',      // CEO, CFO, COO, CISO, analyst, researcher
    'creative'  => 'claude-opus',      // CMO, ads specialist
    'quick'     => 'gpt-4o-mini',      // CHRO, support, exec assistant
],
```

### 14.4 Binding Provider

```php
// AIGatewayServiceProvider
$this->app->singleton(GatewayService::class, function ($app) {
    return new GatewayService(
        $app->make(PiiRedactor::class),
        $app->make(PromptGuard::class)      // ← 2 argumen WAJIB
    );
});
```

### 14.5 Mod Simulasi

`AI_GATEWAY_LIVE=false` (lalai) → respons deterministik tanpa panggilan rangkaian.
**Ini membolehkan ujian tanpa kos dan tanpa kunci API.**

---

## 15. PromptGuard & PII

### 15.1 Struktur

```php
class PromptGuard
{
    public const CORAK = [           // 12 corak input
        '/ignore\s+(all\s+)?(previous|above)\s+(instructions?|prompts?)/i',
        '/you\s+are\s+now\s+(a|an)\s+/i',
        '/system\s*:\s*/i',
        // ... 9 lagi
    ];

    public const CORAK_OUTPUT = [    // 4 corak output (bocor kunci)
        '/(sk|pk|api)[-_]?key\s*[:=]\s*\S+/i',
        '/Bearer\s+[A-Za-z0-9\-._~+\/]{20,}/',
        // ...
    ];

    public function imbasInput(string $teks): array;      // {ditolak, corak, skor}
    public function imbasMesej(array $messages): array;   // mesej berbilang
    public function imbasOutput(string $teks): array;
    public function bungkusSebagaiData(string $input): string;
    public function sanitasiOutput(string $teks): string;
}
```

### 15.2 PII Redaction

Buang **sebelum** ke provider: nombor IC (MyKad), telefon, e-mel, nombor akaun bank.

```php
$safeMessages = $this->redactor->redactMessages($messages);
```

> ⚠️ API key provider ialah **ADMIN ONLY** — tidak pernah ke frontend, tidak
> pernah dalam log.

---

## 16. Wallet & Kredit

```php
// ai_credit_wallets: tenant_id, balance, monthly_allowance, reset_at
// ai_usage_events:    tenant_id, user_id, model, input_tokens, output_tokens,
//                     credits_used, status, correlation_id, latency_ms

$wallet    = AiCreditWallet::forTenant($tenantId);
$anggaran  = $model->calculateCredits($estimatedTokens, $maxOutput);

if (! $wallet->canAfford($anggaran)) {
    $this->logUsage(..., AiUsageEvent::STATUS_BLOCKED, 'Baki kredit tidak mencukupi.');
    return $this->failResponse('Baki kredit AI tidak mencukupi. Sila top-up.');
}
```

**Status penggunaan:** `success` · `failed` · `blocked` · `simulated`

---

## 17. Corak Otonomi & Kelulusan

### 17.1 Tahap Otonomi (0–4)

| Tahap | Nama | Tingkah laku |
|---|---|---|
| **0** | Tanya | Ejen hanya bertanya, tiada tindakan |
| **1** | Cadang | Ejen cadang, manusia pilih |
| **2** | Draf | Ejen sediakan draf, manusia luluskan |
| **3** | Laksana | Ejen laksana, eskalasi jika perlu |
| **4** | Automasi | Ejen laksana sepenuhnya |

### 17.2 Tindakan SENTIASA Perlu Kelulusan

Tidak kira tahap otonomi:

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

### 17.3 Pagar Tindakan AI

| Pagar | Contoh |
|---|---|
| **Identiti disahkan** | Hantar dokumen kewangan hanya selepas pengesahan identiti |
| **Consent direkod** | Blast hanya jika `consent_status = granted` + bukti + belum luput |
| **Opt-out serta-merta** | RFC 8058 `List-Unsubscribe` + one-click |
| **Tetingkap 24 jam** | WhatsApp: mesej perniagaan hanya dalam tetingkap |
| **Tiada dokumen jika bayaran gagal** | Resit/invois hanya selepas bayaran disahkan |
| **Kritikal menyekat** | Penemuan `critical` dalam semakan kod menyekat kelulusan |

### 17.4 Corak "AI Mencadang, Manusia Memutuskan"

```php
// AI mencadang kategori perbelanjaan
$cadangan = $ai->cadangKategori($receipt);
// {kategori: 'pengangkutan', keyakinan: 0.87}

// ⚠️ AI TIDAK BOLEH set approved_amount
// Manusia mesti sahkan
```

---

# BAHAGIAN V — UI

## 18. Filament — 7 Peraturan Wajib

### 18.1 Struktur Resource

```
app/Filament/Resources/<Nama>/
├── <Nama>Resource.php
└── Pages/
    ├── List<Nama>.php
    ├── Create<Nama>.php
    ├── Edit<Nama>.php
    └── View<Nama>.php
```

### 7 Peraturan (setiap satu pernah menyebabkan kegagalan)

**1. `getPages()` WAJIB**

Folder `Pages/` + resource class **tidak cukup**. Tanpa `getPages()`:
`Route [filament.admin.resources.X.index] not defined`.

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

**2. JANGAN `use Filament\Resources\Pages;`**

Resource dalam `App\Filament\Resources\X\` mesti resolve `Pages\ListX`
**secara relatif** ke `App\Filament\Resources\X\Pages\ListX`.
Import eksplisit **memecahkan** rujukan.

**3. Closure kolum terima `$record`, BUKAN `$s`**

```php
// ❌ 500 sunyi — BindingResolutionException [$s] unresolvable
IconColumn::make('x')->boolean(fn ($s) => $s->isOk()),

// ✅
IconColumn::make('x')->boolean(fn ($record) => $record->isOk()),
```

Filament suntik parameter **mengikut nama**. Sah: `$record`, `$state`,
`$column`, `$row`, `$livewire`.

**4. Kebenaran melalui trait**

```php
use HasModulePermission;

protected static function permissionNamespace(): string
{
    return 'ade';      // abstract — MESTI diimplement
}
```

Trait sediakan `canViewAny/canView/canCreate/canEdit/canDelete` +
`shouldRegisterNavigation` berdasarkan `perm:` + entitlemen.

> ⚠️ **PHP 8.2: trait TIDAK BOLEH declare typed static property** yang resource
> override → guna `abstract protected static function permissionNamespace()`.

**5. `$slug` wajib**

Tanpa ia: URL jadi `/admin/expenses/expenses` (berganda).

**6. Widget: `$isLazy = false`**

```php
protected static bool $isLazy = false;
```

Lazy loading tinggalkan `<div class="fi-loading-section">` kosong jika AJAX gagal.

**7. JANGAN gabung `discoverWidgets()` dengan `widgets([])`**

Apabila `filament:cache-components` dijalankan, senarai eksplisit ditimpa dan
sebahagian widget hilang **senyap**. Guna `->widgets([...])` sahaja.

### 18.2 Konteks Tenant untuk Panel

```php
// bootstrap/app.php
$middleware->web(append: [ResolveFilamentTenant::class]);
```

**Kenapa dalam `web`, bukan `authMiddleware` panel:** Livewire memanggil
`POST /livewire/update` di luar laluan panel. Tanpanya, widget berjalan tanpa
konteks → KPI sentiasa 0.

### 18.3 CSP

```php
// config/security.php
'use_nonce' => false,     // lalai
```

> ⚠️ **`nonce` dan `'unsafe-inline'` SALING EKSKLUSIF.** Jika nonce hadir,
> pelayar **abaikan** `'unsafe-inline'` → semua skrip inline Livewire disekat →
> butang login mati. Alpine.js perlukan `'unsafe-eval'`.

---

# BAHAGIAN VI — OPERASI

## 19. Ujian

### 19.1 Trait Asas

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

### 19.2 Kategori Ujian Wajib Setiap Modul

| Kategori | Minimum |
|---|---|
| Pengasingan tenant | 2 (baca + tulis) |
| Cipta gembira | 1 |
| Pengesahan input tidak sah | 2 |
| Had/kuota | 1 |
| Kebenaran ditolak | 1 |
| API endpoint | 2 |

### 19.3 Perangkap Ujian

| Perangkap | Pembetulan |
|---|---|
| `/** @test */` | Guna `#[Test]` (PHPUnit 11) |
| `#[\PHPUnit\...\Test]` | Syntax error PHP 8.2 — mesti `use` |
| `dalamTenant()` tanpa membership | `set($t, $membership)` untuk ujian kebenaran |
| Rekod selepas HTTP request | Konteks dikosongkan — cipta dahulu |
| `withoutGlobalScopes()` | Wajib untuk ujian pengasingan |
| Decimal sebagai string | `number_format((float)$x, 2, '.', '')` |

---

## 20. Penjadual & Queue

```php
// routes/console.php
Schedule::call(function () {
    foreach (Tenant::all() as $tenant) {
        app(TenantContext::class)->set($tenant);      // WAJIB setiap iterasi
        try {
            app(SomeService::class)->jalankan($tenant->id);
        } finally {
            app(TenantContext::class)->clear();
        }
    }
})->dailyAt('04:00');
```

**Jadual lazim:** `reminder-dispatch` (10m) · `content-publish` (5m) ·
`blast-dispatch` (1m) · `orders-reservation-cleanup` (jam) ·
`seo-audit-daily` (03:15) · `business-intel-daily` (04:00)

> ⚠️ **Queue worker mesti tetapkan konteks tenant dari DB, bukan dari payload.**
> Jangan percaya `tenant_id` dalam payload job — ia boleh dipalsukan.

---

## 21. Keselamatan & Pematuhan

### 21.1 Senarai Semak Keselamatan

- [ ] Semua model perniagaan guna `BelongsToTenant`
- [ ] Semua laluan ada `perm:` **dan** `module:` gate
- [ ] Tiada `Auth::user()->tenant_id` — guna `TenantContext::id()`
- [ ] PromptGuard di langkah 0 `GatewayService::call()`
- [ ] API key provider tidak pernah ke frontend/log
- [ ] Kredensial kamera/RTSP dienkripsi, tidak dipulangkan API
- [ ] Webhook: HMAC + replay protection + idempotency
- [ ] Path traversal disekat (worktree, muat naik fail)
- [ ] `composer audit` = 0 advisori
- [ ] Menu tersembunyi **bukan** kawalan keselamatan

### 21.2 Pematuhan Malaysia

| Domain | Kadar/Peraturan | Sumber |
|---|---|---|
| EPF pekerja | 11% | KWSP |
| EPF majikan | 13% (≤RM5k) / 12% (>RM5k) | KWSP |
| SOCSO | Berperingkat (max ~RM1,200/thn) | PERKESO |
| EIS | 0.2% (max RM100/thn) | PERKESO |
| HRDF | 1% (**tiada had**) | HRDF |
| PCB | Jadual bulanan | LHDN |
| Mileage | RM0.60/km | LHDN |
| e-Invois | Wajib berperingkat | LHDN |
| PDPA | Consent + DPO + retensi | Akta 709 |

> ⚠️ **Kadar berubah setiap tahun.** Sahkan dengan templat rasmi terkini.
> Nilai ini ialah **lalai sistem** — tenant mesti sahkan dengan penasihat cukai.

### 21.3 Pagar PDPA (Biometrik — 6 syarat)

Sebelum `enabled = true`:

```php
alternative_method      // rfid|pin|qr|manual WAJIB disediakan
dpo_name, dpo_email
privacy_notice
consent_version
retention_days          // 1–365
legal_review_date       // ≤ 12 bulan
```

Tarik consent → embedding dipadam, rekod kehadiran **kekal**.

---

## 22. Urutan Pembinaan — 12 Fasa

> **Jangan lompat fasa.** Setiap fasa bergantung pada yang sebelumnya.

### Fasa 1 — Asas (3–5 hari)
1. `composer create-project laravel/laravel`
2. Pasang Sanctum, Filament 3.3.53
3. Migration `users` + sambungan
4. Konfigurasi `.env` (SQLite)

**Siap bila:** `php artisan migrate` berjaya, `/admin` boleh dibuka.

### Fasa 2 — Tenancy (5–7 hari) ⭐ PALING KRITIKAL
1. Jadual `plans`, `tenants`, `tenant_users`, `permissions`, `roles`, pivot
2. `TenantContext`
3. Trait `BelongsToTenant`
4. `ResolveTenant` middleware
5. `TenantProvisioner`
6. `skrip/sync-perm.php`

**Siap bila:** ujian pengasingan tenant lulus (2 tenant, tiada kebocoran).

### Fasa 3 — RBAC & Entitlemen (3–4 hari)
1. `config/aibijan.php` (kebenaran + peranan)
2. `RequirePermission` (2 lapisan)
3. `ModuleGate` + `EntitlementService`
4. `tenant_modules`, `subscriptions`, `plan_limits`

**Siap bila:** tenant tanpa langganan ditolak 403.

### Fasa 4 — Domain Teras (10–15 hari)
Inventory · Manufacturing · Projects · Assets · Helpdesk · Invoicing · Finance · Payroll

**Siap bila:** setiap modul ada ujian pengasingan + cipta gembira.

### Fasa 5 — AI Gateway (5–7 hari) ⭐ KRITIKAL
1. `ai_model_catalog`, `ai_credit_wallets`, `ai_usage_events`
2. `PromptGuard` (12 + 4 corak)
3. `PiiRedactor`
4. `GatewayService::call()` — 8 langkah, guard di langkah 0
5. Mod simulasi

**Siap bila:** prompt injection disekat walaupun `AI_GATEWAY_LIVE=false`.

### Fasa 6 — Modul AI (7–10 hari)
AIAgents · PersonalAssistant · Boardroom · FlowAutomation

### Fasa 7 — Jualan & Pemasaran (10–12 hari)
CRM · Marketing · Loyalty · Affiliate · Link · Site

### Fasa 8 — Operasi Lanjutan (12–15 hari)
POS · Booking · Marketplace · Omnichannel · Logistics · Orders · Reminder

### Fasa 9 — Studio AI (8–10 hari)
AgentStudio · McpStudio · WorkflowStudio · ContentStudio · MediaStudio · MediaEditor

### Fasa 10 — Kecerdasan (6–8 hari)
BusinessIntel · Seo · Analytics · BrainHub · ProofOfValue · ExecutiveAI · ADE

### Fasa 11 — UI Filament (10–15 hari)
66 resource + 6 widget + halaman Tetapan

**Siap bila:** setiap halaman 200, jadual render, tiada `BindingResolutionException`.

### Fasa 12 — Pengukuhan (5–7 hari)
1. `composer audit` = 0
2. Semua ujian lulus
3. `sync-perm.php` pada DB produksi
4. Health endpoint `/health` `/ready` `/metrics`
5. Ujian staging

**Jumlah anggaran: 83–118 hari kerja (4–6 bulan) untuk satu jurutera berpengalaman.**

---

## 23. Senarai Semak Penerimaan

### 23.1 Struktur

- [ ] 58 modul didaftar dalam `bootstrap/providers.php`
- [ ] Setiap modul ada prefix sendiri — tiada `api/v1/{id}` wildcard
- [ ] 346 jadual dicipta
- [ ] Semua model perniagaan guna `BelongsToTenant`
- [ ] Model dengan nama jadual ≠ plural ada `$table`

### 23.2 Keselamatan

- [ ] Ujian pengasingan tenant lulus untuk setiap modul
- [ ] `perm:` + `module:` pada setiap laluan domain
- [ ] PromptGuard disekat dalam mod simulasi **juga**
- [ ] Tiada `auth()->user()->tenant_id` dalam kod
- [ ] `composer audit` = 0
- [ ] CSP: nonce dan `unsafe-inline` tidak bercampur

### 23.3 AI

- [ ] Semua panggilan AI melalui `GatewayService::call()`
- [ ] Wallet ditolak bila kredit habis
- [ ] Tindakan berisiko sentiasa perlu kelulusan
- [ ] Tiada kunci API dalam respons atau log

### 23.4 UI

- [ ] Setiap resource ada `getPages()` + 4 kelas `Pages/`
- [ ] Closure kolum guna `$record`
- [ ] Widget `$isLazy = false`
- [ ] `ResolveFilamentTenant` dalam kumpulan `web`
- [ ] Setiap halaman 200 (sahkan dalam pelayar sebenar)

### 23.5 Operasi

- [ ] Ujian penuh lulus, 0 gagal
- [ ] Queue worker tetapkan konteks dari DB
- [ ] Scheduler `clear()` dalam `finally`
- [ ] `/health` `/ready` `/metrics` berfungsi
- [ ] `sync-perm.php` dijalankan pada DB produksi

---

# LAMPIRAN

## Lampiran A — Perangkap Mengikut Kategori

### Migrasi

| Perangkap | Pembetulan |
|---|---|
| `UNIQUE(tenant_id, key)` dengan tenant NULL | NULL dianggap berbeza — semak di PHP |
| Kolum `error_message` tiada | Tambah SEBELUM fillable model |
| `timestamps()` hilang pada jadual yang model guna `HasFactory` | Tambah |
| Awalan jadual bertindih | Guna awalan modul |
| `tax_rate` bukan kolum | `unset($data['tax_rate'])` sebelum `create()` |

### Model

| Perangkap | Pembetulan |
|---|---|
| Cast `array` → `json_encode()` | Hantar array terus (double-encode) |
| `updateOrCreate` dengan kolum `date` | `whereDate()->first()` + create/update |
| `array_merge($defaults, $data)` dengan kunci bukan-kolum | `unset()` dahulu |
| Relation di-cache | Kira dari DB |
| `orderByDesc` atas relation yang ada `orderBy` | Guna `where()` bukan order |

### Laluan

| Perangkap | Pembetulan |
|---|---|
| Laluan statik selepas `{id}` | Statik dulu |
| Tiada prefix | `->prefix('api/v1/x')` |
| `use` dalam closure | Blok import atas |
| Regex pada fail laluan | Guna edit tool, bukan `-replace` |

### Filament

| Perangkap | Pembetulan |
|---|---|
| Tiada `getPages()` | Tambah |
| `use Filament\Resources\Pages` | Buang — resolve relatif |
| `fn ($s)` | `fn ($record)` |
| Widget lazy | `$isLazy = false` |
| `discoverWidgets()` + `widgets()` | `widgets()` sahaja |
| CSP nonce + unsafe-inline | `use_nonce = false` |
| `canCreate()` return true | Jangan takrif — memintas trait |

### AI

| Perangkap | Pembetulan |
|---|---|
| Guard selepas feature flag | Guard di langkah 0 |
| `logUsage()` dengan `$model = null` | `Log::warning` untuk blok awal |
| Binding tanpa PromptGuard | 2 argumen wajib |

---

## Lampiran B — Arahan Pengesahan

```powershell
# Struktur
(Get-ChildItem app\Modules -Directory).Count                    # 58
php artisan route:list --path=api/v1 | Measure-Object -Line     # 1,518
php artisan test                                                 # 962

# Laluan wildcard (mesti KOSONG)
php artisan route:list --path=api/v1 | Select-String "v1/\{"

# Keselamatan
composer audit

# Kesihatan
Invoke-WebRequest http://127.0.0.1:8000/health
Invoke-WebRequest http://127.0.0.1:8000/ready

# Dokumentasi (jika skrip wujud)
php skrip/semak-dokumen.php
```

---

## Lampiran C — Glosari

| Istilah | Maksud |
|---|---|
| **Tenant** | Satu organisasi pelanggan — data terasing sepenuhnya |
| **Keahlian** | Baris `tenant_users` — pengguna ∈ tenant |
| **Namespace** | Bahagian sebelum `.` dalam `perm:` |
| **Kunci modul** | Nilai dalam `plans.modules` — mungkin ≠ namespace (guna `ALIAS`) |
| **Entitlemen** | Modul yang dibenarkan oleh langganan |
| **Otonomi** | Tahap AI bertindak (0–4) |
| **Outbox** | Peristiwa disimpan dulu, dihantar kemudian |
| **Pagar** | Syarat wajib sebelum tindakan berisiko |
| **Worktree** | Salinan git terasing untuk satu subtugas |
| **Deny by default** | Tiada kebenaran → tolak |
| **Teras** | Namespace platform — tidak dikawal entitlemen |
| **Baseline** | Keputusan ujian asas sebelum perubahan |

---

**Akhir dokumen.**

Setiap angka, corak dan perangkap di sini diterbitkan daripada kod sebenar
AI BIJAN pada 26 Oktober 2026. Jika kod berubah, dokumen ini mesti dikemas kini.
