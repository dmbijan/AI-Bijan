# AUDIT-BASELINE — AI BIJAN SaaS

**Tarikh pengesahan:** 28 Oktober 2026
**Skop:** repositori `ai-bijan-saas` pada working tree semasa
**Kaedah:** kiraan statik daripada sistem fail (bmodul, migration, model, service,
controller, resource Filament, widget, laluan, kaedah ujian). Didokumentasikan dalam
`skrip/semak-dokumen.php` untuk pengesahan semula yang boleh diulang.

> **Fail ini ialah satu-satunya sumber kebenaran angka.**
> Dokumen lain (`SISTEM-AI-BIJAN.md`, `KATALOG-MODUL-AI-BIJAN.md`, `TODO.md`,
> `AI-BIJAN-AUDIT-INDUK-LENGKAP.md`, `AGENTS.md`) **wajib merujuk** fail ini dan
> **tidak boleh** menyalin angka secara manual. Jika angka berubah, kemas kini
> fail ini sahaja.

---

## 1. Baseline disahkan

> **Dijana automatik** oleh `php skrip/jana-baseline.php --tulis`.
> Tarikh penjanaan: 2026-10-28
> Jangan tulis angka di sini secara manual — jalankan skrip.
> CI akan gagal jika seksyen ini tidak sepadan dengan kod sebenar.
>
> **Nota 28 Okt 2026:** skrip ini **tidak boleh dijalankan sama sekali** sebelum
> hari ini (`Undefined variable $root` — `$root` ditakrifkan selepas closure
> `static` menggunakannya, dan closure `static` tidak mewarisi skop induk).
> Angka v9.6 di bawah digantikan dengan keluaran skrip yang kini berfungsi.
> Punca angka laluan melonjak: `Laluan api/v1` kini dikira daripada router
> Laravel sebenar (872 → 1,419), bukan anggaran manual.

| METRIK | NILAI | CARA DIUKUR |
|---|---:|---|
| Modul | **60** | direktori di `app/Modules/` |
| Migration | **56** | fail di `database/migrations/` |
| Model | **312** | `app/Modules/**/Models/*.php` |
| Service | **179** | `app/Modules/**/Services/*.php` |
| Controller (modul) | **135** | `app/Modules/**/Http/Controllers/*.php` |
| Resource Filament | **66** | direktori di `app/Filament/Resources/` |
| Widget dashboard | **6** | `app/Filament/Widgets/*.php` |
| Kaedah ujian | **1,136** | `test*` + `#[Test]` + `/** @test */` di `tests/` |
| Laluan `api/v1` | **1,419** | router Laravel (`php artisan route:list`) |
| Jadual DB sebenar | **379** | `sqlite_master` / `SHOW TABLES` |
| Kebenaran | **212** | jadual `permissions` |

### 1.1 Angka tambahan (kiraan statik manual)

Nilai ini dikira semasa audit 27 Okt 2026. Angka yang boleh dijana semula kini
berada dalam §1 (skrip sudah diperbaiki 28 Okt 2026):

| Metrik | Nilai | Cara diukur |
|---|---:|---|
| Fail ujian | **94** | fail `.php` di `tests/` (90 Feature + 3 Support + 1 Unit) |
| Laluan `routes/marketing.php` | **100** | `routes/marketing.php` |
| Laluan web | **6** | `routes/web.php` |
| Liputan ujian pengasingan | **60/60 modul** | `php skrip/audit-tenant.php` (exit 0) |

### 1.2 Angka yang TIDAK disahkan (perlu suite ujian sebenar)

| Metrik | Cara mengukur | Arahan |
|---|---|---|
| Ujian lulus / gagal / skip | PHPUnit | `php artisan test` |
| Bilangan assertion | PHPUnit | `php artisan test` |
| Advisori Composer | Packagist | `composer audit` |
| Versi Laravel / PHP sebenar | — | `php artisan --version` |

> Versi yang disahkan 28 Okt 2026 pada mesin dev: **Laravel 12.70.1 · PHP 8.2.12**.

---

## 2. Percanggahan lama yang sudah dibetulkan

Baseline ini menggantikan angka berikut yang tersalah paparkan dalam dokumen lama:

| Metrik | Nilai lama (salah) | Nilai sebenar | Sumber nilai lama |
|---|---:|---:|---|
| Laluan API | 1,518 | **~872** | `SISTEM-AI-BIJAN.md`, `TODO.md`, `AUDIT-INDUK` |
| Laluan API | 1,388 | **~872** | `KATALOG-MODUL-AI-BIJAN.md` |
| Ujian lulus | 962 | **~1,119 kaedah** | `AUDIT-INDUK`, `KATALOG` |
| Ujian lulus | 1,009 | **~1,119 kaedah** | `SISTEM-AI-BIJAN.md` |
| Ujian lulus | 963 | **~1,119 kaedah** | `TODO.md` |
| Jadual DB | 346 | **368** (kiraan migration) | `AUDIT-INDUK`, `TODO.md` |
| Jadual DB | 366 | **368** (kiraan migration) | `SISTEM-AI-BIJAN.md`, `KATALOG` |
| Migration | 49 / 55 | **58** | `TODO.md` / `SISTEM-AI-BIJAN.md` |
| Resource Filament | 66 | **65** | semua dokumen |
| Model | 292 | **310** | `AUDIT-INDUK`, `TODO.md` |
| Service | 166 | **174** | `AUDIT-INDUK`, `TODO.md` |
| Controller | 127 | **134** | `AUDIT-INDUK`, `TODO.md` |
| Modul | 58 (+1 spesifikasi) | **59 (semua dikod)** | `AUDIT-INDUK` §3.1 |

