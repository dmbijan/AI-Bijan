# Audit Pesaing Lanjutan — AI BIJAN Bab 83

**Tarikh:** 24 Oktober 2026
**Skop:** Pesaing yang **belum pernah** diaudit dalam `PETA-PESAING-34-MODUL.md`
(34 kategori modul, 400+ pesaing) atau `ANALISIS-KOMPETITIF.md` (5 pesaing teras).

> ⚠️ **Nota (27 Okt 2026):** "34" merujuk **kategori modul** dalam peta pesaing,
> bukan bilangan modul dalam kod. Kod kini mempunyai **59 modul** — lihat
> `KATALOG-MODUL-AI-BIJAN.md`.
**Kaedah:** Semakan laman rasmi. Semua data bertanda ✅ disahkan 24 Okt 2026.

---

## Mengapa Audit Ini Penting

Peta pesaing sedia ada audits **modul per modul**. Ia tidak audits **corak
perniagaan** yang pesaing guna untuk-raikan pelanggan ke sistem mereka.

Terdapat 6 jurang strategik:

| # | Jurang | Akibat |
|---|---|---|
| 1 | Tiada pesaing **migrasi** legacy ke AI BIJAN | Orang tak boleh pindah |
| 2 | Tiada pesaing **agentic** (ejen otonom) | Zia, CrewAI, Dify, Retool ada |
| 3 | Tiada **agentic commerce** | Shopify UCP, Stripe agentic |
| 4 | Tiada **AI-first helpdesk** dengan metric | Help Scout Scout = 73% resolve |
| 5 | Tiada **AI-first eSignature** | Docusign Iris |
| 6 | Tiada **universal + low-code** | Retool, Dolibarr Module Builder |

---

## Kumpulan 1 — Platform All-in-One (Universal SaaS)

### 1.1 Zoho One — "The operating system for business" ✅

| Aspek | Data |
|---|---|
| Skala | 150M+ pengguna, 150+ negara, 60+ produk, 30+ tahun, 19K+ pekerja |
| Harga | RM148/pekerja/tahun WAJIB semua pekerja dalam payroll, atau RM360/pengguna fleksibel |
| Model | Tiada iklan, tidak jual data, milik stack sendiri (data center sendiri) |

**Idea yang AI BIJAN belum ada:**

| Idea | Nilai untuk AI BIJAN |
|---|---|
| **Zia Agent Studio** — "digital workforce" | Ejen otonom qualify leads, resolve tickets, draf emel. AI BIJAN ada `AIAgents` tapi tiada studio visual |
| **Zoho Catalyst** — build apps/websites/APIs dengan coding agents | AI BIJAN `Site` ada; Catalyst = tambah AI bina dalaman |
| **60+ produk dari 1 vendor** | Sahkan positioning "one vendor" |
| **Privacy: tidak jual data, tak rely iklan** | AI BIJAN mesti cakap sama — ia kini platform white-label |
| **Zoho Publish** — manage semua local business listings | **Modul Marketplace baru** — sync Google Business Profile, Facebook Page |

> **Idea #1: "B-Agent Studio"** — visual builder untuk ejen AI + persona video
> + kelulusan, supaya tenant boleh bina ejen sendiri tanpa coder.

### 1.2 Odoo — "All your business on one platform" ✅

| Aspek | Data |
|---|---|
| Pengguna | 28 juta pengguna |
| Skala | 40k+ community apps, 100k+ developers |
| AI | "Native AI across all your business", Odoo.sh vibe-code |
| Harga | Community 100% free, Enterprise USD13.50/25.50 per pengguna/tahun |

**Idea yang AI BIJAN belum ada:**

| Idea | Nilai untuk AI BIJAN |
|---|---|
| **No vendor lock-in** — PostgreSQL, source code, host sendiri/on-prem | AI BIJAN mesti tunjuk **data export penuh** |
| **40k+ community apps store** | AI BIJAN ada `PluginMarketplace` (BAB 46) — tapi kosong. Ini laluan plugins |
| **AI Toolkit for devs** + **Agentic Storefronts** + **Campaign Autopilot** (2026) | AI BIJAN AI Gateway boleh jadi "AI toolkit" untuk devs |
| **"All operations done in less than 90ms"** | AI BIJAN perlu **latency SLO** sebagai ciri jualan |
| **"Simple, efficient, yet affordable"** | Pesan harga — AI BIJAN RM288 flat vs Odoo RM1,695/15 pengguna |

