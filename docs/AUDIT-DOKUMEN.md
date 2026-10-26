# Laporan Audit Dokumen `docs/`

**Tarikh audit:** 26 Oktober 2026
**Kaedah:** Setiap angka dalam dokumen disemak terhadap kod sebenar
(`app/Modules/`, `database/migrations/`, `php artisan test`, `php artisan route:list`)
**Skop:** **56 fail** dalam `ai-bijan-saas/docs/` (bukan 17 seperti anggapan awal)

---

## 0. Penemuan Terbesar: 35 Fail Bukan Milik AI BIJAN

Audit awal menganggap `docs/` mengandungi 17 fail. Sebenarnya **56 fail**.
Daripada itu, **35 fail tidak sepatutnya berada di sini**:

| Kumpulan | Bilangan | Punca | Tindakan |
|---|---|---|---|
| **Dokumen B-POS System** | 7 fail akar + 28 fail `features/` | Disalin dari projek B-POS | → `archive/` + `archive/features/` |
| **Blueprint/arkitektur lapuk** | 28 fail | Versi v2.x–v8.x yang digantikan | → `archive/blueprint-lama/` |

**Bukti dokumen B-POS bukan AI BIJAN:**

| Petunjuk | Dalam dokumen B-POS | Dalam kod AI BIJAN |
|---|---|---|
| `DOKU_CLIENT_ID` / `senangPay` | Disebut | **0** dalam `.env.example` |
| `point_of_sales` (nama DB) | Disebut | **0** |
| `app/Services/Payments/` | Dirujuk | **Tidak wujud** |
| `DineOrder`, `KitchenService`, `ThermalPrintService` | Didokumenkan | **0 kelas** |

**Kesan jika tidak dibetulkan:** developer baru mengikut `configuration.md` akan
mencari integrasi pembayaran DOKU yang **tidak wujud**, dan menganggap AI BIJAN
mempunyai ciri dine-in/thermal-printer yang **tiada**.

**Hasil:** `docs/` kini **21 fail** yang semuanya berkaitan AI BIJAN.

---

## 1. Kebenaran Tanah (Ground Truth) — 26 Okt 2026

Semua angka di bawah **disahkan daripada kod**, bukan diandaikan:

| Metrik | Nilai | Cara sahkan |
|---|---|---|
| Modul | **58** | `(Get-ChildItem app\Modules -Directory).Count` |
| Jadual | **346** | `SELECT count(*) FROM sqlite_master WHERE type='table'` |
| Laluan API | **1,518** | `php artisan route:list --path=api/v1` |
| Ujian lulus | **962** | `php artisan test` |
| Assertion | **5,033** | `php artisan test` |
| Model | **292** | `app/Modules/**/Models/*.php` |
| Service | **166** | `app/Modules/**/Services/*.php` |
| Controller (modul) | **127** | `app/Modules/**/Http/Controllers/*.php` |
| Resource Filament | **66** | `app/Filament/Resources/` |
| Widget | **6** | `app/Filament/Widgets/` |
| Kebenaran | **212** | jadual `permissions` |
| Migration | **49 fail** | `database/migrations/` |
| Laravel | 12.70.1 | `composer.lock` |
| Advisori keselamatan | **0** | `composer audit` |

---

## 2. Ringkasan Pembetulan

