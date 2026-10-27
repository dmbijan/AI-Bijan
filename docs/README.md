# AI BIJAN — Dokumentasi Sistem

> **AI-Native Business Operating System** · Blueprint **v9.6** · 27 Oktober 2026
> **59 modul dikodkan (termasuk `Billing` — reseller komisen berulang) · 58 migration · ~872 endpoint `api/v1` · ~1,119 kaedah ujian · 65 resource Filament**
>
> 📊 **Semua angka rasmi:** [`AUDIT-BASELINE.md`](AUDIT-BASELINE.md) — satu-satunya
> sumber kebenaran. Jangan salin angka dari mana-mana dokumen lain.

---

## ▶ Mula Di Sini

### **[`AUDIT-BASELINE.md`](AUDIT-BASELINE.md)** — Sumber Kebenaran Angka ⭐⭐

**Baca ini dahulu jika anda akan membuat dakwaan tentang saiz platform.**
Dijana automatik daripada kod (`php skrip/jana-baseline.php --tulis`), disemak
oleh CI, dan mengandungi senarai penuh angka lama yang telah dibetulkan.

---

### **[`SISTEM-PEMBANGUNAN-DARI-MULA.md`](SISTEM-PEMBANGUNAN-DARI-MULA.md)** — Satu Dokumen, Dari Mula ⭐⭐

**Untuk BINA, KENDALIKAN dan JUAL AI BIJAN** — satu dokumen tanpa perlu melompat
antara 20 fail lain.

| Bahagian | Isi |
|---|---|
| §1–§4 | Ringkasan sistem · keperluan & pemasangan · **10 prinsip wajib** · seni bina 4 lapisan |
| §5–§9 | **Teras 5 lapisan** — skema platform · pengasingan tenant (kod penuh) · RBAC & entitlemen · middleware · audit & outbox |
| §10–§12 | Anatomi modul (templat wajib) · **peta 59 modul** · 7 corak domain berulang |
| §13–§14 | AI Gateway **8 langkah** · PromptGuard/PII/wallet · otonomi 0–4 & pagar |
| §15–§18 | Filament **7 peraturan** · ujian · penjadual & queue · keselamatan & PDPA |
| §19–§20 | Pematuhan Malaysia · **corak jualan, harga pesaing, Reseller** |
| §21–§26 | **12 fasa pembinaan** · skrip · senarai semak penerimaan · **status jujur** · perangkap ikut kategori · arahan pengesahan |
| §27 | Glosari + susunan bacaan mengikut peranan |

27 seksyen · semua angka disahkan `php skrip/semak-dokumen.php` + `php artisan test`

### **[`BLUEPRINT-SISTEM-LENGKAP.md`](BLUEPRINT-SISTEM-LENGKAP.md)** — Reverse Engineering ⭐

**Untuk BINA SEMULA AI BIJAN dari sifar.** Tiada rujukan luaran diperlukan.

| Bahagian | Isi |
|---|---|
| **I — Asas** | Skop sebenar · keperluan teknikal · **6 prinsip wajib** |
| **II — Teras** | Skema 13 jadual platform · **pengasingan tenant 4 lapis (kod penuh)** · RBAC · `ModuleGate` + alias · middleware · audit |
| **III — Modul** | **Templat modul wajib** · **59 modul + awalan jadual** · 7 corak domain |
| **IV — AI** | `GatewayService::call()` **8 langkah** · PromptGuard · wallet · otonomi 0–4 |
| **V — UI** | **7 peraturan Filament** wajib |
| **VI — Operasi** | Ujian · queue · keselamatan · PDPA · **12 fasa pembinaan** · senarai semak |
| **Lampiran** | Perangkap ikut kategori · arahan pengesahan · glosari |

23 bab · kod penuh `TenantContext`, `BelongsToTenant`, `ResolveTenant`,
`RequirePermission`, `ModuleGate`, `GatewayService` · anggaran **83–118 hari kerja**

### **[`SISTEM-AI-BIJAN.md`](SISTEM-AI-BIJAN.md)** — Dokumen Sistem Lengkap

**Satu dokumen. Semua yang perlu tahu tentang sistem.**

