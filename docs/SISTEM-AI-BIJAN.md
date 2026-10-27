# SISTEM AI BIJAN — Dokumen Sistem Lengkap

> **AI-Native Business Operating System** untuk PKS Malaysia → SEA → Global South
> **Versi sistem:** v9.4 · **Blueprint induk:** `AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md` (102 bab / 26 PART)
> **Tarikh:** 26 Oktober 2026 · **Setiap angka disahkan daripada kod**

---

## 0. Ringkasan Satu Halaman

AI BIJAN ialah **satu sistem** — bukan himpunan aplikasi berasingan. Ia menyatukan
jualan, operasi, kewangan, manusia dan AI dalam satu pangkalan data multi-tenant,
dengan **satu lapisan AI**, **satu pengasingan tenant**, dan **satu UI admin**.

| | |
|---|---|
| **Janji produk** | *Ganti 10+ alat perniagaan dengan satu sistem pada RM288–RM1,888/bulan* |
| **Pembeza** | AI yang **memahami aliran kerja** (bukan sekadar menjawab soalan) + **pematuhan Malaysia** |
| **Bukan** | Bukan ERP generik · Bukan chatbot · Bukan himpunan modul berasingan · **Bukan ERPNext/Frappe** |

### Angka Sistem (disahkan `php skrip/semak-dokumen.php`)

| Metrik | Nilai |
|---|---|
| Modul | **58** |
| Jadual pangkalan data | **357** |
| Laluan API (`api/v1`) | **1,518** |
| Prefix API | **76** |
| Ujian lulus | **1009** (4 dilangkau, **0 gagal**) |
| Assertion | **5,190** |
| Model | **292** |
| Service | **166** |
| Controller (modul) | **127** |
| Resource Filament | **66** |
| Widget dashboard | **6** |
| Kebenaran | **212** |
| Migration | **54 fail** |
| Kandungan blueprint | **103 bab / 27 PART** |
| Laravel / PHP | **12.70.1 / 8.2.12** |
| Advisori keselamatan | **0** |

> **Dikemas kini 27 Okt 2026** selepas audit pepijat: +11 jadual (model tanpa
> migration), +13 ujian regresi.

---

## 1. Seni Bina Teras

### 1.1 Empat Lapisan

```
┌──────────────────────────────────────────────────────────────┐
│  4. UI          Filament 3.3 · /admin · 66 resource · 6 widget│
├──────────────────────────────────────────────────────────────┤
│  3. API         routes/api.php + 58 provider · /api/v1/*     │
│                 1,518 laluan · auth:sanctum → tenant → perm:  │
├──────────────────────────────────────────────────────────────┤
│  2. DOMAIN      58 modul · 166 service · 292 model           │
├──────────────────────────────────────────────────────────────┤
│  1. ASAS        TenantContext · BelongsToTenant · Audit       │
│                 346 jadual · transactional outbox            │
└──────────────────────────────────────────────────────────────┘
```

**Peraturan tidak boleh dilanggar:**

1. **Tiada modul memanggil provider AI secara terus** — semua melalui `AIGateway\Services\GatewayService`.
2. **Tiada query tanpa skop tenant** — setiap model perniagaan guna trait `BelongsToTenant`.
3. **Menu tersembunyi BUKAN kawalan keselamatan** — API dikawal `perm:` + entitlemen modul.
4. **Tiada pembayaran dalam modul domain** — `Invoicing`/`Finance` sahaja memproses wang.

---

### 1.2 Pengasingan Tenant — 4 Lapisan

| Lapisan | Mekanisme | Fail |
|---|---|---|
| **1. Global scope** | Setiap query auto-tapis `tenant_id` | `app/Models/Concerns/BelongsToTenant.php` |
| **2. Auto-fill** | `creating` event set `tenant_id` dari konteks | sama |
| **3. Halang ubah** | `updating` event tolak perubahan `tenant_id` | sama |
| **4. Konteks** | `TenantContext::set($tenant, $membership)` | `app/Support/Tenancy/TenantContext.php` |

**Perangkap kritikal:**

