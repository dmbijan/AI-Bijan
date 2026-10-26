# Competitive Advantage — Rujukan Pantas

Digest daripada 400+ pesaing dan blueprint BAB 81 + BAB 82. Rujuk untuk mana-mana
keputusan produk atau cadangan ciri baharu.

## Diagnosa Teras

**Backend gred produksi, UI gred demo.**

| Lapisan | Ukuran |
|---|---|
| Modul | 39 (34 asal + 5 BAB 82) |
| Endpoint | ~1,015 |
| Ujian lulus | 314 |
| View UI | 34 × purata 5.5 KB × 3 panggilan API |

`pos.blade.php` = 7.1 KB. POS sebenar perlu 800–1,500 baris. Kita ada rangka.

## Skor Kompetitif (kod sebenar, bukan rancangan)

| Dimensi | AI BIJAN | Zoho | Odoo | StoreHub | Bukku |
|---|---|---|---|---|---|
| Kedalaman fungsi | 5 | 9 | 9 | 6 | 5 |
| **UI** | **2** | 9 | 7 | 8 | 8 |
| Pematuhan MY | **8** | 3 | 5 | 6 | 8 |
| AI | **7** | 3 | 4 | 2 | 1 |
| Keselamatan | **9** | 7 | 6 | 5 | 6 |
| Kos (15 pekerja) | **9** | 3 | 5 | 8 | 10 |
| Onboarding | 4 | 8 | **2** | 9 | 8 |
| **Purata** | **6.3** | 6.0 | 5.4 | 6.3 | 6.6 |

**UI 2 → 8 menjadikan purata 7.2** (mendahului semua).

### Skor Dikemas Kini (BAB 83 — 3 dimensi baharu)

Audit lanjutan (`docs/AUDIT-PESAING-LANJUTAN.md`) mendedahkan 3 dimensi yang
**tidak pernah** diukur sebelum:

| Dimensi | AI BIJAN | Pemimpin | Nota |
|---|---|---|---|
| **Migrasi masuk** | **2** | Dolibarr 8 | Tiada alat import wizard |
| **Ekosistem plugin** | **1** | Odoo 9 | Marketplace kosong |
| **Pematuhan enterprise** | **3** | Dify 9 | Tiada SOC 2 / ISO 27001 |

| Dimensi | AI BIJAN | Zoho One | Odoo | Dolibarr | Dify | Retool |
|---|---|---|---|---|---|---|
| Kedalaman fungsi | 5 | 9 | 9 | 7 | 4 | 4 |
| **UI** | **2** | 9 | 7 | 6 | 8 | 9 |
| Pematuhan Malaysia | **8** | 3 | 5 | 3 | 1 | 1 |
| Keupayaan AI | **7** | 6 | 5 | 1 | 9 | 8 |
| Keselamatan & isolasi | **9** | 7 | 6 | 5 | 8 | 7 |
| Kos efektif (PKS 15) | **9** | 3 | 5 | 9 | 9 | 5 |
| Kemudahan onboarding | 4 | 8 | **2** | 6 | 7 | 8 |
| **Migrasi masuk** | **2** | 5 | 6 | 8 | 1 | 1 |
| **Ekosistem plugin** | **1** | 7 | 9 | 6 | 7 | 5 |
| **Pematuhan enterprise** | **3** | 8 | 8 | 5 | 9 | 9 |
| **Purata** | **5.0** | 6.5 | 6.0 | 5.6 | 6.7 | 6.3 |

> Skor turun 6.3 → 5.0 **bukan** sebab AI BIJAN melemah — tapi sebab 3
> dimensi baru diekspose. Yang kritikal: **Migrasi masuk (2)** dan **UI (2)**.

## Harga Pesaing (disahkan 24 Okt 2026)

| Produk | Harga | Nota |
|---|---|---|
| Bukku | RM0–135 + 8% SST | Perakaunan sahaja, pengguna tanpa had, e-Invois percuma |
| StoreHub | RM122/235/471 bulanan | POS + e-Invois, 20k pelanggan, per perniagaan |
| Zoho One | **RM148/pekerja** (WAJIB semua pekerja) | 15 pekerja = RM2,220/bulan |
| Odoo | USD13.50–25.50/pengguna | + pelaksanaan RM15k–80k |
| AI BIJAN | RM288 flat | 8× lebih murah dari Zoho untuk 15 pekerja |

