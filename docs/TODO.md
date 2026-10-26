# TODO — AI BIJAN SaaS

**Dikemas kini:** 26 Oktober 2026
**Baseline ujian:** **962 lulus / 0 gagal / 4 dilangkau (5,033 assertions)**
**Sistem:** 58 modul · 346 jadual · 1,518 laluan API · 66 resource Filament · 212 kebenaran

> Fail ini menyenaraikan kerja yang **belum siap** dan **belum bermula** sahaja.
> Kerja yang sudah siap tidak disenaraikan.
>
> **Sahkan baseline:** `php artisan test` · `php skrip/semak-dokumen.php`
> **Dokumen sistem lengkap:** `docs/SISTEM-AI-BIJAN.md`
>
> ⚠️ Angka di dalam bahagian sejarah (cth "486 ujian", "314 lulus") merujuk keadaan
> **pada masa entri itu ditulis** — bukan keadaan semasa.

---

## Status Ringkas

> ⚠️ **Jadual ini disahkan semula 26 Oktober 2026.** Angka lama (39 modul / ~1,015 endpoint /
> 314 ujian / blueprint v9.0) di bawah adalah **sejarah** — jangan guna sebagai status semasa.

| Ukuran | Nilai semasa | Sahkan dengan |
|---|---|---|
| Modul (`app/Modules/`) | **58** (+1 spesifikasi: `Reseller`) | `(Get-ChildItem app\Modules -Directory).Count` |
| Jadual pangkalan data | **346** | `php skrip/semak-dokumen.php` |
| Laluan API (`api/v1`) | **1,518** | `php artisan route:list --path=api/v1` |
| Semua laluan | **1,795** | `php artisan route:list` |
| Ujian | **963 lulus · 4 dilangkau · 0 gagal (5,034 assertion)** | `php artisan test` |
| Migrasi | **49 fail** | `php skrip/semak-dokumen.php` |
| Resource Filament | **66** (+6 widget + halaman Tetapan) | `(Get-ChildItem app\Filament\Resources -Directory).Count` |
| Kebenaran | **212** | `skrip/sync-perm.php` |
| Model · Service · Controller | **292 · 166 · 127** | `php skrip/semak-dokumen.php` |
| DB | SQLite (dev) · MySQL disokong (`skrip/tukar_db.ps1`) | — |
| AI Gateway | **Mod simulasi** (`AI_GATEWAY_LIVE=false`) — ujian live sedia, hanya perlu kunci | — |
| Blueprint | **v9.5 — 103 bab / 27 PART** | `docs/AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md` |
| Advisori keselamatan | **0** | `composer audit` |
| Laravel · PHP | **12.70.1 · 8.2.12** | `php artisan --version` |
| Kawalan versi | **git + CI** (baseline `v9.5-baseline`) | `git log --oneline -1` |

### Sejarah (JANGAN guna sebagai status)

| Tarikh | Modul | Endpoint | Ujian | Blueprint |
|---|---|---|---|---|
| ~BAB 82 | 39 | ~1,015 | 314 | v9.0 |
| BAB 86-87 | 54 | ~1,300 | 781 | v9.0 |
| BAB 89 | 55 | 1,345 | 829 | v9.2 |
| BAB 92 | 56 | 1,460 | 962 | v9.4 |
| **Kini** | **58** | **1,518** | **963** | **v9.5** |

---

## P0 — Blocker (SELESAI)

### ~~T-01 Konflik dua controller `projects`~~ ✅ SELESAI
- **Pembaikan:** Blok route modul Projects dibuang dari `routes/api.php` (56 baris).
  Modul kini hanya didaftar oleh `ProjectsServiceProvider` di prefix `pm/`.
- **Hasil:** `OperationsTest` 5/5 lulus, suite penuh 128/128.

### ~~T-02 Migration biometrik pending~~ ✅ SELESAI
- `php artisan migrate` dijalankan — 4 jadual biometrik dicipta.

---

## P1 — Modul tanpa backend API

### ~~T-03 B-Helpdesk — controller + route + ujian~~ ✅ SELESAI
- `Api\V1\HelpdeskController` **sudah lengkap** (15 kaedah) — tiada controller baharu diperlukan.
- Route: `helpdesk/*` + portal awam `public/{slug}/helpdesk/*`
- Ujian baharu: `HelpdeskTest` — 9 kes lulus (tiket, SLA, balasan, nota dalaman,
  tiket ditutup, kategori/artikel, tenant isolation, RBAC, portal awam)

### ~~T-05 B-Manufacturing — controller + route + ujian~~ ✅ SELESAI
- `Api\V1\ManufacturingController` **sudah lengkap** (9 kaedah) — BOM, work order,
  complete (termasuk `qty_reject`), cancel, saleDeduct.
- Ujian baharu: `ManufacturingApiTest` — 7 kes lulus
- **Nota:** produk siap mesti `is_stock = true` untuk hasil masuk stok.

### ~~T-04 B-Inventory — controller + route + ujian~~ ✅ SELESAI
- `ItemController` + `WarehouseController` dalam `app/Modules/Inventory/Http/Controllers/`
- Route: `inventory/catalog/*` dan `inventory/depots/*` (prefix `depots` elak
  pertindihan dengan `inventory/warehouses` milik Api\V1)
- Ujian: `InventoryCatalogTest` — 10 kes lulus (CRUD, SKU unik, nyahaktif vs padam,
  lejar, baki, laporan pesanan semula, tenant isolation, RBAC)
- **Belum:** `StockCountController`, `ReorderController` (laporan reorder sudah ada
  dalam `ItemController::reorder`)

---

## P2 — Infrastruktur produksi

### T-06 SQLite → MySQL — 🟡 ALAT SEDIA, SERVER TIADA
- **Siap:**
  - Audit keserasian 52 migrasi → `php skrip/semak_mysql.php`
    (0 SQL khusus SQLite, 0 indeks melebihi 3072 bait)
  - Skrip tukar DB selamat + sandaran `.env` → `pwsh -File skrip/tukar_db.ps1 -Ke mysql`
- **Belum:** XAMPP MySQL tidak berjalan pada mesin ini. Mula `C:\xampp\mysql\bin\mysqld.exe`
  atau servis MySQL, kemudian jalankan skrip di atas.
- **Siap apabila:** `migrate:fresh --seed` + suite penuh lulus pada MySQL

### T-07 AI Gateway live — 🟡 UJIAN SEDIA, KUNCI TIADA
- **Siap:**
  - Skrip status/aktif → `pwsh -File skrip/tetapan_ai.ps1` (kunci tidak diterima sebagai parameter)
  - `AiGatewayLiveTest` — 3 ujian, auto-langkau tanpa kunci (CI kekal hijau)
- **Belum:** `AI_GATEWAY_KEY` mesti diisi manual dalam `.env` oleh pemilik.
- **Siap apabila:** `pwsh -File skrip/tetapan_ai.ps1 -Aktifkan` + `php artisan test --filter=AiGatewayLiveTest` lulus

### ~~T-08 Qdrant vector search~~ ✅ SELESAI (fallback tempatan)
- Migration: `2026_10_24_000300_create_knowledge_vector_tables.php`
  (`knowledge_documents`, `knowledge_chunks`)
- Model: `KnowledgeDocument`, `KnowledgeChunk`
- Servis:
  - `EmbeddingService` — `hash-v1` (hashing trick + L2 normalisasi), 256 dim
  - `VectorStoreService` — indeks, carian kosinus, statistik
- Controller: `KnowledgeController` — 6 endpoint (`knowledge/*`)
- Integrasi: `ContextBuilderService::vectorSearch()` kini guna VectorStoreService
  (graceful degradation jika tiada dokumen)
- Ujian: `KnowledgeVectorTest` — 14 kes lulus

**Had yang diakui:** `hash-v1` menangkap pertindihan perkataan, **bukan** makna
semantik sebenar. Untuk produksi, tukar ke model embedding API — antara muka
`EmbeddingService::embed()` kekal. Setiap chunk menyimpan `embedding_model`
supaya vektor model berbeza tidak dicampur.

**Penemuan keselamatan:** `personal_ai.configure` asalnya diberi kepada
`staff`, `auditor`, `cashier` — bermakna sesiapa boleh tulis ke pangkalan
pengetahuan AI (pencemaran konteks). **Diketatkan** kepada pengurusan sahaja.

---

## P3 — Integrasi luaran (belum bermula)

| ID | Integrasi | Status | Blocker |
|---|---|---|---|
| T-09 | Payment gateway (ToyyibPay/FPX) | ❌ | Perlu akaun merchant |
| T-10 | WhatsApp Cloud API | ❌ | Perlu Meta Business approval |
| T-11 | Google Ads / Meta Ads | ❌ | Perlu developer approval |
| T-12 | LHDN e-Invois | ❌ | Perlu sertifikat + sandbox LHDN |
| T-13 | Kurier (J&T/Pos Laju) | ❌ | Perlu akaun API |
| T-14 | Gmail / Google Calendar | ❌ | Perlu OAuth consent screen |

---

## P4 — CCTV / B-Vision

### ~~T-15 B-Vision Fasa 1~~ ✅ SELESAI
- Migration: `2026_10_24_000200_create_cctv_tables.php` —
  `cctv_cameras`, `cctv_events`, `cctv_retention`, `cctv_view_logs`
