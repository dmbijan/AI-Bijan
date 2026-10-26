# Configuration

Kembali ke indeks dokumentasi: `docs/README.md`

## Environment Penting

| Variable | Untuk apa |
|----------|-----------|
| `APP_URL` | Webhook URL, public invoice, customer portal link, payment callback |
| `APP_VERSION` | Versi aplikasi (tampil di sidebar + POS navbar) |
| `DB_DATABASE` | Nama database (default: `point_of_sales`) |
| `DOKU_CLIENT_ID` | Client ID / Brand ID senangPay (DOKU), cth `BRN-0268-...` |
| `DOKU_API_KEY` | API Key DOKU (Basic auth) |
| `DOKU_SECRET_KEY` | Secret Key DOKU (tandatangan HMAC-SHA256) |
| `DOKU_IS_PRODUCTION` | `true` = api.doku.com, `false` = sandbox |
| `DOKU_API_VERSION` | Header `API-Version` (default `doku-global.2025-12-01`) |
| `AUTH_PUBLIC_REGISTRATION` | Aktifkan registrasi publik (`true`/`false`, default: `false`) |
| `SANCTUM_STATEFUL_DOMAINS` | Domain yang diizinkan untuk autentikasi cookie Sanctum. **Wajib diisi domain produksi** (jangan biarkan `localhost` di produksi) |
| `SANCTUM_TOKEN_EXPIRATION` | Masa berlaku token API dalam menit (default `1440` = 24 jam) |
| `SESSION_SECURE_COOKIE` | Set `true` di produksi (HTTPS) agar cookie sesi hanya dikirim via HTTPS |
 | `WA_SERVICE_URL` | Alamat Node.js WhatsApp service (default: `http://localhost:3001`) |
 | `WA_SERVICE_TOKEN` | Token bersama untuk autentikasi request ke WhatsApp service (produksi wajib) |

 Versi runtime yang ditampilkan aplikasi berasal dari `APP_VERSION`. Nilainya harus disamakan
 dengan Git tag rilis, misalnya `v2.10.4`.

## Autentikasi

- **Email verification dinonaktifkan.** `User` tidak lagi mengimplementasikan `MustVerifyEmail`,
  route dashboard tidak memakai middleware `verified`, dan route/controller/halaman verifikasi
  sudah dihapus. Setup wizard dan registrasi publik langsung mengarahkan user ke dashboard.
- `AUTH_PUBLIC_REGISTRATION` mengatur apakah registrasi publik diizinkan (default `false`).
  Saat nonaktif, akun dibuat lewat setup wizard atau menu Users oleh admin.
- `markEmailAsVerified()` tetap tersedia (dipakai seeder dan test).

## APP_URL

`APP_URL` harus public (bukan `localhost`) jika menggunakan:

- Notification senangPay (DOKU)
- Public invoice sharing
- Customer portal link
- Payment gateway callback

## Payment Gateway

Konfigurasi di `dashboard/settings/payments`:

- **Cash** — tanpa konfigurasi
- **Bank Transfer** — memerlukan rekening bank aktif
- **senangPay (DOKU)** — memerlukan Client ID (Brand ID) + API Key + Secret Key + mode produksi

Detail setup: `docs/features/settings-payments.md`

## Bank Accounts

Konfigurasi di `dashboard/settings/bank-accounts`:

- Digunakan untuk pembayaran transfer manual
- Bisa diatur urutan tampilan
- Bisa dinonaktifkan tanpa dihapus

## Tax Settings

Konfigurasi di `dashboard/settings/store` — bagian "Informasi Pajak & Legal":

- **NPWP Toko** — Nomor Pokok Wajib Pajak (format: `XX.XXX.XXX.X-XXX.XXX`)
- **NIB** — Nomor Induk Berusaha
- **Tarif PPN Default** — Persentase PPN untuk produk baru (default: 11.00%)
- Tarif PPN bisa diubah per produk di halaman edit produk

## Printer Settings

Konfigurasi di `dashboard/settings/printer`:

- **Ukuran Kertas** — 80mm atau 58mm
- **Auto-print** — cetak receipt otomatis setelah transaksi (via WebUSB)
- Thermal printer terhubung via WebUSB (browser Chrome/Edge)

## Store Profile

Konfigurasi di `dashboard/settings/store`:

- Nama, alamat, telepon, email, website, kota
- Logo toko
- NPWP dan NIB (untuk keperluan pajak)

## Sales Target

Konfigurasi di `dashboard/settings/target`:

- Target penjualan bulanan
- Muncul di dashboard sebagai progress bar

## WhatsApp Gateway

Konfigurasi di `dashboard/settings/whatsapp`:

- **URL Service** — alamat Node.js service (default `http://localhost:3001`)
- **Aktifkan Gateway** — enable/disable WhatsApp integration
- **Kirim Otomatis** — reminder piutang + invoice otomatis via campaign
- **Koneksi** — scan QR untuk menghubungkan (session tersimpan otomatis)

Detail setup: `docs/features/whatsapp-gateway.md`

 Campaign dan reminder terjadwal membutuhkan Laravel scheduler dan queue worker aktif.

## Multi-Warehouse

Konfigurasi di `dashboard/settings/warehouses`:

- **Main Warehouse** — gudang pusat, dibuat otomatis saat seeding
- **Branch Warehouse** — cabang toko yang juga menjual langsung
- **Stock Warehouse** — gudang penyangga (tidak menjual langsung)
- Stok produk dipisah per warehouse di tabel `product_warehouse`

## Catatan Dependency Eksternal

- `laravolt/indonesia` — data provinsi/kota/kecamatan/desa Indonesia
- `barryvdh/laravel-dompdf` — generate PDF invoice/receipt/shipping label
- `picqer/php-barcode-generator` — barcode di dokumen PDF
- `maatwebsite/excel` — import/export CSV + Excel
- senangPay by DOKU — payment gateway (MYR)
- `whatsapp-web.js` — WhatsApp gateway (Node.js, terpisah dari Laravel)
