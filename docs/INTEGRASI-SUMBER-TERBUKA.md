# Integrasi 9 Suite Sumber Terbuka — AI BIJAN

Dokumen ini merekod integrasi **9 projek sumber terbuka** ke dalam AI BIJAN,
serta cara setiap satu expelled ke dalam platform.

> Tarikh: 8 Nov 2026 ·asas Laravel 12.70.1 · Filament 3.3

---

## Ringkasan

| # | Projek Sumber | Modul AI BIJAN | Jenis | Prefix API |
|---|---------------|----------------|-------|------------|
| 1 | [Higgsfield](https://github.com/Anil-matcha/Open-Generative-AI) → Open Generative AI | `App\Modules\OpenGenAI` | Baharu | `/api/v1/open-gen-ai` |
| 2 | [ElevenLabs](https://github.com/debpalash/VoiceStudio) → VoiceStudio | `App\Modules\VoiceStudio` | Baharu | `/api/v1/voice-studio` |
| 3 | [Semrush](https://github.com/every-app/open-seo) → OpenSEO | `App\Modules\OpenSEO` | Baharu | `/api/v1/open-seo` |
| 4 | [Notion](https://github.com/AppFlowy-IO/AppFlowy) → AppFlowy | `App\Modules\Workspace` | Baharu | `/api/v1/workspace` |
| 5 | [Wispr Flow](https://github.com/OpenWhispr/openwhispr) → OpenWhispr | `App\Modules\OpenWhispr` | Baharu | `/api/v1/open-whispr` |
| 6 | [Asana](https://github.com/opf/openproject) → OpenProject | `App\Modules\WorkManagement` | Sambungan | `/api/v1/work-management` |
| 7 | [Calendly](https://github.com/calcom/cal.diy) → Cal.diy | `App\Modules\Scheduling` | Sambungan | `/api/v1/scheduling` |
| 8 | [Intercom](https://github.com/chatwoot/chatwoot) → Chatwoot | `App\Modules\SharedInbox` | Sambungan | `/api/v1/shared-inbox` |
| 9 | [Loom](https://github.com/CapSoftware/Cap) → Cap | `App\Modules\ScreenRecorder` | Sambungan | `/api/v1/screen-recorder` |

**Strategi:** 5 modul baharu (tiada setara dalam platform) + 4 modul
sambungan (melengkapkan modul sedia ada tanpa mengubah API asal).

---

## Prinsip Reka Bentuk

Semua modul baharu mengikut konvensyen platform AI BIJAN:

1. **Penenggalan tenant** — setiap model guna trait
   `App\Models\Concerns\BelongsToTenant` (skop global + anti-pemalsuan `tenant_id`).
2. **Kebenaran** — laluan API dilindungi middleware `perm:<modul>.<tindakan>`.
3. **Entitlemen** — `ModuleGate` menolak tenant tanpa langganan yangmana-mana modul.
4. **Migration dalam modul** — di bawah `app/Modules/<Modul>/database/migrations`,
   dimuatkan oleh `loadMigrationsFrom()`.
5. **Kontrak seragam** — setiap servis memulirkan array bentuk tetap supaya
   mana-mana modul boleh memanggilnya tanpa knowing asProvider.
6. **Tiada kunci API bocor** — kunci disulitkan (`Crypt::encryptString`) dan
   hanya 4 aksara terakhir dipaparkan.

---

## 1. OpenGenAI — Enjin Generatif Pelbagai Provider

**Sumber:** https://github.com/Anil-matcha/Open-Generative-AI

AI BIJAN tidak terikat kepada satu pembekal model. Tenant boleh menyambungkan
provider sendiri melalui `gen_ai_providers`.

### Provider Disokong

| `kind` | Kontrak | Nota |
|--------|---------|------|
| `openai` | `POST /chat/completions` | Serasi OpenAI |
| `anthropic` | `POST /v1/messages` | Claude |
| `gemini` | `POST /models/{model}:generateContent` | Kunci dalam query |
| `groq` | `POST /chat/completions` | Serasi OpenAI |
| `ollama` | `POST /api/generate` | Tempatan |
| `replicate` | `POST /models/{model}/predictions` | Imej/video |
| `custom` | `POST /chat/completions` | Pelayan sendiri |

### Kemahiran

`text` · `image` · `video` · `audio` · `code`

### API

```
GET    /providers              Senarai provider (kunci_api_disamarkan)
POST   /providers              Tambah provider
PUT    /providers/{id}         Kemas kini provider
DELETE /providers/{id}         Buang provider
POST   /generate               Jana kandungan
GET    /jobs                   Sejarah penjanaan
GET    /jobs/{id}              Butiran job
GET    /summary                Ringkasan penggunaan & kos
```

### Contoh

```bash
curl -X POST /api/v1/open-gen-ai/generate \
  -H 'Authorization: Bearer <token>' \
  -d '{"prompt":"Ringkaskan laporan jualan Q3","capability":"text","max_tokens":800}'
```

### Nota Keselamatan

- `api_key` disulitkan di at-rest; atribut `api_key` ialah `$hidden`.
- `kunciApi()` menyahsulit untuk kegunaan pelayan sahaja; `apiKeyMasked()`
  memulangkan bentuk `••••••••abcd`.
- Setiap penjanaan direkod dalam `gen_ai_jobs` (prompt, token, kos, masa) —
  jejak audit penuh.

---

## 2. VoiceStudio — Enjin Suara (TTS)

**Sumber:** https://github.com/debpalash/VoiceStudio

Profil suara (bahasa Melayu, gaya, pitch/speed) + sintesis teks-ke-audio.

### Enjin

`elevenlabs` · `elevenlabs_local` · `openai` · `piper` · `coqui`

### API

```
GET    /profiles               Senarai profil suara
POST   /profiles               Cip profil
PUT    /profiles/{id}          Kemas kini
DELETE /profiles/{id}          Buang
POST   /synthesize             Sintesis audio
GET    /tracks                 Sejarah sintesis
GET    /tracks/{id}            Butiran rekod
GET    /summary                Ringkasan penggunaan
```

---

## 3. OpenSEO — Intel Competitive & SEO

**Sumber:** https://github.com/every-app/open-seo

Melengkapkan modul `Seo` (metadata/sitemap) dengan intel competitive:
jejak SERP,_flagship kata kunci, pantau backlink, analisis celah kandungan.

### API

```
GET    /projects                       Senarai projek SEO
POST   /projects                       Cip projek
GET    /projects/{id}/summary          Ringkasan prestasi
GET    /projects/{id}/serp             Sejarah SERP
POST   /projects/{id}/serp             Rekod kedudukan SERP
GET    /projects/{id}/keywords         Kata kunci + skor keutamaan
POST   /projects/{id}/keywords         Simpan kata kunci
GET    /projects/{id}/gap              Analisis celah vs pesaing
GET    /projects/{id}/backlinks        Backlink
POST   /projects/{id}/backlinks        Rekod backlink
POST   /audit                          Audit teknikal URL
```

### Skor Keutamaan Kata Kunci

$$
\text{skor} = 0.40 \cdot \text{isipadu} + 0.40 \cdot \text{kemudahan} + 0.20 \cdot \text{celah}
$$

---

## 4. Workspace — Dokumen & Pangkalan Data Kolaboratif

**Sumber:** https://github.com/AppFlowy-IO/AppFlowy

Halaman dibina daripada **blok** yang boleh susun semula (bukan satu dokumen
HTML besar) — ciri utama AppFlowy. termasuk pangkalan data dengan pelbagai
paparan, komen dan versi sejarah.

### API

```
GET    /spaces                    Senarai ruang kerja
POST   /spaces                    Cipta ruang kerja
GET    /spaces/{id}/pages         Halaman dalam ruang kerja
POST   /spaces/{id}/pages         Cipta halaman
GET    /pages/{id}                Halaman + blok + komen
PUT    /pages/{id}                Kemas kini (simpan versi)
POST   /pages/{id}/blocks         Tambah blok
PUT    /pages/{pageId}/blocks/{blockId}     Kemas kini blok
POST   /pages/{id}/blocks/reorder Susun semula
GET    /pages/{id}/versions       Versi sejarah
GET    /pages/{id}/comments       Komen
POST   /pages/{id}/comments       Tambah komen
GET    /spaces/{id}/databases     Pangkalan data
POST   /spaces/{id}/databases     Cipta pangkalan data
GET    /databases/{id}/rows       Baris data
POST   /databases/{id}/rows       Tambah baris
```

### Jenis Blok

`paragraph` · `heading` · `bullet` · `numbered` · `todo` · `quote` ·
`code` · `image` · `table` · `callout` · `divider` · `embed`

---

## 5. OpenWhispr — Diktasi Suara ke Teks

**Sumber:** https://github.com/OpenWhispr/openwhispr

STT dengan model Whisper tempatan (privasi) atau API awam, lengkap dengan
**kosakata tersuai** untuk istilah perniagaan Malaysia.

### API

```
GET    /sessions          Sesi diktasi
POST   /transcribe        Muat naik audio & transkripsi
GET    /sessions/{id}     Butiran sesi
DELETE /sessions/{id}     Buang sesi
GET    /vocabulary        Kamus perniagaan
POST   /vocabulary        Tambah perkataan
PUT    /vocabulary/{id}   Kemas kini
GET    /settings          Tetapan klien (hotkey, model)
GET    /summary           Ringkasan
```

### Enjin

`whisper_local` (faster-whisper hos sendiri) · `openai` (whisper-1)

---

## 6. WorkManagement — Pengurusan Kerja Lanjutan

**Sumber:** https://github.com/opf/openproject

Melengkapkan modul `Projects` dengan Gantt, laluan kritikal, garis dasar
(baseline), bajet per baris dan portfolio projek.

### API

```
GET    /projects/{id}/timeline            Garis masa + laluan kritikal
POST   /projects/{id}/activities          Tambah aktiviti
PUT    /projects/{id}/activities/{aid}    Kemas kini aktiviti
DELETE /projects/{id}/activities/{aid}    Buang aktiviti
GET    /projects/{id}/baselines           Garis dasar
POST   /projects/{id}/baselines           Tangkap garis dasar
GET    /projects/{id}/variance            Analisis variance
GET    /projects/{id}/budget              Bajet + ringkasan
POST   /projects/{id}/budget              Tambah baris bajet
GET    /portfolios                        Portfolio
POST   /portfolios                        Cipta portfolio
```

### Laluan Kritikal

Kira laluan terpanjang melalui graf kebergantungan aktiviti (DFS dengan
memoization). Aktifiti dengan `duration_days` terbesar yang membentuk rantaian
kebergantungan = laluan kritikal.

---

## 7. Scheduling — Penjadualan Tempahan

**Sumber:** https://github.com/calcom/cal.diy

Melengkapkan modul `Booking` dengan pautan penjadualan awam, pengiraan slot
tersedia dan routing round-robin antara provider.

### API

```
GET    /links                    Senarai pautan
POST   /links                    Cipta pautan
PUT    /links/{id}               Kemas kini
DELETE /links/{id}               Buang
GET    /links/{id}/slots          Slot tersedia (mengikut tarikh)
POST   /links/{id}/book          Tempah slot
GET    /team-calendar            Jadual pasukan
```

### Strategi Routing

| Strategi | Algoritma |
|----------|-----------|
| `round_robin` | Giliran mengikut tempahan terakhir |
| `least_loaded` | Provider dengan beban terendah |
| `random` | Rawak |
| `skill_match` | Provider yang dipadankan dengan servis paling banyak |

---

## 8. SharedInbox — Peti Masuk Bersama

**Sumber:** https://github.com/chatwoot/chatwoot

Melengkapkan modul `LiveSupport` (saluran suara/chat) dengan peti masuk
multi-ejen: SLA, makro, ulasan sedia ada dan penugasan automatik.

### API

```
GET    /inboxes                        Senarai peti masuk
POST   /inboxes                        Cipta peti masuk
GET    /inboxes/{id}/statistics        Statistik
GET    /inboxes/{id}/conversations     Perbincangan
POST   /inboxes/{id}/conversations     Cipta perbincangan
GET    /conversations/{id}             Perbincangan + mesej
POST   /conversations/{id}/messages    Hantar mesej
POST   /conversations/{id}/assign      Tugasan
GET    /inboxes/{id}/canned-responses  Ulasan sedia ada
POST   /inboxes/{id}/canned-responses  Tambah
GET    /inboxes/{id}/macros            Makro
POST   /inboxes/{id}/macros            Cipta makro
POST   /inboxes/{id}/macros/{mid}/run  Jalankan makro
POST   /sla/check                      Semak SLA
```

### Jenis Tindakan Makro

`send_reply` · `set_status` · `set_priority` · `assign_agent` · `add_tag` ·
`remove_tag` · `close_conversation` · `resolve_sla`

---

## 9. ScreenRecorder — Rakaman Skrin & Perkongsian

**Sumber:** https://github.com/CapSoftware/Cap

Melengkapkan modul `MediaEditor` dengan rakaman skrin + audio, pautan
perkongsian bermasa dan komen bercapah masa pada timeline.

### API

```
GET    /recordings                  Senarai rakaman
POST   /recordings                  Muat naik rakaman
GET    /recordings/{id}             Butiran + pautan kongsi
PUT    /recordings/{id}             Kemas kini
DELETE /recordings/{id}             Buang
GET    /recordings/{id}/share       Pautan perkongsian
POST   /recordings/{id}/share/regenerate   Jana semula token
GET    /recordings/{id}/comments    Komen
POST   /recordings/{id}/comments    Tambah komen
```

### Privasi

`public` · `unlisted` · `private` — token 32 aksara, boleh dijana semula.

---

## Kebenaran (Permissions)

Semua kebenaran didaftar dalam `config/aibijan.php`:

| Namespace | Tindakan |
|-----------|----------|
| `open_gen_ai` | `view`, `manage`, `execute` |
| `voice_studio` | `view`, `manage`, `execute` |
| `open_whispr` | `view`, `manage`, `execute` |
| `open_seo` | `view`, `manage`, `execute` |
| `workspace` | `view`, `manage` |
| `work_management` | `view`, `manage` |
| `scheduling` | `view`, `manage`, `execute` |
| `shared_inbox` | `view`, `manage`, `execute` |
| `screen_recorder` | `view`, `manage`, `execute` |

---

## Entitlemen (Langganan)

Kunci modul ditambah dalam `app/Modules/Entitlement/config/entitlement.php`:

- **Starter** — `workspace`, `scheduling`, `open_gen_ai`, `open_whispr`
- **Growth** — kesembilan modul penuh
- **Enterprise** — `['*']` (semua modul)

---

## Jadual Baharu

| Jadual | Modul |
|--------|-------|
| `gen_ai_providers`, `gen_ai_jobs` | OpenGenAI |
| `voice_profiles`, `voice_tracks` | VoiceStudio |
| `dictation_sessions`, `dictation_vocabularies` | OpenWhispr |
| `seo_projects`, `serp_snapshots`, `seo_keyword_research`, `seo_backlinks` | OpenSEO |
| `workspace_spaces`, `workspace_pages`, `workspace_blocks`, `workspace_comments`, `workspace_page_versions`, `workspace_databases`, `workspace_database_views`, `workspace_database_rows` | Workspace |
| `project_timelines`, `project_baselines`, `project_budget_lines`, `project_portfolios`, `project_portfolio_project` | WorkManagement |
| `scheduling_links`, `scheduling_slots` | Scheduling |
| `shared_inboxes`, `inbox_conversations`, `inbox_messages`, `canned_responses`, `inbox_macros` | SharedInbox |
| `screen_recordings`, `recording_comments` | ScreenRecorder |

---

## Ujian

Setiap modul mempunyai ujian pengasingan tenant (`UjianPengasingan`):

```
tests/Feature/Pengasingan/
├── OpenGenAITenantIsolationTest.php
├── VoiceStudioTenantIsolationTest.php
├── OpenWhisprTenantIsolationTest.php
├── OpenSEOTenantIsolationTest.php
├── WorkspaceTenantIsolationTest.php
├── WorkManagementTenantIsolationTest.php
├── SchedulingTenantIsolationTest.php
├── SharedInboxTenantIsolationTest.php
└── ScreenRecorderTenantIsolationTest.php
```

Menyemak 5 perkara bagi setiap model:
1. `find()` tidak menembusi skop tenant lain
2. `count()` konteks tenant dua tidak nampak rekod tenant satu
3. `create()` tidak boleh memalsukan `tenant_id`
4. `save()` tidak boleh menukar `tenant_id`
5. Laluan API tidak memulangkan rekod tenant lain

```bash
php vendor/bin/phpunit --filter "TenantIsolationTest"
```