- Model: `app/Modules/Vision/Models/Cctv{Camera,Event,Retention,ViewLog}.php`
- Servis: `VisionIngestService` — HMAC, ingest, retensi, ringkasan
- Controller: `VisionController` — 15 endpoint
- Route: `vision/*` (tenant) + `vision/ingest/{id}` (webhook HMAC)
- Permission: `vision.view|manage|audit`
- Ujian: `VisionTest` — 14 kes lulus

**Ciri keselamatan yang disahkan ujian:**
- URL RTSP disimpan **berenkripsi** dan tidak pernah dipulangkan ke API
- Webhook **wajib HMAC-SHA256** — tandatangan salah → 401
- Jenis `face_match`/`identity` **ditolak 422** (mesti guna pagar biometrik 56C.6A)
- Kamera tidak dipadam, hanya dinyahaktifkan (peristiwa lama kekal)
- Setiap tontonan direkod dalam `cctv_view_logs`

**Belum (Fasa 3+):** live view HLS/WebRTC, klip video sebenar, papan pemuka
berbilang cawangan, servis Vision berasingan (Frigate + YOLO, di luar Laravel).

### ~~Fasa 2 B-Vision: amaran~~ ✅ SELESAI
- `VisionAlertService` — peristiwa kritikal sentiasa dihantar, peristiwa biasa
  perlu keyakinan ≥ 0.8, had kadar 10/jam/kamera
- Integrasi dalam `VisionIngestService` — **amaran tidak pernah menggagalkan
  ingestion** (semua ralat ditelan dan dilog)
- Endpoint: `GET /api/v1/vision/alerts/pending?minutes=30`
- Config: `services.vision.*`
- Ujian: `VisionAlertTest` — 8 kes lulus
- **Belum:** pemacu WhatsApp/SMS sebenar (menunggu B-Notify). Titik sambungan
  sudah disediakan melalui `alert_via_notify`.

---

## P5 — Ciri blueprint belum dikodkan

| ID | Ciri | BAB | Status |
|---|---|---|---|
| T-17 | B-Document AI OCR + ekstraksi | 59 | Controller tiada (dipindah ke `document-ai/`) |
| T-18 | Learning Signal Layer | 72 | Tiada |
| T-19 | Design System (token, komponen) | 73-78 | Tiada — UI guna Blade biasa |
| T-20 | Email Intelligence & Document Intake | 79 | Tiada |
| T-21 | Pengambilan Pekerja (CV, temu duga) | 80 | Tiada |
| ~~T-22~~ | ~~Observability & SLO~~ | 61 | ✅ **SIAP** — `/health`, `/ready`, `/metrics` |
| ~~T-23~~ | ~~AI Safety (prompt injection)~~ | 62 | ✅ **SIAP** — `PromptGuard` + 16 ujian |
| ~~T-24~~ | ~~Competitive Advantage & Business Growth Engine~~ | 81 | ✅ **SIAP** — `docs/BUSINESS-GROWTH-ENGINE.md` |

### ~~T-16 B-Payroll statutory export~~ ✅ SELESAI
- `StatutoryExportService` + `PayrollExportController`
- Fail: EPF, SOCSO, EIS, PCB, bank (IBG), ringkasan — semua CSV UTF-8 BOM
- Route: `payroll/runs/{id}/export` dan `payroll/runs/{id}/export/{jenis}`
- Setiap fail ada checksum SHA-256 (12 aksara) dalam nama fail + header `X-Checksum-Sha256`
- Hanya run `approved`/`finalized` boleh dieksport
- Ujian: `PayrollExportTest` — 9 kes lulus
- ⚠️ **Belum:** format rasmi KWSP/PERKESO/LHDN berubah setiap tahun — bandingkan
  dengan templat terkini sebelum penyerahan sebenar. Format IC penuh juga perlu
  disemak dari sudut PDPA (pertimbangkan topeng IC).

### ~~T-22 Observability~~ ✅ SELESAI
- `HealthController` — 3 endpoint tanpa auth
  - `/health` — liveness (tiada I/O)
  - `/ready` — DB, cache, storan, queue, migrasi pending → 503 jika gagal
  - `/metrics` — tenant, queue, latency DB, versi PHP/Laravel
- Tiada pendedahan kredensial atau laluan fail
- Ujian: `HealthTest` — 5 kes lulus

### ~~T-23 AI Safety~~ ✅ SELESAI
- `PromptGuard` — 12 corak input, 4 corak output, pembungkus data
- Integrasi dalam `GatewayService::call()` sebagai langkah **0** — sebelum feature flag
- Config: `ai_gateway.safety.{scan_input,scan_output,max_patterns_before_block}`
- Ujian: `AiSafetyTest` — 16 kes lulus (termasuk 6 ujian positif palsu)

**Corak yang dikesan:** abaikan arahan, bocor prompt, tukar peranan, jailbreak,
kunci rahsia dalam prompt, SQL mentah, rujukan tenant lain, pintas kebenaran,
paksa kelulusan, suntikan token khas, suntikan markdown.

**Had yang diakui:** ini lapisan pertama sahaja. Pertahanan sebenar bergantung pada
tool allowlist + permission check pada setiap panggilan, bukan prompt.

### ~~T-24 Business Growth Engine~~ ✅ SELESAI
- `docs/BUSINESS-GROWTH-ENGINE.md` — 9 kelebihan dengan status kod sebenar
  (✅ 1 siap, 🟡 7 separa, ⛔ 1 tiada), keperluan teknikal, 5 fasa, prinsip jualan
- **Tidak** mendakwa ciri yang belum dibina
- Rujukan `vervv.io` dikekalkan sebagai belum disahkan

---

## Alat & dokumentasi

### ~~Skill `aibijan-coding`~~ ✅ SELESAI
- `.github/skills/aibijan-coding/SKILL.md` — 7 peraturan wajib, nyahpepijat pantas,
  aliran kerja modul, realiti produk, senarai semak
- `.github/skills/aibijan-coding/references/module-procedure.md` — templat kod
- `.github/skills/aibijan-coding/references/gotchas.md` — pepijat sebenar
- `.github/skills/aibijan-coding/references/security-rules.md` — isolasi tenant,
  biometrik, AI safety, audit
- `.github/skills/aibijan-coding/references/product-strategy.md` — keutamaan produk,
  kesilapan mesti dielak, metrik
- Muncul sebagai `/aibijan-coding`; dimuatkan automatik apabila kerja pada modul

### ~~Analisis kompetitif & cadangan~~ ✅ SELESAI
- `docs/ANALISIS-KOMPETITIF.md` — analisis jujur berpijak kod; skor 6.3/10
  (seri StoreHub 6.3, sedikit di belakang Bukku 6.6)
  - Harga pesaing **disahkan** 24 Okt 2026: StoreHub RM122–471, Bukku RM0–135,
    Zoho RM148/pekerja, Odoo USD13.50–25.50
  - **Ancaman gabungan:** Bukku RM146 + StoreHub RM122 = RM268 ≈ AI BIJAN RM288
  - e-Invois kini **jadual stakes**, bukan pembeza
  - 3 tiang pembeza: pematuhan HR, AI bertindak berpagar, tanpa had pengguna
- `docs/CADANGAN-IMPAK-TINGGI.md` — 15 cadangan + urutan 17 minggu
- `docs/PETA-PESAING-34-MODUL.md` — peta pesaing untuk 34 modul
  - 400+ pesaing dikenal pasti merentas 34 kategori
  - Setiap modul: 10 pesaing utama + senarai global
  - 15 idea terbaik untuk digabung (dengan sumber pesaing)
  - Idea yang **tidak** patut dikejar
  - Data disahkan (✅) vs belum disahkan (⚠️) ditanda jelas

### ~~Blueprint BAB 81~~ ✅ SELESAI
- `AI-Bijan/AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md` naik ke **v8.4**
- PART XVII — KELEBIHAN KOMPETITIF & ENJIN PERTUMBUHAN (BAB 81), 14 seksyen:
  9 kelebihan dengan status kod, rantaian silang-modul, 4 kelebihan unik,
  15 idea pesaing, idea jangan dikejar, analisis kos, skor kompetitif,
  keutamaan 3 fasa, metrik, prinsip jualan, kesilapan posisi
- Indeks blueprint: 89→90 bab, 16→17 PART
- Rujukan skill: `.github/skills/aibijan-coding/references/competitive-advantage.md`

### Alat infrastruktur
- `skrip/semak_mysql.php` — audit migrasi untuk MySQL
- `skrip/tukar_db.ps1` — tukar .env SQLite↔MySQL dengan sandaran
- `skrip/tetapan_ai.ps1` — status/aktif AI_GATEWAY_LIVE

---

## Urutan kerja yang disyorkan

```
T-01 (projects)  ← mula di sini, blocker
   ↓
T-02 (migrate)   ← 1 arahan sahaja
   ↓
T-04 (Inventory) → T-03 (Helpdesk) → T-05 (Manufacturing)
   ↓
T-06 (MySQL)     ← sebelum integrasi luaran
   ↓
T-07 (AI live) → T-08 (Qdrant)
   ↓
P3 integrasi → P4 CCTV → P5 ciri
```

---

## P2 — BAB 82: B-Executive AI (DIIMPLEMENTASIKAN 2026-10-24)

> Blueprint v8.5 · PART XVIII · 5 modul baharu · 94 ujian · 314 lulus keseluruhan.

