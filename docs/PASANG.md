# AI Bijan SaaS: pemasangan

> **Status (26 Okt 2026):** **58 modul siap** — termasuk POS, stok, kewangan, HR/payroll,
> e-Invois LHDN, CRM, ServiceOS, AI Gateway, dan UI admin Filament (66 resource).
> Platform teras (log masuk, multi-tenant, peranan, audit, fail, kunci API, kelulusan,
> Super Admin) juga siap.
>
> ⚠️ **Belum sedia produksi penuh** tanpa: ujian staging, `sync-perm.php` pada DB
> produksi, dan semakan `composer audit` (**mesti 0 advisori**).
> Lihat `docs/SISTEM-AI-BIJAN.md` §6 untuk status jujur.

## Keperluan hosting (sahkan dengan pembekal cPanel)
- PHP **8.2+** (diuji pada **8.2.12**) dengan sambungan: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo, gd, zip, curl.
- MySQL 8 / MariaDB 10.6+ (utf8mb4). SQLite juga disokong untuk dev.
- Composer (atau muat naik folder `vendor/` yang dibina di luar).
- Cron Jobs (setiap minit). SSL/HTTPS. Outbound HTTPS (untuk AI & integrasi).
- Tidak perlu Node, Redis atau Docker di pelayan (Node hanya untuk build aset semasa deploy).

## Langkah
1. Cipta pangkalan data + pengguna (kebenaran minimum) di cPanel.
2. Muat naik kod (tanpa `.env`). Halakan document root domain ke folder `public/`.
3. `cp .env.example .env` → isi `APP_URL`, `DB_*`, `APP_TIMEZONE=Asia/Kuala_Lumpur`, `APP_LOCALE=ms`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database`, `APP_ENV=production`, `APP_DEBUG=false`.
4. `composer install --no-dev --optimize-autoloader`
5. `php artisan key:generate`
6. `php artisan migrate --force && php artisan db:seed --force` (katalog kebenaran + pelan percubaan)
7. **`php skrip/sync-perm.php`** ← **WAJIB** — segerakkan 212 kebenaran daripada
   `config/aibijan.php` ke jadual `permissions`. Tanpanya menu admin boleh 403.
8. `php artisan filament:optimize` (cache resource/widget Filament)
9. Super Admin pertama (CLI, kata laluan diminta interaktif, bukan lalai): *akan disediakan sebagai arahan artisan dalam langkah seterusnya*.
8. Cron: `* * * * * cd /path/aibijan && php artisan schedule:run >> /dev/null 2>&1`
   Queue tanpa worker kekal: jadualkan `queue:work --stop-when-empty` melalui scheduler.
9. `php artisan config:cache route:cache`
10. Jangan letak `.env`, sandaran SQL, log atau eksport dalam `public/`. Jangan guna kebenaran 777.

## Ujian
`php artisan test` (MariaDB pangkalan data ujian berasingan: `aibijan_saas_test`).
