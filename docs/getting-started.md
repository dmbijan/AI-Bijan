# Getting Started — AI BIJAN SaaS

> **Dikemas kini:** 26 Oktober 2026 · disahkan terhadap kod sebenar
> Kembali ke indeks dokumentasi: [`README.md`](README.md)

---

## Tujuan

Panduan ini membolehkan developer baru menjalankan aplikasi dari sifar sehingga
boleh log masuk ke panel `/admin`.

---

## Keperluan Minimum (DISAHKAN)

| Komponen | Versi sebenar | Nota |
|---|---|---|
| **PHP** | **8.2.12** | Diuji pada 8.2.12. Laravel 12 menyokong 8.2–8.4 |
| **Laravel** | **12.70.1** | `php artisan --version` |
| **Composer** | 2.x | |
| **Node.js** | 18+ | Untuk `npm run build` (Vite) |
| **Pangkalan data** | **SQLite** (lalai dev) | MySQL disokong — lihat `skrip/tukar_db.ps1` |
| **Git** | Ada di PATH | **Diperlukan oleh modul ADE** (worktree) |

⚠️ **Tiada ERPNext/Frappe.** AI BIJAN ialah aplikasi Laravel tulen.

---

## Langkah Setup

```bash
cd ai-bijan-saas

# 1. Dependensi
composer install
npm install

# 2. Konfigurasi
cp .env.example .env
php artisan key:generate

# 3. Pangkalan data (SQLite lalai — fail dicipta automatik)
php artisan migrate --seed

# 4. Storan
php artisan storage:link

# 5. Jadual kebenaran (WAJIB — tanpanya menu boleh 403)
php skrip/sync-perm.php

# 6. Jalankan
composer run dev          # server + queue + vite serentak
# atau:
php artisan serve --host=127.0.0.1 --port=8145
```

Buka `http://127.0.0.1:8145/admin` → log masuk.

---

## Cipta Tenant Ujian (DEV SAHAJA)

Skrip disediakan untuk mencipta pengguna + tenant + plan enterprise:

```bash
php skrip/cipta-tenant-ujian.php
```

Menghasilkan:
- Pengguna: `ujian@bijan.test` / `Password12345`
- Tenant: "Syarikat Ujian ADE" (slug `syarikat-ujian-ade`)
- Plan: `enterprise` — **59 modul aktif**

⚠️ **Selepas `migrate:fresh`, anda MESTI jalankan semula:**
```bash
php artisan db:seed --force          # plans + katalog model AI
php skrip/cipta-tenant-ujian.php     # pengguna + tenant
php skrip/sync-perm.php              # kebenaran
```

---

## Sahkan Pemasangan

```bash
php artisan test                     # suite penuh — lulus, 0 gagal
php artisan route:list --path=api/v1 # ~872 laluan api/v1
php skrip/semak-dokumen.php          # semua angka ✅
php skrip/jana-baseline.php          # jana semula AUDIT-BASELINE.md
```

Titik kesihatan HTTP:
- `GET /health` — liveness (tiada I/O)
- `GET /ready` — DB/cache/storage/queue/migrasi (503 jika gagal)
- `GET /metrics` — metrik

---

## Panel Admin Filament

| URL | Fungsi |
|---|---|
| `/admin` | Dashboard — 6 widget (4 KPI, trend 30 hari, saluran, pesanan, invois) |
| `/admin/tetapan` | Tetapan syarikat, langganan, modul aktif, pengguna |
| `/admin/pesanan` · `/admin/invois` · `/admin/pelanggan` … | **66 resource** CRUD |

**Amaran:** menu tersembunyi **bukan** kawalan keselamatan. API tetap dikawal
`perm:` + `module:`. Kedua-dua lapisan mesti selari.

---

## Skrip Penyelenggaraan (`skrip/`)

| Skrip | Fungsi |
|---|---|
| `sync-perm.php` | Segerakkan `config/aibijan.php` → jadual `permissions` (**212**) |
| `cipta-tenant-ujian.php` | Cipta pengguna + tenant ujian + plan enterprise |
| `naik-plan.php` | Naik taraf tenant ke plan (pengesahan visual) |
| `semak-dokumen.php` | Sahkan angka dokumentasi vs kod |
| `jana-resource.php` | Jana resource Filament (**jalankan `pulih-pos.php` selepasnya**) |
| `pulih-pos.php` | Pulihkan 3 resource POS manual |
| `tukar_db.ps1` | Tukar `.env` SQLite ↔ MySQL (dengan sandaran) |
| `semak_mysql.php` | Audit migration untuk keserasian MySQL |

---

## Amaran Setup

| Isu | Keterangan |
|---|---|
| **WhatsApp** | ❌ **JANGAN** guna `whatsapp-web.js` (risiko ban). Guna WhatsApp Business Cloud API + templat Meta diluluskan |
| **Queue worker** | Perlu untuk kempen blast, reminder, publish kandungan |
| **Scheduler** | `php artisan schedule:run` setiap minit |
| **AI Gateway** | `AI_GATEWAY_LIVE=false` = mod simulasi (selamat, lalai) |
| **Produksi** | Jalankan `composer audit` — mesti **0 advisori** |
| **ADE worktree** | Hanya DALAM `storage/app/ade/worktrees` — jangan ubah |
