# AI BIJAN — P1-1: Audit Pengasingan Tenant Menyeluruh

> **Tarikh:** 27 Oktober 2026 · **Asas:** semakan kod langsung, bukan dokumen
> **Baseline:** commit `7a9d5e3` (tag `v9.5-p0`) · 58 modul (kini 59 selepas modul `Billing` ditambah 27 Okt 2026) · 346 jadual
> **Tujuan:** jawab satu soalan sahaja — **adakah setiap rekod tenant benar-benar terasing?**

---

## 0. Ringkasan Eksekutif

| Penemuan | Bilangan | Status |
|---|---|---|
| Laluan berganda (prefix dua kali) | **154** | ✅ Dibaiki (P0-3) |
| IDOR — `X-Tenant-ID` dari klien | **24 lokasi · 4 controller** | ✅ Dibaiki (P0-4) |
| Model tanpa `BelongsToTenant` | **60** | ⚠️ Dinilai di bawah |
| Model ada `tenant_id` **tanpa** skop | **8** | ⚠️ **3 berisiko sebenar** |
| Model tanpa `tenant_id` (jadual anak — betul) | **52** | ✅ Ikut konvensyen |
| Ujian pengasingan tenant | **13** (Finance sahaja) | ⚠️ Perlu 30+ |

**Kesimpulan:** sistem **tidak bocor** dalam kebanyakan modul kerana 3 lapisan
(skop global, auto-fill, halang ubah) berfungsi pada 232 model yang guna trait.
Tetapi **33 modul masih tiada ujian pengasingan** — jadi "tidak bocor" ialah
**andaian**, bukan **bukti**.

---

## 1. Seni Bina Pengasingan Yang Ada

```
Lapisan 1  Global scope    BelongsToTenant → where(tenant_id = konteks ?: 0)
Lapisan 2  Auto-fill       creating → tenant_id = konteks (tolak jika berbeza)
Lapisan 3  Halang ubah     updating → tolak jika tenant_id dirty
Lapisan 4  Konteks         TenantContext::set($tenant, $membership) + clear() finally
```

**232 model** guna trait ini. Ia berfungsi — `FinanceTenantIsolationTest` membuktikan
4 lapisan itu menahan bacaan, tulisan, `find()` dan pertukaran `tenant_id`.

**Tetapi:** lapisan 1–3 **hanya berjalan pada model yang guna trait**. Model tanpa
trait = **tiada skop langsung**, dan setiap query mesti ingat menulis
`where('tenant_id', …)` secara manual. Ini corak rapuh: satu query terlupa = bocor.

---

## 2. Lapan Model Bercanggah

Model ini **mempunyai kolum `tenant_id`** dalam DB tetapi **tidak** guna
`BelongsToTenant`. Disemak satu per satu:

| Model | Jadual | Cara query dalam kod | Penilaian |
|---|---|---|---|
| `ADE\Models\AdeSkill` | `ade_skills` | `where('tenant_id', $tenantId)` **eksplisit**; global skill = `tenant_id` NULL guna `whereNull` | ✅ Selamat — reka bentuk sengaja (skill global + salinan tenant) |
| `Community\Models\CommunitySpace` | `community_spaces` | `findOrFail($spaceId)` **tanpa skop** | 🔴 **BERISIKO** |
| `Community\Models\CommunityPost` | `community_posts` | `findOrFail($postId)` **tanpa skop** | 🔴 **BERISIKO** |
| `Community\Models\CommunityReply` | `community_replies` | `where('community_post_id', …)->findOrFail($replyId)` **tanpa skop** | 🔴 **BERISIKO** |
| `Entitlement\Models\EntitlementGrant` | `entitlement_grants` | `withoutGlobalScope('tenant')` — **tetapi skop itu tidak wujud** pada model | 🟡 Selamat secara tidak sengaja (tiada skop untuk dibuang), tetapi kod **mengelirukan** |
| `Entitlement\Models\UsageCounter` | `usage_counters` | `firstOrCreate` / `query()` **tanpa skop tenant** | 🟡 **BERISIKO** — kaunter boleh dikongsi/tertulis silang tenant |
| `MediaEditor\Models\MediaTemplate` | `media_templates` | `query()` **tanpa skop** | 🟡 Berisiko rendah (templat), tetap perlu skop |
| `Payroll\Models\StatutoryRate` | `statutory_rates` | `where('tenant_id', TenantContext::id())` **eksplisit** | ✅ Selamat — reka bentuk sengaja (kadar global boleh `tenant_id` NULL) |