### 1.3 Dolibarr — Open Source ERP & CRM ✅

| Aspek | Data |
|---|---|
| Skala | "Millions of users worldwide", komuniti ribuan developer |
| Versi | V24 (Okt 2026), 2003-2026 |
| Distribusi | On-premise / SaaS / Docker / auto-installer Windows-Mac-Linux |
| Sasaran | "SMEs, Large Companies, Freelancers, Associations" — satu produk 4 saiz |

**Idea yang AI BIJAN belum ada:**

| Idea | Nilai untuk AI BIJAN |
|---|---|
| **Import/Export Wizard dengan simpan profil** | AI BIJAN ada import CSV ringkas. Dolibarr: pilih profil export, tentukan medan, simpan profil, guna semula satu klik. Plus **simulator** (dry-run) sebelum import sebenar |
| **Module Builder (low-code, no-code)** | AI BIJAN `PluginMarketplace` perlukan developer. Dolibarr: **hanya admin** boleh tambah modul. Ini kunci "universal" |
| **Multi-distribusi** (on-prem / cloud / Docker) | AI BIJAN ada `BAB 64` Kedaulatan Data — perlu dibina semula untuk jadi real |
| **Tiada kos tambahan** | AI BIJAN RM288 flat |

> **Idea #2: "B-Migration Wizard"** — **paling kritikal** untuk pertumbuhan sedia ada.
> Tanpa migrasi mudah, tiada siapa yang bertukar sistem.

### 1.4 Microsoft 365 / Business Apps ⚠️

Ditetapkan secara tidak langsung (redirect OAuth). Data tidak disahkan — tandakan ⚠️.

**Idea luaran (perlu disahkan):** Graph API + Power Platform + Copilot =
standard enterprise. AI BIJAN mesti ada connector Microsoft untuk import
Outlook/Excel/Teams, dan API sendiri yang boleh disambungkan.

---

## Kumpulan 2 — AI-Native Agentic Platforms (Bahaya Tertinggi)

### 2.1 Dify — "The Platform for Production-Ready Agentic Workflows" ✅

| Aspek | Data |
|---|---|
| Skala | 159,373 GitHub stars. Pelanggan: Maersk, Adobe, Google, Panasonic, PayPal, Mercedes-Benz, Volvo, McDonald's, KPMG, Deloitte, MITRE |
| Deployment | **3 mod**: Cloud (managed), Enterprise (self-host/VPC, SSO/SAML, RBAC, audit logs, SOC 2 Type II + ISO 27001, Helm K8s, CSM 24/7), Community (Docker, Apache-2.0-derivative) |
| Komponen | Workflow Studio (canvas visual), Agent (tools, memory), Knowledge Pipeline (extract-clean-chunk-index-test retrieval), **Plugin Marketplace**, Publish (hosted app, API, embed, **MCP-compatible tool**) |

**Idea yang AI BIJAN belum ada:**

| Idea | Nilai untuk AI BIJAN |
|---|---|
| **Publish as MCP-compatible tool** | AI BIJAN `AIAgents` boleh jadi **MCP server** — boleh digunakan oleh Claude Desktop, Cursor, Dify, CrewAI. **Strategik brilliance**: AI BIJAN boleh terus digunakan tanpa jualan langsung |
| **Knowledge Pipeline dengan "test retrieval"** | AI BIJAN ada `EmbeddingService`/`VectorStoreService` (hash-v1) — belum ada UI untuk test retrieval |
| **Plugin Marketplace** | AI BIJAN `PluginMarketplace` (BAB 46) kosong. Dify ada model provider + tool + MCP + data source plugins |
| **3 mod deployment** | AI BIJAN `BAB 64` ada conceptually — perlu dibina |
| **Workflow Studio — visual canvas** | AI BIJAN `FlowAutomation` ada trigger/kondisi secara JSON. Dify ada canvas node |
| **SOC 2 Type II + ISO 27001** | AI BIJAN belum ada pensijilan — ini halangan enterprise |