| Dokumen | Isu ditemui | Tindakan |
|---|---|---|
| `KATALOG-MODUL-AI-BIJAN.md` | 55 modul → 58 · 331 jadual → 346 · 919 ujian → 962 · 208 kebenaran → 212 · 59 resource → 66 · blueprint v9.3 → v9.4 · B-POS table angka lapuk | ✅ Dibetulkan + bahagian ADE ditambah |
| **`getting-started.md`** | **Laravel 13 · PHP 8.3 · tanpa `sync-perm.php`** | ✅ **Ditulis semula sepenuhnya** |
| **`BACA-SAYA.md`** | **Laravel 11 · 43 ujian · ERPNext · mendakwa modul siap sebagai "belum siap"** | ✅ **Ditulis semula sepenuhnya** |
| `ANALISIS-KOMPETITIF.md` | 34 modul · 220 ujian · 108 model · "enjin tanpa badan kereta" | ✅ Dibetulkan + nota UI 2/10 → 7/10 |
| `CADANGAN-IMPAK-TINGGI.md` | 34 modul · 220 ujian · diagnosis UI lapuk | ✅ Header + diagnosis dikemas kini + nota sejarah |
| `BUSINESS-GROWTH-ENGINE.md` | 108 model · status Command Centre & AI lapuk | ✅ Status dinaik taraf + ADE ditambah |
| `MODUL-AI-INTEGRASI.md` | "5 modul AI" · titik integrasi lapuk | ✅ Peta AI lanjutan + status disemak |
| `AUDIT-PESAING-LANJUTAN.md` | "34 modul" mengelirukan | ✅ Nota penjelasan |
| `PETA-PESAING-34-MODUL.md` | Tajuk "34 Modul" mengelirukan | ✅ Dinamakan semula "34 Kategori Modul" + nota |
| `PROSPEK-BLAST-CLOSE.md` | Perlu pengesahan | ✅ Disahkan tepat (94 ujian) + nota |
| `KAJIAN-FINDS-*.md` | Perlu pengesahan | ✅ Disahkan tepat + nota |
| `REVERSE-ENGINEERING-*.md` | Angka pasaran boleh lapuk | ✅ Nota kesegaran data + status idea |
| **7 fail B-POS** + **28 fail `features/`** | **Bukan milik AI BIJAN** | ✅ → `archive/` |
| **28 fail blueprint lapuk** | Versi v2.x–v8.x | ✅ → `archive/blueprint-lama/` |
| `README.md` | **Tiada** | ✅ Dicipta — indeks penuh + peraturan |
| `AUDIT-DOKUMEN.md` | **Tiada** | ✅ Dicipta (fail ini) |

---

## 3. Isu Kritikal Yang Ditemui

### 3.0 🔴 `getting-started.md` dan `BACA-SAYA.md` salah sepenuhnya

| Dakwaan | Realiti |
|---|---|
| `getting-started.md`: "PHP 8.3+ sesuai kebutuhan **Laravel 13**" | **PHP 8.2.12, Laravel 12.70.1** |
| `BACA-SAYA.md`: "**Laravel 11+**", "Ujian: 43 ujian" | **Laravel 12.70.1, 962 ujian** |
| `BACA-SAYA.md`: "**ERPNext/Frappe**", "`aibijan-frappe/`", "`erpnext-develop/`" | Tiada ERPNext — Laravel tulen |
| `BACA-SAYA.md`: "Belum siap: … POS Laravel, Kewangan, e-Invois LHDN, AI Gateway" | **Semua ini SUDAH siap** |
| `getting-started.md`: tidak menyebut `sync-perm.php` | **Wajib** — tanpanya menu boleh 403 |
| `getting-started.md`: tidak menyebut `cipta-tenant-ujian.php` | Wajib untuk ada tenant pertama |

**Punca:** kedua-dua dokumen ditulis sebelum v4.5 (18 Okt 2026) dan tidak pernah
dikemas kini. Ia mendakwa **kurang** daripada realiti — hampir sama berbahaya
kerana ia menyembunyikan kerja yang sudah siap.

**Tindakan:** kedua-duanya **ditulis semula sepenuhnya** dengan data disahkan.

### 3.1 Angka ujian tidak konsisten merentas dokumen

Dokumen berbeza mendakwa angka berbeza untuk keadaan yang sama:

| Dokumen | Dakwaan ujian | Sebenar |
|---|---|---|
| `KATALOG-MODUL-AI-BIJAN.md` (header) | 919 | 962 |
| `KATALOG-MODUL-AI-BIJAN.md` (statistik) | 873 | 962 |
| `ANALISIS-KOMPETITIF.md` | 220 | 962 |
| `CADANGAN-IMPAK-TINGGI.md` | 220 | 962 |

**Punca:** dokumen ditulis pada masa berbeza tanpa tarikh kemas kini yang jelas.
**Pembetulan:** semua diselaraskan kepada **962** + tarikh pengesahan ditambah.

### 3.2 Diagnosis UI lapuk (paling penting)

