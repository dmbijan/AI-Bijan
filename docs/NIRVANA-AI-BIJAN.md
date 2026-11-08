# 🎯 AI BIJAN — Ringkasan Eksekutif & Peta Produk

> Satu-satunya dokumen yang menjawab: **"apa yang saya bina, dan ke mana ia pergi?"**
> Semua angka rasmi → [`AUDIT-BASELINE.md`](AUDIT-BASELINE.md)

---

## 1. Apa Yang Anda Bina

**AI BIJAN SaaS** — *AI-Native Business Operating System* untuk PKS Malaysia → SEA.

> *"Every Business Deserves a Boardroom."*
> 10+ alat digantikan pada harga **RM288–RM1,888/bulan**.

| Aspek | Nilai |
|---|---|
| **Model** | Multi-tenant SaaS, modular, API-first, industry-aware |
| **Stack** | Laravel 12.70.1 · PHP 8.2.12 · Filament 3.3 · Sanctum |
| **Pasaran** | Malaysia → SEA → Global South |
| **Hierarki admin** | Master → Super Admin → White-label Admin → Customer Admin |
| **Kelebihan** | Pematuhan Malaysia · AI autonomy L0–L5 · WhatsApp-first · BM/EN natively · Unlimited users flat rate |
| **Skor kompetitif** | 7.7/10 (SAP 7.0 · Oracle 7.0 · MSFT 7.0 · Zoho 5.7 · Odoo 5.3) |
| **Model perniagaan** | Brand portfolio · revenue sharing · white-label · subscription engine · AI reseller |

### Peta Pengguna

```
Pelajar/Owner PKS  →  AI BIJAN  →  Owner, Pengurus, Ejen AI, Pelanggan
                       │
      ┌────────────────┼────────────────┐
      ▼                ▼                ▼
  Backoffice       Operation         Growth
  (Finance,        (POS, Stock,      (Marketing, CRM,
   Payroll, HR)     SCM, Delivery)     Blast, Prospecting)
```

---

## 2. Di Mana Ia Berada

```
75 MODUL                          1,629 laluan API        440 jadual
┌─────────────────────────┐      ┌──────────────┐       ┌──────────┐
│ 9 domain perniagaan     │      │ /api/v1/*    │       │ SQLite   │
│ + 15 integrasi 2026     │      │ sanctum +    │       │ (dev)    │
└─────────────────────────┘      │ tenant + perm│       │ MySQL    │
         │                       └──────────────┘       │ (prod)   │
         ▼                                              └──────────┘
  66 Filament resource (/admin)
```

| Domain | Modul |
|---|---|
| **Kewangan** (8) | Finance · Invoicing · Billing · Expense · COGS · Payroll · Cod · Fundamental |
| **Inventori** (6) | Inventory · SCM · Manufacturing · Logistics · Fleet · Assets |
| **Jualan** (10) | POS · Orders · Marketplace · Omnichannel · Booking · ServiceOS · Site · Loyalty · Affiliate · ADE |
| **CRM/SM** (11) | HRMS · CRM · Prospecting · PersonalAssistant · VirtualOffice · Boardroom · SalesCopilot · LiveSupport · TeamAI · Vision · Projects |
| **Pemasaran** (8) | Marketing · ContentStudio · Blast · Link · Seo · Community · Reminder · McpStudio |
| **AI** (9) | AIGateway · AIAgents · ExecutiveAI · AgentStudio · BrainHub · FlowAutomation · WorkflowStudio · DocumentAI · ProofOfValue |
| **Dokumen/Media** (5) | Analytics · BusinessIntel · MediaStudio · SignDocs · MediaEditor |
| **Platform** (3) | Entitlement · IndustryPacks · Helpdesk |
| **Integrasi 2026** (15) | OpenGenAI · VoiceStudio · OpenWhispr · OpenSEO · Workspace · WorkManagement · Scheduling · SharedInbox · ScreenRecorder · VideoStudio · POSBpos · OmniOrder · SocialBridge · ContentForge · WhatsAppSuite |

📄 Senarai penuh + descriptor: [`SENARAI-MODUL.md`](SENARAI-MODUL.md)