### 2.2 CrewAI — "The Enterprise Agent Build & Runtime" ✅

| Aspek | Data |
|---|---|
| Skala | 65% daripada Fortune 500. 450M+ agentic workflows sebulan. 4,000+ signups seminggu |
| Pelanggan | DocuSign, Experian, PepsiCo, IBM, CHG, AB InBev, General Assembly, Gelato, Konecta, Piracanjuba |
| Model | Unified build & runtime platform, centrally governed |

**Idea yang AI BIJAN belum ada:**

| Idea | Nilai untuk AI BIJAN |
|---|---|
| **⭐ "Centrally governed" agent platform** | AI BIJAN ada `AgentGovernance` untuk SATU tenant. Enterprise perlaku **semua tenant**ejen dari satu pusat |
| **⭐ Standarditi agent harness** | Satu standard untuk bina + jalankan ejen, semua industri |
| **Lead enrichment agents** (DocuSign: 75% faster first contact) | AI BIJAN ada `Prospecting` Fasa 1 — boleh jadi **enrichment agent** |
| **Voice agent testing** (74 jam QA → 3 jam) | AI BIJAN `ExecutiveAI` voice belum ada; idea: automate QA voice |
| **50%+ pengurangan masa QA** | AI BIJAN `ToolExecutor` ada banyak placeholder — ambil idea ini untuk polish |

### 2.3 Retool — "Secure your vibe-coded apps" ✅

| Aspek | Data |
|---|---|
| Skala | 10,000+ teams. Pelanggan: Amazon, Boeing, EA, Doctordash, Adobe, Stripe, Philips, Pinterest, Ramp, Brex, Databricks |
| Positioning | "Apps that mean business", production-ready from day one |
| Ciri | Centralized agent harness, Retool CLI, unified engine (connect any DB/API/LLM) |

**Idea yang AI BIJAN belum ada:**

| Idea | Nilai untuk AI BIJAN |
|---|---|
| **⭐ "Apps that mean business" — internal tools builder** | AI BIJAN tiada builder internal tools. Retool: connect DB/API/LLM → app production-ready. **AI BIJAN boleh jadi "internal apps untuk PKS MY"** |
| **⭐ Centralized agent harness** | Semua ejen AI BIJANervised dari satu tempat |
| **Governance: "no new risk"** | Setiap tenant boleh bina app tanpa risiko baharu — pattern untuk white-label |
| **CLI untuk bina** | AI BIJAN ada artisan CLI — boleh jadi `aibijan agent:build` |

### 2.4 Zia (Zoho) — see §1.1

---

## Kumpulan 3 — AI-First Vertical & Agentic Commerce

### 3.1 Shopify — "Be the next AI all-star" ✅

| Aspek | Data |
|---|---|
| Skala | 21,000+ apps di App Store. 150+ updates dua kali setahun |
| AI | **Sidekick** (commerce AI dalam admin), **Agentic Storefronts** (checkout dalam chat), **Campaign Autopilot**, **AI Toolkit for devs** |
| Commerce | **Universal Commerce Protocol (UCP)** + APIs untuk agentic commerce |
| Sasaran | "For anyone from entrepreneurs to enterprise" |

**Idea yang AI BIJAN belum ada:**

| Idea | Nilai untuk AI BIJAN |
|---|---|
| **⭐ Agentic commerce — AI beli-belah & beli** | AI BIJAN `B-Site` + `Blast` + `SalesCopilot` boleh jadi ini. **Agen AI snekerrs bounce, buka chat, tanya, beli** — AI BIJAN tak ada ini langsung |
| **⭐ Sidekick** — AI assistant dalam admin yang_secsumber peluang jualan | AI BIJAN `ExecutiveAI` Command Centre ialah versi lebih kuat — tapi Sidekick ada *di dalam* admin, bukan tab berasingan |
| **Campaign Autopilot** | AI BIJAN `Blast` Fasa 2 — tambah autopilot |
| **Universal Commerce Protocol** | AI BIJAN tak ada protocol standard untuk B2B/omnichannel AI-to-AI |