```php
// ❌ Gagal — tiada konteks
TenantUser::create(['user_id' => $id, 'role' => 'owner']);

// ✅ Betul — tetapkan konteks DAHULU
$ctx->set($tenant);
TenantUser::create(['user_id' => $id, 'status' => 'active']);
```

- **`tenant_users` TIADA kolum `role`** — peranan melalui pivot `roles()`.
- Guna **`TenantProvisioner::create()`** untuk cipta tenant lengkap (peranan + langganan + pemilik).
- **`withoutGlobalScope('tenant')`** diperlukan dalam provisioning/CLI/ujian.

---

### 1.3 Middleware & Autentikasi

```
Route::prefix('v1')
  → middleware('auth:sanctum')   // identiti
  → middleware('tenant')          // konteks tenant
  → middleware('perm:{modul}.{aksi}')  // kebenaran
  → middleware('module:{key}')    // entitlemen plan
```

**Filament** guna guard `web` — middleware `tenant` sedia ada **TIDAK** berjalan.
`ResolveFilamentTenant` mesti didaftar dalam kumpulan **`web`**, bukan `authMiddleware` panel,
kerana Livewire memanggil `POST /livewire/update` di luar laluan panel.

---

## 2. Peta 58 Modul

> Laluan = bilangan endpoint `api/v1/<prefix>`. Angka daripada `php artisan route:list --json`.
>
> **Reconcile laluan:** 56 modul domain = **1,429** laluan · teras platform = **89** laluan
> (→ `auth`, `tenant`, `members`, `roles`, `invitations`, `entitlement`, `approvals`,
> `files`, `api-keys`, `audit`, `fundamental`, `webhooks`, `public`, `documents`,
> `admin`, `knowledge`, `tasks`, `timesheets`) · **JUMLAH = 1,518**.
>
> ⚠️ Jumlah melebihi 1,429 kerana beberapa modul mendedahkan lebih daripada satu
> prefix (cth `Projects` → `pm` + `projects` + `timesheets`; `MediaStudio` → `media`;
> `ContentStudio` → `content` + `knowledge`).

### 2.1 Jualan & Pemasaran (8 modul · 398 laluan)

| Modul | Prefix | Laluan | Peranan dalam sistem |
|---|---|---|---|
| **Site** | `site` | **128** | Pembina laman (ganti Shopify) — halaman, tema, domain, katalog, AI copy |
| **Marketing** | `marketing` | **101** | Automasi pemasaran — kempen, segmen, jujukan, baucar, atribusi 5 model |
| **CRM** | `crm` | **46** | Pipeline — pelanggan, kenalan, lead, peluang, sebut harga → pesanan |
| **Loyalty** | `loyalty` | **43** | Tahap, member, poin, tebus, ganjaran, ledger |
| **Affiliate** | `affiliate` | **39** | Rujukan, kod, klik, komisen, payout |
| **Blast** | `blast` | **15** | Enjin omnichannel (e-mel/SMS/WhatsApp) — 4 pagar penghantaran |
| **Link** | `link` | **15** | Pautan pendek & atribusi |
| **Prospecting** | `prospecting` | **11** | Cari prospek — scraper hormat robots.txt, consent PDPA |

### 2.2 AI & Automasi (15 modul · 227 laluan)