---

## 3. 15 Modul Integrasi 2026 — Apa Yang Diambil

| Modul | Sumber | Yang Diambil |
|---|---|---|
| `OpenGenAI` | Higgsfield | Enjin generatif 6 provider (OpenAI/Anthropic/Gemini/Groq/Ollama/Replicate) |
| `VoiceStudio` | ElevenLabs | TTS — profil suara BM, 4 enjin |
| `OpenWhispr` | Wispr Flow | STT + kamus perniagaan |
| `OpenSEO` | Semrush | SERP, backlink, celah kandungan, skor kata kunci |
| `Workspace` | Notion | Dokumen berasaskan blok, pangkalan data kolaboratif |
| `WorkManagement` | Asana | Gantt, laluan kritikal, garis dasar, bajet, portfolio |
| `Scheduling` | Calendly | Pautan penjadualan, routing provider |
| `SharedInbox` | Intercom | SLA, makro, ulasan sedia ada |
| `ScreenRecorder` | Loom | Rakaman skrin, pautan kongsi, komen timeline |
| `VideoStudio` | VOYNANN | 3 skill video (mograph/motion 3D/editor) |
| `POSBpos` | B-POS | Kad hadiah, tempahan meja, food court, kedai online |
| `OmniOrder` | Luxana | Pesanan 6 saluran + 5 alat perlindungan hasil |
| `SocialBridge` | Post Bridge | Siaran silang 10 platform |
| `ContentForge` | SEO Writing | Kandungan panjang, pemeriksa SEO, auto-post WordPress |
| `WhatsAppSuite` | WasapBot | Chatbot AI, blast anti-ban, pengesah nombor, warm-up |

📄 Butiran setiap satu: [`INTEGRASI-SUMBER-TERBUKA.md`](INTEGRASI-SUMBER-TERBUKA.md) ·
[`INTEGRASI-BPOS.md`](INTEGRASI-BPOS.md) ·
[`INTEGRASI-4-LAMAN-WEB.md`](INTEGRASI-4-LAMAN-WEB.md) ·
[`INTEGRASI-VOYNANN.md`](INTEGRASI-VOYNANN.md)

---

## 4. Integrasi Yang Sudah Disambungkan

15 modul baharu **bukan** islands — setiap satu digunakan oleh platform:

```
ContentStudio ──→ OpenGenAI          (tenant boleh guna provider sendiri)
ContentForge  ──→ ContentStudio     (rangka AI)
OpenSEO       ──→ ContentForge      (audit kandungan guna pemeriksa SEO)
SocialBridge  ──→ ContentStudio     (badan auto-jana)
Workspace     ──→ ContentStudio     (dokumen AI)
OpenWhispr    ──→ VoiceStudio       (transkripsi → narasi TTS)
SharedInbox   ──→ OpenGenAI         (balasan AI)
ScreenRecorder──→ MediaEditor       (buka untuk sunting)
VideoStudio   ──→ MediaEditor       (video → editor)
OmniOrder     ──→ Orders            (pesanan → unified order)
Blast         ──→ WhatsAppSuite     (blast WhatsApp)
Scheduling    ──→ Booking           (slot → tempahan)
WorkManagement──→ Projects          (garis masa → projek)
POSBpos       ──→ POS               (ciri lanjutan POS)
```

---

## 5. Keselamatan & Kualiti — Status Sebenar

| Domain | Status | Bukti |
|---|---|---|
| Pengasingan tenant | ✅ **75/75 modul** | `skrip/audit-tenant.php` |
| Pagar kebenaran | ✅ **0 laluan terdedah** | `skrip/semak-kebenaran.php` |
| Konsistensi model/skema | ✅ **0 isu** | `skrip/audit-tenant.php` |
| Suite ujian | ✅ **1,424 lulus · 0 gagal** | `php vendor/bin/phpunit` |
| Advisori keselamatan | ✅ **0** | `composer audit` |
| Baseline automatik | ✅ | `skrip/jana-baseline.php --tulis` |
| Pautan dokumen | ✅ **0 rosak** | `skrip/semak-pautan-dokumen.php` |