### ~~T-25 Modul Prospecting (Fasa 1)~~ ✅ SELESAI
- 6 jadual: `prospect_sources`, `prospect_lists`, `prospects`, `prospect_audit_logs`, `scraping_jobs`, `scraped_pages`
- `ConsentService` — 4 syarat consent (status + bukti + tempoh + channel)
- `DedupeService` — normalisasi Gmail/telefon MY + SHA-256 dedupe
- `PublicScraperService` + `RobotRules` — robots.txt aware, rate limit, URL dihalang
- `CsvImporterService` — mapping kolum BM/EN, consent via kolum atau `assume_consent`
- `ProspectScoringService` — 0–100, label panas/suam/sejuk
- 11 endpoint · 24 ujian lulus

### ~~T-26 Modul Blast Engine (Fasa 2)~~ ✅ SELESAI
- 6 jadual: `blast_campaigns`, `blast_messages`, `blast_suppressions`, `blast_unsubscribes`, `blast_deliverability`, `blast_webhook_events`
- 4 pagar penghantaran: consent, penindihan, had kadar, kelulusan kempen
- `RateLimiterService` — harian tenant + harian domain + sekejap per channel
- `DeliverabilityService` — auto-pause bila bounce > 5% atau complaint > 0.1%
- `OptOutService` — RFC 8058 one-click, suppression list kekal
- Provider adapter: `EmailProvider` (SMTP/Mailgun), `SmsProvider` (Twilio), `WhatsAppProvider` (Meta WABA)
- 15 endpoint · 20 ujian lulus

### ~~T-27 Modul Sales Copilot (Fasa 3)~~ ✅ SELESAI
- `ReplyAnalyzerService` — 8 kategori + logik negasi ("Not interested" ≠ interested)
- `DealBriefService` — brief 1 halaman (identiti, consent, skor, isyarat, timeline, cadangan)
- `FollowUpNudgerService` — hanya prospect dengan consent sah
- 5 endpoint · 16 ujian lulus

### ~~T-28 Modul Media Studio (Fasa 4)~~ ✅ SELESAI
- 5 jadual: `media_assets`, `media_jobs`, `media_transcripts`, `media_understandings`, `media_personas`
- `VideoUnderstandingService` — pipeline 10 langkah (transcode, keyframe, ASR, VLM, ekstraksi, index)
- `ConsentGateService` — persona wajib disahkan; biometrik guna pagar BAB 56C.6A
- `MediaModerationService` — 4 kategori terlarang + watermark "AI-generated"
- `MediaRetentionService` — auto-purge retensi
- 12 endpoint · 17 ujian lulus

### ~~T-29 Modul B-Executive AI (Fasa 5)~~ ✅ SELESAI
- 3 jadual: `executive_preferences`, `executive_briefings`, `executive_approvals`
- `AutonomyService` — 5 aras (0–4) + hierarki tindakan + senarai sentiasa-kelulusan
- `BriefingService` — briefing harian dari pipeline, KPI, prospek panas, risiko, blast
- 7 endpoint · 17 ujian lulus

### ~~T-30 Baiki placeholder blast sedia ada~~ ✅ SELESAI
- `Marketing/Services/WorkflowService.php` — `executeSendEmail/Sms/Whatsapp` kini panggil provider sebenar
- `Marketing/Services/SequenceService.php` — `executeStep` kini hantar + rekod `blast_messages`
- `Marketing/Services/AnalyticsService.php` — `getAttributionModel` kini guna data blast sebenar (5 model)

### ~~T-31 Scheduler & dokumentasi~~ ✅ SELESAI
- Scheduler: `blast-dispatch` (1 min), `prospecting-rescore` (10 min), `media-retention-purge` (03:30), `executive-briefing-daily` (07:30)
- `docs/PROSPEK-BLAST-CLOSE.md` — panduan penuh
- `docs/KAJIAN-FINDS-PROSPECT-BLAST-CLOSE.md` — dikemas kini dengan status sebenar

---

## P2 — BAB 82: Perkara Perlu Tindakan Pengguna

### T-32 Konfigurasi provider blast (perlu akaun)
- [ ] SMTP/Mailgun: set `BLAST_EMAIL_MODE=smtp`, `BLAST_FROM_ADDRESS`, `BLAST_REPLY_TO` (DNS SPF/DKIM/DMARC wajib)
- [ ] Twilio: `TWILIO_SID`, `TWILIO_TOKEN`, `TWILIO_FROM` — **perLesen WASTI MCMC** untuk SMS blast komersial MY
- [ ] Meta WhatsApp Cloud API: `WHATSAPP_ACCESS_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID` + **template diluluskan Meta**
- [ ] HeyGen/Synthesia: `HEYGEN_API_KEY` / `SYNTHESIA_API_KEY` untuk penjanaan video
- Rujukan: `config/services.php` → blok `blast`

### T-33 AI Gateway live (BAB 21) — prerequisite untuk video understanding
- [ ] Set `AI_GATEWAY_LIVE=true` + `AI_GATEWAY_KEY`
- [ ] Tambah model routing: `video_understanding`, `video_generation`, `image_description`
- [ ] Tambah sokongan `attachments` (multimodal) dalam `GatewayService::call()`
- Tanpa ini, pemahaman video guna transkripsi sahaja (degrade graceful)

### T-34 Pagar consent — audit pensyelamatan
- [ ] Semak `blast_unsubscribes` + `prospect_audit_logs` bulanan
- [ ] Sahkan `consent_evidence` tidak kosong untuk semua prospect `granted`
- [ ] Tetapkan tempoh retensi consent mengikut dasar tenant

### T-35 UI untuk 5 modul baharu
- [ ] Papan pemuka Command Centre (Executive AI)
- [ ] Skrin senarai prospect + import CSV
- [ ] Skrin kempen blast + preview + kelulusan
- [ ] Skrin persona video + moderasi
- **Nota:** Backend gred produksi, UI masih gred demo (isas 2/10) — ini punca utama belum mendahului pesaing.

---

## P1 — BAB 83: 3 Syarat Wajib Untuk Platform Universal (BELUM DIBINA)

> Blueprint v8.6 · PART XIX · Audit: `docs/AUDIT-PESAING-LANJUTAN.md` (15 pesaing baharu)
> Skor kompetitif turun 6.3 → 5.0 kerana 3 dimensi baharu diekspose.

### T-36 ⭐ B-Migration Wizard (JURANG #1)
- [ ] Modul `app/Modules/Migration/` + migration jadual
- [ ] Import CSV, XLSX, JSON, XML
- [ ] Preset sumber: Bukku, StoreHub, Excel/Sheets, Xero, SQL Account, Shopee, Lazada, TikTok Shop
- [ ] Simpan profil import (guna semula satu klik) — sumber: Dolibarr
- [ ] Dry-run simulator (tidak ubah data) — sumber: Dolibarr
- [ ] Mod import: `insert` / `update`
- [ ] Guna `DedupeService` sedia ada (BAB 82)
- [ ] Rollback: snapshot sebelum import
- [ ] Post-migration checklist dipacu `IndustryPacks`
- [ ] Export penuh semua jadual tenant (CSV + JSON) — "no vendor lock-in"
- [ ] Export tersedia di SEMUA pelan (bukan upsell)
- **Sasaran:** import 10,000 rekod < 5 minit tanpa ralat

### T-37 ⭐ UI 2 → 7 (SEMUA MODUL)
- [ ] `pos.blade.php` 7.1 KB → 800–1,500 baris + mod offline
- [ ] Papan pemuka Command Centre (Executive AI)
- [ ] Skrin senarai prospect + import CSV wizard
- [ ] Skrin kempen blast + preview + kelulusan
- [ ] Skrin persona video + moderasi
- [ ] Skrin migrasi (B-Migration Wizard)
- [ ] 10 halaman onboarding per industri (seperti Help Scout)
- **Sasaran:** purata skor 5.0 → 6.3 (mendahului Zoho/Dolibarr)

### T-38 ⭐ AI Metrics Dalam Jualan
- [ ] Endpoint `ai-resolution-rate` — % tiket diselesaikan AI
- [ ] Endpoint `first-response-gain` — % tambahan balasan
- [ ] Endpoint `csat-uplift` — perbandingan industri
- [ ] Endpoint `hours-saved` — jam manual dielakkan
- [ ] Paparan metrik di UI (helpdesk + executive AI)
- **Peraturan:** tiada ciri AI diiklankan tanpa satu metrik boleh diukur
- **Sasaran:** ≥50% resolve, ≥40% first-response gain, ≥15% CSAT uplift

### T-39 B-Agent Studio + MCP Server
- [ ] UI builder ejen visual (persona, tools, knowledge, autonomy, kelulusan)
- [ ] Test run + jejak keputusan sebelum publish
- [ ] MCP server endpoint `api/v1/mcp` — tools + resources + prompts
- [ ] Plugin marketplace isi (model provider, tool, MCP, data source, workflow template)

### T-40 Agentic Commerce
- [ ] B-Site agentic storefront (AI sneaker bounce → chat → checkout)
- [ ] Order engine: semak stok → harga → pesanan → e-Invois LHDN → bayar → jadual penghantaran
- [ ] Sokong Shopify Universal Commerce Protocol (UCP)
- [ ] Audit: setiap order AI log (model, prompt, item, harga, kelulusan)
- [ ] Consent blast ≠ consent checkout (pagar berbeza)

