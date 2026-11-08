# Workspace

Dokumen & pangkalan data kolaboratif berasaskan blok. Berasal daripada https://github.com/AppFlowy-IO/AppFlowy

## Struktur

```
app/Modules/Workspace/
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