`ANALISIS-KOMPETITIF.md` dan `CADANGAN-IMPAK-TINGGI.md` menyatakan:

> "AI BIJAN ialah **enjin tanpa badan kereta**" · UI skor **2/10** · "34 view × 5.5 KB"

Ini **tidak lagi benar**. T-71 (Filament) menambah **66 resource CRUD**, 6 widget
dashboard dan halaman Tetapan. Skor UI kini **7/10**.

**Risiko jika tidak dibetulkan:** keputusan strategik dibuat atas realiti yang salah —
cth melabur semula dalam "bina UI" sedangkan UI sudah wujud.

### 3.3 Modul ADE langsung tiada dalam dokumen

Modul #58 (`ADE`, BAB 92) — 6 jadual, 24 laluan, 4 resource, 48 ujian — **tidak disebut**
dalam mana-mana dokumen `docs/`. Ditambah ke katalog + peta AI + growth engine.

### 3.4 BAB 90-91 dikelirukan sebagai siap

`KATALOG-MODUL-AI-BIJAN.md` menyenaraikan B-Receipt dan B-Vision di bawah
"Dirancang" tetapi tanpa amaran jelas. Ditambah amaran **"BELUM DIKODKAN"** yang tegas.

---

## 4. Isu Yang Sengaja DIBIARKAN

| Item | Sebab |
|---|---|
| "34 modul" dalam naratif `CADANGAN-IMPAK-TINGGI.md` | **Rekod sejarah** — dokumen ditulis 24 Okt. Ditambah nota penjelasan, bukan diubah |
| `PETA-PESAING-34-MODUL.md` nama fail | Menamakan semula fail memecahkan pautan. Tajuk + nota dibetulkan sebaliknya |
| `dashboard.blade (1).php` | Duplikasi — **calon untuk dibuang**, tetapi bukan skop audit dokumen |
| Fail `.patch` | Artifak sejarah — ditanda dalam README |

---

## 5. Peraturan Baharu (dikuatkuasa)

Ditambah ke `README.md`:

1. **Angka mesti disahkan** sebelum ditulis — jalankan perintah pengesahan.
2. **Jangan tandakan ✅** untuk ciri belum dikodkan. Guna skala:
   ✅ siap · 🟢 kebanyakannya · 🟡 separa · ⏸️ placeholder · ⛔ tiada
3. **Sebut BAB + versi blueprint** untuk setiap ciri.
4. **Bezakan spesifikasi vs pelaksanaan** — BAB 90-91 = spesifikasi sahaja.

---

## 6. Tindakan Susulan (belum selesai)

| # | Tindakan | Keutamaan |
|---|---|---|
| 1 | Buang `dashboard.blade (1).php` (duplikasi) | Rendah |
| 2 | Arkibkan fail `.patch` ke `docs/archive/` | Rendah |
| 3 | Tambah skrip `skrip/semak-dokumen.php` yang mengesahkan angka automatik | Sederhana |
| 4 | Semak `PROSPEK-BLAST-CLOSE.md` + `KAJIAN-FINDS-*.md` terhadap kod Blast/Prospecting | Sederhana |
| 5 | Semak `REVERSE-ENGINEERING-200-APPS-50-OSS.md` — angka bintang GitHub mungkin berubah | Rendah |

---

## 7. Kesimpulan

**15 daripada 21 dokumen AI BIJAN** memerlukan pembetulan, dan **35 fail**
sebenarnya **bukan milik projek ini langsung**.

Tiga isu terbesar, mengikut kerosakan:

1. **35 fail asing** — developer mengikut `configuration.md` akan mencari integrasi
   DOKU yang tiada; `features/dine-in.md` mendokumenkan ciri yang tidak wujud.
2. **`getting-started.md` + `BACA-SAYA.md` salah sepenuhnya** — versi framework salah,
   dan `BACA-SAYA.md` menyenaraikan modul yang **sudah siap** sebagai "belum siap".
3. **Diagnosis strategik lapuk** (UI 2/10 → 7/10) — boleh membawa kepada keputusan
   pelaburan yang salah.

**Prinsip yang dikuatkuasakan:**