---

## 6. Apa Yang Belum Mengikut Keutamaan

### 🔴 P0 — Wajib sebelum jualan komersial

| ID | Tindakan | Status |
|---|---|---|
| P0-01 | Sahkan baseline pada satu commit | ✅ Selesai |
| P0-02 | Audit tenant isolation | ✅ Selesai (75/75) |
| P0-03 | Audit authorization | ✅ Selesai (0 terdedah) |
| P0-04 | Payment webhook security | ⬜ Belum — perlu signature/replay/idempotency |
| P0-05 | AI Gateway enforcement | ⬜ Belum — perlu static scan "tiada direct provider call" |
| P0-06 | Secret & PII controls | ⬜ Belum — perlu secret scan + key rotation |
| P0-07 | Backup/restore + rollback drill | ⬜ Belum |
| P0-08 | Label status ciri dengan jujur | ⬜ Belum — registry status ciri |
| P0-09 | Bersihkan status dokumen lama | ✅ Selesai (commit `bba5560`) |

### 🟡 P1 — Sebelum skal atau menjual integrasi sebagai "siap"

| ID | Tindakan | Status |
|---|---|---|
| P1-01 | Ujian konkurensi unified order/stok | ⬜ |
| P1-02 | Kitaran penuh pembayaran setiap gateway | ⬜ |
| P1-03 | Lead-to-cash end-to-end | ⬜ |
| P1-04 | Acceptance test connector sosial/WhatsApp | ⬜ |
| P1-05 | Definisi KPI dashboard | ⬜ |
| P1-06 | Queue/outbox/retry/dead-letter | ⬜ |
| P1-07 | Ujian policy autonomy & approval AI agent | ⬜ |
| P1-08 | Inventori data & jadual retensi PDPA | ⬜ |
| P1-09 | Review pematuhan e-Invoice/payroll | ⬜ |
| P1-10 | Stress test margin reseller | ⬜ |

📄 Matriks penuh: [`AI-BIJAN-AUDIT-INDUK-LENGKAP.md`](AI-BIJAN-AUDIT-INDUK-LENGKAP.md) §15

---

## 7. Batasan Yang Anda Tahu

- 🔌 Tiada integrasi luar **aktif** — Meta/WhatsApp, CHIP/FPX, LHDN e-Invois,
  Google/Meta Ads, penghantar kurier → perlu akaun + kelulusan provider.
- 🗄️ Belum disahkan pada **MySQL** — dev jalan SQLite.
- 🔐 Audit keselamatan belum lengkap — belum penetration/load test, belum
  drill backup/restore.
- 📋 Pematuhan belum disahkan pihak berkuasa — PDPA, GST, e-Invois, EPF/SOCSO.
- 🤖 AI Gateway dalam **mod simulasi** (`AI_GATEWAY_LIVE=false`).

---

## 8. Apa Hendak Dilakukan

| Urutan | Tindakan | Fail |
|---|---|---|
| 1 | Semak & merge PR `feat/integrasi-9-suite` → `main` | GitHub |
| 2 | P0-04 … P0-08 (audit induk §15) | — |
| 3 | Sambungkan provider luar (CHIP, Meta, kurier) | — |
| 4 | MySQL + backup drill | — |
| 5 | P1-* | — |

---

## 9. Peta Repositori

| Lokasi | Isi |
|---|---|
| `app/Modules/` | 75 modul domain |
| `app/Filament/Resources/` | 66 resource admin UI |
| `app/Support/Tenancy/` | `TenantContext` — pengasingan tenant |
| `database/migrations/` | Migrasi platform + migrasi per modul |
| `docs/` | 49 dokumen aktif · `archive/` versi lama |
| `skrip/` | 39 skrip penyelenggaraan |
| `.github/skills/` | Skill pengekodan AI + 3 skill video |
| `tests/` | 118 fail ujian |

---

*Nirvana ini kekal sebagai satu dokumen. Sejarah perubahan ada di
[`CHANGELOG.md`](CHANGELOG.md); kerja belum siap di [`TODO.md`](TODO.md).*
