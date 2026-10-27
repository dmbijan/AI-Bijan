# AUDIT-BASELINE — AI BIJAN SaaS

**Tarikh pengesahan:** 28 Oktober 2026
**Skop:** repositori `ai-bijan-saas` pada working tree semasa
**Kaedah:** kiraan statik daripada sistem fail (modul, migration, model, service,
controller, resource Filament, widget, laluan, kaedah ujian). Dijana oleh
`skrip/jana-baseline.php` untuk pengesahan semula yang boleh diulang.

> **Fail ini ialah satu-satunya sumber kebenaran angka.**
> Dokumen lain (`SISTEM-AI-BIJAN.md`, `KATALOG-MODUL-AI-BIJAN.md`, `TODO.md`,
> `AI-BIJAN-AUDIT-INDUK-LENGKAP.md`, `AGENTS.md`) **wajib merujuk** fail ini dan
> **tidak boleh** menyalin angka secara manual. Jika angka berubah, kemas kini
> fail ini sahaja.

---

## 1. Baseline disahkan

> **Dijana automatik** oleh `php skrip/jana-baseline.php --tulis`.
> Tarikh penjanaan: 2026-10-27
> Jangan tulis angka di sini secara manual — jalankan skrip.

| METRIK | **NILAI** | CARA DIUKUR |
|---|---|---:|
| Modul | **60** | direktori di `app/Modules/` |
| Migration | **85** | fail di `database/migrations/` |
| Model | **312** | `app/Modules/**/Models/*.php` |
| Service | **179** | `app/Modules/**/Services/*.php` |
| Controller (modul) | **135** | `app/Modules/**/Http/Controllers/*.php` |
| Resource Filament | **66** | direktori di `app/Filament/Resources/` |
| Widget dashboard | **6** | `app/Filament/Widgets/*.php` |
| Kaedah ujian | **1,150** | `test*` + `#[Test]` + `/** @test */` di `tests/` |
| Laluan `api/v1` | **1,419** | router Laravel (`php artisan route:list`) |
| Jadual DB sebenar | **379** | `sqlite_master` / `SHOW TABLES` |
| Kebenaran | **212** | jadual `permissions` |

### 1.1 Angka tambahan (kiraan statik manual)

| Metrik | Nilai | Cara diukur |
|---|---:|---|
| Fail ujian | **106** | fail `.php` di `tests/` |
| Laluan `routes/marketing.php` | **100** | `routes/marketing.php` |
| Laluan web | **6** | `routes/web.php` |
| Liputan ujian pengasingan | **60/60 modul** | `php skrip/audit-tenant.php` (exit 0) |
| Laluan dengan pagar `perm:` | **1,384 / 1,419** | `php skrip/semak-kebenaran.php` (exit 0) |

### 1.2 Angka ujian (disahkan 28 Okt 2026, suite penuh dijalankan)

| Metrik | Nilai | Arahan |
|---|---:|---|
| Ujian Feature | **1,345** | `php vendor/phpunit/phpunit/phpunit -c phpunit.xml --testsuite Feature` |
| Assertion | **6,749** | sama |
| Gagal / ralat | **0** | sama |
| Dilangkau | **22** | ujian API pengasingan (`$laluanApi` null) + integrasi tanpa kunci |
| Ujian Unit | **4** | `--testsuite Unit` |

| Metrik | Cara mengukur |
|---|---|
| Advisori Composer | `composer audit` |
| Versi Laravel / PHP sebenar | `php artisan --version` |

> Versi yang disahkan 28 Okt 2026 pada mesin dev: **Laravel 12.70.1 · PHP 8.2.12**.
> Baseline §1 diukur pada DB dev yang sama; CI menjalankan `migrate` pada SQLite kosong,
> jadi lajur `Jadual DB sebenar`/`Kebenaran` **mesti sepadan** — jika tidak, CI gagal
> dengan sebab yang jelas (bukan "diff sentiasa gagal" seperti sebelum T-104).

---

## 2. Percanggahan lama yang sudah dibetulkan

Baseline ini menggantikan angka berikut yang tersalah papar dalam dokumen lama:

| Metrik | Nilai lama (salah) | Nilai sebenar | Sumber nilai lama |
|---|---:|---:|---|
| Laluan API | 1,518 | **1,419** | `SISTEM-AI-BIJAN.md`, `TODO.md`, `AUDIT-INDUK` |
| Laluan API | 1,388 | **1,419** | `KATALOG-MODUL-AI-BIJAN.md` |
| Ujian lulus | 962 / 963 / 1,009 | **1,136 kaedah** | `AUDIT-INDUK`, `TODO.md` |
| Jadual DB | 346 / 366 | **379** (ukur sebenar) | `AUDIT-INDUK`, `SISTEM` |
| Migration | 49 / 55 / 58 | **56** | `TODO.md`, `SISTEM`, v9.6 |
| Resource Filament | 65 / 66 | **66** | semua dokumen |
| Model | 292 / 310 | **312** | `AUDIT-INDUK`, `TODO.md` |
| Service | 166 / 174 | **179** | `AUDIT-INDUK`, `TODO.md` |
| Controller | 127 / 134 | **135** | `AUDIT-INDUK`, `TODO.md` |
| Modul | 58 (+1 spesifikasi) | **60 (semua dikod)** | `AUDIT-INDUK` §3.1 |

> **Nota jadual.** "368 jadual" dalam dokumen lama ialah kiraan fail migration,
> bukan jadual sebenar — satu migration boleh mencipta beberapa jadual. Nilai
> sebenar (`379`) diukur daripada `sqlite_master` oleh `skrip/jana-baseline.php`.

---

## 3. Status modul `Reseller` (koreksi penting)

Audit induk mengandungi **dua minor yang bercanggah**:

- §3.1 baris "Modul tambahan" — "Spesifikasi sahaja, dinyatakan belum dikodkan" ❌
- §13.3 — "Status kekal SPECIFIED / NOT CODED" ❌
- §3.2 / penutup — "telah dikodkan dan diuji" ✅

**Fakta disahkan daripada kod:** direktori `app/Modules/Billing/` wujud
(`ResellerCommissionService`, `Reseller`, `ResellerReferral`, `ResellerCommission`,
`ResellerPayout`, `ResellerClick`); migration
`2026_10_27_000500_create_billing_reseller_tables.php` wujud; ujian
`tests/Feature/BillingResellerTest.php` wujud.

| Aspek | Status |
|---|---|
| Implementasi kod | `IMPLEMENTED` |
| Ujian | `TESTED` |
| Kesesuaian komersial | `BLOCKED` (ADR-010 — terma hukum, audit margin, payout, cukai, KYC belum) |
| Denominator rasmi | **60 modul dikodkan (termasuk `Billing`/Reseller, `TeamAI`)** |

---

## 4. Cara mengesahkan semula

```sh
php artisan test                            # suite penuh
php skrip/jana-baseline.php --semak         # §1 vs kod — exit 1 jika hanyut
php skrip/semak-dokumen.php                 # bandingkan katalog vs kod
php skrip/semak-kebenaran.php               # route ↔ permission (T-105)
php skrip/audit-tenant.php                  # audit pengasingan tenant
php skrip/audit-ui.php                      # audit UI
composer audit                              # advisori keselamatan
```

`semak-dokumen.php` menyemak **laluan berganda** dan **laluan wildcard**
(`api/v1/{id}`) — dua kelas pepijat sebenar yang dikenal pasti 26 Okt 2026.

---

## 5. Skop dan had

Baseline ini **membuktikan saiz dan bentuk kod**, bukan kesediaan komersial.
Ia **tidak** membuktikan:

- semua integrasi luaran berfungsi (Meta, WhatsApp, CHIP, LHDN, Ads);
- pengasingan tenant sempurna pada semua laluan, queue, cache dan fail;
- concurrency stok, idempotency webhook, atau pemulihan kegagalan;
- pematuhan PDPA, GST, e-Invois, EPF/SOCSO;
- UI lengkap untuk setiap modul (T-19 `✅ SIAP`; T-35 UI 5 modul BAB 82 belum).

Skrip sokongan lain: `skrip/semak-perm-db.php` (senarai kebenaran DB untuk
pemeriksaan manual) dan `.github/workflows/ci.yml` (menjalankan `semak-dokumen`,
`jana-baseline --semak`, `audit-tenant`, semakan wildcard/prefix, `composer audit`).

---

## 6. How to regenerate

```sh
php skrip/jana-baseline.php                # cetak sahaja (tidak ubah fail)
php skrip/jana-baseline.php --tulis        # kemas kini §1 dalam fail ini
php skrip/jana-baseline.php --semak        # sahkan §1 sepadan dengan kod (CI)
php skrip/jana-baseline.php --tolak=x.md   # artefak (format neutral)
```

CI ([`.github/workflows/ci.yml`](../.github/workflows/ci.yml)) menjalankan `--semak`
dan **gagal** jika §1 tidak sepadan dengan kod.