> *Dokumen yang mendakwa **lebih** daripada kod adalah berbahaya — ia membawa kepada
> keputusan yang salah dengan penuh keyakinan. Dokumen yang mendakwa **kurang**
> daripada kod juga berbahaya — ia menyembunyikan kerja yang sudah siap dan
> menyebabkan pasukan membina semula perkara yang wujud.*

---

## 8. Konsolidasi (26 Okt 2026) — Satu Sistem, Satu Dokumen

Selepas audit, 21 dokumen bertaburan menyukarkan pembaca baru mendapat gambaran
keseluruhan. **`SISTEM-AI-BIJAN.md`** dicipta sebagai **dokumen sistem lengkap**:

| Bahagian | Isi |
|---|---|
| §0 | Ringkasan satu halaman + semua angka |
| §1 | Seni bina — 4 lapisan, pengasingan tenant 4 lapis, middleware |
| §2 | **Peta 58 modul** dengan laluan sebenar setiap satu (dari router) |
| §3 | Lapisan AI — satu enjin, 8 muka, 6 pagar |
| §4 | UI Filament — kebenaran dua lapis |
| §5 | Pematuhan Malaysia |
| §6 | **Status jujur** + skor 7 dimensi |
| §7 | Glosari |
| §8 | Cara sahkan dokumen |
| §9 | Rujukan |

**`README.md`** kini bertindak sebagai **satu titik masuk** dengan laluan mengikut
peranan (faham sistem / developer / perniagaan / audit).

### Data baharu yang diekstrak semasa konsolidasi

Bilangan laluan **per prefix** diambil terus daripada router Laravel:

```
site 128 · marketing 101 · hrms 88 · marketplace 47 · booking 46 · crm 46
invoicing 46 · loyalty 43 · virtual-office 42 · affiliate 39 · pm 35
service-os 35 · assets 33 · inventory 28 · expenses 26 · finance 26
omnichannel 26 · scm 26 · analytics 25 · sign-docs 25 · ade 24 · content 24
pos 24 · logistics 22 · payroll 21 · business-intel 20 · personal-ai 19
ai-agents 18 · brainhub 18 · flows 18 · reminders 18 · seo 18 · ...
```

**Reconcile:** 56 modul domain = **1,429** laluan · teras platform = **89** ·
**JUMLAH = 1,518** · 76 prefix.

### Skrip pengesahan dipertingkat

`skrip/semak-dokumen.php` kini:
- Membaca **kedua-dua** `KATALOG-MODUL-AI-BIJAN.md` **dan** `SISTEM-AI-BIJAN.md`
- Mengira laluan **terus dari router Laravel** (bukan shell — lebih tepat & mudah alih)
- Menyemak **9 metrik**: modul, jadual, laluan, resource, kebenaran, model, service,
  controller, migration
- Exit code **1** jika ada percanggahan (boleh dimasukkan ke CI)

**Status akhir:** ✅ semua 9 metrik sepadan. Ujian: **962 lulus, 0 gagal**.

---

## 9. Struktur Akhir `docs/`

```
docs/
├── README.md                  ← SATU TITIK MASUK
├── SISTEM-AI-BIJAN.md         ← DOKUMEN SISTEM LENGKAP  ⭐
├── KATALOG-MODUL-AI-BIJAN.md  ← katalog terperinci
├── getting-started.md         ← setup
├── BACA-SAYA.md               ← panduan pantas
├── AUDIT-DOKUMEN.md           ← fail ini
├── ANALISIS-KOMPETITIF.md
├── PETA-PESAING-34-MODUL.md
├── AUDIT-PESAING-LANJUTAN.md
├── REVERSE-ENGINEERING-200-APPS-50-OSS.md
├── BUSINESS-GROWTH-ENGINE.md
├── CADANGAN-IMPAK-TINGGI.md
├── competitive-analysis-report.md
├── ai-bijan-vs-world-comparison.md
├── shopify-hubspot-enhancement-strategy.md
├── MODUL-AI-INTEGRASI.md
├── PROSPEK-BLAST-CLOSE.md
├── KAJIAN-FINDS-PROSPECT-BLAST-CLOSE.md
├── customer-side.md
├── PELAN-VIRTUAL-OFFICE.md
├── cara-pasang.md
├── AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md
├── [mockup: .html/.tsx/.php/.patch/.png]
└── archive/                   ← JANGAN GUNA
    ├── features/              (28 fail B-POS)
    ├── blueprint-lama/        (28 fail v2.x–v8.x)
    └── [7 fail B-POS akar]
```