**Ancaman gabungan:** Bukku (RM146) + StoreHub Starter (RM122) = **RM268** ≈ AI BIJAN.
Kedua-duanya sudah matang. Menang melalui **integrasi**, bukan harga.

## 9 Kelebihan (BAB 81) — Status Kod

| Kelebihan | Status | Skor |
|---|---|---|
| B-Owner Command Centre | 🟡 | 5/10 |
| B-Growth Engine | 🟡 | 6/10 |
| B-Sales Intelligence | 🟡 | 6/10 |
| B-Site AI Builder | 🟡 | 6/10 |
| B-Operations Industry Engine | 🟡 | 6/10 |
| **B-AI Voice** | ⛔ | 0/10 |
| B-Finance Intelligence | 🟡 | 6/10 |
| **B-Agent Workforce & Boardroom** | ✅ | 9/10 |
| B-Platform & White-Label | 🟡 | 5/10 |

1 siap, 7 separa, 1 tiada.

## 15 Idea Daripada Pesaing

| # | Idea | Sumber | Modul |
|---|---|---|---|
| 1 | Mod offline penuh | Loyverse ✅ | POS |
| 2 | Resit & notifikasi WhatsApp | Tiada pesaing | POS/Payroll/Invoicing |
| 3 | e-Invois LHDN | Bukku ✅, StoreHub ✅ | Invoicing |
| 4 | Customer/Kitchen display | Loyverse ✅ | POS |
| 5 | Portal pekerja layan diri | BambooHR | HRMS |
| 6 | Papan syif drag-drop | RotaCloud, Deputy | HRMS |
| 7 | EA Form + Borang E automatik | PayrollPanda | Payroll |
| 8 | Pipeline drag-drop visual | Pipedrive | CRM |
| 9 | AI bina laman dari perihalan | Durable, 10Web | Site |
| 10 | Kad loyalty Wallet | Belly | Loyalty |
| 11 | Segerak multichannel | SiteGiant ✅ | Marketplace |
| 12 | Imbasan barcode mudah alih | inFlow | Inventory |
| 13 | Laporan berjadual WhatsApp | Tiada pesaing | Analytics |
| 14 | Kad digital Wallet | Belly, Stampme | Link |
| 15 | MyDigitalID | Tiada pesaing | SignDocs |

**Tiga idea tiada pesaing langsung** — kesemuanya WhatsApp. Pesaing global bina
untuk e-mel; pesaing tempatan bina untuk POS.

## 4 Kelebihan Unik Yang Pesaing Tidak Boleh Tiru

1. **Kaitan CCTV + POS** — "12 masuk, 3 transaksi" → amaran. Vendor CCTV tiada data POS.
2. **Pematuhan HR MY + AI** — EPF/PCB/HRDF dikira betul + disambungkan ke operasi.
3. **Pagar biometrik PDPA** — 6 pagar dikuatkuasakan dalam kod.
4. **Laporan ROI terukur** — semua sumber data sudah wujud.

## Jangan Kejar

| Idea | Sebab |
|---|---|
| Marketplace sendiri | Tidak boleh menang rangkaian Shopee/Lazada |
| Enjin carian | Mustahil |
| Pengganti Excel | Terlalu luas |
| Perakaunan penuh | Bukku sudah menang RM0 |
| CRM enterprise | Salesforce tak boleh dikalahkan |
| Video conferencing | Tiada kaitan |
| Emel transaksional | Komoditi |

## Kesilapan Posisi Mesti Dielak

| Jangan cakap | Credits |
|---|---|
| "Ganti SAP" | "Untuk PKS, bukan perusahaan" |
| "39 modul siap" | "POS, CRM, HR, kewangan dalam satu" |
| "Voice interface" | Belum dikodkan — jangan sebut |
| "Collective intelligence" | Belum dikodkan — jangan sebut |
| "Proprietary model" | "Model terbaik untuk setiap tugas" |
| "AI urus perniagaan anda" | "RM82 kos → RM18,400 jualan" |
| "Jarvis untuk perniagaan anda" | "Pembantu eksekutif yang bertanya sebelum bertindak" |
| "Otonomi penuh" | "Tahap automasi anda yang anda pilih (0–4)" |

---

## BAB 82 — B-Executive AI (PART XVIII)