### T-41 AI-First ESignature + Local Presence
- [ ] `DocumentAI` — klasifikasi auto jenis dokumen + cadangan kedudukan medan
- [ ] Ringkasan kontrak + tanya-jawab untuk tandatangan
- [ ] Pre-send verification penerima
- [ ] Hantar permintaan tandatangan via WhatsApp (guna `Blast`)
- [ ] Conditional logic + bulk send + auto-calc values
- [ ] Sync Google Business Profile / Facebook Page / Instagram / TikTok Business

### T-42 Pematuhan Enterprise
- [ ] SOC 2 Type II — prosedur + bukti
- [ ] ISO 27001 —ISM pengurusan keselamatan maklumat
- [ ] SSO/SAML untuk tenant besar
- [ ] Audit log terpusat (BAB 61)
- **Sebab:** tanpa ini, tiada syarikat besar boleh beli

---

## P1 — BAB 84: Incorporation 200 Apps + 50 OSS (BELUM DIBINA)

> Blueprint v8.7 · PART XX · Audit: `docs/REVERSE-ENGINEERING-200-APPS-50-OSS.md`
> 10 idea tertinggi daripada 200 apps (Zoho/Odoo/ServiceTitan/Shopify) + 50 OSS.

### T-43 ⭐ FilamentPHP UI Builder (JURANG #2)
- [ ] `composer require filament/filament` + konfigurasi multi-tenant
- [ ] Rujukan: `aureuserp/aureuserp` (MIT) — FilamentPHP untuk panel admin
- [ ] Bina 5 skrin teratas: POS, Command Centre, Migration, Blast, Prospect
- [ ] `pos.blade.php` 7.1 KB → 800–1,500 baris
- **Sasaran:** UI 2 → 7, purata skor 5.0 → 6.3

### T-44 ⭐ B-Migration Wizard + MCP + AI Import
- [ ] Import CSV, XLSX, JSON, XML
- [ ] 10 sumber preset: Bukku, StoreHub, Excel/Sheets, Xero, QuickBooks, Shopify, CSV, JSON, XML, SQL
- [ ] **AI auto-mapping** lajur (dari Matrixify: "MCP & AI tools")
- [ ] Simpan profil import + dry-run simulator
- [ ] Export penuh semua jadual (CSV + JSON) — tersedia semua pelan
- [ ] MCP server untuk import — `api/v1/mcp`
- **Rujukan:** Dolibarr Import Wizard (GPL — rujukan reka bentuk sahaja), Matrixify (1,815 review)

### T-45 ⭐ AI Dashboard Builder
- [ ] Tenant bina dashboard sendiri tanpa coder
- [ ] Chart + KPI + filter + drill-down
- [ ] Multi-sumber data + AI generation (Ksolves pattern)
- [ ] Rujukan: `dynamic_dashboard_ai_nexgen` (FREE, 1,107 dl), `ks_dashboard_ninja` ($582, 3,136 dl)

### T-46 ⭐ Cart Recovery WhatsApp AI Agent
- [ ] Abandoned cart recovery via WhatsApp
- [ ] Consent gate WAJIB (blast consent ≠ cart consent)
- [ ] AI agent jawab soalan, tawarkan diskaun
- [ ] Rujukan: Dondy (828 review), Shopify Messaging (4,721 review)

### T-47 ⭐ Connector Pack 20 Sistem
- [ ] Ranking: Shopee, Lazada, TikTok Shop, Grab, MyGDX, SST, e-Invois LHDN, FPX
- [ ] 10 SaaS: QuickBooks, Xero, Sage, NetSuite, Salesforce, HubSpot, Stripe, Square, Google Sheets, Zapier
- [ ] 5 telekom: Twilio, WhatsApp WABA, RingCentral, Mailgun, SendGrid
- [ ] Pattern: `Migration`/`Integrations` modul dengan adapter (seperti `Blast` providers)