### 3.2 Stripe — "Financial infrastructure to grow your revenue" ✅

| Aspek | Data |
|---|---|
| Skala | US$1.9tn volum pembayaran 2025, 200M+ langganan, 135+ negara/mata wang, 500M+ API requests/hari, 99.999% uptime |
| AI | **"Monetise through agentic commerce"** — chatbot AI yang boleh "Buy now" |
| Platform | Stripe for Platforms — vertical SaaS embed pembayaran |

**Idea yang AI BIJAN belum ada:**

| Idea | Nilai untuk AI BIJAN |
|---|---|
| **⭐ Agentic commerce payment rail** | Stripe ada infra untuk AI beli-belah. AI BIJAN boleh jadi **pusat perniagaan AI** yang/'
| **⭐ "Make your SaaS platform a complete financial operating system"** | AI BIJAN `BAB 4a` Platform Business — Stripe Connect model untuk AI BIJAN white-label/reseller |
| **MCP server untuk devs** | Stripe ada MCP server + AI developer tools. AI BIJAN `AIGateway` boleh jadi MCP server juga |
| **No-code paths + pre-integrated platforms + build your own** | 3 laluan integrasi — AI BIJAN perlukan ketiga-tiganya |
| **Startups program** | Program untuk startup — AI BIJAN boleh ada "B-Startup Program" |

### 3.3 ServiceTitan — "AI software for commercial and residential trades" ✅

| Aspek | Data |
|---|---|
| Sasaran | Industri trade (plumb, HVAC, elektrik) — **vertikal bukan horizontal** |
| AI | **Titan Intelligence**, **TitanAdvisor** |
| Integrasi | **70+ pre-built integrations** (ServiceChannel, XOi, Viewpoint Spectrum, Sage, Ferguson) |
| Mobile | Field Mobile App |
| Bukti | +5% increase in net profits, +$10M increase in revenue, manage +$5M projects |

**Idea yang AI BIJAN belum ada:**

| Idea | Nilai untuk AI BIJAN |
|---|---|
| **⭐ 70+ integrasi sedia ada** | AI BIJAN `BAB 10` ada conceptually. ServiceTitan tunjuk **pre-built** beats "API exists". AI BIJAN perlukan **connector packs sedia ada** (Shopee, Lazada, TikTok, Grab, MyGDX, SST, e-Invois LHDN) |
| **⭐ Real-time costing + predictive insights per visit** | AI BIJAN `IndustryPacks` ada 19 industri — perlu real-time costing per industri |
| **Field Mobile App** | AI BIJAN `BAB 56B` ada conceptually |
| **Financing / customer financing** | AI BIJAN belum ada — inirottak ke arah kredit |
| **TitanAdvisor (AI advisor)** | AI BIJAN `ExecutiveAI` = advisor; perbezaan: ServiceTitan duduk dalam aliran kerja technician |

### 3.4 Help Scout — "AI built for the right response, not just the fastest" ✅

| Aspek | Data |
|---|---|
| Skala | 12,000+ jenama. 3+ bilion perbualan |
| AI | **Scout** — resolve **73%** permintaan secara purata. **Copilot** — draf + triage |
| Bukti | 56% lebih banyak mesej dijawab pada tahun pertama. 25% CSAT lebih tinggi. 99% dapat balasan <24 jam. 80% kekal 4+ tahun |
| AI-first | "Envoy combines frontier models with insights learned from 3+ billion conversations" |
| Industri | 10 industri dedicated (Ecommerce, Manufacturing & Logistics, Insurance, SaaS, Financial Services, Real Estate, Professional Services, Property Management, Healthcare, Education) |

**Idea yang AI BIJAN belum ada:**