### 5 Modul Baharu

| Modul | Fasa | Wang | Ujian |
|---|---|---|---|
| `Prospecting/` | 1 | 6 jadual | 24 |
| `Blast/` | 2 | 6 jadual | 20 |
| `SalesCopilot/` | 3 | 0 jadual | 16 |
| `MediaStudio/` | 4 | 5 jadual | 17 |
| `ExecutiveAI/` | 5 | 3 jadual | 17 |

### Pagar Consent Blast (WAJIB)

Tiada mesej keluar tanpa: `consent_status='granted'` **DAN** `consent_evidence`
tidak kosong **DAN** `consent_expires_at` belum lepas **DAN** channel ada alamat.

```php
app(ConsentService::class)->bolehDihantar($prospect, 'email'); // false = jangan hantar
```

### 4 Pagar Penghantaran

```
Consent → Penindihan → Had kadar → Kelulusan kempen
```

### Tahap Autonomi 0–4

| Aras | Nama | Tindakan luar perlu kelulusan? |
|---|---|---|
| 0 | Tanya | Ya (semua) |
| 1 | Cadang | Ya (semua) |
| 2 | Draf | Ya (draf sah) |
| 3 | Laksana | Ya (satu klik) |
| 4 | Automasi | Tidak (kecuali daftar khas) |

**Sentiasa kelulusan** (walaupun aras 4): `send_payment`, `delete_data`,
`change_autonomy`, `change_tenant_settings`, `send_video_blast`.

### Pesaing Video / Pembantu AI

| Pesaing | Kekuatan | Celah AI BIJAN |
|---|---|---|
| HeyGen | Avatar realistik, 175 bahasa | Tak tahu data jualan/HR tenant |
| Synthesia | 140+ bahasa, SCORM/LMS | Fokus cipta video, tak run operasi |
| D-ID, Colossyan, Tavus | Interactive avatar | API/enterprise sahaja |
| Manus, Lindy | Agent autonomy | Tiada multimodal, tiada 34-modul data |

---

## BAB 83 — Universal Platform (Audit Lanjutan)

> Blueprint v8.6 · PART XIX · Audit: `docs/AUDIT-PESAING-LANJUTAN.md`

### 3 Dimensi Yang Perlu Dibina

| # | Keperluan | Skor | Sumber Pesaing | Modul |
|---|---|---|---|---|
| 1 | **B-Migration Wizard** | 2 → 7 | Dolibarr (import wizard + simulator + profil) | `Migration/` (baharu) |
| 2 | **Export penuh** (no vendor lock-in) | — | Odoo ("no vendor lock-in") | `Migration/` (baharu) |
| 3 | **B-Agent Studio + MCP server** | — | Zoho Zia, Dify, CrewAI | `AIAgents` |
| 4 | **AI metrics dalam jualan** | — | Help Scout (73% resolve, 56% gain, 25% CSAT) | `Helpdesk`, `ExecutiveAI` |
| 5 | **Agentic commerce** | — | Shopify UCP, Stripe agentic | `B-Site`, `Blast` |
| 6 | **AI-first eSignature + WhatsApp signing** | — | Docusign Iris | `SignDocs` |
| 7 | **Local Presence sync** | — | Zoho Publish | `Marketplace` |

### PeraturanAI Metrics

> Tiada ciri AI boleh diiklankan tanpa **satu** metrik yang boleh diukur.

| Metrik | Sasaran |
|---|---|
| AI resolution rate | ≥ 50% (Help Scout 73%) |
| First response gain | ≥ 40% (Help Scout 56%) |
| CSAT uplift | ≥ 15% (Help Scout 25%) |
| Auto-close accuracy | ≥ 85% |

### Pesaing Baharu Yang Diaudit (15)

| Produk | Idea Diambil |
|---|---|
| Zoho One / Zia | Zia Agent Studio, Zoho Publish, privacy stance |
| Odoo | No vendor lock-in, plugin ecosystem, 90ms latency SLO |
| Dolibarr | ⭐ Import/Export Wizard + simulator + Module Builder |
| Dify | ⭐ MCP server, Knowledge Pipeline, Plugin Marketplace, 3 deployment modes |
| CrewAI | Centrally governed agents, lead enrichment agents |
| Retool | Centralized agent harness, internal apps builder |
| Shopify | ⭐ Agentic Storefronts, Sidekick, UCP |
| Stripe | ⭐ Agentic commerce payment rail, MCP server, Connect model |
| ServiceTitan | 70+ pre-built integrations, real-time costing |
| Help Scout | ⭐⭐ AI metrics, 4-product model, per-industry pages |
| Docusign | ⭐ Iris agreement detection, WhatsApp delivery |
| Microsoft 365 | ⚠️ belum disahkan |