### 2.1 Bukti `Community` — contoh sebenar

```php
// app/Modules/Community/Services/CommunityService.php
$pos = CommunityPost::findOrFail($postId);      // ← TIADA skop tenant
$pos = CommunityPost::findOrFail($postId);      // ← TIADA skop tenant
$ruang = CommunitySpace::findOrFail($spaceId);  // ← TIADA skop tenant

// app/Modules/Community/Services/... BalasService
$balasan = CommunityReply::where('community_post_id', $pos->id)
    ->findOrFail($replyId);                      // ← TIADA skop tenant
```

**Kesan:** jika pengguna tenant A mengetahui `post_id` (integer berjujukan),
`GET /api/v1/community/posts/{id}` boleh memulangkan siaran tenant B.
Tiada sekatan. Ini **IDOR melalui URL**, bukan header.

### 2.2 Kenapa ini belum ditangkap ujian

`tests/Feature/Community...` tiada. `Community` = 8 laluan, **0 ujian pengasingan**.
Begitu juga `Entitlement`, `MediaEditor` — tiada ujian pengasingan langsung.

---

## 3. Model Tanpa `tenant_id` — 52 jadual anak

Ini **betul** mengikut konvensyen projek (lihat `BLUEPRINT-SISTEM-LENGKAP.md` §5.3):

> *Jadual anak: Tiada `tenant_id`; diwarisi daripada induk.*

Contoh: `invoice_items`, `purchase_order_lines`, `project_activities`,
`vo_cards`, `leave_requests`, `payroll_run_details`, `quotation_lines`.

**Tetapi:** jadual anak **hanya selamat jika ibu bapa disemak dahulu.**

```php
// ❌ BOCOR — baris invois tanpa semak induk
InvoiceItem::where('invoice_id', $id)->get();

// ✅ BETUL — sahkan induk milik tenant dulu
$invois = Invoice::findOrFail($id);            // ← ada skop
$invois->items;                                 // ← selamat melalui relasi
```

**Pepijat ini sudah ditemui sekali:** `JournalEntryLine` (P0-5). Ia ada
`JournalEntryLine::where(...)` yang boleh baca baris semua tenant. Sudah
dibaiki dengan skop `tenant_via_induk`.

**Risiko:** 51 jadual anak lain mungkin ada corak sama. Ini perlu disemak
**satu per satu** — tidak boleh diandaikan.

---

## 4. Liputan Ujian Pengasingan (Jujur)

**Hanya 15 daripada 44 fail ujian** menyebut `withoutGlobalScopes`.
Dan hanya **1 fail** (`FinanceTenantIsolationTest` — baharu) menguji
pengasingan **secara sengaja** dengan 2 tenant sebenar.

| Berisiko tinggi (ada wang/data peribadi) | Ujian pengasingan |
|---|---|
| `Finance` (jurnal, COA) | ✅ 13 ujian baharu |
| `Invoicing` (invois, e-Invois) | ❌ **tiada** |
| `Payroll` (gaji, EPF/SOCSO/PCB) | ⚠️ 9 ujian, **tiada pengasingan** |
| `HRMS` (pekerja, biometrik) | ⚠️ 5 rujukan, tiada pengasingan sengaja |
| `CRM` (pelanggan, PII) | ⚠️ 17 rujukan, tiada pengasingan sengaja |
| `POS` (jualan, syif) | ⚠️ 3 rujukan |
| `Expense`, `ServiceOS`, `ADE`, `Orders`, `Seo`, `ContentStudio` | ✅ ada (daripada BAB 85–92) |

**Modul dengan 0 ujian langsung (30):**

