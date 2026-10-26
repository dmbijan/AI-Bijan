# AI Bijan SaaS: pemasangan (Fasa 1)

> Status: **platform teras sahaja** (log masuk, multi-tenant, peranan, audit, fail, kunci API, kelulusan, Super Admin).
> Modul perniagaan (POS, stok, COGS, kewangan, HR…) dibina dalam fasa seterusnya. Belum sedia produksi.

## Keperluan hosting (sahkan dengan pembekal cPanel)
- PHP **8.3+** dengan sambungan: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo, gd, zip, curl.
- MySQL 8 / MariaDB 10.6+ (utf8mb4).
- Composer (atau muat naik folder `vendor/` yang dibina di luar).
- Cron Jobs (setiap minit). SSL/HTTPS. Outbound HTTPS (untuk AI & integrasi kemudian).
- Tidak perlu Node, Redis atau Docker di pelayan.

## Langkah
1. Cipta pangkalan data + pengguna (kebenaran minimum) di cPanel.
2. Muat naik kod (tanpa `.env`). Halakan document root domain ke folder `public/`.
3. `cp .env.example .env` → isi `APP_URL`, `DB_*`, `APP_TIMEZONE=Asia/Kuala_Lumpur`, `APP_LOCALE=ms`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database`, `APP_ENV=production`, `APP_DEBUG=false`.
4. `composer install --no-dev --optimize-autoloader`
5. `php artisan key:generate`
6. `php artisan migrate --force && php artisan db:seed --force` (katalog kebenaran + pelan percubaan)
7. Super Admin pertama (CLI, kata laluan diminta interaktif, bukan lalai): *akan disediakan sebagai arahan artisan dalam langkah seterusnya*.
8. Cron: `* * * * * cd /path/aibijan && php artisan schedule:run >> /dev/null 2>&1`
   Queue tanpa worker kekal: jadualkan `queue:work --stop-when-empty` melalui scheduler.
9. `php artisan config:cache route:cache`
10. Jangan letak `.env`, sandaran SQL, log atau eksport dalam `public/`. Jangan guna kebenaran 777.

## Ujian
`php artisan test` (MariaDB pangkalan data ujian berasingan: `aibijan_saas_test`).
