# OpenGenAI

Enjin generatif pelbagai provider (teks/imej/video/audio). Berasal daripada https://github.com/Anil-matcha/Open-Generative-AI

## Struktur

```
app/Modules/OpenGenAI/
├── <Modul>ServiceProvider.php
├── config/                       # Konfigurasi modul
├── Models/                       # Model Eloquent (dengan BelongsToTenant)
├── Services/                    # Logik domain
├── Http/Controllers/             # API controller
├── routes/api.php                # Laluan API (middleware perm + tenant)
└── database/migrations/          # Migrasi jadual modul
```

## API

Prefix: `/api/v1/<prefix>`

Semua laluan dilindungi middleware `perm:<modul>.<tindakan>` dan `tenant`.

Lihat dokumentasi penuh: [docs/INTEGRASI-SUMBER-TERBUKA.md](../../../docs/INTEGRASI-SUMBER-TERBUKA.md)