### Verdict: Boleh Jadi #1 Dunia?

**Boleh — tetapi bukan dengan apa yang ada sekarang.** Skor semasa **5.0/10**
(di belakang Zoho 6.5, Dify 6.7, Retool 6.3).

**3 syarat wajib:**

| # | Syarat | Status |
|---|---|---|
| 1 | Migrasi masuk yang mudah | ⛔ kena dibina |
| 2 | UI dari 2 → 7 | ⛔ kena dibina |
| 3 | SOC 2 Type II + ISO 27001 | ⛔ kena dibina |

**Pelan 3 tahun:**

| Tahun | Fokus | Sasaran |
|---|---|---|
| 1 | Boleh Pindah (Migration + UI + AI metrics) | 2,000 tenant MY, skor 7.0 |
| 2 | Boleh Skala (Agent Studio + MCP + marketplace) | 15,000 tenant SEA, skor 8.0 |
| 3 | Boleh Mendominasi (white-label + collective intelligence) | 60,000 tenant SEA + 500 enterprise |

**Nasihat:** Jangan tambah modul. 3 syarat di atas adalah perbezaan antara
"produk bagus" dan "platform yang semua orang guna".

---

## BAB 84 — Reverse Engineering Mendalam: 200 Apps + 50 OSS

> Blueprint v8.8 · PART XX · Audit: `docs/REVERSE-ENGINEERING-200-APPS-50-OSS.md`
> **50 resource setiap platform** (Shopify, Odoo, Zoho, ServiceTitan) + 50 OSS.

### 5 Penemuan Baharu

| # | Penemuan | Bukti |
|---|---|---|
| 1 | **MCP server = semula** — 5 app Odoo | 9,094 + 1,862 + 1,790 + 505 + 261 dl |
| 2 | **Zoho Agent Store** — 16 ejen AI khusus | 16 ejen bernama |
| 3 | **ServiceTitan Pro Products** — 13 produk AI | **6 metrik hasil rasmi** |
| 4 | **Compliance per negara** di Odoo | Vietnam VAS 4 apps + GST |
| 5 | **AI dashboard builder** = kategori standard | 15,941 + 1,107 + 3,136 dl |

### Angka Marketplace

| App Store | App | Angka | Bukti |
|---|---|---|---|
| Shopify | **TikTok** | **16,011 review** | Saluran jualan |
| Shopify | Shopify Flow | 15,009 review | #1 automation |
| Shopify | PageFly AI | 5,917 review | AI CRO |
| Shopify | Pandectes GDPR | 3,108 review | Consent banner |
| Shopify | **EasySell COD Form** | 989 review | ⭐⭐ **COD — jurang MY** |
| Odoo | Accounting Community | **125,350 dl** | `Finance` |
| Odoo | **Tweak Odoo / OEM Brand** | **53,777 dl** | ⭐⭐⭐ **WHITE-LABEL** |
| Odoo | **Odoo AI Dynamic Dashboard** | **15,941 dl** | ⭐⭐⭐ AI dashboard |
| Odoo | **Hide Menu User & Group** | **21,723 dl** | ⭐⭐⭐ Per-user UI |
| Odoo | **MCP Server** | **9,094 dl** | ⭐⭐⭐ MCP |
| Odoo | **Zk Biometric Device** | 22,333 dl | ⭐⭐⭐ AI BIJAN **sudah ada** |
| Zoho | Zoho Meeting for Mail | **1,389 rating** | Integrasi |
| Zoho | **Check Printing** | 160 rating | ⭐⭐⭐ **Cek — MY** |
| ServiceTitan | **Marketing Pro** | **2x** revenue growth | ⭐⭐⭐ Metrik hasil |
| ServiceTitan | **Fleet Pro** | **75%** less accident risk | ⭐⭐⭐ Fleet — tiada dalam AI BIJAN |

### 15 Idea Teratas