| Bahagian | Isi |
|---|---|
| §0 | Ringkasan satu halaman + semua angka |
| §1 | Seni bina teras — 4 lapisan, pengasingan tenant, middleware |
| §2 | **Peta 59 modul** dengan laluan sebenar setiap satu |
| §3 | Lapisan AI — satu enjin, lapan muka, enam pagar |
| §4 | UI Filament — 66 resource, kebenaran dua lapis |
| §5 | Pematuhan Malaysia |
| §6 | **Status jujur** — apa siap, apa belum, skor |
| §7 | Glosari |
| §8 | Cara sahkan dokumen ini |
| §9 | Rujukan |

> **Faham sistem** → `SISTEM-AI-BIJAN.md` · **Bina sistem** → `BLUEPRINT-SISTEM-LENGKAP.md`

> 💡 **Mahu satu fail sahaja?** → [`SISTEM-PEMBANGUNAN-DARI-MULA.md`](SISTEM-PEMBANGUNAN-DARI-MULA.md)
> menggabungkan ketiga-tiga dokumen di atas **dan** corak jualan/harga dalam 27 seksyen.

---

## Ikut Peranan Anda

### 👤 Saya ingin faham sistem ini
1. [`SISTEM-AI-BIJAN.md`](SISTEM-AI-BIJAN.md) §0 → §2
2. [`BACA-SAYA.md`](BACA-SAYA.md) — panduan pantas

### 💻 Saya developer baru
1. [`getting-started.md`](getting-started.md) — setup → log masuk
2. [`SISTEM-AI-BIJAN.md`](SISTEM-AI-BIJAN.md) §1 — seni bina
3. [`MODUL-AI-INTEGRASI.md`](MODUL-AI-INTEGRASI.md) — jika kerja dengan AI
4. `../.github/skills/aibijan-coding/references/gotchas.md` — pepijat sebenar

### 🏗️ Saya nak BINA sistem ini dari sifar
1. **[`BLUEPRINT-SISTEM-LENGKAP.md`](BLUEPRINT-SISTEM-LENGKAP.md)** §22 — 12 fasa pembinaan
2. [`BLUEPRINT-SISTEM-LENGKAP.md`](BLUEPRINT-SISTEM-LENGKAP.md) §4–§9 — teras (WAJIB dahulu)
3. [`BLUEPRINT-SISTEM-LENGKAP.md`](BLUEPRINT-SISTEM-LENGKAP.md) §10 — templat modul
4. [`BLUEPRINT-SISTEM-LENGKAP.md`](BLUEPRINT-SISTEM-LENGKAP.md) Lampiran A — perangkap

> ⚠️ **Jangan lompat ke modul sebelum Bahagian II teras siap.** Kesilapan
di situ tidak boleh dibaiki kemudian tanpa menulis semula setiap modul.

### 📊 Saya buat keputusan perniagaan
1. [`SISTEM-AI-BIJAN.md`](SISTEM-AI-BIJAN.md) §6 — status jujur & skor
2. [`ANALISIS-KOMPETITIF.md`](ANALISIS-KOMPETITIF.md) — pesaing & harga
3. [`BUSINESS-GROWTH-ENGINE.md`](BUSINESS-GROWTH-ENGINE.md) — 9 kelebihan
4. [`CADANGAN-IMPAK-TINGGI.md`](CADANGAN-IMPAK-TINGGI.md) — keutamaan
5. [`MODEL-HASIL-TOKEN.md`](MODEL-HASIL-TOKEN.md) — model hasil: langganan + token AI wajib
6. [`RESELLER-KOMISEN-BERULANG.md`](RESELLER-KOMISEN-BERULANG.md) — model jualan berulang

### 🔍 Saya nak tahu apa yang sudah/belum dibina
1. [`SISTEM-AI-BIJAN.md`](SISTEM-AI-BIJAN.md) §6
2. [`KATALOG-MODUL-AI-BIJAN.md`](KATALOG-MODUL-AI-BIJAN.md)
3. `../TODO.md`

---

## Semua Dokumen

### Teras

