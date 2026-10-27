# CHANGELOG — AI BIJAN SaaS

Sejarah perubahan status platform. Fail ini menyimpan **semua rekod sejarah** yang
sebelum ini berselerak dalam `TODO.md`, supaya `TODO.md` boleh menjadi senarai
**kerja belum siap** yang jernih.

> **Nombor versi** di sini ialah *versi blueprint* (bukan versi aplikasi).
> Rujukan blueprint: [`AI-BIJAN-MASTER-BLUEPRINT.md`](AI-BIJAN-MASTER-BLUEPRINT.md).

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