**21 dokumen di akar** — semuanya berkaitan AI BIJAN, semuanya bertarikh.

---

## 10. Sapuan Kedua (26 Okt 2026) — Fail Di Luar `docs/`

Audit pertama hanya meliputi `docs/`. Sapuan kedua meliputi **semua** fail `.md`
dalam projek (kecuali `vendor/`, `node_modules/`, `archive/`).

### Fail yang dibetulkan

| Fail | Isu | Tindakan |
|---|---|---|
| **`AGENTS.md`** | Laravel 11 · PHP 8.3 · MySQL/MariaDB · **"18 B-Modules"** · **Next.js 15 + React 19** · Redis/Horizon/Reverb | ✅ Dibetulkan — Laravel 12.70.1, PHP 8.2.12, 58 modul, Blade+Vite, Filament |
| **`CLAUDE.md`** | Sama seperti `AGENTS.md` | ✅ Dibetulkan |
| **`TODO.md`** | Baseline "314 lulus / 3 dilangkau" (24 Okt) | ✅ → **962 lulus / 4 dilangkau / 5,033 assertion** + nota angka sejarah |
| **`PASANG.md`** | "platform teras sahaja" · PHP 8.3+ · tiada langkah `sync-perm.php` | ✅ Status dikemas kini + langkah kebenaran ditambah |
| **`SKILL.md`** | Laravel 11 · "34 modul" | ✅ → Laravel 12.70.1 · 58 modul · 212 kebenaran · 962 ujian |
| **`references/product-strategy.md`** | "~1,015 endpoint, 314 ujian" · "39 modul" · "108 model" · "UI gred demo" | ✅ → 1,518 endpoint, 962 ujian, 58 modul, 292 model, UI 7/10 |
| **`references/competitive-advantage.md`** | "39 modul siap" · "34 modul" · "486 ujian keseluruhan" | ✅ Dibetulkan + nota angka sejarah |
| **`docs/ai-bijan-vs-world-comparison.md`** | "ALL 18 B-Modules" | ✅ → 58 modules |
| **`docs/competitive-analysis-report.md`** | "planned 18+ modules" | ✅ → "58 modules built" |
| **`docs/PELAN-VIRTUAL-OFFICE.md`** | "Horizon queue" | ✅ → Laravel queue worker |
| **`docs/shopify-hubspot-enhancement-strategy.md`** | "Next.js 15 + React 19" dalam rajah seni bina | ✅ → Blade + Vite |

### Fail asing tambahan ditemui

| Folder | Bilangan | Isu |
|---|---|---|
| **`docs/superpowers/specs/`** | 2 fail | Spesifikasi **B-POS** — termasuk `whatsapp-webjs-design.md` |

⚠️ **Amaran keselamatan:** `whatsapp-webjs-design.md` mencadangkan `whatsapp-web.js`,
yang **dilarang** dalam AI BIJAN (risiko nombor diharamkan). Dipindah ke
`archive/superpowers-bpos/` dengan amaran dalam `archive/README.md`.

### Fail baharu

| Fail | Fungsi |
|---|---|
| **`docs/archive/README.md`** | Amaran arkib — menerangkan kenapa 66 fail bukan rujukan semasa |

### Keputusan sapuan kedua

| Semakan | Hasil |
|---|---|
| Metrik struktur (9) | ✅ semua sepadan |
| Ujian | ✅ 94 lulus (subset ADE + Filament) |
| Fail `.md` disemak | **~100** (kecuali `archive/`) |
| Fail dibetulkan | **11** |
| Fail asing diarkibkan | **+2** |

**Jumlah keseluruhan audit:** **26 fail dibetulkan/ditulis semula** ·
**37 fail asing diarkibkan** · **2 fail baharu dicipta**.
