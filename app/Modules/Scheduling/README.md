# Scheduling

Penjadualan tempahan — pautan awam, slot tersedia, routing provider. Berasal daripada https://github.com/calcom/cal.diy

## Struktur

```
app/Modules/Scheduling/
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