| Modul | Prefix | Laluan | Peranan |
|---|---|---|---|
| **ADE** | `ade` | **24** | **Agentic Development Environment** — A1 skills, A2 worktree, A3 coordinator, A4 review |
| **ContentStudio** | `content` | **24** | Jana → jadual → terbit 9 platform, komen inbox, chat, moderation |
| **BusinessIntel** | `business-intel` | **20** | AI self-improve — skor 6 dimensi, kajian, idea feed, competitor watch, learning loop |
| **PersonalAssistant** | `personal-ai` | **19** | Memori, tugas, jadual, RAG vector store |
| **AIAgents** | `ai-agents` | **18** | Sistem multi-ejen — definisi, orkestrasi, memori, matriks |
| **BrainHub** | `brainhub` | **18** | Otak ramalan — insight, ramalan, cadangan, anomali |
| **FlowAutomation** | `flows` | **18** | Pencetus → workflow → execute → retry → dead letter → kill switch |
| **Boardroom** | `boardroom` | **14** | Mesyuarat eksekutif AI — majlis, hujah, keputusan berstruktur |
| **McpStudio** | `mcp` | **14** | Protokol MCP `2025-06-18` — Claude Desktop, Cursor, Windsurf |
| **AgentStudio** | `agent-studio` | **13** | 16 templat ejen, persona, ikatan alat, 4 tahap otonomi, ujian |
| **AIGateway** | `ai-gateway` | **12** | Pintu tunggal AI — wallet, routing, PromptGuard, PII redact, circuit breaker |
| **WorkflowStudio** | `workflows` | **9** | Visual automation web-based — 18 node, dry-run lalai, template marketplace |
| **ExecutiveAI** | `executive-ai` | **7** | Otonomi 0–4, command centre, briefing, video blast |
| **SalesCopilot** | `sales-copilot` | **5** | Klasifikasi balasan, brief prospek, barisan susulan |
| **MediaStudio** | `media` | **12** | Jana video, transcript, understanding, persona, consent gate |

### 2.3 Operasi & Logistik (14 modul · 420 laluan)

| Modul | Prefix | Laluan | Peranan |
|---|---|---|---|
| **HRMS** | `hrms` | **88** | Pekerja, jabatan, syif, cuti, kehadiran, **biometrik + consent PDPA** |
| **Marketplace** | `marketplace` | **47** | Multi-vendor — produk, pesanan, cart, review, komisen |
| **Booking** | `booking` | **46** | Slot, temujanji, pengingat, penyedia servis |
| **VirtualOffice** | `virtual-office` | **42** | Ruang kerja maya |
| **ServiceOS** | `service-os` | **35** | Service Business OS — projek 8 status, sebut harga, kontrak, kru, outbox |
| **Inventory** | `inventory` | **28** | Stok & gudang — FIFO/FEFO, lejar, pemindahan, pelarasan |
| **SCM** | `scm` | **26** | Pembekal, pesanan beli, terimaan, KPI |
| **POS** | `pos` | **24** | Kaunter — daftar, jualan, item, bayaran, diskaun, pulangan, syif |
| **Logistics** | `logistics` | **22** | Penghantaran, kurier, gudang, tracking |
| **Reminder** | `reminders` | **18** | 14 jenis, 5 saluran, jadual berulang, eskalasi, kotak masuk AI |
| **Orders** | `orders` | **12** | **Pesanan unified** — 14 status kanonikal, rizab stok berpusat |
| **Fleet** | `fleet` | **13** | Armada & GPS — lokasi, tingkah laku memandu, 9 amaran |
| **Cod** | `cod` | **11** | COD PKS Malaysia — kelayakan, alamat MY, kutipan, reconciliation |
| **Manufacturing** | `manufacturing` | **8** | BOM, kos standard, work order, kit, variance |

### 2.4 Kewangan & People (7 modul · 202 laluan)

| Modul | Prefix | Laluan | Peranan |
|---|---|---|---|
| **Invoicing** | `invoicing` | **46** | Sebut harga → invois → **e-Invois LHDN**, nota kredit, bil berulang |
| **Projects** | `pm` | **35** | Projek & tugas — dependency, timesheet, milestone |
| **Assets** | `assets` | **33** | Aset & susut nilai — garis lurus/berkurangan, penjagaan, pelupusan |
| **Expense** | `expenses` | **26** | Perbelanjaan + resit OCR + tuntutan staff + post jurnal |
| **Finance** | `finance` | **26** | Akaun, jurnal, transaksi, saman bank, untung rugi, kunci akaun |
| **Payroll** | `payroll` | **21** | EPF 11%, EIS, SOCSO, HRDF, PCB, overtime, slip, eksport |
| **COGS** | `cogs` | **15** | Kos barang dijual — snapshot, margin, perbandingan |

### 2.5 Platform, Komunikasi & Analitik (12 modul · 182 laluan)