| Dokumen | Isi | Dikemas kini |
|---|---|---|
| **[`AUDIT-BASELINE.md`](AUDIT-BASELINE.md)** | **Sumber kebenaran angka — dijana automatik daripada kod** | ✅ 27 Okt |
| **[`AI-BIJAN-AUDIT-INDUK-LENGKAP.md`](AI-BIJAN-AUDIT-INDUK-LENGKAP.md)** | **Audit induk — jurang, risiko, pertindihan, matriks P0–P3** | ✅ 27 Okt |
| **[`CHANGELOG.md`](CHANGELOG.md)** | **Sejarah status platform** (v8.9 → v9.6) | ✅ 27 Okt |
| **[`AI-BIJAN-MASTER-BLUEPRINT.md`](AI-BIJAN-MASTER-BLUEPRINT.md)** | **Blueprint induk (v9.6)** | ✅ 27 Okt |
| **[`SISTEM-PEMBANGUNAN-DARI-MULA.md`](SISTEM-PEMBANGUNAN-DARI-MULA.md)** | **Satu dokumen: bina + operasi + jualan (27 seksyen)** | ✅ 26 Okt |
| **[`BLUEPRINT-SISTEM-LENGKAP.md`](BLUEPRINT-SISTEM-LENGKAP.md)** | **Reverse engineering — bina semula dari sifar** | ✅ 26 Okt |
| **[`SISTEM-AI-BIJAN.md`](SISTEM-AI-BIJAN.md)** | **Dokumen sistem lengkap** | ✅ 27 Okt |
| [`KATALOG-MODUL-AI-BIJAN.md`](KATALOG-MODUL-AI-BIJAN.md) | Katalog terperinci setiap modul | ✅ 27 Okt |
| [`getting-started.md`](getting-started.md) | Setup dari sifar | ✅ 26 Okt |
| [`BACA-SAYA.md`](BACA-SAYA.md) | Panduan pantas | ✅ 26 Okt |
| [`AUDIT-DOKUMEN.md`](AUDIT-DOKUMEN.md) | Laporan audit dokumentasi (petikan lama) | ✅ 26 Okt |
| [`TODO.md`](TODO.md) | Kerja belum siap (P0–P5) | ✅ 27 Okt |

### AI & Automasi

| Dokumen | Isi |
|---|---|
| [`MODUL-AI-INTEGRASI.md`](MODUL-AI-INTEGRASI.md) | Peta kebergantungan AI + titik integrasi |
| [`PROSPEK-BLAST-CLOSE.md`](PROSPEK-BLAST-CLOSE.md) | Enjin jualan: prospek → blast → close |
| [`KAJIAN-FINDS-PROSPECT-BLAST-CLOSE.md`](KAJIAN-FINDS-PROSPECT-BLAST-CLOSE.md) | Kajian asal + status kod |
| [`customer-side.md`](customer-side.md) | Customer Portal & Personal AI |
| [`PELAN-VIRTUAL-OFFICE.md`](PELAN-VIRTUAL-OFFICE.md) | Pelan Virtual Office |

### Strategi & Kompetitif

| Dokumen | Isi |
|---|---|
| **[`MODEL-HASIL-TOKEN.md`](MODEL-HASIL-TOKEN.md)** | **Model hasil sebenar** — RM388 langganan + pakej token AI WAJIB (RM55–1,100) · komisen RM100 dari langganan sahaja · **kos token SUDAH DIKIRA** (margin 90.9%) |
| **[`RESELLER-KOMISEN-BERULANG.md`](RESELLER-KOMISEN-BERULANG.md)** | **Model komisen berulang** — RM388/RM100/bulan · 12 peraturan · dikodkan dalam `Billing` (32 ujian) |
| [`ANALISIS-KOMPETITIF.md`](ANALISIS-KOMPETITIF.md) | 5 pesaing teras + skor |
| [`PETA-PESAING-34-MODUL.md`](PETA-PESAING-34-MODUL.md) | 400+ pesaing, 34 **kategori** |
| [`AUDIT-PESAING-LANJUTAN.md`](AUDIT-PESAING-LANJUTAN.md) | 15 pesaing baharu |
| [`REVERSE-ENGINEERING-200-APPS-50-OSS.md`](REVERSE-ENGINEERING-200-APPS-50-OSS.md) | 200 apps + 50 OSS |
| [`BUSINESS-GROWTH-ENGINE.md`](BUSINESS-GROWTH-ENGINE.md) | 9 kelebihan + status kod |
| [`CADANGAN-IMPAK-TINGGI.md`](CADANGAN-IMPAK-TINGGI.md) | 15 cadangan |
| [`competitive-analysis-report.md`](competitive-analysis-report.md) | Laporan panjang |
| [`ai-bijan-vs-world-comparison.md`](ai-bijan-vs-world-comparison.md) | Perbandingan global |
| [`shopify-hubspot-enhancement-strategy.md`](shopify-hubspot-enhancement-strategy.md) | Strategi Shopify/HubSpot |

