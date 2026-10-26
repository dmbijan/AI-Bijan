---
name: aibijan-coding
description: 'Panduan menulis kod untuk projek AI BIJAN SaaS (Laravel 12, multi-tenant). USE FOR: menambah modul/controller/route/migration/model baharu; menulis ujian tenant isolation; menyahpepijat 401/403/404/422/500; menambah permission atau peranan; route conflict antara provider modul dan routes/api.php; payroll statutori; biometrik/consent; CCTV/Vision; AI gateway dan prompt safety; pangkalan pengetahuan/vector search; resource Filament. Triggers: modul, controller, route, migration, ujian, tenant, permission, isolasi, payroll, biometric, vision, gateway, embedding, filament.'
argument-hint: '[modul atau tugasan yang hendak dibina]'
---

# AI BIJAN SaaS — Panduan Kod

Panduan kerja untuk `c:\ai-bijan\AI-Bijan\ai-bijan-saas`. Ikut arahan ini **dahulu** sebelum menulis kod; ia mengelak pepijat yang sudah berulang kali berlaku dalam projek ini.

## Bila Guna

- Menambah modul, controller, route, migration, model atau ujian
- Menyahpepijat kegagalan API (401/403/404/422/500)
- Menyemak isolasi tenant atau kebenaran
- Menyentuh payroll statutori, biometrik, Vision/CCTV, AI gateway

## Fakta Asas Projek

| Perkara | Nilai |
|---|---|
| Backend | **Laravel 12.70.1**, **PHP 8.2.12** |
| DB dev | SQLite (`database/database.sqlite`) |
| Modul | `app/Modules/<Nama>/` (**58 modul**) |
| Migration | **GLOBAL** di `database/migrations/` — bukan dalam modul |
| Permission & peranan | `config/aibijan.php` (`permissions`, `roles`) — **212 kebenaran** |
| Ujian | `tests/Feature/`, trait `Tests\Support\AksesTenant` — **962 lulus** |
| Tenant context | `app(TenantContext::class)->id()` |
| Trait isolasi | `App\Models\Concerns\BelongsToTenant` |
| UI admin | **Filament 3.3.53** di `/admin` — **66 resource**, 6 widget |

> **Sahkan angka:** `php skrip/semak-dokumen.php` · Dokumen sistem: `docs/SISTEM-AI-BIJAN.md`

## 7 Peraturan Wajib

### 1. Jangan guna `Auth::user()->tenant_id`

Atribut itu **tidak wujud** pada `User`. Guna:

```php
app(\App\Support\Tenancy\TenantContext::class)->id()
```

### 2. Setiap model perniagaan mesti guna `BelongsToTenant`

```php
use App\Models\Concerns\BelongsToTenant;

class Item extends Model
{
    use BelongsToTenant;
}
```

**Pengecualian sah:** jadual anak yang tiada `tenant_id` (diwarisi melalui induk), contoh `invoice_items`, `purchase_order_lines`. **Jangan** tambah trait jika jadual tiada kolum `tenant_id` — ia akan menyebabkan SQL error.

### 3. Jangan guna `Set-Content -NoNewline` atau regex `-replace` pada `routes/api.php`

Ini pernah memusnahkan 765 baris dalam satu operasi. Guna `replace_string_in_file`, atau skrip PHP yang baca/tulis baris demi baris dengan sandaran `Copy-Item` dahulu.

### 4. Route provider modul didaftar SEBELUM `routes/api.php`

Provider modul **sentiasa menang** jika URI bertindih. Oleh itu:

- Beri setiap provider prefix modul sendiri
- Jangan daftar URI akar seperti `Route::prefix('api/v1')` tanpa segmen modul
- Semak selepas perubahan:

```powershell
php artisan route:list --path=api/v1 | Select-String "v1/\{"
```

Kosong = bagus. Jika ada `api/v1/{id}`, ada provider yang mencipta wildcard.

### 5. Ujian servis terus perlu `dalamTenant()`

Trait `BelongsToTenant` menolak penciptaan rekod tanpa tenant aktif. Sebelum memanggil model/servis secara langsung dalam ujian:

```php
$this->dalamTenant($this->t);
```

Panggilan melalui HTTP (`sebagai()`) tidak perlu ini.