| # | Idea | Sumber | Kesan |
|---|---|---|---|
| 1 | ⭐ **MCP server + AI Studio + human-in-the-loop** | Odoo (5 apps), Zoho Agent Store | 🔴 Jangkauan tanpa jualan |
| 2 | ⭐ **FilamentPHP UI builder** | aureuserp (MIT) | 🔴 **UI 2 ke 7** |
| 3 | ⭐ **B-Migration Wizard + AI auto-mapping** | Matrixify, Dolibarr | 🔴 **Migrasi 2 ke 7** |
| 4 | ⭐ **AI dashboard builder** | Odoo (15,941 dl) | 🔴 UI + metrics |
| 5 | ⭐ **Metrik hasil per modul** | ServiceTitan (6 metrik) | 🔴 Jualan ROI-based |
| 6 | ⭐ **Cart recovery WhatsApp + COD** | CK, Dondy, EasySell | 🔴 **Revenue MY** |
| 7 | ⭐ **Granular UI access per user** | Hide Menu (21,723 dl) | 🔴 UI + keselamatan |
| 8 | ⭐ **White-label / OEM pattern** | Tweak Odoo (53,777 dl) | 🟠 `BAB 4a` |
| 9 | ⭐ **Compliance per negara** | Vietnam VAS, GST | 🟠 Expand SEA |
| 10 | ⭐ **Fleet & GPS** | Samsara, Fleetio, Motive | 🟠 `Logistics` baru |
| 11 | ⭐ **Connector pack 20+ sistem** | ServiceTitan 70+ | 🟠 `Migration` |
| 12 | ⭐ **AI transkrip panggilan + sales coaching** | Callture, Sales Pro | 🟠 `MediaStudio` |
| 13 | ⭐ **AI Voice Agent overflow** | ServiceTitan AI Voice | 🟠 `ExecutiveAI` |
| 14 | ⭐ **Meddicc scoring + NPS predictor** | Meddicc, NPS Predictor | 🟠 `SalesCopilot` |
| 15 | ⭐ **PDPA consent banner + T&C** | Pandectes (3,108) | 🟠 `Site`/`Helpdesk` |

### 16 Zoho Agent Store (rujukan B-Agent Studio)

Advanced Sales Coach · Instant Lead Response · Lead Intelligence · Case Deflection ·
Intelligent Inventory · NPS Predictor · Deal Gap Analyser · Lost Deal Analysis ·
Onboarding Handover · Deal Contribution · Day Planner · Out-of-Office Intel ·
Multi-Stakeholder · Voice Call Summarizer · Outdated Solution ID · Spiky.ai

### 20 OSS Tier 1 (MIT/Apache-2.0 — boleh rebrand)

liteerp(MIT) · aureuserp(MIT, **Filament**) · akaunting(MIT) · NexoPOS(MIT) ·
lakasir(MIT, **Bahasa Indonesia=SEA**) · triangle-pos · astronaut-agent(**Apache-2.0**) ·
hatchet(MIT) · inngest(Apache-2.0) · conductor(Apache-2.0) · flowable(Apache-2.0) ·
dtm(Apache-2.0) · temporal(MIT) · receiptline(MIT) · grocy(MIT) · open-mercato(MIT) ·
Archon · carbon(**MES+QMS+MCP**) · WebErpMesv2(Laravel+e-invoicing) · moqui-mcp

### Tier 2 — GPL/AGPL: RUJUKAN SAHAJA ⛔

`twenty` · `nocobase` · `openblocks` · `steedos` · `budibase` · `krayin` ·
`Dolibarr` (⭐ Import Wizard + Module Builder) · `espocrm` · `ever-gauzy` ·
`SuiteCRM` · `monica`

### Peraturan Lesen

| Lesen | Boleh? |
|---|---|
| AGPL/GPL | ❌ Jangan salin kod. Rujukan reka bentuk sahaja |
| MIT/Apache-2.0/BSD | ✅ Guna, ubah, rebrand, commercial. **Wajib letak copyright asal** |
| BSL/SSPL | ❌ Bukan OSS |

> Wajib ada fail `THIRD-PARTY-NOTICES.md` untuk setiap OSS yang diincorporate.
> ⚠️ Angka stars GitHub TIDAK dipaparkan tanpa login — jangan guna dalam promosi.

---