| Idea | Nilai untuk AI BIJAN |
|---|---|
| **⭐⭐ Metric AI yang jelas** | "73% resolve" + "56% lebih banyak mesej" + "25% CSAT lebih tinggi". AI BIJAN tiada satu pun metric AI dalam marketing. **Ini yang hilang dalam jualan** |
| **⭐ Signal — proactive monitoring** | "Catch friction before it becomes a fire". AI BIJAN `ExecutiveAI` briefing dah ke arah ni, tapi belum ada "Signal" untuk support |
| **⭐ 4 produk: Inbox / Envoy / Copilot / Signal** | AI BIJAN `Helpdesk` ada service sahaja. Struktur 4 produk ini boleh jadi model |
| **⭐ 10 industri dedicated pages** | AI BIJAN `IndustryPacks` 19 industri — bina halaman & onboarding per industri |
| **AI Drafts: hujung 1/3 respons, jimat 2.5 FBE** | Bukti yang boleh digunakan dalam jualan AI BIJAN |

### 3.5 Docusign — "AI-assisted eSignature" ✅

| Aspek | Data |
|---|---|
| Skala | 1.7 juta pelanggan, 1 bilion orang |
| Harga | Personal $10/pengguna/bulan · Standard $25 · Business Pro $40 |
| AI | **Docusign Iris** — agreement detection, field suggestions, AI summaries + Q&A, AI Agents |
| Bukti | T-Mobile: -44% agreement time. AAA Washington: 12x faster close, 87% <3 jam. ~30% ROI lebih tinggi |

**Idea yang AI BIJAN belum ada:**

| Idea | Nilai untuk AI BIJAN |
|---|---|
| **⭐ Agreement type detection + auto field suggestion** | AI BIJAN `SignDocs` ada e-tandatangan tapi tak ada AI field placement.-IRISIAL |
| **⭐ Pre-send verification** | Semak penerima sebelum hantar —RIX PDPA |
| **⭐ Doc Gen — tarik data dari Salesforce/SAP untuk jana perjanjian** | AI BIJAN boleh tarik dari CRM/Quotation sendiri (lebih kuat — data dalam sistem) |
| **⭐ SMS + WhatsApp delivery untuk tandatangan** | AI BIJAN ada `Blast` WhatsApp — boleh sambung ke `SignDocs`. **Ini integrasi yang nobody dalam MY ada** |
| **Conditional logic + bulk send + auto-calc values** | AI BIJAN `SignDocs` boleh tambah |
| **Muat turun tamat tempoh yang jelas** | Personal $10 vs AI BIJAN — bundle dalam langganan |

---

## Kumpulan 4 — Migration & Interoperability (Jurang #1)

### 4.1 Dolibarr Import/Export Wizard ✅

| Ciri | Perincian |
|---|---|
| Export wizard | Pilih profil export → tentukan medan → filter + susun medan → **simpan profil** → muat turun |
| Import wizard | CSV atau Excel → **mapping medan** → guna profil tersimpan → **Insert atau Update mode** |
| **Simulator** | "Run the simulator so you can know result of import with no data change" |
| Sasaran | "You can get a predefined empty source files, but any source file can be used" |

### 4.2 AI BIJAN — apa yang ada dan apa yang kurang

| Kemahiran | Ada? | Status |
|---|---|---|
| Import CSV | ✅ | `CsvImporterService` — mapping lajur, dedupe, consent |
| Import Excel | ❌ | Perlu tambah |
| Simpan profil import | ❌ | **Perlu** |
| Dry-run / simulator | ❌ | **Perlu** — kritikal untuk data sensitif |
| Insert vs Update mode | 🟡 | `simpanDenganSemakanDupe` merge — perlu eksplisit |
| Export wizard | ❌ | **Perlu** — untuk "no vendor lock-in" |
| Export penuh semua jadual | ❌ | **Perlu** untuk jualan enterprise |

### 4.3 Idea: "B-Migration Wizard" (Idea #2 paling kritikal)

