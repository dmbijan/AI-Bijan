# CHANGELOG — AI BIJAN SaaS

Sejarah perubahan status platform. Fail ini menyimpan **semua rekod sejarah** yang
sebelum ini berselerak dalam `TODO.md`, supaya `TODO.md` boleh menjadi senarai
**kerja belum siap** yang jernih.

> **Nombor versi** di sini ialah *versi blueprint* (bukan versi aplikasi).
> Rujukan blueprint: [`AI-BIJAN-MASTER-BLUEPRINT.md`](AI-BIJAN-MASTER-BLUEPRINT.md).

---

## v9.6.1 — 28 Oktober 2026

### Kerja berbukti kod (bukan dokumen)

- **`skrip/jana-baseline.php` dibaiki** — skrip ini **ranap** sejak dicipta
  (`Undefined variable $root`: `$root` ditakrifkan selepas closure `static` yang
  menggunakannya, dan closure `static` tidak mewarisi skop induk). Kini berjalan
  semula dan menghasilkan 60 modul / 56 migration / 312 model / 179 servis /
  135 controller / 66 resource / 1,136 kaedah ujian / 1,419 laluan `api/v1` /
  379 jadual / 212 kebenaran.
- **Migrasi `2026_10_27_000100_create_team_ai_workspace_tables` dijalankan** pada
  DB dev (status `Pending` → `Ran`). Sebelum ini `team_ai_repositories` dan
  `team_ai_assets` **tidak wujud**, jadi setiap `TeamAiRepository::create()`
  gagal dan `audit-tenant` melaporkan kedua-dua model sebagai "trait tanpa kolum".
- **`tests/Feature/Pengasingan/TeamAiTenantIsolationTest.php` ditambah** —
  liputan pengasingan tenant kini **60/60 modul** (sebelum ini 59/60).
  `php skrip/audit-tenant.php` → exit 0, `0 trait tanpa kolum`, `0 bocor`.
  5/5 ujian lulus (21 assertion); `TeamAiWorkspaceTest` 4/4 masih lulus.

### Dokumen