### Rujukan & Mockup

| Fail | Nota |
|---|---|
| [`AI-BIJAN-MASTER-BLUEPRINT.md`](AI-BIJAN-MASTER-BLUEPRINT.md) | **Blueprint induk v9.6** — 103 bab / 27 PART (termasuk BAB 93 Reseller) |
| [`cara-pasang.md`](cara-pasang.md) | Mockup Command Center + cara lekat |
| `command-center.html` · `CommandCenter.tsx` · `dashboard.blade.php` | Mockup UI — **bukan kod produksi** |
| `dashboard-bi.patch` · `dashboard-command-center.patch` | Patch sejarah |
| `dashboard.blade (1).php` | ⚠️ Duplikasi — calon dibuang |

---

## `archive/` — Jangan Guna Sebagai Rujukan Semasa

| Folder | Bilangan | Isi |
|---|---|---|
| `archive/features/` | 28 fail | **Dokumen B-POS System** — bukan AI BIJAN |
| `archive/` (akar) | 7 fail | **Dokumen B-POS System** — `configuration.md` (DOKU), dll. |
| `archive/blueprint-lama/` | 28 fail | Blueprint v2.x–v8.x yang digantikan |

**Bukti dokumen B-POS bukan AI BIJAN:** `DOKU_CLIENT_ID`/`point_of_sales` = **0**
dalam `.env.example`; `app/Services/Payments/` **tidak wujud**; `DineOrder`,
`KitchenService`, `ThermalPrintService` = **0 kelas**.

**Hanya guna:** [`AI-BIJAN-MASTER-BLUEPRINT.md`](AI-BIJAN-MASTER-BLUEPRINT.md) (v9.6).

---

## Peraturan Dokumentasi

1. **Sahkan angka dulu.**
   ```powershell
   php skrip/semak-dokumen.php     # membandingkan katalog vs kod
   php artisan test
   php artisan route:list --path=api/v1 | Measure-Object -Line
   ```

2. **Jangan tandakan ✅ untuk ciri belum dikodkan.**
   ✅ siap & diuji · 🟢 kebanyakannya · 🟡 separa · ⏸️ placeholder · ⛔ tiada

3. **Sebut BAB + versi blueprint** untuk setiap ciri.

4. **Bezakan spesifikasi vs pelaksanaan.** BAB 90-91 (B-Receipt, B-Vision) =
   **spesifikasi sahaja, tiada kod**.

5. **Setiap dokumen mesti ada tarikh kemas kini** di bahagian atas.

6. **Dokumen yang mendakwa lebih daripada kod** → keputusan salah dengan yakin.
   **Dokumen yang mendakwa kurang** → pasukan bina semula perkara yang wujud.

---

## Rujukan Luar `docs/`

| Lokasi | Isi |
|---|---|
| [`../TODO.md`](../TODO.md) | T-01 → T-92 |
| [`../.github/skills/aibijan-coding/`](../.github/skills/aibijan-coding/) | Skill + gotchas + peraturan keselamatan |
| [`../skrip/`](../skrip/) | 20 skrip penyelenggaraan |
| [`AI-BIJAN-MASTER-BLUEPRINT.md`](AI-BIJAN-MASTER-BLUEPRINT.md) | Blueprint induk v9.6 |
| [`AUDIT-BASELINE.md`](AUDIT-BASELINE.md) | **Satu-satunya sumber kebenaran angka** (disahkan 27 Okt 2026) |