| Modul | Prefix | Laluan | Peranan |
|---|---|---|---|
| **Omnichannel** | `omnichannel` | **26** | Kedai + web + sosial, stok unified, pesanan hibrid |
| **SignDocs** | `sign-docs` | **25** | Tandatangan elektronik, audit trail, templat |
| **Analytics** | `analytics` | **25** | Metrik, dashboard, widget, laporan, export |
| **Seo** | `seo` | **18** | SEO semua objek — 13 peraturan audit, skor 0–100, JSON-LD, sitemap |
| **Vision** | `vision` | **14** | CCTV, RTSP, peristiwa, amaran, retensi |
| **DocumentAI** | `document-ai` | **13** | Ekstrak, klasifikasi, OCR, understanding, embedding |
| **MediaEditor** | `media-editor` | **13** | Canva/CapCut-class — 4 projek, 5 resolusi, 7 lapisan, render job |
| **Helpdesk** | `helpdesk` | **12** | Tiket, SLA, kategori, artikel, portal awam |
| **IndustryPacks** | `industry-packs` | **10** | Retail, F&B, servis, pembuatan, kesihatan |
| **LiveSupport** | `live-support` | **9** | Customer service live AI + eSIM, 3 pencetus eskalasi |
| **ProofOfValue** | `proof-of-value` | **9** | Metrik hasil per modul, baseline, sasaran, verifikasi, eksport |
| **Community** | `community` | **8** | Forum/academy/showcase/help, jawapan diterima, reputasi |

### 2.6 Teras Platform (bukan modul domain)

| Prefix | Laluan | Fungsi |
|---|---|---|
| `auth` | 7 | Daftar, log masuk, token |
| `tenant` | 3 | Tetapan tenant |
| `members` · `roles` · `invitations` | 7 | Pengurusan ahli & peranan |
| `entitlement` | 9 | Langganan, plan, modul aktif |
| `approvals` | 2 | Pintu kelulusan |
| `files` · `api-keys` | 5 | Fail & kunci API |
| `audit` | 1 | Jejak audit |
| `fundamental` | 11 | Health, metrics, backup |
| `webhooks` · `public` · `documents` | 15 | Webhook, awam, dokumen |
| `admin` | 7 | Pentadbiran platform |
| `knowledge` · `tasks` · `timesheets` | 11 | Pengetahuan, tugas, timesheet |

---

## 3. Lapisan AI — Satu Enjin, Banyak Muka

### 3.1 Aliran Wajib

```
Mana-mana modul
      ↓
AIGateway\Services\GatewayService::call()
      ↓
  0. PromptGuard      ← imbasan SEBELUM feature flag (JANGAN alih ke bawah)
  1. PiiRedactor      ← buang IC, telefon, e-mel
  2. Route model      ← reasoning / creative / quick
  3. Wallet check     ← canAfford() ? panggil : tolak
  4. Provider call    ← serveras.click (reseller)
  5. Sanitasi output  ← 4 corak output
  6. AiUsageEvent     ← log penggunaan & kos
```

**⚠️ Langkah 0 mesti kekal di atas.** Jika dialihkan selepas feature flag, mod
simulasi (`AI_GATEWAY_LIVE=false`) **tidak akan** disekat — pepijat keselamatan.

### 3.2 Lapan Muka AI

| Muka | Fungsi | Otonomi |
|---|---|---|
| **AIGateway** | Enjin asas — model, wallet, guard | — |
| **AIAgents** | 11 ejen peranan (CEO, CFO, COO, CISO…) | L0–L5 |
| **PersonalAssistant** | Memori + RAG per pengguna | — |
| **Boardroom** | 7 langkah mesyuarat → keputusan berstruktur | Cadangan |
| **ExecutiveAI** | Briefing, command centre, video blast | **0–4** |
| **AgentStudio** | 16 templat ejen boleh sesuaikan | **0–3** |
| **McpStudio** | Alat MCP untuk Claude/Cursor | HITL |
| **ADE** | Ejen bina & semak kod | A1–A4 |

### 3.3 Pagar AI (WAJIB)