```
┌──────────────────────────────────────────────────────────────┐
│                   B-MIGRATION WIZARD                         │
├──────────────────────────────────────────────────────────────┤
│  Step 1: Pilih sistem asal                                 │
│    [Bukku] [StoreHub] [Excel] [Xero] [SQL Account]          │
│    [Shopee/Lazada] [Google Sheets] [CSV/XML] [Custom]       │
│                                                              │
│  Step 2: Petakan medan (AISuggest)                          │
│    "Saya ternampak lajur 'No. HP' → phone"                 │
│    [Auto-map] [Manual] [Simpan sebagai profil]             │
│                                                              │
│  Step 3: Dry-run simulator                                  │
│    "12,450 rekod akan diimport. 340 akan dilangkau          │
│     (dupe). 23Medan tidak dipetakan."                      │
│    [Muat turun laporan] [Import sebenar]                    │
│                                                              │
│  Step 4: Import + verify                                    │
│    [Background job] [Live progress] [Rollback]              │
│                                                              │
│  Step 5: Post-migration checklist                           │
│    ☑ e-Invois LHDN disemak                 │
│    ☑ Cukai motor karbon                     │
│    ☑ Stok Opening dip natively             │
└──────────────────────────────────────────────────────────────┘
```

**Kenapa ini penentu #:**
- Tiada pesaing MY buat ini (Bukku & StoreHub tak ada import wizard yang jelas)
- Ini **itarik** — orang akan bertukar kalau prosesnya mudah
- Ia bukti "no vendor lock-in" — pengguna boleh keluar bila-bila masa
- Ia **membolehkan** AI BIJAN jadi "satu-satunya pilihan" — bukan hanya pilihan

---

## Kumpulan 5 — Pesaing Horizontil Universal yang Belum Disahkan

| Produk | Status | Nota |
|---|---|---|
| Microsoft 365 Business | ⚠️ | Redirect OAuth — data tidak disahkan |
| Google Workspace + Gemini | ⚠️ | Perlu audit berasingan |
| NetSuite | ⚠️ | Dalam peta sedia ada sebagai "perakaunan penuh" |
| SAP Business ByDesign | ⚠️ | **Perlu audit** — SaaS ERP untuk mid-market, pesaing langsung AI BIJAN Enterprise |
| Workday | ⚠️ | **Perlu audit** — HR+Finance+Planning, saiz enterprise |
| Xero | ⚠️ | Dalam peta, tetapi "apa yang kurang" belum dianalisis |
| Intuit QuickBooks | ⚠️ | **Perlu audit** — pesaing perakaunan kecil |
| Acumatica | ⚠️ | **Perlu audit** — ERP mid-market, harga rendah |
| Aptean / Sage Intacct | ⚠️ | **Perlu audit** — ERP cloud mid-market |
| Zoho Books | ⚠️ | **Perlu audit** — pesaing langsung AI BIJAN Finance |

---

## Idea Terbaik Yang Patut Digabung (15 Idea, mengikut keutamaan)

| # | Idea | Sumber | Modul | Impak | Kesulitan |
|---|---|---|---|---|---|
| 1 | **⭐ B-Migration Wizard** (profil import + simulator + insert/update) | Dolibarr | Baharu `Migration/` | 🔴 Kritikal | Sederhana |
| 2 | **⭐ Data export penuh (no vendor lock-in)** | Odoo | Baharu `Migration/` | 🔴 Kritikal | Sederhana |
| 3 | **⭐ B-Agent Studio** (visual builder ejen + persona + kelulusan) | Zoho Zia, Dify, CrewAI | `AIAgents` | 🔴 Pembeza | Sederhana |
| 4 | **⭐ AI BIJAN sebagai MCP server** | Dify, Stripe | `AIAgents` | 🔴 Strategik | Rendah |
| 5 | **⭐ Agentic commerce** (AI sneaker bounce → chat → beli) | Shopify UCP, Stripe | `B-Site`, `Blast` | 🔴 Trend | Sederhana |
| 6 | **⭐ Metric AI dalam marketing** (73% resolve, 56% lebih mesej, 25% CSAT) | Help Scout | `Helpdesk`, `ExecutiveAI` | 🔴 Jualan | Rendah |
| 7 | **AI-first eSignature** (Iris: field suggestion + summary + Q&A + WhatsApp delivery) | Docusign | `SignDocs` | 🟠 | Sederhana |
| 8 | **Local business listing sync** (Google/Facebook/Instagram) | Zoho Publish | `Marketplace` | 🟠 | Sederhana |
| 9 | **Plugin Marketplace** isi (model provider + tool + MCP + data source) | Dify | `PluginMarketplace` | 🟠 | Sederhana |
| 10 | **Signal — proactive friction detection** | Help Scout | `ExecutiveAI`, `Helpdesk` | 🟠 | Sederhana |
| 11 | **Knowledge Pipeline dengan test retrieval UI** | Dify | `PersonalAssistant` | 🟡 | Rendah |
| 12 | **Real-time costing per industri + predictive insights** | ServiceTitan | `IndustryPacks` | 🟠 | Sederhana |
| 13 | **Zoho Publish — semak listing** | Zoho | `Marketing` | 🟡 | Rendah |
| 14 | **Payment rails untuk platform (Connect model)** | Stripe | `BAB 4a` | 🟠 | Sederhana |
| 15 | **Halaman onboarding per industri** | Help Scout | `IndustryPacks`, `Site` | 🟠 | Rendah |

