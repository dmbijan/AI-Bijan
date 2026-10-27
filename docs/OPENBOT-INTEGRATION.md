# Integrasi OpenBot

AI Bijan menyediakan satu halaman integrasi di `/app/openbot` dengan dua bahagian:

- **Chat native AI Bijan** menggunakan endpoint AI Gateway sedia ada. Pengesahan Sanctum, kebenaran tenant, guardrails, redaksi PII dan metering kekal dikendalikan oleh backend AI Bijan. Sejarah chat native ini hanya berada dalam memori pelayar dan tidak disimpan selepas halaman ditutup.
- **Panel OpenBot** memaparkan servis OpenBot secara iframe apabila `OPENBOT_URL` ditetapkan. Pautan “Buka OpenBot” turut disediakan sebagai laluan alternatif.

## Konfigurasi

1. Deploy OpenBot sebagai servis berasingan mengikut [panduan deployment OpenBot](https://github.com/CopilotKit/OpenBot/blob/main/docs/deployment.md). Ia memerlukan konfigurasi dan perkhidmatan tersendiri, termasuk pangkalan data, CopilotKit Intelligence dan identity provider bagi deployment berbilang pengguna.
2. Tetapkan `OPENBOT_URL` kepada origin HTTPS OpenBot yang boleh dicapai oleh pelayar pengguna. Gunakan reverse proxy pada domain deployment jika diperlukan.
3. Pastikan OpenBot membenarkan origin AI Bijan menjadi frame parent dan polisi CSP AI Bijan membenarkan origin OpenBot. Selepas mengubah `.env`, jalankan `php artisan config:clear`.
4. Konfigurasikan OIDC/SAML atau akses berasingan di OpenBot. Iframe ini **tidak** berkongsi sesi, identiti pengguna atau tenant Laravel secara automatik.

## Log masuk SPA AI Bijan

Sanctum hanya memulakan sesi cookie untuk host yang disenaraikan dalam `SANCTUM_STATEFUL_DOMAINS`. Senarai itu perlu mengandungi host dan port yang digunakan oleh browser untuk membuka AI Bijan (contohnya `127.0.0.1:8145`); padankan nilainya dengan origin sebenar deployment. Jangan masukkan origin OpenBot jika ia hanya digunakan sebagai iframe berasingan. Selepas mengubah `.env`, jalankan `php artisan config:clear` dan muat semula halaman AI Bijan pada origin tersebut.

## Keselamatan dan had

- Jangan aktifkan mod single-user OpenBot untuk deployment yang boleh dicapai pengguna lain.
- Jangan letakkan API key OpenBot, CopilotKit Intelligence atau model provider dalam URL, HTML, atau JavaScript AI Bijan.
- Chat native menggunakan AI Gateway AI Bijan. Ia bukan adapter AG-UI dan belum menghantar identiti tenant, audit ataupun kelulusan AI Bijan kepada OpenBot.
- Penggunaan OpenBot dan penggunaan AI Gateway AI Bijan mempunyai konfigurasi servis, identiti dan metering yang berasingan sehingga integrasi SSO/AG-UI khusus dibangunkan dan diuji.