- `docs/PETA-PESAING-34-MODUL.md` — 400+ pesaing, 34 modul
- `docs/AUDIT-PESAING-LANJUTAN.md` — 15 pesaing baharu (BAB 83)
- `docs/REVERSE-ENGINEERING-200-APPS-50-OSS.md` — 200 apps + 50 GitHub OSS (BAB 84)
- `docs/ANALISIS-KOMPETITIF.md` — analisis mendalam 5 pesaing teras
- `docs/CADANGAN-IMPAK-TINGGI.md` — 15 cadangan, urutan 17 minggu
- `docs/PROSPEK-BLAST-CLOSE.md` — panduan enjin prospect→blast→close
- Blueprint BAB 81 (persaingan) + BAB 82 (B-Executive AI) + BAB 83 (universal platform) + BAB 84 (apps/OSS)

## Nota

Data bertanda ✅ disahkan dari laman rasmi. ⚠️ = dikenal pasti, belum disahkan.
**Jangan** tukar ⚠️ jadi ✅ tanpa menyemak laman rasmi.

---

## BAB 85 — Implementasi: 5 Modul Baharu (PART XXI)

> Blueprint v8.9 · PART XXI · `docs/KATALOG-MODUL-AI-BIJAN.md`
> **Strategi (BAB 84) → Kod (BAB 85).** 5 Idea tertinggi ditukar kepada modul sebenar.

### Idea → Modul (STATUS KOD SEBENAR)

| Idea | Modul | Prefix | Jadual | Laluan | Status |
|---|---|---|---|---|---|
| #1 MCP server | `McpStudio` | `/api/v1/mcp` | 6 | 11 | ✅ Selesai |
| #5 Metrik hasil | `ProofOfValue` | `/api/v1/proof-of-value` | 4 | 9 | ✅ Selesai |
| #6 COD | `Cod` | `/api/v1/cod` | 4 | 11 | ✅ Selesai |
| #8 Agent Studio | `AgentStudio` | `/api/v1/agent-studio` | 5 | 11 | ✅ Selesai |
| #10 Fleet & GPS | `Fleet` | `/api/v1/fleet` | 6 | 13 | ✅ Selesai |

**Jumlah:** 25 jadual, 54 laluan, 168 ujian baharu, 486 ujian keseluruhan (0 gagal).

### Pagar Keselamatan MCP (WAJIB untuk sebarang tool calling)

1. **Allowlist** — `McpToolRegistry::ALLOWLIST` (modul → tindakan). Tindakan luar
   senarai ditolak **sebelum** query. Tiada `ToolExecutor` bebas.
2. **Human-in-the-loop** — alat `is_destructive` → status `awaiting_approval`.
   `_luluskan()` / `_tolak()` hanya boleh satu kali.
3. **Audit** — setiap panggilan ke `mcp_audit_logs`: `tool_called`, `tool_approved`,
   `tool_rejected`, `tool_executed`, `resource_read`, `prompt_used`.
4. **Had baris** — maksimum 100 (default 25) untuk `list`/`read`.

### Corak B-Agent Studio

`templat → sesuaikan → ujian → publish`. Publish **perlukan** ≥1 alat aktif + ≥1
ujian lulus. 4 tahap otonomi: `0 Manual` · `1 Cadangan` · `2 Separuh Auto` · `3 Penuh`.
Otonomi 0–1 **wajib** kelulusan manusia untuk tindakan tulis.

### Corak B-ProofOfValue

Katalog 8 modul × 2–3 metrik. Setiap metrik: `baseline` (sebelum) → `readings`
(harian) → `target_value` + `direction` → `is_verified`. Ringkasan exposes
`kadar_verifikasi` + `sasaran_tercapai` — ini bahan jualan ROI.

### Corak B-COD

Aliran status **divalidasi** — perpindahan tidak sah ditolak tanpa menjejaskan
rekod (`tukarStatus()` pulangkan order tanpa perubahan + log amaran).
`reconcilesi()` banding dijangka vs dikutip → `jurang` + `kadar_kutipan`.

### Corak B-Fleet

Lokasi terkini = satu baris per kenderaan (`fleet_vehicle_locations`) + sejarah penuh
(`fleet_location_histories`). Tingkah laku dikesan automatik pada setiap rekod.
Amaran dedup 30 minit (`cetusAmaran()`). Skor keselamatan = 100 − penalti tingkah laku.