### 6. Model dengan cast `'array'` — hantar array

Eloquent `$casts = ['data' => 'array']` bermaksud hantar **array**, bukan `json_encode(...)`. Double-encode merosakkan data tanpa ralat jelas.

### 7. Jangan bergantung pada prompt untuk kawalan akses

Pagar keselamatan mesti dalam kod: middleware, policy, `abort_unless()`. `PromptGuard` ialah lapisan pertama untuk input AI sahaja — bukan pengganti permission check.

## Aliran Kerja Menambah Modul

```
1. Migration (global)  →  database/migrations/2026_MM_DD_NNNNNN_create_<x>.php
2. Model + trait       →  app/Modules/<Nama>/Models/
3. Servis              →  app/Modules/<Nama>/Services/
4. Controller          →  app/Modules/<Nama>/Http/Controllers/   (extends App\Http\Controllers\Controller)
5. Permission          →  config/aibijan.php
6. Route               →  routes/api.php dalam blok tenant, prefix modul
7. Ujian               →  tests/Feature/<Nama>Test.php
8. Jalankan            →  php artisan test --filter=<Nama>Test
```

Rujuk [prosedur penuh](./references/module-procedure.md) untuk templat setiap langkah.

## Realiti Produk (BACA SEBELUM CADANG CIRI)

**Backend gred produksi (970 endpoint, 220 ujian) tetapi UI gred demo (34 view × 5.5 KB).**

Keutamaan #1 ialah **siapkan POS sebenar**, bukan tambah modul. Jangan cadang ciri
baharu tanpa semak [product-strategy.md](./references/product-strategy.md),
[competitive-advantage.md](./references/competitive-advantage.md) dan
`docs/ANALISIS-KOMPETITIF.md`.

**Jangan tandakan ✅ untuk ciri yang belum dikodkan.** Rujuk status sebenar
9 kelebihan dalam [competitive-advantage.md](./references/competitive-advantage.md).

## Menyahpepijat Pantas

| Gejala | Punca biasa | Semakan |
|---|---|---|
| `401` | Tiada sesi; `sebagai()` belum dipanggil | Panggil `$this->sebagai($user, $tenant)` dulu |
| `403` | Peranan tiada permission | Semak `config/aibijan.php` |
| `404` pada route yang wujud | Provider modul lain menang | `php artisan route:list --path=...` |
| `404` selepas cipta rekod | Tenant scope menapisnya | Cari guna `TenantContext`, bukan `tenant_id` |
| `422` tidak dijangka | Medan wajib tiada | Lihat `errors` dalam respons |
| `500` + `no column named X` | Controller hantar medan tiada dalam skema | Semak migration |
| `MassAssignmentException` | Medan tiada dalam `$fillable` | Tambah atau `unset()` sebelum create |
| `Rekod tenant tidak boleh dicipta` | Tiada context | `$this->dalamTenant($t)` |

Rujuk [senarai penuh pepijat](./references/gotchas.md) — semua ini pernah berlaku.

## Rujukan

| Fail | Kandungan |
|---|---|
| [module-procedure.md](./references/module-procedure.md) | Templat migration, model, servis, controller, route, ujian |
| [gotchas.md](./references/gotchas.md) | Pepijat sebenar dengan punca dan pembaikan |
| [security-rules.md](./references/security-rules.md) | Isolasi tenant, biometrik, AI safety, audit |
| [product-strategy.md](./references/product-strategy.md) | Keutamaan produk, kesilapan mesti dielak, metrik |
| [competitive-advantage.md](./references/competitive-advantage.md) | 9 kelebihan, skor pesaing, 15 idea, harga disahkan |

## Semakan Sebelum Selesai

```
[ ] Tiada Auth::user()->tenant_id
[ ] Model perniagaan guna BelongsToTenant (atau jadual anak disahkan tiada tenant_id)
[ ] Route berprefix modul, tiada wildcard api/v1/{id}
[ ] Permission ditambah dalam config/aibijan.php jika endpoint baharu
[ ] Ujian: CRUD, isolasi tenant, RBAC, kes tepi
[ ] php artisan test --filter=<Suite> lulus
[ ] php artisan test (penuh) tiada regresi
[ ] Tiada kredensial dalam respons API
```