---

## Idea Yang TIDAK Patut Dikejar (Kupdated)

| Idea | Sumber | Sebab tolak |
|---|---|---|
| **Buat model AI sendiri** | Stripe, Odoo | Modal + risiko terlalu tinggi. AI BIJAN tetap guna serveras.click |
| **Stripe Connect model penuh** | Stripe | Terlalu besar. Boleh Later |
| **Zapier-style automation marketplace** | Zapier, Make | Network effect — mustahil menang |
| **Social network / marketplace orang** | Facebook | Mustahil |
| **Buat standard protocol global** | Shopify UCP | Terlalu besar; boleh *support* UCP nanti |
| **Enterprise app builder penuh** | Retool | Retool sudah ada 10k+ teams. AI BIJAN boleh buat versi **ringkas untuk PKS MY** sahaja |
| **Vibe-code dalam production** | Odoo.sh, Retool | Risiko keselamatan. Boleh tambah "AI bina internal tool" dengan pagar |

---

## Skor Kompetitif Dikemas Kini

| Dimensi | AI BIJAN (v8.5) | Zoho One | Odoo | Dolibarr | Dify | Retool |
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

> **Kesimpulan jujur:** Skor purata turun dari 6.3 ke **5.0** — bukan sebab AI BIJAN
> menjadi lebih lemah, tetapi kerana **3 dimensi baru yang tidak pernah diukur**
> (migrasi masuk, ekosistem plugin, pematuhan enterprise) diekspose.

**Petunjuk yang jelas:** AI BIJAN menang 3 dimensi (pematuhan MY, AI, keselamatan)
dan kalah 5. Yang paling kritikal ialah **Migrasi masuk (2)** dan **UI (2)**.

---

## Tindakan Prioriti (Revised)

| Keutamaan | Tindakan | Kesan |
|---|---|---|
| **#1** | **B-Migration Wizard** + export penuh | Membolehkan orang bertukar → requirement #1 |
| **#2** | **UI dari 2 → 7** | Skor purata 5.0 → ~6.2 (mendahului Zoho/Dolibarr) |
| **#3** | **Metric AI dalam marketing** (Help Scout style) |Membolehkan jualanROI-based |
| **#4** | **B-Agent Studio** + MCP server | Pembeza + jangkauan tanpa jualan |
| **#5** | **Plugin Marketplace isi** | Network effect mula bermula |
| **#6** | Agentic commerce | Trend 2026 — jangan tertinggal |
| **#7** | AI-first eSignature + WhatsApp signing | Tiada pesaing MY |

---

## Nota Pemeliharaan

Dokumen ini mesti dikemas kini apabila:
1. Pesaing baharu muncul dalam mana-mana kumpulan di atas
2. Harga berubah (semak laman rasmi setiap suku tahun)
3. Ciri baharu AI BIJAN siap

**Jangan** tambah pesaing tanpa menyemak laman mereka terlebih dahulu.
Tanda ⚠️ bermaksud "dikenal pasti tetapi belum disahkan".