---

## BAB 86-87 — Ekosistem Operasi: 9 Modul + ADE (v9.0)

> Blueprint v9.0 · PART XXII · `docs/KATALOG-MODUL-AI-BIJAN.md`
> **53 modul · 311 jadual · 718 ujian lulus (0 gagal).**

### 9 Modul Baharu (permintaan pengasas → kod)

| Permintaan | Modul | Prefix | Jadual |
|---|---|---|---|
| Order mgmt satu tempat + stok synced | `Orders` | `/api/v1/orders` | 6 |
| SEO semua objek | `Seo` | `/api/v1/seo` | 7 |
| Auto-gen + auto-post + jaga komen/chat | `ContentStudio` | `/api/v1/content` | 10 |
| Reminder | `Reminder` | `/api/v1/reminders` | 5 |
| AI self-improve + kajian + pesaing | `BusinessIntel` | `/api/v1/business-intel` | 7 |
| n8n-style web-based automation | `WorkflowStudio` | `/api/v1/workflows` | 3 |
| Canva/CapCut media editor | `MediaEditor` | `/api/v1/media-editor` | 3 |
| Customer service live AI + eSIM | `LiveSupport` | `/api/v1/live-support` | 3 |
| Komuniti AI BIJAN | `Community` | `/api/v1/community` | 3 |

### Pagar Keselamatan WAJIB (modul baharu)

1. **Orders** — `ALIRAN` map menolak perpindahan status tidak sah; ingest idempoten pada
   `(tenant, source, platform, source_id)`; rizab stok → commit → release.
2. **ContentStudio** — `credentials` cast `encrypted:array` + `makeHidden()`; **auto-publish
   LALAI MATI** (perlu opt-in per platform); had posting harian per platform.
3. **Seo** — audit dijalankan pada setiap `simpanMetadata()`; cadangan AI tidak pernah
   auto-apply (manusia terima/tolak).
4. **BusinessIntel** — AI **tidak boleh** menanda bayaran berjaya tanpa rekod penyedia disahkan;
   `learning loop` merekod setiap penerimaan/penolakan.
5. **WorkflowStudio** — `dry_run_mode` **LALAI TRUE**; node `action.*` hanya berjalan bila
   bukan dry-run; had 100 node/run + kedalaman 25 + fail-fast.
6. **LiveSupport** — 3 pencetus eskalasi (kata kunci kritikal, sentiment negatif, keyakinan
   < ambang); 12 kata kunci kritikal sentiasa ke manusia.
7. **MediaEditor** — `sahkanLapisan()` memaksa x/y; text perlu `text`; image/video/audio perlu `src`.

### Corak penting yang dipelajari

- **Jadual `studio_*`** — nama `workflows`/`workflow_runs` BERTINDIH dengan FlowAutomation
  (`workflow_definitions`). Modul baharu MESTI guna prefix unik.
- **Route ordering** — laluan statik (`/summary`, `/templates`, `/inbox/list`) MESTI
  didahulukan sebelum `/{id}` atau ia ditangkap sebagai id.
- **`updateOrCreate` dengan kolum `date`** — perbandingan tarikh gagal; guna
  `whereDate()->first()` + `create()`/`update()` berasingan.
- **`array_merge($defaults, $data)`** — jika `$data` ada kunci bukan-kolum (`items`), mesti
  `unset()` sebelum mass-assignment.
- **Status platform vs kanonikal** — `array_merge` akhir menang; pastikan status kanonikal
  dimasukkan SELEPAS `$data` dalam gabungan.

### ADE — Agentic Development Environment (BAB 86.11)

4 ciri teras: (1) Autonomous Orchestration, (2) Task & Worktree Isolation,
(3) Multi-Harness & Multi-Model, (4) Built-in Verification & Review.

Pelan 4 fasa: **A1 Skills Engine** → **A2 Worktree Isolation** → **A3 Sub-agent Coordinator**
→ **A4 Review Surface**.

Corak daripada `obra/superpowers` (MIT, 304k★): brainstorming → worktrees → plans →
subagent-driven → TDD → review → finish. **Falsafah:** TDD · systematic over ad-hoc ·
complexity reduction · evidence over claims.

### Penilaian jujur "One Man Show Billion Company" (BAB 86.12)