```
Affiliate      AIAgents       Analytics      Assets        Booking
BrainHub       COGS           Community      DocumentAI    Entitlement
Finance        HRMS(separa)   IndustryPacks  Invoicing     Link
LiveSupport    Logistics      Loyalty        Marketing     Marketplace
MediaEditor    Omnichannel    PersonalAssistant  POS       Projects
SCM            SignDocs       Site           VirtualOffice
```

---

## 5. Keutamaan Kerja (P1)

### P1-1a — Betulkan 4 model berisiko (1–2 hari) ⭐ MULA SINI

| # | Model | Tindakan |
|---|---|---|
| 1 | `CommunitySpace` | Tambah `use BelongsToTenant` + ujian |
| 2 | `CommunityPost` | Tambah `use BelongsToTenant` + ujian |
| 3 | `CommunityReply` | Tambah `use BelongsToTenant` + ujian |
| 4 | `UsageCounter` | Tambah `use BelongsToTenant` + ujian |

⚠️ **Sebelum tambah trait:** semak setiap `create()` menghantar `tenant_id` dalam
`$fillable`, dan semak tiada query sengaja merentas tenant (cth laporan platform).
`EntitlementGrant` & `StatutoryRate` ada `withoutGlobalScope('tenant')` dalam kod —
jika trait ditambah, panggilan itu mula berfungsi dan **boleh mengubah tingkah laku**.

### P1-1b — Ujian pengasingan untuk 3 modul wang (2–3 hari)

`Invoicing`, `Payroll`, `HRMS` — kerana ia menyimpan data paling sensitif.
Ikut corak `FinanceTenantIsolationTest` (13 ujian).

### P1-1c — Audit 51 jadual anak (3–4 hari)

Cari corak `ChildModel::where(...)` tanpa semakan induk. Tambah skop
`*_via_induk` seperti `JournalEntryLine` selepas disahkan.

### P1-1d — Ujian pengasingan seluruh sistem (5–7 hari)

45 modul × 2 ujian minimum = 90 ujian baharu. Capai 1,066+ ujian.
Tambah ke CI: **gagal jika modul baharu tiada ujian pengasingan**.

---

## 6. Cara Sahkan Dokumen Ini

```powershell
# Senarai model tanpa trait yang ada kolum tenant_id
php skrip/audit-tenant.php          # (akan dicipta)

# Ujian pengasingan sahaja
php artisan test --filter="Isolation"

# Laluan berganda (mesti 0)
php artisan route:list --json | ConvertFrom-Json |
  Where-Object { $_.uri -match '^api/v1/([a-z-]+)/\1/' } | Measure-Object
```

---

## 7. Peraturan Baharu (wajib dalam `skrip/semak-dokumen.php`)

1. **Setiap model dengan `tenant_id` MESTI guna `BelongsToTenant`** — atau
   didaftarkan dalam `config/tenant-global-models.php` dengan justifikasi.
2. **Setiap modul (58) MESTI ada ujian pengasingan** — sekurang-kurangnya
   satu ujian 2-tenant yang membuktikan `find()` tidak menembusi skop.
3. **Jadual anak** mesti diakses melalui relasi induk, atau ada skop
   `*_via_induk`.

Jika salah satu gagal → **CI merah**.

---

## 8. Kesimpulan Jujur

Sistem ini **lebih selamat daripada yang didakwa dokumen** dalam satu perkara:
4 lapisan pengasingan memang wujud dan berfungsi (dibuktikan 13 ujian).

Tetapi ia **kurang selamat daripada yang diandaikan** dalam perkara lain:

- **4 model** dengan `tenant_id` tiada skop → IDOR melalui URL
- **51 jadual anak** belum diaudit corak aksesnya
- **33 modul** tiada ujian pengasingan → "tidak bocor" = andaian
- Dokumen lama mendakwa "semua model guna `BelongsToTenant`" — **tidak benar**
  (60 model tidak guna; 8 daripada itu ada `tenant_id`)

**Satu bug IDOR yang tidak diuji lebih berbahaya daripada seratus modul baharu.**