| Pagar | Lokasi | Peraturan |
|---|---|---|
| **PromptGuard** | `GatewayService::call()` langkah 0 | 12 corak input, 4 output, bungkus sebagai data |
| **PiiRedactor** | langkah 1 | IC, telefon, e-mel tidak pernah ke provider |
| **Wallet** | langkah 3 | Tolak jika kredit habis |
| **Sentral kelulusan** | `AutonomyService` | Sentiasa perlu kelulusan: `send_payment`, `delete_data`, `change_autonomy`, `change_tenant_settings` |
| **ADE review** | `AdeReviewService::sahkan()` | Penemuan **kritikal** menyekat kelulusan |
| **Consent blast** | `ConsentService::bolehDihantar()` | consent + bukti + belum luput + saluran ada alamat |

---

## 4. UI Admin — Filament

| Aspek | Nilai |
|---|---|
| Versi | Filament **3.3.53** |
| Panel | `/admin` · guard `web` |
| Resource | **66** (semua 58 modul) |
| Widget | **6** — 4 KPI, trend 30 hari, donut saluran, 2 jadual |
| Halaman | Tetapan Syarikat (`/admin/tetapan`) |

### 4.1 Kebenaran Dua Lapis

| Lapisan | Mekanisme | Fail |
|---|---|---|
| **UI** | `HasModulePermission` — `canViewAny/canCreate/canEdit/canDelete` | `app/Filament/Concerns/` |
| **API** | `perm:{modul}.{aksi}` middleware | `routes/` |

`HasModulePermission` menyemak **RBAC + entitlemen modul**. Menu tersembunyi jika
plan tiada modul → tiada 403 mengejut. Fallback `.view` jika `.manage` tiada.

**Namespace → modul** (`ModuleGate::ALIAS`): `finance→expense` · `omnichannel→orders` ·
`projects→service_os` · `marketing→content_studio` · `brainhub→business_intel` · `ade→ade`

---

## 5. Pematuhan Malaysia

| Domain | Ciri | Status |
|---|---|---|
| **Gaji** | EPF 11% (majikan 13%/12%), SOCSO, EIS 0.2%, HRDF 1% (tiada had), PCB | ✅ |
| **e-Invois** | LHDN — invois, nota kredit/debit, validasi | ✅ |
| **PDPA** | Biometrik 6 pagar (consent, DPO, notis, retensi, semakan guaman) | ✅ |
| **Perbelanjaan** | Mileage RM0.60/km (LHDN), per diem RM50/hari = **default sistem** | 🟡 Sahkan dengan penasihat cukai |
| **COD** | Semak alamat Malaysia, kelayakan, reconciliation | ✅ |
| **SSM / MSIC** | Kod industri | 🟡 Separa |

⚠️ **Kadar berstatut berubah setiap tahun.** Sentiasa bandingkan dengan templat
KWSP/PERKESO/LHDN terkini sebelum penyerahan.

---

## 6. Status Jujur — Apa Yang Ada, Apa Yang Tidak

### 6.1 ✅ Siap & diuji

- 58 modul backend, 346 jadual, 1,518 endpoint, 962 ujian
- 66 resource Filament CRUD + 6 widget
- Pengasingan tenant 4 lapisan (292 model)
- AI Gateway + PromptGuard + PII redact + wallet
- Pematuhan gaji MY + e-Invois LHDN + biometrik PDPA
- Transactional outbox (`ServiceOS`, `FlowAutomation`)
- **ADE A1–A4** — skills, worktree, coordinator, review
- 0 advisori keselamatan

### 6.2 ⏸️ Belum siap (jujur)