### T-48 Visual Automation Canvas
- [ ] Node-based canvas (bukan JSON) — `FlowAutomation`
- [ ] Nodes: trigger, condition, delay, action, AI, kelulusan
- [ ] Rujukan: Shopify Flow (15,008 review — #1)

### T-49 AI CRO — "Find Leaks, A/B Test"
- [ ] Analisis funnel + kesan rate
- [ ] Cadangan ujian A/B automatik
- [ ] `Analytics`/`Site`
- [ ] Rujukan: PageFly AI (5,917 review)

### T-50 Receipt ESC/POS + Kitchen Display
- [ ] Cetak resit thermal (ESC/POS)
- [ ] Kitchen display system
- [ ] Markdown-based resit
- [ ] `POS`
- [ ] Rujukan: `receiptline/receiptline` (MIT)

### T-51 Compliance Per Negara (Expand SEA)
- [ ] SST Malaysia (e-Invois LHDN v2)
- [ ] Indonesia (NpWP, e-Faktur) — `lakasir` (MIT) sudah ada Bahasa Indonesia
- [ ] Thailand (PDPA, VAT) · Philippines (BIR, 1688) · Vietnam (e-Invoice)
- [ ] `IndustryPacks` — `ComplianceProfile` per negara

### T-52 OSS Incorporation — 20 Tier 1
- [ ] Rujukan architectures: liteerp (MIT), open-mercato (MIT), astronaut-agent (Apache-2.0)
- [ ] Incorporate pattern: receiptline (MIT), grocy (MIT), carbon (MES+MCP), WebErpMesv2 (Laravel+e-invoicing)
- [ ] **WAJIB** buat `THIRD-PARTY-NOTICES.md` dengan lesen + copyright asal
- [ ] **LARANG** salin kod dari AGPL/GPL (twenty, nocobase, openblocks, steedos, budibase, krayin, Dolibarr, espocrm, ever-gauzy)
- [ ] Audit automatik: `composer licenses` + scan fail lesen dalam CI

### T-53 MCP Server + Agent Harness
- [ ] `api/v1/mcp` — tools + resources + prompts
- [ ] Agent harness pattern (Archon: "deterministic & repeatable AI coding")
- [ ] `AIAgents`
- [ ] Rujukan: Odoo MCP (9,094 dl), Dify publish, carbon MCP, moqui-mcp

### T-54 ⭐ COD (Cash on Delivery) — Jurang Malaysia ✅ SELESAI (BAB 85 / Idea #6)
- [x] COD form + status tracking (`pending → confirmed → dispatched → delivered → collected`)
- [x] Tetapan COD: caj tambahan, had min/max, whitelist kurier, negeri dikecualikan
- [x] Semak alamat MY (panjang, nombor jalan, poskod 5 digit, telefon `60/01`)
- [x] Rekod kutipan (separa/penuh) + analysis kegagalan + reconciliation
- [x] Modul `Cod` · 4 jadual · 11 laluan `/api/v1/cod`
- **Rujukan:** Shopify EasySell COD Form (4.9, 989 review) — **tiada pesaing MY**

### T-55 ⭐ Metrik Hasil Per Modul (ServiceTitan Pattern) ✅ SELESAI (BAB 85 / Idea #5)
- [x] Katalog 8 modul × 2–3 metrik (inventory, sales, marketing, hr, finance, support, logistics, cod)
- [x] Bacaan harian + auto-kira perubahan % + baseline (sebelum vs selepas)
- [x] Sasaran (`target_value` + `direction` up/down) + status `sasaran_tercapai`
- [x] Verifikasi metrik + eksport bukti jualan (draft → generated → sent)
- [x] Modul `ProofOfValue` · 4 jadual · 9 laluan `/api/v1/proof-of-value`
- [ ] ~~Integrasi `ExecutiveAI` briefing~~ → TODO T-61
- **Rujukan:** ServiceTitan Pro Products — 6 metrik rasmi

### T-56 ⭐ Fleet & GPS (Logistics Baharu) ✅ SELESAI (BAB 85 / Idea #10)
- [x] Tracker + lokasi GPS masa nyata (lat/lng, kelajuan, heading, bateri, ignition)
- [x] Sejarah lokasi + deteksi tingkah laku (harsh braking/acceleration/cornering)
- [x] 9 jenis amaran (breakdown, low fuel, speeding, geofence, idle, maintenance…) + dedup 30 minit
- [x] Perjalanan (`planned → in_progress → completed`) + jarak & tempoh auto
- [x] Skor keselamatan pemandu (0–100, 30 hari)
- [x] Modul `Fleet` · 6 jadual · 13 laluan `/api/v1/fleet`
- [ ] Route optimization (berbanding Google Routes) → TODO T-62
- [ ] Integrasi Samsara/Fleetio/Motive/Verkada → TODO T-62
- **Rujukan:** ServiceTitan Fleet Pro — "75% reduction in accident risk"

### T-57 ⭐ AI MCP Studio (Natural Language → Data/Action) ✅ SELESAI (BAB 85 / Idea #1)
- [x] Protokol MCP `2025-06-18` + JSON-RPC 2.0 (`initialize`, `tools/*`, `resources/*`, `prompts/*`, `ping`)
- [x] Alat (tools) dengan JSON Schema parameter + pemetaan modul/tindakan
- [x] Sumber (resources) semantik + prompt templat + sesi klien
- [x] **3 lapis keselamatan**: allowlist modul/tindakan, human-in-the-loop untuk destructive, audit penuh
- [x] Sokong Claude Desktop, Cursor, Windsurf, n8n, Zapier (JSON konfigurasi sedia)
- [x] Modul `McpStudio` · 6 jadual · 11 laluan `/api/v1/mcp`
- [ ] "Tunjuk jualan bulan ini" → NL→query (varian semasa: tool structured, bukan NL) → TODO T-63
- **Rujukan:** Odoo MCP Studio (INKERP), MuK AI Assistant, Zoho Agent Store

### T-58 B-Agent Studio (Bina Sendiri 16 Ejen Zoho-style) ✅ SELESAI (BAB 85 / Idea #8)
- [x] Persona ejen (nama, persona, sistem prompt auto, bahasa BM/EN)
- [x] Pilih alat (allowlist + tahap kebenaran read/write/admin + sokong MCP)
- [x] Ikat pengetahuan (document/url/faq/text/vector_store + berat)
- [x] 4 tahap otonomi (Manual / Cadangan / Separuh Auto / Penuh) + peraturan eskalasi
- [x] Test run + skor padanan + publish gating (≥1 alat + ≥1 ujian lulus)
- [x] 16 templat ejen siap: sales, support, booking, invoice, logistics, cod, marketing,
  hr, inventory, content, onboarding, compliance, finance, recruitment, field, loyalty
- [x] Modul `AgentStudio` · 5 jadual · 11 laluan `/api/v1/agent-studio`
- **Rujukan 16 ejen Zoho:** Advanced Sales Coach, Instant Lead Response, Lead Intelligence,
  Case Deflection, Intelligent Inventory, NPS Predictor, Deal Gap Analyser,
  Lost Deal Analysis, Onboarding Handover, Deal Contribution, Day Planner,
  Out-of-Office Intel, Multi-Stakeholder, Voice Call Summarizer,
  Outdated Solution ID, Spiky.ai

### T-59 White-Label OEM Pattern
- [ ] Ganti branding platform (logo, warna, nama) ikut tenant
- [ ] Buang "Powered by AI BIJAN" daripada portal/footer/login
- [ ] 65+ penambahbaikan (menu per user, tema, bahasa)
- **Rujukan:** Odoo "Tweak Odoo / OEM Brand" (53,777 dl) + "Powered by Odoo Remove" (2,696 dl)

---

## PART XXII — EKOSISTEM OPERASI (BAB 86-87) · v9.0

### T-60 ✅ B-Orders — Unified Order Management
- [x] Ingest idempoten (tenant + source + platform + source_id)
- [x] 14 status kanonikal + `ALIRAN` validasi peralihan
- [x] Pemetaan status Shopee/Lazada/TikTok/Amazon/POS/Site/Booking
- [x] Rizab stok: reserve → commit → release + pembersihan rizab luput
- [x] Ringkasan merentas saluran (jualan, margin, ikut platform)
- [x] Timeline audit + log sync
- **Modul `Orders`** · 6 jadual · 12 laluan · 27 ujian · `UnifiedOrderService`

### T-61 ✅ B-Seo — SEO & Metadata Engine
- [x] Profil SEO per tenant + templat tajuk
- [x] Metadata polymorphic (product/page/article/video/category/blog_post)
- [x] **13 peraturan audit** + skor 0–100
- [x] Cadangan AI + fallback heuristik (simulasi)
- [x] Schema.org JSON-LD (5 jenis) + sitemap XML + robots.txt
- [x] Preview SERP (Google/Facebook/WhatsApp)
- **Modul `Seo`** · 7 jadual · 16 laluan · 37 ujian · `SeoService`

### T-62 ✅ B-ContentStudio — Auto-Gen / Post / Komen / Chat
- [x] Brand voice (nada, persona, frasa, kata larangan, CTA)
- [x] Adaptasi **9 platform** (had panjang + hashtag + pemotongan)
- [x] Jadual + terbit + had posting harian
- [x] **Auto-publish LALAI MATI** (opt-in per platform)
- [x] Komen inbox terpusat + sentiment + moderation + balasan AI
- [x] Chat/DM + auto-flag manusia pada kata kunci kritikal
- [x] Analytics platform
- **Modul `ContentStudio`** · 10 jadual · 21 laluan · 35 ujian

### T-63 ✅ B-Reminder — Pusat Peringatan
- [x] **14 jenis peringatan** + katalog
- [x] Polymorphic ke mana-mana objek
- [x] 5 saluran (in_app/email/whatsapp/sms/push)
- [x] Jadual berulang (6 frekuensi) + snooze/ack/complete/dismiss
- [x] Eskalasi automatik + kotak masuk AI (triase skor)
- **Modul `Reminder`** · 5 jadual · 18 laluan · 33 ujian · `ReminderService`

### T-64 ✅ B-BusinessIntel — AI Self-Improve
- [x] Skor kesihatan **6 dimensi berwajaran** + gred A–E
- [x] Trend 90 hari
- [x] Kajian bisnes AI (ringkasan, penemuan, cadangan, risiko, peluang) + fallback heuristik
- [x] Idea feed diisih ikut nisbah impak/effort
- [x] Competitor watch + kesan perubahan snapshot
- [x] Sinyal pasaran + **learning loop** (pattern/outcome/correction/preference)
- **Modul `BusinessIntel`** · 7 jadual · 16 laluan · 40 ujian

### T-65 ✅ B-WorkflowStudio — Visual Automation (n8n-style, web-based)
- [x] **18 jenis node** (4 trigger, 3 logic, 2 data, 2 AI, 7 action)
- [x] Pengesahan definisi (node, jenis, ID pendua, edge)
- [x] **Dry-run LALAI TRUE**
- [x] Traversal graf + had 100 node / kedalaman 25
- [x] Template marketplace (global + tenant)
- **Modul `WorkflowStudio`** · 3 jadual (`studio_*`) · 9 laluan · 25 ujian
- ⚠️ **LISEN:** n8n = Sustainable Use License (BUKAN OSS) — rujukan reka bentuk SAHAJA

### T-66 ✅ B-MediaEditor — Canva/CapCut-Class
- [x] 4 jenis projek, 5 resolusi piawai
- [x] **7 jenis lapisan** + pengesahan
- [x] Templat + brand kit + render job queue
- **Modul `MediaEditor`** · 3 jadual · 11 laluan · 18 ujian
- [ ] Renderer sebenar (FFmpeg/Sharp) — worker luar

### T-67 ✅ B-LiveSupport — Call Center AI + eSIM
- [x] 4 jenis saluran (voice/video/chat/whatsapp_call)
- [x] eSIM (ICCID + operator + KYC)
- [x] Transkripsi + sentiment
- [x] **3 pencetus eskalasi** + 12 kata kunci kritikal
- [x] Ringkasan AI + fallback heuristik
- **Modul `LiveSupport`** · 3 jadual · 8 laluan · 9 ujian
- [ ] Telefoni sebenar (Twilio/operator eSIM) — perlu kredensial

### T-68 ✅ B-Community — Komuniti AI BIJAN
- [x] 5 jenis ruang (forum/group/academy/showcase/help)
- [x] 3 peringkat visibiliti (public/members/private)
- [x] 5 jenis pos + jawapan diterima + reputasi (poin)
- **Modul `Community`** · 3 jadual · 8 laluan · 9 ujian

### T-69 ✅ ADE — Agentic Development Environment (BAB 92, SIAP 2026-10-26)
- [x] **Modul `ADE`** · **6 jadual** (`ade_*`) · **24 laluan API** · **48 ujian** (191 assertion)
- [x] **A1 Skills Engine** — 10 skill builtin (7 langkah superpowers + content-brief,
      market-research, financial-review); pencetus automatik (skor = panjang pencetus);
      marketplace skill global; kawalan lesen (MIT/Apache/BSD sahaja)
- [x] **A2 Worktree Isolation** — git worktree SEBENAR + baseline ujian bersih;
      4 jenis sandbox (worktree/directory/container/none); path traversal dilumpuhkan
- [x] **A3 Sub-Agent Coordinator** — 4 peranan (implementer/tester/reviewer/researcher);
      executor callback; ralat diisolasi; semak silang; susun semula
- [x] **A4 Review Surface** — diff walkthrough + test runner + komen baris;
      **kritikal menyekat kelulusan automatik**
- [x] **4 resource Filament** (`/admin/ade-skill`, `/admin/ade-sandbox`,
      `/admin/ade-coordination`, `/admin/ade-review`)
- [x] 4 kebenaran `ade.view|manage|execute|approve` → **212 kebenaran** dalam DB
- [x] Aliran penuh `POST /api/v1/ade/run` (skill → sandbox → coordination → review)
- **Rujukan:** `obra/superpowers` (MIT, 304k★) — 7 langkah metodologi agentic
- [ ] P2 — sambungkan executor kepada `AgentStudio`/`AIGateway` (sub-ejen tulis kod sebenar)
- [ ] P2 — container sandbox (Docker), browser inspection, harness adapter (Claude Code/Codex)
- [ ] P2 — UI canvas ADE drag-drop (seperti n8n)
- **Blueprint:** BAB 92 (v9.4, PART XXVI) · **102 bab / 26 PART**

### T-70 ✅ B-ServiceOS P1 — Service Core (BAB 87)
- [x] **P1 Service Core** — 13 jadual, 33 laluan, 63 ujian
- [x] Projek perkhidmatan + aliran status 8 peringkat (ALIRAN disahkan)
- [x] Sesi kerja + pengesanan konflik masa
- [x] Sebut harga berversi (is_current) + kira jumlah/diskaun/cukai/deposit
- [x] Kontrak auto-dijana daripada sebut harga diterima (idempoten)
- [x] Kru + kemahiran + penugasan + **penolakan konflik**
- [x] Tugas/checklist + 5 templat industri + kemajuan auto
- [x] Peralatan + tempahan + semakan stok
- [x] Penyerahan/handover + penerimaan
- [x] **Outbox peristiwa domain** + idempotency_key + 3 cubaan
- [x] Margin & kos sebenar (TIDAK memproses wang)
- [x] Ringkasan operasi untuk AI COO
- **Modul `ServiceOS`** · 13 jadual (`service_*`) · 33 laluan · 63 ujian · `ServiceOSService`
- [ ] **P2 Automasi** — peringatan, konflik jadual auto, kalendar sync, webhook bayaran, OCR resit
- [ ] **P3 AI** — AI COO/CFO/CSO/CMO + B-Boardroom
- [ ] **P4 Skala** — templat industri, builder tanpa kod, marketplace, white-label
- ⚠️ **LISEN:** RekaDesk terma larang reverse engineering — audit fungsi awam SAHAJA

### T-71 ✅ FilamentPHP UI — 6 Skrin Teras + Polisi Kebenaran (UI 2/10 → 7/10)
- [x] `composer require filament/filament:^3.3` (serasi Laravel 11 & 12 — kini 3.3.53 pada Laravel 12.70.1)
- [x] `php artisan filament:install --panels` → panel `/admin`
- [x] `AdminPanelProvider` — brand AI BIJAN, kumpulan navigasi Melayu
- [x] **`ResolveFilamentTenant`** middleware — tenant context untuk panel (sessions, bukan API)
- [x] `User implements FilamentUser` + `canAccessPanel()` (perlu keahlian aktif)
- [x] **CommandCentreWidget** — 4 KPI sebenar (hasil, belum bayar, perbelanjaan, menunggu)
- [x] **`HasModulePermission` trait** — `canViewAny/canCreate/canEdit/canDelete` + `shouldRegisterNavigation` ikut `perm:` sedia ada
- [x] **6 skrin teras:**
  - [x] `ExpenseResource` `/admin/perbelanjaan` → `expense.*`
  - [x] `OrderResource` `/admin/pesanan` → `orders.*`
  - [x] `CustomerResource` `/admin/pelanggan` → `crm.*`
  - [x] `InvoiceResource` `/admin/invois` → `invoicing.*`
  - [x] `ItemResource` `/admin/produk` → `inventory.*`
  - [x] `ServiceProjectResource` `/admin/projek-perkhidmatan` → `service_os.*`
- [x] **20 ujian** panel (auth, 6 skrin, pengasingan tenant, widget KPI, **4 ujian kebenaran**)
- **Bug ditemui & dibetulkan:** `Item.tenant_id` tiada dalam `$fillable`
- **Keselamatan pakej:** 14 → 6 advisori (dompdf/Filament ditampal)
- **Jumlah: 893 ujian lulus (0 gagal)**

### T-71 Fasa 2 ✅ (2026-10-26) — Widget carta + Tetapan + POS + 59 resource

- [x] **CSP dibaiki** — nonce & `unsafe-inline` saling eksklusif; Alpine perlukan `unsafe-eval`
- [x] **`ResolveFilamentTenant` dalam kumpulan `web`** — Livewire `/livewire/update` tidak melalui `authMiddleware` panel
- [x] **`ModuleGate::ALIAS`** — `finance→expense`, `omnichannel→orders`, `projects→service_os`, `marketing→content_studio`, `brainhub→business_intel`
- [x] **`EntitlementService` skop tenant** — `withoutGlobalScope('tenant')` dalam 6 kaedah grant/modul
- [x] **`HasModulePermission`** — semak RBAC + entitlemen; fallback `.view` bila `.manage` tiada
- [x] **5 widget:** KPI kewangan, KPI pesanan, carta trend 30 hari, carta donut saluran, jadual pesanan, jadual invois tertunggak
- [x] **Semua widget `$isLazy = false`** — lazy loading tinggalkan `fi-loading-section` kosong
- [x] **`->widgets()` sahaja** — JANGAN gabung dengan `->discoverWidgets()` (widget hilang senyap)
- [x] **Halaman Tetapan Syarikat** `/admin/tetapan` — profil, langganan, modul aktif, senarai pengguna
- [x] **POS UI:** Jualan POS (baca sahaja), Kaunter POS (CRUD), Syif Tunai (baca sahaja)
- [x] **59 resource Filament** — liputan semua 55 modul (lihat `skrip/jana-resource.php`)
- [x] **43 ujian panel** (171 assertion)
- **Jumlah: 916 ujian lulus (0 gagal, 4 skip, 4,833 assertion)**

- [ ] Widget carta tambahan (prestasi juruwang, margin produk)
- [ ] Polisi kebenaran per-rekod (skop cawangan)
- [ ] Halaman `create`/`edit` terperinci untuk resource generik (kini generik dinamik)

### T-71 Fasa 3 ✅ (2026-10-26) — Audit UI menyeluruh + 6 namespace permission hilang

**Semua 59 halaman Filament disahkan 200 dengan jadual render (audit Playwright).**

**🔴 6 namespace permission HILANG dari `config/aibijan.php`** — resource muncul dalam
menu tetapi halaman 403 kerana kebenaran tidak wujud langsung dalam jadual `permissions`:
`scm` · `vision` · `cod` · `media_studio` · `content_studio` · `workflow_studio`.
Ditambah 16 kebenaran baharu (view/manage/approve/reports) → **208 kebenaran** (dari 117).

**🔴 Bug model `Loyalty\Models\Reward`:** tiada `$table` dan tiada `use BelongsToTenant`
→ Eloquent meneka `rewards` tetapi jadual sebenar `loyalty_rewards` → 500
"no such table: rewards". Dibetulkan.

**Skrip penyelenggaraan baharu:**
- `skrip/jana-resource.php` — jana 59 resource (definisi dalam fail)
- `skrip/pulih-pos.php` — pulihkan 3 resource POS manual (WAJIB selepas jana semula)
- `skrip/sync-perm.php` — segerakkan permission daripada config → DB + beri
  semua kebenaran kepada `tenant_owner` setiap tenant
- `skrip/naik-plan.php` — naik taraf tenant ke plan (untuk ujian visual)

**3 ujian regresi baharu:** setiap resource mesti guna namespace permission yang
WUJUD dalam DB; setiap model resource mesti mempunyai jadual sebenar; dashboard
mesti render tanpa `SQLSTATE`/`Internal Server Error`.

- **Jumlah: 919 ujian lulus (0 gagal, 4 skip, 4,843 assertion)**

### T-72 📋 B-Migration Wizard
- [ ] 10 importer (Bukku, StoreHub, Excel, Xero, QuickBooks, Sheets, CSV, JSON, XML, Shopify)
- [ ] Dry-run simulator (tunjuk jumlah tepat sebelum ubah data)
- [ ] Profil import + export penuh

### T-73 📋 Compliance per Negara (SEA)
- [ ] 7 negara × 4 domain = 28 pack
- **Sumber:** Odoo (Vietnam VAS, India GST, EU, US, SA, UAE, MX)

### T-74 📋 Kalodata-Class Affiliate Analytics
- [ ] Sinyal produk trending (data awam)
- [ ] Profil pencipta / influencer untuk product seeding
- [ ] Analisis iklan pesaing
- **Sumber:** kalodata.com (200M produk, 250M pencipta) — tiada API awam

### T-75 📋 Connector Pack 20+ Sistem
- [ ] SDK adapter + registry
- [ ] Dashboard pemasangan connector
- **Sumber:** ServiceTitan (70+ integrasi)

---

## PART XXIII — INTEGRASI B-POS SYSTEM (BAB 88) · v9.1

> **Keputusan audit:** B-POS kekal produk berasingan (sudah ada pelanggan, tiada `tenant_id`).
> Ciri unik dipindahkan **satu per satu** ke modul AI BIJAN sedia ada — bukan modul baharu.
> **Rujukan:** `C:\ai-bijan\AI-Bijan\b-pos-system` · audit penuh dalam blueprint BAB 88.

### ✅ Audit Selesai (2026-10-24)
- [x] Inventori teknikal: 109 migration, 61 model, 30 service, 73 controller
- [x] Kenal pasti **6 jurang kritikal** (dine-in, modifier, service charge, multi-tender, offline, shift)
- [x] Kenal pasti **9 isu keselamatan** — paling kritikal: tiada `tenant_id`, `legacySingleOutletBypass` lalai TRUE
- [x] Penilaian: POS 8/10 · Keselamatan 4/10 · AI **0/10** · UI 7/10
- [x] Peta pemindahan 13 ciri → modul AI BIJAN sasaran

### T-76 📋 Dine-in + Kitchen Display → `POS/`
- [ ] Jadual: `pos_dine_areas`, `pos_dine_tables`, `pos_dine_orders`, `pos_dine_order_items`
- [ ] `KitchenService` — status pesanan (queued → cooking → ready → served)
- [ ] `KitchenDisplayController` — paparan dapur masa nyata
- [ ] Pesanan dine-in → `UnifiedOrder` (guna `Orders` sedia ada)
- **Rujukan B-POS:** `DineArea`, `DiningTable`, `DineOrder`, `KitchenService`

### T-77 📋 Modifier / Add-on → `POS/` + `Inventory/`
- [ ] Jadual: `pos_modifier_groups`, `pos_modifier_options`, `pos_product_modifiers`
- [ ] `ModifierService` — kumpulan wajib/pilihan, min/max pilih, tambah harga
- [ ] Modifier masuk ke `TransactionDetail` sebagai baris berasingan atau JSON
- **Rujukan B-POS:** `ModifierGroup`, `ModifierOption`, `ModifierService` (migration `2026_10_23_000004`)

### T-78 📋 Service Charge → `POS/` + `Invoicing/`
- [ ] Medan `service_charge_rate` per outlet/cawangan
- [ ] `ServiceChargeService` — 10% restoran, boleh dimatikan
- [ ] Papar berasingan daripada cukai dalam resit
- **Rujukan B-POS:** `ServiceChargeService` (migration `2026_10_23_000003`)

### T-79 📋 Multi-Tender Split Payment → `POS/`
- [ ] Jadual: `pos_transaction_tenders` (transaction_id, method, amount, cash_received, reference)
- [ ] `TransactionTenderService::normalize()` — sahkan jumlah tender = grand total
- [ ] Sokong: tunai + kad + e-wallet + bayar kemudian dalam satu transaksi
- **Corak B-POS:** `TransactionTender`, `TransactionTenderService` — jauh lebih bersih daripada kolum berasingan

### T-80 📋 Cashier Shift + Cash Movement → `POS/`
- [ ] Jadual: `pos_cashier_shifts`, `pos_shift_cash_movements`
- [ ] Buka syif (float), tutup syif (kira tunai), drop/ambil tunai
- [ ] Kunci outlet semasa syif aktif
- **Rujukan B-POS:** `CashierShift`, `ShiftCashMovement`, `CashierShiftService`

### T-81 📋 Offline Mode PWA + Sync Fingerprint → `POS/`
- [ ] Kolum `client_uuid` + `sync_fingerprint` pada transaksi
- [ ] Queue IndexedDB di frontend, sync bila online
- [ ] Idempoten: `sync_fingerprint = hash(cart + waktu + jumlah)`
- **Corak B-POS:** `client_uuid` + `sync_fingerprint` — corak sama seperti `ServiceOS` `idempotency_key`

### T-82 📋 TNG Soundbox / Bayar Automatik → `POS/` + `Finance/`
- [ ] `TngSoundboxService` — sahkan notifikasi bayar TnG
- [ ] Webhook endpoint + idempotency (isu S-5 B-POS)
- [ ] Auto-tanda transaksi lunas bila notifikasi sah masuk
- **Rujukan B-POS:** `TngSoundboxService`, `TngNotificationController` (migration `2026_10_23_000005`)

### T-83 📋 Discount Approval Workflow → `POS/`
- [ ] Had diskaun per peranan (`pos.discount.max_percent`)
- [ ] `DiscountApprovalLog` — siapa lulus, bila, berapa
- [ ] Blok checkout jika diskaun melebihi had tanpa kelulusan
- **Rujukan B-POS:** `DiscountApprovalLog` (migration `2026_06_22_000014`)

### T-84 📋 Thermal Printer WebUSB → `POS/` (frontend)
- [ ] `ThermalPrintService` — ESC/POS terus dari browser
- [ ] Auto-print selepas checkout, saiz 58mm/80mm
- [ ] Fallback ke PDF jika WebUSB tidak disokong
- **Rujukan B-POS:** `ThermalPrintService`

### ⚠️ JANGAN Ambil Daripada B-POS
- ❌ Struktur modal `outlet_id` sahaja — AI BIJAN guna `tenant_id` (lebih luas)
- ❌ 4 jadual wilayah Indonesia (`provinces`/`cities`/`districts`/`villages`) — Malaysia guna negeri/daerah/mukim
- ❌ `whatsapp-web.js` gateway — risiko ban; guna WABA API rasmi
- ❌ `access_token` uuid tanpa had masa — guna `URL::temporarySignedRoute`
- ❌ `legacySingleOutletBypass` (lalai TRUE) — corak berbahaya
- ❌ UI Inertia + React — AI BIJAN guna FilamentPHP (T-71) untuk konsistensi

### 📋 Lesen & Atribusi
- [ ] Tambah atribusi `aryadwiputra/point-of-sales` (MIT) dalam `THIRD-PARTY-NOTICES.md`
- [ ] Pastikan lapisan proprietari Bijan (DOKU, TnG, PWA) tidak tersalin tanpa kebenaran

---

## PART XXIV — B-EXPENSE (BAB 89) · v9.2

### T-85 ✅ B-Expense P1 — Expense Core
- [x] **7 jadual** (`expense_*`) · **33 laluan** · **48 ujian** (190 assertion)
- [x] Kategori + pautan `chart_of_accounts` + 10 kategori lalai (idempoten)
- [x] Perbelanjaan + baris + multi-mata-wang (`currency` + `exchange_rate` → `amount_myr`)
- [x] Resit upload + OCR (guna semula `DocumentAI`) + `is_verified` oleh manusia
- [x] **Kesan resit pendua** (hash fail + merchant/jumlah)
- [x] **Cadangan kategori** AI/heuristik dengan skor keyakinan
- [x] Tuntutan staff (reimbursement / mileage / per-diem)
- [x] Jejak kelulusan (`expense_claim_approvals` — step, action, amount, komen)
- [x] **Post ke jurnal** melalui `Finance\JournalService` sahaja
- [x] Ringkasan: jumlah, tertunggak, ikut kategori, tuntutan menunggu, resit pendua
- [x] Ambang kelulusan RM200 · Resit wajib RM50 · Mileage RM0.60/km (konfigurasi)
- [x] Pengasingan tenant disahkan
- **Modul `Expense`** · `ExpenseService` · `ExpenseController` · `config/expense.php`
- **Pepatch sampingan:** migration `2026_10_24_002100_add_timestamps_to_journal_entry_lines.php` (bug Finance sedia ada)

### T-86 📋 B-Expense P2 — Staff Claims Lanjutan
- [ ] Kelulusan berperingkat (penyelia → pengurus → kewangan)
- [ ] Polisi per peranan × kategori (had maksimum)
- [ ] Delegasi kelulusan (penyelia bercuti)
- [ ] **Petty cash / imprest** (float → top-up bila < 20%)
- [ ] Reimbursement batch → `Payroll`
- [ ] Per diem ikut negeri/gred jawatan

### T-87 📋 B-Expense P3 — Budget & AI
- [ ] Bajet per kategori/departmen/bulan + amaran 80%/100%
- [ ] Kesan anomali (banding sejarah 6 bulan, tanda jika > 2× median)
- [ ] Cadangan kategori guna `AIGateway` (bukan hanya heuristik)
- [ ] Auto-tag kos ke `ServiceOS` projek → margin tepat
- [ ] Amaran bajet melalui `Reminder`

### T-88 📋 B-Expense P4 — Bank & Reports
- [ ] Padanan transaksi bank (jumlah + tarikh + vendor)
- [ ] Laporan SST/input tax sedia untuk penyata cukai
- [ ] Eksport akauntan (CSV/Excel/PDF)
- [ ] Perbelanjaan berulang (`expense_recurring`) — sewa, langganan, utiliti

---

## PART XXV — DOKUMEN PELANGGAN & KECERDASAN VISUAL (BAB 90-91) · v9.3

### T-89 📋 B-Receipt P1 — Teras Dokumen
- [ ] 9 jadual: `receipt_templates`, `receipt_documents`, `receipt_document_lines`,
      `receipt_deliveries`, `receipt_recipients`, `receipt_send_rules`,
      `receipt_statement_periods`, `receipt_document_requests`, `receipt_audit_logs`
- [ ] Template builder (logo, warna, penomboran, terma) per tenant
- [ ] Jana PDF (guna `DocumentAI` / enjin PDF sedia ada — tiada enjin baharu)
- [ ] Penghantaran manual WhatsApp + muat turun URL bertempoh
- [ ] Audit trail penuh (jana/hantar/lihat/muat turun/padam)

### T-90 📋 B-Receipt P2 — Automasi & Fallback
- [ ] Auto-send rules (jenis transaksi → templat → saluran)
- [ ] Event listener: `PaymentCaptured`, `InvoiceIssued`, `PosSaleCompleted`,
      `BookingConfirmed`, `CodCollected`, `ServiceJobCompleted`
- [ ] Retry berperingkat + fallback e-mel / pautan selamat
- [ ] Document Centre (pelanggan minta salinan semula)

### T-91 📋 B-Receipt P3 — Penyata Berkala
- [ ] Penyata bulanan/mingguan (jadual + consent)
- [ ] Opt-out + suppression kekal
- [ ] Laporan status penghantaran

### T-92 📋 B-Receipt P4 — AI Assistant
- [ ] "Hantar semula resit pelanggan ini" (pengesahan identiti + kebenaran)
- [ ] Ringkasan dokumen + carian dokumen

### T-93 📋 B-Receipt — Pematuhan WhatsApp WAJIB
- [ ] **WhatsApp Business Platform (Cloud API) SAHAJA** — ❌ `whatsapp-web.js`
- [ ] Templat mesej diluluskan Meta sebelum penghantaran automatik
- [ ] Consent direkod (timestamp + sumber + tujuan); opt-out serta-merta
- [ ] Tetingkap 24 jam dihormati
- [ ] ❌ Tiada dokumen dihantar jika pembayaran gagal/belum disahkan
- [ ] ❌ PDF melalui WhatsApp **BUKAN** e-Invois sah LHDN

### T-94 📋 B-Receipt — Kriteria Penerimaan
- [ ] Semua dokumen tenant-scoped (ujian negatif A→B)
- [ ] PDF hanya melalui URL bertempoh (bukan pautan kekal)
- [ ] Status penghantaran sebenar (SENT/FAILED/PENDING) — tiada andaian

### T-95 📋 B-Vision P1 — Foundation
- [ ] 15 jadual `vision_*`
- [ ] Schema + API auth + object storage + queue
- [ ] Kontrak JSON event + status job
- [ ] **Ujian pengasingan tenant** (wajib lulus sebelum Fasa 2)

### T-96 📋 B-Vision P2 — Video Analysis MVP
- [ ] Muat naik video (upload-intent + complete)
- [ ] Ekstrak bingkai + thumbnail (FFmpeg worker)
- [ ] Detection + tracking + analisis visual
- [ ] Timeline + bukti + keyakinan model

### T-97 📋 B-Vision P3 — Events & Governance
- [ ] Event normalizer + rules engine
- [ ] Semakan manusia (`vision_reviews`)
- [ ] Audit trail + kuota + usage tracking
- [ ] Ujian pemadaman data

### T-98 📋 B-Vision P4 — Live Camera Pilot
- [ ] Sambungan RTSP/ONVIF
- [ ] Reconnect + kesihatan kamera
- [ ] Pemprosesan terpilih + amaran
- [ ] Ujian prestasi kamera sebenar

### T-99 📋 B-Vision P5 — Sector Packs
- [ ] Profil restoran / gudang / kontraktor / servis lapangan
- [ ] Integrasi modul AI BIJAN melalui business events
- [ ] Harga ikut penggunaan + ujian margin

### T-100 📋 B-Vision — 8 Kawalan Keselamatan WAJIB
- [ ] Semua query dihadkan tenant sah
- [ ] Worker dapatkan tenant context dari DB (**bukan** payload queue)
- [ ] Signed URL tidak kekal awam
- [ ] Credential kamera tidak dedah ke browser/log
- [ ] Webhook: tandatangan + replay protection + idempotency
- [ ] Kuota + rate limit + had saiz/tempoh video
- [ ] ❌ Tiada latihan model pada video pelanggan tanpa kebenaran
- [ ] Polisi privasi + notis kamera + retensi (PDPA)

### T-101 📋 B-Vision — Perlindungan SSRF
- [ ] ❌ **JANGAN** benarkan URL kamera sewenang-wenangnya (SSRF)
- [ ] Allowlist destinasi + rangkaian peranti diluluskan
- [ ] ❌ Gateway bukan proxy terbuka kepada RTSP

### T-102 ⚠️ B-Vision — Keputusan Seni Bina Belum Selesai
- [ ] **TENTUKAN:** self-hosted computer vision vs gabungan self-hosted + API cloud
- [ ] Kesan pada kos, privasi, skalabiliti & model harga pelanggan

---

## ✅ T-103 SELESAI — Laravel 11 → 12.70.1 + SIFAR ADVISORI (2026-10-26)

> **6 advisori → 0 advisori.** `composer audit` bersih.
> **919 ujian lulus, 0 gagal, 0 deprecation, 4 skip, 4,843 assertion.**

### Pakej dinaik taraf
| Pakej | Sebelum | Selepas |
|---|---|---|
| `laravel/framework` | 11.57.0 | **12.70.1** |
| `phpunit/phpunit` | 10.5.66 | **11.5.57** |
| `nunomaduro/collision` | 8.5.0 | **8.9.5** |
| `barryvdh/laravel-dompdf` | 3.0.0 | **3.1.2** |
| `filament/filament` | 3.3.52 | **3.3.53** ← tampalan CVE-2026-55409 |
| `nesbot/carbon` | 3.15.0 | 3.15.1 |
| `guzzlehttp/guzzle` | 7.16.0 | 7.16.1 |
| `vlucas/phpdotenv` | 5.7.0 | 5.8.0 |

### Kesan rantaian yang perlu diketahui
- `nunomaduro/collision` **8.9.x** memerlukan `phpunit >=11.5.50` — naik taraf PHPUnit
  WAJIB serentak, jika tidak composer gagal dengan "conflict analysis".
- `nunomaduro/collision` **tiada v9** (lompat terus ke Filament 4/5 era) — jangan cuba `^9.0`.
- `barryvdh/laravel-dompdf` **3.0 pin tepat** menyekat `illuminate/support ^11` →
  mesti longgarkan ke `^3.1` (bukan sekadar tukar versi lock).
- `filament/forms` CVE-2026-55409 **sudah ditampal dalam 3.3.53** — TIDAK perlu
  naik ke Filament 5. (Advisory kata "<=3.3.52", tampalan = 3.3.53.)

### Perubahan kod
- `tests/Feature/PayrollStatutoryTest.php` — anotasi `/** @test */` (10×) ditukar
  kepada atribut `#[Test]` + `use PHPUnit\Framework\Attributes\Test;`
  (PHPUnit 12 akan buang sokongan doc-comment; PHPUnit 11 sudah beri deprecation).
  ⚠️ **PHP 8.2 TIDAK membenarkan `#[\PHPUnit\...\Test]`** (backslash hadapan) —
  mesti guna `use` + nama pendek `#[Test]`.

### Sandaran
`C:\ai-bijan\AI-Bijan\backups\pre-laravel12-2026\` — salinan penuh `app/`, `config/`,
`database/`, `tests/`, `routes/`, `resources/`, `docs/` sebelum naik taraf.

### Status produksi
✅ **Boleh deploy** — tiada advisori keselamatan terbuka. Masih perlu:
- Ujian manual UI di staging (59 halaman — sudah disahkan pada dev)
- Jalankan `php skrip/sync-perm.php` pada DB produksi (208 kebenaran)

---

## Nota kerja

- **JANGAN** guna `Set-Content -NoNewline` pada `routes/api.php` — pernah merosakkan fail.
- Ujian servis terus perlu `$this->dalamTenant($t)`.
- Semua model modul mesti guna trait `BelongsToTenant`.
- Migration diletak global di `database/migrations/`, bukan dalam modul.
- Alias middleware ialah `perm:`, **bukan** `permission:`.
- Enum/`NOT NULL` SQLite perlu `$attributes` default dalam model Eloquent.
- `DB::raw()` dengan datetime MESTIL ada tanda petik dalam SQL.
- `create()` dengan `tenant_id` eksplisit **perlu** `tenant_id` dalam `$fillable` model.
- Selepas satu permintaan HTTP, `TenantContext` **dikosongkan** — panggil `$this->dalamTenant($t)` semula sebelum query DB dalam ujian.
- Nama kaedah PHP **tidak boleh** mengandungi ruang (`kemas kini` → `kemasKini`).
- Sifat Eloquent (`$execution->tool`) vs method (`$execution->tool()`) — method mengembalikan objek *relation*, bukan model.
- `GatewayService::call(int $tenantId, array $messages, string $taskType, array $options)` — bukan named args.
- Jalankan ujian dengan `php vendor/phpunit/phpunit/phpunit -c phpunit.xml --filter <nama>` (filter `artisan test --filter` tidaksentiasa honoured).

- **Jadual `workflows`/`workflow_runs` BERTINDIH** dengan FlowAutomation (yang guna `workflow_definitions`).
  Modul baharu MESTI guna prefix unik (`studio_*`, `service_*`, `pos_*`).
- **Route ordering** — laluan statik (`/summary`, `/templates`, `/inbox/list`) MESTI
  didahulukan sebelum `/{id}`.
- `updateOrCreate()` dengan kolum `date` gagal perbandingan — guna `whereDate()->first()`.
- `array_merge($defaults, $data)` — `unset()` kunci bukan-kolum dahulu.
- Status platform vs kanonikal: masukkan status kanonikal SELEPAS `$data` dalam gabungan.
- `Blueprint::array()` tiada — guna `json()`.
- **Decimal cast** pulang `'1560'` bukan `'1560.00'` — dalam ujian guna `number_format((float) $x, 2, '.', '')`.
- **Ujian pengasingan tenant** perlu `withoutGlobalScopes()` — global scope menapis ikut konteks semasa.
- **`dalamTenant($t2)` WAJIB** sebelum cipta rekod untuk `$t2`, walaupun `tenant_id` dihantar eksplisit.
- **Cipta rekod SEBELUM `$this->sebagai()`** dalam ujian API — konteks dikosongkan selepas HTTP request.

---

## Nota Integrasi B-POS (BAB 88)

- **B-POS tiada `tenant_id`** — satu deployment = satu syarikat. **JANGAN** cuba gabung terus ke AI BIJAN.
- **B-POS guna `outlet_id`** sebagai unit pengasingan (cawangan), bukan tenant (syarikat).
- **`legacySingleOutletBypass` lalai TRUE** — jika tiada outlet, semua user dapat semua gudang. Corak berbahaya, jangan tiru.
- **Ciri patut ditiru:** `client_uuid`+`sync_fingerprint` (offline idempotency), `TransactionTender` (multi-tender sebagai entiti berasingan), `AuditLog` snapshot before/after, batch allocation FEFO.
- **Ciri JANGAN ditiru:** 4 jadual wilayah Indonesia (`provinces`/`cities`/`districts`/`villages`), `whatsapp-web.js` gateway, `access_token` uuid tanpa had masa.
- **Lesen:** asas `aryadwiputra/point-of-sales` = **MIT** (perlu atribusi dalam `THIRD-PARTY-NOTICES.md`); lapisan Bijan (DOKU/TnG/PWA) = proprietari.
- **B-POS kekal produk berasingan** — ciri dipindahkan satu per satu (T-76 → T-84), bukan digabung.