- **`AUDIT-BASELINE.md`** — §1 dijana semula (angka v9.6 digantikan); §1.1/§1.2
  dikemas kini; §5.1 ditambah: `skrip/semak-kebenaran.php` **bukan** audit
  `route↔permission` (32 baris, tenant #1 dikod keras) dan langkah CI
  `Periksa baseline dalam repo` membandingkan format berbeza → sentiasa gagal.
- **`TODO.md`** — ringkasan metrik dikemas kini; seksyen baharu
  **"Kerja sesi 28 Okt 2026"** dengan 9 entri bernombor (T-104 … T-112) yang
  setiap satunya dijejak kepada bukti output; `Nota kerja` ditambah 3 perangkap
  (migrasi pending menyamarkan model rosak, output `artisan test` pada PowerShell,
  keperluan DB untuk `jana-baseline`).

### Isu terbuka (tidak diselesaikan)

- T-104 CI masih gagal (format `diff` baseline belum diseragamkan).
- T-105 pagar `route ↔ permission` belum wujud.
- T-109 migration 58 → 56 perlu disahkan (risiko DB produksi tidak boleh dibina semula).

---

## v9.6.2 — 28 Oktober 2026 (sambungan)

### Keselamatan & pagar (T-105)

- **`skrip/semak-kebenaran.php` ditulis semula** — sebelum ini 32 baris yang menyenaraikan
  kebenaran tenant #1 (ID dikod keras). Kini lima semakan sebenar terhadap router:
  `perm:` digunakan tetapi tiada dalam katalog, kebenaran mati, laluan berautentikasi
  tanpa `perm:`, laluan tanpa middleware `tenant`, dan senarai putih
  (`config/semak-kebenaran.php`) yang pengawalnya tidak memeriksa kebenaran.
- **57 laluan terdedah ditemui dan ditutup:**
  - `Assets` — 23 laluan hanya `auth`+`tenant` (padam aset, tutup penyelenggaraan)
  - `Invoicing` — 22 laluan (rekod bayaran, hantar e-Invois LHDN, lulus sebut harga)
  - `routes/api.php` — `GET tenant`, `POST documents`, `void`, `preview`
  - `Billing` — `/packages`; reseller diberi pagar eksplisit
  - `Fundamental` — `health/ready|deep`
- **Pagar `route ↔ permission` kini hijau:** 1,384/1,419 laluan berperm, 0 tanpa pagar,
  0 tanpa tenant. CI menjalankannya.

### Pengasingan tenant (T-111)

- **Audit liputan dibaiki:** `skrip/audit-tenant.php` sebelum ini memberi kredit modul
  berdasarkan `use App\Modules\X\` dalam **korpus gabungan** ujian → "60/60 modul" tidak
  sah. Liputan sebenar ketika itu **36/60**.
- **12 fail bergabung diisytiharkan semula** (`$modul = 'A,B,C'`) melalui
  `skrip/kemas-modul-bergabung.php`; `UjianPengasingan` menyokong senarai modul.
- **5 fail pengasingan baharu** (AIGateway, Boardroom, Community, Finance, FlowAutomation,
  PersonalAssistant, Entitlement, Invoicing, Payroll) → liputan **60/60** yang kini
  benar, `audit-tenant` exit 0.
- **Pepijat pengasingan sebenar:** `BoardroomFinding` tiada trait `BelongsToTenant` dan
  jadualnya tiada `tenant_id`. Ditambah migration
  `2026_10_28_000100_add_tenant_id_to_boardroom_findings` (backfill daripada sesi induk),
  trait pada model, dan `tenant_id` eksplisit dalam `BoardroomOrchestrator`.

### Alat & ujian

- **T-104** `skrip/jana-baseline.php`: mod baharu `--semak` (dan `--semak=<fail>`),
  satu penjana dua gaya (`tebal`/neutral), CI tidak lagi bergantung pada `sed`/`diff`.
- **T-108** `TeamAiWorkspaceGuardTest` — 7 ujian (token, cascade, idempotensi, had
  50 fail/64 KB/1 MB).
- **T-110** `DocumentTest` tidak lagi menulis secara senyap ke direktori yang tiada
  (`CONTOH_DOKUMEN_OUT`); bunyi "The system cannot find the path specified." hilang.
- **T-109** `skrip/jana-migrasi-hilang.php` — mendedahkan **28 migrasi** yang dijalankan
  pada DB tetapi failnya tiada (`payroll_*`, `pos_*`, `hrms_*`, `booking_*`,
  `marketplace_*`, `sites`, `analytics_*`, …). DB tidak boleh dibina semula dari sifar
  sehingga ini dipulihkan.

### Ditemui, belum selesai

- **T-113** `docs/AUDIT-BASELINE.md`, `CHANGELOG.md`, `TODO.md` dan blueprint kanonik
  **tidak dijejak git** (`?? ai-bijan-saas/docs/…`). Jika terpadam, tiada sejarah.
- **T-109** pemulihan 28 migrasi hilang (pemetaan sudah disediakan, `--tulis` sengaja mati).
- 23 kebenaran katalog tidak digunakan oleh mana-mana laluan.

---

## v9.6 — 27 Oktober 2026

### Dokumentasi & audit

- **Audit induk** `AI-BIJAN-AUDIT-INDUK-LENGKAP.md` ditambah — jurang, risiko,
  pertindihan modul, matriks P0–P3, senarai semak pelepasan.
- **`AUDIT-BASELINE.md` ditambah** — satu-satunya sumber kebenaran angka.
- **Koreksi status `Reseller`** — audit induk pernah menyatakan `SPECIFIED / NOT CODED`
  pada dua tempat, walaupun kod wujud di `app/Modules/Billing/`. kini:
  `IMPLEMENTED / TESTED` (kod) + `BLOCKED` (komersial, ADR-010).
- **Blueprint** — header dinaik ke **v9.6**; nama kanonik
  `AI-BIJAN-MASTER-BLUEPRINT.md` dibina; semua rujukan dalam `docs/` dikemas kini.
- **README utama** — boilerplate Laravel digantikan dengan README sebenar: cara pasang,
  stack, struktur, status `DEVELOPMENT`, dan kotak amaran "belum production-ready".
- **`TODO.md`** — sejarah dipindahkan ke fail ini; seksyen `P1`/`P2` berulang
  disatukan; "Urutan kerja" lapuk (mula `T-01` yang sudah selesai) dikemas kini.
- **Arkib** — `docs/archive/README.md` kini menyatakan `ARCHIVED / NOT AUTHORITATIVE`
  dan memaut blueprint kanonik.
- **`skrip/sisip_bab82.php`** — laluan blueprint dibetulkan (`docs/` bukan akar repo).
  Skrip ini tidak pernah boleh berjalan kerana `bab82_teks.md` tiada.

### Metrik (kiraan statik daripada sistem fail)

| Metrik | Nilai |
|---|---|
| Modul | 59 |
| Migration | 58 |
| Model | 310 |
| Service | 174 |
| Controller | 134 |
| Resource Filament | 65 |
| Widget | 6 |
| Kaedah ujian | ~1,119 (89 fail Feature) |
| Laluan `api/v1` | ~872 |

> Nilai **ujian lulus / assertion / jadual sebenar** memerlukan PHP — lihat
> `AUDIT-BASELINE.md` §1.1.

### Angka lama yang dibetulkan

| Metrik | Nilai lama (salah) | Nilai sebenar |
|---|---:|---:|
| Laluan API | 1,518 / 1,388 | **~872** |
| Ujian | 962 / 963 / 1,009 / 1,059 | **~1,119 kaedah** |
| Jadual | 346 / 366 | **368** (kiraan migration) |
| Migration | 49 / 55 | **58** |
| Resource Filament | 66 | **65** |
| Model | 292 / 300 | **310** |
| Service | 166 / 170 | **174** |
| Controller | 127 / 128 | **134** |

---

## v9.5 — 26 Oktober 2026

- **Modul `Billing` (Reseller)** ditambah — `app/Modules/Billing/`, 9 jadual,
  6 laluan API, 32 ujian, integrasi CHIP Collect.
  Kos token disahkan: RM5/RM8/RM10/RM50/RM100 (margin 90.9% seragam).
  Dakwaan lama "kos RM75–230/pelanggan" **dibuang** sebagai salah.
- **Audit pepijat** — 154 laluan berganda dibuang (5 modul mendaftar prefix dua kali);
  `semak-dokumen.php` kini memantau `laluan_berganda` + `laluan_wildcard`.
- **Modul `Billing` = 59 modul.**

---

## v9.4 — 26 Oktober 2026

- **ADE (Agentic Development Environment)** dikodkan — `app/Modules/ADE/`,
  6 jadual, 24 laluan API, 4 resource Filament, 48 ujian.
  4 fasa: Skills Engine, Worktree Isolation, Sub-Agent Coordinator, Review Surface.
- Jumlah: 56 modul.

---

## v9.3 — 26 Oktober 2026

- **B-Receipt & B-Statement** (BAB 90) — 9 jadual, 19 laluan API.
  WhatsApp **Business Platform** sahaja; consent + opt-out dihormati.
- **B-Vision AI** (BAB 91) — 15 jadual, 16 laluan API. 8 kawalan keselamatan
  wajib termasuk SSRF protection.

---

## v9.2 — 26 Oktober 2026

- **Modul `Expense`** (BAB 89) — 7 jadual, 33 laluan API, 48 ujian.
  3 lapisan keselamatan: staff → draf sahaja, kelulusan manusia wajib,
  staff tak boleh lulus sendiri.

---

## v9.1 — 26 Oktober 2026

- **Audit penuh B-POS System** (BAB 88): 109 migration, 61 model, 30 service,
  73 controller. 6 jurang kritikal, 9 isu keselamatan (terkritikal: tiada
  `tenant_id`, `legacySingleOutletBypass` lalai TRUE).
- **Keputusan: B-POS kekal produk berasingan** — ciri dipindah satu per satu
  (T-76 → T-84).

---

## v9.0 — 26 Oktober 2026

- **9 modul baharu** (BAB 86-87): `Orders`, `Seo`, `ContentStudio`, `Reminder`,
  `BusinessIntel`, `WorkflowStudio`, `MediaEditor`, `LiveSupport`, `Community`,
  serta `ServiceOS`.
- Jumlah: 55 modul, 331 jadual, 829 ujian lulus.

---

## v8.9 — 26 Oktober 2026

- **Pelaksanaan Idea Audit** (BAB 85): `Cod`, `Fleet`, `ProofOfValue`,
  `McpStudio`, `AgentStudio`. 25 jadual, 54 laluan API, 168 ujian baharu.
- Jumlah: 50 modul, 486 ujian lulus.

---

## Versi lebih lama

Blueprint v2.x–v8.x berada di [`archive/blueprint-lama/`](archive/blueprint-lama/)
(`ARCHIVED / NOT AUTHORITATIVE`). Dokumentasi B-POS yang tersalah letak berada
di [`archive/features/`](archive/features/).