| Perkara | Keadaan sebenar |
|---|---|
| **B-Receipt & B-Vision** (BAB 90-91) | **Spesifikasi sahaja — tiada kod, tiada jadual** |
| **ADE pelaksanaan kod** | Callback sedia; sambungan enjin P2 — kini dry-run |
| **RAG** | Hash-v1 (padanan perkataan, **bukan** semantik); Qdrant belum |
| **B-Migration Wizard** (T-72) | Tiada — halangan skor migrasi 2/10 |
| **Connector Pack 20 sistem** (T-75) | Tiada |
| **SOC 2 / ISO 27001** | Tiada — USD30k–80k, 6–12 bulan |
| **Integrasi bayaran (FPX)** | Tiada |
| **Telefoni LiveSupport** | Perlu kredensial Twilio/eSIM |
| **Renderer media** | Worker FFmpeg/Sharp luar belum |
| **UI domain-specific** | 66 resource = CRUD generik, bukan UI direka khusus |

### 6.3 Penilaian jujur

| Dimensi | Skor | Nota |
|---|---|---|
| Backend | **8/10** | Berfungsi, diuji, terasing |
| UI | **7/10** | Naik dari 2/10 (T-71); masih generik |
| AI | **7/10** | Guard kuat; pelaksanaan autonomi masih terbatas |
| Pematuhan MY | **8/10** | Kuat untuk MY; tiada per-negara lain |
| Migrasi masuk | **2/10** | Jurang terbesar |
| Ekosistem plugin | **1/10** | Jurang besar |
| Pematuhan enterprise | **3/10** | Tiada SOC 2 |

**Purata ≈ 5.1/10.** Pesaing: Zoho One 6.5, Dify 6.7, Retool 6.3, Odoo 6.0.

**Tiga syarat jadi platform universal:**
1. **B-Migration Wizard** — import + profil + simulator + export
2. **UI 7 → 9** — UI domain khusus untuk POS, CRM, ServiceOS
3. **SOC 2 Type II + ISO 27001**

> **Nasihat:** jangan tambah modul. Ketiga-tiga di atas ialah perbezaan antara
> "produk bagus" dan "platform yang semua orang guna".

---

## 7. Glosari Operasi

| Istilah | Maksud |
|---|---|
| **Tenant** | Satu organisasi pelanggan — semua data terasing |
| **Prefix** | Segmen pertama laluan API, cth `api/v1/orders` → `orders` |
| **Resource** | Halaman CRUD Filament untuk satu model |
| **Namespace permission** | Kunci sebelum `.` dalam `perm:`, cth `orders.view` |
| **Entitlemen** | Modul yang dibenarkan oleh plan tenant |
| **Otonomi** | Tahap AI bertindak: 0 manual → 4 automasi penuh |
| **Outbox** | Peristiwa domain disimpan dulu, dihantar kemudian (idempoten) |
| **Pagar** | Syarat wajib sebelum tindakan berisiko dibenarkan |
| **Worktree** | Salinan git terasing untuk satu subtugas ADE |

---

## 8. Cara Sahkan Dokumen Ini

```bash
# Angka struktur (modul, jadual, model, resource, kebenaran)
php skrip/semak-dokumen.php

# Ujian
php artisan test

# Laluan
php artisan route:list --path=api/v1 | Measure-Object -Line

# Laluan per prefix
php artisan route:list --path=api/v1 --json | ...
```

**Jika angka berbeza → dokumen ini lapuk, kod betul.**

---

## 9. Rujukan

| Dokumen | Isi |
|---|---|
| [`KATALOG-MODUL-AI-BIJAN.md`](KATALOG-MODUL-AI-BIJAN.md) | Katalog terperinci setiap modul |
| [`getting-started.md`](getting-started.md) | Setup dari sifar |
| [`BACA-SAYA.md`](BACA-SAYA.md) | Panduan pantas |
| [`MODUL-AI-INTEGRASI.md`](MODUL-AI-INTEGRASI.md) | Peta kebergantungan AI |
| [`BUSINESS-GROWTH-ENGINE.md`](BUSINESS-GROWTH-ENGINE.md) | 9 kelebihan + status kod |
| [`ANALISIS-KOMPETITIF.md`](ANALISIS-KOMPETITIF.md) | Pesaing & harga |
| [`AUDIT-DOKUMEN.md`](AUDIT-DOKUMEN.md) | Laporan audit dokumen |
| `../TODO.md` | T-01 → T-92 |
| `.github/skills/aibijan-coding/` | Skill + gotchas + peraturan keselamatan |