> **Nota jadual.** "368 jadual" ialah kiraan fail migration, bukan jadual sebenar —
> satu migration boleh mencipta beberapa jadual. Nilai sebenar mesti diukur dengan
> `php skrip/semak-dokumen.php`.

---

## 3. Status modul `Reseller` (koreksi penting)

Audit induk mengandungi **dua minor yang bercanggah**:

- §3.1 baris "Modul tambahan" — menyatakan "Spesifikasi sahaja, dinyatakan belum dikodkan" ❌
- §13.3 — menyatakan "Status kekal SPECIFIED / NOT CODED" ❌
- §3.2 / penutup — menyatakan "telah dikodkan dan diuji" ✅

**Fakta disahkan daripada kod:**

- Direktori `app/Modules/Billing/` wujud dengan `ResellerCommissionService`,
  `Reseller`, `ResellerReferral`, `ResellerCommission`, `ResellerPayout`,
  `ResellerClick`.
- Migration `2026_10_27_000500_create_billing_reseller_tables.php` wujud.
- Ujian `tests/Feature/BillingResellerTest.php` wujud (37 kaedah dikira).

**Status betul:**

| Aspek | Status |
|---|---|
| Implementasi kod | `IMPLEMENTED` |
| Ujian | `TESTED` |
| Kesesuaian komersial | `BLOCKED` (ADR-010 — terma hukum, audit margin, payout, cukai, KYC belum) |
| Denominator rasmi | **59 modul dikodkan (termasuk `Billing`/Reseller)** |

---

## 4. Cara mengesahkan semula

```sh
php artisan test                    # suite penuh
php skrip/semak-dokumen.php         # bandingkan katalog vs kod
php skrip/semak-kebenaran.php       # semak kebenaran
php skrip/audit-tenant.php          # audit pengasingan tenant
php skrip/audit-ui.php              # audit UI
composer audit                      # advisori keselamatan
```

Skrip `semak-dokumen.php` sudah menyemak **laluan berganda** dan **laluan wildcard**
(`api/v1/{id}`) — dua kelas pepijat sebenar yang dikenal pasti 26 Okt 2026.

---

## 5. Skop dan had

Baseline ini **membuktikan saiz dan bentuk kod**, bukan kesediaan komersial.
Ia **tidak** membuktikan:

- semua integrasi luaran berfungsi (Meta, WhatsApp, CHIP, LHDN, Ads);
- pengasingan tenant sempurna pada semua laluan, queue, cache dan fail;
- concurrency stok, idempotency webhook, atau pemulihan kegagalan;
- pematuhan PDPA, GST, e-Invois, EPF/SOCSO;
- UI lengkap untuk setiap modul (T-19 "Design System" `✅ SIAP`; T-35 UI 5 modul
  BAB 82 masih belum).

### 5.1 Skrip kecil / tersasar (semakan dokumentasi vs kod)

`§4` sebelum 28 Okt 2026 merujuk skrip yang **tidak** melakukan pengesahan pukal:

| Rujukan | Realiti (28 Okt 2026) |
|---|---|
| `skrip/semak-kebenaran.php` | **Bukan audit** — skrip 32 baris yang menyenaraikan kebenaran **tenant #1 sahaja** (ID dikod keras). Tidak membandingkan laluan `perm:` dengan jadual `permissions`. Tiada sebarang pagar `route↔permission` (T-105) |
| `.github/workflows/ci.yml` | **Wujud (6.7 KB)** dan menjalankan `semak-dokumen`, `jana-baseline`, `audit-tenant`, semakan wildcard/prefix, `composer audit` |
| Langkah CI `Periksa baseline dalam repo` | **Salah**: jana-baseline menulis dengan label `jadual | **nilai** |`, `AUDIT-BASELINE.md` guna `jadual | nilai |` → `diff` sentiasa berbeza → **CI gagal walaupun baseline betul** (T-104) |

Rujuk `docs/AI-BIJAN-AUDIT-INDUK-LENGKAP.md` §15–§17 untuk matriks P0–P3 dan
senarai semak pelepasan.

---

## 6. How to regenerate

```sh
php skrip/jana-baseline.php            # cetak sahaja (tidak ubah fail)
php skrip/jana-baseline.php --tulis    # kemas kini §1 dalam fail ini
```

CI ([`.github/workflows/ci.yml`](../.github/workflows/ci.yml)) menjalankan skrip ini dan
**gagal** jika §1 tidak sepadan dengan kod — ini menutup litar semakan
dokumentasi secara manual.

> ⚠️ **28 Okt 2026:** langkah CI ini **belum pernah lulus**. Skrip ini sebelum hari
> ini ranap (`Undefined variable $root`), dan selepas dibaiki label `| **NILAI** |`
> yang dijana tidak sepadan dengan jadual dokumen → `diff` sentiasa gagal.
> Sehingga T-104 siap, sahkan secara manual:
> `php skrip/jana-baseline.php` (cetak) vs jadual §1 fail ini.