| Soalan | Jawapan |
|---|---|
| Satu orang boleh urus operasi? | **Ya** — 53 modul + 20 penjadual automatik |
| Boleh jadi bilion? | **Bukan seorang.** Bilion ≈ 60,000 tenant. Perlu 5–8 jurutera + 2–3 jualan. |
| Apa yang menghalang? | **Bukan ciri** — ia **kepercayaan** (SOC 2) + **pengagihan** (SEO/komuniti) + **UI** (2/10). |

---

## BAB 88-89 — Audit B-POS & B-Expense (v9.1-9.2)

> Blueprint v9.2 · PART XXIII-XXIV · `docs/KATALOG-MODUL-AI-BIJAN.md`
> **55 modul · 331 jadual · 829 ujian lulus (0 gagal).**

### BAB 88 — Audit B-POS System (bukan modul)

B-POS (`C:\ai-bijan\AI-Bijan\b-pos-system`) = 109 migration, 61 model, 30 service,
73 controller. Asas MIT (`aryadwiputra/point-of-sales`).

**KEPUTUSAN: JANGAN GABUNG.** B-POS tiada `tenant_id` langsung (satu deployment = satu
syarikat). Menggabung = tulis semula 61 model + 109 migration. B-POS kekal produk
berasingan; ciri dipindah satu per satu (T-76 → T-84).

6 jurang kritikal B-POS: dine-in/kitchen display, modifier, service charge, multi-tender,
offline PWA, cashier shift. 9 isu keselamatan (paling kritikal: `legacySingleOutletBypass`
lalai TRUE).

**Corak B-POS yang patut ditiru:**
- `client_uuid` + `sync_fingerprint` → idempotency offline (sama seperti `ServiceOS`
  `idempotency_key`)
- `TransactionTender` sebagai entiti berasingan (bukan kolum `cash`/`card_amount` berasingan)
- `AuditLog` snapshot before/after
- `TransactionDetailBatchAllocation` → rekod batch FEFO

**Jangan tiru:** 4 jadual wilayah Indonesia, `whatsapp-web.js`, `access_token` uuid tanpa
had masa, `legacySingleOutletBypass`.

### BAB 89 — B-Expense P1 (modul baharu #55)

`app/Modules/Expense/` · 7 jadual (`expense_*`) · 33 laluan · 48 ujian.

**Tertiary reuse (tiada kod duplikasi):** OCR → `DocumentAI`; jurnal →
`Finance\JournalService`; resit → `DocumentAI\Document`; COA → `Finance\ChartOfAccount`.

**3 lapisan keselamatan wajib:**
1. Upload resit → **draf sahaja** (tiada auto-posting)
2. Kelulusan manusia WAJIB; **pemohon tak boleh lulus tuntutan sendiri** → `RuntimeException`
3. AI hanya *mencadangkan* kategori — tak boleh set `approved_amount` atau sahkan bayaran

**Nilai lalai (konfigurasi):** kelulusan RM200 · resit wajib RM50 · mileage RM0.60/km ·
per diem RM50/hari. ⚠️ Kadar cukai/mileage/per diem = default sistem — MESTI disahkan
tenant/penasihat cukai sebelum guna sebenar.

### Perangkap baharu (BAB 88-89)

- **Join + global scope tenancy** → `ambiguous column name: tenant_id`. Guna
  `withoutGlobalScopes()` + qualify semua kolum (`expenses.tenant_id`) + `whereNull('expenses.deleted_at')`.
- **Relation Eloquent di-cache** — `$model->relation()->count()` guna data LAMA selepas
  `create()`. Guna kiraan terus dari DB.
- **`orderByDesc` atas relation yang dah ada `orderBy`** → dua ORDER BY, yang pertama menang.
  Gunakan `where('action', ...)` bukan pengurutan.
- **`TenantContext` tiada method `user()`** — guna `auth()->id()`.
- **Model `HasFactory` + migration tanpa `timestamps()`** → setiap `create()` gagal.
  Semak migration untuk kolum `created_at`/`updated_at`.
- **Nama kaedah boleh rosak** bila ditulis melalui alat tulis automatik — sahkan dengan
  `bin2hex()` / `get_class_methods()`.
- **`decimal:2` cast** pulang `'1560'` bukan `'1560.00'` — dalam ujian guna
  `number_format((float) $x, 2, '.', '')`.
